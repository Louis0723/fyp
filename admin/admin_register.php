<?php
include "../db.php";
session_start();

$message = "";

if(isset($_POST['register'])){
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];

    // check duplicate
    $check = $conn->prepare("SELECT * FROM admins WHERE username=?");
    $check->bind_param("s",$username);
    $check->execute();
    $result = $check->get_result();

    if($result->num_rows > 0){
        $message = "❌ Username already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO admins (username,password,email) VALUES (?,?,?)");
        $stmt->bind_param("sss",$username,$password,$email);

        if($stmt->execute()){
            $message = "✅ Register success! You can login now.";
        } else {
            $message = "❌ Error!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Register</title>

<style>
body{
    font-family:Poppins;
    background:linear-gradient(135deg,#e0f7ff,#c2e9fb);
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.card{
    background:white;
    padding:30px;
    border-radius:15px;
    width:350px;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
}

input{
    width:100%;
    padding:10px;
    margin:10px 0;
}

button{
    width:100%;
    padding:10px;
    background:#0072ff;
    color:white;
    border:none;
}

.message{
    color:red;
}
</style>
</head>

<body>

<div class="card">
<h2>Register Admin</h2>

<p class="message"><?= $message ?></p>

<form method="POST">
<input name="username" placeholder="Username" required>
<input name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>

<button name="register">Register</button>
</form>

</div>

</body>
</html>