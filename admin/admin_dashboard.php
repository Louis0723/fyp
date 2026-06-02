<?php
include __DIR__ . "/../db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

/* =========================================
TOTAL COUNTS
========================================= */

$productCount = $conn->query("
    SELECT COUNT(*) as total
    FROM products
")->fetch_assoc()['total'];

$orderCount = $conn->query("
    SELECT COUNT(*) as total
    FROM orders
")->fetch_assoc()['total'];

$userCount = $conn->query("
    SELECT COUNT(*) as total
    FROM users
")->fetch_assoc()['total'];

$revenue = $conn->query("
    SELECT SUM(total_price) as total
    FROM orders
")->fetch_assoc()['total'] ?? 0;

/* =========================================
MONTHLY REVENUE
========================================= */

$monthLabels = [];
$monthRevenue = [];

for($i=5; $i>=0; $i--){

    $month = date("m", strtotime("-$i month"));
    $year  = date("Y", strtotime("-$i month"));

    $monthLabels[] = date("M", strtotime("-$i month"));

    $sql = $conn->query("
        SELECT SUM(total_price) as total
        FROM orders
        WHERE MONTH(created_at)='$month'
        AND YEAR(created_at)='$year'
    ");

    $row = $sql->fetch_assoc();

    $monthRevenue[] = (float)($row['total'] ?? 0);
}

/* =========================================
ORDER STATUS
========================================= */

$pending = $conn->query("
SELECT COUNT(*) as total
FROM orders
WHERE status='Pending'
")->fetch_assoc()['total'];

$processing = $conn->query("
SELECT COUNT(*) as total
FROM orders
WHERE status='Processing'
")->fetch_assoc()['total'];

$shipped = $conn->query("
SELECT COUNT(*) as total
FROM orders
WHERE status='Shipped'
")->fetch_assoc()['total'];

$completed = $conn->query("
SELECT COUNT(*) as total
FROM orders
WHERE status='Completed'
OR status='Delivered'
")->fetch_assoc()['total'];

/* =========================================
CATEGORY INVENTORY
========================================= */

$categoryLabels = [];
$categoryValues = [];

$categoryQuery = $conn->query("
    SELECT
        category,
        SUM(price * stock) as total_value
    FROM products
    GROUP BY category
");

while($cat = $categoryQuery->fetch_assoc()){

    $categoryLabels[] = $cat['category'];
    $categoryValues[] = (float)$cat['total_value'];
}

/* =========================================
TOP INVENTORY
========================================= */

$topInventory = $conn->query("
    SELECT product_name, stock
    FROM products
    ORDER BY stock DESC
    LIMIT 5
");

/* =========================================
LOW STOCK
========================================= */

$lowStock = $conn->query("
    SELECT product_name, stock
    FROM products
    WHERE stock <= 5
    ORDER BY stock ASC
    LIMIT 5
");

/* =========================================
RECENT ORDERS
========================================= */

$recentOrders = $conn->query("
    SELECT *
    FROM orders
    ORDER BY order_id DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard</title>

<link rel="stylesheet" href="style.css?v=200">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>

<style>

body{
    background:#eef2f7;
    font-family:Arial,sans-serif;
}

.main-content{
    margin-left:250px;
    padding:110px 24px 30px;
}

.sidebar.collapsed ~ .main-content{
    margin-left:90px;
}

.page-title{
    font-size:58px;
    font-weight:900;
    color:#0f172a;
}

.page-sub{
    margin-top:10px;
    color:#64748b;
    margin-bottom:30px;
}

/* CARDS */

.cards{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
    margin-bottom:24px;
}

.card{
    background:#fff;
    border-radius:24px;
    padding:24px;
    border:1px solid #e2e8f0;
}

.card-top{
    display:flex;
    justify-content:space-between;
    margin-bottom:20px;
}

.card-title{
    color:#64748b;
}

.card-icon{
    width:50px;
    height:50px;
    border-radius:16px;
    background:#eff6ff;
    display:flex;
    align-items:center;
    justify-content:center;
}

.card-value{
    font-size:42px;
    font-weight:900;
    color:#0f172a;
}

.card-growth{
    margin-top:10px;
    color:#16a34a;
    font-size:14px;
}

/* GRID */

.top-grid{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:20px;
    margin-bottom:24px;
}

.bottom-grid{
    display:grid;
    grid-template-columns:1fr 1fr 1fr;
    gap:20px;
    margin-bottom:24px;
}

.chart-card,
.inventory-card,
.status-card,
.alert-card,
.table-card{
    background:#fff;
    border-radius:24px;
    padding:24px;
    border:1px solid #e2e8f0;
}

.chart-title{
    font-size:18px;
    font-weight:800;
    margin-bottom:20px;
}

/* CHART */

.chart-wrapper{
    position:relative;
    width:100%;
    height:320px;
}

/* INVENTORY */

.inventory-item{
    margin-bottom:18px;
}

.inventory-top{
    display:flex;
    justify-content:space-between;
    margin-bottom:8px;
    font-size:14px;
}

.progress{
    width:100%;
    height:8px;
    background:#e2e8f0;
    border-radius:20px;
    overflow:hidden;
}

.progress-bar{
    height:100%;
    background:#2563eb;
}

/* ALERT */

.alert-item{
    display:flex;
    justify-content:space-between;
    margin-bottom:18px;
}

.alert-badge{
    padding:6px 12px;
    border-radius:12px;
    font-size:13px;
    font-weight:700;
    background:#fee2e2;
    color:#dc2626;
}

/* TABLE */

.table-title{
    font-size:24px;
    font-weight:800;
    margin-bottom:20px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    text-align:left;
    padding:14px;
    border-bottom:1px solid #e2e8f0;
    color:#64748b;
}

td{
    padding:14px;
    border-bottom:1px solid #f1f5f9;
}

.status{
    padding:8px 14px;
    border-radius:12px;
    font-size:13px;
    font-weight:700;
}

.pending{
    background:#fef3c7;
    color:#b45309;
}

.processing{
    background:#dbeafe;
    color:#2563eb;
}

.shipped{
    background:#dcfce7;
    color:#16a34a;
}

.completed{
    background:#dcfce7;
    color:#15803d;
}

/* FIX HEADER AVATAR */

.admin-header .avatar-btn{
    width:42px !important;
    height:42px !important;
    min-width:42px !important;
    min-height:42px !important;
    border-radius:50% !important;
    overflow:hidden !important;
    padding:0 !important;
}

.admin-header .avatar-btn img{
    width:100% !important;
    height:100% !important;
    object-fit:cover !important;
    object-position:center !important;
    border-radius:50% !important;
    display:block !important;
}

/* DROPDOWN PROFILE AVATAR */

.admin-header .profile-avatar{
    width:52px !important;
    height:52px !important;
    border-radius:50% !important;
    overflow:hidden !important;
}

.admin-header .profile-avatar img{
    width:100% !important;
    height:100% !important;
    object-fit:cover !important;
    object-position:center !important;
    border-radius:50% !important;
    display:block !important;
}

</style>

</head>

<body>

<?php include "admin_sidebar.php"; ?>
<?php include "admin_header.php"; ?>

<div class="main-content">

<div class="page-title">
Overview
</div>

<div class="page-sub">
Welcome back. Here's what's happening in your store.
</div>

<!-- CARDS -->

<div class="cards">

<div class="card">

<div class="card-top">

<div class="card-title">Revenue</div>

<div class="card-icon">
<i data-lucide="dollar-sign"></i>
</div>

</div>

<div class="card-value">
RM <?= number_format($revenue,2) ?>
</div>

<div class="card-growth">
↗ Live revenue
</div>

</div>

<div class="card">

<div class="card-top">

<div class="card-title">Orders</div>

<div class="card-icon">
<i data-lucide="shopping-cart"></i>
</div>

</div>

<div class="card-value">
<?= $orderCount ?>
</div>

<div class="card-growth">
↗ Total orders
</div>

</div>

<div class="card">

<div class="card-top">

<div class="card-title">Customers</div>

<div class="card-icon">
<i data-lucide="users"></i>
</div>

</div>

<div class="card-value">
<?= $userCount ?>
</div>

<div class="card-growth">
↗ Registered users
</div>

</div>

<div class="card">

<div class="card-top">

<div class="card-title">Products</div>

<div class="card-icon">
<i data-lucide="package"></i>
</div>

</div>

<div class="card-value">
<?= $productCount ?>
</div>

<div class="card-growth">
↗ Active products
</div>

</div>

</div>

<!-- TOP GRID -->

<div class="top-grid">

<div class="chart-card">

<div class="chart-title">
Revenue trend
</div>

<div class="chart-wrapper">
<canvas id="lineChart"></canvas>
</div>

</div>

<div class="chart-card">

<div class="chart-title">
Inventory value by category
</div>

<div class="chart-wrapper">
<canvas id="donutChart"></canvas>
</div>

</div>

</div>

<!-- BOTTOM -->

<div class="bottom-grid">

<div class="status-card">

<div class="chart-title">
Order status
</div>

<div class="chart-wrapper">
<canvas id="pieChart"></canvas>
</div>

</div>

<!-- INVENTORY -->

<div class="inventory-card">

<div class="chart-title">
Top inventory
</div>

<?php

$items = [];
$maxStock = 1;

while($row = $topInventory->fetch_assoc()){

    $items[] = $row;

    if($row['stock'] > $maxStock){
        $maxStock = $row['stock'];
    }
}

foreach($items as $row):

$width = ($row['stock'] / $maxStock) * 100;
?>

<div class="inventory-item">

<div class="inventory-top">

<span><?= $row['product_name'] ?></span>

<span><?= $row['stock'] ?> pcs</span>

</div>

<div class="progress">

<div class="progress-bar"
style="width:<?= $width ?>%">
</div>

</div>

</div>

<?php endforeach; ?>

</div>

<!-- ALERT -->

<div class="alert-card">

<div class="chart-title">
Stock alerts
</div>

<?php while($low = $lowStock->fetch_assoc()): ?>

<div class="alert-item">

<div>

<b><?= $low['product_name'] ?></b>

<br>

<small style="color:#64748b">
Low stock product
</small>

</div>

<div class="alert-badge">

<?= $low['stock'] ?> left

</div>

</div>

<?php endwhile; ?>

</div>

</div>

<!-- TABLE -->

<div class="table-card">

<div class="table-title">
Recent orders
</div>

<table>

<thead>

<tr>

<th>Order</th>
<th>Date</th>
<th>Status</th>
<th>Total</th>

</tr>

</thead>

<tbody>

<?php while($row = $recentOrders->fetch_assoc()): ?>

<tr>

<td>
ORD-<?= $row['order_id'] ?>
</td>

<td>
<?= date("Y-m-d", strtotime($row['created_at'])) ?>
</td>

<td>

<span class="status <?= strtolower($row['status']) ?>">

<?= $row['status'] ?>

</span>

</td>

<td>
RM <?= number_format($row['total_price'],2) ?>
</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

<script>

lucide.createIcons();

/* LINE CHART */

new Chart(document.getElementById('lineChart'),{

type:'line',

data:{

labels:<?= json_encode($monthLabels) ?>,

datasets:[{

label:'Revenue',

data:<?= json_encode($monthRevenue) ?>,

borderColor:'#2563eb',

backgroundColor:'rgba(37,99,235,.15)',

fill:true,

tension:.4,

pointRadius:5

}]

},

options:{

responsive:true,

maintainAspectRatio:false

}

});

/* DONUT CHART */

new Chart(document.getElementById('donutChart'),{

type:'doughnut',

data:{

labels:<?= json_encode($categoryLabels) ?>,

datasets:[{

data:<?= json_encode($categoryValues) ?>,

backgroundColor:[
'#2563eb',
'#0ea5e9',
'#14b8a6',
'#8b5cf6',
'#f59e0b',
'#ef4444',
'#22c55e'
],

borderWidth:0

}]

},

options:{

responsive:true,

maintainAspectRatio:false,

cutout:'60%'

}

});

/* PIE CHART */

new Chart(document.getElementById('pieChart'),{

type:'pie',

data:{

labels:[
'Pending',
'Processing',
'Shipped',
'Completed'
],

datasets:[{

data:[
<?= $pending ?>,
<?= $processing ?>,
<?= $shipped ?>,
<?= $completed ?>
],

backgroundColor:[
'#f59e0b',
'#2563eb',
'#0ea5e9',
'#22c55e'
],

borderWidth:0

}]

},

options:{

responsive:true,

maintainAspectRatio:false

}

});

</script>

</body>
</html>