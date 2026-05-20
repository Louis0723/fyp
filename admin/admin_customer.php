<?php
include "../db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

/* =========================
   AUTO CREATE STATUS COLUMN
========================= */

$checkColumn = $conn->query("
    SHOW COLUMNS FROM users LIKE 'status'
");

if($checkColumn->num_rows == 0){

    $conn->query("
        ALTER TABLE users
        ADD status VARCHAR(50)
        NOT NULL DEFAULT 'Active'
    ");

}

/* =========================
   UPDATE STATUS
========================= */

if(isset($_POST['update_status'])){

    $user_id = intval($_POST['user_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("
        UPDATE users
        SET status=?
        WHERE user_id=?
    ");

    $stmt->bind_param("si",$status,$user_id);
    $stmt->execute();

    header("Location: admin_customer.php");
    exit();
}

/* =========================
   USERS + STATS
========================= */

$users = $conn->query("
    SELECT 
        u.user_id,
        u.name,
        u.email,
        u.created_at,
        u.status,

        COUNT(o.order_id) AS total_orders,
        IFNULL(SUM(o.total_price),0) AS total_spent,
        YEAR(u.created_at) AS joined_year

    FROM users u

    LEFT JOIN orders o
    ON u.user_id = o.user_id

    GROUP BY u.user_id
");
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<title>Customers</title>

<link rel="stylesheet" href="style.css?v=10">

<script src="https://unpkg.com/lucide@latest"></script>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter',sans-serif;
}

body{
    background:#f3f7fb;
}

/* MAIN */

.main{
    margin-left:270px;
    margin-top:95px;
    padding:28px;
    transition:.3s ease;
}

/* SIDEBAR COLLAPSE */

.sidebar.collapsed ~ .main{
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

/* SEARCH */

.search-wrapper{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin:24px 0;
}

.search-box{
    width:390px;
    height:50px;
    background:#fff;
    border-radius:15px;
    border:1px solid #dbe2ea;
    display:flex;
    align-items:center;
    gap:12px;
    padding:0 18px;
}

.search-box input{
    width:100%;
    border:none;
    outline:none;
    background:none;
    font-size:15px;
}

/* TABLE */

.customer-table-wrapper{
    margin-top:25px;
    background:#fff;
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 4px 18px rgba(0,0,0,.05);
}

.customer-table{
    width:100%;
    border-collapse:collapse;
}

.customer-table thead{
    background:#f8fafc;
}

.customer-table th{
    text-align:left;
    padding:18px;
    font-size:13px;
    font-weight:700;
    color:#64748b;
    border-bottom:1px solid #e5e7eb;
}

.customer-table td{
    padding:18px;
    border-bottom:1px solid #f1f5f9;
    vertical-align:middle;
}

.customer-table tr:hover{
    background:#f8fbff;
}

/* PROFILE */

.customer-profile{
    display:flex;
    align-items:center;
    gap:12px;
}

/* AVATAR */

.avatar{
    width:46px;
    height:46px;
    border-radius:50%;
    background:#2563eb;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:16px;
    font-weight:700;
}

/* TEXT */

.customer-name{
    font-size:15px;
    font-weight:700;
    color:#0f172a;
}

.customer-email{
    color:#64748b;
    font-size:13px;
    margin-top:3px;
}

/* STATUS */

.customer-status{
    width:fit-content;
    padding:7px 14px;
    border-radius:999px;
    font-size:12px;
    font-weight:700;
}

.status-active{
    background:#dcfce7;
    color:#166534;
}

.status-suspended{
    background:#fef3c7;
    color:#92400e;
}

.status-banned{
    background:#fee2e2;
    color:#991b1b;
}

/* ACTIONS */

.table-actions{
    display:flex;
    align-items:center;
    gap:12px;
}

.action-btn{
    width:36px;
    height:36px;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    transition:.2s ease;
}

.action-btn:hover{
    background:#eff6ff;
}

.edit{
    color:#2563eb;
}

.delete{
    color:#ef4444;
}

/* MODAL */

.customer-modal{
    position:fixed;
    inset:0;
    background:rgba(15,23,42,.65);
    display:none;
    align-items:center;
    justify-content:center;
    z-index:9999;
    backdrop-filter:blur(5px);
}

.customer-modal-box{
    width:520px;
    background:#fff;
    border-radius:24px;
    padding:28px;
    position:relative;
}

.modal-close{
    position:absolute;
    right:20px;
    top:20px;
    cursor:pointer;
    width:34px;
    height:34px;
    border-radius:50%;
    background:#f1f5f9;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:14px;
    font-weight:700;
}

.modal-title{
    font-size:24px;
    font-weight:800;
    margin-bottom:24px;
}

.modal-avatar{
    width:90px;
    height:90px;
    margin:auto;
    margin-bottom:24px;
    border-radius:50%;
    background:#eff6ff;
    display:flex;
    align-items:center;
    justify-content:center;
}

.modal-avatar i{
    width:40px;
    height:40px;
    color:#2563eb;
}

.modal-section{
    margin-top:20px;
}

.modal-section-title{
    font-size:14px;
    font-weight:700;
    color:#ef4444;
    border-bottom:1px solid #e5e7eb;
    padding-bottom:8px;
    margin-bottom:10px;
}

.modal-row{
    display:flex;
    justify-content:space-between;
    padding:10px 0;
    border-bottom:1px solid #f1f5f9;
}

.modal-row span{
    color:#64748b;
    font-size:14px;
}

.modal-row strong{
    color:#0f172a;
    font-size:14px;
}

.status-select{
    width:100%;
    height:42px;
    border-radius:12px;
    border:1px solid #dbe2ea;
    padding:0 12px;
    margin-top:14px;
    font-size:14px;
    font-weight:600;
}

.update-btn{
    width:100%;
    height:44px;
    margin-top:14px;
    border:none;
    border-radius:12px;
    background:#2563eb;
    color:#fff;
    font-size:14px;
    font-weight:700;
    cursor:pointer;
}

</style>

</head>

<body>

<?php
if(isset($_SESSION['role']) && $_SESSION['role']=="super_admin"){
    include "sadmin_sidebar.php";
}else{
    include "admin_sidebar.php";
}
?>

<?php include "admin_header.php"; ?>

<div class="main">

<div class="page-title">
    Customers
</div>

<div class="subtitle">
    Your loyal builders and gamers.
</div>

<div class="search-wrapper">

    <div class="search-box">

        <i data-lucide="search"></i>

        <input type="text"
               id="searchInput"
               placeholder="Search customers...">

    </div>

</div>

<!-- CUSTOMER TABLE -->

<div class="customer-table-wrapper">

<table class="customer-table">

<thead>

<tr>
    <th>Customer</th>
    <th>Email</th>
    <th>Orders</th>
    <th>Total Spent</th>
    <th>Joined</th>
    <th>Status</th>
    <th>Actions</th>
</tr>

</thead>

<tbody>

<?php while($u = $users->fetch_assoc()): 

$initials = strtoupper(substr($u['name'],0,2));

$username = explode('@',$u['email'])[0];

$nameParts = explode(' ', trim($u['name']));
$firstName = $nameParts[0];
$lastName = end($nameParts);

$statusClass = "status-active";

if($u['status'] == "Suspended"){
    $statusClass = "status-suspended";
}

if($u['status'] == "Banned"){
    $statusClass = "status-banned";
}

?>

<tr class="customer-item">

<td>

<div class="customer-profile">

<div class="avatar">
    <?= $initials ?>
</div>

<div>

<div class="customer-name">
    <?= htmlspecialchars($u['name']) ?>
</div>

<div class="customer-email">
    <?= htmlspecialchars($u['email']) ?>
</div>

</div>

</div>

</td>

<td>
    <?= htmlspecialchars($u['email']) ?>
</td>

<td>
    <?= $u['total_orders'] ?>
</td>

<td>
    RM <?= number_format($u['total_spent'],2) ?>
</td>

<td>
    <?= $u['joined_year'] ?>
</td>

<td>

<div class="customer-status <?= $statusClass ?>">
    <?= $u['status'] ?>
</div>

</td>

<td>

<div class="table-actions">

<div class="action-btn edit"

onclick="openCustomerModal(
'<?= $u['user_id'] ?>',
'<?= $username ?>',
'<?= $firstName ?>',
'<?= $lastName ?>',
'<?= addslashes($u['name']) ?>',
'2000-01-01',
'Male',
'<?= addslashes($u['email']) ?>',
'012-3456789',
'<?= $u['status'] ?>',
'<?= date('Y-m-d h:i A', strtotime($u['created_at'])) ?>'
)">

<i data-lucide="eye"></i>

</div>

<div class="action-btn delete">

<i data-lucide="trash-2"></i>

</div>

</div>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

<!-- MODAL -->

<div class="customer-modal"
     id="customerModal">

<div class="customer-modal-box">

<div class="modal-close"
     onclick="closeCustomerModal()">
    ✕
</div>

<div class="modal-title">
    Customer Details
</div>

<div class="modal-avatar">
    <i data-lucide="user"></i>
</div>

<div class="modal-section">

<div class="modal-section-title">
    Personal Information
</div>

<div class="modal-row">
    <span>Customer ID:</span>
    <strong id="m_id"></strong>
</div>

<div class="modal-row">
    <span>Username:</span>
    <strong id="m_username"></strong>
</div>

<div class="modal-row">
    <span>First Name:</span>
    <strong id="m_first"></strong>
</div>

<div class="modal-row">
    <span>Last Name:</span>
    <strong id="m_last"></strong>
</div>

<div class="modal-row">
    <span>Full Name:</span>
    <strong id="m_full"></strong>
</div>

<div class="modal-row">
    <span>Date of Birth:</span>
    <strong id="m_dob"></strong>
</div>

<div class="modal-row">
    <span>Gender:</span>
    <strong id="m_gender"></strong>
</div>

</div>

<div class="modal-section">

<div class="modal-section-title">
    Contact Information
</div>

<div class="modal-row">
    <span>Email:</span>
    <strong id="m_email"></strong>
</div>

<div class="modal-row">
    <span>Phone:</span>
    <strong id="m_phone"></strong>
</div>

</div>

<div class="modal-section">

<div class="modal-section-title">
    Account Information
</div>

<div class="modal-row">
    <span>Registered At:</span>
    <strong id="m_registered"></strong>
</div>

</div>

<form method="POST">

<input type="hidden"
       name="user_id"
       id="form_user_id">

<select class="status-select"
        name="status"
        id="form_status">

<option value="Active">
    Active
</option>

<option value="Suspended">
    Suspended
</option>

<option value="Banned">
    Banned
</option>

</select>

<button type="submit"
        name="update_status"
        class="update-btn">

    Update Status

</button>

</form>

</div>

</div>

<script src="admin.js"></script>

<script>

lucide.createIcons();

/* SEARCH */

document.getElementById("searchInput").addEventListener("keyup", function(){

let value = this.value.toLowerCase();

document.querySelectorAll(".customer-item").forEach(card=>{

    if(card.innerText.toLowerCase().includes(value)){
        card.style.display = "";
    }else{
        card.style.display = "none";
    }

});

});

/* OPEN MODAL */

function openCustomerModal(
id,
username,
first,
last,
full,
dob,
gender,
email,
phone,
status,
registered
){

document.getElementById("customerModal").style.display = "flex";

document.getElementById("m_id").innerText = id;
document.getElementById("m_username").innerText = username;
document.getElementById("m_first").innerText = first;
document.getElementById("m_last").innerText = last;
document.getElementById("m_full").innerText = full;
document.getElementById("m_dob").innerText = dob;
document.getElementById("m_gender").innerText = gender;
document.getElementById("m_email").innerText = email;
document.getElementById("m_phone").innerText = phone;
document.getElementById("m_registered").innerText = registered;

document.getElementById("form_user_id").value = id;
document.getElementById("form_status").value = status;

}

/* CLOSE */

function closeCustomerModal(){

document.getElementById("customerModal").style.display =
"none";

}

window.onclick = function(e){

let modal = document.getElementById("customerModal");

if(e.target == modal){
    modal.style.display = "none";
}

}
</script>
<script src="https://unpkg.com/lucide@latest"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="bootstrap.bundle.js"></script>

<script src="admin.js"></script>
</body>
</html>