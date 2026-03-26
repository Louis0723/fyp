<?php
session_start();
include "db.php";

$user_id = $_SESSION['user']['user_id'];
$id = intval($_GET['id']);
$action = $_GET['action'];

if($action == "inc"){
    mysqli_query($conn, "UPDATE cart SET quantity = quantity + 1 WHERE user_id=$user_id AND product_id=$id");
}

if($action == "dec"){
    mysqli_query($conn, "UPDATE cart SET quantity = quantity - 1 WHERE user_id=$user_id AND product_id=$id");

    mysqli_query($conn, "DELETE FROM cart WHERE quantity <= 0 AND user_id=$user_id");
}