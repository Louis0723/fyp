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
UPLOAD / REMOVE AVATAR
========================================= */

$success = "";
$error   = "";

/* UPLOAD AVATAR */
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

                /* DELETE OLD AVATAR */
                if($adminAvatar != "default-avatar.png"){

                    $oldPath = "../uploads/admins/" . $adminAvatar;

                    if(file_exists($oldPath)){
                        unlink($oldPath);
                    }

                }

                $conn->query("
                    UPDATE admins
                    SET avatar='$newName'
                    WHERE username='$username'
                ");

                $adminAvatar = $newName;

                $success = "Avatar updated successfully.";

            }else{

                $error = "Failed to upload image.";

            }

        }else{

            $error = "Only JPG, JPEG, PNG, WEBP allowed.";

        }

    }else{

        $error = "Please choose an image.";

    }

}

/* REMOVE AVATAR */
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
        WHERE username='$username'
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

<title>Admin Detail</title>

<link rel="stylesheet" href="style.css?v=999">

<script src="https://unpkg.com/lucide@latest"></script>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}

body{
    background:#eef3f9;
    overflow-x:hidden;
}

/* =========================================
MAIN CONTENT
========================================= */

.main-content{
    margin-left:260px;
    padding:0 24px 24px;
    min-height:100vh;
    margin-top:-18px;
}

/* =========================================
HEADER SPACING
========================================= */

.admin-header{
    margin-bottom:6px !important;
}

/* =========================================
PAGE HEADER
========================================= */

.page-header{
    margin-top:4px;
    margin-bottom:18px;
}

.page-header h1{
    font-size:42px;
    font-weight:800;
    color:#0f172a;
}

.page-header p{
    margin-top:8px;
    color:#64748b;
    font-size:16px;
}

/* =========================================
PROFILE CONTAINER
========================================= */

.profile-container{
    width:100%;
    max-width:920px;
    margin:0 auto;
}

/* =========================================
PROFILE CARD
========================================= */

.profile-card{
    background:#fff;
    border-radius:26px;
    padding:30px;
    border:1px solid #e5e7eb;
    box-shadow:0 4px 16px rgba(0,0,0,0.04);
}

/* =========================================
TOP SECTION
========================================= */

.profile-top{
    display:flex;
    align-items:center;
    gap:50px;
    flex-wrap:wrap;
    padding-bottom:26px;
    border-bottom:1px solid #edf2f7;
}

/* =========================================
AVATAR
========================================= */

.avatar-wrapper{
    display:flex;
    flex-direction:column;
    align-items:center;
}

.avatar-image{
    width:140px;
    height:140px;
    border-radius:50%;
    object-fit:cover;
    border:5px solid #2563eb;
    background:#fff;
}

/* =========================================
UPLOAD FORM
========================================= */

.upload-form{
    margin-top:16px;
    text-align:center;
}

.upload-form input[type="file"]{
    margin-bottom:12px;
    font-size:13px;
}

.upload-btn{
    background:#2563eb;
    color:#fff;
    border:none;
    padding:12px 20px;
    border-radius:12px;
    cursor:pointer;
    font-weight:700;
    font-size:14px;
    transition:.2s;
}

.upload-btn:hover{
    background:#1d4ed8;
}

.remove-btn{
    background:#ef4444;
    color:#fff;
    border:none;
    padding:12px 20px;
    border-radius:12px;
    cursor:pointer;
    font-weight:700;
    font-size:14px;
    margin-top:10px;
    transition:.2s;
}

.remove-btn:hover{
    background:#dc2626;
}
.edit-profile-btn{
    margin-top:12px;
    background:#0f172a;
    color:#fff;
    text-decoration:none;
    padding:12px 20px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    font-weight:700;
    transition:.2s;
}

.edit-profile-btn:hover{
    background:#1e293b;
}

.edit-profile-btn i{
    width:18px;
    height:18px;
}
/* =========================================
PROFILE INFO
========================================= */

