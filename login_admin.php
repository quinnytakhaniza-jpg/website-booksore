<?php
session_start();

if (isset($_SESSION['admin_login']) && $_SESSION['admin_login'] === true) {
    header("Location: admin_dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");

    // Login admin dummy
    if ($email === "admin@bookstore.com" && $password === "admin123") {
        $_SESSION['admin_login'] = true;
        $_SESSION['admin_name'] = "Admin BookStore";
        $_SESSION['admin_role'] = "Administrator";

        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Email atau password admin salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - BookStore</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-admin-body">

    <div class="login-admin-wrapper">
        <div class="login-admin-card">
            <div class="login-admin-logo">
                <i class="fa-solid fa-book-open"></i>
                <div>
                    <h1>BOOKSTORE APP</h1>
                    <p>Login Admin Panel</p>
                </div>
            </div>

            <h2>Masuk sebagai Admin</h2>
            <p class="login-subtitle">Silakan login untuk mengelola seluruh data BookStore.</p>

            <?php if ($error !== ""): ?>
                <div class="alert-error"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="login-admin-form">
                <div class="form-group">
                    <label>Email Admin</label>
                    <div class="input-group">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" name="email" placeholder="Masukkan email admin" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-group">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" id="adminPassword" placeholder="Masukkan password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('adminPassword', this)">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary full-btn">Login Admin</button>
            </form>
            
        </div>
    </div>

    <script src="admin.js"></script>
</body>
</html>