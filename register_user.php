<?php

session_start();

require_once "koneksi.php";

$error = "";

// =====================================
// PROSES PENDAFTARAN
// =====================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama = trim($_POST["nama"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";


    // Cek semua input
    if (
        $nama === "" ||
        $email === "" ||
        $password === "" ||
        $confirmPassword === ""
    ) {

        $error = "Semua kolom wajib diisi.";

    }

    // Validasi email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Format email tidak valid.";

    }

    // Minimal password
    elseif (strlen($password) < 6) {

        $error = "Password minimal 6 karakter.";

    }

    // Password harus sama
    elseif ($password !== $confirmPassword) {

        $error = "Konfirmasi password tidak sama.";

    }

    else {

        // =====================================
        // CEK APAKAH EMAIL SUDAH TERDAFTAR
        // =====================================

        $check = $conn->prepare("
            SELECT id
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $check->execute([$email]);


        if ($check->fetch()) {

            $error = "Email sudah terdaftar. Silakan gunakan email lain.";

        }

        else {

            // =====================================
            // HASH PASSWORD
            // =====================================

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            // =====================================
            // SIMPAN KE DATABASE
            // =====================================

            $insert = $conn->prepare("
                INSERT INTO users
                (
                    nama,
                    email,
                    password,
                    role
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    'user'
                )
            ");


            $insert->execute([
                $nama,
                $email,
                $hashedPassword
            ]);


            // Setelah berhasil daftar
            // kembali ke login

            header(
                "Location: login_user.php?register=success"
            );

            exit;
        }
    }
}

?>


<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Daftar User - Nijbook
    </title>

    <!-- Pakai CSS login yang sama -->
    <link
        rel="stylesheet"
        href="login_user.css"
    >

</head>


<body>


<div class="login-page">


    <!-- =====================================
         LOGO
    ====================================== -->

    <div class="logo">

        <span class="logo-icon">
            📖
        </span>

        <span>
            Nijbook
        </span>

    </div>



    <!-- =====================================
         HEADING
    ====================================== -->

    <div class="heading">

        <h1>
            Buat Akun Baru
        </h1>

        <p>
            Daftar akun Nijbook untuk mulai menemukan
            <br>
            buku favorit dan pengalaman membaca terbaik.
        </p>

    </div>



    <!-- =====================================
         DEKORASI
    ====================================== -->

    <div class="left-decoration">

        <div class="small-books">

            <span>📘</span>
            <span>📗</span>
            <span>📙</span>

        </div>

        <div class="plant">
            🪴
        </div>

        <div class="books">
            📚
        </div>

    </div>



    <!-- =====================================
         CARD REGISTER
    ====================================== -->

    <div class="login-card">


        <!-- HEADER -->

        <div class="user-header">

            <div class="user-icon">
                👤
            </div>


            <div>

                <h2>
                    Daftar User
                </h2>

                <p>
                    Buat akun pelanggan Nijbook
                </p>

            </div>

        </div>



        <!-- =====================================
             ERROR
        ====================================== -->

        <?php if ($error !== "") : ?>

            <div class="alert-error">

                <?= htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>



        <!-- =====================================
             FORM
        ====================================== -->

        <form
            method="POST"
            action=""
            id="registerForm"
        >


            <!-- NAMA -->

            <div class="form-group">

                <label for="nama">
                    Nama Lengkap
                </label>


                <div class="input-box">

                    <span class="input-icon">
                        👤
                    </span>


                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        placeholder="Masukkan nama lengkap"
                        value="<?= htmlspecialchars($_POST["nama"] ?? ""); ?>"
                        autocomplete="name"
                        required
                    >

                </div>

            </div>



            <!-- EMAIL -->

            <div class="form-group">

                <label for="email">
                    Email
                </label>


                <div class="input-box">

                    <span class="input-icon">
                        ✉
                    </span>


                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Masukkan email Anda"
                        value="<?= htmlspecialchars($_POST["email"] ?? ""); ?>"
                        autocomplete="email"
                        required
                    >

                </div>

            </div>



            <!-- PASSWORD -->

            <div class="form-group">

                <label for="password">
                    Password
                </label>


                <div class="input-box">

                    <span class="input-icon">
                        🔒
                    </span>


                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Minimal 6 karakter"
                        autocomplete="new-password"
                        required
                    >


                    <button
                        type="button"
                        class="password-eye"
                        id="togglePassword"
                    >
                        👁
                    </button>

                </div>

            </div>



            <!-- KONFIRMASI PASSWORD -->

            <div class="form-group">

                <label for="confirmPassword">
                    Konfirmasi Password
                </label>


                <div class="input-box">

                    <span class="input-icon">
                        🔒
                    </span>


                    <input
                        type="password"
                        id="confirmPassword"
                        name="confirm_password"
                        placeholder="Ulangi password"
                        autocomplete="new-password"
                        required
                    >


                    <button
                        type="button"
                        class="password-eye"
                        id="toggleConfirmPassword"
                    >
                        👁
                    </button>

                </div>

            </div>



            <!-- PESAN JS -->

            <div
                id="passwordMessage"
                style="
                    display:none;
                    color:#dc2626;
                    font-size:13px;
                    margin-bottom:15px;
                "
            >
            </div>



            <!-- BUTTON DAFTAR -->

            <button
                type="submit"
                class="login-button"
            >
                Daftar
            </button>


        </form>



        <!-- =====================================
             SUDAH PUNYA AKUN
        ====================================== -->

        <div class="register">

            <span>
                Sudah punya akun?
            </span>

            <a href="login_user.php">
                Masuk Sekarang
            </a>

        </div>



        <!-- =====================================
             KEMBALI
        ====================================== -->

        <div class="back-home">

            <a href="home.php">

                ← &nbsp; Kembali ke

                <strong>
                    Beranda
                </strong>

            </a>

        </div>


    </div>


</div>


<script src="register_user.js"></script>


</body>

</html>