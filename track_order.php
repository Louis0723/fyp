<?php
session_start();
include "db.php";

// 🔴 SECURITY: prevent crash if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['user_id'];

$res = mysqli_query($conn,"
SELECT * FROM orders 
WHERE user_id=$user_id
ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Track Orders</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
body{
    background: linear-gradient(135deg,#0f0c29,#302b63,#24243e);
    color:white;
    font-family:'Poppins',sans-serif;
}

.container{
    max-width:700px;
    margin:80px auto;
}

h2{
    text-align:center;
    color:#00f0ff;
    text-shadow:0 0 10px #00f0ff;
}

.back{
    display:inline-block;
    margin-bottom:20px;
    color:#ff00ff;
    text-decoration:none;
    font-weight:600;
}

.card{
    background:rgba(255,255,255,0.05);
    padding:15px;
    margin-bottom:15px;
    border-radius:12px;
    border:1px solid rgba(0,240,255,0.2);
    box-shadow:0 0 10px rgba(0,240,255,0.1);
}

.status{
    padding:5px 10px;
    border-radius:8px;
    font-weight:600;
    display:inline-block;
    margin-top:5px;
}

.pending{background:orange;color:black;}
.shipped{background:#00f0ff;color:black;}
.delivered{background:#00ff99;color:black;}

.empty{
    text-align:center;
    margin-top:50px;
    color:#00f0ff;
}
</style>
</head>

<body>

<div class="container">

<h2>🚚 Track Orders</h2>

<a href="product.php" class="back">⬅ Back to Products</a>

<br><br>

<?php if (mysqli_num_rows($res) == 0): ?>

    <div class="empty">
        🛒 You have no orders yet.
    </div>

<?php endif; ?>

<?php while($row = mysqli_fetch_assoc($res)): ?>

<?php
// safety fallback
$status = $row['status'] ?? 'Pending';
$statusClass = strtolower($status);
?>

<div class="card">

<b>Order #<?= $row['order_id'] ?></b><br><br>

💰 Total: RM <?= $row['total_price'] ?><br>
📅 Date: <?= $row['created_at'] ?><br><br>

Status:
<span class="status <?= $statusClass ?>">
    <?= $status ?>
</span>

</div>

<?php endwhile; ?>

</div>

</body>
</html>