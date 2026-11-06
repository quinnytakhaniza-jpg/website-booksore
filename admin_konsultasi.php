<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login_admin.php");
    exit();
}

$selectedUserId = $_GET['user_id'] ?? null;

// Ambil daftar user yang pernah mengirim konsultasi
$usersQuery = $conn->query("
    SELECT DISTINCT u.id, u.nama, u.email 
    FROM users u 
    JOIN chat_konsultasi c ON u.id = c.user_id 
    ORDER BY c.id DESC
");
$listUser = $usersQuery->fetchAll(PDO::FETCH_ASSOC);

// Jika admin mengirim balasan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pesan']) && $selectedUserId) {
    $pesan = trim($_POST['pesan']);
    if (!empty($pesan)) {
        $stmt = $conn->prepare("INSERT INTO chat_konsultasi (user_id, pengirim, pesan, created_at) VALUES (?, 'admin', ?, NOW())");
        $stmt->execute([$selectedUserId, $pesan]);
        header("Location: admin_konsultasi.php?user_id=" . $selectedUserId);
        exit();
    }
}

// Ambil riwayat chat dengan user yang dipilih
$activeChats = [];
$activeUser = null;
if ($selectedUserId) {
    $stmtUser = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmtUser->execute([$selectedUserId]);
    $activeUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

    $stmtChat = $conn->prepare("SELECT * FROM chat_konsultasi WHERE user_id = ? ORDER BY id ASC");
    $stmtChat->execute([$selectedUserId]);
    $activeChats = $stmtChat->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Konsultasi Admin - Nijbook</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; padding: 20px; color: #1e293b; }
        .admin-chat-wrap { max-width: 1000px; margin: 0 auto; background: #fff; border-radius: 14px; border: 1px solid #e2e8f0; display: grid; grid-template-columns: 300px 1fr; height: 85vh; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .chat-sidebar { border-right: 1px solid #e2e8f0; background: #f8fafc; overflow-y: auto; }
        .chat-sidebar h3 { padding: 16px; font-size: 14px; font-weight: 700; border-bottom: 1px solid #e2e8f0; background: #fff; }
        .user-item { padding: 14px 16px; display: block; text-decoration: none; border-bottom: 1px solid #f1f5f9; color: #1e293b; transition: background 0.2s; }
        .user-item:hover, .user-item.active { background: #eff6ff; border-left: 4px solid #2563eb; }
        .user-item strong { display: block; font-size: 13px; font-weight: 700; margin-bottom: 2px; }
        .user-item span { font-size: 11px; color: #64748b; }
        
        .chat-main { display: flex; flex-direction: column; height: 100%; background: #fff; }
        .chat-main-header { padding: 16px 20px; border-bottom: 1px solid #e2e8f0; font-weight: 700; font-size: 14px; display: flex; justify-content: space-between; align-items: center; }
        .chat-messages { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; background: #f8fafc; }
        .bubble { max-width: 70%; padding: 10px 14px; border-radius: 12px; font-size: 13px; line-height: 1.4; }
        .bubble.user { background: #ffffff; border: 1px solid #e2e8f0; align-self: flex-start; }
        .bubble.admin { background: #2563eb; color: #fff; align-self: flex-end; }
        .bubble time { display: block; font-size: 9px; margin-top: 4px; text-align: right; opacity: 0.7; }
        .chat-reply-box { padding: 16px; border-top: 1px solid #e2e8f0; background: #fff; }
        .chat-reply-form { display: flex; gap: 10px; }
        .chat-reply-input { flex: 1; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none; }
        .btn-send { background: #2563eb; color: #fff; border: none; padding: 0 18px; border-radius: 8px; font-weight: 700; cursor: pointer; }
        .no-select { display: flex; align-items: center; justify-content: center; height: 100%; color: #94a3b8; font-size: 13px; }
    </style>
</head>
<body>

<div style="max-width: 1000px; margin: 0 auto 16px;">
    <a href="admin_dashboard.php" style="text-decoration: none; color: #2563eb; font-weight: 600; font-size: 13px;"><i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard Admin</a>
</div>

<div class="admin-chat-wrap">
    <!-- Sidebar List User -->
    <div class="chat-sidebar">
        <h3>Daftar Konsultasi User</h3>
        <?php if (!empty($listUser)) : ?>
            <?php foreach ($listUser as $u) : ?>
                <a href="admin_konsultasi.php?user_id=<?= $u['id']; ?>" class="user-item <?= ($selectedUserId == $u['id']) ? 'active' : ''; ?>">
                    <strong><?= htmlspecialchars($u['nama']); ?></strong>
                    <span><?= htmlspecialchars($u['email']); ?></span>
                </a>
            <?php endforeach; ?>
        <?php else : ?>
            <p style="padding: 20px; font-size: 12px; color: #94a3b8; text-align: center;">Belum ada user yang berkonsultasi.</p>
        <?php endif; ?>
    </div>

    <!-- Main Chat Window -->
    <div class="chat-main">
        <?php if ($activeUser) : ?>
            <div class="chat-main-header">
                <span>Chat dengan: <?= htmlspecialchars($activeUser['nama']); ?> (<?= htmlspecialchars($activeUser['email']); ?>)</span>
            </div>
            <div class="chat-messages" id="adminChatBody">
                <?php foreach ($activeChats as $ac) : ?>
                    <div class="bubble <?= $ac['pengirim']; ?>">
                        <strong><?= $ac['pengirim'] === 'user' ? $activeUser['nama'] : 'Admin'; ?></strong>
                        <p><?= nl2br(htmlspecialchars($ac['pesan'])); ?></p>
                        <time><?= date('d M H:i', strtotime($ac['created_at'])); ?></time>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="chat-reply-box">
                <form action="" method="POST" class="chat-reply-form">
                    <input type="text" name="pesan" class="chat-reply-input" placeholder="Balas pesan user..." autocomplete="off" required>
                    <button type="submit" class="btn-send"><i class="fa-solid fa-paper-plane"></i> Kirim</button>
                </form>
            </div>
        <?php else : ?>
            <div class="no-select">
                Pilih salah satu user di sebelah kiri untuk mulai konsultasi dan membalas pesan.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    const adminChatBody = document.getElementById('adminChatBody');
    if(adminChatBody) {
        adminChatBody.scrollTop = adminChatBody.scrollHeight;
    }
</script>

</body>
</html>