<?php
include "../db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

/* GET USERS + STATS */
$users = $conn->query("
    SELECT 
        u.user_id,
        u.name,
        u.email,
        COUNT(o.order_id) AS total_orders,
        IFNULL(SUM(o.total_price),0) AS total_spent,
        YEAR(u.created_at) AS joined_year
    FROM users u
    LEFT JOIN orders o ON u.user_id = o.user_id
    GROUP BY u.user_id
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Customers</title>

<link rel="stylesheet" href="style.css?v=2">
<script src="https://unpkg.com/lucide@latest"></script>

<style>

/* ===== LAYOUT ===== */
.main{
    margin-left:270px;
    margin-top:100px;
    padding:30px;
}

.sidebar.collapsed ~ .main{
    margin-left:90px;
}

/* ===== HEADER ===== */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.title{
    font-size:26px;
    font-weight:700;
}

.subtitle{
    color:#6b7280;
    font-size:14px;
}

/* ===== SEARCH ===== */
.search-box{
    background:#fff;
    padding:12px 15px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
    width:300px;
    display:flex;
    gap:10px;
    align-items:center;
}

.search-box input{
    border:none;
    outline:none;
    width:100%;
}

/* ===== BUTTON ===== */
.btn-add{
    background:#2563eb;
    color:#fff;
    border:none;
    padding:10px 18px;
    border-radius:10px;
    display:flex;
    align-items:center;
    gap:8px;
    cursor:pointer;
}

/* ===== GRID ===== */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
    gap:20px;
}

/* ===== CARD ===== */
.card{
    background:#fff;
    border-radius:16px;
    padding:20px;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
    transition:0.2s;
}

.card:hover{
    transform:translateY(-5px);
}

/* ===== PROFILE ===== */
.profile{
    display:flex;
    align-items:center;
    gap:12px;
}

.avatar{
    width:45px;
    height:45px;
    border-radius:50%;
    background:#2563eb;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:600;
}

.name{
    font-weight:600;
}

.email{
    font-size:13px;
    color:#6b7280;
}

/* ===== BADGE ===== */
.badge{
    background:#e5e7eb;
    padding:4px 10px;
    border-radius:10px;
    font-size:12px;
}

/* ===== STATS ===== */
.stats{
    display:flex;
    justify-content:space-between;
    margin-top:15px;
    text-align:center;
}

.stats div{
    font-weight:600;
}

.stats small{
    display:block;
    color:#6b7280;
}

/* ===== ACTIONS ===== */
.actions{
    margin-top:15px;
    display:flex;
    justify-content:center;
    gap:20px;
}

.actions i{
    cursor:pointer;
    transition:0.2s;
}

.actions i:hover{
    transform:scale(1.2);
}

.edit{ color:#2563eb; }
.delete{ color:#ef4444; }

</style>
</head>

<body>

<?php include "admin_sidebar.php"; ?>
<?php include "admin_header.php"; ?>

<div class="main">

<!-- HEADER -->
<div class="header">

<div>
    <div class="title">Customers</div>
    <div class="subtitle">Your loyal builders and gamers.</div>
</div>

<button class="btn-add">
    <i data-lucide="plus"></i> Add Customer
</button>

</div>

<!-- SEARCH -->
<div class="search-box">
    <i data-lucide="search"></i>
    <input type="text" id="searchInput" placeholder="Search customers...">
</div>

<br>

<!-- GRID -->
<div class="grid" id="customerGrid">

<?php while($u = $users->fetch_assoc()): 

/* initials */
$initials = strtoupper(substr($u['name'] ?? $u['email'],0,2));

/* badge logic */
$badge = "Bronze";
if($u['total_spent'] > 5000) $badge = "Gold";
elseif($u['total_spent'] > 1000) $badge = "Silver";
?>

<div class="card customer-card">

<div class="profile">
    <div class="avatar"><?= $initials ?></div>

    <div>
        <div class="name"><?= htmlspecialchars($u['name'] ?? 'User') ?></div>
        <div class="email"><?= htmlspecialchars($u['email']) ?></div>
    </div>

    <div style="margin-left:auto" class="badge"><?= $badge ?></div>
</div>

<div class="stats">
    <div><?= $u['total_orders'] ?><small>Orders</small></div>
    <div>$<?= number_format($u['total_spent'],0) ?><small>Spent</small></div>
    <div><?= $u['joined_year'] ?><small>Joined</small></div>
</div>

<div class="actions">
    <i class="edit" data-lucide="pencil"></i>
    <i class="delete" data-lucide="trash-2"></i>
</div>

</div>

<?php endwhile; ?>

</div>

</div>
<script src="admin.js"></script>
<script>
lucide.createIcons();

/* SEARCH FILTER */
document.getElementById("searchInput").addEventListener("keyup", function(){
    let val = this.value.toLowerCase();

    document.querySelectorAll(".customer-card").forEach(card=>{
        card.style.display =
            card.innerText.toLowerCase().includes(val) ? "" : "none";
    });
});
</script>

</body>
</html>