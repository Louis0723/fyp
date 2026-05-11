<?php
session_start();
include "db.php";

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['user_id'];
$order_id = $_GET['order_id'];

$reviews = mysqli_query($conn,"
SELECT r.*, p.product_name, p.image
FROM reviews r
LEFT JOIN products p ON r.product_id = p.product_id
WHERE r.order_id='$order_id' AND r.user_id='$user_id'
");

$count = mysqli_num_rows($reviews);
?>
<!DOCTYPE html>
<html>
<head>
<title>Your Reviews</title>

<style>
body{
    margin:0;
    font-family:'Poppins',sans-serif;
    background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);
    color:white;
}

/* container */
.container{
    max-width:900px;
    margin:60px auto;
    padding:20px;
}

/* back button */
.back{
    display:inline-block;
    margin-bottom:20px;
    background:linear-gradient(90deg,#ff00ff,#00f0ff);
    padding:10px 16px;
    color:white;
    text-decoration:none;
    border-radius:10px;
    font-weight:600;
}

/* title */
h1{
    font-size:28px;
    margin-bottom:25px;
    color:#00f0ff;
    text-shadow:0 0 10px #00f0ff;
}

/* review card */
.card{
    display:flex;
    gap:20px;
    align-items:flex-start;

    background:rgba(255,255,255,0.06);
    padding:20px;
    border-radius:16px;
    margin-bottom:18px;

    backdrop-filter:blur(10px);
    box-shadow:0 10px 25px rgba(0,0,0,0.3);
    transition:0.3s;
}

.card:hover{
    transform:translateY(-3px);
    box-shadow:0 15px 30px rgba(0,240,255,0.15);
}

/* image */
.card img{
    width:100px;
    height:100px;
    object-fit:cover;
    border-radius:12px;
    border:2px solid rgba(0,240,255,0.3);
}

/* content */
.info{
    flex:1;
}

.product-name{
    font-size:18px;
    font-weight:600;
    color:#fff;
    margin-bottom:6px;
}

.rating{
    color:#ffd700;
    font-size:16px;
    margin-bottom:8px;
}

.review-text{
    color:#ddd;
    font-size:14px;
    line-height:1.5;
}

/* empty state */
.empty{
    text-align:center;
    color:#00f0ff;
    margin-top:40px;
    font-size:18px;
}
</style>
</head>

<body>

<a class="back" href="history.php">← Back</a>

<h1>Your Review History (Order #<?= $order_id ?>)</h1>

<?php if($count == 0): ?>
    <p style="color:#00f0ff;">No reviews found for this order.</p>
<?php endif; ?>

<?php while($row = mysqli_fetch_assoc($reviews)): ?>

<div class="card">

    <img src="<?= !empty($row['image']) ? './uploads/reviews/' . htmlspecialchars($row['image']) : 'https://via.placeholder.com/100' ?>">

    <div class="info">

        <div class="product-name">
            <?= htmlspecialchars($row['product_name']) ?>
        </div>

        <div class="rating">
            <?= str_repeat("⭐", (int)$row['rating']) ?>
        </div>

        <div class="review-text">
            <?= htmlspecialchars($row['review_text']) ?>
        </div>

    </div>

</div>

<?php endwhile; ?>

</body>
</html>