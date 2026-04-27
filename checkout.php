<?php
session_start();
include "db.php";

$user_id = $_SESSION['user']['user_id'];

$res_user = mysqli_query($conn,"SELECT * FROM users WHERE user_id=$user_id");
$user = mysqli_fetch_assoc($res_user);

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

if(isset($_POST['pay'])){

    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $method = mysqli_real_escape_string($conn, $_POST['method']);

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

    if(!empty($items)){
        mysqli_query($conn,"
        INSERT INTO orders(user_id,total_price,address,phone)
        VALUES($user_id,$total,'$address','$phone')
        ");

        $order_id = mysqli_insert_id($conn);

        foreach($items as $row){
            mysqli_query($conn,"
            INSERT INTO order_items(order_id,product_id,quantity,price)
            VALUES($order_id,{$row['product_id']},{$row['quantity']},{$row['price']})
            ");

            mysqli_query($conn,"
            UPDATE products 
            SET stock = stock - {$row['quantity']}
            WHERE product_id = {$row['product_id']}
            ");
        }

        mysqli_query($conn,"DELETE FROM cart WHERE user_id=$user_id");

        $to = $user['email'];
        $subject = "Order Receipt - PC Store";

        $message = "Thank you for your order!\n\nOrder ID: $order_id\nTotal: RM $total";

        $headers = "From: noreply@pcstore.com";

        mail($to, $subject, $message, $headers);

        header("Location: checkout.php?success=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Checkout - PC Store</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

<style>
*{ margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
body{ background: linear-gradient(135deg,#0f0c29,#302b63,#24243e); color:white; min-height:100vh; }
#particles-js{ position:fixed; width:100%; height:100%; z-index:-1; pointer-events:none; }

.container{
    max-width:600px;
    margin:100px auto;
    padding:30px;
    background: rgba(255,255,255,0.05);
    border-radius:20px;
    backdrop-filter: blur(15px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.4);
    text-align:center;
}

h1{
    font-size:38px;
    margin-bottom:30px;
    color:#00f0ff;
    text-shadow:0 0 15px #00f0ff;
}

.back{
    display:inline-block;
    margin-bottom:25px;
    padding:10px 20px;
    background:#ff00ff;
    color:#fff;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
    transition:0.3s;
}
.back:hover{ transform: scale(1.05); }

.total{
    font-size:26px;
    margin-bottom:20px;
    color:#ff00ff;
    text-shadow:0 0 10px #ff00ff;
}

form select {
    padding:10px 15px;
    width:100%;
    border-radius:12px;
    border:2px solid #00f0ff; /* added neon cyan border */
    margin-bottom:20px;
    font-weight:600;
    background: rgba(48, 43, 99, 0.8) url('data:image/svg+xml;utf8,<svg fill="white" height="12" viewBox="0 0 24 24" width="12" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 12px center;
    background-size:12px;
    color:#00f0ff; /* neon cyan text */
    appearance:none;
    -webkit-appearance:none;
    -moz-appearance:none;
    box-shadow: 0 0 10px #00f0ff; /* subtle glow */
    transition: 0.3s;
}
form select:focus {
    outline:none;
    border-color:#ff00ff; /* border changes on focus to neon magenta */
    box-shadow: 0 0 15px #ff00ff;
}

form select option {
    background-color: rgba(48, 43, 99, 0.9);
    color:#00f0ff;                             
    font-weight:600;
}
form select option:hover {
    background-color:#ff00ff; 
    color:#000;
}

form button{
    padding:12px 20px;
    width:100%;
    border:none;
    border-radius:12px;
    background: linear-gradient(90deg,#00f0ff,#ff00ff);
    color:white;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}
form button:hover{
    transform:scale(1.05);
    box-shadow:0 0 15px #00f0ff,0 0 25px #ff00ff;
}

.success{
    margin-top:20px;
    padding:20px;
    border-radius:15px;
    background: rgba(0,255,255,0.1);
    color:#00ffea;
    text-shadow:0 0 10px #00f0ff;
}
.success a{
    display:inline-block;
    margin-top:20px;
    padding:10px 20px;
    background:#00f0ff;
    color:#000;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
    transition:0.3s;
}
.success a:hover{ transform:scale(1.05); }

.input-box{
    margin-bottom:15px;
}

.input-box input{
    width:100%;
    padding:12px 15px;
    border-radius:12px;
    border:2px solid #00f0ff;
    background: rgba(48, 43, 99, 0.8);
    color:#00f0ff;
    font-weight:600;
    outline:none;
    box-shadow: 0 0 10px #00f0ff;
    transition:0.3s;
}

.input-box input::placeholder{
    color:rgba(0,240,255,0.6);
}

.input-box input:focus{
    border-color:#ff00ff;
    box-shadow: 0 0 15px #ff00ff;
    color:#ff00ff;
}

</style>
</head>
<body>

<div id="particles-js"></div>

<div class="container">

<h1>Checkout</h1>

<a href="cart.php" class="back">← Back to Cart</a>

<?php if(isset($_GET['success'])): ?>
    <div class="success">
        ✅ Payment Successful!<br>
        <a href="product.php">🏠 Back to Home</a>
    </div>

<?php elseif(empty($items)): ?>
    <div class="success">
        🛒 Your cart is empty!<br>
        <a href="product.php">Back to Products</a>
    </div>

<?php else: ?>

<div style="text-align:left; margin-bottom:20px;">
<h3>Order Summary</h3>

<?php foreach($items as $row): ?>
<p>
<?= $row['product_name'] ?> x <?= $row['quantity'] ?>
= RM <?= $row['price'] * $row['quantity'] ?>
</p>
<?php endforeach; ?>

</div>
<div class="total">Total: RM <?= $total ?></div>

<form method="post">

<div class="input-box">
    <input type="text" name="address"
           value="<?= htmlspecialchars($user['address']) ?>"
           placeholder="Enter Address"
           required>
</div>

<div class="input-box">
    <input type="text" name="phone"
           value="<?= $user['phone'] ?>"
           placeholder="Phone Number"
           required>
</div>

<select name="method" required>
<option value="">Select Payment Method</option>
<option value="Credit Card">Credit Card</option>
<option value="Touch n Go">Touch n Go</option>
<option value="FPX">FPX</option>
</select>

<button name="pay">Pay Now</button>
</form>



<?php endif; ?>
</div>

<script>
particlesJS("particles-js",{
"particles":{
"number":{"value":70},
"color":{"value":["#00f0ff","#ff00ff"]},
"shape":{"type":"circle"},
"opacity":{"value":0.5},
"size":{"value":3,"random":true},
"line_linked":{"enable":true,"distance":150,"color":"#00f0ff","opacity":0.3,"width":1},
"move":{"enable":true,"speed":2}
}
});
</script>

</body>
</html>