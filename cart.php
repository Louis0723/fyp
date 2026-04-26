<?php
session_start();
include "db.php";

$user_id = $_SESSION['user']['user_id'];

$res = mysqli_query($conn,"
SELECT p.*, c.quantity 
FROM cart c 
JOIN products p ON c.product_id = p.product_id
WHERE c.user_id = $user_id
");
// calculate total
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

/* CONTAINER */

.container{
max-width:1000px;
margin:100px auto;
padding:20px;
text-align:center;
}

.title{
text-align:center;
font-size:40px;
margin-bottom:40px;
color:#00f0ff;
text-shadow: 0 0 15px #00f0ff;
}

/* BACK BUTTON */

.back{
display:inline-block;
margin:20px 0 40px 0; /* Top 20px, Bottom 40px */
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

/* EMPTY CART */

.empty-cart{
margin-top:50px;
color:#ff00ff;
font-size:28px;
}

.empty-cart i{
font-size:60px;
display:block;
margin-bottom:20px;
color:#00f0ff;
}

/* CART ITEM CARD */

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
transition:0.3s;
}

.cart-item:hover{
transform:translateY(-3px);
box-shadow:0 15px 30px rgba(0,255,255,0.4);
}

.cart-item img{
width:150px;
height:100px;
object-fit:cover;
border-radius:12px;
}

/* DETAILS COLUMN */

.details{
flex:1;
text-align:left;
}

.details h3{
color:#00f0ff;
margin-bottom:5px;
font-size:20px;
text-shadow:0 0 5px #00f0ff;
}

.details p{
margin:5px 0;
}

/* QUANTITY BUTTONS */

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
color:black;
font-weight:bold;
cursor:pointer;
transition:0.2s;
}

.qty button:hover{
transform:scale(1.1);
}

.qty span{
margin:0 10px;
font-weight:600;
color:#fff;
}

/* REMOVE BUTTON */

.remove{
margin-top:10px;
padding:6px 14px;
border:none;
border-radius:8px;
background:#ff0066;
color:white;
cursor:pointer;
transition:0.2s;
}

.remove:hover{
transform:scale(1.05);
}

/* TOTAL */

.total{
text-align:right;
font-size:28px;
margin-top:20px;
color:#ff00ff;
text-shadow:0 0 10px #ff00ff;
}

/* CHECKOUT BUTTON */

.checkout{
display:block;
margin-top:20px;
text-align:center;
padding:15px;
background:linear-gradient(90deg,#00f0ff,#ff00ff);
border-radius:12px;
color:white;
text-decoration:none;
font-weight:600;
font-size:18px;
transition:0.3s;
}

.checkout:hover{
transform:scale(1.03);
}

/* RESPONSIVE */

@media(max-width:700px){
.cart-item{
flex-direction:column;
align-items:flex-start;
}
.cart-item img{
width:100%;
height:auto;
}
.details{
text-align:left;
}
.qty{
justify-content:flex-start;
}
}
</style>
</head>

<body>
<div id="particles-js"></div>

<div class="container">

<h1 class="title">Your Cart</h1>

<?php if(mysqli_num_rows($res) == 0): ?>
    <div class="empty-cart">
        <i>🛒</i>
        Your cart is empty!
        <br>
        <a href="product.php" class="back">← Back to Products</a>
    </div>
<?php else: ?>

<a href="product.php" class="back">← Back to Products</a>

<?php while($row = mysqli_fetch_assoc($res)): 
$sub = $row['price'] * $row['quantity'];
$total += $sub;
?>

<div class="cart-item">
<img src="<?= !empty($row['image']) ? $row['image'] : 'https://via.placeholder.com/150x100' ?>" alt="<?= $row['product_name'] ?>">
<div class="details">
<h3><?= $row['product_name'] ?></h3>
<p>Price: RM <?= $row['price'] ?></p>
<div class="qty">
<button onclick="update(<?= $row['product_id'] ?>,'dec')">-</button>
<span><?= $row['quantity'] ?></span>
<button onclick="update(<?= $row['product_id'] ?>,'inc')">+</button>
</div>
<p>Subtotal: RM <?= $sub ?></p>
<button class="remove" onclick="removeItem(<?= $row['product_id'] ?>)">Remove</button>
</div>
</div>

<?php endwhile; ?>

<div class="total">
Total: RM <?= $total ?>
</div>

<a href="checkout.php" class="checkout">Proceed to Checkout</a>

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
"line_linked":{"enable":true,"distance":150,"color":"#00f0ff","opacity":0.3,"width":1},
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
</script>

</body>
</html>