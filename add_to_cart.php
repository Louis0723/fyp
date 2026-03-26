<?php
session_start();
include "db.php";

$user_id = $_SESSION['user']['user_id'];
$product_id = intval($_GET['id']);

// check if already in cart
$res = mysqli_query($conn, "SELECT * FROM cart WHERE user_id=$user_id AND product_id=$product_id");

if(mysqli_num_rows($res) > 0){
    mysqli_query($conn, "UPDATE cart SET quantity = quantity + 1 WHERE user_id=$user_id AND product_id=$product_id");
}else{
    mysqli_query($conn, "INSERT INTO cart(user_id, product_id, quantity) VALUES($user_id, $product_id, 1)");
}

echo "added";