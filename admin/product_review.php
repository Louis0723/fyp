<?php
include "../db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
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

<link rel="stylesheet" href="style.css?v=5">

<script src="https://unpkg.com/lucide@latest"></script>

<style>

body{
    background:#f1f5f9;
}

/* MAIN */

.main-content{
    margin-left:95px;
    margin-top:100px;
    padding:30px;
}

/* TITLE */

.review-title{
    font-size:42px;
    font-weight:900;
    color:#0f172a;
}

.review-sub{
    margin-top:6px;
    margin-bottom:30px;
    color:#64748b;
    font-size:15px;
}

/* TABLE */

.review-table{
    width:100%;
    background:#fff;
    border-radius:22px;
    overflow:hidden;
    border:1px solid #e5e7eb;
    box-shadow:0 5px 20px rgba(0,0,0,.05);
}

.review-table table{
    width:100%;
    border-collapse:collapse;
}

.review-table th{
    background:#f8fafc;
    padding:18px;
    text-align:left;
    font-size:14px;
    color:#64748b;
    border-bottom:1px solid #e5e7eb;
}

.review-table td{
    padding:18px;
    border-bottom:1px solid #f1f5f9;
    vertical-align:middle;
}

/* PRODUCT */

.product-box{
    display:flex;
    align-items:center;
    gap:14px;
}

.product-image{
    width:65px;
    height:65px;
    border-radius:14px;
    object-fit:cover;
    border:1px solid #e5e7eb;
    background:#fff;
}

.product-name{
    font-weight:700;
    color:#0f172a;
}

.customer-name{
    font-size:13px;
    color:#64748b;
    margin-top:4px;
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

/* REVIEW */

.review-text{
    max-width:350px;
    color:#334155;
    line-height:1.5;
}

/* BUTTONS */

.action-btn{
    border:none;
    background:#2563eb;
    color:#fff;
    padding:10px 18px;
    border-radius:12px;
    cursor:pointer;
    font-weight:700;
    transition:.2s;
}

.action-btn:hover{
    background:#1d4ed8;
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
    max-height:90vh;
    overflow-y:auto;
    background:#fff;
    border-radius:24px;
    padding:30px;
    position:relative;
}

/* CLOSE */

.close-modal{
    position:absolute;
    top:20px;
    right:20px;
    border:none;
    background:#f1f5f9;
    width:40px;
    height:40px;
    border-radius:50%;
    cursor:pointer;
}

/* MODAL TOP */

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
    border:1px solid #e5e7eb;
}

/* MODAL TITLE */

.modal-title{
    font-size:28px;
    font-weight:800;
    color:#0f172a;
}

.modal-user{
    margin-top:8px;
    color:#64748b;
}

/* FULL REVIEW */

.full-review{
    margin-top:20px;
    line-height:1.8;
    color:#334155;
}

/* REPLY BOX */

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

/* SAVE */

.reply-submit{
    margin-top:16px;
    background:#2563eb;
    color:#fff;
    border:none;
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

/* DATE */

.review-date{
    color:#94a3b8;
    font-size:13px;
}

/* RESPONSIVE */

@media(max-width:900px){

    .main-content{
        margin-left:85px;
        padding:18px;
    }

    .review-table{
        overflow-x:auto;
    }

    .modal-box{
        width:95%;
    }

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
        Manage customer feedback and reply reviews.
    </div>

    <!-- TABLE -->
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

<?php while($r = $reviews->fetch_assoc()): ?>

<tr>

    <!-- ID -->
    <td>

        #<?= $r['review_id'] ?>

    </td>

    <!-- PRODUCT -->
    <td>

        <div class="product-box">

<?php

$imagePath = "../uploads/reviews/" . basename($r['image']);

if(!empty($r['image']) && file_exists($imagePath)){

?>

<img 
src="../uploads/reviews/<?= htmlspecialchars(basename($r['image'])) ?>"
class="product-image"
>

<?php } else { ?>

<img 
src="../no-image.png"
class="product-image"
>

<?php } ?>

            <div>

                <div class="product-name">
                    <?= htmlspecialchars($r['product_name']) ?>
                </div>

            </div>

        </div>

    </td>

    <!-- CUSTOMER -->
    <td>

        <strong>
            <?= htmlspecialchars($r['name']) ?>
        </strong>

    </td>

    <!-- STARS -->
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

    <!-- REVIEW -->
    <td>

        <div class="review-text">

            <?= htmlspecialchars(substr($r['review_text'],0,70)) ?>...

        </div>

    </td>

    <!-- DATE -->
    <td>

        <div class="review-date">

            <?= date('M d, Y', strtotime($r['created_at'])) ?>

        </div>

    </td>

    <!-- ACTION -->
    <td>

        <button 
            class="action-btn openModalBtn"

            data-id="<?= $r['review_id'] ?>"
            data-name="<?= htmlspecialchars($r['name']) ?>"
            data-product="<?= htmlspecialchars($r['product_name']) ?>"
            data-review="<?= htmlspecialchars($r['review_text']) ?>"
            data-rating="<?= $r['rating'] ?>"
            data-image="../uploads/reviews/<?= htmlspecialchars(basename($r['image'])) ?>"
            data-reply="<?= htmlspecialchars($r['admin_reply'] ?? '') ?>"

        >
            View Detail
        </button>

    </td>

</tr>

<?php endwhile; ?>

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

        <!-- ADMIN REPLY -->
        <div class="admin-reply" id="adminReplyBox" style="display:none;">

            <h4>Admin Reply</h4>

            <div id="adminReplyText"></div>

        </div>

        <!-- REPLY FORM -->
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

/* MODAL */

const modal = document.getElementById("reviewModal");
const closeModal = document.getElementById("closeModal");

document.querySelectorAll(".openModalBtn").forEach(btn => {

    btn.addEventListener("click", () => {

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

        /* STARS */

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

        /* REPLY */

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

/* CLOSE */

closeModal.addEventListener("click", () => {

    modal.classList.remove("active");

});

window.addEventListener("click", (e) => {

    if(e.target == modal){

        modal.classList.remove("active");

    }

});

</script>

</body>
</html>