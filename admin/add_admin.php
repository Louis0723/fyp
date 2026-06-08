<?php
include "../db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

if($_SESSION['role'] != "super_admin"){
    header("Location: admin_dashboard.php");
    exit();
}

$checkPhone = $conn->query("
    SHOW COLUMNS FROM admins LIKE 'phone'
");

if($checkPhone->num_rows == 0){

    $conn->query("
        ALTER TABLE admins
        ADD phone VARCHAR(30)
        NULL
    ");

}

$message = "";

if(isset($_POST['add_admin'])){

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];
    $status   = $_POST['status'];

    $check = $conn->prepare("
        SELECT *
        FROM admins
        WHERE email=?
    ");

    $check->bind_param("s",$email);
    $check->execute();

    $result = $check->get_result();

    if($result->num_rows > 0){

        $message = "
        <div class='error-msg'>
            Email already exists.
        </div>
        ";

    }else{

        $stmt = $conn->prepare("
            INSERT INTO admins
            (
                username,
                email,
                phone,
                password,
                role,
                status,
                created_at
            )
            VALUES
            (
                ?,?,?,?,?,?,NOW()
            )
        ");

        $stmt->bind_param(
            "ssssss",
            $username,
            $email,
            $phone,
            $password,
            $role,
            $status
        );

        $stmt->execute();

        header("Location: admin_management.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<title>Add Admin</title>

<link rel="stylesheet" href="style.css?v=500">

<script src="https://unpkg.com/lucide@latest"></script>

<style>

body{
    background:#eef3f8;
    font-family:'Inter',sans-serif;
}

.main-content{
    margin-left:270px;
    margin-top:95px;
    padding:34px;
    transition:.3s ease;
}

.sidebar.collapsed ~ .main-content{
    margin-left:95px;
}

.page-title{
    font-size:52px;
    font-weight:900;
    letter-spacing:-1px;
    color:#0f172a;
}

.subtitle{
    margin-top:10px;
    color:#64748b;
    font-size:16px;
}

.form-card{
    margin-top:28px;

    background:#fff;

    border-radius:30px;

    padding:35px;

    border:1px solid #e2e8f0;

    box-shadow:
    0 10px 40px rgba(15,23,42,.05);

    max-width:950px;
}

.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:22px;
}

.form-group{
    margin-bottom:24px;
}

.form-group.full{
    grid-column:1/3;
}

.form-group label{
    display:block;
    margin-bottom:10px;

    font-size:15px;
    font-weight:800;

    color:#0f172a;
}

.form-control{
    width:100%;
    height:58px;

    border-radius:18px;

    border:1px solid #dbe4ee;

    padding:0 18px;

    font-size:15px;

    background:#fff;

    transition:.2s ease;
}

.form-control:focus{
    border-color:#2563eb;

    box-shadow:
    0 0 0 4px rgba(37,99,235,.12);

    outline:none;
}

.submit-btn{
    margin-top:10px;

    height:58px;

    border:none;

    background:#2563eb;
    color:#fff;

    padding:0 28px;

    border-radius:18px;

    font-size:15px;
    font-weight:800;

    display:flex;
    align-items:center;
    gap:10px;

    cursor:pointer;

    transition:.25s ease;

    box-shadow:
    0 12px 24px rgba(37,99,235,.22);
}

.submit-btn:hover{
    transform:translateY(-2px);
}

.error-msg{
    margin-bottom:22px;

    background:#fee2e2;
    color:#b91c1c;

    padding:16px 18px;

    border-radius:16px;

    font-weight:700;
}

@media(max-width:900px){

    .main-content{
        margin-left:0;
        padding:20px;
    }

    .form-grid{
        grid-template-columns:1fr;
    }

    .form-group.full{
        grid-column:auto;
    }

}

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

<?php
if(isset($_SESSION['role']) &&
$_SESSION['role']=="super_admin"){

    include "sadmin_sidebar.php";

}else{

    include "admin_sidebar.php";
}
?>

<?php include "admin_header.php"; ?>

<div class="main-content">

    <div class="page-title">
        Add Admin
    </div>

    <div class="subtitle">
        Create and manage administrator accounts.
    </div>

    <div class="form-card">

        <?= $message ?>

        <form method="POST">

            <div class="form-grid">

                <div class="form-group">

                    <label>Username</label>

                    <input
                    type="text"
                    name="username"
                    class="form-control"
                    required>

                </div>

                <div class="form-group">

                    <label>Email Address</label>

                    <input
                    type="email"
                    name="email"
                    class="form-control"
                    required>

                </div>

                <div class="form-group">

                    <label>Phone Number</label>

                    <input
                    type="text"
                    name="phone"
                    class="form-control">

                </div>

                <div class="form-group">

                    <label>Password</label>

                    <input
                    type="password"
                    name="password"
                    class="form-control"
                    required>

                </div>

                <div class="form-group">

                    <label>Role</label>

                    <select
                    name="role"
                    class="form-control">

                        <option value="admin">
                            Admin
                        </option>

                        <option value="super_admin">
                            Super Admin
                        </option>

                    </select>

                </div>

                <div class="form-group">

                    <label>Status</label>

                    <select
                    name="status"
                    class="form-control">

                        <option value="Active">
                            Active
                        </option>

                        <option value="Suspended">
                            Suspended
                        </option>

                    </select>

                </div>

            </div>

            <button
            type="submit"
            name="add_admin"
            class="submit-btn">

                <i data-lucide="plus"></i>

                Add Admin

            </button>

        </form>

    </div>

</div>

<script>

lucide.createIcons();

</script>

</body>
</html>