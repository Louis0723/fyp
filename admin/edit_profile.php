<?php
session_start();
include "../db.php";

/* =========================================
GET ADMIN INFO
========================================= */

$adminName     = "Admin";
$adminEmail    = "";
$adminPhone    = "";
$adminRole     = "";
$adminStatus   = "";
$adminCreated  = "";
$adminAvatar   = "default-avatar.png";

if(isset($_SESSION['admin'])){

    $username = $_SESSION['admin'];

    $query = $conn->query("
        SELECT *
        FROM admins
        WHERE username = '$username'
        LIMIT 1
    ");

    if($query && $query->num_rows > 0){

        $admin = $query->fetch_assoc();

        $adminID       = $admin['admin_id'];
        $adminName     = $admin['username'];
        $adminEmail    = $admin['email'];
        $adminRole     = $admin['role'];
        $adminStatus   = $admin['status'];
        $adminCreated  = $admin['created_at'];

        if(isset($admin['phone'])){
            $adminPhone = $admin['phone'];
        }

        if(isset($admin['avatar']) && !empty($admin['avatar'])){
            $adminAvatar = $admin['avatar'];
        }

    }

}

/* =========================================
UPDATE PROFILE
========================================= */

$success = "";
$error = "";

if(isset($_POST['save_profile'])){

    $newUsername = trim($_POST['username']);
    $newEmail = $adminEmail;
    $newPhone    = trim($_POST['phone']);
    $newPassword = trim($_POST['password']);

   if(empty($newUsername)){
    $error = "Username is required.";
}
else{

        if(!empty($newPassword)){

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $update = $conn->query("
                UPDATE admins
            SET
              username='$newUsername',
              phone='$newPhone',
            password='$hashedPassword'
            WHERE admin_id='$adminID'
            ");

        }else{

            $update = $conn->query("
              UPDATE admins
            SET
              username='$newUsername',
              phone='$newPhone'
              WHERE admin_id='$adminID'
            ");

        }

        if($update){

            $_SESSION['admin'] = $newUsername;

            $adminName  = $newUsername;
            $adminPhone = $newPhone;

            $success = "Profile updated successfully.";

        }else{

            $error = "Failed to update profile.";

        }

    }

}

/* =========================================
UPLOAD AVATAR
========================================= */

if(isset($_POST['upload_avatar'])){

    if(isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0){

        $allowed = ['jpg','jpeg','png','webp'];

        $fileName = $_FILES['avatar']['name'];
        $tmpName  = $_FILES['avatar']['tmp_name'];

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if(in_array($ext, $allowed)){

            $newName = time() . "_" . rand(1000,9999) . "." . $ext;

            if(!file_exists("../uploads/admins")){
                mkdir("../uploads/admins",0777,true);
            }

            $uploadPath = "../uploads/admins/" . $newName;

            if(move_uploaded_file($tmpName, $uploadPath)){

                if($adminAvatar != "default-avatar.png"){

                    $oldPath = "../uploads/admins/" . $adminAvatar;

                    if(file_exists($oldPath)){
                        unlink($oldPath);
                    }

                }

                $conn->query("
                    UPDATE admins
                    SET avatar='$newName'
                    WHERE admin_id='$adminID'
                ");

                $adminAvatar = $newName;

                $success = "Avatar updated successfully.";

            }

        }else{

            $error = "Only JPG, PNG, JPEG, WEBP allowed.";

        }

    }

}

/* =========================================
REMOVE AVATAR
========================================= */

if(isset($_POST['remove_avatar'])){

    if($adminAvatar != "default-avatar.png"){

        $oldPath = "../uploads/admins/" . $adminAvatar;

        if(file_exists($oldPath)){
            unlink($oldPath);
        }

    }

    $conn->query("
        UPDATE admins
        SET avatar='default-avatar.png'
        WHERE admin_id='$adminID'
    ");

    $adminAvatar = "default-avatar.png";

    $success = "Avatar removed successfully.";

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Profile</title>

<link rel="stylesheet" href="style.css?v=999">

<script src="https://unpkg.com/lucide@latest"></script>

<style>

body{
    background:#eef3f9;
    overflow-x:hidden;
    font-family:'Segoe UI',sans-serif;
}

.main-content{
    margin-left:260px;
    padding:0 24px 24px;
    margin-top:-18px;
}

.page-header{
    margin-top:4px;
    margin-bottom:20px;
}

.page-header h1{
    font-size:42px;
    font-weight:800;
    color:#0f172a;
}

.page-header p{
    margin-top:8px;
    color:#64748b;
}

.profile-container{
    max-width:920px;
    margin:auto;
}

.profile-card{
    background:#fff;
    border-radius:24px;
    padding:30px;
    border:1px solid #e5e7eb;
}

.profile-top{
    display:flex;
    gap:50px;
    flex-wrap:wrap;
    align-items:center;
    border-bottom:1px solid #edf2f7;
    padding-bottom:30px;
}

.avatar-wrapper{
    display:flex;
    flex-direction:column;
    align-items:center;
}

.avatar-image{
    width:150px;
    height:150px;
    border-radius:50%;
    object-fit:cover;
    border:5px solid #2563eb;
}

.upload-form{
    margin-top:18px;
    text-align:center;
}

.upload-btn,
.remove-btn,
.save-btn{
    border:none;
    border-radius:12px;
    padding:12px 22px;
    color:#fff;
    font-weight:700;
    cursor:pointer;
    margin-top:12px;
}

.upload-btn{
    background:#2563eb;
}

.remove-btn{
    background:#ef4444;
}

.save-btn{
    background:#0f172a;
    width:100%;
    margin-top:20px;
}

.profile-info{
    flex:1;
}

.profile-info h2{
    font-size:42px;
    font-weight:800;
    margin-bottom:18px;
    color:#0f172a;
}

.profile-info p{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:14px;
    color:#64748b;
    font-size:18px;
}

.form-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:20px;
    margin-top:30px;
}

.form-group{
    display:flex;
    flex-direction:column;
}

.form-group label{
    margin-bottom:10px;
    font-weight:700;
    color:#475569;
}

.form-group input{
    height:55px;
    border:1px solid #dbe2ea;
    border-radius:14px;
    padding:0 16px;
    font-size:16px;
    outline:none;
}

.readonly{
    background:#f1f5f9;
    color:#64748b;
}

.success-msg{
    background:#dcfce7;
    color:#166534;
    padding:14px;
    border-radius:12px;
    margin-bottom:20px;
    font-weight:700;
}

.error-msg{
    background:#fee2e2;
    color:#991b1b;
    padding:14px;
    border-radius:12px;
    margin-bottom:20px;
    font-weight:700;
}

@media(max-width:1000px){

    .main-content{
        margin-left:0;
    }

    .form-grid{
        grid-template-columns:1fr;
    }

}

/* FIX HEADER AVATAR */

.admin-header .avatar-btn{
    width:42px !important;
    height:42px !important;
    min-width:42px !important;
    min-height:42px !important;
    border-radius:50% !important;
    overflow:hidden !important;
    padding:0 !important;
}

.admin-header .avatar-btn img{
    width:100% !important;
    height:100% !important;
    object-fit:cover !important;
    object-position:center !important;
    border-radius:50% !important;
    display:block !important;
}

/* DROPDOWN PROFILE AVATAR */

.admin-header .profile-avatar{
    width:52px !important;
    height:52px !important;
    border-radius:50% !important;
    overflow:hidden !important;
}

.admin-header .profile-avatar img{
    width:100% !important;
    height:100% !important;
    object-fit:cover !important;
    object-position:center !important;
    border-radius:50% !important;
    display:block !important;
}

</style>

</head>

<body>

<?php include "admin_sidebar.php"; ?>

<div class="main-content">

<?php include "admin_header.php"; ?>

<div class="page-header">

    <h1>Edit Profile</h1>

    <p>
        Update your administrator account information.
    </p>

</div>

<div class="profile-container">

    <div class="profile-card">

        <?php if($success != ""): ?>
            <div class="success-msg">
                <?= $success ?>
            </div>
        <?php endif; ?>

        <?php if($error != ""): ?>
            <div class="error-msg">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- TOP -->
        <div class="profile-top">

            <!-- AVATAR -->
            <div class="avatar-wrapper">

                <img src="../uploads/admins/<?= $adminAvatar ?>"
                     class="avatar-image">

                <form method="POST"
                      enctype="multipart/form-data"
                      class="upload-form">

                    <input type="file" name="avatar">

                    <br>

                    <button type="submit"
                            name="upload_avatar"
                            class="upload-btn">

                        Change Avatar

                    </button>

                    <br>

                    <button type="submit"
                            name="remove_avatar"
                            class="remove-btn">

                        Remove Avatar

                    </button>

                </form>

            </div>

            <!-- INFO -->
            <div class="profile-info">

                <h2><?= htmlspecialchars($adminName) ?></h2>

                <p>
                    <i data-lucide="mail"></i>
                    <?= htmlspecialchars($adminEmail) ?>
                </p>

                <p>
                    <i data-lucide="phone"></i>
                    <?= htmlspecialchars($adminPhone) ?>
                </p>

                <p>
                    <i data-lucide="shield"></i>
                    <?= htmlspecialchars($adminRole) ?>
                </p>

                <p>
                    <i data-lucide="badge-check"></i>
                    <?= htmlspecialchars($adminStatus) ?>
                </p>

            </div>

        </div>

        <!-- FORM -->
        <form method="POST">

            <div class="form-grid">

                <div class="form-group">

                    <label>Username</label>

                    <input type="text"
                           name="username"
                           value="<?= htmlspecialchars($adminName) ?>">

                </div>

              <div class="form-group">

    <label>Email Address</label>

    <input type="email"
           value="<?= htmlspecialchars($adminEmail) ?>"
           class="readonly"
           readonly>

</div>

                <div class="form-group">

                    <label>Phone Number</label>

                    <input type="text"
                           name="phone"
                           value="<?= htmlspecialchars($adminPhone) ?>">

                </div>

                <div class="form-group">

                    <label>New Password</label>

                    <input type="password"
                           name="password"
                           placeholder="Leave blank to keep current password">

                </div>

                <div class="form-group">

                    <label>Role</label>

                    <input type="text"
                           value="<?= htmlspecialchars($adminRole) ?>"
                           class="readonly"
                           readonly>

                </div>

                <div class="form-group">

                    <label>Status</label>

                    <input type="text"
                           value="<?= htmlspecialchars($adminStatus) ?>"
                           class="readonly"
                           readonly>

                </div>

            </div>

            <button type="submit"
                    name="save_profile"
                    class="save-btn">

                Save Changes

            </button>

        </form>

    </div>

</div>

</div>

<script>
lucide.createIcons();
</script>

</body>
</html>