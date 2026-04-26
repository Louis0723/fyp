<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "../db.php";

$count = $conn->query("SELECT COUNT(*) as t FROM orders WHERE status='Pending'")
              ->fetch_assoc()['t'];

$res = $conn->query("SELECT order_id,total_price FROM orders ORDER BY order_id DESC LIMIT 5");
?>

<header class="admin-header">

    <div class="header-left">

        <!-- TOGGLE -->
       <button id="toggleSidebar" class="logo-toggle">
    <i data-lucide="panel-left"></i>
       </button>
        <!-- SEARCH -->
        <div class="search-box">
            <i data-lucide="search"></i>
            <input type="text" placeholder="Search...">
        </div>

    </div>

    <div class="header-right">

        <!-- NOTIFICATION -->
        <div class="notif">
            <button id="notifBtn">
                <i data-lucide="bell"></i>
                <?php if($count>0): ?>
                    <span class="badge"><?= $count ?></span>
                <?php endif; ?>
            </button>

            <div class="notif-box" id="notifBox">
                <strong>Notifications</strong>

                <?php while($n=$res->fetch_assoc()): ?>
                    <p>Order #<?= $n['order_id'] ?> (RM <?= $n['total_price'] ?>)</p>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- AVATAR -->
        <div class="avatar">
            <?= strtoupper(substr($_SESSION['admin'] ?? 'AD',0,2)) ?>
        </div>

    </div>
</header>

<style>
.admin-header{
    position:fixed;
    top:0;
    left:240px;
    width:calc(100% - 240px);
    height:70px;
    background:#fff;
    border-bottom:1px solid #eee;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:0 20px;
    transition:0.3s;
}

.sidebar.collapsed ~ .admin-header{
    left:70px;
    width:calc(100% - 70px);
}

/* LEFT */
.header-left{
    display:flex;
    align-items:center;
    gap:15px;
}

/* 🔥 ANIMATED MENU */
.menu-btn{
    width:28px;
    height:22px;
    border:none;
    background:none;
    cursor:pointer;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
}

.menu-btn span{
    height:3px;
    background:#333;
    border-radius:5px;
    transition:0.3s;
}

/* animate to X */
.menu-btn.active span:nth-child(1){
    transform:rotate(45deg) translate(5px,5px);
}

.menu-btn.active span:nth-child(2){
    opacity:0;
}

.menu-btn.active span:nth-child(3){
    transform:rotate(-45deg) translate(5px,-5px);
}

/* SEARCH */
.search-box{
    display:flex;
    align-items:center;
    background:#f1f5f9;
    padding:8px 12px;
    border-radius:10px;
}

.search-box input{
    border:none;
    background:none;
    outline:none;
    margin-left:8px;
}

/* NOTIF */
.notif{
    position:relative;
}

.notif button{
    border:none;
    background:none;
    cursor:pointer;
    position:relative;
}

.badge{
    position:absolute;
    top:-5px;
    right:-5px;
    background:red;
    color:#fff;
    font-size:10px;
    padding:3px 6px;
    border-radius:50%;
}

.notif-box{
    display:none;
    position:absolute;
    right:0;
    top:40px;
    width:220px;
    background:#fff;
    padding:10px;
    border-radius:10px;
    box-shadow:0 5px 20px rgba(0,0,0,0.1);
    animation:fadeIn 0.2s ease;
}

@keyframes fadeIn{
    from{opacity:0; transform:translateY(-5px);}
    to{opacity:1; transform:translateY(0);}
}

/* AVATAR */
.avatar{
    width:35px;
    height:35px;
    background:#3b82f6;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:50%;
}
</style>