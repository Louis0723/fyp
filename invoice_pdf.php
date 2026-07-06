<?php
require 'vendor/autoload.php';
include "db.php";

use Dompdf\Dompdf;

$order_id = intval($_GET['id']);

$res = mysqli_query($conn,"
SELECT
    oi.*,
    p.product_name,
    o.created_at,
    o.status,
    u.name,
    u.email
FROM order_items oi
JOIN products p ON oi.product_id = p.product_id
JOIN orders o ON oi.order_id = o.order_id
JOIN users u ON o.user_id = u.user_id
WHERE oi.order_id = $order_id
");

$total = 0;

$first = mysqli_fetch_assoc($res);

$customer_name = $first['name'];
$customer_email = $first['email'];
$order_date = $first['created_at'];
$order_status = $first['status'];

mysqli_data_seek($res, 0);

$html = '

<style>
body{
    font-family:Arial,sans-serif;
}

.header{
    text-align:center;
    border-bottom:2px solid #00bcd4;
    padding-bottom:15px;
}

.header h1{
    margin:0;
    color:#00bcd4;
}

.header h2{
    margin:5px 0;
}

.info{
    margin-top:20px;
}

.info p{
    margin:5px 0;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

th,td{
    border:1px solid #ddd;
    padding:10px;
}

th{
    background:#f2f2f2;
}

.total{
    margin-top:20px;
    text-align:right;
    font-size:20px;
    font-weight:bold;
}

.footer{
    margin-top:40px;
    text-align:center;
    color:#666;
}
</style>

<div class="header">

<h1>LOZ PC STORE</h1>

<h2>RECEIPT</h2>

<p>Receipt No: RCPT-'.$order_id.'</p>

<p>Order ID: #'.$order_id.'</p>

<p>Date: '.date('d M Y H:i', strtotime($order_date)).'</p>

</div>

<div class="info">

<h3>Customer Information</h3>

<p><strong>Name:</strong> '.$customer_name.'</p>

<p><strong>Email:</strong> '.$customer_email.'</p>

<p><strong>Status:</strong> '.strtoupper($order_status).'</p>

</div>

<table>

<tr>
<th>Product</th>
<th>Qty</th>
<th>Price</th>
<th>Subtotal</th>
</tr>

';

while($row = mysqli_fetch_assoc($res)){
    $subtotal = $row['price'] * $row['quantity'];
    $total += $subtotal;

    $html .= '
    <tr>
        <td>'.$row['product_name'].'</td>
        <td>'.$row['quantity'].'</td>
        <td>RM '.number_format($row['price'],2).'</td>
        <td>RM '.number_format($subtotal,2).'</td>
    </tr>';
}

$html .= '

</table>

<div class="total">
Total Paid: RM '.number_format($total,2).'
</div>

<div class="footer">

<h3>Thank You For Shopping With LOZ PC STORE </h3>

<p>
This receipt serves as proof of purchase.
Please keep it for warranty and support purposes.
</p>

</div>

';

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("invoice_$order_id.pdf", ["Attachment" => 1]);
?>