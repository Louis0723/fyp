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

$revenueQuery = $conn->query("
    SELECT SUM(total_price) as total 
    FROM orders
");

$revenueData = $revenueQuery->fetch_assoc();

$revenue = $revenueData['total'] ?? 0;

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
        WHERE MONTH(created_at) = '$month'
        AND YEAR(created_at) = '$year'
    ");

    $row = $sql->fetch_assoc();

    $monthRevenue[] = $row['total'] ?? 0;
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
")->fetch_assoc()['total'];

/* =========================================
TOP INVENTORY
========================================= */

$topInventory = $conn->query("
    SELECT 
        product_name,
        stock
    FROM products
    ORDER BY stock DESC
    LIMIT 5
");

/* =========================================
LOW STOCK
========================================= */

$lowStock = $conn->query("
    SELECT 
        product_name,
        stock
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
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard</title>

<link rel="stylesheet" href="style.css?v=50">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial,sans-serif;
    background:#f1f5f9;
}

/* =========================================
MAIN
========================================= */

.main-content{
    margin-left:250px;
    padding:110px 24px 30px;
    transition:.3s;
}

.sidebar.collapsed ~ .main-content{
    margin-left:90px;
}

/* =========================================
TITLE
========================================= */

.page-title{
    font-size:54px;
    font-weight:900;
    color:#0f172a;
}

.page-sub{
    color:#64748b;
    margin-top:10px;
    margin-bottom:30px;
    font-size:16px;
}

/* =========================================
CARDS
========================================= */

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
    align-items:center;
    margin-bottom:20px;
}

.card-title{
    color:#64748b;
    font-size:16px;
}

.card-icon{
    width:44px;
    height:44px;
    border-radius:14px;
    background:#eff6ff;
    display:flex;
    align-items:center;
    justify-content:center;
}

.card-icon i{
    width:22px;
    height:22px;
    color:#2563eb;
}

.card-value{
    font-size:40px;
    font-weight:900;
    color:#0f172a;
    margin-bottom:10px;
}

.card-growth{
    color:#16a34a;
    font-size:14px;
}

/* =========================================
TOP GRID
========================================= */

.top-grid{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:20px;
    margin-bottom:24px;
}

.chart-card{
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

.chart-wrapper{
    position:relative;
    width:100%;
    height:420px;
}

.small-chart{
    height:380px;
}

/* =========================================
BOTTOM GRID
========================================= */

.bottom-grid{
    display:grid;
    grid-template-columns:1fr 1fr 1fr;
    gap:20px;
    margin-bottom:24px;
}

.inventory-card,
.status-card,
.alert-card{
    background:#fff;
    border-radius:24px;
    padding:24px;
    border:1px solid #e2e8f0;
}

/* =========================================
INVENTORY
========================================= */

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
    border-radius:50px;
    overflow:hidden;
}

.progress-bar{
    height:100%;
    background:#2563eb;
}

/* =========================================
ALERT
========================================= */

.alert-item{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:18px;
}

.alert-info h4{
    font-size:15px;
}

.alert-info p{
    font-size:13px;
    color:#64748b;
}

.alert-badge{
    background:#eff6ff;
    color:#2563eb;
    padding:6px 12px;
    border-radius:10px;
    font-size:13px;
    font-weight:700;
}

.alert-badge.red{
    background:#fee2e2;
    color:#dc2626;
}

/* =========================================
TABLE
========================================= */

.table-card{
    background:#fff;
    border-radius:24px;
    padding:24px;
    border:1px solid #e2e8f0;
}

.table-title{
    font-size:24px;
    font-weight:800;
    margin-bottom:20px;
}

table{
    width:100%;
    border-collapse:collapse;
}

table th{
    text-align:left;
    padding:16px;
    color:#64748b;
    border-bottom:1px solid #e2e8f0;
}

table td{
    padding:16px;
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

/* =========================================
SIDEBAR DROPDOWN
========================================= */

.has-dropdown .dropdown-menu{
    max-height:0;
    overflow:hidden;
    transition:.3s ease;
}

.has-dropdown.open .dropdown-menu{
    max-height:300px;
}

.dropdown-toggle{
    cursor:pointer;
}

/* =========================================
COLLAPSED SIDEBAR
========================================= */

.sidebar.collapsed{
    width:90px;
}

.sidebar.collapsed .menu-text,
.sidebar.collapsed .dropdown-icon,
.sidebar.collapsed .dropdown-menu,
.sidebar.collapsed .brand-text{
    display:none;
}

.sidebar.collapsed .menu-link{
    justify-content:center;
}

/* =========================================
RESPONSIVE
========================================= */

@media(max-width:1200px){

    .cards{
        grid-template-columns:repeat(2,1fr);
    }

    .top-grid{
        grid-template-columns:1fr;
    }

    .bottom-grid{
        grid-template-columns:1fr;
    }

}

@media(max-width:768px){

    .main-content{
        margin-left:0;
        padding:100px 16px 20px;
    }

    .cards{
        grid-template-columns:1fr;
    }

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

                <div class="card-title">
                    Revenue
                </div>

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

                <div class="card-title">
                    Orders
                </div>

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

                <div class="card-title">
                    Customers
                </div>

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

                <div class="card-title">
                    Products
                </div>

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

            <div class="chart-wrapper small-chart">

                <canvas id="donutChart"></canvas>

            </div>

        </div>

    </div>

    <!-- BOTTOM GRID -->

    <div class="bottom-grid">

        <div class="status-card">

            <div class="chart-title">
                Order status
            </div>

            <div class="chart-wrapper small-chart">

                <canvas id="pieChart"></canvas>

            </div>

        </div>

        <!-- INVENTORY -->

        <div class="inventory-card">

            <div class="chart-title">
                Top inventory
            </div>

            <?php

            $maxStock = 1;

            $items = [];

            while($row = $topInventory->fetch_assoc()){

                $items[] = $row;

                if($row['stock'] > $maxStock){
                    $maxStock = $row['stock'];
                }

            }

            foreach($items as $row):

            $width =
            ($row['stock'] / $maxStock) * 100;

            ?>

            <div class="inventory-item">

                <div class="inventory-top">

                    <span>
                        <?= $row['product_name'] ?>
                    </span>

                    <span>
                        <?= $row['stock'] ?> pcs
                    </span>

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

                <div class="alert-info">

                    <h4>
                        <?= $low['product_name'] ?>
                    </h4>

                    <p>
                        Low stock product
                    </p>

                </div>

                <div class="alert-badge <?= $low['stock'] == 0 ? 'red' : '' ?>">

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
                        <?= date("Y-m-d",
                        strtotime($row['created_at'])) ?>
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

<!-- =========================================
SCRIPT
========================================= -->

<script>

lucide.createIcons();

/* =========================================
SIDEBAR TOGGLE
========================================= */

const toggleBtn =
document.getElementById("toggleSidebar");

const sidebar =
document.getElementById("sidebar");

if(toggleBtn && sidebar){

    toggleBtn.addEventListener("click", function(){

        sidebar.classList.toggle("collapsed");

        if(sidebar.classList.contains("collapsed")){

            localStorage.setItem(
                "sidebar",
                "collapsed"
            );

        }else{

            localStorage.setItem(
                "sidebar",
                "expanded"
            );

        }

    });

}

/* =========================================
RESTORE SIDEBAR
========================================= */

window.addEventListener("load", function(){

    if(localStorage.getItem("sidebar")
       === "collapsed"){

        sidebar.classList.add("collapsed");

    }

});

/* =========================================
DROPDOWN MENU
========================================= */

const dropdowns =
document.querySelectorAll(".has-dropdown");

dropdowns.forEach(menu => {

    const trigger =
    menu.querySelector(".dropdown-toggle");

    if(trigger){

        trigger.addEventListener("click", function(e){

            e.preventDefault();

            menu.classList.toggle("open");

        });

    }

});

/* =========================================
LINE CHART
========================================= */

const lineCanvas =
document.getElementById("lineChart");

if(lineCanvas){

new Chart(lineCanvas, {

    type:'line',

    data:{

        labels:<?= json_encode($monthLabels) ?>,

        datasets:[{

            label:'Revenue',

            data:<?= json_encode($monthRevenue) ?>,

            borderColor:'#2563eb',

            backgroundColor:'rgba(37,99,235,.12)',

            fill:true,

            tension:.4,

            pointRadius:4,

            pointBackgroundColor:'#2563eb'

        }]

    },

    options:{

        responsive:true,

        maintainAspectRatio:false,

        plugins:{
            legend:{
                display:false
            }
        }

    }

});

}

/* =========================================
DONUT
========================================= */

const donutCanvas =
document.getElementById("donutChart");

if(donutCanvas){

new Chart(donutCanvas, {

    type:'doughnut',

    data:{

        labels:[
            'GPU',
            'CPU',
            'RAM',
            'Power',
            'Case',
            'Cooling',
            'Storage'
        ],

        datasets:[{

            data:[30,28,25,8,4,3,2],

            backgroundColor:[
                '#2563eb',
                '#0d9488',
                '#155e75',
                '#eab308',
                '#f59e0b',
                '#10b981',
                '#8b5cf6'
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

}

/* =========================================
PIE CHART
========================================= */

const pieCanvas =
document.getElementById("pieChart");

if(pieCanvas){

new Chart(pieCanvas, {

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
                '#2563eb',
                '#0d9488',
                '#155e75',
                '#16a34a'
            ],

            borderWidth:0

        }]

    },

    options:{
        responsive:true,
        maintainAspectRatio:false
    }

});

}

</script>

</body>
</html>