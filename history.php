<?php
session_start();
include "db.php";

// Redirect if user is not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['user_id']; // current logged-in user

// Fetch orders for this user
$result = mysqli_query($conn, "SELECT * FROM orders WHERE user_id=$user_id ORDER BY created_at DESC");
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
        echo "<div class='order'>";
        echo "<div class='order-header'>
                <span>Order ID: {$order['order_id']}</span>
                <span>Date: {$order['created_at']}</span>
              </div>";
        echo "<div class='order-header'>
        <span>Total:</span>
        <span>RM {$order['total_price']}</span>
        </div>";

        echo "<div style='margin:10px 0; padding:10px; border:1px solid rgba(0,255,255,0.3); border-radius:10px;'>
        <div>📍 <b>Address:</b> {$order['address']}</div>
        <div>📞 <b>Phone:</b> {$order['phone']}</div>
        </div>";

        // Fetch order items
        $items = mysqli_query($conn,"SELECT oi.*, p.product_name, p.price 
                                     FROM order_items oi 
                                     JOIN products p ON oi.product_id=p.product_id 
                                     WHERE order_id={$order['order_id']}");

        while($item = mysqli_fetch_assoc($items)){
            $subtotal = $item['price'] * $item['quantity'];
            echo "<div class='order-item'>
                    <span>{$item['product_name']} x {$item['quantity']}</span>
                    <span>RM $subtotal</span>
                  </div>";
        }

        echo "</div>"; // end order
    }
    ?>
</div>

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