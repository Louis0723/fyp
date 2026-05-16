<?php
session_start();
include "db.php";

// Redirect if user is not logged in//
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['user_id']; // current logged-in user

$result = mysqli_query($conn, "
SELECT * FROM orders 
WHERE user_id=" . (int)$user_id . "
ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order History - PC Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
        body{background: linear-gradient(135deg,#0f0c29,#302b63,#24243e); color:white; min-height:100vh;}
        #particles-js{position:fixed;width:100%;height:100%;z-index:-1;pointer-events:none;}

        .container{
            max-width:900px;
            margin:80px auto;
            padding:30px;
            background: rgba(255,255,255,0.05);
            border-radius:20px;
            backdrop-filter: blur(15px);
            box-shadow: 0 10px 25px rgba(0,255,255,0.4);
        }

        h1{
            text-align:center;
            font-size:40px;
            margin-bottom:30px;
            color:#00f0ff;
            text-shadow:0 0 15px #00f0ff;
        }

        .order{
            background: rgba(255,255,255,0.05);
            padding:20px;
            border-radius:15px;
            margin-bottom:25px;
            box-shadow:0 8px 20px rgba(0,255,255,0.2);
        }

        .order-header{
            display:flex;
            justify-content: space-between;
            margin-bottom:10px;
            font-weight:600;
            color:#ff00ff;
        }

        .order-header span{
            color:#00f0ff;
        }

        .order-item{
            display:flex;
            justify-content: space-between;
            margin:5px 0;
            padding:5px 0;
            border-bottom:1px dashed rgba(0,255,255,0.3);
        }

        .order-item:last-child{
            border-bottom:none;
        }

        .back{
            display:inline-block;
            margin-top:20px;
            margin-bottom:20px;
            padding:10px 20px;
            background:#ff00ff;
            color:#fff;
            border-radius:10px;
            text-decoration:none;
            font-weight:600;
            transition:0.3s;
        }
        .back:hover{transform:scale(1.05);}

        .status{
    padding:5px 10px;
    border-radius:8px;
    font-weight:600;
    display:inline-block;
    margin-top:5px;
}
.actions{
    margin-top:15px;
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.pending{background:orange;color:black;}
.shipped{background:#00f0ff;color:black;}
.delivered{background:#00ff99;color:black;}
.completed{
    background:#22c55e; /* green */
    color:white;
}
    </style>
</head>
<body>

<div id="particles-js"></div>

<div class="container">
    <h1>Your Order History</h1>
    <a href="product.php" class="back">← Back to Products</a>

    <?php
    if(mysqli_num_rows($result) == 0){
        echo "<p style='text-align:center;color:#00f0ff;margin-top:50px;'>🛒 You have no orders yet!</p>";
    }

    while($order = mysqli_fetch_assoc($result)){
$status = trim($order['status'] ?? 'Pending');
$statusLower = strtolower($status);

$statusClass = match($statusLower) {
    'pending' => 'pending',
    'shipped' => 'shipped',
    'delivered' => 'delivered',
    'completed' => 'completed',
    default => 'pending'
};
if ($statusLower !== 'pending' && $statusLower !== 'shipped' && $statusLower !== 'delivered') {
    $statusLower = 'pending';
}

echo "<div class='order'>";

echo "<div class='order-header'>
        <span>Order ID: {$order['order_id']}</span>
        <span>Date: {$order['created_at']}</span>
      </div>";

echo "<div class='order-header'>
        <span>Total:</span>
        <span>RM {$order['total_price']}</span>
      </div>";

echo "<div style='margin-top:10px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;'>";

echo "<div>
        Status:
        <span class='status $statusClass'>
            " . htmlspecialchars($status) . "
        </span>
      </div>";

echo "<div class='actions'>";

echo "<a href='order_detail.php?id={$order['order_id']}' 
style='padding:8px 12px;background:#00f0ff;color:#000;border-radius:8px;text-decoration:none;'>
View Details
</a>";

echo "<a href='invoice.php?id={$order['order_id']}' 
style='padding:8px 12px;background:#ff00ff;color:white;border-radius:8px;text-decoration:none;'>
🧾 Receipt
</a>";

if($status == "Delivered"){

echo "<a href='complete_order.php?id={$order['order_id']}'
style='padding:8px 12px;background:#00ff99;color:black;border-radius:8px;text-decoration:none;font-weight:600;'>
✅ Order Received
</a>";
}

$checkReview = mysqli_query($conn, "
SELECT * FROM order_items
WHERE order_id='{$order['order_id']}'
AND review_status='not_reviewed'
");

if($status == "Completed" && mysqli_num_rows($checkReview) > 0){

echo "<a href='reviews.php?order_id={$order['order_id']}'
style='padding:8px 12px;background:orange;color:black;border-radius:8px;text-decoration:none;font-weight:600;'>
⭐ Review
</a>";
}

$allReviewed = mysqli_query($conn, "
SELECT * FROM order_items
WHERE order_id='{$order['order_id']}'
AND review_status='not_reviewed'
");

if($status == "Completed" && mysqli_num_rows($allReviewed) == 0){

echo "<a href='view_review.php?order_id={$order['order_id']}' 
style='padding:8px 12px;background:#00f0ff;color:black;border-radius:8px;text-decoration:none;font-weight:600;'>
👁 View Review
</a>";

}

echo "</div>"; // close actions

echo "</div>"; // close flex wrapper

echo "</div>"; // close order card
}
?>

<script>
particlesJS("particles-js",{
"particles":{
"number":{"value":70},
"color":{"value":["#00f0ff","#ff00ff"]},
"shape":{"type":"circle"},
"opacity":{"value":0.5},
"size":{"value":3,"random":true},
"line_linked":{"enable":true,"distance":150,"color":"#00f0ff","opacity":0.3,"width":1},
"move":{"enable":true,"speed":2}
}
});
</script>

</body>
</html>