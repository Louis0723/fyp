
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PC Store</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tsparticles@2/tsparticles.bundle.min.js"></script>

<style>
*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;
}

body{
background:#0f172a;
color:white;
overflow-x:hidden;
}

#tsparticles{
position:fixed;
width:100%;
height:100%;
z-index:-1;
}

.hero{
min-height:100vh;
display:flex;
justify-content:center;
align-items:center;
padding:30px;
}

.hero-box{
max-width:1100px;
width:100%;
background:rgba(255,255,255,.06);
backdrop-filter:blur(20px);
padding:60px;
border-radius:35px;
box-shadow:0 15px 50px rgba(0,0,0,.4);
text-align:center;
}

.logo{
width:130px;
margin-bottom:25px;
filter:drop-shadow(0 0 20px cyan);
}

h1{
font-size:65px;
margin-bottom:20px;
background:linear-gradient(90deg,#00dbde,#fc00ff);
-webkit-background-clip:text;
-webkit-text-fill-color:transparent;
}

.subtitle{
font-size:18px;
line-height:1.9;
max-width:850px;
margin:auto;
opacity:.85;
}

.buttons{
margin-top:35px;
display:flex;
gap:20px;
justify-content:center;
flex-wrap:wrap;
}

.btn{
padding:15px 35px;
text-decoration:none;
border-radius:50px;
font-weight:600;
transition:.4s;
}

.login{
background:linear-gradient(90deg,#00dbde,#fc00ff);
color:white;
}

.register{
border:2px solid white;
color:white;
}

.btn:hover{
transform:translateY(-5px) scale(1.05);
}

.features{
padding:10px 8%;
display:grid;
grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
gap:30px;
}

.card{
background:rgba(255,255,255,.08);
backdrop-filter:blur(20px);
padding:35px;
border-radius:30px;
text-align:center;
transition:.4s;
}

.card:hover{
transform:translateY(-10px);
}

.card h3{
margin-bottom:15px;
color:#00dbde;
}

.about{
padding:60px 10%;
text-align:center;
}

.about h2{
margin-bottom:20px;
font-size:35px;
}

.about p{
line-height:2;
opacity:.85;
max-width:900px;
margin:auto;
}

footer{
text-align:center;
padding:25px;
background:rgba(0,0,0,.3);
}
</style>
</head>
<body>

<div id="tsparticles"></div>

<section class="hero">
<div class="hero-box">

<img src="image4.jpeg" class="logo">

<h1>Welcome to PC Store</h1>

<p class="subtitle">
Discover premium gaming PCs, high-performance components, accessories, and technology products designed to deliver an exceptional shopping experience.
</p>

<div class="buttons">
<a href="login.php" class="btn login">Login</a>
<a href="register.php" class="btn register">Register</a>
</div>

</div>
</section>

<section class="features">

<div class="card">
<img src="image5.jpeg" style="width:100%;height:180px;object-fit:cover;border-radius:20px;margin-bottom:15px;">
<h3>Gaming Keyboard</h3>
<p>Premium mechanical keyboards with responsive switches and RGB lighting.</p>
</div>

<div class="card">
<img src="image6.jpg" style="width:100%;height:180px;object-fit:cover;border-radius:20px;margin-bottom:15px;">
<h3>High Performance PC</h3>
<p>Powerful gaming and workstation PCs designed for speed and performance.</p>
</div>

<div class="card">
<img src="image7.png" style="width:100%;height:180px;object-fit:cover;border-radius:20px;margin-bottom:15px;">
<h3>Gaming Monitor</h3>
<p>High refresh rate displays with crystal-clear visuals and immersive viewing.</p>
</div>

</section>

<section class="about">
<h2>About PC Store</h2>
<p>
PC Store is committed to delivering quality technology products and services while ensuring a smooth and secure shopping experience.
</p>
</section>

<footer>
© 2026 PC Store | Technology Solutions Platform
</footer>

<script>
tsParticles.load("tsparticles",{
particles:{
number:{value:70},
color:{value:["#00dbde","#fc00ff"]},
move:{enable:true,speed:1},
size:{value:{min:1,max:4}},
opacity:{value:.5}
}
});
</script>

</body>
</html>
