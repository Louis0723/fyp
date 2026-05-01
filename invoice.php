<?php
session_start();
include "db.php";

$order_id = intval($_GET['id']);

$res = mysqli_query($conn,"
SELECT oi.*, p.product_name 
FROM order_items oi
JOIN products p ON oi.product_id = p.product_id
WHERE oi.order_id = $order_id
");

$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>Invoice</title>

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
}

.item span:first-child{
    color:#fff;
}

.item span:last-child{
    color:#00f0ff;
    font-weight:600;
}

.total{
    margin-top:20px;
    font-size:24px;
    color:#ff00ff;
    font-weight:bold;
    text-align:right;
}

.actions{
    margin-top:25px;
    display:flex;
    justify-content:center;
}

button{
    padding:10px 20px;
    background:#00f0ff;
    border:none;
    border-radius:10px;
    cursor:pointer;
    font-weight:bold;
    transition:0.3s;
}

button:hover{
    transform:scale(1.05);
    box-shadow:0 0 15px #00f0ff;
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

/* Base button style */
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

/* PRINT BUTTON */
.btn.print{
    background:rgba(0,240,255,0.1);
    color:#00f0ff;
    border:2px solid #00f0ff;
}

.btn.print:hover{
    background:#00f0ff;
    color:#000;
    box-shadow:0 0 20px #00f0ff;
    transform:translateY(-2px);
}

/* PDF BUTTON */
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

<h2>🧾 INVOICE</h2>

<div class="top-bar">
    <div>Order ID: #<?= $order_id ?></div>
    <a href="history.php" class="back-btn">⬅ Back</a>
</div>

<hr>

<?php while($row = mysqli_fetch_assoc($res)): 
$total += $row['price'] * $row['quantity'];
?>

<div class="item">
    <span><?= $row['product_name'] ?> x <?= $row['quantity'] ?></span>
    <span>RM <?= $row['price'] * $row['quantity'] ?></span>
</div>

<?php endwhile; ?>

<div class="total">
Total: RM <?= $total ?>
</div>

<div class="actions">

    <a href="invoice_pdf.php?id=<?= $order_id ?>" class="btn pdf">
        ⬇ Download PDF
    </a>

    <button onclick="window.print()" class="btn print">
        🖨 Print Invoice
    </button>

</div>


</div>

</body>
</html>