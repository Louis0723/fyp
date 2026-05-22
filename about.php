
<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
<title>About Us - PC Store</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{
background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);
min-height:100vh;
color:white;
}

#particles-js{
position:fixed;
width:100%;
height:100%;
z-index:-1;
}

.container{
max-width:900px;
margin:60px auto;
padding:35px;
border-radius:25px;
background:rgba(255,255,255,0.05);
backdrop-filter:blur(15px);
box-shadow:0 10px 30px rgba(0,0,0,.5);
}

h1{
text-align:center;
margin-bottom:30px;
color:#00f0ff;
text-shadow:0 0 15px #00f0ff;
}

.back{
display:inline-block;
margin-top:25px;
color:white;
text-decoration:none;
background:#ff00ff;
padding:12px 20px;
border-radius:12px;
font-weight:600;
transition:.3s;
}

.back:hover{
transform:translateY(-3px);
box-shadow:0 0 20px #ff00ff;
}

.summary{
margin-bottom:25px;
padding:25px;
border-radius:20px;
background:rgba(255,255,255,0.05);
border:1px solid rgba(255,255,255,0.1);
}

.summary h2{
color:#00f0ff;
margin-bottom:15px;
}

.summary p{
line-height:1.9;
color:#ddd;
}

.summary table{
width:100%;
border-collapse:collapse;
margin-top:10px;
}

.summary th,.summary td{
padding:15px;
text-align:left;
border-bottom:1px solid rgba(255,255,255,0.2);
}

.summary th{
color:#00f0ff;
}
</style>
</head>

<body>

<div id="particles-js"></div>

<div class="container">

<h1>About PC Store</h1>

<div class="summary">
<h2>Who We Are</h2>
<p>
PC Store is a trusted online technology platform specializing in computer products,
gaming systems, PC components, accessories, and digital solutions. We are committed
to delivering high-quality products and a seamless shopping experience for customers.
</p>
</div>

<div class="summary">
<h2>Our Mission</h2>
<p>
Our mission is to provide customers with premium technology products at competitive
prices while maintaining excellent customer service, secure transactions, and fast delivery.
</p>
</div>

<div class="summary">
<h2>Why Choose Us</h2>

<table>
<tr>
<th>Feature</th>
<th>Description</th>
</tr>

<tr>
<td>Premium Products</td>
<td>High quality products from trusted brands.</td>
</tr>

<tr>
<td>Secure Payment</td>
<td>Protected payment methods and safe checkout process.</td>
</tr>

<tr>
<td>Fast Delivery</td>
<td>Quick processing and efficient order management.</td>
</tr>

<tr>
<td>Customer Support</td>
<td>Dedicated assistance for all customer inquiries.</td>
</tr>

<tr>
<td>User Experience</td>
<td>Modern platform with smooth shopping experience.</td>
</tr>

</table>
</div>

<div class="summary">
<h2>Our Vision</h2>
<p>
To become a leading technology retailer recognized for innovation,
service excellence, and customer satisfaction.
</p>
</div>

<div style="text-align:center;">
<a href="product.php" class="back">← Back to Products</a>
</div>

</div>

<script>
particlesJS("particles-js",{
particles:{
number:{value:70},
color:{value:"#00f0ff"},
shape:{type:"circle"},
opacity:{value:.5},
size:{value:3},
line_linked:{enable:true,color:"#00f0ff"},
move:{enable:true,speed:2}
}
});
</script>

</body>
</html>
```
