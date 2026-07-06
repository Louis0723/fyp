<?php
session_start();
include "db.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid order ID.");
}

$order_id = (int)$_GET['id'];

$stmt = $conn->prepare("
    SELECT
        oi.quantity,
        oi.price,
        p.product_name,
        o.created_at,
        o.status,
        o.total_price,
        u.name,
        u.email
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    JOIN orders o ON oi.order_id = o.order_id
    JOIN users u ON o.user_id = u.user_id
    WHERE oi.order_id = ?
");

$stmt->bind_param("i", $order_id);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
    die("Receipt not found.");
}

$order_info = mysqli_fetch_assoc($res);
$res->data_seek(0);

$subtotal = 0;
$tax_rate = 0.06;
$delivery_fee = 5;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body{
            background: linear-gradient(135deg,#0f0c29,#302b63,#24243e);
            color:white;
            font-family:'Poppins',sans-serif;
            display:flex;
            justify-content:center;
            align-items:center;
            min-height:100vh;
            margin:0;
        }

        .box{
            width:650px;
            padding:30px;
            background:rgba(255,255,255,0.05);
            border-radius:20px;
            backdrop-filter:blur(15px);
            box-shadow:0 0 25px rgba(0,255,255,0.2);
            text-align:left;
        }

        .receipt-info{
            background:rgba(255,255,255,0.05);
            padding:15px;
            border-radius:12px;
            margin-bottom:20px;
            line-height:1.8;
        }

        h2{
            text-align:center;
            color:#00f0ff;
            margin-bottom:10px;
            text-shadow:0 0 10px #00f0ff;
        }

        .top-bar{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:20px;
            font-size:14px;
            color:#ccc;
        }

        .back-btn{
            display:inline-block;
            padding:8px 12px;
            background:#ff00ff;
            color:white;
            border-radius:8px;
            text-decoration:none;
            font-weight:600;
            transition:0.3s;
        }

        .back-btn:hover{
            transform:scale(1.05);
        }

        hr{
            border:1px solid rgba(0,255,255,0.2);
            margin:15px 0;
        }

        .item{
            display:flex;
            justify-content:space-between;
            padding:10px 0;
            border-bottom:1px dashed rgba(0,255,255,0.3);
            font-size:15px;
            gap:20px;
        }

        .item span:first-child{
            color:#fff;
            flex:1;
            word-break:break-word;
        }

        .item span:last-child{
            color:#00f0ff;
            font-weight:600;
            white-space:nowrap;
        }

        .total{
            margin-top:20px;
            font-size:24px;
            color:#ff00ff;
            font-weight:bold;
            text-align:right;
        }

        .actions{
            margin-top:30px;
            display:flex;
            justify-content:center;
            gap:20px;
            padding:15px;
            background:rgba(255,255,255,0.05);
            border-radius:15px;
            backdrop-filter:blur(10px);
        }

        .btn{
            padding:12px 18px;
            border-radius:12px;
            text-decoration:none;
            font-weight:600;
            font-size:14px;
            letter-spacing:0.5px;
            transition:0.3s ease;
            display:inline-flex;
            align-items:center;
            gap:8px;
            border:2px solid transparent;
        }

        .btn.pdf{
            background:rgba(255,0,255,0.1);
            color:#ff00ff;
            border:2px solid #ff00ff;
        }

        .btn.pdf:hover{
            background:#ff00ff;
            color:#fff;
            box-shadow:0 0 20px #ff00ff;
            transform:translateY(-2px);
        }
    </style>
</head>

<body>
<div class="box">

    <h2>RECEIPT</h2>

    <div class="receipt-info">
        <p><strong>Receipt No:</strong> RCPT-<?= $order_id ?></p>
        <p><strong>Order ID:</strong> #<?= $order_id ?></p>
        <p><strong>Date:</strong> <?= date('d M Y H:i', strtotime($order_info['created_at'])) ?></p>

        <hr>

        <p><strong>Customer:</strong> <?= htmlspecialchars($order_info['name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($order_info['email']) ?></p>
        <p><strong>Status:</strong> <?= strtoupper($order_info['status']) ?></p>
    </div>

    <div class="top-bar">
        <div>Order ID: #<?= $order_id ?></div>
        <a href="history.php" class="back-btn">⬅ Back</a>
    </div>

    <hr>

    <?php while($row = mysqli_fetch_assoc($res)): 
        $itemTotal = $row['price'] * $row['quantity'];
        $subtotal += $itemTotal;
    ?>
    
        <div class="item">
            <span><?= htmlspecialchars($row['product_name']) ?> x <?= $row['quantity'] ?></span>
            <span>RM <?= number_format($itemTotal, 2) ?></span>
        </div>
        <?php endwhile; ?>

    <?php
        $tax = $subtotal * $tax_rate;
        $grand_total = $subtotal + $tax + $delivery_fee;
    ?>
    

    <div style="text-align:right; font-size:18px;">
    <p>Subtotal: RM <?= number_format($subtotal, 2) ?></p>
    <p>Tax (6%): RM <?= number_format($tax, 2) ?></p>
    <p>Delivery Fee: RM <?= number_format($delivery_fee, 2) ?></p>

    <h2 style="color:#ff00ff;">
        Grand Total: RM <?= number_format($grand_total, 2) ?>
    </h2>
    </div>

    <div style="
        text-align:center;
        margin-top:25px;
        color:#ccc;
        font-size:14px;
    ">
      Thank you for shopping with LOZ PC STORE 
        <br><br>
        For support:   
        support@lozpcstore.com
    </div>

    <div class="actions">
        <a href="invoice_pdf.php?id=<?= $order_id ?>" class="btn pdf">
            ⬇ Download PDF
        </a>
    </div>

</div>
</body>
</html>