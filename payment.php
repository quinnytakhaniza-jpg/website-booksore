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

if (!isset($_SESSION["checkout_data"])) {
    header("Location: checkout.php");
    exit;
}

$userId = (int)$_SESSION["user_id"];

$query = $conn->prepare("
    SELECT c.id, c.buku_id, c.judul_buku, COALESCE(b.harga, c.harga) AS harga, c.jumlah
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

$checkout = $_SESSION["checkout_data"];

$subtotalBarang = 0;
$totalQty = 0;
foreach ($items as $item) {
    $subtotalBarang += ((float)$item["harga"]) * ((int)$item["jumlah"]);
    $totalQty += (int)$item["jumlah"];
}

$ongkir = (float)($checkout["ongkir"] ?? 0);
$grandTotal = $subtotalBarang + $ongkir;

// POIN 6: Penambahan Opsi E-Wallet & Tipe Pembayaran
$paymentMethods = [
    "bca" => [
        "label" => "Transfer Bank BCA",
        "detail" => "Rek. DEMO 1234567890 a.n. NIJBOOK DEMO",
        "type" => "bank"
    ],
    "bri" => [
        "label" => "Transfer Bank BRI",
        "detail" => "Rek. DEMO 9876543210 a.n. NIJBOOK DEMO",
        "type" => "bank"
    ],
    "mandiri" => [
        "label" => "Transfer Bank Mandiri",
        "detail" => "Rek. DEMO 1122334455 a.n. NIJBOOK DEMO",
        "type" => "bank"
    ],
    "qris" => [
        "label" => "QRIS",
        "detail" => "QRIS DEMO — unggah screenshot setelah pembayaran",
        "type" => "qris"
    ],
    "dana" => [
        "label" => "E-Wallet DANA",
        "detail" => "No. 081234567890 a.n. NIJBOOK",
        "type" => "ewallet"
    ],
    "gopay" => [
        "label" => "E-Wallet GoPay",
        "detail" => "No. 081234567890 a.n. NIJBOOK",
        "type" => "ewallet"
    ]
];

$error = "";
$success_redirect = "";

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["confirm_payment"])
) {
    $methodCode = trim($_POST["metode_pembayaran"] ?? "");
    $rekeningPengirim = trim($_POST["rekening_pengirim"] ?? ""); // Ambil data rekening ewallet

    if (!isset($paymentMethods[$methodCode])) {
        $error = "Pilih metode pembayaran terlebih dahulu.";
    } elseif ($paymentMethods[$methodCode]["type"] === "ewallet" && $rekeningPengirim === "") {
        // Validasi Poin 6
        $error = "Nomor E-Wallet pengirim wajib diisi untuk verifikasi.";
    } elseif (
        !isset($_FILES["bukti_pembayaran"]) ||
        $_FILES["bukti_pembayaran"]["error"] !== UPLOAD_ERR_OK
    ) {
        $error = "Upload bukti pembayaran terlebih dahulu.";
    } else {
        $file = $_FILES["bukti_pembayaran"];
        $maxSize = 5 * 1024 * 1024;
        $allowedExt = ["jpg", "jpeg", "png", "webp"];

        $originalName = $file["name"];
        $tmpName = $file["tmp_name"];
        $size = (int)$file["size"];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($size <= 0 || $size > $maxSize) {
            $error = "Ukuran bukti pembayaran maksimal 5 MB.";
        } elseif (!in_array($ext, $allowedExt, true)) {
            $error = "Bukti pembayaran harus berupa JPG, JPEG, PNG, atau WEBP.";
        } elseif (@getimagesize($tmpName) === false) {
            $error = "File yang diupload bukan gambar yang valid.";
        } else {
            $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "bukti_pembayaran";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $safeFileName =
                "bukti_" .
                $userId . "_" .
                date("Ymd_His") . "_" .
                bin2hex(random_bytes(4)) . "." .
                $ext;

            $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $safeFileName;
            $relativePath = "uploads/bukti_pembayaran/" . $safeFileName;

            if (!move_uploaded_file($tmpName, $targetPath)) {
                $error = "Bukti pembayaran gagal disimpan. Cek permission folder uploads.";
            } else {
                $method = $paymentMethods[$methodCode];
                
                // Tambahkan nomor e-wallet ke label agar admin bisa baca
                $metodeLabel = $method["label"];
                if ($method["type"] === "ewallet") {
                    $metodeLabel .= " (No. " . $rekeningPengirim . ")";
                }

                $conn->beginTransaction();

                try {
                    $insertOrder = $conn->prepare("
                        INSERT INTO pesanan
                        (
                            user_id, nama_user, email_user, total,
                            nama_penerima, no_hp, provinsi, kota,
                            kecamatan, kode_pos, alamat_lengkap, kurir,
                            layanan_pengiriman, ongkir, estimasi_pengiriman,
                            metode_pembayaran, bukti_pembayaran, status_pembayaran,
                            status_pesanan
                        )
                        VALUES
                        (
                            :user_id, :nama_user, :email_user, :total,
                            :nama_penerima, :no_hp, :provinsi, :kota,
                            :kecamatan, :kode_pos, :alamat_lengkap, :kurir,
                            :layanan_pengiriman, :ongkir, :estimasi_pengiriman,
                            :metode_pembayaran, :bukti_pembayaran, :status_pembayaran,
                            :status_pesanan
                        )
                    ");

                    $insertOrder->execute([
                        ":user_id" => $userId,
                        ":nama_user" => $_SESSION["nama"] ?? "User",
                        ":email_user" => $_SESSION["email"] ?? "-",
                        ":total" => $grandTotal,
                        ":nama_penerima" => $checkout["nama_penerima"],
                        ":no_hp" => $checkout["no_hp"],
                        ":provinsi" => $checkout["provinsi"],
                        ":kota" => $checkout["kota"],
                        ":kecamatan" => $checkout["kecamatan"],
                        ":kode_pos" => $checkout["kode_pos"],
                        ":alamat_lengkap" => $checkout["alamat_lengkap"],
                        ":kurir" => $checkout["kurir"],
                        ":layanan_pengiriman" => $checkout["layanan_pengiriman"],
                        ":ongkir" => $ongkir,
                        ":estimasi_pengiriman" => $checkout["estimasi_pengiriman"],
                        ":metode_pembayaran" => $metodeLabel, // Disimpan bersama nomor HP e-wallet
                        ":bukti_pembayaran" => $relativePath,
                        ":status_pembayaran" => "Menunggu Verifikasi",
                        ":status_pesanan" => "Menunggu Verifikasi Pembayaran"
                    ]);

                    $pesananId = (int)$conn->lastInsertId();

                    $insertDetail = $conn->prepare("
                        INSERT INTO detail_pesanan
                            (pesanan_id, buku_id, judul_buku, harga_satuan, jumlah)
                        VALUES
                            (?, ?, ?, ?, ?)
                    ");

                    foreach ($items as $item) {
                        $insertDetail->execute([
                            $pesananId,
                            $item["buku_id"],
                            $item["judul_buku"],
                            $item["harga"],
                            $item["jumlah"]
                        ]);
                    }

                    $clearCart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
                    $clearCart->execute([$userId]);

                    $conn->commit();
                    unset($_SESSION["checkout_data"]);

                    // POIN 7: Tandai sukses untuk trigger JS Bubble Notification
                    $success_redirect = $pesananId;

                } catch (Throwable $e) {
                    if ($conn->inTransaction()) {
                        $conn->rollBack();
                    }

                    if (is_file($targetPath)) {
                        @unlink($targetPath);
                    }

                    $error = "Pesanan gagal diproses: " . $e->getMessage();
                }
            }
        }
    }
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
    <title>Pembayaran - Nijbook</title>
    <link rel="stylesheet" href="payment.css">
    
    <!-- TAMBAHAN POIN 7: SweetAlert2 untuk Notifikasi -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .ewallet-input-container {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
        }
        .ewallet-input-container label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            color: #334155;
        }
        .ewallet-input-container input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #94a3b8;
            border-radius: 6px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<header class="payment-header">
    <a href="home.php">📖 Nijbook</a>
</header>

<main class="payment-page">

<section class="payment-card">
    <div class="step-label">LANGKAH 2 DARI 2</div>

    <h1>Metode Pembayaran</h1>
    <p class="payment-description">
        Pilih metode pembayaran, lakukan pembayaran, lalu upload bukti pembayaran.
        Pesanan baru akan diproses setelah admin memverifikasi bukti pembayaran.
    </p>

    <!-- Error manual fallback bila JS mati -->
    <?php if ($error !== "" && empty($success_redirect)): ?>
        <div class="payment-error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="address-box">
        <h3>Alamat & Pengiriman</h3>
        <strong><?= htmlspecialchars($checkout["nama_penerima"]); ?></strong>
        <p><?= htmlspecialchars($checkout["no_hp"]); ?></p>
        <p><?= nl2br(htmlspecialchars($checkout["alamat_lengkap"])); ?></p>
        <p>
            <?= htmlspecialchars($checkout["kecamatan"]); ?>,
            <?= htmlspecialchars($checkout["kota"]); ?>,
            <?= htmlspecialchars($checkout["provinsi"]); ?>
            <?= htmlspecialchars($checkout["kode_pos"]); ?>
        </p>
        <div class="shipping-summary">
            <strong>
                <?= htmlspecialchars($checkout["kurir"]); ?>
                <?= htmlspecialchars($checkout["layanan_pengiriman"]); ?>
            </strong>
            <span>
                <?= rupiah($ongkir); ?> • Estimasi
                <?= htmlspecialchars($checkout["estimasi_pengiriman"]); ?>
            </span>
        </div>
    </div>

    <div class="payment-items">
        <h3>Pesanan</h3>
        <?php foreach ($items as $item): ?>
            <div class="payment-item">
                <span>
                    <?= htmlspecialchars($item["judul_buku"]); ?>
                    × <?= (int)$item["jumlah"]; ?>
                </span>
                <strong>
                    <?= rupiah(((float)$item["harga"]) * ((int)$item["jumlah"])); ?>
                </strong>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="total-breakdown">
        <div>
            <span>Subtotal Buku</span>
            <strong><?= rupiah($subtotalBarang); ?></strong>
        </div>
        <div>
            <span>Ongkir</span>
            <strong><?= rupiah($ongkir); ?></strong>
        </div>
        <div class="grand-total">
            <span>Total Pembayaran</span>
            <strong><?= rupiah($grandTotal); ?></strong>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <h3 class="payment-title">Pilih Metode Pembayaran</h3>

        <div class="payment-options">
            <?php foreach ($paymentMethods as $code => $method): ?>
                <label class="method-card">
                    <input
                        type="radio"
                        name="metode_pembayaran"
                        value="<?= htmlspecialchars($code); ?>"
                        data-type="<?= htmlspecialchars($method["type"]); ?>"
                        <?= ($_POST["metode_pembayaran"] ?? "") === $code ? "checked" : ""; ?>
                        required
                    >
                    <div>
                        <strong><?= htmlspecialchars($method["label"]); ?></strong>
                        <span><?= htmlspecialchars($method["detail"]); ?></span>
                    </div>
                </label>
            <?php endforeach; ?>
        </div>
        
        <!-- POIN 6: Input Dinamis untuk Rekening / Nomor E-Wallet -->
        <div class="ewallet-input-container" id="ewalletContainer">
            <label for="rekening_pengirim">Nomor HP E-Wallet Pengirim (Wajib)</label>
            <input type="text" name="rekening_pengirim" id="rekening_pengirim" placeholder="Contoh: 08123456789">
        </div>

        <div class="upload-box" style="margin-top:20px;">
            <label for="bukti_pembayaran">
                <strong>Upload Bukti Pembayaran</strong>
                <span>JPG, JPEG, PNG, WEBP • Maksimal 5 MB</span>
            </label>

            <input
                type="file"
                name="bukti_pembayaran"
                id="bukti_pembayaran"
                accept=".jpg,.jpeg,.png,.webp,image/*"
                required
            >

            <div id="fileName" class="file-name">
                Belum ada file dipilih
            </div>
        </div>

        <button
            type="submit"
            name="confirm_payment"
            value="1"
            class="confirm-button"
        >
            Kirim Bukti & Buat Pesanan
        </button>
    </form>

    <a href="checkout.php" class="back-link">← Ubah Alamat / Pengiriman</a>
</section>

</main>

<script>
// Menangani nama file upload
const proofInput = document.getElementById("bukti_pembayaran");
const fileName = document.getElementById("fileName");

if (proofInput && fileName) {
    proofInput.addEventListener("change", function () {
        fileName.textContent =
            this.files && this.files[0]
                ? this.files[0].name
                : "Belum ada file dipilih";
    });
}

// POIN 6: Logika menampilkan kolom e-wallet
const paymentRadios = document.querySelectorAll('input[name="metode_pembayaran"]');
const ewalletContainer = document.getElementById('ewalletContainer');
const rekeningInput = document.getElementById('rekening_pengirim');

paymentRadios.forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.dataset.type === 'ewallet') {
            ewalletContainer.style.display = 'block';
            rekeningInput.setAttribute('required', 'required');
        } else {
            ewalletContainer.style.display = 'none';
            rekeningInput.removeAttribute('required');
        }
    });
});
</script>

<!-- POIN 7: Logika Notifikasi Buble Sukses / Gagal pakai JS -->
<?php if (!empty($success_redirect)): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Bukti pembayaran berhasil dikirim.',
        showConfirmButton: false,
        timer: 2000
    }).then(() => {
        window.location.href = 'order_success.php?id=<?= $success_redirect ?>';
    });
</script>
<?php elseif ($error !== ""): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: '<?= addslashes($error) ?>',
        confirmButtonColor: '#1766f5'
    });
</script>
<?php endif; ?>

</body>
</html>