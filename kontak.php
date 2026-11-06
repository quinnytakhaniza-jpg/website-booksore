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
  <title>Kontak - Nijbook</title>
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
        <a href="about.php">About Us</a>
        <a href="kontak.php" class="active">Kontak</a>
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

  <main class="main-content" style="max-width: 800px; margin: 40px auto; padding: 0 16px;">
    <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); text-align: center;">
      <div style="width: 56px; height: 56px; border-radius: 50%; background-color: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 24px;">
        <i class="fa-solid fa-headset"></i>
      </div>
      
      <h2 style="font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">Pusat Bantuan & Kontak</h2>
      <p style="font-size: 14px; color: #64748b; margin-bottom: 32px; max-width: 450px; margin-left: auto; margin-right: auto;">
        Butuh bantuan seputar pemesanan buku? Hubungi tim Customer Service kami melalui informasi di bawah ini atau gunakan widget chat yang tersedia.
      </p>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; text-align: left; margin-bottom: 32px;">
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; display: flex; gap: 14px; align-items: flex-start;">
          <div style="width: 36px; height: 36px; border-radius: 10px; background-color: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="fa-brands fa-whatsapp"></i>
          </div>
          <div>
            <span style="font-size: 11px; color: #64748b; font-weight: 600; display: block;">WhatsApp CS</span>
            <strong style="font-size: 13px; color: #1e293b;">+62 889-0504-7384</strong>
          </div>
        </div>

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; display: flex; gap: 14px; align-items: flex-start;">
          <div style="width: 36px; height: 36px; border-radius: 10px; background-color: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="fa-regular fa-clock"></i>
          </div>
          <div>
            <span style="font-size: 11px; color: #64748b; font-weight: 600; display: block;">Jam Operasional</span>
            <strong style="font-size: 13px; color: #1e293b;">Senin - Jumat, 08.00 - 18.00</strong>
          </div>
        </div>
      </div>

      <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; display: flex; gap: 14px; align-items: flex-start; text-align: left;">
        <div style="width: 36px; height: 36px; border-radius: 10px; background-color: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
          <i class="fa-solid fa-location-dot"></i>
        </div>
        <div>
          <span style="font-size: 11px; color: #64748b; font-weight: 600; display: block;">Alamat Kantor</span>
          <strong style="font-size: 13px; color: #1e293b; line-height: 1.4; display: block;">Jl. Literasi No. 123<br>Jakarta Selatan, DKI Jakarta 12510</strong>
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