<?php
include "../db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

/* =========================
DATE FILTER
========================= */

$selected_date = isset($_GET['date'])
? $_GET['date']
: date('Y-m-d');

/* =========================
MAIN STATS
========================= */

// Revenue
$revenue_query = mysqli_query($conn,"
SELECT SUM(total_price) as revenue
FROM orders
WHERE DATE(created_at) = '$selected_date'
");

$revenue = mysqli_fetch_assoc($revenue_query)['revenue'] ?? 0;

// Orders
$order_query = mysqli_query($conn,"
SELECT COUNT(*) as total
FROM orders
WHERE DATE(created_at) = '$selected_date'
");

$total_orders = mysqli_fetch_assoc($order_query)['total'] ?? 0;

// Customers
$customer_query = mysqli_query($conn,"
SELECT COUNT(DISTINCT user_id) as total
FROM orders
WHERE DATE(created_at) = '$selected_date'
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
WHERE DATE(created_at) = '$selected_date'
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

<script src="https://unpkg.com/lucide@latest"></script>

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
    transition:.3s;
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

.filter-box{
    display:flex;
    gap:14px;
    align-items:center;
    margin-bottom:30px;
}

.filter-box input{
    height:52px;
    padding:0 18px;
    border:none;
    border-radius:14px;
    background:#fff;
    font-size:15px;
    box-shadow:0 5px 20px rgba(0,0,0,.06);
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

.btn-report:hover{
    background:#1d4ed8;
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
    margin-bottom:35px;
}

.report-header h2{
    font-size:52px;
    font-weight:800;
    margin-bottom:8px;
    color:#0f172a;
}

.report-header p{
    color:#6b7280;
    font-size:17px;
}

/* STATS */

.stats-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px;
    margin-bottom:45px;
}

.stat-card{
    border:1px solid #e5e7eb;
    border-radius:18px;
    padding:24px;
    min-height:140px;
    break-inside:avoid;
}

.stat-label{
    font-size:14px;
    text-transform:uppercase;
    color:#6b7280;
    margin-bottom:12px;
}

.stat-value{
    font-size:46px;
    font-weight:800;
    color:#0f172a;
}

/* SECTION */

.section-title{
    font-size:30px;
    font-weight:800;
    margin-bottom:18px;
    color:#111827;
    page-break-after:avoid;
}

/* STATUS */

.status-list{
    display:flex;
    flex-wrap:wrap;
    gap:12px;
    margin-bottom:40px;
}

.status-badge{
    background:#f3f4f6;
    border-radius:999px;
    padding:12px 18px;
    font-weight:700;
    color:#374151;
}

/* TABLE */

.report-table{
    width:100%;
    border-collapse:collapse;
    margin-bottom:45px;
    page-break-inside:auto;
}

.report-table th{
    text-align:left;
    padding:15px;
    background:#f8fafc;
    color:#64748b;
    border-bottom:1px solid #e5e7eb;
    font-size:15px;
}

.report-table td{
    padding:16px 15px;
    border-bottom:1px solid #f1f5f9;
    font-size:16px;
}

.report-table tr{
    page-break-inside:avoid;
}

.report-table tr:hover{
    background:#f8fbff;
}

/* ALERT */

.stock-alert{
    font-size:18px;
    color:#374151;
    margin-bottom:35px;
}

/* BUTTONS */

.action-buttons{
    display:flex;
    justify-content:flex-end;
    gap:14px;
    margin-top:30px;
}

.btn-print,
.btn-pdf{
    border:none;
    border-radius:14px;
    padding:14px 26px;
    font-weight:700;
    cursor:pointer;
    font-size:15px;
}

.btn-print{
    background:#111827;
    color:#fff;
}

.btn-pdf{
    background:#2563eb;
    color:#fff;
}

/* PDF FIX */

#reportArea{
    width:100%;
}

.stats-grid,
.report-table,
.section-title,
.status-list,
.stock-alert{
    page-break-inside:avoid;
    break-inside:avoid;
}

@media(max-width:1000px){

.stats-grid{
    grid-template-columns:1fr 1fr;
}

}

@media(max-width:700px){

.stats-grid{
    grid-template-columns:1fr;
}

}

/* PRINT */

@media print{

.sidebar,
.top-header,
.filter-box,
.action-buttons{
    display:none !important;
}

.main-content{
    margin:0 !important;
    padding:0 !important;
}

body{
    background:#fff;
}

.report-box{
    box-shadow:none;
    border-radius:0;
    padding:25px;
}

.stats-grid{
    grid-template-columns:repeat(2,1fr);
}

}

</style>

</head>

<body>

<?php include "admin_sidebar.php"; ?>

<div class="top-header">
<?php include "admin_header.php"; ?>
</div>

<div class="main-content">

<div class="page-title">
View Report
</div>

<form method="GET" class="filter-box">

<input
type="date"
name="date"
value="<?= $selected_date ?>"
required>

<button class="btn-report">
Get Report
</button>

</form>

<div class="report-box" id="reportArea">

<!-- HEADER -->

<div class="report-header">

<h2>Business Performance Report</h2>

<p>
Generated <?= date("m/d/Y, h:i:s A") ?>
• snapshot of current store state
</p>

</div>

<!-- STATS -->

<div class="stats-grid">

<div class="stat-card">
<div class="stat-label">Revenue</div>
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
4.5 / 5
</div>
</div>

</div>

<!-- STATUS -->

<div class="section-title">
Order Status
</div>

<div class="status-list">

<?php
$statuses = ['Pending','Processing','Shipped','Delivered','Cancelled'];

foreach($statuses as $status):

$count = $status_data[$status] ?? 0;
?>

<div class="status-badge">
<?= $status ?>: <?= $count ?>
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
<td><?= htmlspecialchars($inv['category']) ?></td>
<td><?= $inv['units'] ?></td>
<td>RM <?= number_format($inv['value'],2) ?></td>
</tr>

<?php endwhile; ?>

</tbody>

</table>

<!-- CUSTOMERS -->

<div class="section-title">
Top Customers
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
<td><?= htmlspecialchars($top['name']) ?></td>
<td><?= $top['orders_count'] ?></td>
<td>RM <?= number_format($top['spent'],2) ?></td>
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

•

Out of stock:
<b><?= $out_stock ?></b>
</div>

<!-- BUTTONS -->

<div class="action-buttons">

<button class="btn-print" onclick="printReport()">
Print
</button>

<button class="btn-pdf" onclick="downloadPDF()">
Download PDF
</button>

</div>

</div>

</div>

<!-- PDF -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>

lucide.createIcons();

/* PRINT */

function printReport(){
    window.print();
}

/* PDF */

function downloadPDF(){

    const revenue =
    document.querySelectorAll('.stat-value')[0].innerText;

    const orders =
    document.querySelectorAll('.stat-value')[1].innerText;

    const avgOrder =
    document.querySelectorAll('.stat-value')[2].innerText;

    const customers =
    document.querySelectorAll('.stat-value')[3].innerText;

    const products =
    document.querySelectorAll('.stat-value')[4].innerText;

    const rating =
    document.querySelectorAll('.stat-value')[5].innerText;

    /* CREATE CLEAN PDF */

    let pdfContent = `

    <div style="
        font-family:Arial;
        padding:40px;
        color:#111827;
        width:1000px;
        background:white;
    ">

        <!-- HEADER -->

        <div style="
            background:#0f172a;
            color:white;
            padding:30px;
            margin-bottom:30px;
        ">

            <div style="
                display:flex;
                justify-content:space-between;
                align-items:flex-start;
            ">

                <div>
                    <div style="
                        font-size:42px;
                        font-weight:800;
                        margin-bottom:10px;
                    ">
                        LOZ PC STORE
                    </div>

                    <div style="font-size:22px;">
                        Business Performance Report
                    </div>

                    <div style="
                        margin-top:10px;
                        opacity:.8;
                    ">
                        Generated:
                        ${new Date().toLocaleString()}
                    </div>
                </div>

                <div style="text-align:right;">

                    <div style="
                        font-size:48px;
                        font-weight:800;
                    ">
                        REPORT
                    </div>

                    <div style="
                        margin-top:10px;
                        font-size:20px;
                    ">
                        Daily Store Analytics
                    </div>

                </div>

            </div>

        </div>

        <!-- STATS -->

        <div style="
            display:grid;
            grid-template-columns:repeat(3,1fr);
            gap:20px;
            margin-bottom:40px;
        ">

            ${createCard("TOTAL REVENUE", revenue)}
            ${createCard("ORDERS", orders)}
            ${createCard("AVG. ORDER", avgOrder)}
            ${createCard("CUSTOMERS", customers)}
            ${createCard("PRODUCTS", products)}
            ${createCard("AVG. RATING", rating)}

        </div>

        <!-- ORDER STATUS -->

        <h2 style="
            font-size:32px;
            margin-bottom:15px;
        ">
            Order Status Breakdown
        </h2>

        ${generateTable(
            ["Status","Orders"],
            document.querySelectorAll('.status-badge')
        )}

        <!-- INVENTORY -->

        <h2 style="
            font-size:32px;
            margin:40px 0 15px;
        ">
            Inventory by Category
        </h2>

        ${document.querySelectorAll('.report-table')[0].outerHTML}

        <!-- CUSTOMERS -->

        <h2 style="
            font-size:32px;
            margin:40px 0 15px;
        ">
            Top Customers by Spend
        </h2>

        ${document.querySelectorAll('.report-table')[1].outerHTML}

    </div>
    `;

    /* OPEN TEMP WINDOW */

    const printWindow =
    window.open('', '_blank');

    printWindow.document.write(`
    <html>
    <head>

    <title>LOZ Report</title>

    <style>

    body{
        background:white;
        margin:0;
    }

    table{
        width:100%;
        border-collapse:collapse;
        margin-top:20px;
        margin-bottom:30px;
        font-size:18px;
    }

    th{
        background:#0f172a;
        color:white;
        padding:14px;
        text-align:left;
    }

    td{
        padding:14px;
        border-bottom:1px solid #e5e7eb;
    }

    tr:nth-child(even){
        background:#f8fafc;
    }

    </style>

    </head>

    <body>

    ${pdfContent}

    </body>
    </html>
    `);

    printWindow.document.close();

    setTimeout(() => {

        printWindow.print();

    }, 500);

}

/* CARD */

function createCard(label, value){

    return `
    <div style="
        border:1px solid #e5e7eb;
        padding:25px;
        border-radius:16px;
        min-height:130px;
    ">

        <div style="
            color:#64748b;
            font-size:15px;
            margin-bottom:14px;
        ">
            ${label}
        </div>

        <div style="
            font-size:52px;
            font-weight:800;
            color:#0f172a;
        ">
            ${value}
        </div>

    </div>
    `;
}

/* STATUS TABLE */

function generateTable(headers, rows){

    let html = `
    <table>

        <tr>
            <th>${headers[0]}</th>
            <th>${headers[1]}</th>
        </tr>
    `;

    rows.forEach(row => {

        const text = row.innerText.split(':');

        html += `
        <tr>
            <td>${text[0]}</td>
            <td>${text[1]}</td>
        </tr>
        `;
    });

    html += `</table>`;

    return html;
}
</script>

<script src="admin.js"></script>

</body>
</html>