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

$userId = (int)$_SESSION["user_id"];

// PERBAIKAN: Mengambil harga langsung dari tabel books agar tidak Rp0
$query = $conn->prepare("
    SELECT
        c.id,
        c.buku_id,
        c.judul_buku,
        COALESCE(b.harga, c.harga) AS harga,
        c.jumlah
    FROM cart c
    LEFT JOIN books b ON c.buku_id = b.id
    WHERE c.user_id = ?
    ORDER BY c.id ASC
");
$query->execute([$userId]);
$items = $query->fetchAll(PDO::FETCH_ASSOC);

if (!$items) {
    header("Location: cart.php");
    exit;
}

$subtotalBarang = 0;
foreach ($items as $item) {
    $subtotalBarang += ((float)$item["harga"]) * ((int)$item["jumlah"]);
}

/*
 * Ongkir simulasi untuk project.
 * Nanti bisa diganti API RajaOngkir/Biteship bila dibutuhkan.
 */
$shippingOptions = [
    "jne_reg" => [
        "kurir" => "JNE",
        "layanan" => "REG",
        "ongkir" => 15000,
        "estimasi" => "2-4 hari"
    ],
    "jnt_ez" => [
        "kurir" => "J&T Express",
        "layanan" => "EZ",
        "ongkir" => 14000,
        "estimasi" => "2-4 hari"
    ],
    "sicepat_reg" => [
        "kurir" => "SiCepat",
        "layanan" => "REG",
        "ongkir" => 13000,
        "estimasi" => "2-4 hari"
    ]
];

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $namaPenerima = trim($_POST["nama_penerima"] ?? "");
    $noHp = trim($_POST["no_hp"] ?? "");
    $provinsi = trim($_POST["provinsi"] ?? "");
    $kota = trim($_POST["kota"] ?? "");
    $kecamatan = trim($_POST["kecamatan"] ?? "");
    $kodePos = trim($_POST["kode_pos"] ?? "");
    $alamatLengkap = trim($_POST["alamat_lengkap"] ?? "");
    $shippingCode = trim($_POST["shipping"] ?? "");

    if (
        $namaPenerima === "" ||
        $noHp === "" ||
        $provinsi === "" ||
        $kota === "" ||
        $kecamatan === "" ||
        $kodePos === "" ||
        $alamatLengkap === "" ||
        !isset($shippingOptions[$shippingCode])
    ) {
        $error = "Lengkapi alamat dan pilih metode pengiriman terlebih dahulu.";
    } else {
        $shipping = $shippingOptions[$shippingCode];

        $_SESSION["checkout_data"] = [
            "nama_penerima" => $namaPenerima,
            "no_hp" => $noHp,
            "provinsi" => $provinsi,
            "kota" => $kota,
            "kecamatan" => $kecamatan,
            "kode_pos" => $kodePos,
            "alamat_lengkap" => $alamatLengkap,
            "shipping_code" => $shippingCode,
            "kurir" => $shipping["kurir"],
            "layanan_pengiriman" => $shipping["layanan"],
            "ongkir" => $shipping["ongkir"],
            "estimasi_pengiriman" => $shipping["estimasi"]
        ];

        header("Location: payment.php");
        exit;
    }
}

function rupiah($angka) {
    return "Rp" . number_format((float)$angka, 0, ",", ".");
}

$old = $_SESSION["checkout_data"] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Alamat & Pengiriman - Nijbook</title>
    <link rel="stylesheet" href="checkout.css">
</head>
<body>

<header class="checkout-header">
    <a href="home.php">📖 Nijbook</a>
</header>

<main class="checkout-page">

