<?php
include "../db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

// stats
$productCount = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
$totalStock = $conn->query("SELECT SUM(stock) as total FROM products")->fetch_assoc()['total'];

// chart data
$productData = [];
$productLabels = [];

$res = $conn->query("SELECT product_name, stock FROM products ORDER BY stock DESC");

while($row = $res->fetch_assoc()){
    $productLabels[] = $row['product_name'];
    $productData[] = (int)$row['stock'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>

<!-- ✅ IMPORTANT: LOAD YOUR CSS -->
<link rel="stylesheet" href="style.css">

<!-- Chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Poppins;}

body{
    min-height:100vh;
    background:linear-gradient(135deg,#e0f7ff,#c2e9fb);
}

/* layout */
.main-layout{
    display:flex;
}

/* content area */
.content-area{
    flex:1;
    padding:40px;
    padding-top:120px;
    margin-left:240px; /* ✅ push away from sidebar */
    display:flex;
    flex-direction:column;
    align-items:center;
}

/* when sidebar collapsed */
.sidebar.collapsed ~ .main-layout .content-area{
    margin-left:70px;
}

/* title */
.dashboard-title{
    font-size:34px;
    font-weight:700;
    color:#0072ff;
    margin-bottom:30px;
}

/* chart */
.chart-wrapper{
    width:100%;
    max-width:500px;
    background:rgba(255,255,255,0.7);
    backdrop-filter:blur(15px);
    border-radius:20px;
    padding:25px;
    box-shadow:0 10px 30px rgba(0,0,0,0.15);
    margin-bottom:40px;
}

/* cards */
.dashboard-grid{
    display:grid;
    grid-template-columns: repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    width:100%;
    max-width:800px;
}

.card{
    background:rgba(255,255,255,0.7);
    backdrop-filter:blur(10px);
    padding:25px;
    border-radius:15px;
    text-align:center;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
    transition:0.3s;
}

.card:hover{
    transform:translateY(-5px);
}

.card h3{
    margin-bottom:10px;
    color:#333;
}

.card p{
    font-size:28px;
    font-weight:bold;
    color:#0072ff;
}
</style>

</head>

<body>

<?php include "admin_header.php"; ?>
<?php include "admin_sidebar.php"; ?>

<div class="main-layout">

    <div class="content-area">

        <div class="dashboard-title">📊 PC Store Dashboard</div>

        <!-- Chart -->
        <div class="chart-wrapper">
            <div class="chart-title">Product Stock Distribution</div>
            <canvas id="stockChart"></canvas>
        </div>

        <!-- Cards -->
        <div class="dashboard-grid">
            <div class="card">
                <h3>Total Products</h3>
                <p><?= $productCount ?></p>
            </div>

            <div class="card">
                <h3>Total Stock</h3>
                <p><?= $totalStock ?></p>
            </div>

            <div class="card">
                <h3>Admin</h3>
                <p><?= $_SESSION['admin'] ?></p>
            </div>
        </div>

    </div>

</div>

<!-- Chart JS -->
<script>
const ctx = document.getElementById('stockChart').getContext('2d');

new Chart(ctx,{
    type:'doughnut',
    data:{
        labels: <?= json_encode($productLabels) ?>,
        datasets:[{
            data: <?= json_encode($productData) ?>,
            backgroundColor: ['#00c6ff','#0072ff','#4facfe','#43e97b','#f9d423','#ff4e50'],
            borderWidth:2
        }]
    }
});
</script>

<!-- icons -->
<script src="https://unpkg.com/lucide@latest"></script>

<!-- your JS -->
<script src="admin.js"></script>

<script>
lucide.createIcons();
</script>

</body>
</html>