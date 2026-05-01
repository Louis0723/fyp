<?php
session_start();
include "db.php";

$message = "";

if (!isset($_SESSION['otp_type'])) {
    header("Location: login.php");
    exit;
}

$type = $_SESSION['otp_type'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $otp = $_POST['otp'];

    // =====================================================
    // REGISTER OTP FLOW
    // =====================================================
if ($type === 'register') {

    if (!isset($_SESSION['temp_user'])) {
        die("Session expired. Please register again.");
    }

    $data = $_SESSION['temp_user'];

    if (time() - $data['time'] > 300) {
        die("OTP expired. Please register again.");
    }

    if (password_verify($otp, $data['otp'])) {

        $stmt = $conn->prepare("
            INSERT INTO users (name,email,phone,password) 
            VALUES (?,?,?,?)
        ");

        $stmt->bind_param(
            "ssss",
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['password']
        );

        $stmt->execute();

        unset($_SESSION['temp_user']);
        unset($_SESSION['otp_type']);

        header("Location: login.php?verified=1");
        exit;

    } else {
        $message = "Invalid OTP!";
    }
}

    // =====================================================
    // PASSWORD CHANGE OTP FLOW
    // =====================================================
    else if ($type === 'password_change') {

    if (!isset($_SESSION['user'])) {
        die("Session expired.");
    }

    $user_id = (int) $_SESSION['user']['user_id'];

    $res = mysqli_query($conn,"SELECT * FROM users WHERE user_id=$user_id");
    $user = mysqli_fetch_assoc($res);

    if (
        $user &&
        $user['otp_code'] == $otp &&
        strtotime($user['otp_expiry']) > time()
    ) {

        $hashed = password_hash($_SESSION['temp_new_password'], PASSWORD_DEFAULT);

        mysqli_query($conn,"
            UPDATE users 
            SET password='$hashed',
                last_password_change=NOW(),
                otp_code=NULL,
                otp_expiry=NULL
            WHERE user_id=$user_id
        ");

        unset($_SESSION['temp_new_password']);
        unset($_SESSION['otp_type']);

        header("Location: profile.php?success=1");
        exit;

    } else {
        $message = "Invalid or expired OTP!";
    }
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Verify OTP</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background: linear-gradient(135deg,#0f0c29,#302b63,#24243e);
    color:white;
}

.card{
    width:380px;
    padding:40px;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(15px);
    border-radius:25px;
    box-shadow: 0 10px 30px rgba(0,255,255,0.2);
    text-align:center;
}

h2{
    color:#00f0ff;
    margin-bottom:20px;
    text-shadow:0 0 10px #00f0ff;
}

input{
    width:100%;
    padding:14px;
    border:none;
    border-radius:12px;
    margin-top:15px;
    font-size:16px;
    text-align:center;
    background: rgba(255,255,255,0.2);
    color:white;
    letter-spacing:5px;
    outline:none;
}

input::placeholder{
    color:rgba(255,255,255,0.6);
}

button{
    width:100%;
    padding:12px;
    margin-top:20px;
    border:none;
    border-radius:12px;
    background: linear-gradient(90deg,#00f0ff,#ff00ff);
    color:white;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    transform:scale(1.05);
}

.msg{
    color:#ff5555;
    margin-top:10px;
    font-size:14px;
}

.small{
    margin-top:15px;
    font-size:13px;
    color:rgba(255,255,255,0.7);
}
</style>

</head>
<body>

<div class="card">

    <h2>OTP Verification</h2>

    <p class="small">We sent a 6-digit code to your email</p>

    <?php if($message!=""): ?>
        <div class="msg"><?= $message ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="otp" maxlength="6" placeholder="Enter OTP" required>
        <button>Verify OTP</button>
    </form>

    <p class="small" style="margin-top:20px;">
        Didn't receive OTP? 
        <a href="resend_otp.php" style="color:#00f0ff;text-decoration:none;font-weight:600;">
            Resend
        </a>
    </p>

</div>

</body>
</html>