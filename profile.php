<?php
session_start();
include "db.php";
require "vendor/autoload.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['user_id'];

$res = mysqli_query($conn, "SELECT * FROM users WHERE user_id=$user_id");
$user = mysqli_fetch_assoc($res);

if (isset($_POST['update'])) {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);

    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!empty($new_password)) {

        if (strlen($new_password) < 8) {
            echo "<script>alert('❌ Password must be at least 8 characters');</script>";
            exit;
        }

        if ($new_password !== $confirm_password) {
            echo "<script>alert('❌ Passwords do not match!');</script>";
            exit;
        }

        if ($user['last_password_change'] != NULL) {
            $last = strtotime($user['last_password_change']);
            $six_months = strtotime("+6 months", $last);

            if (time() < $six_months) {
                echo "<script>alert('❌ You can only change password once every 6 months');</script>";
                exit;
            }
        }

        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        mysqli_query($conn, "
            UPDATE users 
            SET otp_code='$otp', otp_expiry='$expiry'
            WHERE user_id=$user_id
        ");

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
    $mail->addAddress($user['email']);

    $mail->isHTML(true);
    $mail->Subject = "Your OTP Code";
    $mail->Body = "<h2>Your OTP is</h2><h1>$otp</h1>";

    $mail->send();

} catch (Exception $e) {
    die("Email failed: " . $mail->ErrorInfo);
}

        $_SESSION['otp_type'] = 'password_change';
        $_SESSION['temp_user_id'] = $user_id;
        $_SESSION['temp_new_password'] = $new_password;

        header("Location: verify.php");
        exit;
    }

    mysqli_query($conn, "
        UPDATE users 
        SET name='$name', email='$email', address='$address', phone='$phone'
        WHERE user_id=$user_id
    ");

    header("Location: profile.php?updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Profile</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
body{
    background: linear-gradient(135deg,#0f0c29,#302b63,#24243e);
    color:white;
    font-family:'Poppins',sans-serif;
}

.container{
    max-width:500px;
    margin:100px auto;
    padding:30px;
    background: rgba(255,255,255,0.05);
    border-radius:20px;
    backdrop-filter: blur(15px);
    box-shadow:0 10px 25px rgba(0,255,255,0.3);
}

h2{
    text-align:center;
    margin-bottom:25px;
    color:#00f0ff;
}

input, textarea{
    width:100%;
    padding:10px;
    margin-bottom:15px;
    border-radius:10px;
    border:none;
    outline:none;
}

button{
    width:100%;
    padding:12px;
    border:none;
    border-radius:10px;
    background: linear-gradient(90deg,#00f0ff,#ff00ff);
    color:white;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    transform:scale(1.05);
}

.back{
    display:block;
    text-align:center;
    margin-top:20px;
    color:#00f0ff;
    text-decoration:none;
}
</style>
</head>

<body>

<div class="container">

<h2>👤 My Profile</h2>

<form method="post">

<input name="name" placeholder="Name" value="<?= $user['name'] ?>" required>

<input name="email" placeholder="Email" value="<?= $user['email'] ?>" required>

<input type="password" name="password" placeholder="New Password">

<input type="password" name="confirm_password" placeholder="Confirm New Password">

<textarea name="address" placeholder="Address" required><?= $user['address'] ?></textarea>

<input name="phone" placeholder="Phone Number" value="<?= $user['phone'] ?>" required>

<button name="update">Update Profile</button>

</form>

<a href="product.php" class="back">⬅ Back to Products</a>

</div>

</body>
</html>