<?php
session_start();
require_once "koneksi.php";

// Pastikan yang mengakses adalah admin
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login_admin.php");
    exit;
}

// Proses jika ada kiriman form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $statusPembayaran = trim($_POST['status_pembayaran'] ?? '');
    $statusPesanan = trim($_POST['status_pesanan'] ?? '');

    // Update data jika ID valid
    if ($orderId > 0 && $statusPembayaran !== '' && $statusPesanan !== '') {
        try {
            $updateQuery = $conn->prepare("
                UPDATE pesanan 
                SET status_pembayaran = ?, status_pesanan = ? 
                WHERE id = ?
            ");
            $updateQuery->execute([$statusPembayaran, $statusPesanan, $orderId]);
        } catch (PDOException $e) {
            die("Gagal mengupdate status: " . $e->getMessage());
        }
    }
}

// Kembalikan ke halaman pesanan admin dengan notifikasi sukses
header("Location: admin_dashboard.php?page=pesanan&updated=1");
exit;
?>