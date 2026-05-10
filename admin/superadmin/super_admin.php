<?php
session_start();

// ✅ FIX DB PATH (2 LEVEL UP)
include __DIR__ . "/../../db.php";

// ✅ PROTECT PAGE (SUPERADMIN ONLY)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../login.php");
    exit();
}

// ✅ SAFE QUERY FUNCTION (avoid crash)
function getValue($conn, $query) {
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['t'] ?? 0;
    }
    return 0;
}

// ===== DATA =====
$totalProducts = getValue($conn, "SELECT COUNT(*) as t FROM products");
$totalOrders   = getValue($conn, "SELECT COUNT(*) as t FROM orders");
$totalUsers    = getValue($conn, "SELECT COUNT(*) as t FROM users");
$totalRevenue  = getValue($conn, "SELECT SUM(total_price) as t FROM orders WHERE status='Completed'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Super Admin Dashboard</title>

    <!-- CSS -->
    <link rel="stylesheet" href="../admin.css">

    <!-- ICON -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
    .cards{
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
        gap:20px;
    }

    .card{
        background:#fff;
        padding:20px;
        border-radius:15px;
        box-shadow:0 5px 15px rgba(0,0,0,0.05);
        transition:0.3s;
    }

    .card:hover{
        transform:translateY(-5px);
    }

    .card h3{
        margin:0;
        font-size:14px;
        color:#64748b;
    }

    .card p{
        font-size:22px;
        font-weight:bold;
        margin-top:10px;
    }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<?php include __DIR__ . "/../admin_sidebar.php"; ?>

<!-- HEADER -->
<?php include __DIR__ . "/../admin_header.php"; ?>

<div class="main-content">

    <!-- ✅ safer output -->
    <h2>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Super Admin') ?></h2>
    <p>Super Admin Dashboard Overview</p>

    <div class="cards">

        <div class="card">
            <h3>Total Products</h3>
            <p><?= $totalProducts ?></p>
        </div>

        <div class="card">
            <h3>Total Orders</h3>
            <p><?= $totalOrders ?></p>
        </div>

        <div class="card">
            <h3>Total Users</h3>
            <p><?= $totalUsers ?></p>
        </div>

        <div class="card">
            <h3>Total Revenue</h3>
            <p>RM <?= number_format($totalRevenue, 2) ?></p>
        </div>

    </div>

</div>

<script>
lucide.createIcons();

// toggle sidebar
document.getElementById("toggleSidebar")?.addEventListener("click", () => {
    document.getElementById("sidebar")?.classList.toggle("collapsed");
});

// notification
const notifBtn = document.getElementById("notifBtn");
const notifBox = document.getElementById("notifBox");

notifBtn?.addEventListener("click", () => {
    notifBox.style.display =
        notifBox.style.display === "block" ? "none" : "block";
});

document.addEventListener("click", function(e){
    if(notifBtn && notifBox &&
       !notifBtn.contains(e.target) &&
       !notifBox.contains(e.target)){
        notifBox.style.display = "none";
    }
});
</script>
                           
</body>
</html>