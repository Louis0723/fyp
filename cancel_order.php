<?php
session_start();
include "db.php";

$user_id = $_SESSION['user']['user_id'];
$order_id = $_GET['id'];

// check ownership
$check = mysqli_query($conn,"
SELECT * FROM orders 
WHERE order_id=$order_id AND user_id=$user_id
AND status='Pending'
");

if(mysqli_num_rows($check) > 0){

    mysqli_query($conn,"DELETE FROM order_items WHERE order_id=$order_id");
    mysqli_query($conn,"DELETE FROM orders WHERE order_id=$order_id");
}

header("Location: history.php");
exit;
?>