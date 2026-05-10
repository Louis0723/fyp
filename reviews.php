<?php
session_start();
include "db.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['user_id'];

/* SUBMIT REVIEW */
if (isset($_POST['submit_review'])) {

    $review = mysqli_real_escape_string($conn, $_POST['review']);
    $rating = intval($_POST['rating']);

    $imageName = NULL;

    if (!empty($_FILES['image']['name'])) {

        $targetDir = "uploads/reviews/";

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $imageName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $imageName;

        move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);
    }

    mysqli_query($conn, "
        INSERT INTO reviews (user_id, review_text, rating, image)
        VALUES ($user_id, '$review', $rating, " . ($imageName ? "'$imageName'" : "NULL") . ")
    ");

    header("Location: reviews.php");
    exit;
}

$reviews = mysqli_query($conn, "
    SELECT * FROM reviews
    WHERE user_id = $user_id
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>My Reviews</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
body{
margin:0;
font-family:'Poppins',sans-serif;
background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);
color:white;
}

/* WRAPPER */
.wrapper{
max-width:1000px;
margin:60px auto;
padding:20px;
}

/* HEADER */
.header{
text-align:center;
margin-bottom:20px;
}

.header h1{
font-size:38px;
color:#00f0ff;
margin-bottom:5px;
}

.header p{
color:#aaa;
font-size:14px;
}

/* BACK */
.back{
display:flex;
justify-content:center;
margin-bottom:25px;
}

.back a{
text-decoration:none;
}

.back button{
padding:10px 18px;
border:none;
border-radius:12px;
background:rgba(255,255,255,0.08);
color:white;
cursor:pointer;
font-weight:500;
transition:0.3s;
}

.back button:hover{
background:rgba(0,240,255,0.25);
transform:scale(1.05);
}

/* FORM CARD (FIXED LAYOUT) */
.form-card{
background:rgba(255,255,255,0.05);
backdrop-filter:blur(12px);
padding:25px;
border-radius:18px;
margin-bottom:30px;
box-shadow:0 10px 30px rgba(0,0,0,0.3);

display:flex;
flex-direction:column;
gap:12px;
}

/* TEXTAREA FIX */
textarea{
width:97%;
min-height:120px;
padding:14px;
border-radius:12px;
border:none;
outline:none;
background:rgba(255,255,255,0.08);
color:white;
resize:none;
font-size:14px;
}

/* FILE INPUT FIX */
input[type="file"]{
width:97.8%;
padding:10px;
border-radius:10px;
background:rgba(255,255,255,0.06);
color:white;
border:1px solid rgba(255,255,255,0.1);
cursor:pointer;
}

/* SELECT */
select{
width:100%;
padding:12px;
border-radius:10px;
border:none;
outline:none;

background:rgba(255,255,255,0.08);
color:white;

cursor:pointer;
font-weight:500;

backdrop-filter: blur(12px);
-webkit-backdrop-filter: blur(12px);

border:1px solid rgba(0,240,255,0.15);
transition:0.3s;
}

select:focus{
border:1px solid rgba(0,240,255,0.5);
box-shadow:0 0 12px rgba(0,240,255,0.2);
}

select option{
background:#24243e;
color:white;
}

/* SUBMIT */
button.submit{
margin-top:5px;
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

button.submit:hover{
transform:scale(1.03);
}

/* REVIEW CARD */
.review{
background:rgba(255,255,255,0.04);
backdrop-filter:blur(10px);
padding:18px;
border-radius:16px;
margin-bottom:15px;
box-shadow:0 8px 25px rgba(0,0,0,0.25);
transition:0.3s ease;
}

.review:hover{
transform:translateY(-5px);
box-shadow:0 12px 35px rgba(0,240,255,0.15);
}

/* TOP */
.review-top{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:8px;
}

/* STARS */
.stars{
font-size:16px;
letter-spacing:2px;
background: linear-gradient(90deg,#00f0ff,#ff00ff);
-webkit-background-clip: text;
-webkit-text-fill-color: transparent;
text-shadow: 0 0 8px rgba(0,240,255,0.3);
}

.stars span{
opacity:0.2;
}

/* DATE */
.date{
font-size:12px;
color:#aaa;
}

/* TEXT */
.review-text{
font-size:14px;
line-height:1.5;
color:#eaeaea;
margin-top:8px;
}

/* IMAGE */
.review img{
width:100%;
max-height:320px;
object-fit:contain;
background:rgba(0,0,0,0.35);
border-radius:12px;
margin-top:12px;
padding:6px;
}
</style>
</head>

<body>

<div class="wrapper">

<div class="header">
    <h1>My Reviews</h1>
    <p>Share and manage your product feedback</p>
</div>

<div class="back">
    <a href="product.php">
        <button type="button">← Back to Products</button>
    </a>
</div>

<!-- FORM -->
<div class="form-card">

<form method="POST" enctype="multipart/form-data">

<textarea name="review" placeholder="Write your experience about the product..." required></textarea>

<select name="rating" required>
<option value="">Select Rating</option>
<option value="5">★★★★★ Excellent</option>
<option value="4">★★★★ Good</option>
<option value="3">★★★ Average</option>
<option value="2">★★ Poor</option>
<option value="1">★ Bad</option>
</select>

<input type="file" name="image" accept="image/*">

<button class="submit" type="submit" name="submit_review">
Submit Review
</button>

</form>

</div>

<!-- REVIEWS -->
<?php while($row = mysqli_fetch_assoc($reviews)): ?>

<div class="review">

<div class="review-top">

<div class="stars">
<?php
for ($i = 1; $i <= 5; $i++) {
    if ($i <= $row['rating']) {
        echo "★";
    } else {
        echo "<span>★</span>";
    }
}
?>
</div>

<div class="date">
<?= $row['created_at'] ?>
</div>

</div>

<div class="review-text">
<?= htmlspecialchars($row['review_text']) ?>
</div>

<?php if (!empty($row['image'])): ?>
<img src="uploads/reviews/<?= $row['image'] ?>">
<?php endif; ?>

</div>

<?php endwhile; ?>

</div>

</body>
</html>