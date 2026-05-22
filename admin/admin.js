document.addEventListener("DOMContentLoaded", function () {

    // =========================
    // LUCIDE ICONS
    // =========================

    if (typeof lucide !== "undefined") {
        lucide.createIcons();
    }

    // =========================
    // SIDEBAR TOGGLE
    // =========================

    const toggleBtn =
    document.getElementById("toggleSidebar");

    const sidebar =
    document.getElementById("sidebar");

    if (toggleBtn && sidebar) {

        toggleBtn.addEventListener("click", function () {

            sidebar.classList.toggle("collapsed");

        });

    }

    // =========================
    // PRODUCT DROPDOWN
    // =========================

    const dropdownBtns =
    document.querySelectorAll(".dropdown-btn");

    dropdownBtns.forEach(btn => {

        btn.addEventListener("click", function () {

            this.classList.toggle("open");

            const dropdownMenu =
            this.nextElementSibling;

            dropdownMenu.classList.toggle("show");

        });

    });

    // =========================
    // NOTIFICATION
    // =========================

    const notifBtn =
    document.getElementById("notifBtn");

    const notifBox =
    document.getElementById("notifBox");

    if (notifBtn && notifBox) {

        notifBtn.addEventListener("click", function (e) {

            e.stopPropagation();

            notifBox.style.display =
                notifBox.style.display === "block"
                ? "none"
                : "block";

        });

        document.addEventListener("click", function () {

            notifBox.style.display = "none";

        });

    }

});