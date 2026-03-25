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

if(mysqli_num_rows($res) == 0){
    echo "<h2>Cart Empty</h2>";
    exit;
}

$total = 0;
?>

<h2>Your Cart</h2>

<a href="product.php" style="
    display:inline-block;
    margin-bottom:15px;
    padding:10px 20px;
    background:#00f0ff;
    color:#000;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
    transition:0.3s;
">← Back to Products</a>

<?php while($row = mysqli_fetch_assoc($res)): 
$sub = $row['price'] * $row['quantity'];
$total += $sub;
?>

<div>
<h3><?= $row['product_name'] ?></h3>

<p>RM <?= $row['price'] ?></p>

<button onclick="update(<?= $row['product_id'] ?>,'dec')">-</button>
<?= $row['quantity'] ?>
<button onclick="update(<?= $row['product_id'] ?>,'inc')">+</button>

<p>Subtotal: RM <?= $sub ?></p>

<button onclick="removeItem(<?= $row['product_id'] ?>)">Remove</button>
<hr>
</div>

<?php endwhile; ?>

<h2>Total: RM <?= $total ?></h2>

<a href="checkout.php">Checkout</a>

<script>
function update(id,action){
    fetch(`update_cart.php?id=${id}&action=${action}`)
    .then(()=>location.reload());
}

function removeItem(id){
    fetch("remove_cart.php?id="+id)
    .then(()=>location.reload());
}
</script>