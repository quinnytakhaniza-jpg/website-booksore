<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login_admin.php");
    exit;
}

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul     = $_POST['judul'];
    $kategori  = $_POST['kategori'];
    $stok      = $_POST['stok'];
    $harga     = $_POST['harga'];
    $deskripsi = $_POST['deskripsi'];
    
    $is_popular = isset($_POST['is_popular']) ? 1 : 0;
    
    $penulis        = $_POST['penulis'] ?? '';
    $penerbit       = $_POST['penerbit'] ?? '';
    $isbn           = $_POST['isbn'] ?? '';
    $bahasa         = $_POST['bahasa'] ?? 'Indonesia';
    $halaman        = !empty($_POST['halaman']) ? (int)$_POST['halaman'] : null;
    $tanggal_terbit = $_POST['tanggal_terbit'] ?? '';
    $berat          = $_POST['berat'] ?? '';
    $ukuran         = $_POST['ukuran'] ?? '';
    
    $nama_file_gambar = '';

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['gambar']['tmp_name'];
        $file_name = $_FILES['gambar']['name'];
        
        $ekstensi_diperbolehkan = array('png','jpg','jpeg','webp','avif');
        $x = explode('.', $file_name);
        $ekstensi = strtolower(end($x));
        
        $nama_file_baru = uniqid() . '-' . $file_name;
        $folder_tujuan = 'image/' . $nama_file_baru;
        
        if (in_array($ekstensi, $ekstensi_diperbolehkan) === true) {
            if (move_uploaded_file($file_tmp, $folder_tujuan)) {
                $nama_file_gambar = $nama_file_baru;
            } else {
                $pesan = "<div class='alert-danger'><i class='fa-solid fa-circle-exclamation'></i> Gagal mengunggah gambar.</div>";
            }
        } else {
            $pesan = "<div class='alert-danger'><i class='fa-solid fa-circle-exclamation'></i> Ekstensi gambar tidak diperbolehkan.</div>";
        }
    }

    if (empty($pesan)) {
        try {
            $query = $conn->prepare("INSERT INTO books (judul, kategori, stok, harga, gambar, deskripsi, penulis, penerbit, isbn, bahasa, halaman, tanggal_terbit, berat, ukuran, is_popular) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $query->execute([$judul, $kategori, $stok, $harga, $nama_file_gambar, $deskripsi, $penulis, $penerbit, $isbn, $bahasa, $halaman, $tanggal_terbit, $berat, $ukuran, $is_popular]);
            
            $pesan = "<div class='alert-success'><i class='fa-solid fa-check-circle'></i> Buku '$judul' berhasil ditambahkan!</div>";
        } catch (PDOException $e) {
            $pesan = "<div class='alert-danger'><i class='fa-solid fa-circle-exclamation'></i> Gagal: " . $e->getMessage() . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Buku - BookStore Admin</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-container { background: #fff; padding: 30px; border-radius: 12px; max-width: 600px; margin: 40px auto; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #dbe4f0; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-control { width: 100%; padding: 10px 15px; border: 1px solid #dbe4f0; border-radius: 8px; font-family: inherit; box-sizing: border-box; }
        textarea.form-control { resize: vertical; min-height: 120px; }
        .btn-back { display: inline-block; margin-bottom: 20px; color: #64748b; text-decoration: none; font-weight: 500; }
        .alert-success { background: #ecfdf3; color: #15803d; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; }
        .alert-danger { background: #fef2f2; color: #b91c1c; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; }
        .btn-simpan { background: #2563eb; color: white; width: 100%; padding: 12px; border-radius: 8px; font-size: 16px; border: none; cursor: pointer; font-weight: 600; margin-top: 10px; }
        .dua-kolom { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .checkbox-populer { display: flex; align-items: center; gap: 12px; background: #fffbeb; padding: 16px; border-radius: 10px; border: 1px solid #fde68a; margin-bottom: 20px; }
        .checkbox-populer input { width: 22px; height: 22px; cursor: pointer; }
        .checkbox-populer label { margin: 0; color: #b45309; cursor: pointer; font-size: 15px; }
    </style>
</head>
<body style="background: #f4f7fe; font-family: 'Plus Jakarta Sans', sans-serif;">

    <div class="form-container">
        <a href="admin_dashboard.php?page=buku" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Kembali ke Data Buku</a>
        <h2 style="margin-bottom: 20px;">Tambah Buku Baru</h2>
        <?= $pesan; ?>
        
        <form action="" method="POST" enctype="multipart/form-data">
            
            <div class="checkbox-populer">
                <input type="checkbox" name="is_popular" id="is_popular" value="1">
                <label for="is_popular"><i class="fa-solid fa-star" style="color:#f59e0b;"></i> <strong>Jadikan Buku Populer</strong> (Tampilkan di tab utama web)</label>
            </div>

            <div class="form-group">
                <label>Judul Buku</label>
                <input type="text" name="judul" class="form-control" required placeholder="Contoh: Laut Bercerita">
            </div>
            
            <!-- KATEGORI DITAMBAHKAN OPSI "Semua" -->
            <div class="form-group">
                <label>Kategori</label>
                <select name="kategori" class="form-control" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="Semua">Semua (Umum / Pilihan Utama)</option>
                    <option value="Fiksi">Fiksi</option>
                    <option value="Non-Fiksi">Non-Fiksi</option>
                    <option value="Fantasi">Fantasi</option>
                    <option value="Bisnis">Bisnis</option>
                    <option value="Pengembangan Diri">Pengembangan Diri</option>
                    <option value="Romance">Romance</option>
                    <option value="Teen & Young Adult Fiction">Teen & Young Adult Fiction</option>
                    <option value="Adult Fiction">Adult Fiction</option>
                    <option value="Motivation & Self-Help">Motivation & Self-Help</option>
                    <option value="Business & Investing">Business & Investing</option>
                </select>
            </div>
            
            <div class="dua-kolom">
                <div class="form-group"><label>Stok Tersedia</label><input type="number" name="stok" class="form-control" required min="0"></div>
                <div class="form-group"><label>Harga</label><input type="number" name="harga" class="form-control" required min="0"></div>
            </div>

            <hr style="border: 0; border-top: 1px dashed #cbd5e1; margin: 25px 0;">
            <h4 style="margin-bottom: 15px; color: #475569;">Informasi Detail Buku</h4>
            
            <div class="dua-kolom">
                <div class="form-group"><label>Penulis</label><input type="text" name="penulis" class="form-control"></div>
                <div class="form-group"><label>Penerbit</label><input type="text" name="penerbit" class="form-control"></div>
            </div>
            <div class="dua-kolom">
                <div class="form-group"><label>ISBN</label><input type="text" name="isbn" class="form-control"></div>
                <div class="form-group"><label>Bahasa</label><input type="text" name="bahasa" class="form-control" value="Indonesia"></div>
            </div>
            <div class="dua-kolom">
                <div class="form-group"><label>Jumlah Halaman</label><input type="number" name="halaman" class="form-control"></div>
                <div class="form-group"><label>Tanggal Terbit</label><input type="text" name="tanggal_terbit" class="form-control"></div>
            </div>
            <div class="dua-kolom">
                <div class="form-group"><label>Berat</label><input type="text" name="berat" class="form-control"></div>
                <div class="form-group"><label>Ukuran</label><input type="text" name="ukuran" class="form-control"></div>
            </div>

            <hr style="border: 0; border-top: 1px dashed #cbd5e1; margin: 15px 0 25px 0;">

            <div class="form-group">
                <label>Deskripsi Sinopsis Buku</label>
                <textarea name="deskripsi" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <label>Gambar Cover (Opsional)</label>
                <input type="file" name="gambar" class="form-control" accept="image/*">
            </div>
            
            <button type="submit" class="btn-simpan"><i class="fa-solid fa-save"></i> Simpan ke Database</button>
        </form>
    </div>
</body>
</html>