<?php
include "db.php";
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL);
    if(!$email){
    $message = "Invalid email format!";
    }
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $res = $stmt->get_result();

    if($user = $res->fetch_assoc()){
    if(password_verify($password,$user['password'])){
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'user_id' => $user['user_id'],
            'name' => $user['name'],
            'email' => $user['email']
        ];
        header("Location: product.php");
        exit;
    } else {
        $message = "Invalid email or password!";
    }
    } else {
    $message = "Invalid email or password!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - PC Store</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }

body, html { 
    height:100%; 
    background: 
    linear-gradient(135deg,#0f0c29,#302b63,#24243e); 
    color:white; 
}

#particles-js { 
    position: fixed; 
    width:100%; 
    height:100%; 
    z-index:-1; 
}

header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:20px 50px;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(10px);
    position: sticky;
    top:0;
    z-index:100;
    border-bottom:1px solid rgba(255,255,255,0.1);
}

header h2 { 
    color:#00f0ff; 
    font-size:28px; 
    text-shadow:0 0 10px #00f0ff; 
    cursor:pointer; 
}

header nav a {
    margin-left:30px;
    color:white;
    text-decoration:none;
    font-weight:600;
    transition:0.3s;
}

header nav a:hover { 
    color:#ff00ff; 
    text-shadow:0 0 10px #ff00ff; 
}

.container {
    width:400px;
    margin:120px auto;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(15px);
    border-radius:25px;
    padding:40px;
    box-shadow: 0 10px 30px rgba(0,255,255,0.2);
    text-align:center;
}
.container h2 { 
    color:#00f0ff; 
    font-size:28px; 
    margin-bottom:30px; 
    text-shadow:0 0 10px #00f0ff; 
}

input {
    width:100%;
    padding:12px;
    margin:10px 0;
    border-radius:12px;
    border:none;
    background: rgba(255,255,255,0.2);
    color:white;
    font-size:16px;
}

input::placeholder { 
    color: rgba(255,255,255,0.7); 
}

button {
    width:100%;
    padding:12px;
    border:none;
    border-radius:12px;
    background: linear-gradient(90deg,#00f0ff,#ff00ff);
    color:white;
    font-weight:600;
    cursor:pointer;
    margin-top:15px;
    box-shadow: 0 5px 15px rgba(0,255,255,0.3);
    transition:0.3s;
}

button:hover { 
    transform: scale(1.05); 
    box-shadow:0 10px 25px rgba(255,0,255,0.5); 
}

.logo-center {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    margin-bottom: 20px;
}

.logo-center img {
    height: 60px;
}

.logo-center h2 {
    font-size: 28px;
    color: white;
    letter-spacing: 2px;
    text-shadow: 0 0 10px #00f0ff;
}

.msg { 
    color:#ff5555; 
    margin-bottom:15px; 
    font-weight:600; 
}

.link-text { 
    margin-top:15px; 
    color:white; 

}
.link-text a { 
    color:#ff00ff; 
    text-decoration:none; 
    font-weight:600; 
}

.link-text a:hover { 
    text-decoration:underline; 
    }

button:disabled{
    opacity:0.7;
    cursor:not-allowed;
}

</style>

</head>
<body>

<div id="particles-js"></div>

<header>
    <div class="logo-center">
        <img src="storelogo.jpeg" alt="LOZ PC STORE" onclick="window.location.href='product.php'">
        <h2>LOZ PC STORE</h2>
    </div>
    <nav>
        <a href="about.php">About Us</a>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    </nav>
</header>

<div class="container">
    <h2>🔐 Login</h2>

    <?php if(isset($_GET['success'])): ?>
        <div class="msg" style="color:lime;">
            Registration successful! Please login.
        </div>
    <?php endif; ?>

    <?php if($message!=""): ?>
        <div class="msg"><?= $message ?></div>
    <?php endif; ?>

    <form method="post" onsubmit="return validateLogin()">
        <input type="email" name="email" placeholder="Email" required autocomplete="email">
        <div style="position:relative;">
        <input type="password" name="password" id="password" placeholder="Password" required autocomplete="current-password">
        <span onclick="togglePass(this)" style="
        position:absolute;
        right:15px;
        top:50%;
        transform:translateY(-50%);
        cursor:pointer;
        ">👁️</span>
        </div>
        <button>Login</button>
    </form>

    <div class="link-text">
        Don't have an account? <a href="register.php">Register</a>
    </div>
</div>

<script>
particlesJS("particles-js", {
  "particles": {
    "number": {"value":60,"density":{"enable":true,"value_area":800}},
    "color":{"value":["#00f0ff","#ff00ff"]},
    "shape":{"type":"circle"},
    "opacity":{"value":0.5,"random":true},
    "size":{"value":3,"random":true},
    "line_linked":{"enable":true,"distance":150,"color":"#00f0ff","opacity":0.3,"width":1},
    "move":{"enable":true,"speed":2,"out_mode":"out"}
  },
  "interactivity": {
    "detect_on":"canvas",
    "events":{"onhover":{"enable":true,"mode":"grab"},"onclick":{"enable":true,"mode":"push"}},
    "modes":{"grab":{"distance":200,"line_linked":{"opacity":0.5}},"push":{"particles_nb":4}}
  },
  "retina_detect":true
});
function togglePass(icon){
    const p = document.getElementById("password");

    if(p.type === "password"){
        p.type = "text";
        icon.textContent = "🙈";
    } else {
        p.type = "password";
        icon.textContent = "👁️";
    }
}

function validateLogin(){
    const email = document.querySelector("input[name='email']").value.trim();
    const pass = document.querySelector("input[name='password']").value;

    if(email === "" || pass === ""){
        alert("Please fill in all fields");
        return false;
    }

    const btn = document.querySelector("button");
    btn.disabled = true;
    btn.innerText = "Logging in...";

    return true;
}
</script>

</body>
</html>