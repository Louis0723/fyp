<?php
session_start();
include "db.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LOZ PC STORE</title>

<link rel="icon" type="image/jpeg" href="assets/storelogo.jpeg">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }

body, html {
    height:100%;
    background: linear-gradient(135deg,#0f0c29,#302b63,#24243e);
    color:white;
    overflow-x:hidden;
}

#particles-js {
    position: fixed;
    width:100%;
    height:100%;
    z-index:-1;
}

header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 50px;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(12px);
    position: sticky;
    top:0;
    z-index:100;
    border-bottom:1px solid rgba(255,255,255,0.1);
}

.logo img {
    height:50px;
    cursor:pointer;
    transition:0.3s;
}
.logo img:hover {
    transform: scale(1.1);
    filter: drop-shadow(0 0 10px #00f0ff);
}

header nav a, header nav span {
    margin-left:30px;
    color:white;
    text-decoration:none;
    font-weight:600;
    transition:0.3s;
}
header nav a:hover {
    color:#ff00ff;
    text-shadow:0 0 10px #ff00ff;
}

.container {
    max-width:1300px;
    margin:100px auto;
    padding:0 20px;
}
.title {
    text-align:center;
    font-size:42px;
    margin-bottom:50px;
    color:#00f0ff;
    text-shadow:0 0 20px #00f0ff;
}

.grid {
    display:grid;
    grid-template-columns: repeat(auto-fit,minmax(280px,1fr));
    gap:40px;
}

.card {
    background: rgba(255,255,255,0.05);
    border-radius:25px;
    padding:20px;
    backdrop-filter: blur(15px);
    box-shadow: 0 10px 25px rgba(0,255,255,0.2);
    transition:0.4s;
}
.card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0,255,255,0.5);
}

.card img {
    width:100%;
    height:200px;
    object-fit:cover;
    border-radius:20px;
}

.card h3 {
    font-size:22px;
    color:#00f0ff;
    margin:12px 0;
}
.spec { 
    font-size:14px; 
    margin-bottom:4px; 
}

.price {
    font-weight:700;
    font-size:18px;
    margin-top:10px;
    color:#ff00ff;
}
.stock {
    font-size:12px;
    color:#aaa;
    margin-bottom:10px;
}

button {
    width:100%;
    padding:12px;
    border:none;
    border-radius:12px;
    background: linear-gradient(90deg,#00f0ff,#ff00ff);
    color:white;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}
button:hover {
    transform: scale(1.1);
    box-shadow:0 10px 25px rgba(255,0,255,0.5);
}
</style>
</head>

<body>

<div id="particles-js"></div>

<header>
    <div class="logo" onclick="window.location.href='products.php'">
        <img src="storelogo.jpeg" alt="LOZ PC STORE">
    </div>

    <nav>
        <a href="about.php">About Us</a>
        <span>Hello, <?= $_SESSION['user'] ['name'] ?></span>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h1 class="title">Explore Our PC Collection</h1>

    <div class="grid">
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <div class="card">
            <img src="<?= $row['image'] ?: 'https://via.placeholder.com/300x200' ?>">

            <h3><?= $row['product_name'] ?></h3>

            <div class="spec">CPU: <?= $row['cpu'] ?></div>
            <div class="spec">GPU: <?= $row['gpu'] ?></div>
            <div class="spec">RAM: <?= $row['ram'] ?></div>
            <div class="spec">Storage: <?= $row['storage'] ?></div>
            <div class="spec">Motherboard: <?= $row['motherboard'] ?></div>

            <div class="price">RM <?= $row['price'] ?></div>
            <div class="stock">Stock: <?= $row['stock'] ?></div>

            <button onclick="buyNow('<?= $row['product_id'] ?>')">Buy Now</button>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
particlesJS("particles-js", {
  particles: {
    number:{value:70},
    color:{value:["#00f0ff","#ff00ff"]},
    shape:{type:"circle"},
    opacity:{value:0.5,random:true},
    size:{value:3,random:true},
    line_linked:{enable:true,distance:150,color:"#00f0ff",opacity:0.3,width:1},
    move:{enable:true,speed:2}
  }
});

function buyNow(id){
    alert("Product ID " + id + " added to cart! (Coming Soon)");
}
</script>

</body>
</html>