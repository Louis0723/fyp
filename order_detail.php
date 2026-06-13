<?php
session_start();
include "db.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['user_id'];
$order_id = intval($_GET['id']);

/* Get Order */
$order_query = mysqli_query($conn, "
    SELECT * 
    FROM orders
    WHERE order_id = $order_id
    AND user_id = $user_id
");

$order = mysqli_fetch_assoc($order_query);

if (!$order) {
    die("Order not found!");
}

/* Get Order Items */
$items = mysqli_query($conn, "
    SELECT oi.*, p.product_name, p.image
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
            background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);
            color:white;
            font-family:'Poppins',sans-serif;
        }

        .container{
            max-width:900px;
            margin:60px auto;
            padding:30px;
            background:rgba(255,255,255,0.05);
            border-radius:20px;
        }

        h1{
            text-align:center;
            color:#00f0ff;
            margin-bottom:30px;
        }

        .item{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin:15px 0;
            padding:15px;
            border-radius:15px;
            background:rgba(255,255,255,0.06);
        }

        .left{
            display:flex;
            gap:20px;
            align-items:center;
        }

        .left img{
            width:100px;
            height:100px;
            object-fit:cover;
            border-radius:12px;
            border:2px solid rgba(255,255,255,.2);
            background:white;
        }

        .product-name{
            font-size:18px;
            font-weight:600;
        }

        .price{
            font-size:18px;
            font-weight:700;
            color:#00f0ff;
        }

        .back{
            display:inline-block;
            margin-top:25px;
            padding:12px 25px;
            background:#ff00ff;
            color:white;
            border-radius:10px;
            text-decoration:none;
        }

        hr{
            margin:25px 0;
            border:1px solid rgba(255,255,255,.1);
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Order #<?= $order_id ?></h1>

    <p><b>Date:</b> <?= $order['created_at'] ?></p>

    <p><b>Total:</b> RM <?= number_format($order['total_price'],2) ?></p>

    <p><b>Address:</b> <?= htmlspecialchars($order['address']) ?></p>

    <p><b>Phone:</b> <?= htmlspecialchars($order['phone']) ?></p>

    <hr>

    <h2>Items</h2>

    <?php while($row = mysqli_fetch_assoc($items)): ?>

        <?php
        $image = trim($row['image']);

        if ($image == '') {

            $image_path = "images/no-image.png";

        } elseif (filter_var($image, FILTER_VALIDATE_URL)) {

            /* External image URL */
            $image_path = $image;

        } else {

            /*
             * Your VS Code screenshot shows:
             * fyp/uploads/products/
             */

            $image_path = "uploads/products/" . $image;
        }
        ?>

        <div class="item">

            <div class="left">

                <img
                    src="<?= $image_path ?>"
                    onerror="this.src='images/no-image.png';"
                >

                <div>

                    <div class="product-name">
                        <?= htmlspecialchars($row['product_name']) ?>
                    </div>

                    <div>
                        Quantity: <?= $row['quantity'] ?>
                    </div>

                    <div>
                        Price: RM <?= number_format($row['price'],2) ?>
                    </div>

                </div>

            </div>

            <div class="price">
                RM <?= number_format($row['price'] * $row['quantity'],2) ?>
            </div>

        </div>

    <?php endwhile; ?>

    <a href="history.php" class="back">
        ← Back to History
    </a>

</div>

</body>
</html>