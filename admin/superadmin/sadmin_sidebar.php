<?php 
$current = basename($_SERVER['PHP_SELF']);

// assume you store role in session like:
// $_SESSION['role'] = 'admin' or 'superadmin';
$role = $_SESSION['role'] ?? 'admin';
?>

<div class="sidebar" id="sidebar">

    <div class="brand">
        <img src="storelogo.jpeg" class="logo-img">

        <div class="brand-text">
            <div class="store-name">LOZ PC Store</div>
            <div class="store-sub">Admin Console</div>
        </div>
    </div>

    <!-- DASHBOARD -->
    <a href="admin_dashboard.php" class="<?= $current=='admin_dashboard.php'?'active':'' ?>">
        <i data-lucide="layout-dashboard"></i>
        <span class="text">Overview</span>
    </a>

    <!-- PRODUCTS -->
    <a href="admin_product.php" class="<?= $current=='admin_product.php'?'active':'' ?>">
        <i data-lucide="box"></i>
        <span class="text">Products</span>
    </a>

    <!-- ORDERS -->
    <a href="admin_orders.php" class="<?= $current=='admin_orders.php'?'active':'' ?>">
        <i data-lucide="file-text"></i>
        <span class="text">Orders</span>
    </a>

    <!-- CUSTOMERS -->
    <a href="admin_profile.php" class="<?= $current=='admin_profile.php'?'active':'' ?>">
        <i data-lucide="users"></i>
        <span class="text">Customers</span>
    </a>

    <!-- SETTINGS -->
    <a href="admin_settings.php" class="<?= $current=='admin_settings.php'?'active':'' ?>">
        <i data-lucide="settings"></i>
        <span class="text">Settings</span>
    </a>

    <!-- 🔥 SUPER ADMIN ONLY -->
    <?php if($role === 'superadmin'): ?>
        <a href="admin_manage.php" class="<?= $current=='admin_manage.php'?'active':'' ?>">
            <i data-lucide="shield"></i>
            <span class="text">Admin Management</span>
        </a>
    <?php endif; ?>

</div>