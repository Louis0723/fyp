<?php
session_start();
include "db.php";

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['user_id'];
$order_id = $_GET['order_id'];

$checkOrder = mysqli_query($conn,"
SELECT * FROM orders 
WHERE order_id='$order_id' AND user_id=$user_id
");

if(mysqli_num_rows($checkOrder) == 0){
    die("Invalid order.");
}

$productQuery = mysqli_query($conn,"
SELECT 
    oi.product_id,
    oi.review_status,
    p.product_name,
    p.image
FROM order_items oi
JOIN products p 
ON oi.product_id = p.product_id
WHERE oi.order_id = '$order_id'
");

/* SUBMIT REVIEW */
if(isset($_POST['submit'])){

    $rating = intval($_POST['rating']);
    $review = mysqli_real_escape_string($conn,$_POST['review']);
    $product_id = intval($_POST['product_id']);

    $imageName = "";

    if(!empty($_FILES['image']['name'])){

        $folder = "uploads/reviews/";

        if(!is_dir($folder)){
            mkdir($folder,0777,true);
        }

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

$original = pathinfo($_FILES['image']['name'], PATHINFO_FILENAME);
/*Clean the file name*/
$original = preg_replace("/[^a-zA-Z0-9]/", "_", $original);

$imageName = time() . "_" . $original . "." . $ext;

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $folder . $imageName
        );
    }
/*save the review*/
   mysqli_query($conn,"
INSERT INTO reviews
(order_id, product_id, user_id, rating, review_text, image)
VALUES
('$order_id', {$product_id}, $user_id, $rating, '$review', '$imageName')
");
 
mysqli_query($conn,"
UPDATE order_items
SET review_status='reviewed'
WHERE order_id='$order_id'
AND product_id=$product_id
");

    header("Location: history.php");
    exit;

    var_dump($order_id);

$res = mysqli_query($conn,"SELECT * FROM order_items WHERE order_id='$order_id'");
echo mysqli_num_rows($res);
exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Review Order</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
body{
background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);
font-family:'Poppins',sans-serif;
color:white;
padding:40px;
}

h1{
text-align:center;
margin-bottom:25px;
color:#00f0ff;
text-shadow:0 0 10px #00f0ff;
}

.card{
max-width:700px;
margin:auto;
background:rgba(255,255,255,0.08);
padding:30px;
border-radius:20px;
backdrop-filter:blur(20px);
text-align:center;
box-shadow:0 10px 30px rgba(0,255,255,0.2);
}

img{
width:140px;
height:140px;
object-fit:cover;
border-radius:15px;
margin-bottom:15px;
border:2px solid #00f0ff;
}

h2{
margin-bottom:15px;
color:#fff;
}

/* INPUTS */
select, textarea, input[type=file]{
width:100%;
padding:14px;
margin-top:12px;
border:none;
border-radius:12px;
background:#ffffff;
color:#000;
font-size:15px;
outline:none;
}

textarea{
height:120px;
resize:none;
}

select{
    background:white;
    color:black;
    font-weight:600;
    font-size:16px;
}

/* BUTTON */
button{
width:100%;
padding:14px;
margin-top:18px;
border:none;
border-radius:12px;
background:linear-gradient(90deg,#00f0ff,#ff00ff);
color:white;
font-weight:700;
cursor:pointer;
transition:0.3s;
}

button:hover{
transform:scale(1.03);
box-shadow:0 10px 20px rgba(255,0,255,0.4);
}

/* SMALL LABEL STYLE (optional improvement) */
label{
display:block;
text-align:left;
margin-top:15px;
margin-bottom:5px;
font-size:14px;
color:#00f0ff;
font-weight:600;
}

.review-form{
    display:flex;
    flex-direction:column;
    gap:15px;
}

/* make everything same width + alignment */
.review-form select,
.review-form textarea,
.review-form button,
.file-box{
    width:100%;
    box-sizing:border-box;
}

/* file input fix (important part) */
.file-box{
    background:white;
    border-radius:12px;
    padding:12px;
    display:flex;
    align-items:center;
}

.file-box input[type="file"]{
    width:100%;
    color:black;
}

.file-box:hover{
    border:1px solid #00f0ff;
}

/* consistent textarea */
textarea{
    height:120px;
    resize:none;
}

/* button consistency */
button{
    padding:14px;
    border:none;
    border-radius:12px;
    background:linear-gradient(90deg,#00f0ff,#ff00ff);
    color:white;
    font-weight:700;
    cursor:pointer;
}

.back-btn{
    display:inline-block;
    margin-bottom:20px;
    padding:10px 18px;
    background:#ff00ff;
    color:white;
    text-decoration:none;
    border-radius:10px;
    font-weight:600;
    transition:0.3s;
}

.back-btn:hover{
    transform:scale(1.05);
    box-shadow:0 5px 15px rgba(255,0,255,0.4);
}
</style>
</head>

<body>

<h1 style="text-align:center;">⭐ Review Order #<?= $order_id ?></h1>

<a href="history.php" class="back-btn">← Back</a>

<div class="card">

<?php while($row = mysqli_fetch_assoc($productQuery)): ?>

<?php if($row['review_status'] == 'reviewed') continue; ?>

<div style="margin-bottom:40px;padding-bottom:30px;border-bottom:1px solid rgba(255,255,255,0.2);">

<img src="<?= $row['image'] ?>">
<h2><?= $row['product_name'] ?></h2>

<form method="POST" enctype="multipart/form-data" class="review-form">

<input type="hidden" 
name="product_id" 
value="<?= $row['product_id'] ?>">

<select name="rating" required>
    <option value="">⭐ Select Rating</option>
    <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
    <option value="4">⭐⭐⭐⭐ Good</option>
    <option value="3">⭐⭐⭐ Average</option>
    <option value="2">⭐⭐ Poor</option>
    <option value="1">⭐ Bad</option>
</select>

<textarea 
name="review" 
placeholder="Write your review..." 
required></textarea>

<!--choose file-->
<div class="file-box">
<input type="file" 
name="image" 
accept="image/*">
</div>

<button name="submit">
Submit Review
</button>

</form>

</div>

<?php endwhile; ?>

<script>
document.getElementById("imageInput").addEventListener("change", function(event){
    const file = event.target.files[0];
    const preview = document.getElementById("preview");

    if(file){
        const reader = new FileReader();

        reader.onload = function(e){
            preview.src = e.target.result;
            preview.style.display = "block";
        }

        reader.readAsDataURL(file);
    }
});
</script>

</body>
</html>

