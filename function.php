<?php
// Function untuk mengambil array daftar buku populer
function getPopularBooks() {
    return [
        [
            'id' => 1,
            'title' => 'Laut bercerita',
            'author' => 'Leila S. Chudori',
            'rating' => 4.8,
            'price' => 99000,
            'image' => 'image/laut bercerita.jpg',
            'tag' => 'Bestseller',
            'tag_color' => 'tag-red'
        ],
        [
            'id' => 2,
            'title' => 'Cantik Itu luka',
            'author' => 'Eka Kurniawan',
            'rating' => 4.0,
            'price' => 100000,
            'image' => 'image/cantik itu luka.jpg',
            'tag' => 'Baru',
            'tag_color' => 'tag-emerald'
        ],
        [
            'id' => 3,
            'title' => 'seporsi Mie Ayam Sebelum Mati',
            'author' => 'Brian Khrisna',
            'rating' => 4.8,
            'price' => 74000,
            'image' => 'image/seporsi mie ayam sebelum.jpg',
            'tag' => null,
            'tag_color' => ''
        ],
        [
            'id' => 4,
            'title' => 'Sisi Tergelap Surga',
            'author' => 'Brian Khrisna',
            'rating' => 4.8,
            'price' => 79200,
            'image' => 'image/sisi tergelap surga.jpg',
            'tag' => null,
            'tag_color' => ''
        ],
        [
            'id' => 5,
            'title' => 'Pulang',
            'author' => 'Leila S. Chudori',
            'rating' => 5.0,
            'price' => 104000,
            'image' => 'image/pulang.jpg',
            'tag' => null,
            'tag_color' => ''
        ],
        [
            'id' => 6,
            'title' => 'Yang Telah Lama Pergi',
            'author' => 'Tere Liye',
            'rating' => 4.9,
            'price' => 94500,
            'image' => 'image/yang telah lama pergi.jpg',
            'tag' => null,
            'tag_color' => ''
        ],
        [
            'id' => 7,
            'title' => 'Teka Teki Rumah Aneh',
            'author' => 'Uketsu',
            'rating' => 4.5,
            'price' => 105825,
            'image' => 'image/teka teki rumah aneh.jpg',
            'tag' => null,
            'tag_color' => ''
        ],
        [
            'id' => 8,
            'title' => 'Tentang Kamu',
            'author' => 'Tere Liye',
            'rating' => 5.0,
            'price' => 98100,
            'image' => 'image/tentang kamu.avif',
            'tag' => null,
            'tag_color' => ''
        ]
    ];
}

// Function helper untuk format mata uang Rupiah
function formatRupiah($angka) {
    return 'Rp' . number_format($angka, 0, ',', '.');
}

// ================================
// FUNGSI PROSES LOGIN (TAMBAHAN UNTUK POIN 2)
// ================================
function loginUser($conn, $email, $password) {
    if ($email === "" || $password === "") {
        return ["status" => "error", "message" => "Email dan password wajib diisi."];
    }

    $query = $conn->prepare("
        SELECT id, nama, email, password, role
        FROM users
        WHERE email = ?
        AND role = 'user'
        LIMIT 1
    ");
    $query->execute([$email]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {
        // Mencegah session fixation
        session_regenerate_id(true);

        // Simpan data login ke session
        $_SESSION["login"] = true;
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["nama"] = $user["nama"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["role"] = $user["role"];

        return ["status" => "success", "message" => "Login berhasil!"];
    } else {
        return ["status" => "error", "message" => "Email atau password salah."];
    }
}
?>