<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login_admin.php");
    exit;
}

$adminName = $_SESSION['admin_name'] ?? 'Admin BookStore';
$adminRole = $_SESSION['admin_role'] ?? 'Administrator';
$page = $_GET['page'] ?? 'dashboard';

if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

$adminCsrf = $_SESSION['admin_csrf'];

// --- FUNGSI DELETE BUKU ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_book_id'])) {
    $delId = (int)$_POST['delete_book_id'];
    try {
        $cekGambar = $conn->prepare("SELECT gambar FROM books WHERE id = ?");
        $cekGambar->execute([$delId]);
        $rowGambar = $cekGambar->fetch(PDO::FETCH_ASSOC);
        
        if ($rowGambar && !empty($rowGambar['gambar'])) {
            $pathFile = 'image/' . $rowGambar['gambar'];
            if (file_exists($pathFile)) {
                unlink($pathFile);
            }
        }
        
        try {
            $delDetail = $conn->prepare("DELETE FROM detail_pesanan WHERE buku_id = ? OR id_buku = ?");
            $delDetail->execute([$delId, $delId]);
        } catch (Exception $e) {}

        try {
            $delCart = $conn->prepare("DELETE FROM cart WHERE buku_id = ? OR id_buku = ?");
            $delCart->execute([$delId, $delId]);
        } catch (Exception $e) {}

        $hapusBuku = $conn->prepare("DELETE FROM books WHERE id = ?");
        $hapusBuku->execute([$delId]);
        
        header("Location: admin_dashboard.php?page=buku");
        exit;
    } catch (PDOException $e) {
        echo "<script>alert('Gagal menghapus buku: " . addslashes($e->getMessage()) . "');</script>";
    }
}

function isActive($menu, $page) {
    return $menu === $page ? 'active' : '';
}

function formatRupiah($angka) {
    return "Rp" . number_format((float)$angka, 0, ",", ".");
}

function orderCode($id) {
    return "ORD-" . str_pad((string)$id, 6, "0", STR_PAD_LEFT);
}

// AMBIL KATEGORI
$categoryQuery = $conn->query("SELECT kategori AS nama, COUNT(id) AS jumlah_buku FROM books GROUP BY kategori");
$categories = $categoryQuery->fetchAll(PDO::FETCH_ASSOC);

// AMBIL BUKU
$booksQuery = $conn->query("SELECT id, judul, kategori, stok, harga FROM books ORDER BY id DESC");
$booksDb = $booksQuery->fetchAll(PDO::FETCH_ASSOC);
$books = [];
foreach ($booksDb as $row) {
    $books[] = [
        'id' => $row['id'],
        'judul' => $row['judul'],
        'kategori' => $row['kategori'],
        'stok' => $row['stok'],
        'harga' => formatRupiah($row['harga'])
    ];
}

// AMBIL USERS
$userQuery = $conn->query("SELECT id, nama, email FROM users WHERE role = 'user' ORDER BY id DESC");
$usersDb = $userQuery->fetchAll(PDO::FETCH_ASSOC);
$users = [];
foreach ($usersDb as $row) {
    $users[] = [
        "id" => $row["id"],
        "nama" => $row["nama"],
        "email" => $row["email"],
        "gabung" => "-"
    ];
}

// HITUNG TOTAL PESAN CHAT MASUK
try {
    $chatCountQuery = $conn->query("SELECT COUNT(DISTINCT user_id) FROM chat_konsultasi WHERE pengirim = 'user'");
    $totalPesanChat = $chatCountQuery->fetchColumn();
} catch (Exception $e) {
    $totalPesanChat = 0;
}

