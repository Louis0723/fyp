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
SELECT r.image AS review_image,
       r.rating,
       r.review_text,
       p.image AS product_image,
       p.product_name
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
    display:inline-flex;
    align-items:center;
    gap:8px;
    margin-bottom:25px;

    background:rgba(0,240,255,0.15);
    border:1px solid #00f0ff;
    padding:10px 16px;

    color:#00f0ff;
    text-decoration:none;
    border-radius:12px;
    font-weight:600;
    transition:0.3s;
}

.back:hover{
    background:#00f0ff;
    color:black;
    transform:translateY(-2px);
}

/* title */
h1{
    font-size:28px;
    margin-bottom:25px;
    color:#00f0ff;
    text-shadow:0 0 10px #00f0ff;
}

/* review card (modern glass style) */
.card{
    display:flex;
    gap:18px;
    align-items:flex-start;

    background:rgba(255,255,255,0.06);
    border:1px solid rgba(255,255,255,0.08);

    padding:18px;
    border-radius:16px;
    margin-bottom:16px;

    backdrop-filter:blur(12px);
    transition:0.25s;
}

.card:hover{
    transform:translateY(-3px);
    box-shadow:0 10px 25px rgba(0,240,255,0.2);
}

/* review image */
.review-img{
    width:90px;
    height:90px;
    object-fit:cover;
    border-radius:12px;
    border:2px solid rgba(0,240,255,0.3);
}

/* content */
.info{
    flex:1;
}

/* product name */
.product-name{
    font-size:16px;
    font-weight:600;
    margin-bottom:6px;
}

/* rating (better style) */
.rating{
    display:flex;
    gap:3px;
    margin-bottom:8px;
    font-size:18px;
}

.rating span{
    color:#ffd700;
}

/* review text */
.review-text{
    font-size:14px;
    color:#ddd;
    line-height:1.5;
}

/* empty */
.empty{
    text-align:center;
    margin-top:40px;
    color:#00f0ff;
    font-size:18px;
}
</style>
</head>

<body>

<div class="container">

    <a class="back" href="history.php">← Back</a>

    <h1>Your Review History</h1>

    <?php if($count == 0): ?>
        <p>No reviews found.</p>
    <?php endif; ?>

    <?php while($row = mysqli_fetch_assoc($reviews)): ?>

    <div class="card">

        <?php if(!empty($row['review_image'])): ?>
            <img class="review-img"
                 src="uploads/reviews/<?= htmlspecialchars($row['review_image']) ?>">
        <?php endif; ?>

        <div class="info">

            <div class="product-name">
                <?= htmlspecialchars($row['product_name']) ?>
            </div>

            <div class="rating">
                <?php for($i=1;$i<=5;$i++): ?>
                    <span><?= $i <= $row['rating'] ? "★" : "☆" ?></span>
                <?php endfor; ?>
            </div>

            <div class="review-text">
                <?= htmlspecialchars($row['review_text']) ?>
            </div>

        </div>

    </div>

    <?php endwhile; ?>

</div>

</body>

</body>
</html>