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

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";
    $cartId = filter_input(
        INPUT_POST,
        "cart_id",
        FILTER_VALIDATE_INT
    );

    if ($cartId) {

        if ($action === "update") {

            $jumlah = filter_input(
                INPUT_POST,
                "jumlah",
                FILTER_VALIDATE_INT
            );

            if (!$jumlah || $jumlah < 1) {
                $jumlah = 1;
            }

            if ($jumlah > 99) {
                $jumlah = 99;
            }

            $update = $conn->prepare("
                UPDATE cart
                SET jumlah = ?
                WHERE id = ?
                AND user_id = ?
            ");

            $update->execute([
                $jumlah,
                $cartId,
                $userId
            ]);

        } elseif ($action === "delete") {

            $delete = $conn->prepare("
                DELETE FROM cart
                WHERE id = ?
                AND user_id = ?
            ");

            $delete->execute([
                $cartId,
                $userId
            ]);
        }
    }

    header("Location: cart.php");
    exit;
}

// Mengambil harga langsung dari tabel books agar tidak Rp0
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
    ORDER BY c.id DESC
");

$query->execute([$userId]);

$items = $query->fetchAll(PDO::FETCH_ASSOC);


$total = 0;
$totalQty = 0;

foreach ($items as $item) {

    $subtotal =
        ((float)$item["harga"]) *
        ((int)$item["jumlah"]);

    $total += $subtotal;
    $totalQty += (int)$item["jumlah"];
}


function rupiah($angka) {

    return "Rp" . number_format(
        (float)$angka,
        0,
        ",",
        "."
    );
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

    <title>Keranjang Belanja - Nijbook</title>

    <link rel="stylesheet" href="cart.css">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >

</head>


<body>


<header class="cart-header">

    <div class="cart-header-inner">

        <a
            href="home.php"
            class="cart-brand"
        >

            <i class="fa-solid fa-book-open"></i>

            <span>Nijbook</span>

        </a>


        <a
            href="home.php#popular-books"
            class="continue-shopping"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Lanjut Belanja

        </a>

    </div>

</header>


<main class="cart-page">


    <div class="cart-title">

        <div>

            <h1>Keranjang Belanja</h1>

            <p>
                <?= $totalQty; ?> buku di keranjang kamu
            </p>

        </div>

    </div>


    <?php if (!$items) : ?>


        <section class="empty-cart">

            <div class="empty-icon">

                <i class="fa-solid fa-bag-shopping"></i>

            </div>


            <h2>Keranjang masih kosong</h2>

            <p>
                Pilih buku favoritmu dan masukkan
                ke keranjang terlebih dahulu.
            </p>


            <a
                href="home.php#popular-books"
                class="primary-button"
            >

                Pilih Buku

            </a>

        </section>


    <?php else : ?>


        <div class="cart-layout">


            <section class="cart-list">


                <?php foreach ($items as $item) : ?>


                    <?php

                    $subtotal =
                        ((float)$item["harga"]) *
                        ((int)$item["jumlah"]);

                    ?>


                    <article class="cart-item">


                        <div class="book-placeholder">

                            <i class="fa-solid fa-book"></i>

                        </div>


                        <div class="cart-item-info">


                            <span class="item-category">

                                Buku Nijbook

                            </span>


                            <h3>

                                <?= htmlspecialchars(
                                    $item["judul_buku"]
                                ); ?>

                            </h3>


                            <div class="item-price">

                                <?= rupiah($item["harga"]); ?>

                            </div>


                            <div class="quantity-area">


                                <form
                                    method="POST"
                                    class="quantity-form"
                                >

                                    <input
                                        type="hidden"
                                        name="cart_id"
                                        value="<?= (int)$item["id"]; ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="update"
                                    >


                                    <button
                                        type="button"
                                        class="qty-button qty-minus"
                                    >
                                        -
                                    </button>


                                    <input
                                        type="number"
                                        name="jumlah"
                                        class="qty-input"
                                        value="<?= (int)$item["jumlah"]; ?>"
                                        min="1"
                                        max="99"
                                    >


                                    <button
                                        type="button"
                                        class="qty-button qty-plus"
                                    >
                                        +
                                    </button>


                                    <button
                                        type="submit"
                                        class="update-button"
                                    >
                                        Update
                                    </button>

                                </form>


                                <form method="POST">

                                    <input
                                        type="hidden"
                                        name="cart_id"
                                        value="<?= (int)$item["id"]; ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="delete"
                                    >


                                    <button
                                        type="submit"
                                        class="delete-button"
                                        onclick="
                                            return confirm(
                                                'Hapus buku ini dari keranjang?'
                                            );
                                        "
                                    >

                                        <i class="fa-solid fa-trash"></i>

                                        Hapus

                                    </button>

                                </form>


                            </div>

                        </div>


                        <div class="subtotal-area">

                            <span>Subtotal</span>

                            <strong>

                                <?= rupiah($subtotal); ?>

                            </strong>

                        </div>


                    </article>


                <?php endforeach; ?>


            </section>


            <aside class="cart-summary">


                <h2>Ringkasan Belanja</h2>


                <div class="summary-row">

                    <span>
                        Total Barang
                    </span>

                    <strong>
                        <?= $totalQty; ?>
                    </strong>

                </div>


                <div class="summary-row">

                    <span>
                        Total Harga
                    </span>

                    <strong>
                        <?= rupiah($total); ?>
                    </strong>

                </div>


                <div class="summary-divider"></div>


                <div class="summary-total">

                    <span>Total</span>

                    <strong>
                        <?= rupiah($total); ?>
                    </strong>

                </div>


                <a
                    href="checkout.php"
                    class="checkout-button"
                >

                    Lanjut Isi Alamat

                    <i class="fa-solid fa-arrow-right"></i>

                </a>


                <p class="summary-note">

                    Alamat pengiriman akan diisi
                    pada tahap selanjutnya.

                </p>


            </aside>


        </div>


    <?php endif; ?>


</main>


<!-- JavaScript Tambahan agar tombol plus minus berfungsi dinamis -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".quantity-form").forEach(form => {
        const minusBtn = form.querySelector(".qty-minus");
        const plusBtn = form.querySelector(".qty-plus");
        const input = form.querySelector(".qty-input");

        plusBtn.addEventListener("click", function() {
            let val = parseInt(input.value) || 1;
            if (val < 99) {
                input.value = val + 1;
            }
        });

        minusBtn.addEventListener("click", function() {
            let val = parseInt(input.value) || 1;
            if (val > 1) {
                input.value = val - 1;
            }
        });
    });
});
</script>

<script src="cart.js"></script>


</body>

</html>