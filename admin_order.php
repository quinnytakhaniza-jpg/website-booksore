<?php
session_start();
require_once "koneksi.php";

if (
    !isset($_SESSION["admin_login"]) ||
    $_SESSION["admin_login"] !== true
) {
    header("Location: login_admin.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admin_dashboard.php?page=pesanan");
    exit;
}

$csrf = $_POST["csrf"] ?? "";

if (
    empty($_SESSION["admin_csrf"]) ||
    !hash_equals($_SESSION["admin_csrf"], $csrf)
) {
    http_response_code(403);
    exit("Permintaan tidak valid.");
}

$orderId = filter_input(INPUT_POST, "order_id", FILTER_VALIDATE_INT);
$statusPembayaran = trim($_POST["status_pembayaran"] ?? "");
$statusPesanan = trim($_POST["status_pesanan"] ?? "");

$allowedPayment = [
    "Menunggu Verifikasi",
    "Terverifikasi",
    "Ditolak"
];

$allowedOrder = [
    "Menunggu Verifikasi Pembayaran",
    "Menunggu Diproses",
    "Diproses",
    "Dikemas",
    "Dikirim",
    "Selesai",
    "Dibatalkan"
];

if (
    !$orderId ||
    !in_array($statusPembayaran, $allowedPayment, true) ||
    !in_array($statusPesanan, $allowedOrder, true)
) {
    header("Location: admin_dashboard.php?page=pesanan");
    exit;
}

/*
 * Jika admin memverifikasi pembayaran dan status pesanan
 * masih menunggu verifikasi, otomatis pindah ke Menunggu Diproses.
 */
if (
    $statusPembayaran === "Terverifikasi" &&
    $statusPesanan === "Menunggu Verifikasi Pembayaran"
) {
    $statusPesanan = "Menunggu Diproses";
}

$update = $conn->prepare("
    UPDATE pesanan
    SET
        status_pembayaran = :status_pembayaran,
        status_pesanan = :status_pesanan,
        status = :legacy_status
    WHERE id = :id
");

$update->execute([
    ":status_pembayaran" => $statusPembayaran,
    ":status_pesanan" => $statusPesanan,
    ":legacy_status" => $statusPesanan,
    ":id" => $orderId
]);

header("Location: admin_dashboard.php?page=pesanan&updated=1");
exit;
?>