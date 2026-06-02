
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

/* =========================
   UPDATE ADMIN
========================= */

if(isset($_POST['update_admin'])){

    $id      = $_POST['admin_id'];
    $name    = trim($_POST['username']);
    $email   = trim($_POST['email']);
    $phone   = trim($_POST['phone']);
    $role    = $_POST['role'];
    $status  = $_POST['status'];

    $stmt = $conn->prepare("
        UPDATE admins
        SET
        username=?,
        email=?,
        phone=?,
        role=?,
        status=?
        WHERE admin_id=?
    ");

    $stmt->bind_param(
        "sssssi",
        $name,
        $email,
        $phone,
        $role,
        $status,
        $id
    );

    $stmt->execute();

    header("Location: admin_management.php");
    exit();
}

/* =========================
   GET ADMINS
========================= */

$admins = $conn->query("
    SELECT *
    FROM admins
    ORDER BY admin_id ASC
");
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<title>Admin Management</title>

<link rel="stylesheet" href="style.css?v=999">

<script src="https://unpkg.com/lucide@latest"></script>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#eef3f8;
    font-family:'Inter',sans-serif;
    color:#0f172a;
}

/* MAIN */

.main-content{
    margin-left:270px;
    margin-top:100px;
    padding:34px;
}

/* TITLE */

.page-title{
    font-size:58px;
    font-weight:900;
    color:#0f172a;
}

.subtitle{
    margin-top:10px;
    color:#64748b;
    font-size:16px;
}

/* TOP */

.top-bar{
    margin-top:28px;

    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
}

/* SEARCH */

.search-box{
    width:420px;
    height:58px;

    background:#fff;

    border-radius:18px;

    border:1px solid #dbe4ee;

    display:flex;
    align-items:center;
    gap:12px;

    padding:0 18px;

    box-shadow:
    0 4px 18px rgba(0,0,0,.03);
}

.search-box input{
    width:100%;
    border:none;
    outline:none;
    background:none;
    font-size:15px;
}

.search-box i{
    color:#64748b;
}

/* BUTTON */

.add-btn{
    height:58px;

    padding:0 28px;

    border-radius:18px;

    background:#2563eb;
    color:#fff;

    display:flex;
    align-items:center;
    gap:10px;

    text-decoration:none;

    font-weight:800;

    box-shadow:
    0 12px 24px rgba(37,99,235,.22);
}

/* TABLE */

.table-card{
    margin-top:24px;

    background:#fff;

    border-radius:30px;

    overflow:hidden;

    border:1px solid #e5edf5;

    box-shadow:
    0 10px 40px rgba(15,23,42,.05);
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#f8fbff;

    color:#64748b;

    text-align:left;

    font-size:13px;
    font-weight:800;

    padding:22px;

    border-bottom:1px solid #edf2f7;
}

td{
    padding:22px;

    border-bottom:1px solid #f1f5f9;

    font-size:15px;
}

tr:hover{
    background:#f8fbff;
}

/* BADGES */

.role-badge{
    padding:8px 15px;

    border-radius:999px;

    font-size:12px;
    font-weight:800;
}

.super{
    background:#dbeafe;
    color:#2563eb;
}

.admin{
    background:#eef2ff;
    color:#4f46e5;
}

.status{
    padding:8px 15px;

    border-radius:999px;

    font-size:12px;
    font-weight:800;
}

.active{
    background:#dcfce7;
    color:#15803d;
}

.suspended{
    background:#fee2e2;
    color:#dc2626;
}

/* ACTION */

.action-icons{
    display:flex;
    align-items:center;
    gap:18px;
}

.action-icons i{
    width:22px;
    height:22px;
    cursor:pointer;
}

.edit{
    color:#2563eb;
}

/* MODAL */

.modal{
    position:fixed;
    inset:0;

    background:rgba(15,23,42,.45);

    display:none;
    align-items:center;
    justify-content:center;

    z-index:99999;

    backdrop-filter:blur(8px);
}

.modern-modal{
    width:560px;

    background:#fff;

    border-radius:34px;

    padding:28px;

    position:relative;

    box-shadow:
    0 30px 80px rgba(15,23,42,.18);
}

.modern-close{
    position:absolute;
    top:20px;
    right:20px;

    width:46px;
    height:46px;

    border-radius:50%;

    background:#f1f5f9;

    display:flex;
    align-items:center;
    justify-content:center;

    cursor:pointer;

    font-size:30px;
}

.modern-title{
    font-size:34px;
    font-weight:900;

    margin-bottom:24px;
}

/* AVATAR */

.admin-avatar-wrap{
    display:flex;
    justify-content:center;

    margin-bottom:28px;
}

.admin-avatar{
    width:120px;
    height:120px;

    border-radius:50%;

    object-fit:cover;
    object-position:center;

    border:5px solid #dbeafe;

    background:#f8fafc;

    display:block;
}

/* SECTION */

.section-title{
    color:#ef4444;

    font-size:18px;
    font-weight:800;

    margin-top:18px;
    margin-bottom:16px;
}

/* INPUT */

.edit-field{
    margin-bottom:18px;
}

.edit-field label{
    display:block;

    margin-bottom:10px;

    font-size:15px;
    font-weight:700;

    color:#64748b;
}

.modern-input-box{
    width:100%;
    height:56px;

    border-radius:16px;

    border:1px solid #dbe4ee;

    padding:0 18px;

    font-size:16px;
    font-weight:700;

    outline:none;
}

.modern-input-box:focus{
    border-color:#2563eb;

    box-shadow:
    0 0 0 4px rgba(37,99,235,.10);
}

/* SELECT */

.form-row{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:16px;
}

.form-group label{
    display:block;

    margin-bottom:10px;

    font-size:14px;
    font-weight:800;
}

.modern-select{
    width:100%;
    height:56px;

    border-radius:16px;

    border:1px solid #dbe4ee;

    padding:0 16px;

    font-size:15px;
    font-weight:700;

    outline:none;
}

/* BUTTON */

.update-btn{
    width:100%;
    height:58px;

    border:none;

    background:#2563eb;
    color:#fff;

    border-radius:18px;

    font-size:17px;
    font-weight:800;

    cursor:pointer;

    margin-top:28px;
}

.update-btn:hover{
    background:#1d4ed8;
}

</style>

</head>

<body>

<?php include "sadmin_sidebar.php"; ?>
<?php include "admin_header.php"; ?>

<div class="main-content">

<div class="page-title">
    Admin Management
</div>

<div class="subtitle">
    Super Admin · manage staff access to the console.
</div>

<div class="top-bar">

    <div class="search-box">

        <i data-lucide="search"></i>

        <input
        type="text"
        id="searchInput"
        placeholder="Search admins..."
        >

    </div>

    <a href="add_admin.php" class="add-btn">

        <i data-lucide="plus"></i>

        Add Admin

    </a>

</div>

<!-- TABLE -->

<div class="table-card">

<table>

<thead>

<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Status</th>
<th>Created</th>
<th>Actions</th>
</tr>

</thead>

<tbody>

<?php while($a = $admins->fetch_assoc()): ?>

<tr class="admin-row">

<td>
A-<?= str_pad($a['admin_id'],3,'0',STR_PAD_LEFT) ?>
</td>

<td>
<?= htmlspecialchars($a['username']) ?>
</td>

<td>
<?= htmlspecialchars($a['email']) ?>
</td>

<td>

<?php
$roleClass =
$a['role']=="super_admin"
? "super"
: "admin";
?>

<span class="role-badge <?= $roleClass ?>">
<?= ucfirst(str_replace('_',' ',$a['role'])) ?>
</span>

</td>

<td>

<?php
$statusClass =
$a['status']=="Suspended"
? "suspended"
: "active";
?>

<span class="status <?= $statusClass ?>">
<?= $a['status'] ?>
</span>

</td>

<td>
<?= date('Y-m-d', strtotime($a['created_at'])) ?>
</td>

<td>

<div class="action-icons">

<i
class="edit"
data-lucide="square-pen"

onclick="openEditModal(
'<?= $a['admin_id'] ?>',
'<?= htmlspecialchars(addslashes($a['username'])) ?>',
'<?= htmlspecialchars(addslashes($a['email'])) ?>',
'<?= htmlspecialchars(addslashes($a['phone'] ?? '')) ?>',
'<?= $a['role'] ?>',
'<?= $a['status'] ?>',
'<?= trim($a['avatar'] ?? '') ?>'
)"

