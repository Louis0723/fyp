<?php
include "../db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

/* =========================
   REVIEWS
========================= */

$reviews = $conn->query("
    SELECT 
        r.*,
        u.name,
        p.product_name

    FROM reviews r

    JOIN users u
    ON r.user_id = u.user_id

    JOIN products p
    ON r.product_id = p.product_id

    ORDER BY r.review_id DESC
");
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<title>Product Reviews</title>

<link rel="stylesheet" href="style.css?v=2">

<script src="https://unpkg.com/lucide@latest"></script>

<style>

.main-content{
    margin-left:275px;
    margin-top:100px;
    padding:28px;
}

/* TITLE */

.review-title{
    font-size:32px;
    font-weight:800;
    color:#0f172a;
}

.review-sub{
    margin-top:5px;
    margin-bottom:25px;
    color:#64748b;
    font-size:14px;
}

/* GRID */

.review-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(420px,1fr));
    gap:20px;
}

/* CARD */

.review-card{
    background:#fff;
    border-radius:20px;
    padding:20px;
    border:1px solid #e5e7eb;
    box-shadow:0 5px 20px rgba(0,0,0,.05);
}

/* TOP */

.review-top{
    display:flex;
    justify-content:space-between;
}

.review-user{
    display:flex;
    gap:12px;
}

/* AVATAR */

.avatar{
    width:50px;
    height:50px;
    border-radius:50%;
    background:#2563eb;
    color:#fff;

    display:flex;
    align-items:center;
    justify-content:center;

    font-weight:700;
}

/* INFO */

.review-info h4{
    font-size:17px;
    font-weight:700;
    color:#0f172a;
}

.review-info small{
    color:#64748b;
    font-size:13px;
}

/* STARS */

.stars{
    display:flex;
    gap:2px;
}

.star-fill{
    color:#2563eb;
    fill:#2563eb;
    width:18px;
    height:18px;
}

.star-empty{
    color:#cbd5e1;
    width:18px;
    height:18px;
}

/* IMAGE */

.review-image-box{
    margin-top:15px;
    border-radius:14px;
    overflow:hidden;
}

.review-image{
    width:100%;
    height:220px;
    object-fit:cover;
    border-radius:14px;
    display:block;
}

/* TEXT */

.review-heading{
    margin-top:15px;
    font-size:16px;
    font-weight:700;
}

.review-text{
    margin-top:8px;
    color:#475569;
    line-height:1.6;
    font-size:14px;
}

/* FOOTER */

.review-footer{
    margin-top:16px;

    display:flex;
    justify-content:space-between;
    align-items:center;

    font-size:12px;
    color:#64748b;
}

.verified-badge{
    background:#dcfce7;
    color:#166534;

    padding:6px 12px;

    border-radius:999px;

    font-size:11px;
    font-weight:700;
}

</style>

</head>

<body>

<?php
if(isset($_SESSION['role']) && $_SESSION['role']=="super_admin"){
    include "sadmin_sidebar.php";
}else{
    include "admin_sidebar.php";
}
?>

<?php include "admin_header.php"; ?>

<div class="main-content">

    <div class="review-title">
        Product Reviews
    </div>

    <div class="review-sub">
        Gaming product feedback and customer experience.
    </div>

    <div class="review-grid">

<?php while($r = $reviews->fetch_assoc()): ?>

<div class="review-card">

    <div class="review-top">

        <div class="review-user">

            <div class="avatar">
                <?= strtoupper(substr($r['name'],0,2)) ?>
            </div>

            <div class="review-info">

                <h4>
                    <?= htmlspecialchars($r['name']) ?>
                </h4>

                <small>
                    <?= htmlspecialchars($r['product_name']) ?>
                </small>

            </div>

        </div>

        <div class="stars">

<?php
for($i=1;$i<=5;$i++){

    if($i <= $r['rating']){
        echo '<i data-lucide="star" class="star-fill"></i>';
    }else{
        echo '<i data-lucide="star" class="star-empty"></i>';
    }

}
?>

        </div>

    </div>

<!-- REVIEW IMAGE -->

<?php if(!empty($r['image'])): ?>

<div class="review-image-box">

<?php

$imagePath = "../uploads/reviews/" . basename($r['image']);

if(file_exists($imagePath)){

?>

<img 
    src="../uploads/reviews/<?= htmlspecialchars(basename($r['image'])) ?>"
    class="review-image"
>

<?php } ?>

</div>

<?php endif; ?>

<!-- TITLE -->

<div class="review-heading">
    <?= htmlspecialchars(substr($r['review_text'],0,40)) ?>...
</div>

<!-- TEXT -->

<div class="review-text">
    <?= htmlspecialchars($r['review_text']) ?>
</div>

<!-- FOOTER -->

<div class="review-footer">

    <span>
        <?= date('M d, Y h:i A', strtotime($r['created_at'])) ?>
    </span>

    <div class="verified-badge">
        Verified Purchase
    </div>

</div>

</div>

<?php endwhile; ?>

    </div>

</div>

<script>

lucide.createIcons();

</script>

</body>
</html>