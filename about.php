<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>About Us</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

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
z-index:-1;
width:100%;
height:100%;
}

.logo-container{
text-align:center;
padding-top:40px;
}

.logo{
width:140px;
height:140px;
object-fit:contain;
border-radius:50%;
padding:15px;

background:rgba(255,255,255,.08);

box-shadow:
0 0 30px rgba(0,240,255,.4);

transition:.5s;
}

.logo:hover{
transform:scale(1.08) rotate(5deg);

box-shadow:
0 0 50px rgba(252,0,255,.7);
}

.hero{
text-align:center;
padding:40px 20px;
}

.hero h1{
font-size:55px;

background:
linear-gradient(
90deg,
#00dbde,
#fc00ff
);

-webkit-background-clip:text;
-webkit-text-fill-color:transparent;

margin-bottom:15px;
}

.hero p{
opacity:.8;
font-size:18px;
}

.team{
max-width:1200px;
margin:auto;

display:grid;

grid-template-columns:
repeat(auto-fit,minmax(320px,1fr));

gap:40px;

padding:20px 30px 50px;
}

.card{

background:
rgba(255,255,255,.07);

backdrop-filter:blur(18px);

border-radius:30px;

padding:35px;

text-align:center;

position:relative;

overflow:hidden;

box-shadow:
0 10px 30px rgba(0,0,0,.4);

transition:.5s;
}

.card:hover{

transform:
translateY(-15px)
scale(1.03);

box-shadow:
0 20px 50px rgba(0,240,255,.3);
}

.card::before{

content:"";

position:absolute;

inset:0;

padding:2px;

border-radius:30px;

background:
linear-gradient(
45deg,
#00dbde,
#fc00ff,
#00dbde
);

-webkit-mask:
linear-gradient(#fff 0 0) content-box,
linear-gradient(#fff 0 0);

-webkit-mask-composite:xor;
}

.avatar{

width:160px;
height:160px;

object-fit:cover;

border-radius:50%;

border:5px solid rgba(255,255,255,.5);

margin-bottom:20px;
}

.name{
font-size:24px;
font-weight:700;
margin-top:10px;
}

.role{
color:#00f0ff;
font-size:15px;
margin-top:5px;
}

.id{
margin-top:8px;
opacity:.75;
}

.email{
margin-top:18px;
padding:12px;

background:
rgba(255,255,255,.08);

border-radius:12px;

font-size:14px;

word-break:break-word;
}

.email a{
color:#00f0ff;
text-decoration:none;
}

.email a:hover{
text-decoration:underline;
}

.glow{

position:absolute;

width:250px;
height:250px;

background:
radial-gradient(
circle,
rgba(255,255,255,.25),
transparent
);

pointer-events:none;

transform:
translate(-50%,-50%);
}

.fade{
opacity:0;
transform:translateY(50px);
transition:1s;
}

.fade.show{
opacity:1;
transform:translateY(0);
}

.back-container{
text-align:center;
margin-bottom:50px;
}

.back-btn{

display:inline-block;

padding:15px 35px;

border-radius:50px;

text-decoration:none;

font-weight:600;

color:white;

background:
linear-gradient(
90deg,
#00dbde,
#fc00ff
);

box-shadow:
0 8px 25px rgba(0,219,222,.5);

transition:.4s;
}

.back-btn:hover{

transform:
translateY(-5px)
scale(1.05);

box-shadow:
0 15px 40px rgba(252,0,255,.6);
}

footer{

text-align:center;

padding:30px;

background:rgba(0,0,0,.3);

font-size:14px;

opacity:.7;
}

</style>
</head>

<body>

<div id="tsparticles"></div>


<div class="logo-container fade">

<img src="image4.jpeg" class="logo">

</div>


<div class="hero fade">

<h1>Meet Our Team</h1>

<p>
Passionate developers dedicated to building innovative systems and delivering excellent user experiences.
</p>

</div>


<div class="team">




<div class="card fade">

<img src="Image2.jpeg" class="avatar">

<div class="name">
ONG ZHENG HAO
</div>

<div class="role">
System Developer
</div>

<div class="id">
ID: 242DT241T3
</div>

<div class="email">
📧email:
<a href="mailto:ONG.ZHENG.HAO@student.mmu.edu.my">
ONG.ZHENG.HAO@student.mmu.edu.my
</a>
</div>

</div>

<div class="card fade">

<img src="Image1.jpeg" class="avatar">

<div class="name">
CHIA SHI LOUIS
</div>

<div class="role">
System Developer
</div>

<div class="id">
ID: 242DT241JN
</div>

<div class="email">
📧email:
<a href="mailto:CHIA.SHI.LOUIS@student.mmu.edu.my">
CHIA.SHI.LOUIS@student.mmu.edu.my
</a>
</div>

</div>


<div class="card fade">

<img src="Image3.jpeg" class="avatar">

<div class="name">
YAP ZI YI
</div>

<div class="role">
System Developer
</div>

<div class="id">
ID: 242DT2421J
</div>

<div class="email">
📧email:
<a href="mailto:YAP.ZI.YI@student.mmu.edu.my">
YAP.ZI.YI@student.mmu.edu.my
</a>
</div>

</div>

</div>


<div class="back-container">

<a href="product.php" class="back-btn">
← Back to Products
</a>

</div>


<footer>
© 2026 PC Store System | Developed by Creative Team
</footer>

<script>

tsParticles.load("tsparticles",{

background:{
color:"#0f172a"
},

particles:{
number:{value:60},

color:{
value:["#00dbde","#fc00ff"]
},

move:{
enable:true,
speed:1
},

opacity:{
value:.6
},

size:{
value:{min:1,max:3}
}
}

});


const faders=document.querySelectorAll('.fade');

function showOnLoad(){

faders.forEach(el=>{

const top=el.getBoundingClientRect().top;

if(top<window.innerHeight-100){

el.classList.add('show');

}

});

}

window.addEventListener('load',showOnLoad);
window.addEventListener('scroll',showOnLoad);


document.querySelectorAll('.card').forEach(card=>{

const glow=document.createElement('div');

glow.className='glow';

card.appendChild(glow);

card.addEventListener('mousemove',e=>{

const rect=card.getBoundingClientRect();

glow.style.left=(e.clientX-rect.left)+'px';

glow.style.top=(e.clientY-rect.top)+'px';

});

});

</script>

</body>
</html>