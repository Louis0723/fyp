<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <h3>⚙️ Admin</h3>

    <a href="admin_dashboard.php"
    class="<?= $current_page=='admin_dashboard.php'?'active':'' ?>">
    📊 Dashboard</a>

    <a href="admin_profile.php"
    class="<?= $current_page=='admin_profile.php'?'active':'' ?>">
    👤 Profile</a>

    <a href="admin_product.php"
    class="<?= $current_page=='admin_product.php'?'active':'' ?>">
    📦 View Products</a>

    <a href="add_product.php"
    class="<?= $current_page=='add_product.php'?'active':'' ?>">
    ➕ Add Product</a>

    <!-- 新增 View Orders -->
    <a href="admin_orders.php"
    class="<?= $current_page=='admin_orders.php'?'active':'' ?>">
    🧾 View Orders</a>
</div>

<style>
.sidebar{
    position:fixed;
    top:60px;
    left:0;
    width:230px;
    height:100%;
    background:linear-gradient(180deg,#0f2027,#203a43);
    padding:20px;
    box-shadow:2px 0 20px rgba(0,0,0,0.5);
}

.sidebar h3{
    color:#fff;
    margin-bottom:20px;
}

.sidebar a{
    display:block;
    color:#ccc;
    padding:12px;
    border-radius:10px;
    margin-bottom:10px;
    text-decoration:none;
    transition:0.3s;
}

.sidebar a:hover{
    background:#00c6ff;
    color:#fff;
    transform:translateX(5px);
}

.sidebar a.active{
    background:linear-gradient(90deg,#00c6ff,#0072ff);
    color:#fff;
}
</style>