// AMBIL PESANAN (DIJAMIN TAMPIL SEMUA WALAU DETAIL KOSONG)
$orderQuery = $conn->query("
    SELECT * FROM pesanan ORDER BY id DESC
");
$ordersDb = $orderQuery->fetchAll(PDO::FETCH_ASSOC);
$orders = [];

foreach ($ordersDb as $row) {
    $orderId = $row['id'];
    
    // Ambil detail item pesanan secara terpisah agar tidak pernah gagal tampil
    $detailBukuList = "-";
    $totalQty = 1;
    try {
        $stmtD = $conn->prepare("SELECT judul_buku, jumlah FROM detail_pesanan WHERE pesanan_id = ? OR order_id = ? OR id_pesanan = ?");
        $stmtD->execute([$orderId, $orderId, $orderId]);
        $details = $stmtD->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($details)) {
            $arrTitles = [];
            $totalQty = 0;
            foreach ($details as $det) {
                $arrTitles[] = ($det['judul_buku'] ?? 'Buku') . " x" . ($det['jumlah'] ?? 1);
                $totalQty += (int)($det['jumlah'] ?? 1);
            }
            $detailBukuList = implode(', ', $arrTitles);
        } else {
            $detailBukuList = $row['judul_buku'] ?? 'Pesanan Buku';
        }
    } catch (Exception $e) {
        $detailBukuList = $row['judul_buku'] ?? 'Pesanan Buku';
    }

    $orders[] = [
        "db_id" => (int)$row["id"],
        "id" => orderCode($row["id"]),
        "user" => $row["nama_user"] ?? 'User',
        "email" => $row["email_user"] ?? '-',
        "judul" => $detailBukuList,
        "jumlah" => $totalQty,
        "tanggal" => date("d-m-Y H:i", strtotime($row["created_at"] ?? 'now')),
        "total" => formatRupiah($row["total"] ?? 0),
        "status" => $row["status_pesanan"] ?? "Menunggu",
        "pembayaran" => $row["status_pembayaran"] ?? "Belum Dibayar",
        "metode_pembayaran" => $row["metode_pembayaran"] ?? "-",
        "bukti_pembayaran" => $row["bukti_pembayaran"] ?? "",
        "pengiriman" => trim(($row["kurir"] ?? "") . " " . ($row["layanan_pengiriman"] ?? "")),
        "ongkir" => formatRupiah($row["ongkir"] ?? 0),
        "estimasi" => $row["estimasi_pengiriman"] ?? "-",
        "alamat" => ($row["alamat_lengkap"] ?? '') . ", " . ($row["kecamatan"] ?? '') . ", " . ($row["kota"] ?? '') . ", " . ($row["provinsi"] ?? '') . " " . ($row["kode_pos"] ?? '')
    ];
}

$totalKategori = count($categories);
$totalBuku = count($books);
$totalUser = count($users);
$totalPesanan = count($orders);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BookStore</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">

