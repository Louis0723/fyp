document.addEventListener("DOMContentLoaded", function () {

    if (typeof lucide !== "undefined") {
        lucide.createIcons();
    }

    const toggleBtn = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener("click", function () {

            sidebar.classList.toggle("collapsed");

            // ✅ FORCE layout sync
            document.body.classList.toggle("sidebar-collapsed");

        });
    }

    const notifBtn = document.getElementById("notifBtn");
    const notifBox = document.getElementById("notifBox");

    if (notifBtn && notifBox) {

        notifBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            notifBox.style.display =
                notifBox.style.display === "block" ? "none" : "block";
        });

        document.addEventListener("click", function () {
            notifBox.style.display = "none";
        });

        notifBox.addEventListener("click", function (e) {
            e.stopPropagation();
        });
    }

});