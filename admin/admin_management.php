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
   ADD STATUS COLUMN
========================= */

$check = $conn->query("
    SHOW COLUMNS FROM admins LIKE 'status'
");

if($check->num_rows == 0){

    $conn->query("
        ALTER TABLE admins
        ADD status VARCHAR(30)
        NOT NULL DEFAULT 'Active'
    ");

}

/* =========================
   ADD ADMIN
========================= */

if(isset($_POST['add_admin'])){

    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role      = $_POST['role'];
    $status    = $_POST['status'];

    $stmt = $conn->prepare("
        INSERT INTO admins
        (username,email,password,role,status,created_at)
        VALUES (?,?,?,?,?,NOW())
    ");

    $stmt->bind_param(
        "sssss",
        $username,
        $email,
        $password,
        $role,
        $status
    );

    $stmt->execute();

    header("Location: admin_management.php");
    exit();
}

/* =========================
   UPDATE ADMIN
========================= */

if(isset($_POST['update_admin'])){

    $id     = $_POST['admin_id'];
    $name   = $_POST['username'];
    $email  = $_POST['email'];
    $role   = $_POST['role'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("
        UPDATE admins
        SET
        username=?,
        email=?,
        role=?,
        status=?
        WHERE admin_id=?
    ");

    $stmt->bind_param(
        "ssssi",
        $name,
        $email,
        $role,
        $status,
        $id
    );

    $stmt->execute();

    header("Location: admin_management.php");
    exit();
}

/* =========================
   DELETE ADMIN
========================= */

if(isset($_GET['delete'])){

    $id = intval($_GET['delete']);

    $conn->query("
        DELETE FROM admins
        WHERE admin_id='$id'
    ");

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

<link rel="stylesheet" href="style.css?v=2">
<script src="https://unpkg.com/lucide@latest"></script>

<style>

body{
    background:#f3f7fb;
    font-family:'Inter',sans-serif;
}

/* MAIN */

.main-content{
    margin-left:270px;
    margin-top:95px;
    padding:28px;
    transition:.3s ease;
}

.sidebar.collapsed ~ .main-content{
    margin-left:95px;
}

/* TITLE */

.page-title{
    font-size:42px;
    font-weight:800;
    color:#0f172a;
}

.subtitle{
    margin-top:5px;
    color:#64748b;
    font-size:15px;
}

/* TOP */

.top-bar{
    margin-top:25px;

    display:flex;
    justify-content:space-between;
    align-items:center;
}

/* SEARCH */

.search-box{
    width:390px;
    height:48px;

    background:#fff;

    border-radius:14px;
    border:1px solid #dbe2ea;

    display:flex;
    align-items:center;
    gap:10px;

    padding:0 16px;
}

.search-box input{
    width:100%;
    border:none;
    outline:none;
    background:none;
    font-size:14px;
}

/* BUTTON */

.add-btn{
    border:none;
    background:#2563eb;
    color:#fff;

    height:48px;
    padding:0 24px;

    border-radius:14px;

    font-weight:700;
    font-size:14px;

    display:flex;
    align-items:center;
    gap:8px;

    cursor:pointer;

    transition:.2s;
}

.add-btn:hover{
    transform:translateY(-2px);
}

/* TABLE */

.table-card{
    margin-top:20px;

    background:#fff;

    border-radius:18px;

    overflow:hidden;

    border:1px solid #e5e7eb;

    box-shadow:0 4px 18px rgba(0,0,0,.05);
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#f8fafc;
    color:#64748b;

    text-align:left;

    font-size:13px;
    font-weight:700;

    padding:18px;
}

td{
    padding:18px;
    border-top:1px solid #eef2f7;
}

/* ROLE */

.role-badge{
    padding:7px 14px;
    border-radius:999px;

    font-size:12px;
    font-weight:700;

    display:inline-block;
}

.super{
    background:#dbeafe;
    color:#2563eb;
}

.admin{
    background:#eef2f7;
    color:#334155;
}

/* STATUS */

.status{
    padding:7px 14px;
    border-radius:999px;

    font-size:12px;
    font-weight:700;

    display:inline-block;
}

.active{
    background:#dcfce7;
    color:#166534;
}

.suspended{
    background:#fee2e2;
    color:#991b1b;
}

/* ACTION */

.action-icons{
    display:flex;
    gap:14px;
}

.action-icons i{
    cursor:pointer;
    transition:.2s;
}

.action-icons i:hover{
    transform:scale(1.15);
}

.edit{
    color:#2563eb;
}

.delete{
    color:#ef4444;
}

/* MODAL */

.modal{
    position:fixed;
    inset:0;

    background:rgba(15,23,42,.6);

    display:none;
    align-items:center;
    justify-content:center;

    z-index:9999;

    backdrop-filter:blur(4px);
}

.modal-box{
    width:520px;

    background:#fff;

    border-radius:24px;

    padding:28px;

    position:relative;
}

.modal-close{
    position:absolute;
    right:20px;
    top:18px;

    cursor:pointer;

    font-size:22px;
}

.modal-title{
    font-size:28px;
    font-weight:800;

    margin-bottom:22px;
}

/* FORM */

.form-group{
    margin-bottom:16px;
}

.form-group label{
    display:block;
    margin-bottom:8px;

    font-size:14px;
    font-weight:700;
}

.form-control{
    width:100%;
    height:48px;

    border-radius:12px;
    border:1px solid #dbe2ea;

    padding:0 14px;

    font-size:14px;
}

.form-row{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px;
}

/* FOOTER */

.modal-footer{
    margin-top:24px;

    display:flex;
    justify-content:flex-end;
    gap:12px;
}

.cancel-btn{
    border:none;
    background:#e2e8f0;

    padding:12px 20px;
    border-radius:12px;

    font-weight:700;

    cursor:pointer;
}

.save-btn{
    border:none;
    background:#2563eb;
    color:#fff;

    padding:12px 22px;
    border-radius:12px;

    font-weight:700;

    cursor:pointer;
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

    <button
        class="add-btn"
        onclick="openAddModal()"
    >

        <i data-lucide="plus"></i>
        Add Admin

    </button>

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
$roleClass = "admin";

if($a['role']=="super_admin"){
    $roleClass = "super";
}
?>

<span class="role-badge <?= $roleClass ?>">
    <?= ucfirst(str_replace('_',' ',$a['role'])) ?>
</span>

</td>

<td>

<?php
$statusClass = "active";

if($a['status']=="Suspended"){
    $statusClass = "suspended";
}
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
data-lucide="pencil"

onclick="openEditModal(
'<?= $a['admin_id'] ?>',
'<?= addslashes($a['username']) ?>',
'<?= addslashes($a['email']) ?>',
'<?= $a['role'] ?>',
'<?= $a['status'] ?>'
)"

></i>

<a href="?delete=<?= $a['admin_id'] ?>"
onclick="return confirm('Delete this admin?')">

<i class="delete" data-lucide="trash-2"></i>

</a>

</div>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

<!-- ADD MODAL -->

<div class="modal" id="addModal">

<div class="modal-box">

<div class="modal-close" onclick="closeModal('addModal')">
×
</div>

<div class="modal-title">
    Add Admin
</div>

<form method="POST">

<div class="form-group">
<label>Username</label>
<input
type="text"
name="username"
class="form-control"
required
>
</div>

<div class="form-group">
<label>Email</label>
<input
type="email"
name="email"
class="form-control"
required
>
</div>

<div class="form-group">
<label>Password</label>
<input
type="password"
name="password"
class="form-control"
required
>
</div>

<div class="form-row">

<div class="form-group">

<label>Role</label>

<select
name="role"
class="form-control"
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
class="form-control"
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

<div class="modal-footer">

<button
type="button"
class="cancel-btn"
onclick="closeModal('addModal')"
>
Cancel
</button>

<button
type="submit"
name="add_admin"
class="save-btn"
>
Add Admin
</button>

</div>

</form>

</div>

</div>

<!-- EDIT MODAL -->

<div class="modal" id="editModal">

<div class="modal-box">

<div class="modal-close"
onclick="closeModal('editModal')">
×
</div>

<div class="modal-title">
    Edit Admin
</div>

<form method="POST">

<input
type="hidden"
name="admin_id"
id="edit_id"
>

<div class="form-group">

<label>Name</label>

<input
type="text"
name="username"
id="edit_name"
class="form-control"
required
>

</div>

<div class="form-group">

<label>Email</label>

<input
type="email"
name="email"
id="edit_email"
class="form-control"
required
>

</div>

<div class="form-row">

<div class="form-group">

<label>Role</label>

<select
name="role"
id="edit_role"
class="form-control"
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
class="form-control"
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

<div class="modal-footer">

<button
type="button"
class="cancel-btn"
onclick="closeModal('editModal')"
>
Cancel
</button>

<button
type="submit"
name="update_admin"
class="save-btn"
>
Save changes
</button>

</div>

</form>

</div>

</div>

<script src="admin.js"></script>

<script>

lucide.createIcons();

/* SEARCH */

document.getElementById("searchInput")
.addEventListener("keyup",function(){

let value = this.value.toLowerCase();

document.querySelectorAll(".admin-row")
.forEach(row=>{

    row.style.display =
    row.innerText.toLowerCase().includes(value)
    ? ""
    : "none";

});

});

/* MODAL */

function openAddModal(){

    document.getElementById("addModal")
    .style.display = "flex";

}

function openEditModal(
id,
name,
email,
role,
status
){

    document.getElementById("editModal")
    .style.display = "flex";

    document.getElementById("edit_id").value = id;
    document.getElementById("edit_name").value = name;
    document.getElementById("edit_email").value = email;
    document.getElementById("edit_role").value = role;
    document.getElementById("edit_status").value = status;

}

function closeModal(id){

    document.getElementById(id)
    .style.display = "none";

}

window.onclick = function(e){

    if(e.target.classList.contains("modal")){

        e.target.style.display = "none";

    }

}

</script>

</body>
</html>