<?php
include "../db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

/* =========================
   CHECK COLUMN EXISTS
========================= */

$checkColumn = $conn->query("
    SHOW COLUMNS FROM reviews LIKE 'admin_reply'
");

if($checkColumn->num_rows == 0){

    $conn->query("
        ALTER TABLE reviews
        ADD admin_reply TEXT NULL
    ");
}

/* =========================
   REPLY REVIEW
========================= */

if(isset($_POST['reply_review'])){

    $review_id = intval($_POST['review_id']);
    $reply     = mysqli_real_escape_string($conn,$_POST['reply']);

    $conn->query("
        UPDATE reviews
        SET admin_reply = '$reply'
        WHERE review_id = '$review_id'
    ");

    header("Location: product_review.php");
    exit();
}

/* =========================
   GET REVIEWS
========================= */

$reviews = $conn->query("
    SELECT
        r.*,

        COALESCE(u.name,'Deleted User') AS customer_name,

        COALESCE(p.product_name,'Deleted Product') AS product_name,

        p.image

    FROM reviews r

    LEFT JOIN users u
    ON r.user_id = u.user_id

    LEFT JOIN products p
    ON r.product_id = p.product_id

    ORDER BY r.review_id DESC
");
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<title>Product Reviews</title>

<link rel="stylesheet" href="style.css?v=999">

<script src="https://unpkg.com/lucide@latest"></script>

<style>

body{
    background:#eef3f9;
    font-family:'Segoe UI',sans-serif;
    overflow-x:hidden;
}

/* MAIN */

.main-content{
    margin-left:260px;
    padding:120px 28px 40px;
}

/* TITLE */

.review-title{
    font-size:58px;
    font-weight:900;
    color:#0f172a;
    line-height:1;
}

.review-sub{
    margin-top:12px;
    margin-bottom:35px;
    color:#64748b;
    font-size:18px;
}

/* TABLE */

.review-table{
    width:100%;
    background:#fff;
    border-radius:28px;
    overflow:hidden;
    box-shadow:0 10px 30px rgba(0,0,0,.05);
}

.review-table table{
    width:100%;
    border-collapse:collapse;
}

.review-table th{
    background:#f8fafc;
    padding:24px 18px;
    text-align:left;
    color:#64748b;
    font-size:15px;
    border-bottom:1px solid #e2e8f0;
}

.review-table td{
    padding:22px 18px;
    border-bottom:1px solid #edf2f7;
    vertical-align:middle;
}

.review-table tr:last-child td{
    border-bottom:none;
}

/* PRODUCT */

.product-box{
    display:flex;
    align-items:center;
    gap:15px;
}

.product-image{
    width:70px;
    height:70px;
    border-radius:18px;
    object-fit:cover;
    border:1px solid #e2e8f0;
    background:#fff;
}

.product-name{
    font-weight:700;
    color:#0f172a;
}

/* STARS */

.stars{
    display:flex;
    gap:3px;
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

/* REVIEW */

.review-text{
    max-width:300px;
    line-height:1.5;
    color:#475569;
}

/* BUTTON */

.action-btn{
    border:none;
    background:#2563eb;
    color:#fff;
    padding:12px 18px;
    border-radius:14px;
    cursor:pointer;
    font-weight:700;
    transition:.2s;
}

.action-btn:hover{
    background:#1d4ed8;
}

/* DATE */

.review-date{
    color:#94a3b8;
    font-size:14px;
}

/* MODAL */

.modal{
    position:fixed;
    inset:0;
    background:rgba(15,23,42,.65);
    display:none;
    align-items:center;
    justify-content:center;
    z-index:999999;
}

.modal.active{
    display:flex;
}

.modal-box{
    width:700px;
    max-width:95%;
    background:#fff;
    border-radius:28px;
    padding:30px;
    position:relative;
}

/* CLOSE */

.close-modal{
    position:absolute;
    top:18px;
    right:18px;
    width:42px;
    height:42px;
    border:none;
    border-radius:50%;
    background:#f1f5f9;
    cursor:pointer;
}

/* TOP */

.modal-top{
    display:flex;
    gap:20px;
    margin-bottom:25px;
}

.modal-image{
    width:120px;
    height:120px;
    border-radius:18px;
    object-fit:cover;
    border:1px solid #e2e8f0;
}

.modal-title{
    font-size:32px;
    font-weight:900;
    color:#0f172a;
}

.modal-user{
    margin-top:8px;
    color:#64748b;
}

.full-review{
    margin-top:20px;
    line-height:1.8;
    color:#334155;
}

/* REPLY */

.reply-box{
    margin-top:25px;
}

.reply-box textarea{
    width:100%;
    min-height:130px;
    border:1px solid #cbd5e1;
    border-radius:16px;
    padding:16px;
    resize:none;
    font-size:15px;
    outline:none;
}

.reply-submit{
    margin-top:16px;
    border:none;
    background:#2563eb;
    color:#fff;
    padding:14px 24px;
    border-radius:14px;
    cursor:pointer;
    font-weight:700;
}

.reply-submit:hover{
    background:#1d4ed8;
}

/* ADMIN REPLY */

.admin-reply{
    margin-top:20px;
    background:#eff6ff;
    border:1px solid #bfdbfe;
    padding:18px;
    border-radius:16px;
}

.admin-reply h4{
    color:#2563eb;
    margin-bottom:10px;
}

@media(max-width:900px){

    .main-content{
        margin-left:0;
        padding:110px 15px 30px;
    }

    .review-title{
        font-size:42px;
    }

    .review-table{
        overflow-x:auto;
    }
}


/* FIX HEADER AVATAR */

.admin-header .avatar-btn{
    width:42px !important;
    height:42px !important;
    min-width:42px !important;
    min-height:42px !important;
    border-radius:50% !important;
    overflow:hidden !important;
    padding:0 !important;
}

.admin-header .avatar-btn img{
    width:100% !important;
    height:100% !important;
    object-fit:cover !important;
    object-position:center !important;
    border-radius:50% !important;
    display:block !important;
}

/* DROPDOWN PROFILE AVATAR */

.admin-header .profile-avatar{
    width:52px !important;
    height:52px !important;
    border-radius:50% !important;
    overflow:hidden !important;
}

.admin-header .profile-avatar img{
    width:100% !important;
    height:100% !important;
    object-fit:cover !important;
    object-position:center !important;
    border-radius:50% !important;
    display:block !important;
}


</style>

</head>

<body>

<?php include "admin_sidebar.php"; ?>
<?php include "admin_header.php"; ?>

<div class="main-content">

    <div class="review-title">
        Product Reviews
    </div>

    <div class="review-sub">
        Manage customer feedback and reply reviews.
    </div>

    <div class="review-table">

        <table>

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody>

<?php if($reviews && $reviews->num_rows > 0): ?>

<?php while($r = $reviews->fetch_assoc()): ?>

<tr>

<td>#<?= $r['review_id'] ?></td>

<td>

<div class="product-box">

<?php

$image = trim($r['image'] ?? '');

if(
    strpos($image,'http://') === 0 ||
    strpos($image,'https://') === 0
){
    $image = $image;
}
elseif(!empty($image)){
    $image = "../uploads/" . $image;
}
else{
    $image = "https://via.placeholder.com/70";
}

?>

<img
    src="<?= htmlspecialchars($image) ?>"
    class="product-image"
    onerror="this.src='https://via.placeholder.com/70';"
>

<div>
    <div class="product-name">
        <?= htmlspecialchars($r['product_name']) ?>
    </div>
</div>

</div>

</td>

<td>
    <?= htmlspecialchars($r['customer_name']) ?>
</td>

<td>

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

</td>

<td>

<div class="review-text">
<?= htmlspecialchars(substr($r['review_text'],0,70)) ?>
</div>

</td>

<td>

<div class="review-date">
<?= date('M d, Y', strtotime($r['created_at'])) ?>
</div>

</td>

<td>

<button
class="action-btn openModalBtn"

data-id="<?= $r['review_id'] ?>"
data-name="<?= htmlspecialchars($r['customer_name']) ?>"
data-product="<?= htmlspecialchars($r['product_name']) ?>"
data-review="<?= htmlspecialchars($r['review_text']) ?>"
data-rating="<?= $r['rating'] ?>"
data-image="<?= $image ?>"
data-reply="<?= htmlspecialchars($r['admin_reply'] ?? '') ?>"
>

View Detail

</button>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="7" style="text-align:center;padding:40px;">
No reviews found
</td>

</tr>

<?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<!-- MODAL -->

<div class="modal" id="reviewModal">

<div class="modal-box">

<button class="close-modal" id="closeModal">
<i data-lucide="x"></i>
</button>

<div class="modal-top">

<img src="" id="modalImage" class="modal-image">

<div>

<div class="modal-title" id="modalProduct"></div>

<div class="modal-user" id="modalUser"></div>

<div class="stars" id="modalStars"></div>

</div>

</div>

<div class="full-review" id="modalReview"></div>

<div class="admin-reply" id="adminReplyBox" style="display:none;">

<h4>Admin Reply</h4>

<div id="adminReplyText"></div>

</div>

<form method="POST" class="reply-box">

<input type="hidden" name="review_id" id="replyReviewId">

<textarea
name="reply"
placeholder="Write reply to customer..."
required
></textarea>

<button
type="submit"
name="reply_review"
class="reply-submit"
>
Reply Review
</button>

</form>

</div>

</div>

<script>

lucide.createIcons();

const modal = document.getElementById("reviewModal");

document.querySelectorAll(".openModalBtn").forEach(btn=>{

btn.addEventListener("click",()=>{

modal.classList.add("active");

document.getElementById("modalImage").src =
btn.dataset.image;

document.getElementById("modalProduct").innerHTML =
btn.dataset.product;

document.getElementById("modalUser").innerHTML =
"Review by " + btn.dataset.name;

document.getElementById("modalReview").innerHTML =
btn.dataset.review;

document.getElementById("replyReviewId").value =
btn.dataset.id;

let rating = parseInt(btn.dataset.rating);

let starsHTML = '';

for(let i=1;i<=5;i++){

if(i <= rating){

starsHTML +=
'<i data-lucide="star" class="star-fill"></i>';

}else{

starsHTML +=
'<i data-lucide="star" class="star-empty"></i>';

}
}

document.getElementById("modalStars").innerHTML =
starsHTML;

let reply = btn.dataset.reply;

if(reply !== ''){

document.getElementById("adminReplyBox").style.display =
"block";

document.getElementById("adminReplyText").innerHTML =
reply;

}else{

document.getElementById("adminReplyBox").style.display =
"none";

}

lucide.createIcons();

});

});

document.getElementById("closeModal").addEventListener("click",()=>{

modal.classList.remove("active");

});

window.addEventListener("click",(e)=>{

if(e.target == modal){

modal.classList.remove("active");

}

});

</script>

</body>
</html>