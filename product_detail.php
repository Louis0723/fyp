<?php
session_start();
include "db.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$res = mysqli_query($conn, "SELECT * FROM products WHERE product_id=$id");

if (!$res || mysqli_num_rows($res) == 0) {
    die("❌ Product not found.");
}

$row = mysqli_fetch_assoc($res);
$reviews = mysqli_query($conn, "
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
<title><?= htmlspecialchars($row['product_name']) ?></title>

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

.admin-reply{
    margin-top:18px;
    padding:18px 20px;
    border-radius:16px;
    background:rgba(0,255,255,0.08);
    border:1px solid rgba(0,255,255,0.22);
}

.admin-reply-header{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:10px;
}

.admin-avatar{
    width:42px;
    height:42px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(135deg,#00f0ff,#00bfff);
    color:#000;
    font-weight:700;
    flex-shrink:0;
}

.admin-name{
    font-weight:700;
    color:#00f0ff;
    line-height:1.2;
}

.admin-date{
    font-size:12px;
    color:#aaa;
    margin-top:2px;
}

.admin-reply-text{
    color:#ddd;
    line-height:1.7;
    font-size:14px;
    margin-left:54px;
}
</style>
</head>

<body>

<div id="particles-js"></div>

<div class="container">

    <a href="product.php" class="back">⬅ Back to Products</a>

    <div class="card">

       <div class="image">

<?php
$image = '';

if (!empty($row['image'])) {

    if (filter_var($row['image'], FILTER_VALIDATE_URL)) {

        $image = $row['image'];

    } else {

        $image = "uploads/products/" . $row['image'];

    }

} else {

    $image = "https://via.placeholder.com/400";
}
?>

<img
    src="<?= htmlspecialchars($image) ?>"
    alt="Product Image"
    onerror="this.src='https://via.placeholder.com/400';"
>

</div>

        <div class="details">
            <h2><?= htmlspecialchars($row['product_name']) ?></h2>

            <?php
            $category = strtolower(trim($row['category']));
            ?>

            <?php if (
                $category == 'gaming pc' ||
                $category == 'pc' ||
                $category == 'laptop'
            ): ?>

                <?php if (!empty($row['cpu'])): ?>
                    <div class="spec"><strong>CPU:</strong> <?= htmlspecialchars($row['cpu']) ?></div>
                <?php endif; ?>

                <?php if (!empty($row['gpu'])): ?>
                    <div class="spec"><strong>GPU:</strong> <?= htmlspecialchars($row['gpu']) ?></div>
                <?php endif; ?>

                <?php if (!empty($row['ram'])): ?>
                    <div class="spec"><strong>RAM:</strong> <?= htmlspecialchars($row['ram']) ?></div>
                <?php endif; ?>

                <?php if (!empty($row['storage'])): ?>
                    <div class="spec"><strong>Storage:</strong> <?= htmlspecialchars($row['storage']) ?></div>
                <?php endif; ?>

                <?php if (!empty($row['motherboard'])): ?>
                    <div class="spec"><strong>Motherboard:</strong> <?= htmlspecialchars($row['motherboard']) ?></div>
                <?php endif; ?>

            <?php elseif (strpos($category, 'monitor') !== false): ?>

                <?php if (!empty($row['screen_size'])): ?>
                    <div class="spec"><strong>Screen Size:</strong> <?= htmlspecialchars($row['screen_size']) ?></div>
                <?php endif; ?>

            <?php elseif (strpos($category, 'keyboard') !== false): ?>

                <?php if (!empty($row['switch_type'])): ?>
                    <div class="spec"><strong>Switch Type:</strong> <?= htmlspecialchars($row['switch_type']) ?></div>
                <?php endif; ?>

                <?php if (!empty($row['keyboard_size'])): ?>
                    <div class="spec"><strong>Keyboard Size:</strong> <?= htmlspecialchars($row['keyboard_size']) ?></div>
                <?php endif; ?>

                <?php if (!empty($row['battery'])): ?>
                    <div class="spec"><strong>Battery:</strong> <?= htmlspecialchars($row['battery']) ?></div>
                <?php endif; ?>

            <?php elseif (strpos($category, 'mouse') !== false): ?>

                <?php if (!empty($row['dpi'])): ?>
                    <div class="spec"><strong>DPI:</strong> <?= htmlspecialchars($row['dpi']) ?></div>
                <?php endif; ?>

                <?php if (!empty($row['mouse_type'])): ?>
                    <div class="spec"><strong>Mouse Type:</strong> <?= htmlspecialchars($row['mouse_type']) ?></div>
                <?php endif; ?>

                <?php if (!empty($row['battery'])): ?>
                    <div class="spec"><strong>Battery:</strong> <?= htmlspecialchars($row['battery']) ?></div>
                <?php endif; ?>

            <?php endif; ?>

            <div class="desc">
                <?= !empty($row['description']) ? nl2br(htmlspecialchars($row['description'])) : 'No description available.' ?>
            </div>

            <?php if (!empty($row['specs'])): ?>
                <div class="desc" style="margin-top:20px;">
                    <strong>Specifications:</strong><br><br>
                    <?= nl2br(htmlspecialchars($row['specs'])) ?>
                </div>
            <?php endif; ?>

            <div class="qty-box">
                <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>

                <input
                    type="number"
                    id="qty"
                    value="1"
                    min="1"
                    max="<?= (int)$row['stock'] ?>"
                >

                <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
            </div>

            <div class="btn-group">

                <button
                    onclick="add(<?= (int)$row['product_id'] ?>)"
                    <?= ((int)$row['stock'] <= 0) ? 'disabled' : '' ?>
                >
                    <?= ((int)$row['stock'] <= 0) ? 'Out of Stock' : 'Add to Cart' ?>
                </button>

                <button
                    class="buy-btn"
                    onclick="buyNow(<?= (int)$row['product_id'] ?>)"
                    <?= ((int)$row['stock'] <= 0) ? 'disabled' : '' ?>
                >
                    Buy Now
                </button>

            </div>
        </div>

    </div>

    <div class="reviews-box">
        <h2 style="margin-bottom:25px;">⭐ Customer Reviews</h2>

        <?php if (mysqli_num_rows($reviews) == 0): ?>
            <p>No reviews yet.</p>
        <?php endif; ?>

        <?php while ($r = mysqli_fetch_assoc($reviews)): ?>
            <div class="review-item">

                <h3><?= htmlspecialchars($r['name']) ?></h3>

                <div style="color:gold;font-size:20px;margin:8px 0;">
                    <?php
                    for ($i = 1; $i <= 5; $i++) {
                        echo ($i <= (int)$r['rating']) ? "★" : "☆";
                    }
                    ?>
                </div>

                <p><?= nl2br(htmlspecialchars($r['review_text'])) ?></p>

                <?php if (!empty($r['image'])): ?>
                    <img
                        src="uploads/reviews/<?= htmlspecialchars($r['image']) ?>"
                        style="width:150px;margin-top:15px;border-radius:12px;object-fit:cover;"
                        alt="Review Image"
                    >
                <?php endif; ?>

                <div style="margin-top:10px;font-size:13px;color:#aaa;">
                    <?= date('Y-m-d H:i', strtotime($r['created_at'])) ?>
                </div>

                <?php


$replyResult = mysqli_query($conn, "
    SELECT *
    FROM review_replies
    WHERE review_id = ".$r['review_id']."
    ORDER BY created_at ASC
");

while($reply = mysqli_fetch_assoc($replyResult)):

?>

<div class="admin-reply">

    <div class="admin-reply-header">

        <div class="admin-avatar">
            A
        </div>

        <div>

            <div class="admin-name">
                Admin Reply
            </div>

            <div class="admin-date">
                <?= date(
                    'Y-m-d H:i',
                    strtotime($reply['created_at'])
                ) ?>
            </div>

        </div>

    </div>

    <div class="admin-reply-text">
        <?= nl2br(htmlspecialchars($reply['reply_text'])) ?>
    </div>

</div>

<?php endwhile; ?>

</div>               <!-- END review-item -->

<?php endwhile; ?>   <!-- END reviews loop -->

</div>               <!-- END reviews-box -->

</div>               <!-- END container -->   

<script>
particlesJS("particles-js", {
  particles: {
    number: { value: 70 },
    color: { value: ["#00f0ff", "#ff00ff"] },
    shape: { type: "circle" },
    opacity: { value: 0.5 },
    size: { value: 3, random: true },
    line_linked: { enable: true, distance: 150, color: "#00f0ff", opacity: 0.3, width: 1 },
    move: { enable: true, speed: 2 }
  }
});

function changeQty(amount){
    let qty = document.getElementById("qty");
    let current = parseInt(qty.value) || 1;

    current += amount;

    if(current < 1){
        current = 1;
    }

    if(current > <?= (int)$row['stock'] ?>){
        current = <?= (int)$row['stock'] ?>;
    }

    qty.value = current;
}

// ADD THIS PART HERE
document.getElementById("qty").addEventListener("input", function () {

    let stock = <?= (int)$row['stock'] ?>;
    let qty = parseInt(this.value);

    if (qty < 1) {
        alert("Minimum quantity is 1.");
        this.value = 1;
    }

    if (qty > stock) {
        alert("Only " + stock + " item(s) available in stock.");
        this.value = stock;
    }

});

function add(id){

    let qty = parseInt(document.getElementById("qty").value);

    fetch("add_to_cart.php?id=" + id + "&qty=" + qty)
    .then(res => res.text())
    .then(msg => {
        alert(msg);
    });

}

function buyNow(id){
    let qty = document.getElementById("qty").value;
    window.location.href = "checkout.php?id=" + id + "&qty=" + qty;
}
</script>

</body>
</html> 