></i>

</div>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

<!-- MODAL -->

<div class="modal" id="editModal">

<div class="modern-modal">

<div
class="modern-close"
onclick="closeModal('editModal')"
>
×
</div>

<div class="modern-title">
Admin Details
</div>

<form method="POST">

<input
type="hidden"
name="admin_id"
id="edit_id"
>

<!-- AVATAR -->

<div class="admin-avatar-wrap">

<img
id="edit_avatar"
src="https://cdn-icons-png.flaticon.com/512/149/149071.png"
class="admin-avatar"
>

</div>

<!-- PERSONAL -->

<div class="section-title">
Personal Information
</div>

<div class="edit-field">

<label>Name</label>

<input
type="text"
name="username"
id="edit_name"
class="modern-input-box"
required
>

</div>

<div class="edit-field">

<label>Email</label>

<input
type="email"
name="email"
id="edit_email"
class="modern-input-box"
required
>

</div>

<div class="edit-field">

<label>Phone</label>

<input
type="text"
name="phone"
id="edit_phone"
class="modern-input-box"
>

</div>

<!-- ACCOUNT -->

<div class="section-title">
Account Information
</div>

<div class="form-row">

<div class="form-group">

<label>Role</label>

<select
name="role"
id="edit_role"
class="modern-select"
>

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
id="edit_status"
class="modern-select"
>

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
name="update_admin"
class="update-btn"
>
Update Information
</button>

