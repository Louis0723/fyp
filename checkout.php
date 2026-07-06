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

$items = [];
$total = 0;
//this is buy single product 
if(isset($_GET['id']) && isset($_GET['qty'])){

    $product_id = intval($_GET['id']);
    $quantity = intval($_GET['qty']);

    $res = mysqli_query($conn,"
    SELECT *
    FROM products
    WHERE product_id = $product_id
    ");

    if(mysqli_num_rows($res) == 0){
        die("Product not found.");
    }

    $row = mysqli_fetch_assoc($res);

    if($quantity > $row['stock']){
    die("Not enough stock available.");
}

if($quantity < 1){
    die("Invalid quantity.");
}

    $row['quantity'] = $quantity;

    $items[] = $row;

    $total = $row['price'] * $quantity;

    $buy_now = true;

}else{

    $itemArray = $_SESSION['checkout_items'] ?? [];

    if (empty($itemArray)) {
        header("Location: cart.php");
        exit;
    }

    $itemList = implode(',', $itemArray);

    $res = mysqli_query($conn,"
    SELECT p.*, c.quantity
    FROM cart c
    JOIN products p ON c.product_id = p.product_id
    WHERE c.user_id = $user_id
    AND c.product_id IN ($itemList)
    ");

    while($row = mysqli_fetch_assoc($res)){
        if($row['quantity'] > $row['stock']){
    die("Product stock is insufficient: " . $row['product_name']);
}
        $total += $row['price'] * $row['quantity'];
        $items[] = $row;
    }

    $buy_now = false;
}


$year = date("y");
$day = date("d");
$month = date("m");

$prefix = $year . $day . $month;

if(isset($_POST['pay'])){

$address=mysqli_real_escape_string(
$conn,
$_POST['address']
);

$phone=mysqli_real_escape_string(
$conn,
$_POST['phone']
);

$method=mysqli_real_escape_string(
$conn,
$_POST['method']
);

$payment_success=false;
$error_msg="";
$account_used="";

if($method=="Credit Card"){

$card_name=mysqli_real_escape_string(
$conn,
$_POST['card_name']
);

$card_number=mysqli_real_escape_string(
$conn,
$_POST['card_number']
);

$expiry=mysqli_real_escape_string(
$conn,
$_POST['expiry']
);

$cvv=mysqli_real_escape_string(
$conn,
$_POST['cvv']
);

$check=mysqli_query($conn,"
SELECT *
FROM dummy_cards
WHERE card_name='$card_name'
AND card_number='$card_number'
AND expiry='$expiry'
AND cvv='$cvv'
LIMIT 1
");

if(mysqli_num_rows($check)==0){

$error_msg="Invalid card details!";

}else{

sleep(2);

$payment_success=true;
$account_used=$card_number;

}

}

elseif($method=="Touch n Go"){

$tng_acc=mysqli_real_escape_string(
$conn,
$_POST['tng_number']
);

$tng_pin=mysqli_real_escape_string(
$conn,
$_POST['tng_pin']
);

$check=mysqli_query($conn,"
SELECT *
FROM dummy_tng
WHERE account_number='$tng_acc'
AND pin='$tng_pin'
LIMIT 1
");

if(mysqli_num_rows($check)==0){

$error_msg="Invalid TNG account!";

}else{

$tng=mysqli_fetch_assoc($check);

if($tng['balance']<$total){

$error_msg="Insufficient TNG balance!";

}else{

sleep(2);

mysqli_query($conn,"
UPDATE dummy_tng
SET balance=balance-$total
WHERE id={$tng['id']}
");

$payment_success=true;

$account_used=$tng_acc;

}

}

}

elseif($method=="FPX"){

$bank=mysqli_real_escape_string(
$conn,
$_POST['fpx_bank']
);

$bank_id=mysqli_real_escape_string(
$conn,
$_POST['fpx_userid']
);

$password=mysqli_real_escape_string(
$conn,
$_POST['fpx_password']
);

$check=mysqli_query($conn,"
SELECT *
FROM dummy_fpx
WHERE bank_name='$bank'
AND bank_id='$bank_id'
AND password='$password'
LIMIT 1
");

if(mysqli_num_rows($check)==0){

$error_msg="Invalid FPX login!";

}else{

$fpx=mysqli_fetch_assoc($check);

if($fpx['balance']<$total){

$error_msg="Insufficient FPX balance!";

}else{

sleep(2);

mysqli_query($conn,"
UPDATE dummy_fpx
SET balance=balance-$total
WHERE id={$fpx['id']}
");

$payment_success=true;

$account_used=$bank_id;

}

}

}

elseif($method=="Boost"){

$boost_acc=mysqli_real_escape_string(
$conn,
$_POST['account_number']
);

$boost_pin=mysqli_real_escape_string(
$conn,
$_POST['boost_pin']
);

$check=mysqli_query($conn,"
SELECT *
FROM dummy_boost
WHERE account_number='$boost_acc'
AND pin='$boost_pin'
LIMIT 1
");

if(mysqli_num_rows($check)==0){

$error_msg="Invalid Boost account!";

}else{

$boost=mysqli_fetch_assoc($check);

if($boost['balance']<$total){

$error_msg="Insufficient Boost balance!";

}else{

sleep(2);

mysqli_query($conn,"
UPDATE dummy_boost
SET balance=balance-$total
WHERE id={$boost['id']}
");

$payment_success=true;

$account_used=$boost_acc;

}

}

}


if(!$payment_success){

header(
"Location: checkout.php?error=1&msg="
.urlencode($error_msg)
);

exit;

}


$res=mysqli_query($conn,"
SELECT order_id
FROM orders
WHERE order_id LIKE '$prefix%'
ORDER BY order_id DESC
LIMIT 1
");

$row=mysqli_fetch_assoc($res);

if($row){

$last=(int)substr(
$row['order_id'],
6
);

$next=str_pad(
$last+1,
4,
"0",
STR_PAD_LEFT
);

}else{

$next="0001";

}

$order_id=$prefix.$next;

$transaction_id=
"TXN".rand(100000,999999);

mysqli_query($conn,"
INSERT INTO payment_transactions
(
transaction_id,
payment_method,
account_used,
amount,
status
)
VALUES
(
'$transaction_id',
'$method',
'$account_used',
$total,
'Success'
)
");


mysqli_query($conn,"
INSERT INTO orders
(
order_id,
user_id,
total_price,
address,
phone,
payment_method
)
VALUES
(
'$order_id',
$user_id,
$total,
'$address',
'$phone',
'$method'
)
");

/* ADD THIS */
mysqli_query($conn,"
INSERT INTO admin_notifications
(
order_id,
is_read,
created_at
)
VALUES
(
'$order_id',
0,
NOW()
)
");


foreach($items as $row){

mysqli_query($conn,"
INSERT INTO order_items
(
order_id,
product_id,
quantity,
price
)
VALUES
(
'$order_id',
{$row['product_id']},
{$row['quantity']},
{$row['price']}
)
");

mysqli_query($conn,"
UPDATE products
SET stock=stock-{$row['quantity']}
WHERE product_id={$row['product_id']}
");


if(!$buy_now){

mysqli_query($conn,"
DELETE FROM cart
WHERE user_id=$user_id
AND product_id={$row['product_id']}
");

}

}

header(
"Location: checkout.php?success=1"
);

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

*{margin:0;
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
width:100%;height:100%;
z-index:-1;
}
.container{max-width:750px;
margin:60px auto;padding:35px;
border-radius:25px;
background:rgba(255,255,255,0.05);
backdrop-filter:blur(15px);
box-shadow:0 10px 30px rgba(0,0,0,0.5);
}

h1{text-align:center;
margin-bottom:25px;
color:#00f0ff;text-shadow:0 0 15px #00f0ff;
}

.back{display:inline-block;
margin-bottom:25px;color:white;
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

.summary table{
width:100%;
border-collapse:collapse;
}
.summary th,.summary td{
padding:12px;
text-align:left;
border-bottom:1px solid rgba(255,255,255,0.2);
}

.summary th{color:#00f0ff;font-weight:600;}
.total{font-size:30px;
color:#00f0ff;margin-bottom:25px;
text-align:center;
font-weight:700;
}

.payment-methods{display:grid;
grid-template-columns:repeat(2,1fr);
gap:18px;
margin-bottom:25px;
}
.payment-methods label{cursor:pointer;}

.payment-methods input{display:none;}

.payment-card{padding:20px;
border-radius:20px;
background:rgba(255,255,255,0.06);
border:2px solid transparent;
text-align:center;
transition:0.3s;font-weight:600;
}

.payment-methods input:checked + .payment-card{border-color:#00f0ff;
box-shadow:0 0 20px #00f0ff;
}

.input-box{margin-bottom:15px;}

.input-box input,
.input-box select{
width:100%;
padding:14px;
border-radius:12px;
border:2px solid #00f0ff;
background:rgba(255,255,255,0.05);
color:white;
outline:none;
font-size:15px;
}

.input-box select option{
background:#24243e;
color:white;
}

.hidden-box{display:none;}

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
}

</style>
</head>

<body>

<div id="particles-js"></div>

<div class="container">

<!-- after checkout-->
<h1>Secure Checkout</h1>
<?php if(isset($_GET['success'])): ?>
<a href="product.php" class="back">← Back to Products</a>
<?php else: ?>
    
<!-- before checkout-->
<a href="javascript:history.back()" class="back">← Go Back</a>
<?php endif; ?>

<?php if(isset($_GET['success'])): ?>
<div class="summary"><h2>✅ Payment Successful</h2></div>
<?php elseif(isset($_GET['error'])): ?>
<div class="summary" style="border:1px solid red;">
<h2>❌ Payment Failed</h2>
<p><?= htmlspecialchars($_GET['msg']) ?></p>
</div>
<?php endif; ?>

<?php if(empty($items)): ?>



<?php else: ?>

<div class="summary">
<table>
<tr><th>Product</th><th>Unit Price</th><th>Qty</th><th>Total</th></tr>
<?php foreach($items as $row): ?>
<tr>
<td><?= $row['product_name'] ?></td>
<td>RM <?= $row['price'] ?></td>
<td><?= $row['quantity'] ?></td>
<td>RM <?= $row['price'] * $row['quantity'] ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

<div class="total">Total: RM <?= $total ?></div>

<form method="post">

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
<div class="input-box"><input name="card_name" placeholder="Cardholder Name"></div>
<div class="input-box"><input name="card_number" placeholder="Card Number"></div>
<div class="input-box"><input name="expiry" placeholder="MM/YY"></div>
<div class="input-box"><input name="cvv" placeholder="CVV"></div>
</div>

<!-- TNG -->
<div id="tng-box" class="payment-box hidden-box">

<div class="input-box">
<input name="tng_number" placeholder="Account Number">
</div>

<div class="input-box">
<input name="tng_pin" placeholder="6-digit PIN">
</div>

</div>

<!-- FPX -->
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

<div class="input-box">
<input name="fpx_userid" placeholder="Bank ID">
</div>

<div class="input-box">
<input type="password" name="fpx_password" placeholder="Password">
</div>

</div>

<!-- payment BOOST -->
<div id="boost-box" class="payment-box hidden-box">

<div class="input-box">
<input name="account_number" placeholder="Account Number">
</div>

<div class="input-box">
<input name="boost_pin" placeholder="6-digit PIN">
</div>

</div>
<div class="input-box">
<input name="address" placeholder="Address" value="<?= htmlspecialchars($user['address']) ?>" required> 
</div>

<div class="input-box">
<input name="phone" placeholder="Phone Number" value="<?= $user['phone'] ?>" required> </div>

<button name="pay">🔒 Place Order</button>

</form>

<?php endif; ?>

</div>

<script>
const methods = document.querySelectorAll('input[name="method"]');

const boxes = {
"Credit Card": document.getElementById("credit-card-box"),
"Touch n Go": document.getElementById("tng-box"),
"FPX": document.getElementById("fpx-box"),
"Boost": document.getElementById("boost-box")
};

function hideAll(){
Object.values(boxes).forEach(b => b.classList.add("hidden-box"));
}

methods.forEach(m=>{
m.addEventListener("change", ()=>{
hideAll();
boxes[m.value].classList.remove("hidden-box");
});
});

window.onload = ()=>{
hideAll();
boxes["Credit Card"].classList.remove("hidden-box");
};
</script>

</body>
</html>