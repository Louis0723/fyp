<?php
include "../db.php";
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $query = $stmt->get_result();

    if ($query->num_rows == 1) {
        $admin = $query->fetch_assoc();

        if (password_verify($password, $admin['password'])) {
            $_SESSION["admin"] = $admin['username'];
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $message = "❌ Invalid password!";
        }
    } else {
        $message = "❌ Username not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Login</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Poppins;}

body{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:linear-gradient(135deg,#e0f7ff,#c2e9fb);
    color:#333;
}

/* glowing background（亮色版） */
body::before{
    content:"";
    position:absolute;
    width:600px;
    height:600px;
    background:radial-gradient(circle,#00c6ff,#90f7ec);
    filter:blur(150px);
    opacity:0.5;
    animation:moveGlow 6s infinite alternate;
}

@keyframes moveGlow{
    from{transform:translate(-100px,-100px);}
    to{transform:translate(100px,100px);}
}

/* card */
.login-card{
    margin-top:80px;
    width:360px;
    padding:45px 35px;
    border-radius:20px;
    background:rgba(255,255,255,0.7);
    backdrop-filter:blur(20px);
    box-shadow:0 10px 40px rgba(0,0,0,0.15);
    text-align:center;
}

/* title */
.login-card h2{
    font-size:24px;
    font-weight:600;
    margin-bottom:25px;
    color:#0072ff;
}

/* input */
.input-group{
    display:flex;
    align-items:center;
    background:rgba(0,0,0,0.05);
    padding:14px 18px;
    border-radius:30px;
    margin-bottom:18px;
    transition:0.3s;
}

.input-group:hover{
    background:rgba(0,114,255,0.1);
}

.input-group input{
    background:transparent;
    border:none;
    outline:none;
    color:#333;
    font-size:15px;
    flex:1;
}

.input-group span{
    margin-right:10px;
    font-size:18px;
}

/* eye */
.eye{
    cursor:pointer;
}

/* button */
.btn{
    width:100%;
    padding:14px;
    border:none;
    border-radius:30px;
    font-size:16px;
    font-weight:600;
    background:linear-gradient(90deg,#00c6ff,#0072ff);
    color:#fff;
    cursor:pointer;
    transition:0.3s;
}

.btn:hover{
    transform:scale(1.05);
    box-shadow:0 0 15px rgba(0,198,255,0.6);
}

/* message */
.message{
    color:red;
    margin-bottom:10px;
}
</style>
</head>

<body>

<?php include "login_header.php"; ?>

<div class="login-card">
<h2>Admin Login</h2>

<?php if($message): ?>
<p class="message"><?= $message ?></p>
<?php endif; ?>

<form method="POST">

<div class="input-group">
<span>👤</span>
<input type="text" name="username" placeholder="Username" required>
</div>

<div class="input-group">
<span>🔒</span>
<input type="password" name="password" id="password" placeholder="Password" required>
<span class="eye" onclick="togglePassword()">👁</span>
</div>

<button class="btn">Login</button>

</form>
</div>

<script>
function togglePassword(){
    let p = document.getElementById("password");
    p.type = (p.type === "password") ? "text" : "password";
}
</script>

</body>
</html>