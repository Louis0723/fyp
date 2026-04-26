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
<meta charset="UTF-8">
<title><?= $row['product_name'] ?></title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

<style>
*{ margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }

body{
    background: linear-gradient(135deg,#0f0c29,#302b63,#24243e);
    color:white;
    min-height:100vh;
}

#particles-js{
    position:fixed;
    width:100%;
    height:100%;
    z-index:-1;
    pointer-events:none;
}

.container{
    max-width:1000px;
    margin:80px auto;
    padding:30px;
}

.card{
    display:flex;
    gap:40px;
    background: rgba(255,255,255,0.05);
    border-radius:25px;
    padding:30px;
    backdrop-filter: blur(15px);
    box-shadow:0 15px 40px rgba(0,255,255,0.2);
}

.image{
    flex:1;
}

.image img{
    width:100%;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,255,255,0.4);
}

.details{
    flex:1;
}

h2{
    font-size:30px;
    margin-bottom:15px;
    color:#00f0ff;
    text-shadow:0 0 15px #00f0ff;
}

.spec{
    margin-bottom:8px;
    font-size:15px;
}

.price{
    font-size:28px;
    margin:20px 0;
    color:#ff00ff;
    text-shadow:0 0 10px #ff00ff;
}

.stock{
    margin-bottom:15px;
    color:#aaa;
}

.desc{
    margin:15px 0;
    font-size:14px;
    line-height:1.5;
}

button{
    padding:12px 20px;
    border:none;
    border-radius:12px;
    background: linear-gradient(90deg,#00f0ff,#ff00ff);
    color:white;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
    width:100%;
}
button:hover{
    transform:scale(1.05);
    box-shadow:0 0 15px #00f0ff,0 0 25px #ff00ff;
}

button:disabled{
    background:gray;
    cursor:not-allowed;
}

.back{
    display:inline-block;
    margin-bottom:20px;
    padding:10px 20px;
    background:#ff00ff;
    color:white;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
}
.back:hover{
    transform:scale(1.05);
}

</style>
</head>

<body>

<div id="particles-js"></div>

<div class="container">

<a href="product.php" class="back">⬅ Back to Products</a>

<div class="card">

    <div class="image">
        <img src="<?= !empty($row['image']) ? $row['image'] : 'https://via.placeholder.com/400' ?>">
    </div>

    <div class="details">
        <h2><?= $row['product_name'] ?></h2>

        <div class="spec">CPU: <?= $row['cpu'] ?></div>
        <div class="spec">GPU: <?= $row['gpu'] ?></div>
        <div class="spec">RAM: <?= $row['ram'] ?></div>
        <div class="spec">Storage: <?= $row['storage'] ?></div>
        <div class="spec">Motherboard: <?= $row['motherboard'] ?></div>

        <div class="desc">
            <?= !empty($row['description']) ? $row['description'] : 'No description available.' ?>
        </div>

        <div class="price">RM <?= $row['price'] ?></div>
        <div class="stock">Stock: <?= $row['stock'] ?></div>

        <button 
            onclick="add(<?= $row['product_id'] ?>)"
            <?= ($row['stock'] <= 0) ? 'disabled' : '' ?>
        >
            <?= ($row['stock'] <= 0) ? 'Out of Stock' : 'Add to Cart' ?>
        </button>
    </div>

</div>
</div>

<script>
particlesJS("particles-js",{
  particles:{
    number:{value:70},
    color:{value:["#00f0ff","#ff00ff"]},
    shape:{type:"circle"},
    opacity:{value:0.5},
    size:{value:3,random:true},
    line_linked:{enable:true,distance:150,color:"#00f0ff",opacity:0.3,width:1},
    move:{enable:true,speed:2}
  }
});

function add(id){
    fetch("add_to_cart.php?id="+id)
    .then(()=>{
        alert("✅ Added to cart!");
    });
}
</script>

</body>
</html>