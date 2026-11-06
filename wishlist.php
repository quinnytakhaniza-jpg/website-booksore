<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
    header("Location: login_user.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Ambil data buku yang berelasi dengan tabel wishlist menggunakan kolom buku_id
$query = $conn->prepare("SELECT b.* FROM books b JOIN wishlist w ON b.id = w.buku_id WHERE w.user_id = ? ORDER BY w.id DESC");
$query->execute([$userId]);
$wishlistBooks = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wishlist Saya - Nijbook</title>
  <link rel="stylesheet" href="home.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body style="background: #f8fafc;">

  <div class="navbar-wrapper" style="background:#fff; border-bottom:1px solid #e2e8f0; padding:15px 0;">
    <div class="container navbar" style="display:flex; justify-content:space-between; align-items:center;">
      <a href="home.php" class="brand-logo"><i class="fa-solid fa-book-open"></i><span>Nijbook</span></a>
      <a href="home.php" class="btn btn-outline-blue" style="text-decoration:none; padding:8px 15px; border-radius:8px;">Kembali ke Beranda</a>
    </div>
  </div>

  <div class="container section">
    <h2><i class="fa-solid fa-heart" style="color:#be185d;"></i> Wishlist Saya</h2>
    <p style="color:#64748b; margin-bottom: 30px;">Daftar buku-buku yang kamu sukai.</p>

    <?php if (count($wishlistBooks) > 0): ?>
      <div class="books-grid">
        <?php foreach ($wishlistBooks as $b): 
          $img = !empty($b['gambar']) ? 'image/' . htmlspecialchars($b['gambar']) : 'image/placeholder.jpg';
        ?>
        <a href="deskripsi.php?id=<?= $b['id']; ?>" class="book-card" style="display:flex;">
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($b['judul']); ?>">
          <div class="book-card-body">
            <h3><?= htmlspecialchars($b['judul']); ?></h3>
            <p class="author"><?= htmlspecialchars($b['penulis'] ?? $b['kategori']); ?></p>
            <div class="card-footer" style="margin-top:auto;">
              <span class="price"><?= "Rp" . number_format((float)$b['harga'], 0, ",", "."); ?></span>
              <button type="button" class="heart-icon" style="color:#be185d;" onclick="event.preventDefault(); window.location.href='tambah_wishlist.php?id=<?= $b['id']; ?>';">
                <i class="fa-solid fa-heart"></i>
              </button>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div style="text-align:center; padding: 50px 20px; background:#fff; border-radius:12px;">
        <i class="fa-regular fa-folder-open" style="font-size: 3rem; color:#cbd5e1; margin-bottom:15px;"></i>
        <h3 style="color:#475569;">Wishlist kamu masih kosong</h3>
        <p style="color:#94a3b8; margin-bottom:20px;">Yuk, cari buku favoritmu dan tekan tombol love!</p>
        <a href="home.php" class="btn btn-primary">Mulai Belanja</a>
      </div>
    <?php endif; ?>
  </div>

</body>
</html>