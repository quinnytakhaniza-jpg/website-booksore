<?php
  // Mulai session
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  require_once 'function.php';
  require_once 'koneksi.php';

  // Cek apakah yang sedang login adalah user.
  $isUserLoggedIn = (isset($_SESSION['login']) && $_SESSION['login'] === true && isset($_SESSION['role']) && $_SESSION['role'] === 'user');
  $userName  = $_SESSION['nama'] ?? 'User';
  $userEmail = $_SESSION['email'] ?? '';
  $userInitial = strtoupper(substr(trim($userName), 0, 1));

  // Hitung jumlah buku yang ada di keranjang & wishlist user.
  $cartCount = 0; $wishlistCount = 0;
  if ($isUserLoggedIn && isset($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
    
    $cartQuery = $conn->prepare("SELECT COALESCE(SUM(jumlah), 0) FROM cart WHERE user_id = ?");
    $cartQuery->execute([$userId]);
    $cartCount = (int)$cartQuery->fetchColumn();

    $wishQuery = $conn->prepare("SELECT COUNT(w.id) FROM wishlist w JOIN books b ON w.buku_id = b.id WHERE w.user_id = ?");
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
  <title>Nijbook - Toko Buku Online</title>
  
  <link rel="stylesheet" href="home.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

  <!-- PROMO BANNER TOP -->
  <div class="top-banner" id="promoBanner">
    <div class="banner-content">
      <span>📦 🔥 <strong>Diskon hingga 50%</strong> untuk member baru!</span>
      <a href="#" class="btn-banner">Belanja Sekarang</a>
    </div>
    <button class="close-banner-btn" id="closeBannerBtn"><i class="fa-solid fa-xmark"></i></button>
  </div>

  <!-- NAVBAR / HEADER -->
  <header class="navbar-wrapper">
    <div class="container navbar">
      
      <!-- Brand Logo -->
      <a href="home.php" class="brand-logo">
        <i class="fa-solid fa-book-open"></i>
        <span>Nijbook</span>
      </a>

      <!-- Nav Links -->
      <nav class="nav-links">
        <a href="home.php" class="nav-item active">Beranda</a>
        <a href="#categories" class="nav-item">Kategori</a>
        <a href="#popular-books" class="nav-item">Buku</a>
        <a href="about.php" class="nav-item">About Us</a>
        <a href="kontak.php" class="nav-item">Kontak</a>
      </nav>
      
      <!-- Search Bar -->
      <form action="pencarian.php" method="GET" class="search-box">
        <input type="text" name="q" placeholder="Cari buku, penulis, atau genre..." required>
        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
      </form>

      <!-- Nav Icons & User Menu -->
      <div class="nav-icons">
        <div class="navbar-user-menu">
          <?php if ($isUserLoggedIn) : ?>
            <button type="button" class="navbar-user-button" id="navbarUserButton" style="display: flex; align-items: center; gap: 6px; font-weight: 600; padding: 0 10px; background: none; border: none; cursor: pointer; color: #1e293b; white-space: nowrap; flex-shrink: 0; width: max-content;">
              <i class="fa-regular fa-user" style="font-size: 1.1rem;"></i> 
              <span style="font-size: 14px;">Halo, <?= htmlspecialchars(explode(' ', trim($userName))[0]); ?></span>
            </button>
            <div class="navbar-user-dropdown" id="navbarUserDropdown">
              <div class="navbar-user-info">
                <div class="navbar-user-avatar"><?= htmlspecialchars($userInitial); ?></div>
                <div class="navbar-user-text">
                  <strong><?= htmlspecialchars($userName); ?></strong>
                  <span><?= htmlspecialchars($userEmail); ?></span>
                </div>
              </div>
              <div class="navbar-user-divider"></div>
              <a href="profile.php" class="navbar-user-link"><i class="fa-regular fa-user"></i><span>Profil Saya</span></a>
              <a href="logout.php" class="navbar-user-link navbar-logout-link"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Logout</span></a>
            </div>
          <?php else : ?>
            <a href="login_user.php" class="navbar-login-link"><i class="fa-regular fa-user"></i></a>
          <?php endif; ?>
        </div>
        
        <!-- Ikon Wishlist -->
        <a href="<?= $isUserLoggedIn ? 'wishlist.php' : 'login_user.php'; ?>" class="cart-icon" id="wishlistNavIcon" aria-label="Wishlist">
          <i class="fa-regular fa-heart"></i>
          <?php if ($wishlistCount > 0) : ?><span class="badge" style="background-color: #be185d;"><?= $wishlistCount; ?></span><?php endif; ?>
        </a>
        
        <!-- Ikon Keranjang -->
        <a href="<?= htmlspecialchars($cartLink); ?>" class="cart-icon" aria-label="Keranjang belanja">
          <i class="fa-solid fa-bag-shopping"></i>
          <?php if ($cartCount > 0) : ?><span class="badge"><?= $cartCount; ?></span><?php endif; ?>
        </a>
      </div>

    </div>
  </header>

  <!-- HERO SECTION -->
  <div class="hero-section">
    <div class="hero-bg"></div>
    <div class="container hero-container">
      <div class="hero-text">
        <h1>Find Your <br>Next Favorite <br><span class="highlight">Book.</span></h1>
        <p>Temukan ribuan buku pilihan dari berbagai genre terbaik untuk menemani harimu.</p>
        <div class="hero-buttons">
          <a href="#popular-books" class="btn btn-primary">Jelajahi Sekarang</a>
          <a href="#categories" class="btn btn-outline">Lihat Kategori</a>
        </div>
      </div>
      <div class="hero-card-wrapper">
        <div class="book-card-featured">
          <div class="tag tag-blue">Bestseller</div>
          <button class="wishlist-btn"><i class="fa-regular fa-heart"></i></button>
          <img src="image/Atomic Habits.jpg" alt="Atomic Habits">
          <div class="featured-info">
            <h3>Atomic Habits</h3>
            <p class="author">James Clear</p>
            <div class="stars">
              <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
              <span>(4.9)</span>
            </div>
            <div class="price">Rp98.000</div>
            <button class="btn btn-primary btn-full"><i class="fa-solid fa-cart-shopping"></i> Beli Sekarang</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="stats-bar">
      <div class="stat-item"><i class="fa-solid fa-book-open"></i><div><h4>20K+</h4><p>Buku Tersedia</p></div></div>
      <div class="stat-item"><i class="fa-solid fa-landmark"></i><div><h4>150+</h4><p>Penerbit</p></div></div>
      <div class="stat-item"><i class="fa-regular fa-star"></i><div><h4>4.9★</h4><p>Rating Toko</p></div></div>
    </div>
  </div>

  <!-- EXPLORE CATEGORIES -->
  <div class="container section" id="categories">
    <div class="section-header">
      <h2>Explore Categories</h2>
    </div>
    <div class="categories-grid">
      <a href="kategori.php?cat=teen" class="cat-card cat-blue"><i class="fa-solid fa-book"></i><h3>Teen & Young Adult Fiction</h3></a>
      <a href="kategori.php?cat=fiksi" class="cat-card cat-amber"><i class="fa-solid fa-chart-line"></i><h3>Adult Fiction</h3></a>
      <a href="kategori.php?cat=pengembangan-diri" class="cat-card cat-emerald"><i class="fa-solid fa-seedling"></i><h3>Motivation & Self-Help</h3></a>
      <a href="kategori.php?cat=bisnis" class="cat-card cat-purple"><i class="fa-solid fa-hat-wizard"></i><h3>Business & Investing</h3></a>
    </div>
  </div>

  <!-- POPULAR BOOKS -->
  <div class="container section" id="popular-books">
    <h2>Popular Books</h2>
    <div class="filter-wrapper">
      <div class="filter-tabs">
        <button type="button" class="tab-btn active" data-category="semua">Semua</button>
        <button type="button" class="tab-btn" data-category="fiksi">Fiksi</button>
        <button type="button" class="tab-btn" data-category="nonfiksi">Non-Fiksi</button>
        <button type="button" class="tab-btn" data-category="fantasi">Fantasi</button>
        <button type="button" class="tab-btn" data-category="bisnis">Bisnis</button>
        <button type="button" class="tab-btn" data-category="pengembangan">Pengembangan Diri</button>
        <button type="button" class="tab-btn" data-category="romance">Romance</button>
      </div>
      <div class="mini-search">
        <input type="text" id="bookSearchInput" placeholder="Cari buku...">
        <i class="fa-solid fa-magnifying-glass"></i>
      </div>
    </div>

    <div class="books-grid" id="booksGrid">
      <?php
      $booksQuery = $conn->query("SELECT * FROM books WHERE is_popular = 1 ORDER BY id DESC");
      $booksData = $booksQuery->fetchAll(PDO::FETCH_ASSOC);

      foreach ($booksData as $b): 
        $rawCat = strtolower(trim($b['kategori']));
        
        if (str_contains($rawCat, 'semua')) { $catFilter = 'semua'; } 
        elseif (str_contains($rawCat, 'non-fiksi') || str_contains($rawCat, 'nonfiksi')) { $catFilter = 'nonfiksi'; } 
        elseif (str_contains($rawCat, 'fiksi') || str_contains($rawCat, 'novel') || str_contains($rawCat, 'adult fiction')) { $catFilter = 'fiksi'; } 
        elseif (str_contains($rawCat, 'fantasi')) { $catFilter = 'fantasi'; } 
        elseif (str_contains($rawCat, 'bisnis') || str_contains($rawCat, 'business')) { $catFilter = 'bisnis'; } 
        elseif (str_contains($rawCat, 'pengembangan') || str_contains($rawCat, 'self-help')) { $catFilter = 'pengembangan'; } 
        elseif (str_contains($rawCat, 'romance') || str_contains($rawCat, 'romantis')) { $catFilter = 'romance'; } 
        else { $catFilter = 'lainnya'; }
        
        $img = !empty($b['gambar']) ? 'image/' . htmlspecialchars($b['gambar']) : 'image/placeholder.jpg';
      ?>
      <a href="deskripsi.php?id=<?= $b['id']; ?>" class="book-card" data-category="<?= $catFilter; ?>">
        <?php if($b['stok'] > 0): ?><span class="tag tag-emerald">Stok: <?= $b['stok']; ?></span>
       <?php else: ?><span class="tag tag-red">Habis</span><?php endif; ?>
        
        <img src="<?= $img; ?>" alt="<?= htmlspecialchars($b['judul']); ?>">
        <div class="book-card-body">
          <h3><?= htmlspecialchars($b['judul']); ?></h3>
          <p class="author"><?= htmlspecialchars($b['penulis'] ?? $b['kategori']); ?></p>
          <div class="stars"><i class="fa-solid fa-star"></i> <span>(4.9)</span></div>
          <div class="card-footer">
            <span class="price"><?= "Rp" . number_format((float)$b['harga'], 0, ",", "."); ?></span>
            <button type="button" class="heart-icon" data-id="<?= $b['id']; ?>">
              <i class="fa-regular fa-heart"></i>
            </button>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- CLUB BANNER SECTION -->
  <div class="container section">
    <div class="club-banner">
      <div class="club-text">
        <h2>Gabung Nijbook Club</h2>
        <p>Nikmati berbagai keuntungan eksklusif hanya untuk member Remora.</p>
        <button class="btn btn-white">Gabung Sekarang</button>
      </div>
      <div class="club-features">
        <div class="feature-item"><i class="fa-solid fa-truck-fast"></i><h4>Gratis Ongkir</h4><p>Gratis ongkir untuk setiap pembelian</p></div>
        <div class="feature-item"><i class="fa-solid fa-gift"></i><h4>Voucher Eksklusif</h4><p>Dapatkan voucher diskon spesial</p></div>
        <div class="feature-item"><i class="fa-regular fa-clock"></i><h4>Akses Lebih Awal</h4><p>Akses lebih awal untuk buku terbaru</p></div>
        <div class="feature-item"><i class="fa-regular fa-star"></i><h4>Poin Member</h4><p>Kumpulkan poin dan tukar hadiah</p></div>
      </div>
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

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // 1. FILTER CATEGORY & SEARCH
      const tabButtons = document.querySelectorAll('.tab-btn');
      const bookCards = document.querySelectorAll('.book-card');
      const searchInput = document.getElementById('bookSearchInput');

      let currentCategory = 'semua'; 

      function filterBooks() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';

        bookCards.forEach(card => {
          const cardCategory = (card.getAttribute('data-category') || '').toLowerCase();
          const titleElement = card.querySelector('h3');
          const cardTitle = titleElement ? titleElement.textContent.toLowerCase() : '';

          const matchesCategory = (currentCategory === 'semua' || cardCategory === currentCategory);
          const matchesSearch = cardTitle.includes(searchTerm);

          if (matchesCategory && matchesSearch) {
            card.style.display = 'flex';
          } else {
            card.style.display = 'none';
          }
        });
      }

      tabButtons.forEach(button => {
        button.addEventListener('click', function () {
          tabButtons.forEach(btn => btn.classList.remove('active'));
          this.classList.add('active');

          currentCategory = this.getAttribute('data-category').toLowerCase();
          filterBooks();
        });
      });

      if (searchInput) {
        searchInput.addEventListener('input', filterBooks);
      }
      
      filterBooks();

      // 2. USER DROPDOWN TOGGLE
      const userButton = document.getElementById('navbarUserButton');
      const userDropdown = document.getElementById('navbarUserDropdown');
      if (userButton && userDropdown) {
        userButton.addEventListener('click', function (e) {
          e.stopPropagation();
          userDropdown.classList.toggle('is-open');
        });
        document.addEventListener('click', function () {
          userDropdown.classList.remove('is-open');
        });
      }

      // 3. PROMO BANNER CLOSE
      const promoBanner = document.getElementById('promoBanner');
      const closeBannerBtn = document.getElementById('closeBannerBtn');
      if (closeBannerBtn && promoBanner) {
        closeBannerBtn.addEventListener('click', function () {
          promoBanner.style.display = 'none';
        });
      }

      // 4. ANIMASI WISHLIST TERBANG (FLYING HEART)
      const heartButtons = document.querySelectorAll('.heart-icon');
      const wishlistNavIcon = document.getElementById('wishlistNavIcon');

      heartButtons.forEach(button => {
        button.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();

          const bookId = this.getAttribute('data-id');
          if (!bookId) return;

          const rectBtn = this.getBoundingClientRect();
          const rectNav = wishlistNavIcon ? wishlistNavIcon.getBoundingClientRect() : { top: 20, left: window.innerWidth - 100 };

          const flyingHeart = document.createElement('i');
          flyingHeart.className = 'fa-solid fa-heart flying-heart';
          flyingHeart.style.top = rectBtn.top + 'px';
          flyingHeart.style.left = rectBtn.left + 'px';
          flyingHeart.style.setProperty('--target-top', rectNav.top + 'px');
          flyingHeart.style.setProperty('--target-left', rectNav.left + 'px');

          document.body.appendChild(flyingHeart);

          const iconInside = this.querySelector('i');
          if (iconInside) {
            iconInside.classList.toggle('fa-solid');
            iconInside.classList.toggle('fa-regular');
            this.style.color = '#ef4444';
          }

          fetch('tambah_wishlist.php?id=' + bookId)
            .then(() => {
              setTimeout(() => {
                flyingHeart.remove();

                if (wishlistNavIcon) {
                  wishlistNavIcon.classList.add('heart-bounce');
                  setTimeout(() => wishlistNavIcon.classList.remove('heart-bounce'), 400);

                  let badge = wishlistNavIcon.querySelector('.badge');
                  if (badge) {
                    let count = parseInt(badge.innerText) || 0;
                    badge.innerText = count + 1;
                  } else {
                    const newBadge = document.createElement('span');
                    newBadge.className = 'badge';
                    newBadge.style.backgroundColor = '#be185d';
                    newBadge.innerText = '1';
                    wishlistNavIcon.appendChild(newBadge);
                  }
                }
              }, 750);
            })
            .catch(err => console.error(err));
        });
      });
    });
  </script>

  <!-- WIDGET LIVE CHAT CUSTOMER SERVICE -->
  <?php if ($isUserLoggedIn) : ?>
    <?php include 'live_chat.php'; ?>
  <?php endif; ?>

</body>
</html>