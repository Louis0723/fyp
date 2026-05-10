<?php
session_start();
include "db.php";
require "vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['user_id'];

$res_user = mysqli_query($conn, "SELECT * FROM users WHERE user_id = $user_id");
$user = mysqli_fetch_assoc($res_user);

$res = mysqli_query($conn, "
SELECT p.*, c.quantity
FROM cart c
JOIN products p ON c.product_id = p.product_id
WHERE c.user_id = $user_id
");

$total = 0;
$items = [];

while($row = mysqli_fetch_assoc($res)){
    $total += $row['price'] * $row['quantity'];
    $items[] = $row;
}

if(isset($_POST['pay'])){

    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $method = mysqli_real_escape_string($conn, $_POST['method']);

    mysqli_query($conn,"
    INSERT INTO orders(user_id,total_price,address,phone,payment_method)
    VALUES($user_id,$total,'$address','$phone','$method')
    ");

    $order_id = mysqli_insert_id($conn);

    foreach($items as $row){

        mysqli_query($conn,"
        INSERT INTO order_items(order_id,product_id,quantity,price)
        VALUES($order_id,{$row['product_id']},{$row['quantity']},{$row['price']})
        ");

        mysqli_query($conn,"
        UPDATE products
        SET stock = stock - {$row['quantity']}
        WHERE product_id = {$row['product_id']}
        ");
    }

    mysqli_query($conn,"DELETE FROM cart WHERE user_id=$user_id");

    $mail = new PHPMailer(true);

    try{

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        $mail->Username = 'yourgmail@gmail.com';
        $mail->Password = 'your_app_password';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('yourgmail@gmail.com', 'PC STORE');
        $mail->addAddress($user['email']);

        $mail->isHTML(true);
        $mail->Subject = "Order Receipt - PC STORE";

        $mail->Body = "
        <h2>Thank you for your order 🎉</h2>
        <p><b>Order ID:</b> $order_id</p>
        <p><b>Total:</b> RM $total</p>
        <p><b>Payment Method:</b> $method</p>
        <p>Your order is now being prepared.</p>
        ";

        $mail->send();

    }catch(Exception $e){
        error_log($mail->ErrorInfo);
    }

    header("Location: checkout.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Checkout - PC Store</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

<style>

/* ===== YOUR ORIGINAL THEME (UNCHANGED) ===== */

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);
    min-height:100vh;
    color:white;
}

#particles-js{
    position:fixed;
    width:100%;
    height:100%;
    z-index:-1;
}

.container{
    max-width:750px;
    margin:60px auto;
    padding:35px;
    border-radius:25px;
    background:rgba(255,255,255,0.05);
    backdrop-filter:blur(15px);
    box-shadow:0 10px 30px rgba(0,0,0,0.5);
}

h1{
    text-align:center;
    margin-bottom:25px;
    color:#00f0ff;
    text-shadow:0 0 15px #00f0ff;
}

.back{
    display:inline-block;
    margin-bottom:25px;
    color:white;
    text-decoration:none;
    background:#ff00ff;
    padding:10px 18px;
    border-radius:10px;
    font-weight:600;
}

.summary{
    margin-bottom:25px;
    padding:20px;
    border-radius:15px;
    background:rgba(255,255,255,0.05);
}

/* NEW FORMAL TABLE STYLE */
.summary table{
    width:100%;
    border-collapse:collapse;
}

.summary th,
.summary td{
    padding:12px;
    text-align:left;
    border-bottom:1px solid rgba(255,255,255,0.2);
}

.summary th{
    color:#00f0ff;
    font-weight:600;
}

.total{
    font-size:30px;
    color:#00f0ff;
    margin-bottom:25px;
    text-align:center;
    font-weight:700;
}

.payment-methods{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:18px;
    margin-bottom:25px;
}

.payment-methods label{
    cursor:pointer;
}

.payment-methods input{
    display:none;
}

.payment-card{
    padding:20px;
    border-radius:20px;
    background:rgba(255,255,255,0.06);
    border:2px solid transparent;
    text-align:center;
    transition:0.3s;
    font-weight:600;
}

.payment-card:hover{
    transform:translateY(-5px);
}

.payment-methods input:checked + .payment-card{
    border-color:#00f0ff;
    box-shadow:0 0 20px #00f0ff;
}

.input-box{
    margin-bottom:15px;
}

.input-box input,
select{
    width:100%;
    padding:14px;
    border-radius:12px;
    border:2px solid #00f0ff;
    background:rgba(255,255,255,0.05);
    color:white;
    outline:none;
    font-size:15px;
}

select option{
    background:#302b63;
}

.hidden-box{
    display:none;
}

button{
    width:100%;
    padding:16px;
    border:none;
    border-radius:14px;
    background:linear-gradient(90deg,#00f0ff,#ff00ff);
    color:white;
    font-size:16px;
    font-weight:700;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    transform:scale(1.03);
    box-shadow:0 0 20px #00f0ff;
}

</style>
</head>

<body>

<div id="particles-js"></div>

<div class="container">

<h1>Secure Checkout</h1>

<a href="cart.php" class="back">← Back to Cart</a>

<?php if(isset($_GET['success'])): ?>

<div class="summary">
<h2>✅ Payment Successful</h2>
<p>Your order has been placed successfully.</p>
</div>

<?php elseif(empty($items)): ?>

<div class="summary">
<h2>🛒 Cart is Empty</h2>
</div>

<?php else: ?>

<div class="summary">

<table>
<tr>
<th>Product</th>
<th>Qty</th>
<th>Unit Price</th>
<th>Total</th>
</tr>

<?php foreach($items as $row): ?>
<tr>
<td><?= $row['product_name'] ?></td>
<td><?= $row['quantity'] ?></td>
<td>RM <?= $row['price'] ?></td>
<td>RM <?= $row['price'] * $row['quantity'] ?></td>
</tr>
<?php endforeach; ?>

</table>

</div>

<div class="total">
Total: RM <?= $total ?>
</div>

<form method="post">

<!-- PAYMENT METHODS -->
<div class="payment-methods">

<label>
<input type="radio" name="method" value="Credit Card" checked>
<div class="payment-card">💳 Credit Card</div>
</label>

<label>
<input type="radio" name="method" value="Touch n Go">
<div class="payment-card">📱 TNG</div>
</label>

<label>
<input type="radio" name="method" value="FPX">
<div class="payment-card">🏦 FPX</div>
</label>

<label>
<input type="radio" name="method" value="Boost">
<div class="payment-card">⚡ Boost</div>
</label>

</div>

<!-- CREDIT CARD -->
<div id="credit-card-box" class="payment-box">
<div class="input-box"><input placeholder="Cardholder Name"></div>
<div class="input-box"><input placeholder="Card Number"></div>
<div class="input-box"><input placeholder="MM/YY"></div>
<div class="input-box"><input placeholder="CVV"></div>
</div>

<!-- TNG (UPGRADED) -->
<div id="tng-box" class="payment-box hidden-box">
<div class="input-box"><input name="tng_phone" placeholder="TNG Phone Number"></div>
<div class="input-box"><input name="tng_name" placeholder="Account Name"></div>
<div class="input-box"><input name="tng_ref" placeholder="Reference (Optional)"></div>
</div>

<!-- FPX (UPGRADED) -->
<div id="fpx-box" class="payment-box hidden-box">

<div class="input-box">
<select name="fpx_bank">
<option value="">Select Bank</option>
<option>Maybank</option>
<option>CIMB</option>
<option>Public Bank</option>
<option>RHB</option>
<option>Hong Leong</option>
</select>
</div>

<div class="input-box"><input name="fpx_userid" placeholder="Bank ID"></div>
<div class="input-box"><input name="fpx_ref" placeholder="Reference (Optional)"></div>

</div>

<!-- BOOST (UPGRADED) -->
<div id="boost-box" class="payment-box hidden-box">
<div class="input-box"><input name="boost_phone" placeholder="Boost Phone Number"></div>
<div class="input-box"><input name="boost_ref" placeholder="Reference (Optional)"></div>
</div>

<!-- USER INFO -->
<div class="input-box">
<input name="address" value="<?= htmlspecialchars($user['address']) ?>" required>
</div>

<div class="input-box">
<input name="phone" value="<?= $user['phone'] ?>" required>
</div>

<button name="pay">🔒 Place Order</button>

</form>

<?php endif; ?>

</div>

<script>

const methods = document.querySelectorAll('input[name="method"]');

const boxes = {
"Credit Card": document.getElementById('credit-card-box'),
"Touch n Go": document.getElementById('tng-box'),
"FPX": document.getElementById('fpx-box'),
"Boost": document.getElementById('boost-box')
};

function hideAll(){
Object.values(boxes).forEach(b => b.classList.add('hidden-box'));
}

methods.forEach(m=>{
m.addEventListener('change',()=>{
hideAll();
boxes[m.value].classList.remove('hidden-box');
});
});

window.onload = () => {
hideAll();
boxes["Credit Card"].classList.remove('hidden-box');
};

</script>

</body>
</html>