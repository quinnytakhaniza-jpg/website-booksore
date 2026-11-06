function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector("i");

    if (input.type === "password") {
        input.type = "text";
        icon.className = "fa-regular fa-eye-slash";
    } else {
        input.type = "password";
        icon.className = "fa-regular fa-eye";
    }
}

function showAction(message) {
    alert("Tombol diklik: " + message);
}

function toggleSidebar() {
    const sidebar = document.querySelector(".sidebar");
    if (sidebar) {
        sidebar.classList.toggle("show");
    }
}