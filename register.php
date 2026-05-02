<?php
include "db.php";
session_start();
require "vendor/autoload.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = htmlspecialchars(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL);
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if (!$email) {
        $message = "Invalid email format!";
    }
    else if (strlen($password) < 8) {
        $message = "Password must be at least 8 characters!";
    }
    else if ($password !== $confirm_password) {
        $message = "Passwords do not match!";
    }
    else {

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        if ($res->num_rows > 0) {
            $message = "Email already registered!";
        } 
        else {

            $otp = rand(100000, 999999);

$_SESSION['otp_type'] = 'register';

$_SESSION['temp_user'] = [
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'password' => $hashed,
    'otp' => password_hash($otp, PASSWORD_DEFAULT),
    'time' => time()
];

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'ziyiyap2006@gmail.com';
                $mail->Password = 'ncprqebxyjjoegxx';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('ziyiyap2006@gmail.com', 'LOZ PC STORE');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = "Your OTP Code";
                $mail->Body = "
                    <h2>LOZ PC STORE Verification</h2>
                    <p>Your OTP is:</p>
                    <h1>$otp</h1>
                ";

                if ($mail->send()) {
                    header("Location: verify.php");
                    exit;
                } else {
                    $message = "Failed to send OTP.";
                }

            } catch (Exception $e) {
                $message = "Failed to send OTP. Try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register - PC Store</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
body, html { height:100%; background: linear-gradient(135deg,#0f0c29,#302b63,#24243e); color:white; }
#particles-js { position: fixed; width:100%; height:100%; z-index:-1; }

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
    width: 600px;
    margin: 80px auto;
    padding: 40px;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(15px);
    border-radius: 25px;
    box-shadow: 0 10px 30px rgba(0,255,255,0.2);
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

.row {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
}

.input-group {
    flex: 1;
}

.input-group label {
    display: block;
    margin-bottom: 5px;
    font-size: 14px;
}

.input-group input {
    width: 100%;
    padding: 12px;
    border-radius: 10px;
}

.input-group.full {
    width: 100%;
    margin-bottom: 15px;
}

.eye {
    position:absolute;
    right:15px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
}

input {
    margin-top: 5px;
}

#strength, #matchMsg {
    font-size: 13px;
    margin-top: 5px;
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
    <h2>📝 Create Account</h2>

    <?php if($message!=""): ?>
        <div class="msg"><?= $message ?></div>
    <?php endif; ?>

    <form method="post" onsubmit="return validateForm()">

        <!-- Row 1 -->
        <div class="row">
            <div class="input-group">
                <label>Full Name <span style="color:red">*</span></label>
                <input type="text" name="name" required>
            </div>

            <div class="input-group">
                <label>Email <span style="color:red">*</span></label>
                <input type="email" name="email" placeholder="abc@gmail.com" required>
            </div>
        </div>

        <div class="input-group full">
            <label>Phone Number <span style="color:white">*</span></label>
            <input type="text" name="phone">
        </div>

        <div class="row">
            <div class="input-group">
                <label>Password <span style="color:red">*</span></label>

                <div style="position:relative;">
                    <input type="password" name="password" id="password" required>
                    <span onclick="togglePass('password', this)" class="eye">👁️</span>
                </div>

                <div id="strength"></div>
            </div>

            <div class="input-group">
                <label>Confirm Password <span style="color:red">*</span></label>

                <div style="position:relative;">
                    <input type="password" name="confirm_password" id="confirm_password" required>
                    <span onclick="togglePass('confirm_password', this)" class="eye">👁️</span>
                </div>

                <div id="matchMsg"></div>
            </div>
        </div>

        <button>Register</button>
    </form>

    <div class="link-text">
        Already have an account? <a href="login.php">Login</a>
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

const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirm_password");
const strengthText = document.getElementById("strength");
const matchMsg = document.getElementById("matchMsg");

password.addEventListener("input", () => {
    const val = password.value;

    if(val.length < 8){
        strengthText.textContent = "Too short (min 8 characters)";
        strengthText.style.color = "red";
    }
    else if(/[A-Z]/.test(val) && /[0-9]/.test(val) && /[!@#$%^&*]/.test(val)){
        strengthText.textContent = "Strong password 💪";
        strengthText.style.color = "lime";
    }
    else if(/[A-Z]/.test(val) || /[0-9]/.test(val)){
        strengthText.textContent = "Medium strength";
        strengthText.style.color = "orange";
    }
    else{
        strengthText.textContent = "Weak password";
        strengthText.style.color = "red";
    }
});

confirmPassword.addEventListener("input", () => {
    if(confirmPassword.value === ""){
        matchMsg.textContent = "";
        return;
    }

    if(password.value === confirmPassword.value){
        matchMsg.textContent = "Passwords match ✅";
        matchMsg.style.color = "lime";
    } else {
        matchMsg.textContent = "Passwords do not match ❌";
        matchMsg.style.color = "red";
    }
});

function validateForm(){
    if(password.value.length < 8){
        alert("Password must be at least 8 characters");
        return false;
    }

    if(password.value !== confirmPassword.value){
        matchMsg.textContent = "Fix errors before submitting!";
        return false;
    }

    const btn = document.querySelector("button");
    btn.disabled = true;
    btn.innerText = "Registering...";

    return true;
}

function togglePass(fieldId, icon){
    const input = document.getElementById(fieldId);

    if(input.type === "password"){
        input.type = "text";
        icon.textContent = "🙈";
    } else {
        input.type = "password";
        icon.textContent = "👁️";
    }
}
</script>

</body>
</html>