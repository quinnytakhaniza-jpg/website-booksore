<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || $_SESSION['role'] !== 'user') {
    header("Location: login_user.php");
    exit();
}

$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['nama'] ?? 'User';
$userEmail = $_SESSION['email'] ?? '';

if ($userId && (empty($userName) || empty($userEmail))) {
    $stmt = $conn->prepare("SELECT nama, email FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $userName = $user['nama'];
        $userEmail = $user['email'];
        $_SESSION['nama'] = $userName;
        $_SESSION['email'] = $userEmail;
    }
}

// Ambil data pesanan berdasarkan user_id (user yang sedang login)
$pesananList = [];
if ($userId) {
    try {
        $stmtPesanan = $conn->prepare("SELECT * FROM pesanan WHERE user_id = ? ORDER BY id DESC");
        $stmtPesanan->execute([$userId]);
        $pesananList = $stmtPesanan->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $pesananList = [];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil Saya - Nijbook</title>
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: #f8fafc;
      color: #0f172a;
      min-height: 100vh;
      display: flex;
    }

    /* SIDEBAR KIRI */
    .app-sidebar {
      width: 260px;
      background: #ffffff;
      border-right: 1px solid #e2e8f0;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 24px;
      position: fixed;
      height: 100vh;
    }

    .brand-logo {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 20px;
      font-weight: 800;
      color: #2563eb;
      text-decoration: none;
    }

    .sidebar-menu {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-top: 40px;
    }

    .sidebar-link {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      border-radius: 10px;
      color: #64748b;
      text-decoration: none;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.2s;
    }

    .sidebar-link:hover, .sidebar-link.active {
      background-color: #eff6ff;
      color: #2563eb;
    }

    /* KONTEN UTAMA */
    .main-wrapper {
      margin-left: 260px;
      flex: 1;
      padding: 40px;
      max-width: 1200px;
    }

    @media (max-width: 900px) {
      .app-sidebar { display: none; }
      .main-wrapper { margin-left: 0; padding: 20px; }
    }

    .topbar-icons {
      display: flex;
      justify-content: flex-end;
      gap: 16px;
      margin-bottom: 30px;
    }

    .topbar-icon-btn {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #475569;
      text-decoration: none;
      transition: all 0.2s;
    }

    .topbar-icon-btn:hover {
      border-color: #2563eb;
      color: #2563eb;
    }

    .dashboard-grid {
      display: grid;
      grid-template-columns: 1fr 340px;
      gap: 32px;
      align-items: start;
    }

    @media (max-width: 1024px) {
      .dashboard-grid { grid-template-columns: 1fr; }
    }

    .section-title {
      font-size: 26px;
      font-weight: 800;
      color: #0f172a;
      margin-bottom: 4px;
    }

    .section-subtitle {
      font-size: 13px;
      color: #64748b;
      margin-bottom: 24px;
    }

    .profile-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      padding: 24px;
      margin-bottom: 32px;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
    }

    .profile-info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    .profile-info-item span {
      font-size: 12px;
      color: #94a3b8;
      font-weight: 600;
      display: block;
      margin-bottom: 4px;
    }

    .profile-info-item strong {
      font-size: 14px;
      color: #0f172a;
      font-weight: 700;
    }

    .orders-section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
    }

    .orders-section-header h3 {
      font-size: 18px;
      font-weight: 800;
      color: #0f172a;
    }

    .view-all-link {
      font-size: 13px;
      font-weight: 700;
      color: #2563eb;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .order-card-item {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      padding: 20px;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      text-decoration: none;
      transition: all 0.2s;
      box-shadow: 0 2px 4px rgba(0,0,0,0.01);
    }

    .order-card-item:hover {
      border-color: #2563eb;
      transform: translateY(-2px);
    }

    .order-card-left {
      display: flex;
      align-items: center;
      gap: 16px;
      min-width: 0;
    }

    .book-cover-img {
      width: 52px;
      height: 68px;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid #e2e8f0;
      flex-shrink: 0;
    }

    .order-meta {
      min-width: 0;
    }

    .order-meta h4 {
      font-size: 14px;
      font-weight: 700;
      color: #0f172a;
      margin-bottom: 2px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .order-meta p {
      font-size: 12px;
      color: #64748b;
      margin-bottom: 6px;
    }

    .order-details-sub {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      font-size: 11px;
      color: #64748b;
    }

    .order-status-badge {
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 700;
      white-space: nowrap;
    }

    .status-completed { background: #d1fae5; color: #059669; }
    .status-processing { background: #dbeafe; color: #2563eb; }
    .status-pending { background: #fef3c7; color: #d97706; }
    .status-cancelled { background: #fee2e2; color: #dc2626; }

    /* PURCHASE HISTORY */
    .history-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      padding: 24px;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
    }

    .history-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 12px;
      border-bottom: 1px solid #f1f5f9;
    }

    .history-header h3 {
      font-size: 16px;
      font-weight: 800;
      color: #0f172a;
    }

    .history-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid #f8fafc;
      gap: 12px;
    }

    .history-item:last-child { border-bottom: none; }

    .history-item-left {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 0;
    }

    .history-thumb {
      width: 36px;
      height: 46px;
      object-fit: cover;
      border-radius: 6px;
      border: 1px solid #e2e8f0;
      flex-shrink: 0;
    }

    .history-info {
      min-width: 0;
    }

    .history-info h5 {
      font-size: 12px;
      font-weight: 700;
      color: #0f172a;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .history-info span {
      font-size: 10px;
      color: #94a3b8;
    }

    .history-price {
      font-size: 12px;
      font-weight: 700;
      color: #0f172a;
      white-space: nowrap;
    }

    .quote-box {
      margin-top: 24px;
      text-align: center;
      padding: 20px;
      font-style: italic;
      font-size: 13px;
      color: #475569;
      line-height: 1.5;
    }

    .btn-logout-custom {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      background: #fef2f2;
      color: #dc2626;
      border-radius: 8px;
      text-decoration: none;
      font-size: 13px;
      font-weight: 700;
      margin-top: 16px;
      transition: background 0.2s;
    }
    .btn-logout-custom:hover { background: #fee2e2; }
  </style>
</head>
<body>

  <!-- SIDEBAR KIRI -->
  <aside class="app-sidebar">
    <div>
      <a href="home.php" class="brand-logo">
        <i class="fa-solid fa-book-open"></i>
        <span>Nijbook</span>
      </a>

      <div class="sidebar-menu">
        <a href="home.php" class="sidebar-link"><i class="fa-solid fa-house"></i> Beranda</a>
        <a href="cart.php" class="sidebar-link"><i class="fa-solid fa-bag-shopping"></i> Keranjang</a>
        <a href="profile.php" class="sidebar-link active"><i class="fa-regular fa-user"></i> Profil Saya</a>
      </div>
    </div>

    <div>
      <a href="logout.php" class="btn-logout-custom">
        <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
      </a>
    </div>
  </aside>

  <!-- KONTEN UTAMA -->
  <main class="main-wrapper">
    
    <div class="topbar-icons">
      <a href="cart.php" class="topbar-icon-btn" aria-label="Keranjang"><i class="fa-solid fa-bag-shopping"></i></a>
      <a href="wishlist.php" class="topbar-icon-btn" aria-label="Wishlist"><i class="fa-regular fa-heart"></i></a>
    </div>

    <div class="dashboard-grid">
      
      <div>
        <h1 class="section-title">My Profile</h1>
        <p class="section-subtitle">Manage your account, view your orders, and track your purchases.</p>

        <!-- KARTU PROFIL -->
        <div class="profile-card">
          <div class="profile-info-grid">
            <div class="profile-info-item">
              <span>Name</span>
              <strong><?= htmlspecialchars($userName); ?></strong>
            </div>
            <div class="profile-info-item">
              <span>Email</span>
              <strong><?= htmlspecialchars($userEmail); ?></strong>
            </div>
          </div>
        </div>

        <!-- MY ORDERS -->
        <div class="orders-section-header">
          <h3>My Orders</h3>
          <a href="home.php" class="view-all-link">Jelajahi Buku <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="orders-container">
          <?php if (!empty($pesananList)) : ?>
            <?php foreach ($pesananList as $p) : 
              $orderId = $p['id'] ?? 1;

              // Ambil detail buku & gambar berdasarkan pesanan_id dan buku_id
              $stmtDetail = $conn->prepare("
                SELECT d.*, b.judul AS book_title, b.gambar AS book_image 
                FROM detail_pesanan d 
                LEFT JOIN books b ON d.buku_id = b.id 
                WHERE d.pesanan_id = ?
              ");
              $stmtDetail->execute([$orderId]);
              $detailItem = $stmtDetail->fetch(PDO::FETCH_ASSOC);

              $judulBuku = $detailItem['book_title'] ?? $detailItem['judul_buku'] ?? 'Pesanan Buku Nijbook';
              $gambarBuku = !empty($detailItem['book_image']) ? 'image/' . htmlspecialchars($detailItem['book_image']) : 'image/placeholder.jpg';
              $qty = $detailItem['jumlah'] ?? 1;

              $rawStatus = $p['status_pesanan'] ?? $p['status'] ?? $p['status_pembayaran'] ?? 'Pending';
              $st = strtolower(trim($rawStatus));

              if (str_contains($st, 'selesai') || str_contains($st, 'dikirim') || str_contains($st, 'terverifikasi') || str_contains($st, 'disetujui') || str_contains($st, 'sukses') || str_contains($st, 'berhasil')) {
                $statusClass = 'status-completed';
                $statusText = 'Completed';
              } elseif (str_contains($st, 'proses') || str_contains($st, 'dibayar') || str_contains($st, 'bayar') || str_contains($st, 'verifikasi') || str_contains($st, 'konfirmasi')) {
                $statusClass = 'status-processing';
                $statusText = 'Processing';
              } elseif (str_contains($st, 'batal') || str_contains($st, 'tolak') || str_contains($st, 'gagal')) {
                $statusClass = 'status-cancelled';
                $statusText = 'Cancelled';
              } else {
                $statusClass = 'status-pending';
                $statusText = 'Pending';
              }

              $total = $p['total'] ?? $p['total_harga'] ?? 0;
              $tanggal = $p['created_at'] ?? $p['tanggal'] ?? 'now';
              $formattedOrderId = "ORD-" . str_pad((string)$orderId, 6, "0", STR_PAD_LEFT);
            ?>
              <a href="struk.php?id=<?= $orderId; ?>" class="order-card-item">
                <div class="order-card-left">
                  <img src="<?= $gambarBuku; ?>" alt="<?= htmlspecialchars($judulBuku); ?>" class="book-cover-img">
                  <div class="order-meta">
                    <h4><?= htmlspecialchars($judulBuku); ?></h4>
                    <p>Nijbook Store</p>
                    <div class="order-details-sub">
                      <span><i class="fa-regular fa-calendar"></i> <?= date('M d, Y', strtotime($tanggal)); ?></span>
                      <span><i class="fa-solid fa-hashtag"></i> <?= $formattedOrderId; ?></span>
                      <span><i class="fa-solid fa-box"></i> Qty: <?= $qty; ?></span>
                      <span><strong>Rp<?= number_format((float)$total, 0, ',', '.'); ?></strong></span>
                    </div>
                  </div>
                </div>
                <span class="order-status-badge <?= $statusClass; ?>">
                  <?= $statusText; ?>
                </span>
              </a>
            <?php endforeach; ?>
          <?php else : ?>
            <div style="text-align: center; padding: 40px; background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 16px; color: #94a3b8; font-size: 13px;">
              <i class="fa-solid fa-box-open" style="font-size: 32px; margin-bottom: 10px; display: block; color: #cbd5e1;"></i>
              Belum ada riwayat pesanan buku.
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- PURCHASE HISTORY -->
      <div>
        <div class="history-card">
          <div class="history-header">
            <h3>Purchase History</h3>
            <a href="home.php" class="view-all-link" style="font-size: 12px;">View All <i class="fa-solid fa-arrow-right"></i></a>
          </div>

          <div class="history-list">
            <?php if (!empty($pesananList)) : ?>
              <?php foreach (array_slice($pesananList, 0, 5) as $p) : 
                $orderId = $p['id'] ?? 1;
                $stmtH = $conn->prepare("
                  SELECT d.*, b.judul AS book_title, b.gambar AS book_image 
                  FROM detail_pesanan d 
                  LEFT JOIN books b ON d.buku_id = b.id 
                  WHERE d.pesanan_id = ?
                ");
                $stmtH->execute([$orderId]);
                $hItem = $stmtH->fetch(PDO::FETCH_ASSOC);

                $judulBuku = $hItem['book_title'] ?? $hItem['judul_buku'] ?? 'Buku Nijbook';
                $gambarBuku = !empty($hItem['book_image']) ? 'image/' . htmlspecialchars($hItem['book_image']) : 'image/placeholder.jpg';
                $total = $p['total'] ?? $p['total_harga'] ?? 0;
                $tanggal = $p['created_at'] ?? $p['tanggal'] ?? 'now';
              ?>
                <div class="history-item">
                  <div class="history-item-left">
                    <img src="<?= $gambarBuku; ?>" alt="Cover" class="history-thumb">
                    <div class="history-info">
                      <h5 title="<?= htmlspecialchars($judulBuku); ?>"><?= htmlspecialchars($judulBuku); ?></h5>
                      <span><?= date('M d, Y', strtotime($tanggal)); ?></span>
                    </div>
                  </div>
                  <div class="history-price">
                    Rp<?= number_format((float)$total, 0, ',', '.'); ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else : ?>
              <p style="font-size: 12px; color: #94a3b8; text-align: center; padding: 20px;">No recent purchases.</p>
            <?php endif; ?>
          </div>
        </div>

        <div class="quote-box">
          “Jika kamu tidak suka membaca maka kamu belum menemukan buku yang tepat”
        </div>
      </div>

    </div>
  </main>

  <!-- WIDGET LIVE CHAT CUSTOMER SERVICE -->
  <?php include 'live_chat.php'; ?>

</body>
</html>