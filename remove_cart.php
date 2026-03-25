<?php
session_start();
include "db.php";

$user_id = $_SESSION['user']['user_id'];
$id = intval($_GET['id']);

mysqli_query($conn, "DELETE FROM cart WHERE user_id=$user_id AND product_id=$id");