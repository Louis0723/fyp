<?php
include "../db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

// 统计数据
$productCount = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
$totalStock = $conn->query("SELECT SUM(stock) as total FROM products")->fetch_assoc()['total'];

// Chart 数据（按产品）
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

.content-area{
    flex:1;
    padding:40px;
    padding-top:120px;
    display:flex;
    flex-direction:column;
    align-items:center;
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
    position:relative;
}

.chart-title{
    font-weight:600;
    color:#0072ff;
    margin-bottom:10px;
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

<script>
const ctx = document.getElementById('stockChart').getContext('2d');

const colors = ['#00c6ff','#0072ff','#4facfe','#43e97b','#f9d423','#ff4e50'];

new Chart(ctx,{
    type:'doughnut',
    data:{
        labels: <?= json_encode($productLabels) ?>,
        datasets:[{
            data: <?= json_encode($productData) ?>,
            backgroundColor: colors,
            borderWidth:2
        }]
    },
    options:{
        responsive:true,
        plugins:{
            legend:{
                position:'bottom'
            }
        }
    }
});
</script>

</body>
</html>