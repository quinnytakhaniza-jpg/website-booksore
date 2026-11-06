<?php
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
  require_once 'function.php';
  require_once 'koneksi.php';

  $isUserLoggedIn = (isset($_SESSION['login']) && $_SESSION['login'] === true && isset($_SESSION['role']) && $_SESSION['role'] === 'user');
  $userName  = $_SESSION['nama'] ?? 'User';
  $userEmail = $_SESSION['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us - Nijbook</title>
  <link rel="stylesheet" href="deskripsi.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

  <div class="top-banner">
    <span>📦 🔥 <strong>Diskon hingga 50%</strong> untuk member baru!</span>
    <a href="#" class="btn-top-banner">Belanja Sekarang</a>
    <span class="close-banner">&times;</span>
  </div>

  <header class="navbar-wrapper">
    <div class="navbar-container">
      <a href="home.php" class="brand-logo">
        <i class="fa-solid fa-book-open"></i>
        <span>Nijbook</span>
      </a>

      <nav class="nav-links">
        <a href="home.php">Beranda</a>
        <a href="kategori.php">Kategori</a>
        <a href="home.php#popular-books">Buku</a>
        <a href="about.php" class="active">About Us</a>
        <a href="kontak.php">Kontak</a>
      </nav>

      <div class="search-box">
        <input type="text" placeholder="Cari buku, penulis, atau genre...">
        <button type="button"><i class="fa-solid fa-magnifying-glass"></i></button>
      </div>

      <div class="nav-icons">
        <?php if ($isUserLoggedIn) : ?>
          <span style="font-size: 14px; font-weight: 600; color: #1e293b;">Halo, <?= htmlspecialchars(explode(' ', trim($userName))[0]); ?></span>
        <?php else : ?>
          <a href="login_user.php"><i class="fa-regular fa-user"></i></a>
        <?php endif; ?>
        <a href="#"><i class="fa-regular fa-heart"></i></a>
        <a href="cart.php" class="cart-icon">
          <i class="fa-solid fa-bag-shopping"></i>
        </a>
      </div>
    </div>
  </header>

  <main class="main-content" style="max-width: 1200px; margin: 40px auto; padding: 0 16px;">
    <div style="background: #ffffff; padding: 40px; border-radius: 20px; border: 1px solid #f1f5f9; text-align: center; margin-bottom: 40px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
      <h1 style="font-size: 2.2rem; font-weight: 800; color: #0f172a; margin-bottom: 16px;">Tentang Nijbook</h1>
      <p style="color: #475569; line-height: 1.8; max-width: 800px; margin: 0 auto 24px; font-size: 1rem;">
        Nijbook adalah toko buku online terpercaya yang didirikan untuk mendekatkan literasi kepada semua orang. Kami menyediakan berbagai macam jenis buku, mulai dari novel fiksi, buku akademik, pengembangan diri, hingga komik dari berbagai penerbit terkemuka.
      </p>
      
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-top: 32px; text-align: left;">
        <div style="background: #f8fafc; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
          <i class="fa-solid fa-bullseye" style="font-size: 1.5rem; color: #2563eb; margin-bottom: 12px;"></i>
          <h3 style="font-size: 1.1rem; margin-bottom: 8px; color: #0f172a;">Visi Kami</h3>
          <p style="font-size: 0.85rem; color: #64748b; line-height: 1.5;">Menjadi platform toko buku online pilihan utama yang menginspirasi masyarakat Indonesia lewat bacaan berkualitas.</p>
        </div>

        <div style="background: #f8fafc; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
          <i class="fa-solid fa-rocket" style="font-size: 1.5rem; color: #2563eb; margin-bottom: 12px;"></i>
          <h3 style="font-size: 1.1rem; margin-bottom: 8px; color: #0f172a;">Misi Kami</h3>
          <p style="font-size: 0.85rem; color: #64748b; line-height: 1.5;">Memberikan kemudahan akses buku original dengan harga terjangkau, layanan cepat, dan garansi transaksi aman.</p>
        </div>

        <div style="background: #f8fafc; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
          <i class="fa-solid fa-heart" style="font-size: 1.5rem; color: #2563eb; margin-bottom: 12px;"></i>
          <h3 style="font-size: 1.1rem; margin-bottom: 8px; color: #0f172a;">Layanan Terbaik</h3>
          <p style="font-size: 0.85rem; color: #64748b; line-height: 1.5;">Dukungan pelanggan 24/7, pengiriman ke seluruh Indonesia, dan proses pengembalian yang sangat mudah.</p>
        </div>
      </div>
    </div>
  </main>

  <footer style="background-color: #020617; color: #94a3b8; padding: 48px 0 24px; margin-top: 60px; width: 100%;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 16px;">
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 32px; text-align: left;">
        <div>
          <a href="home.php" style="display: inline-flex; align-items: center; gap: 8px; font-size: 20px; font-weight: 800; color: #2563eb; text-decoration: none; margin-bottom: 12px;">
            <i class="fa-solid fa-book-open"></i>
            <span style="color: #ffffff;">Nijbook</span>
          </a>
          <p style="font-size: 12px; color: #94a3b8; line-height: 1.6; max-width: 260px;">
            Nijbook adalah toko buku online terpercaya yang menyediakan ribuan buku terbaik untuk menginspirasi harimu.
          </p>
        </div>

        <div>
          <h4 style="color: #ffffff; font-size: 14px; font-weight: 700; margin-bottom: 16px;">Toko</h4>
          <ul style="list-style: none; padding: 0; margin: 0; font-size: 12px;">
            <li style="margin-bottom: 10px;"><a href="home.php" style="color: #94a3b8; text-decoration: none;">Beranda</a></li>
            <li style="margin-bottom: 10px;"><a href="kategori.php" style="color: #94a3b8; text-decoration: none;">Kategori</a></li>
            <li style="margin-bottom: 10px;"><a href="home.php#popular-books" style="color: #94a3b8; text-decoration: none;">Buku</a></li>
          </ul>
        </div>

        <div>
          <h4 style="color: #ffffff; font-size: 14px; font-weight: 700; margin-bottom: 16px;">Informasi</h4>
          <ul style="list-style: none; padding: 0; margin: 0; font-size: 12px;">
            <li style="margin-bottom: 10px;"><a href="about.php" style="color: #94a3b8; text-decoration: none;">Tentang Kami</a></li>
            <li style="margin-bottom: 10px;"><a href="#" style="color: #94a3b8; text-decoration: none;">FAQ</a></li>
          </ul>
        </div>

        <div>
          <h4 style="color: #ffffff; font-size: 14px; font-weight: 700; margin-bottom: 16px;">Kontak</h4>
          <p style="font-size: 12px; color: #94a3b8; margin: 0;"><strong>WhatsApp:</strong> +62 889-0504-7384</p>
        </div>
      </div>

      <div style="border-top: 1px solid #1e293b; padding-top: 24px; margin-top: 40px; text-align: center; font-size: 12px; color: #64748b;">
        <p>&copy; 2026 Nijbook Bookstore. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <?php include 'live_chat.php'; ?>

</body>
</html>