<?php
session_start();
include "db.php";

$id = intval($_GET['id']);
$res = mysqli_query($conn,"SELECT * FROM products WHERE product_id=$id");
$row = mysqli_fetch_assoc($res);
?>

<!DOCTYPE html>
<html>
<head>
<title>Product Detail</title>
</head>

<body>

<h2><?= $row['product_name'] ?></h2>

<img src="<?= $row['image'] ?>" width="300">

<p>CPU: <?= $row['cpu'] ?></p>
<p>GPU: <?= $row['gpu'] ?></p>
<p>RAM: <?= $row['ram'] ?></p>
<p>Storage: <?= $row['storage'] ?></p>
<p>Motherboard: <?= $row['motherboard'] ?></p>

<h3>RM <?= $row['price'] ?></h3>

<button onclick="add(<?= $row['product_id'] ?>)">Add to Cart</button>

<br><br>
<a href="product.php">⬅ Back</a>

<script>
function add(id){
    fetch("add_to_cart.php?id="+id)
    .then(()=>alert("Added to cart"));
}
</script>

</body>
</html>