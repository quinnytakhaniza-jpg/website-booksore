<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login_admin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pesanId = filter_input(INPUT_POST, 'pesan_id', FILTER_VALIDATE_INT);
    $balasan = trim($_POST['balasan'] ?? '');

    if ($pesanId && !empty($balasan)) {
        try {
            $stmt = $conn->prepare("UPDATE pesan_kontak SET balasan = ? WHERE id = ?");
            $stmt->execute([$balasan, $pesanId]);
        } catch (PDOException $e) {
            // Error handling
        }
    }
}

header("Location: admin_dashboard.php?page=pesan_masuk");
exit();