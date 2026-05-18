<?php
session_start();
include "db.php";

if (
    !isset($_SESSION['verified_reset']) ||
    !isset($_SESSION['reset_user_id'])
){
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $pass1 = $_POST['password'];
    $pass2 = $_POST['confirm_password'];

    if ($pass1 != $pass2) {

        $message = "Passwords do not match!";

    } else {

        $hashed = password_hash($pass1, PASSWORD_DEFAULT);

        $user_id = $_SESSION['reset_user_id'];

        mysqli_query($conn,"
            UPDATE users
            SET password='$hashed',
                otp_code=NULL,
                otp_expiry=NULL
            WHERE user_id=$user_id
        ");

        unset($_SESSION['verified_reset']);
        unset($_SESSION['reset_user_id']);
        unset($_SESSION['otp_type']);

        header("Location: login.php?reset=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Reset Password</title>

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

<h2>Reset Password</h2>

<?php if($message!=""): ?>
<div class="msg"><?= $message ?></div>
<?php endif; ?>

<form method="post">

<input type="password" name="password" placeholder="New Password" required>

<input type="password" name="confirm_password" placeholder="Confirm Password" required>

<button>Reset Password</button>

</form>

</div>

</body>
</html>