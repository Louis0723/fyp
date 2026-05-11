<?php
session_start();
include "db.php";

/* Check login */
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['user_id'];

/* Get order ID safely */
if (!isset($_GET['id'])) {
    header("Location: history.php");
    exit;
}

$order_id = intval($_GET['id']);

/* SECURITY CHECK:
   Only allow user-owned order AND only if status is Delivered */
$stmt = $conn->prepare("
    UPDATE orders 
    SET status = 'Completed'
    WHERE order_id = ? 
    AND user_id = ? 
    AND status = 'Delivered'
");

$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();

/* Optional feedback handling */
if ($stmt->affected_rows > 0) {
    $_SESSION['msg'] = "Order marked as completed successfully!";
} else {
    $_SESSION['msg'] = "Invalid action or order already completed.";
}

$stmt->close();

/* Redirect back to history */
header("Location: history.php");
exit;
?>