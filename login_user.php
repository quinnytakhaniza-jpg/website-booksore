<?php
session_start();
require_once "koneksi.php";
require_once "function.php"; // Memanggil file function untuk proses (Poin 2)

if (
    isset($_SESSION["login"]) &&
    $_SESSION["login"] === true &&
    $_SESSION["role"] === "user"
) {
    header("Location: home.php");
    exit;
}

$error = "";
$success = "";
$login_status = ""; // Variabel penanda untuk memunculkan bubble JS

if (isset($_GET["register"]) && $_GET["register"] === "success") {
    $success = "Akun berhasil dibuat. Silakan login menggunakan akun Anda.";
}

// ================================
// PROSES LOGIN DIPISAH KE FUNCTION (Poin 2)
// ================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // Memanggil fungsi dari function.php
    $loginResult = loginUser($conn, $email, $password);

    if ($loginResult["status"] === "success") {
        $login_status = "success";
    } else {
        $error = $loginResult["message"];
        $login_status = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login User - Nijbook</title>
    <link rel="stylesheet" href="login_user.css">
    
    <!-- TAMBAHAN UNTUK POIN 3: Library SweetAlert untuk Bubble Notifikasi -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .admin-login-section {
            margin-top: 14px;
            text-align: center;
        }
        .admin-login-button {
            width: 100%;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid #1766f5;
            border-radius: 7px;
            background: #ffffff;
            color: #1766f5;
            text-decoration: none;
            font-size: 15px;
            font-weight: 700;
            transition: 0.2s;
        }
        .admin-login-button:hover {
            background: #edf4ff;
            box-shadow: 0 6px 16px rgba(23, 102, 245, 0.12);
        }
        .admin-login-note {
            margin-top: 8px;
            color: #7b8ba4;
            font-size: 12px;
        }
    </style>
</head>
<body>

<div class="login-page">

    <div class="logo">
        <span class="logo-icon">📖</span>
        <span>Nijbook</span>
    </div>

    <div class="heading">
        <h1>Selamat Datang!</h1>
        <p>Masuk ke akun Nijbook untuk menemukan<br>buku favorit dan pengalaman membaca terbaik.</p>
    </div>

    <div class="left-decoration">
        <div class="small-books">
            <span>📘</span><span>📗</span><span>📙</span>
        </div>
        <div class="plant">🪴</div>
        <div class="books">📚</div>
    </div>

    <div class="login-card">

        <div class="user-header">
            <div class="user-icon">👤</div>
            <div>
                <h2>Login User</h2>
                <p>Untuk pelanggan Nijbook</p>
            </div>
        </div>

        <!-- ERROR MESSAGE LAMA (Bisa dihapus jika hanya ingin bubble, tapi saya biarkan agar original) -->
        <?php if ($error !== "" && $login_status !== "error") : ?>
            <div class="alert-error"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success !== "") : ?>
            <div class="alert-success"><?= htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-box">
                    <span class="input-icon">✉</span>
                    <input type="email" name="email" id="email" placeholder="Masukkan email Anda" autocomplete="email" value="<?= htmlspecialchars($_POST["email"] ?? ""); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-box">
                    <span class="input-icon">🔒</span>
                    <input type="password" name="password" id="password" placeholder="Masukkan password Anda" autocomplete="current-password" required>
                    <button type="button" id="togglePassword" class="password-eye">👁</button>
                </div>
            </div>

            <div class="form-options">
                <label class="remember">
                    <input type="checkbox" id="rememberMe">
                    <span>Ingat saya</span>
                </label>
                <a href="#">Lupa password?</a>
            </div>

            <button type="submit" class="login-button">Masuk</button>
        </form>

        <div class="admin-login-section">
            <a href="login_admin.php" class="admin-login-button">🛡️ Login sebagai Admin</a>
            <div class="admin-login-note">Khusus untuk pengelola BookStore</div>
        </div>

        <div class="register">
            <span>Belum punya akun?</span>
            <a href="register_user.php">Daftar Sekarang</a>
        </div>

        <div class="back-home">
            <a href="home.php">← &nbsp; Kembali ke <strong>Beranda</strong></a>
        </div>

    </div>
</div>

<script src="login_user.js"></script>

<!-- TAMBAHAN UNTUK POIN 3: Javascript Bubble Trigger -->
<?php if ($login_status === "success") : ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Login Berhasil!',
        text: 'Selamat datang, <?= htmlspecialchars($_SESSION["nama"]); ?>',
        showConfirmButton: false,
        timer: 1500
    }).then(() => {
        window.location.href = 'home.php';
    });
</script>
<?php elseif ($login_status === "error") : ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal Login',
        text: '<?= $error; ?>',
        confirmButtonColor: '#1766f5'
    });
</script>
<?php endif; ?>

</body>
</html>