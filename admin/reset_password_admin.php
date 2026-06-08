<?php
include "../db.php";
session_start();

$message = "";
$valid_token = false;
$token = "";

if(isset($_GET['token']) && !empty($_GET['token'])){

    $token = trim($_GET['token']);

    $stmt = $conn->prepare("
        SELECT admin_id
        FROM admins
        WHERE reset_token=?
        LIMIT 1
    ");

    $stmt->bind_param("s", $token);
    $stmt->execute();

    $result = $stmt->get_result();

    if($result->num_rows > 0){

        $valid_token = true;

    }else{

        $message = "<div class='error'>Invalid or expired reset link.</div>";
    }

}else{

    $message = "<div class='error'>Reset token missing.</div>";
}

if(isset($_POST['update_password'])){

    $token = trim($_POST['token']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if($password != $confirm_password){

        $message = "<div class='error'>Passwords do not match.</div>";
        $valid_token = true;

    }elseif(strlen($password) < 6){

        $message = "<div class='error'>Password must be at least 6 characters.</div>";
        $valid_token = true;

    }else{

        $stmt = $conn->prepare("
            SELECT admin_id
            FROM admins
            WHERE reset_token=?
            LIMIT 1
        ");

        $stmt->bind_param("s", $token);
        $stmt->execute();

        $result = $stmt->get_result();

        if($result->num_rows > 0){

            $admin = $result->fetch_assoc();

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $update = $conn->prepare("
                UPDATE admins
                SET password=?,
                    reset_token=NULL
                WHERE admin_id=?
            ");

            $update->bind_param(
                "si",
                $hashed_password,
                $admin['admin_id']
            );

            if($update->execute()){

                $message = "
                <div class='success'>
                    Password updated successfully!
                    <br><br>
                    <a href='admin_login.php' class='login-btn'>
                        Login Now
                    </a>
                </div>";

                $valid_token = false;

            }else{

                $message = "<div class='error'>Failed to update password.</div>";
                $valid_token = true;
            }

        }else{

            $message = "<div class='error'>Invalid token.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Reset Password</title>

<style>

body{
    margin:0;
    font-family:'Segoe UI',sans-serif;
    background:#eef3f9;
    overflow-x:hidden;
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
}

.reset-card{

    width:450px;
    max-width:90%;

    background:#fff;

    padding:40px;

    border-radius:20px;

    box-shadow:0 10px 30px rgba(0,0,0,.15);
}

.reset-title{

    text-align:center;

    color:#07122b;

    font-size:28px;

    font-weight:700;

    margin-bottom:25px;
}

.form-group{
    margin-bottom:18px;
}

.form-group label{

    display:block;

    margin-bottom:8px;

    font-weight:600;

    color:#334155;
}

.form-group input{

    width:100%;

    padding:14px;

    border:1px solid #dbeafe;

    border-radius:12px;

    outline:none;

    box-sizing:border-box;

    font-size:15px;
}

.form-group input:focus{
    border-color:#3b82f6;
}

.btn{

    width:100%;

    padding:14px;

    border:none;

    border-radius:12px;

    background:#3b82f6;

    color:#fff;

    font-size:16px;

    font-weight:600;

    cursor:pointer;

    transition:.3s;
}

.btn:hover{
    background:#2563eb;
}

.error{

    background:#fee2e2;

    color:#dc2626;

    padding:14px;

    border-radius:12px;

    margin-bottom:20px;

    text-align:center;

    font-weight:600;
}

.success{

    background:#dcfce7;

    color:#15803d;

    padding:14px;

    border-radius:12px;

    margin-bottom:20px;

    text-align:center;

    font-weight:600;
}

.login-btn{

    display:inline-block;

    margin-top:10px;

    padding:12px 20px;

    background:#3b82f6;

    color:#fff;

    text-decoration:none;

    border-radius:10px;
}

.login-btn:hover{
    background:#2563eb;
}

.back{

    display:block;

    text-align:center;

    margin-top:15px;

    color:#3b82f6;

    text-decoration:none;

    font-weight:600;
}

.back:hover{
    text-decoration:underline;
}

</style>
</head>

<body>

<div class="reset-card">

    <div class="reset-title">
        Reset Password
    </div>

    <?= $message ?>

    <?php if($valid_token): ?>

    <form method="POST">

        <input
            type="hidden"
            name="token"
            value="<?= htmlspecialchars($token) ?>"
        >

        <div class="form-group">
            <label>New Password</label>
            <input
                type="password"
                name="password"
                placeholder="Enter New Password"
                required
            >
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <input
                type="password"
                name="confirm_password"
                placeholder="Confirm New Password"
                required
            >
        </div>

        <button
            type="submit"
            name="update_password"
            class="btn"
        >
            Update Password
        </button>

    </form>

    <?php endif; ?>

    <a href="admin_login.php" class="back">
        ← Back To Login
    </a>

</div>

</body>
</html>