<?php
session_start();
include "db.php";

if (!isset($_SESSION['user']['user_id'])) {
    exit("Please login first");
}

$user_id = (int)$_SESSION['user']['user_id'];
$product_id = (int)$_GET['id'];
$qty = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;

if ($qty < 1) {
    $qty = 1;
}

/* Get stock */
$product = mysqli_query($conn,
    "SELECT stock FROM products WHERE product_id = $product_id"
);

if (!$product || mysqli_num_rows($product) == 0) {
    exit("Product not found");
}

$productData = mysqli_fetch_assoc($product);
$stock = (int)$productData['stock'];

/* Check existing cart item */
$cart = mysqli_query($conn,
    "SELECT quantity FROM cart
     WHERE user_id = $user_id
     AND product_id = $product_id"
);

if (mysqli_num_rows($cart) > 0) {

    $cartData = mysqli_fetch_assoc($cart);

    $newQty = $cartData['quantity'] + $qty;

    if ($newQty > $stock) {
        exit("❌ Only $stock item(s) available in stock.");
    }

    mysqli_query($conn,
        "UPDATE cart
         SET quantity = $newQty
         WHERE user_id = $user_id
         AND product_id = $product_id"
    );

} else {

    if ($qty > $stock) {
        exit("❌ Only $stock item(s) available in stock.");
    }

    mysqli_query($conn,
        "INSERT INTO cart(user_id, product_id, quantity)
         VALUES($user_id, $product_id, $qty)"
    );
}

echo "✅ Added to cart successfully!";
?>