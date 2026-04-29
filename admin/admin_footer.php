<script src="https://unpkg.com/lucide@latest"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {

    // ✅ load icons
    lucide.createIcons();

    /* =========================
       ☰ SIDEBAR TOGGLE + ANIMATION
    ========================== */
    const btn = document.getElementById("toggleSidebar");
    const sidebar = document.querySelector(".sidebar");

    if(btn && sidebar){
        btn.addEventListener("click", () => {
            sidebar.classList.toggle("collapsed");
            btn.classList.toggle("active"); // animate hamburger
        });
    }

    /* =========================
       🔔 NOTIFICATION TOGGLE
    ========================== */
    const notifBtn = document.getElementById("notifBtn");
    const notifBox = document.getElementById("notifBox");

    if(notifBtn && notifBox){
        notifBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            notifBox.style.display =
                notifBox.style.display === "block" ? "none" : "block";
        });

        // close when clicking outside
        document.addEventListener("click", () => {
            notifBox.style.display = "none";
        });
    }

    /* =========================
       🔍 LIVE SEARCH (YOUR CODE IMPROVED)
    ========================== */
    const searchInput = document.getElementById("searchInput");

    if(searchInput){
        searchInput.addEventListener("keyup", function(){
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
</script>