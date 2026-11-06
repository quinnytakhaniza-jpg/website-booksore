<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || $_SESSION['role'] !== 'user') {
    exit();
}

$userId = $_SESSION['user_id'];

// Proses kirim pesan chat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pesan_chat'])) {
    $pesan = trim($_POST['pesan_chat']);
    if (!empty($pesan)) {
        $stmt = $conn->prepare("INSERT INTO chat_konsultasi (user_id, pengirim, pesan, created_at) VALUES (?, 'user', ?, NOW())");
        $stmt->execute([$userId, $pesan]);
        exit('success');
    }
}

// Ambil riwayat chat
$stmtChat = $conn->prepare("SELECT * FROM chat_konsultasi WHERE user_id = ? ORDER BY id ASC");
$stmtChat->execute([$userId]);
$chats = $stmtChat->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Tombol Floating Customer Service -->
<div id="csFloatWrapper">
  <button id="csToggleButton" title="Bantuan Customer Service">
    <i class="fa-solid fa-headset"></i>
    <span>CS Admin</span>
  </button>

  <!-- Jendela Box Chat -->
  <div id="csChatBox" style="display: none;">
    <div class="cs-chat-header">
      <div style="display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-circle" style="font-size: 8px; color: #22c55e;"><span></span></i>
        <strong>Live Support Admin</strong>
      </div>
      <button id="csCloseBtn">&times;</button>
    </div>

    <div class="cs-chat-body" id="csChatBody">
      <?php if (!empty($chats)) : ?>
        <?php foreach ($chats as $c) : ?>
          <div class="cs-bubble <?= $c['pengirim']; ?>">
            <p><?= nl2br(htmlspecialchars($c['pesan'])); ?></p>
            <span class="cs-time"><?= date('H:i', strtotime($c['created_at'])); ?></span>
          </div>
        <?php endforeach; ?>
      <?php else : ?>
        <div style="text-align: center; color: #94a3b8; font-size: 12px; margin-top: 40px;">
          <i class="fa-solid fa-comments" style="font-size: 28px; margin-bottom: 6px; display: block; color: #cbd5e1;"></i>
          Halo! Ada yang bisa kami bantu? Silakan kirim pesan ke admin.
        </div>
      <?php endif; ?>
    </div>

    <div class="cs-chat-footer">
      <form id="csChatForm">
        <input type="text" id="csInputPesan" placeholder="Ketik pesan..." autocomplete="off" required>
        <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
      </form>
    </div>
  </div>
</div>

<style>
  #csFloatWrapper {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 99999;
    font-family: 'Plus Jakarta Sans', sans-serif;
  }
  #csToggleButton {
    background-color: #2563eb;
    color: #ffffff;
    border: none;
    padding: 12px 20px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
    display: flex;
    align-items: center;
    gap: 8px;
    transition: transform 0.2s ease, background-color 0.2s;
  }
  #csToggleButton:hover {
    background-color: #1d4ed8;
    transform: scale(1.05);
  }

  #csChatBox {
    position: absolute;
    bottom: 65px;
    right: 0;
    width: 340px;
    height: 440px;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    border: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: fadeInScale 0.2s ease forwards;
  }
  @keyframes fadeInScale {
    from { opacity: 0; transform: translateY(10px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
  }

  .cs-chat-header {
    background: #1e1e1e;
    color: #ffffff;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 13px;
  }
  #csCloseBtn {
    background: none;
    border: none;
    color: #94a3b8;
    font-size: 20px;
    cursor: pointer;
  }
  #csCloseBtn:hover { color: #fff; }

  .cs-chat-body {
    flex: 1;
    padding: 14px;
    overflow-y: auto;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
  .cs-bubble {
    max-width: 80%;
    padding: 10px 14px;
    border-radius: 12px;
    font-size: 12px;
    line-height: 1.4;
    word-break: break-word;
  }
  .cs-bubble.user {
    background: #2563eb;
    color: #ffffff;
    align-self: flex-end;
    border-bottom-right-radius: 2px;
  }
  .cs-bubble.admin {
    background: #ffffff;
    color: #1e293b;
    align-self: flex-start;
    border-bottom-left-radius: 2px;
    border: 1px solid #e2e8f0;
  }
  .cs-time {
    font-size: 9px;
    display: block;
    text-align: right;
    margin-top: 3px;
    opacity: 0.7;
  }

  .cs-chat-footer {
    padding: 12px;
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
  }
  #csChatForm {
    display: flex;
    gap: 8px;
  }
  #csInputPesan {
    flex: 1;
    padding: 9px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 12px;
    outline: none;
  }
  #csInputPesan:focus { border-color: #2563eb; }
  #csChatForm button {
    background: #2563eb;
    color: #fff;
    border: none;
    padding: 0 14px;
    border-radius: 8px;
    cursor: pointer;
  }
  #csChatForm button:hover { background: #1d4ed8; }
</style>

<script>
  const csToggle = document.getElementById('csToggleButton');
  const csBox = document.getElementById('csChatBox');
  const csClose = document.getElementById('csCloseBtn');
  const csBody = document.getElementById('csChatBody');
  const csForm = document.getElementById('csChatForm');
  const csInput = document.getElementById('csInputPesan');

  csToggle.addEventListener('click', () => {
    csBox.style.display = csBox.style.display === 'none' ? 'flex' : 'none';
    csBody.scrollTop = csBody.scrollHeight;
  });
  csClose.addEventListener('click', () => {
    csBox.style.display = 'none';
  });

  csForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const pesan = csInput.value.trim();
    if (!pesan) return;

    const formData = new FormData();
    formData.append('pesan_chat', pesan);

    fetch('live_chat.php', {
      method: 'POST',
      body: formData
    }).then(res => res.text()).then(data => {
      if(data === 'success') {
        // Tambah gelembung chat secara instan
        const bubble = document.createElement('div');
        bubble.className = 'cs-bubble user';
        bubble.innerHTML = `<p>${escapeHtml(pesan)}</p><span class="cs-time">Baru saja</span>`;
        csBody.appendChild(bubble);
        csInput.value = '';
        csBody.scrollTop = csBody.scrollHeight;
      }
    });
  });

  function escapeHtml(text) {
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
  }
</script>