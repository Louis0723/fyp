<?php
session_start();
include "db.php";
require "vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $res = $stmt->get_result();

    if ($user = $res->fetch_assoc()) {

        $otp = rand(100000,999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        mysqli_query($conn,"
            UPDATE users
            SET otp_code='$otp',
                otp_expiry='$expiry'
            WHERE user_id={$user['user_id']}
        ");

        $_SESSION['otp_type'] = "forgot_password";
        $_SESSION['reset_user_id'] = $user['user_id'];

        // SEND EMAIL
        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'ziyiyap2006@gmail.com';
            $mail->Password   = 'dnuaffkldwjxlqhh';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('ziyiyap2006@gmail.com', 'LOZ PC STORE');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP';
            $mail->Body    = "
                <h2>Password Reset Request</h2>
                <p>Your OTP code is:</p>
                <h1>$otp</h1>
                <p>This OTP expires in 5 minutes.</p>
            ";

            $mail->send();

            header("Location: verify.php");
            exit;

        } catch (Exception $e) {
            $message = "Mailer Error: {$mail->ErrorInfo}";
        }

    } else {
        $message = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Forgot Password</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
body{
    font-family:Poppins,sans-serif;
    background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    color:white;
}

.card{
    width:400px;
    padding:40px;
    border-radius:20px;
    background:rgba(255,255,255,0.1);
    backdrop-filter:blur(10px);
}

input{
    width:100%;
    padding:12px;
    margin-top:15px;
    border:none;
    border-radius:10px;
}

button{
    width:100%;
    padding:12px;
    margin-top:20px;
    border:none;
    border-radius:10px;
    background:linear-gradient(90deg,#00f0ff,#ff00ff);
    color:white;
    font-weight:600;
    cursor:pointer;
}

.msg{
    color:#ff7777;
    margin-top:10px;
}
</style>
</head>

<body>

<div class="card">

<h2>Forgot Password</h2>

<?php if($message!=""): ?>
<div class="msg"><?= $message ?></div>
<?php endif; ?>

<form method="post">

<input type="email" name="email" placeholder="Enter your email" required>

<button>Send OTP</button>

</form>

</div>

</body>
</html>