<?php
session_start();
require_once 'koneksi.php';

// Menangkap kata kunci dari URL (contoh: pencarian.php?q=hujan)
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';

$searchResults = [];
if ($keyword !== '') {
    // Mencari kecocokan di Judul, Penulis, atau Kategori
    $searchQuery = $conn->prepare("SELECT * FROM books WHERE judul LIKE ? OR penulis LIKE ? OR kategori LIKE ? ORDER BY id DESC");
    $likeKeyword = "%" . $keyword . "%";
    $searchQuery->execute([$likeKeyword, $likeKeyword, $likeKeyword]);
    $searchResults = $searchQuery->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pencarian: <?= htmlspecialchars($keyword); ?> - Nijbook</title>
  <link rel="stylesheet" href="home.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body style="background: #f8fafc;">

  <!-- Navbar Simple -->
  <div class="navbar-wrapper" style="background:#fff; border-bottom:1px solid #e2e8f0; padding:15px 0;">
    <div class="container navbar" style="display:flex; justify-content:space-between; align-items:center;">
      <a href="home.php" class="brand-logo"><i class="fa-solid fa-book-open"></i><span>Nijbook</span></a>
      <form action="pencarian.php" method="GET" class="search-box">
        <input type="text" name="q" value="<?= htmlspecialchars($keyword); ?>" placeholder="Cari buku, penulis..." required>
        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
      </form>
      <a href="home.php" class="btn btn-outline-blue" style="text-decoration:none; padding:8px 15px; border-radius:8px;">Kembali</a>
    </div>
  </div>

  <div class="container section">
    <h2>Hasil Pencarian: "<span style="color:#2563eb;"><?= htmlspecialchars($keyword); ?></span>"</h2>
    <p style="color:#64748b; margin-bottom: 30px;">Ditemukan <?= count($searchResults); ?> buku.</p>

    <?php if (count($searchResults) > 0): ?>
      <div class="books-grid">
        <?php foreach ($searchResults as $b): 
          $img = !empty($b['gambar']) ? 'image/' . htmlspecialchars($b['gambar']) : 'image/placeholder.jpg';
        ?>
        <a href="deskripsi.php?id=<?= $b['id']; ?>" class="book-card" style="display:flex;">
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($b['judul']); ?>">
          <div class="book-card-body">
            <h3><?= htmlspecialchars($b['judul']); ?></h3>
            <p class="author"><?= htmlspecialchars($b['penulis'] ?? $b['kategori']); ?></p>
            <div class="card-footer" style="margin-top:auto;">
              <span class="price"><?= "Rp" . number_format((float)$b['harga'], 0, ",", "."); ?></span>
              <button type="button" class="heart-icon" onclick="event.preventDefault(); window.location.href='tambah_wishlist.php?id=<?= $b['id']; ?>';">
                <i class="fa-regular fa-heart"></i>
              </button>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div style="text-align:center; padding: 50px 20px; background:#fff; border-radius:12px;">
        <i class="fa-solid fa-magnifying-glass-minus" style="font-size: 3rem; color:#cbd5e1; margin-bottom:15px;"></i>
        <h3 style="color:#475569;">Buku tidak ditemukan</h3>
        <p style="color:#94a3b8;">Coba gunakan kata kunci lain (misal judul spesifik atau nama penulis).</p>
      </div>
    <?php endif; ?>
  </div>

</body>
</html>