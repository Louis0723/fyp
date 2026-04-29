<?php
session_start();
include "db.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM products");

$user_id = $_SESSION['user']['user_id'];

$res_cart = mysqli_query($conn, "SELECT SUM(quantity) as total FROM cart WHERE user_id=$user_id");
$data = mysqli_fetch_assoc($res_cart);

$cart_count = $data['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PC STORE - Products</title>

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
background: linear-gradient(135deg,#0f0c29,#302b63,#24243e);
color:white;
min-height:100vh;
overflow-x:hidden;
}

#particles-js{
position:fixed;
width:100%;
height:100%;
z-index:-1;
pointer-events:none;
}

/* HEADER */

header{
display:flex;
justify-content:space-between;
align-items:center;
padding:20px 50px;
background:rgba(0,0,0,0.5);
backdrop-filter:blur(10px);
position:sticky;
top:0;
z-index:100;
}

.logo img{
height:60px;
cursor:pointer;
}

header nav a,
header nav span{
margin-left:25px;
color:white;
text-decoration:none;
font-weight:600;
transition:0.3s;
}

header nav a:hover{
color:#ff00ff;
}

/* CART BADGE */

.cart-badge{
background:red;
border-radius:50%;
padding:3px 8px;
font-size:12px;
margin-left:5px;
}

/* PAGE */

.container{
max-width:1300px;
margin:100px auto;
padding:0 20px;
}

.title{
text-align:center;
font-size:40px;
margin-bottom:50px;
color:#00f0ff;
}

/* GRID */

.grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
gap:35px;
}

/* PRODUCT CARD */

.card{
background:rgba(255,255,255,0.05);
border-radius:20px;
padding:20px;
backdrop-filter:blur(15px);
box-shadow:0 10px 25px rgba(0,0,0,0.4);
transition:0.3s;

display:flex;
flex-direction:column;

cursor:pointer;
}

.card:hover{
transform:translateY(-6px) scale(1.02);
box-shadow:0 15px 35px rgba(0,255,255,0.35);
}

.card img{
width:100%;
height:200px;
object-fit:cover;
border-radius:15px;
margin-bottom:10px;
transition:0.3s;
}

.card:hover img{
transform:scale(1.05);
}

.card h3{
font-size:22px;
color:#00f0ff;
margin:10px 0;
}

.spec{
font-size:14px;
margin-bottom:4px;
}

.price{
font-weight:700;
font-size:18px;
margin-top:8px;
color:#ff00ff;
}

.stock{
font-size:12px;
color:#ccc;
margin-bottom:12px;
}

/* BUTTON */

.card button{
margin-top:auto;
width:100%;
padding:12px;
border:none;
border-radius:10px;
background:linear-gradient(90deg,#00f0ff,#ff00ff);
color:white;
font-weight:600;
cursor:pointer;
transition:0.3s;
}

.card button:hover{
transform:scale(1.05);
box-shadow:0 0 15px #00f0ff,0 0 25px #ff00ff;
}

button:disabled{
background:#555;
cursor:not-allowed;
}

</style>
</head>

<body>

<div id="particles-js"></div>

<header>

<div class="logo" onclick="window.location.href='product.php'">
<img src="storelogo.jpeg">
</div>

<nav>

<a href="about.php">About Us</a>

<a href="cart.php">
Cart 🛒 <span class="cart-badge"><?= $cart_count ?></span>
</a>

<a href="history.php">Orders</a>

<span>Hello, <?= $_SESSION['user']['name'] ?></span>

<a href="logout.php">Logout</a>

</nav>

</header>

<div class="container">

<h1 class="title">Explore Our Futuristic PC Products</h1>

<div class="grid">

<?php while($row = mysqli_fetch_assoc($result)): ?>

<div class="card" onclick="goDetail(<?= $row['product_id'] ?>)">

<img src="<?= !empty($row['image']) ? $row['image'] : 'https://via.placeholder.com/300x200' ?>">

<h3><?= $row['product_name'] ?></h3>

<div class="spec">CPU: <?= $row['cpu'] ?></div>
<div class="spec">GPU: <?= $row['gpu'] ?></div>
<div class="spec">RAM: <?= $row['ram'] ?></div>
<div class="spec">Storage: <?= $row['storage'] ?></div>
<div class="spec">Motherboard: <?= $row['motherboard'] ?></div>

<div class="price">RM <?= $row['price'] ?></div>

<div class="stock">Stock: <?= $row['stock'] ?></div>

<?php if($row['stock'] > 0): ?>

<button onclick="event.stopPropagation(); buyNow(<?= $row['product_id'] ?>)">
Add to Cart
</button>

<?php else: ?>

<button disabled>Out of Stock</button>

<?php endif; ?>

</div>

<?php endwhile; ?>

</div>
</div>

<script>

/* PARTICLES */

particlesJS("particles-js",{
"particles":{
"number":{"value":70,"density":{"enable":true,"value_area":800}},
"color":{"value":["#00f0ff","#ff00ff"]},
"shape":{"type":"circle"},
"opacity":{"value":0.5,"random":true},
"size":{"value":3,"random":true},
"line_linked":{"enable":true,"distance":150,"color":"#00f0ff","opacity":0.3,"width":1},
"move":{"enable":true,"speed":2}
},
"interactivity":{
"detect_on":"canvas",
"events":{
"onhover":{"enable":true,"mode":"grab"},
"onclick":{"enable":true,"mode":"push"}
},
"modes":{
"grab":{"distance":200,"line_linked":{"opacity":0.5}},
"push":{"particles_nb":4}
}
},
"retina_detect":true
});

/* ADD TO CART *

function buyNow(id){
fetch("add_to_cart.php?id="+id)
.then(()=>{
alert("Added to cart!");
location.reload();
});
}

/* PRODUCT DETAIL */

function goDetail(id){
window.location.href="product_detail.php?id="+id;
}

</script>

</body>
</html>
