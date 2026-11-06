<?php
session_start();
require_once "koneksi.php";

if (
    !isset($_SESSION["login"]) ||
    $_SESSION["login"] !== true ||
    ($_SESSION["role"] ?? "") !== "user"
) {
    header("Location: login_user.php");
    exit;
}

$orderId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$orderId) {
    header("Location: home.php");
    exit;
}

$query = $conn->prepare("
    SELECT
        id,
        total,
        metode_pembayaran,
        status_pembayaran,
        status_pesanan,
        kurir,
        layanan_pengiriman
    FROM pesanan
    WHERE id = ? AND user_id = ?
    LIMIT 1
");
$query->execute([$orderId, (int)$_SESSION["user_id"]]);
$order = $query->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: home.php");
    exit;
}

function rupiah($angka) {
    return "Rp" . number_format((float)$angka, 0, ",", ".");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Dibuat - Nijbook</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px;
        }
        .card {
            width: 100%;
            max-width: 540px;
            padding: 36px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(15,23,42,.07);
        }
        .icon {
            width: 76px;
            height: 76px;
            margin: 0 auto 18px;
            border-radius: 50%;
            background: #dbeafe;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
        }
        h1 { color: #0f172a; }
        p { color: #64748b; line-height: 1.6; }
        .box {
            margin: 24px 0;
            padding: 17px;
            background: #f8fafc;
            border-radius: 13px;
            text-align: left;
        }
        .box div {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin: 9px 0;
        }
        .btn {
            display: inline-block;
            padding: 13px 22px;
            border-radius: 10px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            font-weight: 800;
        }
        .back {
            display: block;
            margin-top: 14px;
            color: #64748b;
            text-decoration: none;
            font-weight: 700;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="icon">✓</div>
    <h1>Bukti Pembayaran Terkirim</h1>
    <p>
        Pesanan sudah dibuat dan bukti pembayaran sedang menunggu verifikasi admin.
        Buku belum dikirim sampai pembayaran diverifikasi.
    </p>

    <div class="box">
        <div>
            <span>ID Pesanan</span>
            <strong>ORD-<?= str_pad((string)$order["id"], 6, "0", STR_PAD_LEFT); ?></strong>
        </div>
        <div>
            <span>Total</span>
            <strong><?= rupiah($order["total"]); ?></strong>
        </div>
        <div>
            <span>Metode</span>
            <strong><?= htmlspecialchars($order["metode_pembayaran"]); ?></strong>
        </div>
        <div>
            <span>Pembayaran</span>
            <strong><?= htmlspecialchars($order["status_pembayaran"]); ?></strong>
        </div>
        <div>
            <span>Pengiriman</span>
            <strong>
                <?= htmlspecialchars($order["kurir"] . " " . $order["layanan_pengiriman"]); ?>
            </strong>
        </div>
    </div>

    <!-- Tombol diarahkan langsung ke struk.php dengan membawa ID pesanan -->
    <a href="struk.php?id=<?= $order["id"]; ?>" class="btn">Lihat Pesanan Saya</a>
    <a href="home.php" class="back">Kembali ke Beranda</a>
</div>
</body>
</html>