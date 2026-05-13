<?php $current = basename($_SERVER['PHP_SELF']); ?>

<div class="sidebar" id="sidebar">

    <div class="brand">
        <!-- LOGO -->
        <img src="../storelogo.jpeg" class="logo-img">

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
<a href="admin_customer.php" class="<?= $current=='admin_customer.php'?'active':'' ?>">
        <i data-lucide="users"></i>
        <span class="text">Customers</span>
    </a>

    <!-- LOGOUT -->
    <a href="admin_logout.php" onclick="return confirm('Logout from admin panel?')">
        <i data-lucide="log-out"></i>
        <span class="text">Logout</span>
    </a>

</div>