.profile-info{
    flex:1;
}

.profile-info h2{
    font-size:42px;
    font-weight:800;
    color:#0f172a;
    margin-bottom:18px;
}

.profile-info p{
    display:flex;
    align-items:center;
    gap:12px;
    font-size:18px;
    color:#64748b;
    margin-bottom:14px;
}

.profile-info i{
    width:20px;
    height:20px;
}

/* =========================================
GRID
========================================= */

.profile-grid{
    margin-top:28px;
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:18px;
}

/* =========================================
INFO BOX
========================================= */

.info-box{
    background:#f8fafc;
    border:1px solid #e5e7eb;
    border-radius:20px;
    padding:24px;
}

.info-box h4{
    color:#64748b;
    font-size:13px;
    text-transform:uppercase;
    letter-spacing:1px;
    margin-bottom:14px;
    font-weight:700;
}

.info-box p{
    color:#0f172a;
    font-size:22px;
    font-weight:800;
    word-break:break-word;
}

/* =========================================
SUCCESS / ERROR
========================================= */

.success-msg{
    background:#dcfce7;
    color:#166534;
    padding:14px;
    border-radius:12px;
    margin-bottom:18px;
    font-weight:700;
}

.error-msg{
    background:#fee2e2;
    color:#991b1b;
    padding:14px;
    border-radius:12px;
    margin-bottom:18px;
    font-weight:700;
}

/* =========================================
RESPONSIVE
========================================= */

@media(max-width:1100px){

    .main-content{
        margin-left:0;
        padding:0 16px 16px;
    }

    .profile-grid{
        grid-template-columns:1fr;
    }

    .profile-top{
        flex-direction:column;
        align-items:flex-start;
        gap:30px;
    }

}

</style>

</head>

<body>
<?php
if(isset($_SESSION['role']) &&
$_SESSION['role']=="super_admin"){

    include "sadmin_sidebar.php";

}else{

    include "admin_sidebar.php";
}
?>

<div class="main-content">

    <?php include "admin_header.php"; ?>

    <!-- PAGE HEADER -->
    <div class="page-header">

        <h1>Admin Detail</h1>

        <p>
            View and manage your administrator profile information.
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

            <!-- PROFILE TOP -->
            <div class="profile-top">

                <!-- AVATAR -->
                <div class="avatar-wrapper">

                    <img src="../uploads/admins/<?= $adminAvatar ?>"
                         class="avatar-image">

                    <form method="POST"
                          enctype="multipart/form-data"
                          class="upload-form">

                        <input type="file"
                               name="avatar">

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
          <br>

                 <a href="edit_profile.php" class="edit-profile-btn">

                   <i data-lucide="square-pen"></i>

                Edit Profile

            </a>

                    </form>

                </div>

                <!-- PROFILE INFO -->
                <div class="profile-info">

                    <h2>
                        <?= htmlspecialchars($adminName) ?>
                    </h2>

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

            <!-- DETAILS -->
            <div class="profile-grid">

                <div class="info-box">

                    <h4>Username</h4>

                    <p><?= htmlspecialchars($adminName) ?></p>

                </div>

                <div class="info-box">

                    <h4>Email Address</h4>

                    <p><?= htmlspecialchars($adminEmail) ?></p>

                </div>

                <div class="info-box">

                    <h4>Phone Number</h4>

                    <p><?= htmlspecialchars($adminPhone) ?></p>

                </div>

                <div class="info-box">

                    <h4>Role</h4>

                    <p><?= htmlspecialchars($adminRole) ?></p>

                </div>

                <div class="info-box">

                    <h4>Status</h4>

                    <p><?= htmlspecialchars($adminStatus) ?></p>

                </div>

                <div class="info-box">

                    <h4>Created At</h4>

                    <p><?= date("d M Y", strtotime($adminCreated)) ?></p>

                </div>

            </div>

        </div>

    </div>

</div>

<script>
lucide.createIcons();
</script>

</body>
</html>