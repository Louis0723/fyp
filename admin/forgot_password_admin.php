<?php
include "../db.php";
session_start();
require "../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";

if(isset($_POST['reset'])){

    $email = trim($_POST['email']);

    // 1. Find admin
    $stmt = $conn->prepare("
        SELECT * FROM admins
        WHERE email=?
        LIMIT 1
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){

        $admin = $result->fetch_assoc();

        // 2. Generate token
        $token = bin2hex(random_bytes(16));

// 4. Save token to DB (DEBUG VERSION)
$stmt = $conn->prepare("
    UPDATE admins
    SET reset_token=?
    WHERE admin_id=?
");

$stmt->bind_param("si", $token, $admin['admin_id']);

if(!$stmt->execute()){
    die("UPDATE FAILED: " . $stmt->error);
}

       $reset_link = "http://localhost/fyp/fyp/admin/reset_password_admin.php?token=" . $token;
       
        $mail = new PHPMailer(true);

        try{

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ziyiyap2006@gmail.com';
            $mail->Password = 'dnuaffkldwjxlqhh';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('ziyiyap2006@gmail.com', 'LOZ PC STORE ADMIN');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Admin Password Reset";
            $mail->Body = "<a href='$reset_link'>Reset Password</a>";

            $mail->send();

            $message = "<span style='color:lime;'>Reset link sent!</span>";

        }catch(Exception $e){
            $message = "<span style='color:red;'>Mail Error</span>";
        }

    } else {
        $message = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Forgot Password - Admin</title>

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Poppins;
}

body{
height:100vh;
display:flex;
justify-content:center;
align-items:center;
background:linear-gradient(135deg,#e0f7ff,#c2e9fb);
}

.card{

width:400px;
padding:40px;
border-radius:20px;
background:rgba(255,255,255,0.8);
backdrop-filter:blur(15px);
box-shadow:0 10px 30px rgba(0,0,0,0.15);

}

h2{
text-align:center;
margin-bottom:25px;
color:#0072ff;
}

input{

width:100%;
padding:14px;
margin-bottom:18px;
border:none;
border-radius:12px;
background:#f1f5f9;
outline:none;

}

button{

width:100%;
padding:14px;
border:none;
border-radius:12px;
background:linear-gradient(90deg,#00c6ff,#0072ff);
color:white;
font-size:16px;
font-weight:600;
cursor:pointer;

}

.msg{
text-align:center;
margin-bottom:15px;
font-weight:600;
}

.back{

display:block;
margin-top:15px;
text-align:center;
text-decoration:none;
color:#0072ff;
font-weight:600;

}

</style>
</head>

<body>

<div class="card">

<h2>Forgot Password</h2>

<?php if($message!=""): ?>
<div class="msg"><?= $message ?></div>
<?php endif; ?>

<form method="POST">

<input
type="email"
name="email"
placeholder="Enter Admin Email"
required
>

<button name="reset">
Send Reset Link
</button>

</form>

<a href="admin_login.php" class="back">
← Back to Login
</a>

</div>

</body>
</html>