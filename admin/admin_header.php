<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . "/../db.php";

/* =========================================
NOTIFICATION COUNT
========================================= */

$countQuery = $conn->query("
    SELECT COUNT(*) as total 
    FROM orders 
    WHERE status='Pending'
");

$count = $countQuery->fetch_assoc()['total'];

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

$adminName  = $_SESSION['admin'] ?? 'Admin';
$adminEmail = $_SESSION['admin_email'] ?? 'admin@lozpcstore.com';

$avatar = strtoupper(substr($adminName,0,2));
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

            <!-- BUTTON -->
            <button id="notifBtn" class="notif-btn">

                <i data-lucide="bell"></i>

                <?php if($count > 0): ?>
                    <span class="badge" id="notifBadge">
                        <?= $count ?>
                    </span>
                <?php endif; ?>

            </button>

            <!-- DROPDOWN -->
            <div class="notif-box" id="notifBox">

                <!-- TOP -->
                <div class="notif-header">

                    <div>
                        <h4>Notifications</h4>

                        <span id="unreadText">
                            <?= $count ?> unread
                        </span>
                    </div>

                    <div class="notif-actions">

                        <!-- MARK ALL -->
                        <button type="button"
                                class="mark-all-btn"
                                id="markAllBtn">

                            <i data-lucide="check"></i>
                            Mark all

                        </button>

                        <!-- CLEAR -->
                        <button type="button"
                                class="clear-btn"
                                id="clearBtn">

                            <i data-lucide="trash-2"></i>
                            Clear

                        </button>

                    </div>

                </div>

                <!-- LIST -->
                <div class="notif-list" id="notifList">

                    <?php while($n = $notifications->fetch_assoc()): ?>

                        <?php

                        $status = strtolower($n['status']);

                        if($status == 'pending'){
                            $title = "New pending order";
                            $iconClass = "pending";
                            $icon = "shopping-cart";
                        }
                        elseif($status == 'processing'){
                            $title = "Processing order";
                            $iconClass = "processing";
                            $icon = "package";
                        }
                        elseif($status == 'completed'){
                            $title = "Completed order";
                            $iconClass = "completed";
                            $icon = "badge-check";
                        }
                        else{
                            $title = ucfirst($status)." order";
                            $iconClass = "default";
                            $icon = "bell";
                        }

                        ?>

                        <!-- CLICKABLE ITEM -->
                        <a href="admin_orders.php"
                           class="notif-item">

                            <!-- ICON -->
                            <div class="notif-icon <?= $iconClass ?>">

                                <i data-lucide="<?= $icon ?>"></i>

                            </div>

                            <!-- CONTENT -->
                            <div class="notif-content">

                                <div class="notif-top">

                                    <h5>
                                        <?= $title ?> #<?= $n['order_id'] ?>
                                    </h5>

                                    <span class="notif-new">
                                        New
                                    </span>

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

                </div>

            </div>

        </div>

        <!-- PROFILE -->
        <div class="profile-wrapper">

            <button class="avatar-btn" id="profileBtn">
                <?= $avatar ?>
            </button>

            <!-- PROFILE BOX -->
            <div class="profile-box" id="profileBox">

                <!-- HEADER -->
                <div class="profile-header">

                    <div class="profile-avatar">
                        <?= $avatar ?>
                    </div>

                    <div>
                        <h4><?= $adminName ?></h4>
                        <p><?= $adminEmail ?></p>
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

                    <a href="logout.php" class="logout-btn">
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
    gap:18px;
}

.header-right{
    display:flex;
    align-items:center;
    gap:18px;
    position:relative;
}

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

/* =========================================
NOTIFICATION
========================================= */

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

.notif-btn i{
    width:20px;
    height:20px;
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
    width:390px;
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
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    padding:18px;
    border-bottom:1px solid #f1f5f9;
}

.notif-header h4{
    margin:0;
    font-size:18px;
}

.notif-header span{
    font-size:13px;
    color:#6b7280;
}

.notif-actions{
    display:flex;
    gap:12px;
}

.notif-actions button{
    border:none;
    background:none;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:5px;
    font-size:13px;
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
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
}

.notif-icon.pending{
    background:#dbeafe;
    color:#2563eb;
}

.notif-icon.processing{
    background:#ede9fe;
    color:#7c3aed;
}

.notif-icon.completed{
    background:#dcfce7;
    color:#16a34a;
}

.notif-icon.default{
    background:#f3f4f6;
    color:#6b7280;
}

.notif-content{
    flex:1;
}

.notif-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.notif-top h5{
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
    width:50px;
    height:50px;
    border-radius:50%;
    background:#2563eb;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:700;
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

/* ICONS */
lucide.createIcons();

/* =========================================
SIDEBAR TOGGLE
========================================= */

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

    if(localStorage.getItem("sidebarCollapsed") === "true"){

        sidebar.classList.add("collapsed");

    }

}

/* =========================================
NOTIFICATION DROPDOWN
========================================= */

const notifBtn = document.getElementById("notifBtn");
const notifBox = document.getElementById("notifBox");

if(notifBtn){

    notifBtn.addEventListener("click", function(e){

        e.stopPropagation();

        notifBox.classList.toggle("active");

    });

}

/* =========================================
PROFILE
========================================= */

const profileBtn = document.getElementById("profileBtn");
const profileBox = document.getElementById("profileBox");

if(profileBtn){

    profileBtn.addEventListener("click", function(e){

        e.stopPropagation();

        profileBox.classList.toggle("active");

    });

}

/* =========================================
CLOSE DROPDOWN
========================================= */

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

/* =========================================
MARK ALL
========================================= */

const markAllBtn = document.getElementById("markAllBtn");

if(markAllBtn){

    markAllBtn.addEventListener("click", function(){

        let notifNew = document.querySelectorAll(".notif-new");

        notifNew.forEach(item => {

            item.innerHTML = "Read";

            item.style.color = "#16a34a";

            item.style.fontWeight = "700";

        });

        const badge = document.getElementById("notifBadge");

        if(badge){

            badge.remove();

        }

        const unreadText = document.getElementById("unreadText");

        if(unreadText){

            unreadText.innerHTML = "0 unread";

        }

    });

}

/* =========================================
CLEAR ALL
========================================= */

const clearBtn = document.getElementById("clearBtn");

if(clearBtn){

    clearBtn.addEventListener("click", function(){

        const notifList = document.getElementById("notifList");

        notifList.innerHTML = `

            <div class="empty-notification">

                <i data-lucide="bell-off"></i>

                <p>No notifications</p>

            </div>

        `;

        const badge = document.getElementById("notifBadge");

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

</script>