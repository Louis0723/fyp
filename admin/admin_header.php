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
        order_id,
        total_price,
        status,
        created_at
    FROM orders
    ORDER BY order_id DESC
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

<!-- RIGHT ---->
<div class="header-right">

        <!-- NOTIFICATION -->
        <div class="notif">

            <button id="notifBtn" class="notif-btn">

                <i data-lucide="bell"></i>

                <?php if($count > 0): ?>
                    <span class="badge">
                        <?= $count ?>
                    </span>
                <?php endif; ?>

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

                <!-- LIST -->
                <div class="notif-list">

                    <?php if($notifications && $notifications->num_rows > 0): ?>

                        <?php while($n = $notifications->fetch_assoc()): ?>

                            <a href="admin_orders.php"
                               class="notif-item">

                                <div class="notif-icon pending">

                                    <i data-lucide="shopping-cart"></i>

                                </div>

                                <div class="notif-content">

                                    <div class="notif-top">

                                        <h5>
                                            Order #<?= $n['order_id'] ?>
                                        </h5>

                                    </div>

                                    <p>
                                        RM <?= number_format($n['total_price'],2) ?>
                                    </p>

                                    <small>
                                        <?= date('Y-m-d', strtotime($n['created_at'])) ?>
                                    </small>

                                </div>

                            </a>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <div class="empty-notification">

                            <i data-lucide="bell-off"></i>

                            <p>No notifications</p>

                        </div>

                    <?php endif; ?>

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

                <!-- HEADER -->
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

                <!-- MENU -->
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

    </div>

</header>

<style>

.admin-header{
    height:80px;
    background:#fff;
    border-bottom:1px solid #e5e7eb;
    display:flex;
    align-items:center;
    justify-content:space-between;
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
    top:2px;
    right:2px;
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
    background:#dbeafe;
    color:#2563eb;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
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

/* SIDEBAR */

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

/* NOTIFICATION */

const notifBtn = document.getElementById("notifBtn");
const notifBox = document.getElementById("notifBox");

notifBtn.addEventListener("click", function(e){

    e.stopPropagation();

    notifBox.classList.toggle("active");

});

/* MARK ALL */

const markAllBtn = document.getElementById("markAllBtn");

if(markAllBtn){

    markAllBtn.addEventListener("click", function(){

        const badge = document.querySelector(".badge");

        if(badge){
            badge.remove();
        }

        const unreadText = document.getElementById("unreadText");

        if(unreadText){
            unreadText.innerHTML = "0 unread";
        }

    });

}

/* CLEAR */

const clearBtn = document.getElementById("clearBtn");

if(clearBtn){

    clearBtn.addEventListener("click", function(){

        const notifList = document.querySelector(".notif-list");

        notifList.innerHTML = `
            <div class="empty-notification">

                <i data-lucide="bell-off"></i>

                <p>No notifications</p>

            </div>
        `;

        const badge = document.querySelector(".badge");

        if(badge){
            badge.remove();
        }

        const unreadText = document.getElementById("unreadText");

        if(unreadText){
            unreadText.innerHTML = "0 unread";
        }

        lucide.createIcons();

    });

}

/* PROFILE */

const profileBtn = document.getElementById("profileBtn");
const profileBox = document.getElementById("profileBox");

profileBtn.addEventListener("click", function(e){

    e.stopPropagation();

    profileBox.classList.toggle("active");

});

/* CLOSE */

document.addEventListener("click", function(e){

    if(!notifBox.contains(e.target) && !notifBtn.contains(e.target)){
        notifBox.classList.remove("active");
    }

    if(!profileBox.contains(e.target) && !profileBtn.contains(e.target)){
        profileBox.classList.remove("active");
    }

});

</script>