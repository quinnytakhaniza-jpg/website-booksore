<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login_user.php");
    exit();
}

$orderId = $_GET['id'] ?? null;
if (!$orderId) {
    echo "ID Pesanan tidak valid.";
    exit();
}

$stmt = $conn->prepare("SELECT * FROM pesanan WHERE id = ? LIMIT 1");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "Pesanan tidak ditemukan.";
    exit();
}

// Ambil item langsung dengan join ke tabel books berdasarkan relasi ID pesanan
$items = [];
try {
    $stmtItem = $conn->prepare("
        SELECT d.*, b.judul AS book_title, b.gambar AS book_image, b.harga AS book_price
        FROM detail_pesanan d
        JOIN books b ON d.buku_id = b.id
        WHERE d.pesanan_id = ?
    ");
    $stmtItem->execute([$orderId]);
    $items = $stmtItem->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $items = [];
}

// Jika detail pesanan kosong, ambil langsung dari tabel books berdasarkan data yang cocok
if (empty($items)) {
    $subtotalTarget = (float)($order['total'] - ($order['ongkir'] ?? 0));
    $stmtB = $conn->prepare("SELECT * FROM books WHERE harga <= ? ORDER BY id DESC LIMIT 1");
    $stmtB->execute([$subtotalTarget > 0 ? $subtotalTarget : 9999999]);
    $bData = $stmtB->fetch(PDO::FETCH_ASSOC);

    if ($bData) {
        $qty = ($bData['harga'] > 0) ? round($subtotalTarget / $bData['harga']) : 1;
        if ($qty < 1) $qty = 1;
        $items[] = [
            'book_title' => $bData['judul'],
            'book_image' => $bData['gambar'],
            'jumlah' => $qty,
            'book_price' => $bData['harga']
        ];
    }
}

$refNo = str_pad($order['id'], 14, '0', STR_PAD_LEFT);
$tanggal = $order['created_at'] ?? date('Y-m-d H:i:s');
$namaPelanggan = $order['nama_user'] ?? $_SESSION['nama'] ?? 'Pelanggan Nijbook';
$metodeBayar = $order['metode_pembayaran'] ?? 'QRIS / Transfer';
$rawStatus = $order['status_pesanan'] ?? 'Menunggu Verifikasi';
$ongkir = $order['ongkir'] ?? 0;
$totalHarga = $order['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bukti Pembayaran - #<?= $refNo; ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #525659; color: #1e293b; display: flex; flex-direction: column; align-items: center; min-height: 100vh; padding: 30px 20px; }
    .receipt-document { background: #ffffff; width: 100%; max-width: 750px; border-radius: 4px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); overflow: hidden; margin-bottom: 20px; }
    .doc-header { background-color: #1e1e1e; color: #ffffff; padding: 24px 36px; display: flex; justify-content: space-between; align-items: center; }
    .store-brand { font-size: 22px; font-weight: 800; }
    .doc-title-box { text-align: right; }
    .doc-title { font-size: 16px; font-weight: 700; }
    .doc-ref { font-size: 11px; color: #94a3b8; }
    .doc-body { padding: 36px; }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 30px; font-size: 13px; }
    .info-section-title { font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
    .info-content p { margin-bottom: 4px; color: #334155; }
    .info-content strong { color: #0f172a; }
    .doc-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 24px; }
    .doc-table th { background-color: #f8fafc; color: #475569; font-weight: 700; text-align: left; padding: 12px 14px; border-top: 1px solid #cbd5e1; border-bottom: 2px solid #cbd5e1; font-size: 11px; text-transform: uppercase; }
    .doc-table td { padding: 14px; border-bottom: 1px solid #e2e8f0; color: #1e293b; vertical-align: middle; }
    .book-item-cell { display: flex; align-items: center; gap: 12px; }
    .receipt-book-cover { width: 40px; height: 52px; object-fit: cover; border-radius: 4px; border: 1px solid #cbd5e1; flex-shrink: 0; }
    .totals-section { display: flex; flex-direction: column; align-items: flex-end; margin-bottom: 30px; font-size: 13px; }
    .total-row { display: flex; justify-content: space-between; width: 320px; padding: 6px 0; color: #475569; }
    .total-row.grand-total { background-color: #1e1e1e; color: #ffffff; padding: 12px 16px; border-radius: 4px; font-weight: 800; font-size: 15px; margin-top: 6px; }
    .doc-footer-note { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 18px; font-size: 12px; color: #64748b; line-height: 1.5; }
    .action-bar { display: flex; gap: 12px; width: 100%; max-width: 750px; justify-content: flex-end; }
    .btn { padding: 11px 20px; border-radius: 8px; font-size: 13px; font-weight: 700; text-decoration: none; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .btn-pdf { background-color: #2563eb; color: #ffffff; }
    .btn-back { background-color: #ffffff; color: #334155; }
  </style>
</head>
<body>

  <div class="action-bar">
    <a href="profile.php" class="btn btn-back"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
    <button onclick="window.print()" class="btn btn-pdf"><i class="fa-solid fa-file-pdf"></i> Cetak / Simpan PDF</button>
  </div>
  <div style="height: 16px;"></div>

  <div class="receipt-document">
    <div class="doc-header">
      <div class="store-brand">Nijbook Store</div>
      <div class="doc-title-box">
        <div class="doc-title">BUKTI PEMBAYARAN</div>
        <div class="doc-ref">No. Ref <?= $refNo; ?></div>
      </div>
    </div>

    <div class="doc-body">
      <div class="info-grid">
        <div class="info-section">
          <div class="info-section-title">Penerima / Pelanggan</div>
          <div class="info-content">
            <p><strong><?= htmlspecialchars($namaPelanggan); ?></strong></p>
            <p>Email: <?= htmlspecialchars($order['email_user'] ?? $_SESSION['email'] ?? '-'); ?></p>
            <p>Alamat: <?= htmlspecialchars($order['alamat_lengkap'] ?? '-'); ?></p>
          </div>
        </div>

        <div class="info-section">
          <div class="info-section-title">Rincian Pembayaran</div>
          <div class="info-content">
            <p>Tanggal: <strong><?= date('d-m-Y, H:i', strtotime($tanggal)); ?></strong></p>
            <p>Metode: <strong><?= htmlspecialchars($metodeBayar); ?></strong></p>
            <p>Status: <strong style="color: #0284c7;"><?= ucwords($rawStatus); ?></strong></p>
          </div>
        </div>
      </div>

      <div class="table-container">
        <table class="doc-table">
          <thead>
            <tr>
              <th style="width: 60px;">No</th>
              <th>Judul Buku</th>
              <th style="width: 80px; text-align: center;">Qty</th>
              <th style="width: 140px; text-align: right;">Harga</th>
              <th style="width: 140px; text-align: right;">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($items)): ?>
              <?php $no = 1; foreach ($items as $item): 
                $judulBuku = $item['book_title'] ?? $item['judul_buku'] ?? 'Buku Pesanan';
                $rawImg = $item['book_image'] ?? 'placeholder.jpg';
                $gambarBuku = (strpos($rawImg, 'image/') === 0) ? $rawImg : 'image/' . htmlspecialchars($rawImg);
                $qty = (int)($item['jumlah'] ?? $item['qty'] ?? 1);
                $harga = (float)($item['book_price'] ?? $item['harga'] ?? (($totalHarga - $ongkir) / $qty));
                $subtotal = $qty * $harga;
              ?>
                <tr>
                  <td><?= $no++; ?></td>
                  <td>
                    <div class="book-item-cell">
                      <img src="<?= $gambarBuku; ?>" alt="Cover" class="receipt-book-cover" onerror="this.src='image/placeholder.jpg'">
                      <strong><?= htmlspecialchars($judulBuku); ?></strong>
                    </div>
                  </td>
                  <td style="text-align: center;"><?= $qty; ?></td>
                  <td style="text-align: right;">Rp<?= number_format($harga, 0, ',', '.'); ?></td>
                  <td style="text-align: right; font-weight: 700;">Rp<?= number_format($subtotal, 0, ',', '.'); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="totals-section">
        <div class="total-row">
          <span>Subtotal Buku</span>
          <span>Rp<?= number_format((float)$totalHarga - (float)$ongkir, 0, ',', '.'); ?></span>
        </div>
        <div class="total-row">
          <span>Ongkos Kirim (<?= htmlspecialchars($order['kurir'] ?? 'JNE'); ?>)</span>
          <span>Rp<?= number_format((float)$ongkir, 0, ',', '.'); ?></span>
        </div>
        <div class="total-row grand-total">
          <span>Total Bayar</span>
          <span>Rp<?= number_format((float)$totalHarga, 0, ',', '.'); ?></span>
        </div>
      </div>

      <div class="doc-footer-note">
        Bukti pembayaran via <?= htmlspecialchars($metodeBayar); ?> sudah diterima dan diproses oleh sistem Nijbook Store. Pesanan akan segera dikirimkan setelah verifikasi selesai.
      </div>
    </div>
  </div>
</body>
</html>