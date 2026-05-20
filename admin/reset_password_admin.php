<?php
include "../db.php";

$message = "";

if(!isset($_GET['token'])){
    die("Invalid token");
}

$token = trim($_GET['token']);

// CHECK TOKEN
$stmt = $conn->prepare("
SELECT * FROM admins
WHERE reset_token=?
LIMIT 1
");

$stmt->bind_param("s", $token);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows == 0){
    die("Invalid or expired token.");
}

$admin = $result->fetch_assoc();

// UPDATE PASSWORD
if(isset($_POST['update'])){

    $newpass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        UPDATE admins
        SET password=?, reset_token=NULL
        WHERE admin_id=?
    ");

    $stmt->bind_param("si", $newpass, $admin['admin_id']);
    $stmt->execute();

    header("Location: admin_login.php?reset=success");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Reset Admin Password</title>

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
background:white;
box-shadow:0 10px 30px rgba(0,0,0,0.15);

}

h2{
text-align:center;
margin-bottom:20px;
color:#0072ff;
}

input{

width:100%;
padding:14px;
margin-bottom:15px;
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

</style>
</head>

<body>

<div class="card">

<h2>Reset Password</h2>

<?php if($message!=""): ?>
<div class="msg"><?= $message ?></div>
<?php endif; ?>

<form method="POST">

<input
type="password"
name="password"
placeholder="New Password"
required
>

<button name="update">
Update Password
</button>

</form>

</div>

</body>
</html>