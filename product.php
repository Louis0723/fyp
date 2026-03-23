<?php
session_start();
include "db.php";

// Redirect to login if not logged in,
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Fetch all products
$result = mysqli_query($conn, "SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>💻 PC STORE - Products</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
body, html { height:100%; background: linear-gradient(135deg,#0f0c29,#302b63,#24243e); color:white; overflow-x:hidden; }
#particles-js { position: fixed; width:100%; height:100%; z-index:-1; }

/* HEADER */
header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:20px 50px;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(10px);
    position: sticky;
    top:0;
    z-index:100;
    border-bottom:1px solid rgba(255,255,255,0.1);
}
header h2 { color:#00f0ff; font-size:28px; text-shadow:0 0 10px #00f0ff; cursor:pointer; }
header nav a, header nav span {
    margin-left:30px;
    color:white;
    text-decoration:none;
    font-weight:600;
    transition:0.3s;
}
header nav a:hover { color:#ff00ff; text-shadow:0 0 10px #ff00ff; }

/* TITLE */
.container { max-width:1300px; margin:100px auto; padding:0 20px; }
.title { text-align:center; font-size:42px; margin-bottom:50px; color:#00f0ff; text-shadow:0 0 20px #00f0ff; }

/* GRID */
.grid { display:grid; grid-template-columns: repeat(auto-fit,minmax(280px,1fr)); gap:40px; }

/* PRODUCT CARD */
.card {
    background: rgba(255,255,255,0.05);
    border-radius:25px;
    padding:20px;
    backdrop-filter: blur(15px);
    box-shadow: 0 10px 25px rgba(0,255,255,0.2);
    transition: transform 0.5s, box-shadow 0.5s;
    overflow:hidden;
}
.card img {
    width:100%;
    height:200px;
    object-fit:cover;
    border-radius:20px;
    transition: transform 0.5s;
}
.card:hover img { transform: scale(1.05) rotate(2deg); }
.card:hover { transform: translateY(-5px) scale(1.02); box-shadow: 0 15px 30px rgba(0,255,255,0.4); }

.card h3 { font-size:22px; color:#00f0ff; margin:12px 0; text-shadow:0 0 5px #00f0ff; }
.spec { font-size:14px; margin-bottom:4px; }
.price { font-weight:700; font-size:18px; margin-top:10px; color:#ff00ff; text-shadow:0 0 5px #ff00ff; }
.stock { font-size:12px; color:#aaa; margin-bottom:10px; }

/* BUY NOW BUTTON */
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
    box-shadow:0 5px 15px rgba(0,255,255,0.3);
}
button:hover { transform: scale(1.1); box-shadow:0 10px 25px rgba(255,0,255,0.5); }
</style>
</head>
<body>

<div id="particles-js"></div>

<!-- HEADER -->
<header>
    <h2 onclick="window.location.href='products.php'">💻 PC STORE</h2>
    <nav>
        <a href="about.php">About Us</a>
        <span>Hello, <?= $_SESSION['user']['name'] ?></span>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h1 class="title">Explore Our Futuristic PC Products</h1>
    <div class="grid">
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <div class="card">
            <img src="<?= $row['image'] ?: 'https://via.placeholder.com/300x200' ?>" alt="<?= $row['product_name'] ?>">
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
  "particles": {
    "number":{"value":70,"density":{"enable":true,"value_area":800}},
    "color":{"value":["#00f0ff","#ff00ff"]},
    "shape":{"type":"circle"},
    "opacity":{"value":0.5,"random":true},
    "size":{"value":3,"random":true},
    "line_linked":{"enable":true,"distance":150,"color":"#00f0ff","opacity":0.3,"width":1},
    "move":{"enable":true,"speed":2,"out_mode":"out"}
  },
  "interactivity":{"detect_on":"canvas",
      "events":{"onhover":{"enable":true,"mode":"grab"},"onclick":{"enable":true,"mode":"push"}},
      "modes":{"grab":{"distance":200,"line_linked":{"opacity":0.5}},"push":{"particles_nb":4}}
  },
  "retina_detect":true
});

// Buy Now button action
function buyNow(productId) {
    alert("Product ID " + productId + " added to cart! (Coming Soon)");
    // Optional: redirect to product detail page
    // window.location.href = "product_detail.php?id=" + productId;
}
</script>

</body>
</html>