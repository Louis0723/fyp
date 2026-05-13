<?php
include __DIR__ . "/../db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

/* =========================
   STATS
========================= */

$productCount = $conn->query("
    SELECT COUNT(*) as t 
    FROM products
")->fetch_assoc()['t'];

$orderCount = $conn->query("
    SELECT COUNT(*) as t 
    FROM orders
")->fetch_assoc()['t'];

$userCount = $conn->query("
    SELECT COUNT(*) as t 
    FROM users
")->fetch_assoc()['t'];

$adminCount = $conn->query("
    SELECT COUNT(*) as t 
    FROM admins
")->fetch_assoc()['t'];

$revenue = $conn->query("
    SELECT SUM(total_price) as t 
    FROM orders
")->fetch_assoc()['t'] ?? 0;

/* =========================
   RECENT ADMINS
========================= */

$recentAdmins = $conn->query("
    SELECT *
    FROM admins
    ORDER BY admin_id DESC
    LIMIT 5
");

?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>Super Admin Dashboard</title>

<link rel="stylesheet" href="style.css?v=2">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter',sans-serif;
}

body{
    background:#f4f7fb;
}

/* MAIN */

.main-content{
    margin-left:270px;
    margin-top:95px;
    padding:30px;
    transition:.3s ease;
}

.sidebar.collapsed ~ .main-content{
    margin-left:95px;
}

/* TITLE */

.page-title{
    font-size:42px;
    font-weight:800;
    color:#0f172a;
}

.page-subtitle{
    margin-top:6px;
    color:#64748b;
    font-size:15px;
}

/* CARDS */

.cards{
    margin-top:28px;

    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:20px;
}

.card{
    background:#fff;
    border-radius:20px;
    padding:24px;

    border:1px solid #e5e7eb;

    box-shadow:0 8px 24px rgba(0,0,0,.05);

    position:relative;
    overflow:hidden;

    transition:.25s ease;
}

.card:hover{
    transform:translateY(-5px);
}

.card-icon{
    width:52px;
    height:52px;

    border-radius:14px;

    background:#eff6ff;

    display:flex;
    align-items:center;
    justify-content:center;

    margin-bottom:18px;
}

.card-icon i{
    width:24px;
    height:24px;
    color:#2563eb;
}

.card-title{
    color:#64748b;
    font-size:14px;
    margin-bottom:8px;
}

.card-value{
    font-size:34px;
    font-weight:800;
    color:#0f172a;
}

/* CHARTS */

.charts{
    margin-top:24px;

    display:grid;
    grid-template-columns:2fr 1fr;
    gap:20px;
}

.chart-box{
    background:#fff;

    border-radius:20px;
    padding:24px;

    border:1px solid #e5e7eb;

    box-shadow:0 8px 24px rgba(0,0,0,.05);
}

.chart-title{
    font-size:20px;
    font-weight:700;
    color:#0f172a;
    margin-bottom:20px;
}

/* ADMIN TABLE */

.admin-box{
    margin-top:24px;

    background:#fff;

    border-radius:20px;
    padding:24px;

    border:1px solid #e5e7eb;

    box-shadow:0 8px 24px rgba(0,0,0,.05);
}

.admin-title{
    font-size:22px;
    font-weight:700;
    margin-bottom:20px;
    color:#0f172a;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    text-align:left;
    padding-bottom:16px;
    color:#64748b;
    font-size:14px;
}

td{
    padding:18px 0;
    border-top:1px solid #f1f5f9;
}

.admin-user{
    display:flex;
    align-items:center;
    gap:12px;
}

.avatar{
    width:42px;
    height:42px;
    border-radius:50%;

    background:#2563eb;
    color:#fff;

    display:flex;
    align-items:center;
    justify-content:center;

    font-size:14px;
    font-weight:700;
}

.admin-name{
    font-weight:700;
    color:#0f172a;
}

.admin-email{
    color:#64748b;
    font-size:13px;
    margin-top:2px;
}

/* BADGE */

.badge{
    width:fit-content;

    padding:7px 14px;

    border-radius:999px;

    font-size:12px;
    font-weight:700;
}

.badge-super{
    background:#fee2e2;
    color:#991b1b;
}

.badge-admin{
    background:#dbeafe;
    color:#1d4ed8;
}

/* RESPONSIVE */

@media(max-width:1000px){

    .charts{
        grid-template-columns:1fr;
    }

}

</style>

</head>

<body>

<?php include "sadmin_sidebar.php"; ?>
<?php include "admin_header.php"; ?>

<div class="main-content">

    <div class="page-title">
        Super Admin Dashboard
    </div>

    <div class="page-subtitle">
        Full system analytics and administrator overview.
    </div>

    <!-- CARDS -->

    <div class="cards">

        <div class="card">

            <div class="card-icon">
                <i data-lucide="shield"></i>
            </div>

            <div class="card-title">
                Total Admins
            </div>

            <div class="card-value">
                <?= $adminCount ?>
            </div>

        </div>

        <div class="card">

            <div class="card-icon">
                <i data-lucide="wallet"></i>
            </div>

            <div class="card-title">
                Total Revenue
            </div>

            <div class="card-value">
                RM <?= number_format($revenue,2) ?>
            </div>

        </div>

        <div class="card">

            <div class="card-icon">
                <i data-lucide="shopping-cart"></i>
            </div>

            <div class="card-title">
                Total Orders
            </div>

            <div class="card-value">
                <?= $orderCount ?>
            </div>

        </div>

        <div class="card">

            <div class="card-icon">
                <i data-lucide="users"></i>
            </div>

            <div class="card-title">
                Total Customers
            </div>

            <div class="card-value">
                <?= $userCount ?>
            </div>

        </div>

    </div>

    <!-- CHARTS -->

    <div class="charts">

        <div class="chart-box">

            <div class="chart-title">
                Revenue Analytics
            </div>

            <canvas id="lineChart"></canvas>

        </div>

        <div class="chart-box">

            <div class="chart-title">
                Product Sales
            </div>

            <canvas id="barChart"></canvas>

        </div>

    </div>

    <!-- RECENT ADMINS -->

    <div class="admin-box">

        <div class="admin-title">
            Recent Admin Activity
        </div>

        <table>

            <thead>

                <tr>
                    <th>Administrator</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>

            </thead>

            <tbody>

<?php while($a = $recentAdmins->fetch_assoc()): 

$initials = strtoupper(substr($a['username'],0,2));

$role = $a['role'] ?? 'Admin';

?>

<tr>

    <td>

        <div class="admin-user">

            <div class="avatar">
                <?= $initials ?>
            </div>

            <div>

                <div class="admin-name">
                    <?= htmlspecialchars($a['username']) ?>
                </div>

                <div class="admin-email">
                    <?= htmlspecialchars($a['email']) ?>
                </div>

            </div>

        </div>

    </td>

    <td>

<?php if($role == "Super Admin"): ?>

        <div class="badge badge-super">
            Super Admin
        </div>

<?php else: ?>

        <div class="badge badge-admin">
            Admin
        </div>

<?php endif; ?>

    </td>

    <td style="color:#22c55e;font-weight:700;">
        Active
    </td>

</tr>

<?php endwhile; ?>

            </tbody>

        </table>

    </div>

</div>

<script src="admin.js"></script>

<script>

lucide.createIcons();

/* LINE CHART */

new Chart(document.getElementById("lineChart"), {

    type:'line',

    data:{
        labels:['Nov','Dec','Jan','Feb','Mar','Apr'],

        datasets:[{
            label:'Revenue',

            data:[28000,40000,35000,42000,48000,52000],

            borderColor:'#2563eb',

            backgroundColor:'rgba(37,99,235,.1)',

            tension:.4,

            fill:true
        }]
    },

    options:{
        responsive:true,
        plugins:{
            legend:{
                display:true
            }
        }
    }

});

/* BAR CHART */

new Chart(document.getElementById("barChart"), {

    type:'bar',

    data:{
        labels:['GPU','CPU','RAM','Storage','Case'],

        datasets:[{
            label:'Sales',

            data:[150,200,300,250,100],

            backgroundColor:'#2563eb',

            borderRadius:8
        }]
    },

    options:{
        responsive:true
    }

});

</script>

</body>
</html>