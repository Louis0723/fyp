<?php
session_start();
include "../db.php";

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

if(!isset($_GET['id'])){
    die("Invalid invoice ID");
}

$order_id = intval($_GET['id']);

/* =========================
   GET ORDER INFORMATION
========================= */

$sql = "
SELECT 
    o.order_id,
    o.created_at,
    o.total_price,
    o.status,

  u.name,
  u.email,
  u.address,

    p.product_name,

    oi.quantity,
    oi.price

FROM orders o

JOIN users u 
ON o.user_id = u.user_id

JOIN order_items oi 
ON o.order_id = oi.order_id

JOIN products p
ON oi.product_id = p.product_id

WHERE o.order_id = '$order_id'
";

$result = $conn->query($sql);

if($result->num_rows == 0){
    die('Invoice not found');
}

$items = [];
$first = null;

while($row = $result->fetch_assoc()){

    if(!$first){
        $first = $row;
    }

    $items[] = $row;
}

/* =========================
   CALCULATIONS
========================= */

$subtotal = 0;

foreach($items as $item){

    $subtotal += ($item['price'] * $item['quantity']);

}

$tax = $subtotal * 0.06;

$shipping = 5.00;

$total = $subtotal + $tax + $shipping;
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Invoice #<?= $first['order_id'] ?></title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#eef2f7;
    font-family:'Inter',sans-serif;
    color:#1e293b;
    padding:40px;
}

/* =========================
   PRINT BUTTON
========================= */

.print-btn{
    position:fixed;
    top:30px;
    right:30px;

    background:#0f172a;
    color:#fff;

    border:none;
    border-radius:10px;

    padding:14px 24px;

    font-size:14px;
    font-weight:700;

    cursor:pointer;

    transition:.2s;
}

.print-btn:hover{
    background:#1e293b;
}

/* =========================
   INVOICE
========================= */

.invoice{
    width:900px;
    margin:auto;

    background:#fff;

    border-radius:10px;

    padding:60px;

    box-shadow:0 10px 40px rgba(0,0,0,0.08);
}

/* =========================
   TOP HEADER
========================= */

.top-header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;

    padding-bottom:35px;

    border-bottom:2px solid #e2e8f0;
}

.company h1{
    font-size:34px;
    font-weight:800;
    color:#0f172a;

    margin-bottom:8px;
}

.company p{
    font-size:14px;
    color:#64748b;
    line-height:1.8;
}

.invoice-title{
    text-align:right;
}

.invoice-title h2{
    font-size:44px;
    font-weight:800;
    color:#0f172a;

    margin-bottom:10px;
}

.invoice-title p{
    font-size:14px;
    color:#64748b;

    margin-bottom:6px;
}

/* =========================
   BILL SECTION
========================= */

.bill-section{
    display:grid;
    grid-template-columns:1fr 1fr;

    gap:60px;

    margin-top:45px;
    margin-bottom:45px;
}

.bill-box h3{
    font-size:13px;
    font-weight:800;
    letter-spacing:1px;

    color:#94a3b8;

    margin-bottom:14px;
}

.bill-box h2{
    font-size:20px;
    margin-bottom:10px;
}

.bill-box p{
    font-size:14px;
    color:#475569;
    line-height:1.8;
}

/* =========================
   STATUS
========================= */

.status{
    margin-top:18px;

    display:inline-block;

    padding:8px 18px;

    border-radius:8px;

    background:#dcfce7;
    color:#166534;

    font-size:13px;
    font-weight:700;
}

/* =========================
   TABLE
========================= */

table{
    width:100%;
    border-collapse:collapse;

    margin-top:10px;
}

thead{
    background:#0f172a;
}

thead th{
    color:#fff;

    padding:16px;

    font-size:13px;
    font-weight:700;

    text-align:left;
}

tbody td{
    padding:18px 16px;

    border-bottom:1px solid #e2e8f0;

    font-size:14px;
}

tbody tr:hover{
    background:#f8fafc;
}

td:nth-child(2),
td:nth-child(3),
td:nth-child(4){
    text-align:center;
}

.product-name{
    font-weight:700;
    color:#0f172a;
}

/* =========================
   SUMMARY
========================= */

.summary{
    width:320px;

    margin-left:auto;
    margin-top:40px;
}

.summary-row{
    display:flex;
    justify-content:space-between;

    margin-bottom:14px;

    font-size:15px;
}

