import './bootstrap';

document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("sidebar");
    const openBtn = document.getElementById("open-sidebar");
    const closeBtn = document.getElementById("close-sidebar");

    openBtn.addEventListener("click", () => {
        sidebar.classList.remove("-translate-x-full");
    });

    closeBtn.addEventListener("click", () => {
        sidebar.classList.add("-translate-x-full");
    });
});
