<?php
session_start();
include "db.php";
require "vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['temp_user'])) {
    header("Location: register.php");
    exit;
}

$data = $_SESSION['temp_user'];

$otp = rand(100000, 999999);

// update OTP in session
$_SESSION['temp_user']['otp'] = password_hash($otp, PASSWORD_DEFAULT);
$_SESSION['temp_user']['time'] = time();

// send again
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
    $mail->addAddress($data['email']);

    $mail->isHTML(true);
    $mail->Subject = "Resend OTP Code";
    $mail->Body = "
        <h2>LOZ PC STORE Verification</h2>
        <p>Your new OTP is:</p>
        <h1>$otp</h1>
    ";

    $mail->send();

    $_SESSION['message'] = "OTP resent successfully!";
    header("Location: verify.php");
    exit;

} catch (Exception $e) {
    echo "Failed to resend OTP.<br>";
    echo $mail->ErrorInfo;
}