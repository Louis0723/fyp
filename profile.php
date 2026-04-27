<?php
session_start();
include "db.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['user_id'];

$res = mysqli_query($conn,"SELECT * FROM users WHERE user_id=$user_id");
$user = mysqli_fetch_assoc($res);

if(isset($_POST['update'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    mysqli_query($conn,"
    UPDATE users 
    SET name='$name', email='$email', address='$address', phone='$phone'
    WHERE user_id=$user_id
    ");

    header("Location: profile.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Profile</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
body{
    background: linear-gradient(135deg,#0f0c29,#302b63,#24243e);
    color:white;
    font-family:'Poppins',sans-serif;
}

.container{
    max-width:500px;
    margin:100px auto;
    padding:30px;
    background: rgba(255,255,255,0.05);
    border-radius:20px;
    backdrop-filter: blur(15px);
    box-shadow:0 10px 25px rgba(0,255,255,0.3);
}

h2{
    text-align:center;
    margin-bottom:25px;
    color:#00f0ff;
}

input, textarea{
    width:100%;
    padding:10px;
    margin-bottom:15px;
    border-radius:10px;
    border:none;
    outline:none;
}

button{
    width:100%;
    padding:12px;
    border:none;
    border-radius:10px;
    background: linear-gradient(90deg,#00f0ff,#ff00ff);
    color:white;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    transform:scale(1.05);
}

.back{
    display:block;
    text-align:center;
    margin-top:20px;
    color:#00f0ff;
    text-decoration:none;
}
</style>
</head>

<body>

<div class="container">

<h2>👤 My Profile</h2>

<form method="post">

<input name="name" placeholder="Name" value="<?= $user['name'] ?>" required>

<input name="email" placeholder="Email" value="<?= $user['email'] ?>" required>

<textarea name="address" placeholder="Address" required><?= $user['address'] ?></textarea>

<input name="phone" placeholder="Phone Number" value="<?= $user['phone'] ?>" required>

<button name="update">Update Profile</button>

</form>

<a href="product.php" class="back">⬅ Back to Products</a>

</div>

</body>
</html>