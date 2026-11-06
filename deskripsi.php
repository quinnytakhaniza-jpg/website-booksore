<?php
session_start();
require_once "koneksi.php";

$isUserLoggedIn = (
    isset($_SESSION["login"]) &&
    $_SESSION["login"] === true &&
    ($_SESSION["role"] ?? "") === "user"
);

$bukuId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$bukuId) {
    $bukuId = 1;
}

// AMBIL DATA BUKU DARI DATABASE
$stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$bukuId]);
$buku = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika buku tidak ditemukan, alihkan atau default ke buku pertama
if (!$buku) {
    $fallbackStmt = $conn->query("SELECT * FROM books ORDER BY id ASC LIMIT 1");
    $buku = $fallbackStmt->fetch(PDO::FETCH_ASSOC);
    $bukuId = $buku ? (int)$buku["id"] : 1;
}

$cartCount = 0;
$wishlistCount = 0;

if ($isUserLoggedIn && isset($_SESSION["user_id"])) {
    $userId = (int)$_SESSION["user_id"];

    // Hitung Cart
    $countQuery = $conn->prepare("
        SELECT COALESCE(SUM(jumlah), 0)
        FROM cart
        WHERE user_id = ?
    ");
    $countQuery->execute([$userId]);
    $cartCount = (int)$countQuery->fetchColumn();

    // Hitung Wishlist
    $wishQuery = $conn->prepare("SELECT COUNT(id) FROM wishlist WHERE user_id = ?");
    $wishQuery->execute([$userId]);
    $wishlistCount = (int)$wishQuery->fetchColumn();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!$isUserLoggedIn) {
        header("Location: login_user.php");
        exit;
    }

    $action = $_POST["action"] ?? "";
    $jumlah = filter_input(INPUT_POST, "jumlah", FILTER_VALIDATE_INT);

    if (!$jumlah || $jumlah < 1) {
        $jumlah = 1;
    }

    $jumlah = min($jumlah, (int)($buku["stok"] ?? 1));

    if (in_array($action, ["add_cart", "buy_now"], true)) {
        $userId = (int)$_SESSION["user_id"];

        $check = $conn->prepare("
            SELECT id, jumlah
            FROM cart
            WHERE user_id = ? AND buku_id = ?
            LIMIT 1
        ");
        $check->execute([$userId, $bukuId]);
        $existing = $check->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $newQty = min(
                (int)$buku["stok"],
                ((int)$existing["jumlah"]) + $jumlah
            );

            $update = $conn->prepare("
                UPDATE cart
                SET jumlah = ?
                WHERE id = ? AND user_id = ?
            ");
            $update->execute([
                $newQty,
                (int)$existing["id"],
                $userId
            ]);
        } else {
            $insert = $conn->prepare("
                INSERT INTO cart
                    (user_id, buku_id, jumlah)
                VALUES
                    (?, ?, ?)
            ");
            $insert->execute([
                $userId,
                $bukuId,
                $jumlah
            ]);
        }

        if ($action === "buy_now") {
            header("Location: cart.php");
            exit;
        }

        header("Location: deskripsi.php?id=" . $bukuId . "&cart=success");
        exit;
    } elseif ($action === "add_wishlist") {
        $userId = (int)$_SESSION["user_id"];
        
        $checkW = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND buku_id = ?");
        $checkW->execute([$userId, $bukuId]);
        if (!$checkW->fetch()) {
            $insertW = $conn->prepare("INSERT INTO wishlist (user_id, buku_id) VALUES (?, ?)");
            $insertW->execute([$userId, $bukuId]);
        }
        header("Location: deskripsi.php?id=" . $bukuId . "&wishlist=success");
        exit;
    }
}

function rupiah($angka) {
    return "Rp" . number_format((float)$angka, 0, ",", ".");
}

// Fungsi pengaman huruf besar/kecil dari database
function getDbVal($array, $key1, $key2, $default = "-") {
    if (!empty($array[$key1])) return $array[$key1];
    if (!empty($array[$key2])) return $array[$key2];
    return $default;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($buku["judul"] ?? "Detail Buku"); ?> - Nijbook</title>
  <link rel="stylesheet" href="deskripsi.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    /* Tambahan CSS khusus agar scrollbar deskripsi terlihat rapi */
    .desc-scroll-box {
      max-height: 300px; /* Batas tinggi kotak deskripsi */
      overflow-y: auto;
      padding-right: 15px;
      line-height: 1.8;
      color: #475569;
    }
    
    /* Mempercantik bentuk scrollbar */
    .desc-scroll-box::-webkit-scrollbar {
      width: 6px;
    }
    .desc-scroll-box::-webkit-scrollbar-track {
      background: #f1f5f9; 
      border-radius: 4px;
    }
    .desc-scroll-box::-webkit-scrollbar-thumb {
      background: #cbd5e1; 
      border-radius: 4px;
    }
    .desc-scroll-box::-webkit-scrollbar-thumb:hover {
      background: #94a3b8; 
    }
  </style>
</head>
<body>

  <!-- TOP ANNOUNCEMENT BANNER -->
  <div class="top-banner">
    <span>📦 🔥 <strong>Diskon hingga 50%</strong> untuk member baru!</span>
    <a href="#" class="btn-top-banner">Belanja Sekarang</a>
    <span class="close-banner">&times;</span>
  </div>

  <!-- NAVBAR / HEADER -->
  <header class="navbar-wrapper">
    <div class="navbar-container">
      <a href="home.php" class="brand-logo">
        <i class="fa-solid fa-book-open"></i>
        <span>Nijbook</span>
      </a>

      <nav class="nav-links">
        <a href="home.php">Beranda</a>
        <a href="#">Kategori</a>
        <a href="javascript:history.back()" class="active">Buku</a>
        <a href="about.php">About Us</a>
        <a href="kontak.php">Kontak</a>
      </nav>

      <div class="search-box">
        <input type="text" placeholder="Cari buku, penulis, atau genre...">
        <button type="button"><i class="fa-solid fa-magnifying-glass"></i></button>
      </div>

      <div class="nav-icons">
        <a href="#"><i class="fa-regular fa-user"></i></a>
        <a href="#" class="cart-icon">
          <i class="fa-regular fa-heart"></i>
          <?php if ($wishlistCount > 0): ?>
            <span class="cart-badge" style="background-color: #be185d;"><?= $wishlistCount; ?></span>
          <?php endif; ?>
        </a>
        <a href="<?= $isUserLoggedIn ? 'cart.php' : 'login_user.php'; ?>" class="cart-icon">
          <i class="fa-solid fa-bag-shopping"></i>
          <?php if ($cartCount > 0): ?>
            <span class="cart-badge"><?= $cartCount; ?></span>
          <?php endif; ?>
        </a>
      </div>
    </div>
  </header>

  <!-- MAIN WRAPPER -->
  <main class="main-content">

    <div class="breadcrumb">
      <a href="home.php">Beranda</a>
      <i class="fa-solid fa-chevron-right"></i>
      <a href="javascript:history.back()">Buku</a>
      <i class="fa-solid fa-chevron-right"></i>
      <a href="javascript:history.back()"><?= htmlspecialchars($buku["kategori"] ?? "Kategori"); ?></a>
      <i class="fa-solid fa-chevron-right"></i>
      <span class="current"><?= htmlspecialchars($buku["judul"] ?? ""); ?></span>
    </div>

    <!-- PRODUCT DETAIL MAIN CARD -->
    <div class="product-card">

      <!-- KOLOM 1: GAMBAR BUKU -->
      <div class="col-cover">
        <div class="cover-wrapper">
          <span class="badge-bestseller">BESTSELLER</span>
          <?php 
            $imgPath = !empty($buku['gambar']) ? 'image/' . htmlspecialchars($buku['gambar']) : 'image/placeholder.jpg';
          ?>
          <img src="<?= $imgPath; ?>" alt="<?= htmlspecialchars($buku["judul"] ?? ""); ?>">
        </div>
      </div>

      <!-- KOLOM 2: INFORMASI UTAMA & BELI -->
      <div class="col-main-info">

        <a href="javascript:history.back()" style="display: inline-flex; align-items: center; gap: 6px; color: #64748b; text-decoration: none; font-size: 0.85rem; font-weight: 600; margin-bottom: 12px; cursor: pointer;">
          <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
        <br>

        <span class="category-link"><?= htmlspecialchars($buku["kategori"] ?? ""); ?></span>
        <h1 class="book-title"><?= htmlspecialchars($buku["judul"] ?? ""); ?></h1>
        <p class="author-name"><?= htmlspecialchars(getDbVal($buku, 'penulis', 'Penulis', 'Penulis Tidak Diketahui')); ?></p>

        <div class="rating-row">
          <div class="stars">
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
          </div>
          <span class="score">4.8</span>
          <span class="reviews">(1.240 Ulasan)</span>
        </div>

        <div class="price-row">
          <span class="price-current"><?= rupiah($buku["harga"] ?? 0); ?></span>
          <span class="price-old">Rp130.000</span>
          <span class="badge-discount">Hemat 14%</span>
        </div>

        <div class="stock-info">
          <i class="fa-solid fa-check"></i> Stok tersedia (<?= (int)($buku["stok"] ?? 0); ?>)
        </div>

        <?php if (isset($_GET["cart"]) && $_GET["cart"] === "success"): ?>
          <div style="
            margin: 14px 0;
            padding: 12px 14px;
            border-radius: 10px;
            background: #ecfdf3;
            color: #15803d;
            border: 1px solid #bbf7d0;
            font-size: 14px;
            font-weight: 600;
          ">
            <i class="fa-solid fa-circle-check"></i>
            Buku berhasil ditambahkan ke keranjang.
            <a href="cart.php" style="color:#166534;font-weight:800;margin-left:6px;">
              Lihat Keranjang
            </a>
          </div>
        <?php endif; ?>
        
        <?php if (isset($_GET["wishlist"]) && $_GET["wishlist"] === "success"): ?>
          <div style="
            margin: 14px 0;
            padding: 12px 14px;
            border-radius: 10px;
            background: #fdf2f8;
            color: #be185d;
            border: 1px solid #fbcfe8;
            font-size: 14px;
            font-weight: 600;
          ">
            <i class="fa-solid fa-heart"></i>
            Buku berhasil ditambahkan ke Wishlist (Favorit).
          </div>
        <?php endif; ?>

        <form method="POST" action="deskripsi.php?id=<?= (int)$bukuId; ?>" id="orderForm">
          <div class="action-row">
            <div class="qty-control">
              <button type="button" id="qtyMinus">-</button>
              <input type="text" name="jumlah" id="qtyInput" value="1" readonly>
              <button type="button" id="qtyPlus">+</button>
            </div>
          </div>

          <div class="button-group">
            <button type="submit" name="action" value="add_cart" class="btn btn-cart">
              <i class="fa-solid fa-cart-shopping"></i> Tambah Keranjang
            </button>

            <button type="submit" name="action" value="buy_now" class="btn btn-buy">
              <i class="fa-solid fa-bolt"></i> Beli Sekarang
            </button>

            <button type="submit" name="action" value="add_wishlist" class="btn-wishlist">
              <i class="fa-regular fa-heart"></i>
            </button>
          </div>
        </form>

        <div class="features-grid">
          <div class="feature-item">
            <div class="icon-box"><i class="fa-solid fa-shield"></i></div>
            <div class="feature-text">
              <strong>100% Original</strong>
              <span>Buku asli & bergaransi</span>
            </div>
          </div>
          <div class="feature-item">
            <div class="icon-box"><i class="fa-solid fa-truck-fast"></i></div>
            <div class="feature-text">
              <strong>Pengiriman Cepat</strong>
              <span>Dikirim hari ini</span>
            </div>
          </div>
          <div class="feature-item">
            <div class="icon-box"><i class="fa-solid fa-rotate-left"></i></div>
            <div class="feature-text">
              <strong>Bisa Retur</strong>
              <span>7 hari setelah diterima</span>
            </div>
          </div>
          <div class="feature-item">
            <div class="icon-box"><i class="fa-solid fa-lock"></i></div>
            <div class="feature-text">
              <strong>Pembayaran Aman</strong>
              <span>Transaksi terjamin</span>
            </div>
          </div>
        </div>
      </div>

      <!-- KOLOM 3: TAB TABEL DETAIL & DESKRIPSI -->
      <div class="col-tabs-spec">
        <!-- HEADER TAB (Ditambahkan onclick) -->
        <div class="tab-header">
          <button class="tab-btn active" onclick="bukaTab('konten-deskripsi', this)">Deskripsi</button>
          <button class="tab-btn" onclick="bukaTab('konten-detail', this)">Detail Buku</button>
        </div>

        <div class="tab-body">
          
          <!-- TAB 1: DESKRIPSI -->
          <div id="konten-deskripsi" class="isi-tab" style="display: block;">
            <div class="desc-scroll-box">
              <p style="white-space: pre-line; margin:0;">
                <?= htmlspecialchars(getDbVal($buku, 'deskripsi', 'Deskripsi', 'Belum ada deskripsi untuk buku ini.')); ?>
              </p>
            </div>
          </div>

          <!-- TAB 2: DETAIL BUKU (TABEL) -->
          <div id="konten-detail" class="isi-tab" style="display: none;">
            <table class="specs-table">
              <tr>
                <td class="label">Penulis</td>
                <td class="colon">:</td>
                <td class="val"><?= htmlspecialchars(getDbVal($buku, 'penulis', 'Penulis')); ?></td>
              </tr>
              <tr>
                <td class="label">Penerbit</td>
                <td class="colon">:</td>
                <td class="val"><?= htmlspecialchars(getDbVal($buku, 'penerbit', 'Penerbit')); ?></td>
              </tr>
              <tr>
                <td class="label">ISBN</td>
                <td class="colon">:</td>
                <td class="val"><?= htmlspecialchars(getDbVal($buku, 'isbn', 'Isbn')); ?></td>
              </tr>
              <tr>
                <td class="label">Bahasa</td>
                <td class="colon">:</td>
                <td class="val"><?= htmlspecialchars(getDbVal($buku, 'bahasa', 'Bahasa', 'Indonesia')); ?></td>
              </tr>
              <tr>
                <td class="label">Jumlah Halaman</td>
                <td class="colon">:</td>
                <td class="val"><?= htmlspecialchars(getDbVal($buku, 'halaman', 'Halaman')); ?></td>
              </tr>
              <tr>
                <td class="label">Tanggal Terbit</td>
                <td class="colon">:</td>
                <td class="val"><?= htmlspecialchars(getDbVal($buku, 'tanggal_terbit', 'Tanggal_terbit')); ?></td>
              </tr>
              <tr>
                <td class="label">Berat</td>
                <td class="colon">:</td>
                <td class="val"><?= htmlspecialchars(getDbVal($buku, 'berat', 'Berat')); ?></td>
              </tr>
              <tr>
                <td class="label">Ukuran</td>
                <td class="colon">:</td>
                <td class="val"><?= htmlspecialchars(getDbVal($buku, 'ukuran', 'Ukuran')); ?></td>
              </tr>
            </table>
          </div>

        </div>
      </div>

    </div>

  </main>

 <!-- FOOTER GELAP (RAPIH & SIMETRIS DI TENGAH) -->
  <footer style="background-color: #020617; color: #94a3b8; padding: 48px 0 24px; margin-top: 60px; width: 100%;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 16px;">
      
      <!-- GRID 4 KOLOM -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 32px; text-align: left;">
        
        <!-- Kolom 1: Brand Logo -->
        <div>
          <a href="home.php" style="display: inline-flex; align-items: center; gap: 8px; font-size: 20px; font-weight: 800; color: #2563eb; text-decoration: none; margin-bottom: 12px;">
            <i class="fa-solid fa-book-open"></i>
            <span style="color: #ffffff;">Nijbook</span>
          </a>
          <p style="font-size: 12px; color: #94a3b8; line-height: 1.6; max-width: 260px;">
            Nijbook adalah toko buku online terpercaya yang menyediakan ribuan buku terbaik untuk menginspirasi harimu.
          </p>
        </div>

        <!-- Kolom 2: Toko -->
        <div>
          <h4 style="color: #ffffff; font-size: 14px; font-weight: 700; margin-bottom: 16px;">Toko</h4>
          <ul style="list-style: none; padding: 0; margin: 0; font-size: 12px;">
            <li style="margin-bottom: 10px;"><a href="home.php" style="color: #94a3b8; text-decoration: none;">Beranda</a></li>
            <li style="margin-bottom: 10px;"><a href="kategori.php" style="color: #94a3b8; text-decoration: none;">Kategori</a></li>
            <li style="margin-bottom: 10px;"><a href="home.php#popular-books" style="color: #94a3b8; text-decoration: none;">Buku</a></li>
          </ul>
        </div>

        <!-- Kolom 3: Informasi -->
        <div>
          <h4 style="color: #ffffff; font-size: 14px; font-weight: 700; margin-bottom: 16px;">Informasi</h4>
          <ul style="list-style: none; padding: 0; margin: 0; font-size: 12px;">
            <li style="margin-bottom: 10px;"><a href="about.php" style="color: #94a3b8; text-decoration: none;">Tentang Kami</a></li>
            <li style="margin-bottom: 10px;"><a href="#" style="color: #94a3b8; text-decoration: none;">FAQ</a></li>
          </ul>
        </div>

        <!-- Kolom 4: Kontak -->
        <div>
          <h4 style="color: #ffffff; font-size: 14px; font-weight: 700; margin-bottom: 16px;">Kontak</h4>
          <p style="font-size: 12px; color: #94a3b8; margin: 0;"><strong>WhatsApp:</strong> +62 889-0504-7384</p>
        </div>

      </div>

      <!-- COPYRIGHT LINE -->
      <div style="border-top: 1px solid #1e293b; padding-top: 24px; margin-top: 40px; text-align: center; font-size: 12px; color: #64748b;">
        <p>&copy; 2024 Nijbook. All rights reserved.</p>
      </div>

    </div>
  </footer>

<script>
// Fungsi untuk ganti-ganti isi Tab (Deskripsi & Detail)
function bukaTab(idTab, elemenTombol) {
  // Sembunyikan semua konten tab
  var semuaIsiTab = document.getElementsByClassName("isi-tab");
  for (var i = 0; i < semuaIsiTab.length; i++) {
    semuaIsiTab[i].style.display = "none";
  }
  
  // Hapus warna biru (active) dari semua tombol tab
  var semuaTombol = document.getElementsByClassName("tab-btn");
  for (var j = 0; j < semuaTombol.length; j++) {
    semuaTombol[j].classList.remove("active");
  }
  
  // Tampilkan tab yang dipilih & beri warna biru ke tombolnya
  document.getElementById(idTab).style.display = "block";
  elemenTombol.classList.add("active");
}

document.addEventListener("DOMContentLoaded", function () {
  const minus = document.getElementById("qtyMinus");
  const plus = document.getElementById("qtyPlus");
  const input = document.getElementById("qtyInput");
  if (!minus || !plus || !input) return;

  const maxStock = <?= (int)($buku["stok"] ?? 1); ?>;

  minus.addEventListener("click", function () {
    const qty = parseInt(input.value, 10) || 1;
    if (qty > 1) input.value = qty - 1;
  });

  plus.addEventListener("click", function () {
    const qty = parseInt(input.value, 10) || 1;
    if (qty < maxStock) input.value = qty + 1;
  });
});
</script>

</body>
</html>