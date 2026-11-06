<?php 
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
  require_once 'koneksi.php';
  require_once 'function.php';
  
  $catKey = isset($_GET['cat']) ? $_GET['cat'] : 'fiksi';

  $dbCategories = [];
  $catTitle = 'Kategori';
  $catDesc = 'Temukan buku pilihanmu';

  if ($catKey === 'teen') {
      $dbCategories = ['Teen & Young Adult Fiction'];
      $catTitle = 'Teen & Young Adult Fiction';
      $catDesc = 'Temukan dunia imajinasi tanpa batas untuk remaja dan dewasa muda';
  } elseif ($catKey === 'fiksi' || $catKey === 'adult') {
      $dbCategories = ['Adult Fiction', 'Fiksi', 'Novel'];
      $catTitle = 'Adult Fiction / Fiksi';
      $catDesc = 'Buku fiksi mendalam untuk pembaca';
  } elseif ($catKey === 'pengembangan-diri' || $catKey === 'self-help') {
      $dbCategories = ['Motivation & Self-Help', 'Pengembangan Diri'];
      $catTitle = 'Motivation & Self-Help';
      $catDesc = 'Tingkatkan potensi diri dan pola pikir positif';
  } elseif ($catKey === 'bisnis' || $catKey === 'business') {
      $dbCategories = ['Business & Investing', 'Bisnis'];
      $catTitle = 'Business & Investing';
      $catDesc = 'Panduan finansial, bisnis, dan strategi investasi';
  } else {
      $dbCategories = [urldecode($catKey)];
      $catTitle = ucfirst($catKey);
      $catDesc = 'Buku pilihan terbaik untukmu';
  }

  $inPlaceholders = str_repeat('?,', count($dbCategories) - 1) . '?';
  
  // Mengambil jumlah total buku (untuk info di banner)
  $stmtCount = $conn->prepare("SELECT COUNT(id) FROM books WHERE kategori IN ($inPlaceholders)");
  $stmtCount->execute($dbCategories);
  $totalBooks = (int) $stmtCount->fetchColumn();

  // MENGHILANGKAN BATAS HALAMAN (Menampilkan semua buku sekaligus)
  $stmtBooks = $conn->prepare("SELECT * FROM books WHERE kategori IN ($inPlaceholders) ORDER BY id DESC");
  $stmtBooks->execute($dbCategories);
  $displayBooks = $stmtBooks->fetchAll(PDO::FETCH_ASSOC);

  $isUserLoggedIn = (isset($_SESSION['login']) && $_SESSION['login'] === true && isset($_SESSION['role']) && $_SESSION['role'] === 'user');
  $cartCount = 0; $wishlistCount = 0;
  if ($isUserLoggedIn && isset($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
    $cartQuery = $conn->prepare("SELECT COALESCE(SUM(jumlah), 0) FROM cart WHERE user_id = ?");
    $cartQuery->execute([$userId]);
    $cartCount = (int) $cartQuery->fetchColumn();
    $wishQuery = $conn->prepare("SELECT COUNT(id) FROM wishlist WHERE user_id = ?");
    $wishQuery->execute([$userId]);
    $wishlistCount = (int)$wishQuery->fetchColumn();
  }
  $cartLink = $isUserLoggedIn ? 'cart.php' : 'login_user.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($catTitle); ?> - Nijbook</title>
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="home.css">

  <style>
    .cat-detail-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    .btn-back { background: #eff6ff; color: #2563eb; padding: 8px 16px; border-radius: 20px; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
    .btn-back:hover { background: #dbeafe; }
    .cat-banner { background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-radius: 24px; padding: 32px; display: flex; align-items: center; gap: 24px; margin-bottom: 24px; }
    .banner-icon-circle { width: 72px; height: 72px; background: #2563eb; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; }
    .banner-info h1 { font-size: 28px; font-weight: 800; color: #0f172a; }
    .banner-info p { color: #475569; font-size: 14px; margin: 4px 0 8px; }
    .banner-count { color: #2563eb; font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 6px; }
  </style>
</head>
<body>

  <!-- NAVBAR / HEADER -->
  <div class="navbar-wrapper">
    <div class="container navbar">
      <a href="home.php" class="brand-logo"><i class="fa-solid fa-book-open"></i><span>Nijbook</span></a>
      <div class="nav-links">
        <a href="home.php" class="nav-item">Beranda</a>
        <a href="kategori.php" class="nav-item active">Kategori</a>
        <a href="home.php#popular-books" class="nav-item">Buku</a>
        <a href="about.php" class="nav-item">About Us</a>
        <a href="kontak.php" class="nav-item">Kontak</a>
      </div>
      <div class="search-box">
        <input type="text" placeholder="Cari buku, penulis, atau genre...">
        <button><i class="fa-solid fa-magnifying-glass"></i></button>
      </div>
      <div class="nav-icons">
        <a href="<?= $isUserLoggedIn ? 'profile.php' : 'login_user.php'; ?>"><i class="fa-regular fa-user"></i></a>
        <a href="#" class="cart-icon"><i class="fa-regular fa-heart"></i>
          <?php if ($wishlistCount > 0): ?><span class="badge" style="background-color: #be185d;"><?= $wishlistCount; ?></span><?php endif; ?>
        </a>
        <a href="<?= htmlspecialchars($cartLink); ?>" class="cart-icon"><i class="fa-solid fa-bag-shopping"></i>
          <?php if ($cartCount > 0): ?><span class="badge"><?= $cartCount; ?></span><?php endif; ?>
        </a>
      </div>
    </div>
  </div>

  <!-- KONTEN UTAMA KATEGORI -->
  <div class="container section">

    <div class="cat-detail-header">
      <a href="home.php#categories" class="btn-back"><i class="fa-solid fa-chevron-left"></i> Kembali</a>
      <h2 style="font-size: 20px; font-weight: 700;">Kategori</h2>
      <div style="width: 80px;"></div>
    </div>

    <div class="cat-banner">
      <div class="banner-icon-circle"><i class="fa-solid fa-book-open"></i></div>
      <div class="banner-info">
        <h1><?= htmlspecialchars($catTitle); ?></h1>
        <p><?= htmlspecialchars($catDesc); ?></p>
        <span class="banner-count"><i class="fa-solid fa-book"></i> <?= $totalBooks; ?> buku tersedia</span>
      </div>
    </div>

    <!-- Grid Buku Dinamis -->
    <div class="books-grid">
      <?php if (!empty($displayBooks)): ?>
        <?php foreach ($displayBooks as $b): 
          $img = !empty($b['gambar']) ? 'image/' . htmlspecialchars($b['gambar']) : 'image/placeholder.jpg';
        ?>
          <a href="deskripsi.php?id=<?= $b['id']; ?>" class="book-card">
            <?php if($b['stok'] > 0): ?><span class="tag tag-emerald">Stok: <?= $b['stok']; ?></span>
            <?php else: ?><span class="tag tag-red">Habis</span><?php endif; ?>
            
            <img src="<?= $img; ?>" alt="<?= htmlspecialchars($b['judul']); ?>">
            <div class="book-card-body">
              <h3><?= htmlspecialchars($b['judul']); ?></h3>
              <p class="author"><?= htmlspecialchars($b['penulis'] ?? $b['kategori']); ?></p>
              <div class="stars"><i class="fa-solid fa-star"></i> <span>(4.9)</span></div>
              <div class="card-footer">
                <span class="price"><?= "Rp" . number_format((float)$b['harga'], 0, ",", "."); ?></span>
                <button type="button" class="heart-icon"><i class="fa-regular fa-heart"></i></button>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="grid-column: 1 / -1; text-align: center; color: #64748b; padding: 40px; border: 1px dashed #cbd5e1; border-radius: 12px;">Belum ada buku untuk kategori ini.</p>
      <?php endif; ?>
    </div>

  </div>

   <!-- FOOTER -->
  <div class="footer">
    <div class="container footer-grid">
      <div class="footer-brand">
        <a href="home.php" class="brand-logo white"><i class="fa-solid fa-book-open"></i><span>Nijbook</span></a>
        <p>Nijbook adalah toko buku online terpercaya yang menyediakan ribuan buku terbaik untuk menginspirasi harimu.</p>
      </div>
      <div class="footer-links">
        <h4>Toko</h4>
        <ul>
          <li><a href="home.php">Beranda</a></li>
          <li><a href="#categories">Kategori</a></li>
          <li><a href="#popular-books">Buku</a></li>
        </ul>
      </div>
      <div class="footer-links">
        <h4>Informasi</h4>
        <ul>
          <li><a href="about.php">Tentang Kami</a></li>
          <li><a href="#">FAQ</a></li>
        </ul>
      </div>
      <div class="footer-links">
        <h4>Kontak</h4>
        <p class="contact-text"><strong>WhatsApp:</strong> +62 889-0504-7384</p>
      </div>
    </div>
    <div class="footer-bottom"><p>© 2024 Nijbook Bookstore. All rights reserved.</p></div>
  </div>

</body>
</html>