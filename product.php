<?php
session_start();
include "db.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

/* GET FILTER VALUES */
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? '';
$category = $_GET['category'] ?? '';

/* MAIN QUERY */
$sql = "SELECT * FROM products WHERE 1";

/* SEARCH */
if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $sql .= " AND product_name LIKE '%$search%'";
}

/* CATEGORY FILTER (FIXED PROPERLY) */
if (!empty($category)) {
    $category = mysqli_real_escape_string($conn, $category);
    $sql .= " AND category='$category'";
}

/* SORT */
if ($sort == "low") {
    $sql .= " ORDER BY price ASC";
} else if ($sort == "high") {
    $sql .= " ORDER BY price DESC";
}

/* EXECUTE */
$result = mysqli_query($conn, $sql);

/* CART COUNT */
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

header nav a{
margin-left:25px;
color:white;
text-decoration:none;
font-weight:600;
transition:0.3s;
}

header nav a:hover{
color:#ff00ff;
}

/* CART */
.cart-badge{
background:red;
border-radius:50%;
padding:3px 8px;
font-size:12px;
margin-left:5px;
}

/* CONTAINER */
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

/* FILTER BAR CENTER */
.filter-bar{
display:flex;
justify-content:center;
align-items:center;
gap:10px;
flex-wrap:wrap;
margin-bottom:30px;
}

/* GRID */
.grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
gap:35px;
}

/* CARD */
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
<a href="cart.php">Cart 🛒 <span class="cart-badge"><?= $cart_count ?></span></a>
<a href="history.php">Orders</a>
<a href="profile.php">👤 Profile</a>
<a href="logout.php">Logout</a>
</nav>
</header>

<div class="container">

<h1 class="title">Explore Our Futuristic PC Products</h1>

<form method="get">
<div class="filter-bar">

<!-- SEARCH -->
<input type="text" name="search"
placeholder="Search product..."
value="<?= htmlspecialchars($search) ?>"
style="padding:10px;width:250px;border-radius:15px;">

<select name="category" style="padding:10px;border-radius:15px;">
<option value="">Category</option>

<option value="PC" <?= $category=='PC'?'selected':'' ?>>
PC
</option>

<option value="Laptop" <?= $category=='Laptop'?'selected':'' ?>>
Laptop
</option>

<option value="Keyboard" <?= $category=='Keyboard'?'selected':'' ?>>
Keyboard
</option>

<option value="Mouse" <?= $category=='Mouse'?'selected':'' ?>>
Mouse
</option>

</select>

<!-- SORT -->
<select name="sort" style="padding:10px;border-radius:15px;">
<option value="">Sort By</option>
<option value="low" <?= $sort=='low'?'selected':'' ?>>Price Low → High</option>
<option value="high" <?= $sort=='high'?'selected':'' ?>>Price High → Low</option>
</select>

<button type="submit" style="padding:10px;border-radius:15px;">Search</button>

</div>
</form>

<div class="grid">

<?php while($row = mysqli_fetch_assoc($result)): ?>

<div class="card" onclick="window.location.href='product_detail.php?id=<?= $row['product_id'] ?>'">

<img src="<?= !empty($row['image']) ? $row['image'] : 'https://via.placeholder.com/300x200' ?>">

<h3><?= $row['product_name'] ?></h3>

<?php if($row['category'] == "PC" || $row['category'] == "Laptop"): ?>

<div class="spec">CPU: <?= $row['cpu'] ?></div>
<div class="spec">GPU: <?= $row['gpu'] ?></div>
<div class="spec">RAM: <?= $row['ram'] ?></div>
<div class="spec">Storage: <?= $row['storage'] ?></div>
<div class="spec">Motherboard: <?= $row['motherboard'] ?></div>

<?php elseif($row['category'] == "Keyboard"): ?>

<div class="spec">Switch Type: <?= $row['switch_type'] ?></div>
<div class="spec">Keyboard Size: <?= $row['keyboard_size'] ?></div>

<?php elseif($row['category'] == "Mouse"): ?>

<div class="spec">DPI: <?= $row['dpi'] ?></div>
<div class="spec">Mouse Type: <?= $row['mouse_type'] ?></div>

<?php endif; ?>

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
particlesJS("particles-js",{
"particles":{
"number":{"value":70,"density":{"enable":true}},
"color":{"value":["#00f0ff","#ff00ff"]},
"shape":{"type":"circle"},
"opacity":{"value":0.5,"random":true},
"size":{"value":3,"random":true},
"move":{"enable":true,"speed":2}
}
});

function buyNow(id){
fetch("add_to_cart.php?id="+id)
.then(()=>{ alert("Added to cart!"); location.reload(); });
}
</script>

</body>
</html>