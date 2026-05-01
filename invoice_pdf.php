<?php
require 'vendor/autoload.php';
include "db.php";

use Dompdf\Dompdf;

$order_id = intval($_GET['id']);

$res = mysqli_query($conn,"
SELECT oi.*, p.product_name 
FROM order_items oi
JOIN products p ON oi.product_id = p.product_id
WHERE oi.order_id = $order_id
");

$total = 0;

// Build HTML
$html = '
<style>
body { font-family: Arial, sans-serif; }
.header {
    text-align:center;
    border-bottom:2px solid #00bcd4;
    padding-bottom:10px;
}
h1 { color:#00bcd4; margin:0; }
table {
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}
th, td {
    border-bottom:1px solid #ddd;
    padding:10px;
    text-align:left;
}
th {
    background:#f2f2f2;
}
.total {
    text-align:right;
    margin-top:20px;
    font-size:18px;
    font-weight:bold;
    color:#ff4081;
}
.footer {
    margin-top:30px;
    text-align:center;
    font-size:12px;
    color:#777;
}
</style>

<div class="header">
    <h1>INVOICE</h1>
    <p>Order ID: #' . $order_id . '</p>
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
Total: RM '.number_format($total,2).'
</div>

<div class="footer">
Thank you for your purchase!
</div>
';

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("invoice_$order_id.pdf", ["Attachment" => 1]);
?>