<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div>
            <div class="sidebar-brand">
                <div class="brand-icon"><i class="fa-solid fa-book-open"></i></div>
                <div>
                    <h2>BOOKSTORE APP</h2>
                    <p>Panel Admin</p>
                </div>
            </div>

            <div class="sidebar-box">
                <h3>ADMIN</h3>
                <p>Control dan monitor keseluruhan data BookStore dengan mudah dan terstruktur.</p>
            </div>

            <nav class="sidebar-nav">
                <a href="admin_dashboard.php?page=dashboard" class="<?= isActive('dashboard', $page); ?>">
                    <i class="fa-solid fa-house"></i> Dashboard
                </a>
                <a href="admin_dashboard.php?page=kategori" class="<?= isActive('kategori', $page); ?>">
                    <i class="fa-solid fa-tags"></i> Kategori Buku
                </a>
                <a href="admin_dashboard.php?page=buku" class="<?= isActive('buku', $page); ?>">
                    <i class="fa-solid fa-book"></i> Data Buku
                </a>
                <a href="admin_dashboard.php?page=users" class="<?= isActive('users', $page); ?>">
                    <i class="fa-solid fa-users"></i> Data Users
                </a>
                <a href="admin_dashboard.php?page=pesanan" class="<?= isActive('pesanan', $page); ?>">
                    <i class="fa-solid fa-cart-shopping"></i> Pesanan Buku
                </a>
                <a href="admin_konsultasi.php">
                    <i class="fa-solid fa-envelope"></i> Pesan Masuk 
                    <?php if($totalPesanChat > 0): ?>
                        <span style="background: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 10px; margin-left: auto;"><?= $totalPesanChat; ?></span>
                    <?php endif; ?>
                </a>
            </nav>
        </div>

        <a href="logout.php" class="sidebar-logout">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </aside>

    <!-- MAIN -->
    <main class="main-content">
        <header class="topbar">
            <button class="mobile-menu-btn" onclick="toggleSidebar()">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="topbar-right">
                <div class="admin-profile">
                    <div class="admin-avatar">A</div>
                    <div>
                        <strong><?= htmlspecialchars($adminName); ?></strong>
                        <small><?= htmlspecialchars($adminRole); ?></small>
                    </div>
                </div>
            </div>
        </header>

        <?php if ($page === 'dashboard'): ?>
            <section class="page-header">
                <h1>Beranda Admin</h1>
                <p>Selamat datang kembali, <?= htmlspecialchars($adminName); ?>! Berikut ringkasan data dan aktivitas sistem.</p>
            </section>

            <section class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-table-cells-large"></i></div>
                    <div>
                        <h4>Total Kategori</h4>
                        <h2><?= $totalKategori; ?></h2>
                        <span>Data sistem</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-book"></i></div>
                    <div>
                        <h4>Total Buku</h4>
                        <h2><?= $totalBuku; ?></h2>
                        <span>Data sistem</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <h4>Total User</h4>
                        <h2><?= $totalUser; ?></h2>
                        <span>User terdaftar</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-cart-shopping"></i></div>
                    <div>
                        <h4>Total Pesanan</h4>
                        <h2><?= $totalPesanan; ?></h2>
                        <span>Pesanan masuk</span>
                    </div>
                </div>
            </section>

            <section class="two-col-grid">
                <div class="table-card">
                    <div class="table-head">
                        <h3>List Pesanan Terbaru</h3>
                        <a href="admin_dashboard.php?page=pesanan" class="small-link">Lihat Semua</a>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>User</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach (array_slice($orders, 0, 5) as $i => $order): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['id']); ?></td>
                                    <td><?= htmlspecialchars($order['user']); ?></td>
                                    <td><?= htmlspecialchars($order['total']); ?></td>
                                    <td><span class="status-badge"><?= htmlspecialchars($order['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        <?php elseif ($page === 'kategori'): ?>
            <section class="page-header">
                <h1>Kategori Buku</h1>
                <p>Kelola seluruh kategori buku yang tersedia.</p>
            </section>
            <div class="table-card">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama Kategori</th>
                                <th>Jumlah Buku</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($categories as $i => $cat): ?>
                            <tr>
                                <td><?= $i + 1; ?></td>
                                <td><?= htmlspecialchars($cat['nama']); ?></td>
                                <td><?= htmlspecialchars($cat['jumlah_buku']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($page === 'buku'): ?>
            <section class="page-header">
                <h1>Data Buku</h1>
                <p>Kelola seluruh data buku di sistem.</p>
            </section>
            <div class="page-action-top">
                <button class="btn-primary" onclick="window.location.href='tambah_buku.php'"><i class="fa-solid fa-plus"></i> Tambah Buku</button>
            </div>
            <div class="table-card">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Judul Buku</th>
                                <th>Kategori</th>
                                <th>Stok</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($books as $i => $book): ?>
                            <tr>
                                <td><?= $i + 1; ?></td>
                                <td><?= htmlspecialchars($book['judul']); ?></td>
                                <td><?= htmlspecialchars($book['kategori']); ?></td>
                                <td><?= htmlspecialchars($book['stok']); ?></td>
                                <td><?= htmlspecialchars($book['harga']); ?></td>
                                <td>
                                    <form method="POST" action="admin_dashboard.php?page=buku" style="margin: 0;" onsubmit="return confirm('Yakin ingin menghapus buku ini?');">
                                        <input type="hidden" name="delete_book_id" value="<?= $book['id']; ?>">
                                        <button type="submit" class="btn-danger-outline small-btn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($page === 'users'): ?>
            <section class="page-header">
                <h1>List User Terdaftar</h1>
                <p>Daftar user yang telah mendaftar di aplikasi.</p>
            </section>
            <div class="table-card">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama User</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $i => $user): ?>
                            <tr>
                                <td><?= $i + 1; ?></td>
                                <td><?= htmlspecialchars($user['nama']); ?></td>
                                <td><?= htmlspecialchars($user['email']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($page === 'pesanan'): ?>
            <section class="page-header">
                <h1>List Pesanan Buku</h1>
                <p>Daftar seluruh pesanan dari user yang berbeda-beda.</p>
            </section>

            <div class="table-card">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>ID Pesanan</th>
                                <th>Nama User</th>
                                <th>Buku & Qty</th>
                                <th>Total</th>
                                <th>Metode Bayar</th>
                                <th>Bukti Bayar</th>
                                <th>Status Bayar</th>
                                <th>Pengiriman & Alamat</th>
                                <th>Aksi & Ganti Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orders as $i => $order): ?>
                            <tr>
                                <td><?= $i + 1; ?></td>
                                <td><strong><?= htmlspecialchars($order['id']); ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($order['user']); ?><br>
                                    <small><?= htmlspecialchars($order['email']); ?></small>
                                </td>
                                <td><?= htmlspecialchars($order['judul']); ?> <br> <strong>(Total: <?= (int)$order['jumlah']; ?>)</strong></td>
                                <td><strong><?= htmlspecialchars($order['total']); ?></strong></td>
                                <td><?= htmlspecialchars($order['metode_pembayaran']); ?></td>
                                
                                <td style="text-align: center; vertical-align: middle;">
                                    <?php if ($order['bukti_pembayaran'] !== ''): ?>
                                        <a href="<?= htmlspecialchars($order['bukti_pembayaran']); ?>" target="_blank" title="Klik untuk perbesar" style="display: inline-block; margin-bottom: 6px;">
                                            <img src="<?= htmlspecialchars($order['bukti_pembayaran']); ?>" alt="Bukti" style="display: block; width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 0 auto;">
                                        </a>
                                        <br>
                                        <a href="<?= htmlspecialchars($order['bukti_pembayaran']); ?>" target="_blank" style="display: inline-block; padding: 4px 8px; background: #eff6ff; color: #1d4ed8; text-decoration: none; font-size: 11px; font-weight: 700; border-radius: 4px; border: 1px solid #bfdbfe; white-space: nowrap;">
                                            <i class="fa-solid fa-expand"></i> Lihat
                                        </a>
                                    <?php else: ?>
                                        <span style="display: inline-block; padding: 4px 8px; background: #f1f5f9; color: #64748b; font-size: 11px; font-weight: 600; border-radius: 4px; border: 1px solid #e2e8f0; white-space: nowrap;">
                                            Belum Ada
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td><span class="status-badge"><?= htmlspecialchars($order['pembayaran']); ?></span><br><span class="status-badge" style="margin-top:4px;"><?= htmlspecialchars($order['status']); ?></span></td>
                                
                                <td style="min-width:200px; white-space:normal; font-size:13px;">
                                    <strong><?= htmlspecialchars($order['pengiriman']); ?></strong> (<?= htmlspecialchars($order['ongkir']); ?>)<br>
                                    <?= htmlspecialchars($order['alamat']); ?>
                                </td>
                                
                                <td style="min-width:230px;">
                                    <form action="admin_update_order.php" method="POST" style="display:grid;gap:6px; background:#f8fafc; padding:8px; border-radius:8px; border:1px solid #e2e8f0;">
                                        <input type="hidden" name="order_id" value="<?= (int)$order['db_id']; ?>">
                                        <select name="status_pembayaran" style="padding:6px; border:1px solid #cbd5e1; border-radius:6px; font-size:12px; font-weight:600;">
                                            <?php
                                            $paymentOptions = ['Menunggu Verifikasi', 'Terverifikasi', 'Ditolak'];
                                            foreach ($paymentOptions as $opt): ?>
                                                <option value="<?= $opt; ?>" <?= $order['pembayaran'] === $opt ? 'selected' : ''; ?>><?= $opt; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <select name="status_pesanan" style="padding:6px; border:1px solid #cbd5e1; border-radius:6px; font-size:12px; font-weight:600;">
                                            <?php
                                            $statusOptions = ['Menunggu Verifikasi Pembayaran', 'Menunggu Diproses', 'Diproses', 'Dikemas', 'Dikirim', 'Selesai', 'Dibatalkan'];
                                            foreach ($statusOptions as $opt): ?>
                                                <option value="<?= $opt; ?>" <?= $order['status'] === $opt ? 'selected' : ''; ?>><?= $opt; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn-primary small-btn" style="padding:6px; font-size:12px;">Simpan Status</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <footer class="admin-footer">
            © 2026 BookStore App. All rights reserved.
        </footer>
    </main>
</div>

<script src="admin.js"></script>
</body>
</html>