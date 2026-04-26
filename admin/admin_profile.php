<?php
include "../db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

$username = $_SESSION['admin'];

$success_msg = "";
$error_msg = "";

// GET DATA
$stmt = $conn->prepare("SELECT username, email FROM admins WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

// UPDATE
if(isset($_POST['update'])){
    $email = trim($_POST['email']);

    if($email == ""){
        $error_msg = "Email cannot be empty!";
    }else{
        $stmt = $conn->prepare("UPDATE admins SET email=? WHERE username=?");
        $stmt->bind_param("ss", $email, $username);

        if($stmt->execute()){
            $success_msg = "Profile updated successfully!";
            $admin['email'] = $email;
        }else{
            $error_msg = "Update failed!";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Profile</title>

<link rel="stylesheet" href="style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest"></script>

<style>

/* ✅ FIXED LAYOUT */
.main-layout{
    display:flex;
}

.content-area{
    margin-left:240px;
    margin-top:90px;
    padding:30px;
    width:100%;
}

/* collapse support */
.sidebar.collapsed ~ .main-layout .content-area{
    margin-left:70px;
}

/* CARD */
.profile-card{
    max-width:600px;
    background: rgba(255,255,255,0.95);
    border-radius:20px;
    padding:40px;
    box-shadow:0 20px 50px rgba(0,0,0,0.15);
}

/* header line */
.profile-card::before{
    content:"";
    display:block;
    height:6px;
    border-radius:20px 20px 0 0;
    background: linear-gradient(135deg, #4facfe, #00c6ff);
    margin:-40px -40px 20px -40px;
}

/* title */
h2{
    font-weight:700;
    color:#2c3e50;
    margin-bottom:30px;
}

/* rows */
.profile-row{
    display:flex;
    justify-content:space-between;
    padding:12px 0;
    border-bottom:1px solid #eee;
}

.label{font-weight:600;}
.value{color:#333;}

/* input */
input{
    border-radius:10px;
    padding:10px;
    margin-bottom:10px;
}

/* buttons */
.btn-main{
    background:#3b82f6;
    color:#fff;
    border:none;
    padding:10px 20px;
    border-radius:10px;
}

.btn-cancel{
    background:#ccc;
    color:#333;
}

/* buttons group */
.button-group{
    display:flex;
    gap:10px;
    margin-top:15px;
}

</style>
</head>

<body>

<?php include "admin_header.php"; ?>
<?php include "admin_sidebar.php"; ?>

<div class="main-layout">

<div class="content-area">

<div class="profile-card">

<h2>👤 Admin Profile</h2>

<?php if($success_msg): ?>
<div class="alert alert-success"><?= $success_msg ?></div>
<?php endif; ?>

<?php if($error_msg): ?>
<div class="alert alert-danger"><?= $error_msg ?></div>
<?php endif; ?>

<!-- VIEW -->
<div id="view-mode">
    <div class="profile-row">
        <div class="label">Username</div>
        <div class="value"><?= htmlspecialchars($admin['username']) ?></div>
    </div>

    <div class="profile-row">
        <div class="label">Email</div>
        <div class="value"><?= htmlspecialchars($admin['email']) ?></div>
    </div>

    <div class="button-group">
        <button id="edit-btn" class="btn-main">Edit</button>
    </div>
</div>

<!-- EDIT -->
<div id="edit-mode" style="display:none;">
    <form method="POST">
        <input value="<?= htmlspecialchars($admin['username']) ?>" disabled class="form-control">

        <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" class="form-control">

        <div class="button-group">
            <button name="update" class="btn-main">Save</button>
            <button type="button" id="cancel-btn" class="btn-main btn-cancel">Cancel</button>
        </div>
    </form>
</div>

</div>

</div>
</div>

<script src="admin.js"></script>
<script>
lucide.createIcons();

document.getElementById('edit-btn').onclick = () => {
    document.getElementById('view-mode').style.display = 'none';
    document.getElementById('edit-mode').style.display = 'block';
};

document.getElementById('cancel-btn').onclick = () => {
    document.getElementById('edit-mode').style.display = 'none';
    document.getElementById('view-mode').style.display = 'block';
};
</script>

</body>
</html>