</form>

</div>

</div>

<script src="admin.js"></script>

<script>

lucide.createIcons();

/* =========================
   SEARCH ADMIN
========================= */

document
.getElementById("searchInput")
.addEventListener("keyup", function(){

    let value =
    this.value.toLowerCase();

    document
    .querySelectorAll(".admin-row")
    .forEach(row => {

        row.style.display =
        row.innerText
        .toLowerCase()
        .includes(value)
        ? ""
        : "none";

    });

});

/* =========================
   OPEN EDIT MODAL
========================= */

function openEditModal(
id,
name,
email,
phone,
role,
status,
avatar
){

    /* SHOW MODAL */

    document
    .getElementById("editModal")
    .style.display = "flex";

    /* SET FORM VALUES */

    document
    .getElementById("edit_id")
    .value = id;

    document
    .getElementById("edit_name")
    .value = name;

    document
    .getElementById("edit_email")
    .value = email;

    document
    .getElementById("edit_phone")
    .value = phone;

    document
    .getElementById("edit_role")
    .value = role;

    document
    .getElementById("edit_status")
    .value = status;

    /* =========================
       AVATAR SYSTEM
    ========================= */

    let avatarImg =
    document.getElementById("edit_avatar");

    /* DEFAULT IMAGE */

    let defaultAvatar =
    "https://cdn-icons-png.flaticon.com/512/149/149071.png";

    /* RESET */

    avatarImg.onerror = null;

    /* IF DATABASE HAS AVATAR */

    if(
        avatar &&
        avatar !== "NULL" &&
        avatar !== "null" &&
        avatar.trim() !== ""
    ){

        /* TRY uploads FOLDER */

        let uploadPath =
        "../uploads/" + avatar;

        /* TRY uploaded_img FOLDER */

        let uploadedImgPath =
        "../uploaded_img/" + avatar;

        /* FIRST TRY */

        avatarImg.src =
        uploadPath +
        "?v=" +
        new Date().getTime();

        /* IF FAIL */

        avatarImg.onerror = function(){

            this.onerror = null;

            /* SECOND TRY */

            this.src =
            uploadedImgPath +
            "?v=" +
            new Date().getTime();

            /* IF FAIL AGAIN */

            this.onerror = function(){

                this.onerror = null;

                /* DEFAULT IMAGE */

                this.src = defaultAvatar;

            };

        };

    }else{

        /* NO DATABASE AVATAR */

        avatarImg.src = defaultAvatar;

    }

}

/* =========================
   CLOSE MODAL
========================= */

function closeModal(id){

    document
    .getElementById(id)
    .style.display = "none";

}

/* =========================
   CLOSE WHEN CLICK OUTSIDE
========================= */

window.onclick = function(e){

    if(
        e.target.classList.contains("modal")
    ){

        e.target.style.display = "none";

    }

}

</script>

</body>
</html>