<section class="checkout-form-card">
    <div class="checkout-heading">
        <span>LANGKAH 1 DARI 2</span>
        <h1>Alamat & Pengiriman</h1>
        <p>Lengkapi alamat penerima dan pilih jasa pengiriman sebelum melakukan pembayaran.</p>
    </div>

    <?php if ($error !== ""): ?>
        <div class="checkout-error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" id="checkoutForm">
        <h3 class="form-section-title">Alamat Penerima</h3>

        <div class="form-group">
            <label>Nama Penerima</label>
            <input
                type="text"
                name="nama_penerima"
                value="<?= htmlspecialchars($_POST["nama_penerima"] ?? ($old["nama_penerima"] ?? ($_SESSION["nama"] ?? ""))); ?>"
                required
            >
        </div>

        <div class="form-group">
            <label>No. HP</label>
            <input
                type="text"
                name="no_hp"
                placeholder="08xxxxxxxxxx"
                value="<?= htmlspecialchars($_POST["no_hp"] ?? ($old["no_hp"] ?? "")); ?>"
                required
            >
        </div>

        <div class="two-column">
            <div class="form-group">
                <label>Provinsi</label>
                <input
                    type="text"
                    name="provinsi"
                    value="<?= htmlspecialchars($_POST["provinsi"] ?? ($old["provinsi"] ?? "")); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Kota / Kabupaten</label>
                <input
                    type="text"
                    name="kota"
                    value="<?= htmlspecialchars($_POST["kota"] ?? ($old["kota"] ?? "")); ?>"
                    required
                >
            </div>
        </div>

        <div class="two-column">
            <div class="form-group">
                <label>Kecamatan</label>
                <input
                    type="text"
                    name="kecamatan"
                    value="<?= htmlspecialchars($_POST["kecamatan"] ?? ($old["kecamatan"] ?? "")); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Kode Pos</label>
                <input
                    type="text"
                    name="kode_pos"
                    value="<?= htmlspecialchars($_POST["kode_pos"] ?? ($old["kode_pos"] ?? "")); ?>"
                    required
                >
            </div>
        </div>

        <div class="form-group">
            <label>Alamat Lengkap</label>
            <textarea
                name="alamat_lengkap"
                rows="5"
                placeholder="Nama jalan, nomor rumah, RT/RW, patokan..."
                required
            ><?= htmlspecialchars($_POST["alamat_lengkap"] ?? ($old["alamat_lengkap"] ?? "")); ?></textarea>
        </div>

        <h3 class="form-section-title shipping-title">Pilih Pengiriman</h3>

        <div class="shipping-options">
            <?php foreach ($shippingOptions as $code => $ship): ?>
                <?php
                $selectedShipping = $_POST["shipping"] ?? ($old["shipping_code"] ?? "");
                ?>
                <label class="shipping-card">
                    <input
                        type="radio"
                        name="shipping"
                        value="<?= htmlspecialchars($code); ?>"
                        <?= $selectedShipping === $code ? "checked" : ""; ?>
                        required
                    >
                    <div class="shipping-main">
                        <strong>
                            <?= htmlspecialchars($ship["kurir"]); ?>
                            <?= htmlspecialchars($ship["layanan"]); ?>
                        </strong>
                        <span>Estimasi <?= htmlspecialchars($ship["estimasi"]); ?></span>
                    </div>
                    <strong class="shipping-price"><?= rupiah($ship["ongkir"]); ?></strong>
                </label>
            <?php endforeach; ?>
        </div>

        <p class="shipping-note">
            * Ongkir di atas adalah tarif simulasi untuk project lokal.
        </p>

        <div class="form-actions">
            <a href="cart.php">← Kembali ke Keranjang</a>
            <button type="submit">Lanjut ke Pembayaran →</button>
        </div>
    </form>
</section>

<aside class="order-summary">
    <h2>Ringkasan Belanja</h2>

    <?php foreach ($items as $item): ?>
        <div class="order-item">
            <div>
                <strong><?= htmlspecialchars($item["judul_buku"]); ?></strong>
                <span><?= (int)$item["jumlah"]; ?> × <?= rupiah($item["harga"]); ?></span>
            </div>
            <strong>
                <?= rupiah(((float)$item["harga"]) * ((int)$item["jumlah"])); ?>
            </strong>
        </div>
    <?php endforeach; ?>

    <div class="summary-total">
        <span>Subtotal Buku</span>
        <strong><?= rupiah($subtotalBarang); ?></strong>
    </div>

    <p class="summary-small">
        Ongkir akan ditambahkan sesuai metode pengiriman yang dipilih.
    </p>
</aside>

</main>
</body>
</html>