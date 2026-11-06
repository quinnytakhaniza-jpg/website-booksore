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
$bukuId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($userId && $bukuId > 0) {
    try {
        // Cek apakah buku sudah ada di wishlist user
        $checkQuery = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND buku_id = ?");
        $checkQuery->execute([$userId, $bukuId]);
        
        if ($checkQuery->rowCount() == 0) {
            // Jika belum ada, masukkan ke wishlist
            $insertQuery = $conn->prepare("INSERT INTO wishlist (user_id, buku_id) VALUES (?, ?)");
            $insertQuery->execute([$userId, $bukuId]);
        } else {
            // Jika sudah ada, hapus dari wishlist (toggle)
            $deleteQuery = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND buku_id = ?");
            $deleteQuery->execute([$userId, $bukuId]);
        }
    } catch (PDOException $e) {
        // Abaikan error jika ada
    }
}

// Kembalikan user ke halaman sebelumnya secara otomatis
$referer = $_SERVER['HTTP_REFERER'] ?? 'home.php';
header("Location: " . $referer);
exit();
?>