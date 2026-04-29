document.addEventListener("DOMContentLoaded", function () {

    /* =========================
       ✅ LOAD LUCIDE ICONS
    ========================== */
    if (typeof lucide !== "undefined") {
        lucide.createIcons();
    }

    /* =========================
       ☰ SIDEBAR TOGGLE + ANIMATION
    ========================== */
    const toggleBtn = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener("click", function () {
            sidebar.classList.toggle("collapsed");
            toggleBtn.classList.toggle("active");
        });
    } else {
        console.warn("Sidebar toggle elements not found");
    }

    /* =========================
       🔔 NOTIFICATION TOGGLE
    ========================== */
    const notifBtn = document.getElementById("notifBtn");
    const notifBox = document.getElementById("notifBox");

    if (notifBtn && notifBox) {

        notifBtn.addEventListener("click", function (e) {
            e.stopPropagation();

            notifBox.style.display =
                notifBox.style.display === "block" ? "none" : "block";
        });

        // click outside to close
        document.addEventListener("click", function () {
            notifBox.style.display = "none";
        });

        // prevent closing when clicking inside
        notifBox.addEventListener("click", function (e) {
            e.stopPropagation();
        });
    }

    /* =========================
       🔍 LIVE SEARCH (TABLE FILTER)
    ========================== */
    const searchInput = document.getElementById("searchInput");

    if (searchInput) {
        searchInput.addEventListener("keyup", function () {
            let value = this.value.toLowerCase();
            let rows = document.querySelectorAll(".product-row");

            rows.forEach(row => {
                row.style.display =
                    row.innerText.toLowerCase().includes(value)
                        ? ""
                        : "none";
            });
        });
    }

});