.summary-total{
    margin-top:18px;

    padding-top:18px;

    border-top:2px solid #cbd5e1;

    display:flex;
    justify-content:space-between;
    align-items:center;
}

.summary-total h2{
    font-size:30px;
    color:#0f172a;
}

/* =========================
   FOOTER
========================= */

.footer{
    margin-top:70px;

    padding-top:30px;

    border-top:2px solid #e2e8f0;
}

.footer h4{
    font-size:15px;
    margin-bottom:10px;
}

.footer p{
    font-size:13px;
    color:#64748b;
    line-height:1.8;
}

/* =========================
   PRINT
========================= */

@media print{

    body{
        background:#fff;
        padding:0;
    }

    .print-btn{
        display:none;
    }

    .invoice{
        width:100%;
        box-shadow:none;
        border-radius:0;
        padding:30px;
    }

}

</style>

</head>
<body>

<button class="print-btn" onclick="window.print()">
    Download Invoice
</button>

<div class="invoice">

    <!-- ======================
         HEADER
    ======================= -->

    <div class="top-header">

        <div class="company">

            <h1>LOZ PC STORE</h1>

            <p>
                123 Technology Street<br>
                Kuala Lumpur, Malaysia<br>
                support@lozpcstore.com<br>
                +60 12-345 6789
            </p>

        </div>

        <div class="invoice-title">

            <h2>INVOICE</h2>

            <p>
                <strong>Invoice No:</strong>
                ORD-<?= $first['order_id'] ?>
            </p>

            <p>
                <strong>Date:</strong>
                <?= date('d M Y', strtotime($first['created_at'])) ?>
            </p>

            <p>
                <strong>Payment Status:</strong>
                <?= $first['status'] ?>
            </p>

        </div>

    </div>

    <!-- ======================
         BILL TO
    ======================= -->

    <div class="bill-section">

        <div class="bill-box">

            <h3>BILL TO</h3>

            <h2>
                <?= htmlspecialchars($first['name']) ?>
            </h2>

         <p>
    <strong>Email:</strong><br>
    <?= htmlspecialchars($first['email']) ?>
</p>

<br>

<p>
    <strong>Address:</strong><br>

    <?php if(!empty($first['address'])): ?>
        <?= nl2br(htmlspecialchars($first['address'])) ?>
    <?php else: ?>
        No Address Available
    <?php endif; ?>
</p>

        </div>

        <div class="bill-box">

            <h3>PAYMENT DETAILS</h3>

            <p>
                Payment Method: Online Banking<br>
                Currency: Malaysian Ringgit (MYR)<br>
                Invoice Generated Automatically
            </p>

            <div class="status">
                <?= strtoupper($first['status']) ?>
            </div>

        </div>

    </div>

    <!-- ======================
         TABLE
    ======================= -->

    <table>

        <thead>

            <tr>
                <th>DESCRIPTION</th>
                <th>QTY</th>
                <th>UNIT PRICE</th>
                <th>TOTAL</th>
            </tr>

        </thead>

        <tbody>

        <?php foreach($items as $item): ?>

            <tr>

                <td class="product-name">
                    <?= htmlspecialchars($item['product_name']) ?>
                </td>

                <td>
                    <?= $item['quantity'] ?>
                </td>

                <td>
                    RM <?= number_format($item['price'],2) ?>
                </td>

                <td>
                    RM <?= number_format($item['price'] * $item['quantity'],2) ?>
                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

    <!-- ======================
         SUMMARY
    ======================= -->

    <div class="summary">

        <div class="summary-row">
            <span>Subtotal</span>
            <span>RM <?= number_format($subtotal,2) ?></span>
        </div>

        <div class="summary-row">
            <span>SST (6%)</span>
            <span>RM <?= number_format($tax,2) ?></span>
        </div>
        <div class="summary-row">
             <span>Shipping</span>
               <span>RM <?= number_format($shipping,2) ?></span>
        </div>

        <div class="summary-total">

            <strong>Total Amount</strong>

            <h2>
                RM <?= number_format($total,2) ?>
            </h2>

        </div>

    </div>

    <!-- ======================
         FOOTER
    ======================= -->

    <div class="footer">

        <h4>Terms & Conditions</h4>

        <p>
            This invoice serves as an official proof of purchase issued by LOZ PC STORE.
            All products sold are subject to the store warranty policy.
            Goods sold are non-refundable unless stated otherwise.
            Please retain this invoice for warranty and support purposes.
        </p>

    </div>

</div>

</body>
</html>