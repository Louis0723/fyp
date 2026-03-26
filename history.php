<?php
session_start();
include "db.php";

$user_id = 1;

$result = mysqli_query($conn,"SELECT * FROM orders WHERE user_id=$user_id");

echo "<h2>Order History</h2>";

while($order = mysqli_fetch_assoc($result)){
    echo "<hr>";
    echo "<p>Order ID: {$order['order_id']}</p>";
    echo "<p>Date: {$order['created_at']}</p>";
    echo "<p>Total: RM {$order['total_price']}</p>";

    $items = mysqli_query($conn,"SELECT * FROM order_items WHERE order_id={$order['order_id']}");

    while($item = mysqli_fetch_assoc($items)){
        echo "<p>Product ID: {$item['product_id']} x {$item['quantity']}</p>";
    }
}