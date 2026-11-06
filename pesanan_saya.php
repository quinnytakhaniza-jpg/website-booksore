<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || ($_SESSION['role'] ?? '') !== 'user') {
    header("Location: login_user.php");
    exit();
}

$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['nama'] ?? 'Pelanggan';

// Ambil data pesanan milik user dari database
$pesananList = [];
if ($userId) {
    $tablesToTry = ['pesanan', 'orders', 'transaksi', 'checkout'];
    foreach ($tablesToTry as $tableName) {
        try {
            $stmt = $conn->prepare("SELECT * FROM {$tableName} WHERE user_id = ? ORDER BY id DESC");
            $stmt->execute([$userId]);
            $pesananList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($pesananList)) {
                break;
            }
        } catch (PDOException $e) {
            continue;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pesanan Saya - Nijbook</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #1e293b; padding: 40px 20px; display: flex; justify-content: center; min-height: 100vh; }
    
    .container { width: 100%; max-width: 700px; }
    
    .header-card { background: #ffffff; padding: 24px; border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
    .header-title h1 { font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 2px; }
    .header-title p { font-size: 12px; color: #64748b; }
    
    .btn-home { background-color: #f1f5f9; color: #334155; padding: 10px 16px; border-radius: 10px; font-size: 12px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    .btn-home:hover { background-color: #e2e8f0; }

    .order-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.01); }
    .order-card:hover { border-color: #2563eb; transform: translateY(-2px); }

    .order-info strong { display: block; font-size: 15px; color: #0f172a; margin-bottom: 4px; }
    .order-info span { font-size: 12px; color: #64748b; }

    .order-right { text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
    .order-price { font-size: 14px; font-weight: 800; color: #2563eb; }
    
    .btn-detail { background-color: #2563eb; color: #ffffff; padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
    .btn-detail:hover { background-color: #1d4ed8; }

    .empty-box { background: #ffffff; padding: 50px 20px; text-align: center; border-radius: 16px; border: 1px solid #e2e8f0; color: #94a3b8; font-size: 13px; }
  </style>
</head>
<body>

  <div class="container">
    <div class="header-card">
      <div class="header-title">
        <h1>Pesanan Saya</h1>
        <p>Daftar riwayat transaksi belanja buku oleh <?= htmlspecialchars($userName); ?></p>
      </div>
      <a href="home.php" class="btn-home"><i class="fa-solid fa-house"></i> Beranda</a>
    </div>

    <?php if (!empty($pesananList)) : ?>
      <?php foreach ($pesananList as $p) : 
        $orderId = $p['id'] ?? $p['id_pesanan'] ?? 1;
        $judul = $p['judul_buku'] ?? $p['nama_buku'] ?? $p['buku'] ?? 'Pesanan Buku Nijbook';
        $total = $p['total_harga'] ?? $p['total'] ?? $p['harga'] ?? 0;
        $tanggal = $p['created_at'] ?? $p['tanggal'] ?? 'now';
        $status = $p['status'] ?? $p['status_pembelian'] ?? 'Pending';
      ?>
        <div class="order-card">
          <div class="order-info">
            <strong><?= htmlspecialchars($judul); ?></strong>
            <span>ID: #ORD-<?= str_pad($orderId, 5, '0', STR_PAD_LEFT); ?> • <?= date('d M Y', strtotime($tanggal)); ?> • <strong style="color: #d97706;"><?= ucwords($status); ?></strong></span>
          </div>
          <div class="order-right">
            <div class="order-price">Rp<?= number_format((float)$total, 0, ',', '.'); ?></div>
            <a href="struk.php?id=<?= $orderId; ?>" class="btn-detail">
              <i class="fa-solid fa-file-invoice"></i> Lihat Struk
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else : ?>
      <div class="empty-box">
        <i class="fa-solid fa-box-open" style="font-size: 36px; margin-bottom: 10px; display: block; color: #cbd5e1;"></i>
        Belum ada riwayat pesanan yang tercatat.
      </div>
    <?php endif; ?>
  </div>

</body>
</html>