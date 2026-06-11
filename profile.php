<?php
session_start();
include "db.php";
require "vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)($_SESSION['user']['user_id'] ?? 0);
if ($user_id <= 0) {
    header("Location: login.php");
    exit;
}

function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function profileUploadDir(): string
{
    return __DIR__ . '/uploads/profile/';
}

function profilePublicDir(): string
{
    return 'uploads/profile/';
}

function getProfilePhotoUrl(int $userId): string
{
    $dir = profileUploadDir();
    $files = glob($dir . 'profile_' . $userId . '.*');
    if (!empty($files)) {
        return profilePublicDir() . basename($files[0]);
    }
    return '';
}

function deleteProfilePhotos(int $userId): void
{
    $dir = profileUploadDir();
    foreach (glob($dir . 'profile_' . $userId . '.*') as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
}

function isValidImageExtension(string $ext): bool
{
    return in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
}

$res = mysqli_query($conn, "SELECT * FROM users WHERE user_id = $user_id LIMIT 1");
$user = $res ? mysqli_fetch_assoc($res) : null;

if (!$user) {
    die('User not found.');
}

$currentPhotoUrl = getProfilePhotoUrl($user_id);
$errorMessage = '';

if (isset($_POST['update'])) {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);

    $new_password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!empty($new_password)) {

        if (strlen($new_password) < 8) {
            $errorMessage = 'Password must be at least 8 characters';

        } elseif ($new_password !== $confirm_password) {
            $errorMessage = 'Passwords do not match';

        } elseif (!empty($user['last_password_change'])) {

            $last = strtotime($user['last_password_change']);
            $six_months = strtotime('+6 months', $last);

            if (time() < $six_months) {
                $errorMessage = 'You can only change password once every 6 months';
            }
        }

        if ($errorMessage === '') {

            $otp = random_int(100000, 999999);
            $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $stmt = $conn->prepare("
                UPDATE users
                SET otp_code = ?, otp_expiry = ?
                WHERE user_id = ?
            ");

            $stmt->bind_param("ssi", $otp, $expiry, $user_id);
            $stmt->execute();
            $stmt->close();

            try {

                $mail = new PHPMailer(true);

                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'ziyiyap2006@gmail.com';
                $mail->Password = 'dnuaffkldwjxlqhh';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom(
                    'ziyiyap2006@gmail.com',
                    'LOZ PC STORE'
                );

                $mail->addAddress($user['email']);

                $mail->isHTML(true);
                $mail->Subject = 'Your OTP Code';
                $mail->Body = "<h2>Your OTP is</h2><h1>{$otp}</h1>";

                $mail->send();

                $_SESSION['otp_type'] = 'password_change';
                $_SESSION['temp_user_id'] = $user_id;
                $_SESSION['temp_new_password'] = $new_password;

                header("Location: verify.php");
                exit;

            } catch (Exception $e) {
                $errorMessage = 'Email failed: ' . $mail->ErrorInfo;
            }
        }
    }

    if ($errorMessage === '') {

        $stmt = $conn->prepare("
            UPDATE users
            SET
                name = ?,
                email = ?,
                address = ?,
                phone = ?
            WHERE user_id = ?
        ");

        $stmt->bind_param(
            "ssssi",
            $name,
            $email,
            $address,
            $phone,
            $user_id
        );

        $stmt->execute();
        $stmt->close();

        header("Location: profile.php?updated=1");
        exit;
    }
}

$name = mysqli_real_escape_string($conn, $name);
$email = mysqli_real_escape_string($conn, $email);
$address = mysqli_real_escape_string($conn, $address);
$phone = mysqli_real_escape_string($conn, $phone);

mysqli_query($conn, "
    UPDATE users
    SET
        name='$name',
        email='$email',
        address='$address',
        phone='$phone'
    WHERE user_id=$user_id
");

    header("Location: profile.php?updated=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root{
            --bg1:#090a1f;
            --bg2:#1b1148;
            --card:#1a1834;
            --card2:#11162d;
            --text:#f6f7ff;
            --muted:#a4abc4;
            --line:rgba(255,255,255,.08);
            --blue:#14c8ff;
            --pink:#ff4fd8;
            --button1:#18c8ff;
            --button2:#e04fd8;
        }
        *{box-sizing:border-box}
        body{
            margin:0;
            min-height:100vh;
            font-family:'Poppins',sans-serif;
            color:var(--text);
            background:
                radial-gradient(circle at 0% 0%, rgba(76,29,149,.85), transparent 32%),
                radial-gradient(circle at 100% 0%, rgba(236,72,153,.18), transparent 20%),
                linear-gradient(135deg, #060814 0%, #0f1022 42%, #080a16 100%);
            display:flex;
            align-items:center;
            justify-content:center;
            padding:28px 18px;
        }
        .page{
            width:100%;
            max-width:760px;
            display:flex;
            justify-content:center;
        }
        .card{
            width:100%;
            max-width:390px;
            background:linear-gradient(180deg, rgba(28,29,60,.96), rgba(16,18,38,.96));
            border:1px solid rgba(255,255,255,.08);
            border-radius:22px;
            padding:26px 24px 20px;
            box-shadow:0 26px 80px rgba(0,0,0,.45);
            position:relative;
            overflow:hidden;
        }
        .card::before{
            content:'';
            position:absolute;
            inset:0 0 auto 0;
            height:3px;
            background:linear-gradient(90deg, var(--blue), var(--pink));
        }
        .title{
            text-align:center;
            font-size:24px;
            font-weight:800;
            margin-top:4px;
            margin-bottom:18px;
        }
        .title span{
            background:linear-gradient(90deg, #fff, #ff6be1);
            -webkit-background-clip:text;
            background-clip:text;
            color:transparent;
        }
        .avatar-wrap{
            display:flex;
            flex-direction:column;
            align-items:center;
            gap:12px;
            padding-bottom:18px;
            margin-bottom:20px;
            border-bottom:1px solid var(--line);
        }
        .avatar-circle{
            width:92px;
            height:92px;
            border-radius:50%;
            background:linear-gradient(180deg, rgba(15,17,41,.92), rgba(30,24,56,.92));
            border:1px solid rgba(255,255,255,.08);
            box-shadow:0 0 0 1px rgba(255,255,255,.03) inset;
            display:flex;
            align-items:center;
            justify-content:center;
            overflow:hidden;
            position:relative;
        }
        .avatar-circle img{
            width:100%;
            height:100%;
            object-fit:cover;
            display:block;
        }
        .avatar-circle .avatar-icon{
            width:36px;
            height:36px;
            color:#8c95b8;
        }
        .upload-row{
            display:flex;
            flex-wrap:wrap;
            gap:8px;
            justify-content:center;
        }
        .upload-btn,
        .remove-btn{
            border:none;
            border-radius:999px;
            padding:8px 14px;
            font-family:inherit;
            font-size:12px;
            font-weight:700;
            cursor:pointer;
            transition:.2s ease;
        }
        .upload-btn{
            background:rgba(255,255,255,.06);
            color:#fff;
            border:1px solid rgba(255,255,255,.12);
        }
        .remove-btn{
            background:rgba(255,79,216,.12);
            color:#ff88e6;
            border:1px solid rgba(255,79,216,.18);
        }
        .upload-btn:hover,
        .remove-btn:hover{
            transform:translateY(-1px);
        }
        .hint{
            font-size:11px;
            color:var(--muted);
        }
        .message{
            border-radius:14px;
            padding:12px 14px;
            margin-bottom:14px;
            font-size:13px;
            line-height:1.4;
        }
        .message.success{
            background:rgba(34,197,94,.12);
            border:1px solid rgba(34,197,94,.25);
            color:#9bf5b5;
        }
        .message.error{
            background:rgba(239,68,68,.12);
            border:1px solid rgba(239,68,68,.25);
            color:#ffb3b3;
        }
        .field{margin-bottom:14px;}
        .label{
            display:block;
            margin-bottom:7px;
            font-size:11px;
            font-weight:700;
            letter-spacing:.08em;
            color:#97a1c1;
            text-transform:uppercase;
        }
        .input-wrap{
            position:relative;
        }
        .input-wrap .icon{
            position:absolute;
            left:12px;
            top:50%;
            transform:translateY(-50%);
            width:16px;
            height:16px;
            color:#b9bfd8;
            pointer-events:none;
        }
        input, textarea{
            width:100%;
            border:none;
            outline:none;
            border-radius:10px;
            background:#fff;
            color:#1b1f2f;
            font-family:inherit;
            font-size:14px;
            padding:12px 14px 12px 40px;
            box-shadow:inset 0 0 0 1px rgba(255,255,255,.06);
        }
        textarea{
            min-height:92px;
            resize:none;
            padding-top:12px;
        }
        input[readonly]{
            background:#f4f5fb;
            color:#8086a2;
            cursor:not-allowed;
            padding-right:84px;
        }
        .locked-pill{
            position:absolute;
            right:10px;
            top:50%;
            transform:translateY(-50%);
            font-size:10px;
            font-weight:800;
            color:#e5e7ff;
            background:#2c305e;
            border-radius:999px;
            padding:5px 10px;
            letter-spacing:.04em;
        }
        .save-btn{
            width:100%;
            border:none;
            border-radius:12px;
            padding:13px 16px;
            color:#fff;
            font-family:inherit;
            font-weight:800;
            font-size:14px;
            cursor:pointer;
            margin-top:8px;
            background:linear-gradient(90deg, var(--button1), var(--button2));
            box-shadow:0 12px 26px rgba(80,101,255,.18);
            transition:.2s ease;
        }
        .save-btn:hover{transform:translateY(-1px);}
        .back-link{
            display:block;
            text-align:center;
            margin-top:14px;
            color:#36d3ff;
            text-decoration:none;
            font-weight:700;
            font-size:13px;
        }
        .back-link:hover{text-decoration:underline;}
        .file-input{display:none;}
        @media (max-width: 480px){
            .card{padding:22px 16px 18px;}
        }
    </style>
</head>
<body>

<div class="container">

<h2>👤 My Profile</h2>

<form method="post">

<input name="name" placeholder="Name" value="<?= $user['name'] ?>" required>

<input name="email" placeholder="Email" value="<?= $user['email'] ?>" required>

<input type="password" name="password" placeholder="New Password">

<input type="password" name="confirm_password" placeholder="Confirm New Password">

<textarea name="address" placeholder="Address" required><?= $user['address'] ?></textarea>

<input name="phone" placeholder="Phone Number" value="<?= $user['phone'] ?>" required>

<button name="update">Update Profile</button>

</form>

            <a href="product.php" class="back-link">← Back to Products</a>
        </div>
    </div>

    <script>
        lucide.createIcons();

        const fileInput = document.getElementById('profile_photo');
        const choosePhotoBtn = document.getElementById('choosePhotoBtn');
        const removePhotoBtn = document.getElementById('removePhotoBtn');
        const removePhotoField = document.getElementById('remove_photo');
        const avatarPreview = document.getElementById('avatarPreview');
        const avatarIcon = document.getElementById('avatarIcon');
        const avatarCircle = document.getElementById('avatarCircle');

        choosePhotoBtn.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) return;

            removePhotoField.value = '0';
            const reader = new FileReader();
            reader.onload = function (e) {
                if (avatarIcon) avatarIcon.style.display = 'none';
                avatarPreview.src = e.target.result;
                avatarPreview.style.display = 'block';
                avatarCircle.style.background = '#0f1224';
            };
            reader.readAsDataURL(file);
        });

        removePhotoBtn.addEventListener('click', () => {
            fileInput.value = '';
            removePhotoField.value = '1';
            avatarPreview.src = '';
            avatarPreview.style.display = 'none';
            if (avatarIcon) avatarIcon.style.display = 'block';
            avatarCircle.style.background = 'linear-gradient(180deg, rgba(15,17,41,.92), rgba(30,24,56,.92))';
        });
    </script>
</body>
</html>