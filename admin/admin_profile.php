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

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* 不影响 header */
.main{
    margin-left:240px;
    margin-top:80px;
    padding:30px;
    background: linear-gradient(135deg, #eef2ff, #f8fbff);
    min-height:100vh;
}

/* ✅ 左对齐关键 */
.main .profile-card{
    max-width:600px;
    margin:0; /* ❌ 去掉 auto */
    background: rgba(255,255,255,0.9);
    border-radius:20px;
    padding:40px;
    box-shadow:0 20px 50px rgba(0,0,0,0.15);
    position:relative;
    transition:0.3s;
}

/* hover */
.main .profile-card:hover{
    transform:translateY(-8px);
}

/* 顶部渐变 */
.main .profile-card::before{
    content:"";
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:6px;
    background: linear-gradient(135deg, #4facfe, #00c6ff);
}

/* 标题 */
.main h2{
    text-align:left; /* ✅ 改左 */
    font-weight:700;
    color:#2c3e50;
    margin-bottom:30px;
}

/* 行 */
.main .profile-row{
    display:flex;
    justify-content:space-between;
    padding:12px 0;
    border-bottom:1px solid #eee;
}

.main .label{
    font-weight:600;
    color:#555;
}

.main .value{
    color:#333;
}

/* 输入 */
.main input{
    border-radius:12px;
    padding:12px;
    border:1px solid #ddd;
    margin-bottom:15px;
    transition:0.25s;
}

.main input:focus{
    border-color:#4facfe;
    box-shadow:0 0 8px rgba(79,172,254,0.3);
    outline:none;
}

/* 按钮 */
.main .btn-main{
    background: linear-gradient(135deg, #4facfe, #00c6ff);
    border:none;
    color:white;
    padding:12px 25px;
    border-radius:25px;
    font-weight:600;
    transition:0.3s;
}

.main .btn-main:hover{
    transform:translateY(-2px);
    box-shadow:0 10px 25px rgba(0,198,255,0.4);
}

.main .btn-cancel{
    background:#ccc;
    color:#333;
}

.main .btn-cancel:hover{
    background:#999;
}

/* ✅ 按钮靠左 */
.main .button-group{
    display:flex;
    justify-content:flex-start;
    gap:15px;
    margin-top:20px;
}

/* alert */
.main .alert{
    border-radius:12px;
}
</style>
</head>

<body>

<?php include "admin_header.php"; ?>
<?php include "admin_sidebar.php"; ?>

<div class="main">

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
        <button id="edit-btn" class="btn-main">Edit Profile</button>
    </div>
</div>

<!-- EDIT -->
<div id="edit-mode" style="display:none;">
    <form method="POST">
        <input value="<?= htmlspecialchars($admin['username']) ?>" disabled class="form-control">

        <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" class="form-control" required>

        <div class="button-group">
            <button name="update" class="btn-main">Save</button>
            <button type="button" id="cancel-btn" class="btn-main btn-cancel">Cancel</button>
        </div>
    </form>
</div>

</div>
</div>

<script>
document.getElementById('edit-btn').onclick = function(){
    document.getElementById('view-mode').style.display = 'none';
    document.getElementById('edit-mode').style.display = 'block';
};

document.getElementById('cancel-btn').onclick = function(){
    document.getElementById('edit-mode').style.display = 'none';
    document.getElementById('view-mode').style.display = 'block';
};
</script>

</body>
</html>