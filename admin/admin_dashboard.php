<?php
include __DIR__ . "/../db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

$productCount = $conn->query("SELECT COUNT(*) as t FROM products")->fetch_assoc()['t'];
$orderCount   = $conn->query("SELECT COUNT(*) as t FROM orders")->fetch_assoc()['t'];
$userCount    = $conn->query("SELECT COUNT(*) as t FROM users")->fetch_assoc()['t'];
$revenue      = $conn->query("SELECT SUM(total_price) as t FROM orders")->fetch_assoc()['t'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard</title>

<link rel="stylesheet" href="style.css?v=2">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
.cards{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
}
.card{
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
}
.charts{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:20px;
    margin-top:20px;
}
.chart-box{
    background:#fff;
    padding:20px;
    border-radius:12px;
}
</style>
</head>

<body>

<?php include "admin_sidebar.php"; ?>
<?php include "admin_header.php"; ?>

<div class="main-content">

<h2>Overview</h2>

<div class="cards">
    <div class="card"><h4>Revenue</h4><h2>RM <?= number_format($revenue,2) ?></h2></div>
    <div class="card"><h4>Orders</h4><h2><?= $orderCount ?></h2></div>
    <div class="card"><h4>Customers</h4><h2><?= $userCount ?></h2></div>
    <div class="card"><h4>Products</h4><h2><?= $productCount ?></h2></div>
</div>

<div class="charts">
    <div class="chart-box"><canvas id="lineChart"></canvas></div>
    <div class="chart-box"><canvas id="barChart"></canvas></div>
</div>

</div>

<!-- ✅ IMPORTANT FIX -->
<script src="https://unpkg.com/lucide@latest"></script>
<script src="admin.js"></script>

<script>
new Chart(document.getElementById("lineChart"), {
    type: 'line',
    data: {
        labels: ['Nov','Dec','Jan','Feb','Mar','Apr'],
        datasets: [{data: [28000,40000,35000,42000,48000,52000], borderColor:'#3b82f6'}]
    }
});

new Chart(document.getElementById("barChart"), {
    type: 'bar',
    data: {
        labels: ['GPU','CPU','RAM','Storage','Case'],
        datasets: [{data: [150,200,300,250,100], backgroundColor:'#3b82f6'}]
    }
});


</script>

</body>
</html>