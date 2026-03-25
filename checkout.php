<?php
session_start();
include "db.php";

$user_id = $_SESSION['user']['user_id'];

$res = mysqli_query($conn,"
SELECT p.*, c.quantity 
FROM cart c 
JOIN products p ON c.product_id = p.product_id
WHERE c.user_id = $user_id
");

$total = 0;
$items = [];

while($row = mysqli_fetch_assoc($res)){
    $total += $row['price'] * $row['quantity'];
    $items[] = $row;
}
?>

<a href="cart.php" style="
    display:inline-block;
    margin-bottom:15px;
    padding:10px 20px;
    background:#ff00ff;
    color:#fff;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
    transition:0.3s;
">← Back to Cart</a>

<h2>Checkout</h2>

<p>Total: RM <?= $total ?></p>

<form method="post">
<select name="method">
<option>Credit Card</option>
<option>Touch n Go</option>
<option>FPX</option>
</select>

<br><br>
<button name="pay">Pay Now</button>
</form>

<?php
if(isset($_POST['pay'])){

    // create order
    mysqli_query($conn,"INSERT INTO orders(user_id,total_price) VALUES($user_id,$total)");
    $order_id = mysqli_insert_id($conn);

    foreach($items as $row){

        // insert items
        mysqli_query($conn,"
        INSERT INTO order_items(order_id,product_id,quantity,price)
        VALUES($order_id,{$row['product_id']},{$row['quantity']},{$row['price']})
        ");

        // reduce stock
        mysqli_query($conn,"
        UPDATE products SET stock = stock - {$row['quantity']}
        WHERE product_id = {$row['product_id']}
        ");
    }

    // clear cart
    mysqli_query($conn,"DELETE FROM cart WHERE user_id=$user_id");

    echo "
<h2>✅ Payment Successful</h2>
<a href='product.php' style=\"
    display:inline-block;
    margin-top:20px;
    padding:10px 20px;
    background:#00f0ff;
    color:#000;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
    transition:0.3s;
\">🏠 Back to Home</a>
";
}