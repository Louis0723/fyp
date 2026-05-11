<?php
session_start();
include "db.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$res = mysqli_query($conn,"SELECT * FROM products WHERE product_id=$id");

if(!$res || mysqli_num_rows($res) == 0){
    die("❌ Product not found.");
}

$row = mysqli_fetch_assoc($res);
$reviews = mysqli_query($conn,"
SELECT r.*, u.name
FROM reviews r
JOIN users u ON r.user_id = u.user_id
WHERE r.product_id = $id
ORDER BY r.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?= $row['product_name'] ?></title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

<style>
*{ margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }

body{
    background: linear-gradient(135deg,#0f0c29,#302b63,#24243e);
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
    max-width:1000px;
    margin:80px auto;
    padding:30px;
}

.card{
    display:flex;
    gap:40px;
    background: rgba(255,255,255,0.05);
    border-radius:25px;
    padding:30px;
    backdrop-filter: blur(15px);
    box-shadow:0 15px 40px rgba(0,255,255,0.2);
}

.image{
    flex:1;
}

.image img{
    width:100%;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,255,255,0.4);
}

.details{
    flex:1;
}

h2{
    font-size:30px;
    margin-bottom:15px;
    color:#00f0ff;
    text-shadow:0 0 15px #00f0ff;
}

.spec{
    margin-bottom:8px;
    font-size:15px;
}

.price{
    font-size:28px;
    margin:20px 0;
    color:#ff00ff;
    text-shadow:0 0 10px #ff00ff;
}

.stock{
    margin-bottom:15px;
    color:#aaa;
}

.desc{
    margin:15px 0;
    font-size:14px;
    line-height:1.5;
}

button{
    padding:12px 20px;
    border:none;
    border-radius:12px;
    background: linear-gradient(90deg,#00f0ff,#ff00ff);
    color:white;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
    width:100%;
}
button:hover{
    transform:scale(1.05);
    box-shadow:0 0 15px #00f0ff,0 0 25px #ff00ff;
}

button:disabled{
    background:gray;
    cursor:not-allowed;
}

.back{
    display:inline-block;
    margin-bottom:20px;
    padding:10px 20px;
    background:#ff00ff;
    color:white;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
}
.back:hover{
    transform:scale(1.05);
}

.qty-box{
    display:flex;
    align-items:center;
    gap:10px;
    margin:20px 0;
}

.qty-btn{
    width:45px !important;
    height:45px;

    display:flex;
    align-items:center;
    justify-content:center;

    border:none;
    border-radius:10px;

    background:#00f0ff;
    color:black;

    font-size:22px;
    font-weight:bold;
    line-height:1;

    cursor:pointer;
    padding:0;
}

.qty-box input{
    width:80px;
    text-align:center;
    font-size:18px;
    border:none;
    border-radius:10px;
    padding:10px;
    background:rgba(255,255,255,0.15);
    color:white;
}

.btn-group{
    display:flex;
    gap:15px;
    margin-top:20px;
}

.buy-btn{
    background: linear-gradient(90deg,#ff9800,#ff00ff);
}

.reviews-box{
    margin-top:40px;
    background:rgba(255,255,255,0.05);
    padding:25px;
    border-radius:20px;
}

.review-item{
    padding:20px;
    margin-bottom:20px;
    background:rgba(255,255,255,0.05);
    border-radius:15px;
}

</style>
</head>

<body>

<div id="particles-js"></div>

<div class="container">

<a href="product.php" class="back">⬅ Back to Products</a>

<div class="reviews-box">

<h2 style="margin-bottom:25px;">⭐ Customer Reviews</h2>

<?php if(mysqli_num_rows($reviews) == 0): ?>
    <p>No reviews yet.</p>
<?php endif; ?>

<?php while($r = mysqli_fetch_assoc($reviews)): ?>

<div class="review-item">

    <h3><?= htmlspecialchars($r['name']) ?></h3>

    <div style="color:gold;font-size:20px;margin:8px 0;">
        <?php
        for($i=1;$i<=5;$i++){
            echo ($i <= $r['rating']) ? "★" : "☆";
        }
        ?>
    </div>

    <p><?= htmlspecialchars($r['review_text']) ?></p>

    <?php if(!empty($r['image'])): ?>
        <img src="uploads/reviews/<?= htmlspecialchars($r['image']) ?>"
             style="width:150px;margin-top:15px;border-radius:12px;">
    <?php endif; ?>

    <div style="margin-top:10px;font-size:13px;color:#aaa;">
        <?= $r['created_at'] ?>
    </div>

</div>

<?php endwhile; ?>

</div>

<div class="card">

    <div class="image">
        <img src="<?= !empty($row['image']) ? $row['image'] : 'https://via.placeholder.com/400' ?>">
    </div>

    <div class="details">
        <h2><?= $row['product_name'] ?></h2>

        <div class="spec">CPU: <?= $row['cpu'] ?></div>
        <div class="spec">GPU: <?= $row['gpu'] ?></div>
        <div class="spec">RAM: <?= $row['ram'] ?></div>
        <div class="spec">Storage: <?= $row['storage'] ?></div>
        <div class="spec">Motherboard: <?= $row['motherboard'] ?></div>

        <div class="desc">
            <?= !empty($row['description']) ? $row['description'] : 'No description available.' ?>
        </div>

        <div class="price">RM <?= $row['price'] ?></div>
        <div class="stock">Stock: <?= $row['stock'] ?></div>

<div class="qty-box">
    <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>

    <input type="number" id="qty" value="1" min="1" max="<?= $row['stock'] ?>">

    <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
</div>

<div class="btn-group">

    <button 
        onclick="add(<?= $row['product_id'] ?>)"
        <?= ($row['stock'] <= 0) ? 'disabled' : '' ?>
    >
        <?= ($row['stock'] <= 0) ? 'Out of Stock' : 'Add to Cart' ?>
    </button>

    <button 
        class="buy-btn"
        onclick="buyNow(<?= $row['product_id'] ?>)"
        <?= ($row['stock'] <= 0) ? 'disabled' : '' ?>
    >
        Buy Now
    </button>

</div>
    </div>

</div>
</div>

<script>
particlesJS("particles-js",{
  particles:{
    number:{value:70},
    color:{value:["#00f0ff","#ff00ff"]},
    shape:{type:"circle"},
    opacity:{value:0.5},
    size:{value:3,random:true},
    line_linked:{enable:true,distance:150,color:"#00f0ff",opacity:0.3,width:1},
    move:{enable:true,speed:2}
  }
});

function changeQty(amount){
    let qty = document.getElementById("qty");
    let current = parseInt(qty.value);

    current += amount;

    if(current < 1){
        current = 1;
    }

    if(current > <?= $row['stock'] ?>){
        current = <?= $row['stock'] ?>;
    }

    qty.value = current;
}

function add(id){
    let qty = document.getElementById("qty").value;

    fetch("add_to_cart.php?id=" + id + "&qty=" + qty)
    .then(() => {
        alert("✅ Added to cart!");
    });
}

function buyNow(id){
    let qty = document.getElementById("qty").value;

    window.location.href =
        "checkout.php?id=" + id + "&qty=" + qty;
}
</script>

</body>
</html>