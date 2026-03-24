<?php
include "../db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

// 统计数据
$total_products = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
?>

<?php include "admin_header.php"; ?>
<?php include "admin_sidebar.php"; ?>

<div class="main">

<h2>📊 Dashboard</h2>

<div class="cards">
    <div class="card">
        <h3>Total Products</h3>
        <p><?= $total_products ?></p>
    </div>

    <div class="card">
        <h3>Welcome</h3>
        <p><?= $_SESSION['admin'] ?></p>
    </div>
</div>

</div>

<style>
.main{
    margin-left:240px;
    margin-top:80px;
    padding:20px;
    color:white;
}

.cards{
    display:flex;
    gap:20px;
}

.card{
    flex:1;
    background:rgba(255,255,255,0.08);
    padding:20px;
    border-radius:15px;
    backdrop-filter:blur(10px);
}
</style>