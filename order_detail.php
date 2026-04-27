<?php
session_start();
include "db.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['user_id'];
$order_id = intval($_GET['id']);

// Get order (IMPORTANT: must belong to user)
$order = mysqli_query($conn,"
SELECT * FROM orders 
WHERE order_id=$order_id AND user_id=$user_id
");

$order = mysqli_fetch_assoc($order);

if(!$order){
    echo "Order not found!";
    exit;
}

// Get items
$items = mysqli_query($conn,"
SELECT oi.*, p.product_name 
FROM order_items oi
JOIN products p ON oi.product_id = p.product_id
WHERE oi.order_id = $order_id
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Order Detail</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
body{
    background: linear-gradient(135deg,#0f0c29,#302b63,#24243e);
    color:white;
    font-family:'Poppins',sans-serif;
}

.container{
    max-width:700px;
    margin:80px auto;
    padding:30px;
    background: rgba(255,255,255,0.05);
    border-radius:20px;
}

h1{
    text-align:center;
    color:#00f0ff;
}

.item{
    display:flex;
    justify-content:space-between;
    margin:10px 0;
    border-bottom:1px dashed #555;
    padding-bottom:5px;
}

.back{
    display:inline-block;
    margin-top:20px;
    padding:10px 20px;
    background:#ff00ff;
    color:#fff;
    border-radius:10px;
    text-decoration:none;
}
</style>
</head>

<body>

<div class="container">

<h1>Order #<?= $order_id ?></h1>

<p><b>Date:</b> <?= $order['created_at'] ?></p>
<p><b>Total:</b> RM <?= $order['total_price'] ?></p>

<p><b>Address:</b> <?= $order['address'] ?></p>
<p><b>Phone:</b> <?= $order['phone'] ?></p>

<hr>

<h3>Items</h3>

<?php while($row = mysqli_fetch_assoc($items)): ?>
<div class="item">
    <span><?= $row['product_name'] ?> x <?= $row['quantity'] ?></span>
    <span>RM <?= $row['price'] * $row['quantity'] ?></span>
</div>
<?php endwhile; ?>

<a href="history.php" class="back">← Back to History</a>

</div>

</body>
</html>