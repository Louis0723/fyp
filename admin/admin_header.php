<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "../db.php";

/* 🔔 获取 Pending 订单数量 */
$count_sql = "SELECT COUNT(*) AS total FROM orders WHERE status='Pending'";
$count_result = $conn->query($count_sql);
$pending_count = 0;

if ($count_result && $row = $count_result->fetch_assoc()) {
    $pending_count = $row['total'];
}

/* 🔔 获取最新 5 个订单 */
$notif_sql = "SELECT order_id, total_price 
              FROM orders 
              ORDER BY order_id DESC 
              LIMIT 5";

$notif_result = $conn->query($notif_sql);
?>

<header class="admin-header">

    <!-- LEFT -->
    <div class="header-left">
        <h2>🖥️ PC Store Admin</h2>
    </div>

    <!-- RIGHT -->
    <div class="header-right">

        <!-- 🔔 Notification -->
        <div class="notification">
            <button class="notif-btn">
                🔔
                <?php if($pending_count > 0): ?>
                    <span class="notif-badge"><?= $pending_count ?></span>
                <?php endif; ?>
            </button>

            <div class="notif-dropdown">
                <strong>New Orders</strong>

                <?php if($notif_result && $notif_result->num_rows > 0): ?>
                    <?php while($n = $notif_result->fetch_assoc()): ?>
                        <p>
                            🧾 Order #<?= $n['order_id'] ?>  
                            (RM <?= number_format($n['total_price'],2) ?>)
                        </p>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No new orders</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- 👤 Admin -->
        <div class="dropdown">
            <button class="dropbtn">
                <span class="status-dot"></span>

                <span class="admin-name">
                    <?= isset($_SESSION['admin']) 
                        ? htmlspecialchars($_SESSION['admin']) 
                        : "Admin" ?>
                </span> ▼
            </button>

            <div class="dropdown-content">
                <a href="admin_profile.php">Profile</a>
                <a href="admin_logout.php">Logout</a>
            </div>
        </div>

    </div>
</header>

<style>

/* ✅ 关键：防止整体撑大 */
* {
    box-sizing: border-box;
}

/* HEADER */
.admin-header {
    background: linear-gradient(135deg, #2c3e50, #3498db);
    color: white;

    padding: 15px 30px;

    display: flex;
    justify-content: space-between;
    align-items: center;

    position: fixed;
    top: 0;
    left: 0;
    width: 100%;

    height: 70px; /* ⭐ 关键修复（不会再变大） */

    z-index: 100;
}

/* LEFT */
.header-left h2 {
    font-size: 26px;
    white-space: nowrap;
}

/* RIGHT */
.header-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

/* 🔔 NOTIFICATION */
.notification {
    position: relative;
}

.notif-btn {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: white;
    position: relative;
}

.notif-badge {
    position: absolute;
    top: -5px;
    right: -8px;
    background: red;
    color: white;
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 50%;
}

/* dropdown */
.notif-dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: 40px; /* ⭐ 防止撑高 header */
    background: #4facfe;
    border-radius: 10px;
    padding: 10px;
    width: 220px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.notif-dropdown p {
    margin: 6px 0;
    font-size: 13px;
}

.notification:hover .notif-dropdown {
    display: block;
}

/* 👤 ADMIN */
.dropdown {
    position: relative;
}

.dropbtn {
    background: none;
    border: none;
    color: white;
    cursor: pointer;

    display: flex;
    align-items: center;
    gap: 6px;

    max-width: 160px;
    overflow: hidden;
}

/* 🟢 online */
.status-dot {
    width: 8px;
    height: 8px;
    background: #22c55e;
    border-radius: 50%;
}

/* name */
.admin-name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* dropdown */
.dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    top: 40px; /* ⭐ 防止影响 header 高度 */
    background-color:#4facfe;
    min-width:150px;
    border-radius:8px;
    overflow:hidden;
}

.dropdown-content a {
    color:white;
    padding:12px;
    display:block;
    text-decoration:none;
}

.dropdown-content a:hover {
    background:#f39f86;
}

.dropdown:hover .dropdown-content {
    display:block;
}

/* 📱 responsive */
@media (max-width: 768px) {
    .header-left h2 {
        font-size: 18px;
    }

    .admin-name {
        display: none;
    }

    .notif-dropdown {
        width: 160px;
    }
}
</style>