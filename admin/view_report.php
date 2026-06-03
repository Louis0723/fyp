<?php
include "../db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

/* =========================
DATE RANGE FILTER
========================= */

$where = "";

if(isset($_GET['range'])){

    $range = $_GET['range'];

    if($range == "7"){

        $where =
        "WHERE DATE(created_at)
        >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";

    }
    elseif($range == "30"){

        $where =
        "WHERE DATE(created_at)
        >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";

    }
    elseif($range == "90"){

        $where =
        "WHERE DATE(created_at)
        >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)";

    }
    else{

        $where = "";

    }

}
elseif(
    isset($_GET['start_date']) &&
    isset($_GET['end_date'])
){

    $start = $_GET['start_date'];
    $end   = $_GET['end_date'];

    $where =
    "WHERE DATE(created_at)
    BETWEEN '$start' AND '$end'";
}
else{

    $today = date('Y-m-d');

    $where =
    "WHERE DATE(created_at) = '$today'";
}

/* =========================
MAIN STATS
========================= */

// Revenue
$revenue_query = mysqli_query($conn,"
SELECT SUM(total_price) as revenue
FROM orders
$where
");

$revenue = mysqli_fetch_assoc($revenue_query)['revenue'] ?? 0;

// Orders
$order_query = mysqli_query($conn,"
SELECT COUNT(*) as total
FROM orders
$where
");

$total_orders = mysqli_fetch_assoc($order_query)['total'] ?? 0;

// Customers
$customer_query = mysqli_query($conn,"
SELECT COUNT(DISTINCT user_id) as total
FROM orders
$where
");

$total_customers = mysqli_fetch_assoc($customer_query)['total'] ?? 0;

// Products
$product_query = mysqli_query($conn,"
SELECT COUNT(*) as total
FROM products
");

$total_products = mysqli_fetch_assoc($product_query)['total'] ?? 0;

// Average Order
$avg_order = $total_orders > 0
? $revenue / $total_orders
: 0;

/* =========================
ORDER STATUS
========================= */

$status_result = mysqli_query($conn,"
SELECT status, COUNT(*) as total
FROM orders
$where
GROUP BY status
");

$status_data = [];

while($row = mysqli_fetch_assoc($status_result)){
    $status_data[$row['status']] = $row['total'];
}

/* =========================
INVENTORY
========================= */

$inventory_query = mysqli_query($conn,"
SELECT 
    category,
    SUM(stock) as units,
    SUM(price * stock) as value
FROM products
GROUP BY category
");

/* =========================
TOP PRODUCTS
========================= */

$top_products_query = mysqli_query($conn,"
SELECT 
    product_name,
    (price * stock) as value
FROM products
ORDER BY value DESC
LIMIT 5
");

/* =========================
TOP CUSTOMERS
========================= */

$customer_top_query = mysqli_query($conn,"
SELECT 
    u.name,
    COUNT(o.order_id) as orders_count,
    SUM(o.total_price) as spent
FROM orders o
LEFT JOIN users u ON o.user_id = u.user_id
GROUP BY o.user_id
ORDER BY spent DESC
LIMIT 5
");

/* =========================
LOW STOCK
========================= */

$low_stock = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) as total
FROM products
WHERE stock <= 5
"))['total'];

$out_stock = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) as total
FROM products
WHERE stock = 0
"))['total'];

?>

<!DOCTYPE html>
<html>

<head>

<title>Business Report</title>

<link rel="stylesheet" href="style.css?v=100">

<style>

body{
    background:#eef2f7;
    font-family:Segoe UI;
}

/* MAIN */

.main-content{
    margin-left:270px;
    margin-top:95px;
    padding:30px;
}

.sidebar.collapsed ~ .main-content{
    margin-left:95px;
}

/* TITLE */

.page-title{
    font-size:42px;
    font-weight:800;
    color:#111827;
    margin-bottom:25px;
}

/* FILTER */

.report-filter-card{
    background:#fff;
    border-radius:24px;
    padding:28px;
    margin-bottom:30px;
    box-shadow:0 8px 25px rgba(0,0,0,.05);
}

.report-filter-title{
    font-size:28px;
    font-weight:800;
    color:#111827;
    margin-bottom:20px;
}

.quick-filter{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    margin-bottom:24px;
}

.quick-btn{
    height:46px;
    padding:0 20px;
    border-radius:14px;
    border:none;
    background:#f1f5f9;
    color:#111827;
    font-weight:700;
    text-decoration:none;
    display:flex;
    align-items:center;
    justify-content:center;
}

.quick-btn.active{
    background:#2563eb;
    color:#fff;
}

.custom-filter{
    display:flex;
    align-items:flex-end;
    gap:16px;
    flex-wrap:wrap;
}

.date-group{
    display:flex;
    flex-direction:column;
    gap:8px;
}

.date-group label{
    font-size:14px;
    font-weight:700;
    color:#64748b;
}

.date-group input{
    width:220px;
    height:52px;
    border:none;
    border-radius:14px;
    padding:0 18px;
    background:#f8fafc;
    font-size:15px;
    box-shadow:0 4px 15px rgba(0,0,0,.05);
}

.btn-report{
    height:52px;
    border:none;
    padding:0 24px;
    border-radius:14px;
    background:#2563eb;
    color:#fff;
    font-weight:700;
    cursor:pointer;
    font-size:15px;
}

.clear-btn{
    height:52px;
    padding:0 18px;
    display:flex;
    align-items:center;
    text-decoration:none;
    color:#111827;
    font-weight:700;
}

/* REPORT */

.report-box{
    background:#fff;
    border-radius:24px;
    padding:40px;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
}

/* HEADER */

.report-header{
    background:#0f172a;
    border-radius:18px;
    padding:30px 35px;
    color:#fff;
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    margin-bottom:35px;
    border-bottom:5px solid #38bdf8;
}

.report-left h2{
    font-size:38px;
    font-weight:900;
    margin-bottom:10px;
    text-transform:uppercase;
}

.report-left p{
    font-size:14px;
    color:#cbd5e1;
    line-height:1.8;
}

.report-right{
    text-align:right;
}

.report-right h1{
    font-size:48px;
    font-weight:900;
    margin-bottom:8px;
}

.report-right span{
    color:#cbd5e1;
    font-size:14px;
}

/* STATS */

.stats-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:18px;
    margin-bottom:35px;
}

.stat-card{
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:14px;
    padding:22px;
}

.stat-label{
    font-size:12px;
    font-weight:700;
    color:#64748b;
    text-transform:uppercase;
    margin-bottom:10px;
}

.stat-value{
    font-size:34px;
    font-weight:900;
    color:#0f172a;
}

/* TITLE */

.section-title{
    font-size:28px;
    font-weight:900;
    color:#0f172a;
    margin-top:28px;
    margin-bottom:16px;
    border-bottom:3px solid #38bdf8;
    padding-bottom:8px;
}

/* TABLE */

.report-table{
    width:100%;
    border-collapse:collapse;
    margin-bottom:35px;
}

.report-table th{
    background:#0f172a;
    color:#fff;
    padding:14px;
    font-size:13px;
    text-transform:uppercase;
    text-align:left;
}

.report-table td{
    padding:12px 14px;
    border-bottom:1px solid #e5e7eb;
    font-size:14px;
}

.report-table tr:nth-child(even){
    background:#f8fafc;
}

/* STATUS */

.status-list{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:12px;
    margin-bottom:35px;
}

.status-badge{
    background:#f8fafc;
    border:1px solid #dbeafe;
    border-radius:10px;
    padding:14px 18px;
    font-weight:700;
}

/* ALERT */

.stock-alert{
    background:#f8fafc;
    border-left:5px solid #2563eb;
    padding:18px;
    border-radius:10px;
    font-size:16px;
    font-weight:700;
}

/* BUTTONS */

.action-buttons{
    display:flex;
    justify-content:flex-end;
    gap:14px;
    margin-top:30px;
}

.btn-print{
    border:none;
    border-radius:14px;
    padding:14px 26px;
    font-weight:700;
    cursor:pointer;
    font-size:15px;
    background:#111827;
    color:#fff;
}

/* PRINT */

@media print{

@page{
    size:A4;
    margin:16mm;
}

.sidebar,
.top-header,
.report-filter-card,
.page-title,
.action-buttons{
    display:none !important;
}

body{
    background:#fff;
    print-color-adjust:exact;
    -webkit-print-color-adjust:exact;
}

.main-content{
    margin:0 !important;
    padding:0 !important;
}

.report-box{
    box-shadow:none;
    border-radius:0;
    padding:0;
}

}

/* RESPONSIVE */

@media(max-width:1000px){

.stats-grid{
    grid-template-columns:1fr 1fr;
}

}

@media(max-width:700px){

.stats-grid{
    grid-template-columns:1fr;
}

.status-list{
    grid-template-columns:1fr;
}

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

<?php
if(isset($_SESSION['role']) &&
$_SESSION['role']=="super_admin"){

    include "sadmin_sidebar.php";

}else{

    include "admin_sidebar.php";
}
?>

<div class="top-header">
<?php include "admin_header.php"; ?>
</div>

<div class="main-content">

<div class="page-title">
View Report
</div>

<!-- FILTER -->

<div class="report-filter-card">

<div class="report-filter-title">
Report period
</div>

<div class="quick-filter">

<a href="?range=all"
class="quick-btn <?= (isset($_GET['range']) && $_GET['range']=='all') ? 'active' : '' ?>">
All time
</a>

<a href="?range=7"
class="quick-btn <?= (isset($_GET['range']) && $_GET['range']=='7') ? 'active' : '' ?>">
Last 7 days
</a>

<a href="?range=30"
class="quick-btn <?= (isset($_GET['range']) && $_GET['range']=='30') ? 'active' : '' ?>">
Last 30 days
</a>

<a href="?range=90"
class="quick-btn <?= (isset($_GET['range']) && $_GET['range']=='90') ? 'active' : '' ?>">
Last 90 days
</a>

</div>

<form method="GET" class="custom-filter">

<div class="date-group">
<label>From</label>

<input
type="date"
name="start_date"
value="<?= $_GET['start_date'] ?? '' ?>"
required>
</div>

<div class="date-group">
<label>To</label>

<input
type="date"
name="end_date"
value="<?= $_GET['end_date'] ?? '' ?>"
required>
</div>

<button class="btn-report">
Generate
</button>

<a href="view_report.php"
class="clear-btn">
Clear
</a>

</form>

</div>

<!-- REPORT -->

<div class="report-box">

<!-- HEADER -->

<div class="report-header">

<div class="report-left">

<h2>
LOZ PC STORE
</h2>

<p>

Business Performance Report

<br>

Generated:
<?= date("n/j/Y, g:i:s A") ?>

<br>

<?php

if(isset($_GET['range'])){

    if($_GET['range'] == 'all'){

        echo "Period: All Time";

    }else{

        echo "Period: Last ".$_GET['range']." Days";
    }

}
elseif(isset($_GET['start_date'])){

    echo "Period: ".
    $_GET['start_date'].
    " to ".
    $_GET['end_date'];

}
?>

</p>

</div>

<div class="report-right">

<h1>REPORT</h1>

<span>
LOZ PC STORE SYSTEM
</span>

</div>

</div>

<!-- STATS -->

<div class="stats-grid">

<div class="stat-card">
<div class="stat-label">Total Revenue</div>
<div class="stat-value">
RM <?= number_format($revenue,0) ?>
</div>
</div>

<div class="stat-card">
<div class="stat-label">Orders</div>
<div class="stat-value">
<?= $total_orders ?>
</div>
</div>

<div class="stat-card">
<div class="stat-label">Avg. Order</div>
<div class="stat-value">
RM <?= number_format($avg_order,0) ?>
</div>
</div>

<div class="stat-card">
<div class="stat-label">Customers</div>
<div class="stat-value">
<?= $total_customers ?>
</div>
</div>

<div class="stat-card">
<div class="stat-label">Products</div>
<div class="stat-value">
<?= $total_products ?>
</div>
</div>

<div class="stat-card">
<div class="stat-label">Avg. Rating</div>
<div class="stat-value">
4.43 / 5
</div>
</div>

</div>

<!-- STATUS -->

<div class="section-title">
Order Status Breakdown
</div>

<div class="status-list">

<?php
$statuses = ['Pending','Processing','Shipped','Delivered','Cancelled'];

foreach($statuses as $status):

$count = $status_data[$status] ?? 0;
?>

<div class="status-badge">
<?= $status ?> : <?= $count ?>
</div>

<?php endforeach; ?>

</div>

<!-- INVENTORY -->

<div class="section-title">
Inventory by Category
</div>

<table class="report-table">

<thead>

<tr>
<th>Category</th>
<th>Units</th>
<th>Value</th>
</tr>

</thead>

<tbody>

<?php while($inv = mysqli_fetch_assoc($inventory_query)): ?>

<tr>

<td>
<?= htmlspecialchars($inv['category']) ?>
</td>

<td>
<?= $inv['units'] ?>
</td>

<td>
RM <?= number_format($inv['value'],2) ?>
</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

<!-- TOP PRODUCTS -->

<div class="section-title">
Top Products by Inventory Value
</div>

<table class="report-table">

<thead>

<tr>
<th>Product</th>
<th>Value</th>
</tr>

</thead>

<tbody>

<?php while($product = mysqli_fetch_assoc($top_products_query)): ?>

<tr>

<td>
<?= htmlspecialchars($product['product_name']) ?>
</td>

<td>
RM <?= number_format($product['value'],2) ?>
</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

<!-- CUSTOMERS -->

<div class="section-title">
Top Customers by Spend
</div>

<table class="report-table">

<thead>

<tr>
<th>Customer</th>
<th>Orders</th>
<th>Spent</th>
</tr>

</thead>

<tbody>

<?php while($top = mysqli_fetch_assoc($customer_top_query)): ?>

<tr>

<td>
<?= htmlspecialchars($top['name']) ?>
</td>

<td>
<?= $top['orders_count'] ?>
</td>

<td>
RM <?= number_format($top['spent'],2) ?>
</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

<!-- STOCK -->

<div class="section-title">
Stock Alerts
</div>

<div class="stock-alert">

Low stock:
<b><?= $low_stock ?></b>

&nbsp;&nbsp;&nbsp;

Out of stock:
<b><?= $out_stock ?></b>

</div>

<!-- BUTTON -->

<div class="action-buttons">

<button class="btn-print" onclick="window.print()">

🖨 Print / Save PDF

</button>

</div>

</div>

</div>

</body>
</html>