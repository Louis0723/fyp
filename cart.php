<?php
session_start();
include "db.php";

if (!isset($_SESSION['user']['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user']['user_id'];

$res = mysqli_query($conn,"
SELECT p.*, c.quantity 
FROM cart c 
JOIN products p ON c.product_id = p.product_id
WHERE c.user_id = $user_id
");

if (!$res) {
    die("Query Error: " . mysqli_error($conn));
}

$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>Your Cart</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

<style>
*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;
}

body{
background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);
color:white;
min-height:100vh;
}

#particles-js{
position:fixed;
width:100%;
height:100%;
z-index:-1;
pointer-events:none;
}

.container{
max-width:800px;
margin:100px auto;
padding:20px;
text-align:center;
}

.title{
font-size:40px;
margin-bottom:40px;
color:#00f0ff;
text-shadow:0 0 15px #00f0ff;
}

.back{
display:inline-block;
margin:20px 0 40px 0;
padding:10px 20px;
background:#00f0ff;
color:black;
border-radius:10px;
text-decoration:none;
font-weight:600;
transition:0.3s;
}

.back:hover{
transform:scale(1.05);
}

/* CART ITEM */
.cart-item{
background:rgba(255,255,255,0.05);
padding:20px;
border-radius:20px;
margin-bottom:20px;
backdrop-filter:blur(15px);
box-shadow:0 10px 25px rgba(0,0,0,0.5);
display:flex;
align-items:center;
gap:20px;
text-align:left;
}

/* BIG CHECKBOX */
.select-item{
transform:scale(1.3);
margin-top:8px;
cursor:pointer;
}

.cart-item img{
width:150px;
height:100px;
object-fit:cover;
border-radius:12px;
}

.details{
flex:1;
}

.details h3{
color:#00f0ff;
margin-bottom:5px;
}

.qty{
margin:10px 0;
display:flex;
align-items:center;
}

.qty button{
width:30px;
height:30px;
border:none;
border-radius:8px;
background:#00f0ff;
cursor:pointer;
font-weight:bold;
}

.qty span{
margin:0 10px;
}

.remove{
margin-top:10px;
padding:6px 14px;
border:none;
border-radius:8px;
background:#ff0066;
color:white;
cursor:pointer;
}

/* TOTAL BOX */
.total-box{
margin-top:30px;
background:rgba(255,255,255,0.07);
padding:20px;
border-radius:15px;
backdrop-filter:blur(10px);
box-shadow:0 10px 25px rgba(0,0,0,0.4);
text-align:left;
}

.total-box div{
display:flex;
justify-content:space-between;
margin:6px 0;
font-size:18px;
}

.total-box h2{
color:#00f0ff;
margin-bottom:15px;
text-align:left;
}

.total-box .grand{
font-size:26px;
color:#ff00ff;
font-weight:bold;
display:flex;
justify-content:space-between;
margin-top:10px;
}

.checkout{
display:block;
margin-top:20px;
padding:15px;
background:linear-gradient(90deg,#00f0ff,#ff00ff);
border-radius:12px;
color:white;
text-decoration:none;
font-weight:600;
text-align:center;
}
</style>
</head>

<body>
<div id="particles-js"></div>

<div class="container">

<h1 class="title">Your Cart</h1>

<?php if(mysqli_num_rows($res) == 0): ?>

<div style="color:#ff00ff; font-size:24px;">
🛒 Your cart is empty
<br><br>
<a href="product.php" class="back">← Back to Products</a>
</div>

<?php else: ?>

<!-- SELECT ALL -->


<a href="product.php" class="back">← Back to Products</a>

<div style="text-align:left;margin-bottom:15px;">
<label>
<input type="checkbox" id="selectAll" checked style="transform:scale(1.3);margin-right:8px;">
Select All
</label>
</div>

<?php while($row = mysqli_fetch_assoc($res)):

$sub = $row['price'] * $row['quantity'];
$total += $sub;

?>

<div class="cart-item">

<!-- CHECKBOX ADDED -->
<input type="checkbox"
class="select-item"
data-id="<?= $row['product_id'] ?>"
data-price="<?= $row['price'] ?>"
data-qty="<?= $row['quantity'] ?>"
checked>

<img src="<?= !empty($row['image']) ? $row['image'] : 'https://via.placeholder.com/150x100' ?>">

<div class="details">

<h3><?= $row['product_name'] ?></h3>
<p>Price: RM <?= number_format($row['price'],2) ?></p>

<div class="qty">
<button onclick="update(<?= $row['product_id'] ?>,'dec')">-</button>
<span><?= $row['quantity'] ?></span>
<button onclick="update(<?= $row['product_id'] ?>,'inc')">+</button>
</div>

<p>Subtotal: RM <?= number_format($sub,2) ?></p>

<button class="remove" onclick="removeItem(<?= $row['product_id'] ?>)">Remove</button>

</div>
</div>

<?php endwhile; ?>

<?php
$delivery_fee = 5;
$tax_rate = 0.06;

$tax = $total * $tax_rate;
$grand_total = $total + $tax + $delivery_fee;
?>

<!-- ORDER SUMMARY -->
<div class="total-box">

<h2>Order Summary</h2>

<div>
<span>Subtotal</span>
<span>RM <?= number_format($total,2) ?></span>
</div>

<div>
<span>Tax (6%)</span>
<span>RM <?= number_format($tax,2) ?></span>
</div>

<div>
<span>Delivery Fee</span>
<span>RM <?= number_format($delivery_fee,2) ?></span>
</div>

<hr style="margin:10px 0; opacity:0.3;">

<div class="grand">
<span>Grand Total</span>
<span>RM <?= number_format($grand_total,2) ?></span>
</div>

<a href="javascript:void(0)" class="checkout" onclick="goCheckout()">Proceed to Checkout</a>

</div>

<?php endif; ?>

</div>

<script>
particlesJS("particles-js",{
"particles":{
"number":{"value":70},
"color":{"value":["#00f0ff","#ff00ff"]},
"shape":{"type":"circle"},
"opacity":{"value":0.5},
"size":{"value":3,"random":true},
"line_linked":{"enable":true,"distance":150,"color":"#00f0ff","opacity":0.3},
"move":{"enable":true,"speed":2}
}
});

function update(id,action){
fetch(`update_cart.php?id=${id}&action=${action}`)
.then(()=>location.reload());
}

function removeItem(id){
fetch("remove_cart.php?id="+id)
.then(()=>location.reload());
}

/* SELECT ALL Nice*/
document.getElementById("selectAll").addEventListener("change",function(){
document.querySelectorAll(".select-item").forEach(i=>{
i.checked = this.checked;
});
});

function goCheckout(){
    let selected = [];

    document.querySelectorAll(".select-item:checked").forEach(i=>{
        selected.push(i.dataset.id);
    });

    if(selected.length === 0){
        alert("No items selected");
        return;
    }

    fetch("set_checkout_session.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ items: selected })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            window.location.href = "checkout.php";
        }
    });
}
</script>

</body>
</html>