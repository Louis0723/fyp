<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include __DIR__ . "/../db.php";

$count = $conn->query("SELECT COUNT(*) as t FROM orders WHERE status='Pending'")
              ->fetch_assoc()['t'];

$res = $conn->query("SELECT order_id,total_price FROM orders ORDER BY order_id DESC LIMIT 5");
?>

<header class="admin-header">

    <div class="header-left">

        <button id="toggleSidebar" class="toggle-btn">
            <i data-lucide="panel-left"></i>
        </button>

        <div class="search-box">
            <i data-lucide="search"></i>
            <input type="text" placeholder="Search...">
        </div>

    </div>

    <div class="header-right">

        <div class="notif">
            <button id="notifBtn">
                <i data-lucide="bell"></i>

                <?php if($count > 0): ?>
                    <span class="badge"><?= $count ?></span>
                <?php endif; ?>
            </button>

            <div class="notif-box" id="notifBox">
                <strong>Notifications</strong>

                <?php while($n = $res->fetch_assoc()): ?>
                    <p>Order #<?= $n['order_id'] ?> (RM <?= $n['total_price'] ?>)</p>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="avatar">
            <?= strtoupper(substr($_SESSION['admin'] ?? 'AD',0,2)) ?>
        </div>

    </div>

</header>