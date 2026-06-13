<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . "/../db.php";

/* =========================================
NOTIFICATION COUNT
========================================= */

$count = 0;

$countQuery = $conn->query("
    SELECT COUNT(*) as total
    FROM admin_notifications
    WHERE is_read = 0
");

if($countQuery && $countQuery->num_rows > 0){

    $countData = $countQuery->fetch_assoc();

    $count = $countData['total'];

}

/* =========================================
NOTIFICATIONS
========================================= */

$notifications = $conn->query("
    SELECT
        oi.id,
        oi.order_id,
        oi.product_id,
        oi.quantity,
        oi.price,
        oi.review_status,
        p.product_name,
        p.image,
        o.created_at
    FROM order_items oi
    LEFT JOIN products p
        ON oi.product_id = p.product_id
    LEFT JOIN orders o
        ON oi.order_id = o.order_id
    WHERE oi.order_id > 1000000
ORDER BY oi.id DESC
LIMIT 5
");

/* =========================================
ADMIN INFO
========================================= */

$adminName   = "Admin";
$adminEmail  = "No Email";
$adminAvatar = "";

if(isset($_SESSION['admin'])){

    $username = $_SESSION['admin'];

    $adminQuery = $conn->query("
        SELECT *
        FROM admins
        WHERE username = '$username'
        LIMIT 1
    ");

    if($adminQuery && $adminQuery->num_rows > 0){

        $adminData = $adminQuery->fetch_assoc();

        $adminName  = $adminData['username'];
        $adminEmail = $adminData['email'];

        if(isset($adminData['avatar'])){
            $adminAvatar = $adminData['avatar'];
        }

    }

}

$avatarText = strtoupper(substr($adminName,0,2));
?>

<header class="admin-header">
<!-- LEFT -->
<div class="header-left">

    <button class="toggle-btn" id="toggleSidebar">
        <i data-lucide="panel-left"></i>
    </button>

</div>

<!-- RIGHT -->
<div class="header-right">

    <!-- NOTIFICATION -->
    <div class="notif">

        <button id="notifBtn" class="notif-btn">

            <i data-lucide="bell"></i>

          <span class="badge"
      id="notifBadge"
      style="<?= ($count > 0 ? '' : 'display:none;') ?>">

    <?= $count ?>

</span>

        </button>

        <!-- DROPDOWN -->
        <div class="notif-box" id="notifBox">

            <!-- HEADER -->
            <div class="notif-header">

                <div>

                    <h4>Notifications</h4>

                    <span id="unreadText">
                        <?= $count ?> unread
                    </span>

                </div>

                <div class="notif-actions">

                    <button type="button"
                            class="mark-all-btn"
                            id="markAllBtn">

                        <i data-lucide="check"></i>
                        Mark all

                    </button>

                    <button type="button"
                            class="clear-btn"
                            id="clearBtn">

                        <i data-lucide="trash-2"></i>
                        Clear

                    </button>

                </div>

            </div>

            <div class="notif-list" id="notifList">

                <div class="empty-notification">

                    <i data-lucide="loader"></i>

                    <p>Loading notifications...</p>

                </div>

            </div>

        </div>

    </div>

   <!-- PROFILE -->
<div class="profile-wrapper">

    <button class="avatar-btn" id="profileBtn">

        <?php if(!empty($adminAvatar) && $adminAvatar != "default-avatar.png"): ?>

            <img src="../uploads/admins/<?= $adminAvatar ?>">

        <?php else: ?>

            <?= $avatarText ?>

        <?php endif; ?>

    </button>

    <!-- PROFILE BOX -->
    <div class="profile-box" id="profileBox">

        <div class="profile-header">

            <div class="profile-avatar">

                <?php if(!empty($adminAvatar) && $adminAvatar != "default-avatar.png"): ?>

                    <img src="../uploads/admins/<?= $adminAvatar ?>">

                <?php else: ?>

                    <?= $avatarText ?>

                <?php endif; ?>

            </div>

            <div>

                <h4><?= htmlspecialchars($adminName) ?></h4>

                <p><?= htmlspecialchars($adminEmail) ?></p>

            </div>

        </div>

        <div class="profile-menu">

            <a href="view_profile.php">

                <i data-lucide="user"></i>

                View Profile

            </a>

            <a href="edit_profile.php">

                <i data-lucide="pencil"></i>

                Edit Profile

            </a>

            <a href="admin_logout.php" class="logout-btn">

                <i data-lucide="log-out"></i>

                Logout

            </a>

        </div>

    </div>

</div>

</header>

<style>

.admin-header{
    height:80px;
    background:#fff;
    border-bottom:1px solid #e5e7eb;
    display:flex;
    align-items:center;
    padding:0 24px;
    position:sticky;
    top:0;
    z-index:99999;
    border-radius:0 0 18px 18px;
}

.header-left{
    display:flex;
    align-items:center;
}

.header-right{
    display:flex;
    align-items:center;
    gap:18px;
    margin-left:auto;
    position:relative;
}

/* TOGGLE */

.toggle-btn{
    width:42px;
    height:42px;
    border:none;
    border-radius:12px;
    background:#fff;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    border:1px solid #e5e7eb;
}

.toggle-btn:hover{
    background:#f3f4f6;
}

/* NOTIFICATION */

.notif{
    position:relative;
}

.notif-btn{
    width:42px;
    height:42px;
    border:none;
    border-radius:50%;
    background:#fff;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    position:relative;
}

.badge{
    position:absolute;
    top:-6px;
    right:-6px;
    min-width:18px;
    height:18px;
    border-radius:50px;
    background:#ef233c;
    color:#fff;
    font-size:11px;
    font-weight:700;
    display:flex;
    align-items:center;
    justify-content:center;
}

.notif-box{
    position:absolute;
    top:60px;
    right:0;
    width:360px;
    background:#fff;
    border-radius:18px;
    border:1px solid #e5e7eb;
    box-shadow:0 10px 30px rgba(0,0,0,0.12);
    display:none;
    overflow:hidden;
    z-index:999999;
}

.notif-box.active{
    display:block;
}

.notif-header{
    padding:18px;
    border-bottom:1px solid #f1f5f9;
    display:flex;
    align-items:center;
    justify-content:space-between;
}

.notif-header h4{
    font-size:18px;
}

.notif-header span{
    font-size:13px;
    color:#6b7280;
}

.notif-actions{
    display:flex;
    gap:10px;
}

.notif-actions button{
    border:none;
    background:none;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:5px;
    font-size:13px;
    color:#475569;
    font-weight:600;
    transition:.2s;
}

.notif-actions button:hover{
    color:#2563eb;
}

.notif-list{
    max-height:450px;
    overflow-y:auto;
}

.notif-item{
    display:flex;
    gap:14px;
    padding:18px;
    border-bottom:1px solid #f1f5f9;
    text-decoration:none;
    color:#111827;
    transition:.2s;
}

.notif-item:hover{
    background:#f8fafc;
}

.notif-icon{
    width:44px;
    height:44px;
    border-radius:50%;
    overflow:hidden;
    flex-shrink:0;
    background:#dbeafe;
}

.notif-icon img{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}

.notif-content{
    flex:1;
}

.notif-content h5{
    margin:0;
    font-size:15px;
}

.notif-content p{
    margin:5px 0;
    font-size:14px;
    color:#4b5563;
}

.notif-content small{
    color:#9ca3af;
}

/* PROFILE */

.profile-wrapper{
    position:relative;
    z-index:999999;
}

.avatar-btn{
    width:42px;
    height:42px;
    border:none;
    border-radius:50%;
    background:#2563eb;
    color:#fff;
    font-weight:700;
    cursor:pointer;
    overflow:hidden;
    display:flex;
    align-items:center;
    justify-content:center;
}

.avatar-btn img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.profile-box{
    position:absolute;
    top:60px;
    right:0;
    width:280px;
    background:#fff;
    border-radius:16px;
    border:1px solid #e5e7eb;
    box-shadow:0 10px 30px rgba(0,0,0,0.12);
    display:none;
    z-index:999999;
}

.profile-box.active{
    display:block;
}

.profile-header{
    display:flex;
    gap:14px;
    align-items:center;
    padding:18px;
    border-bottom:1px solid #f1f5f9;
}

.profile-avatar{
    width:52px;
    height:52px;
    border-radius:50%;
    background:#2563eb;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:700;
    overflow:hidden;
}

.profile-avatar img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.profile-menu{
    padding:8px;
}

.profile-menu a{
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px 14px;
    border-radius:12px;
    text-decoration:none;
    color:#111827;
}

.profile-menu a:hover{
    background:#f3f4f6;
}

.logout-btn{
    color:#dc2626 !important;
}

.empty-notification{
    padding:40px;
    text-align:center;
    color:#9ca3af;
}

.empty-notification i{
    width:40px;
    height:40px;
    margin-bottom:10px;
}

</style>

<script>

lucide.createIcons();

/* ==========================
   SIDEBAR
========================== */

const toggleSidebar = document.getElementById("toggleSidebar");
const sidebar = document.getElementById("sidebar");

if(toggleSidebar && sidebar){

    toggleSidebar.addEventListener("click", () => {

        sidebar.classList.toggle("collapsed");

        localStorage.setItem(
            "sidebarCollapsed",
            sidebar.classList.contains("collapsed")
        );

    });

}

/* ==========================
   NOTIFICATION DROPDOWN
========================== */

const notifBtn = document.getElementById("notifBtn");
const notifBox = document.getElementById("notifBox");

if(notifBtn && notifBox){

    notifBtn.addEventListener("click", function(e){

        e.stopPropagation();

        notifBox.classList.toggle("active");

    });

}

/* ==========================
   MARK ALL
========================== */

const markAllBtn = document.getElementById("markAllBtn");

if(markAllBtn){

markAllBtn.addEventListener("click", function(){

fetch("mark_notifications.php")

    .then(response => response.json())

    .then(data => {

        if(data.success){

            const badge = document.getElementById("notifBadge");

            badge.style.display = "none";
            badge.innerText = "0";

            document.getElementById("unreadText").innerHTML = "0 unread";

        }

    });

});

}

/* ==========================
   CLEAR
========================== */

const clearBtn = document.getElementById("clearBtn");

if(clearBtn){

   clearBtn.addEventListener("click", function(){

  fetch("clear_notifications.php")

    .then(response => response.json())

    .then(data => {

        if(data.success){

            document.getElementById("notifList").innerHTML = `
                <div class="empty-notification">

                    <i data-lucide="bell-off"></i>

                    <p>No notifications</p>

                </div>
            `;

            document.getElementById("notifBadge").style.display = "none";

            document.getElementById("notifBadge").innerText = "0";

            document.getElementById("unreadText").innerHTML = "0 unread";

            lucide.createIcons();

        }

    });

});
}

/* ==========================
   REAL-TIME NOTIFICATIONS
========================== */

let lastOrder = null;

function loadNotifications(){

    fetch('get_notifications.php')

.then(response => {

    if(!response.ok){

        throw new Error(
            "HTTP Error: " + response.status
        );

    }

    return response.json();

})

.then(data => {
        let html = '';

        /* ===== UPDATE BADGE ===== */

        const badge = document.getElementById("notifBadge");
        const unreadText = document.getElementById("unreadText");

       const total = data.filter(n => n.is_read == 0).length;

        if(total > 0){

            badge.style.display = "flex";
            badge.innerText = total;

            unreadText.innerText = total + " unread";

        }else{

            badge.style.display = "none";

            unreadText.innerText = "0 unread";

        }

        /* ===== PLAY SOUND ===== */

        if(data.length > 0){

            if(
                lastOrder !== null &&
                lastOrder != data[0].order_id
            ){

                new Audio(
                    "../assets/notification.mp3"
                ).play();

            }

            lastOrder = data[0].order_id;

        }

        /* ===== NOTIFICATION LIST ===== */

        if(data.length === 0){

            html = `
                <div class="empty-notification">

                    <i data-lucide="bell-off"></i>

                    <p>No notifications</p>

                </div>
            `;

        }else{

            data.forEach(n => {

                html += `
                    <a href="admin_orders.php"
                       class="notif-item">

                        <div class="notif-icon">

                            <img
                                src="${n.image}"
                                onerror="this.src='../assets/images/no-image.png';"
                            >

                        </div>

                        <div class="notif-content">

                            <h5>

                                Order #${n.order_id}

                            </h5>

                            <p>

                                ${n.product_name}

                            </p>

                            <p>

                                Qty: ${n.quantity}

                                •
                                RM ${parseFloat(n.price).toFixed(2)}

                            </p>

                            <small>

                                ${n.created_at}

                            </small>

                        </div>

                    </a>
                `;

            });

        }

        document.getElementById("notifList").innerHTML = html;

        lucide.createIcons();

    })

    .catch(error => {

        console.error(
            "Notification Error:",
            error
        );

    });

}

/* ==========================
   PROFILE
========================== */

const profileBtn = document.getElementById("profileBtn");
const profileBox = document.querySelector(".profile-box");

if(profileBtn && profileBox){

    profileBtn.onclick = function(e){

        e.stopPropagation();

        profileBox.classList.toggle("active");

    };

}

/* ==========================
   CLOSE DROPDOWNS
========================== */

document.addEventListener("click", function(e){

    if(
        notifBox &&
        notifBtn &&
        !notifBox.contains(e.target) &&
        !notifBtn.contains(e.target)
    ){

        notifBox.classList.remove("active");

    }

    if(
        profileBox &&
        profileBtn &&
        !profileBox.contains(e.target) &&
        !profileBtn.contains(e.target)
    ){

        profileBox.classList.remove("active");

    }

});

/* ==========================
   START REAL-TIME
========================== */

loadNotifications();

/* Check every 3 seconds */
setInterval(loadNotifications, 3000);

</script>