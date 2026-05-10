<?php
session_start();
include "../db.php";

if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit();
}

/* UPDATE STATUS */
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE order_id=?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
}

/* DELETE ORDER */
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $conn->query("DELETE FROM orders WHERE order_id=$id");
}

/* GET ORDERS */
$sql = "SELECT 
            o.order_id,
            u.name AS user_name,
            u.email,
            DATE(o.created_at) as order_date,
            SUM(oi.quantity) AS total_qty,
            o.total_price,
            o.status
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        JOIN order_items oi ON o.order_id = oi.order_id
        GROUP BY o.order_id
        ORDER BY o.order_id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Orders</title>

<link rel="stylesheet" href="style.css?v=2">
<script src="https://unpkg.com/lucide@latest"></script>

<style>

/* ===== GENERAL ===== */
body{
    font-family:'Inter',sans-serif;
    background:#f5f7fb;
}

/* ===== LAYOUT ===== */
.content-area{
    margin-left:260px;
    margin-top:100px;
    padding:30px;
}

/* ===== TABLE ===== */
.table-card{
    background:#fff;
    border-radius:16px;
    padding:20px;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
}

table{ width:100%; border-collapse:collapse; }

th{ padding:14px; color:#6b7280; }
td{ padding:16px 14px; border-top:1px solid #eee; }

tr:hover{ background:#f9fbff; }

/* ===== ACTIONS ===== */
.actions i{
    cursor:pointer;
    margin-right:10px;
    padding:6px;
    border-radius:8px;
    transition:0.2s;
}

.actions i:hover{
    background:#eef2ff;
    transform:scale(1.2);
}

.view{color:#111;}
.delete{color:#ef4444;}

/* ===== MODAL ===== */
.modal{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.5);
    display:none;
    justify-content:center;
    align-items:center;
    z-index:999;
}

.modal-content{
    background:#fff;
    padding:25px;
    border-radius:14px;
    width:400px;
    box-shadow:0 20px 50px rgba(0,0,0,0.2);
    position:relative;
    animation:fadeIn 0.25s ease;
}

@keyframes fadeIn{
    from{opacity:0; transform:scale(0.9);}
    to{opacity:1; transform:scale(1);}
}

.modal h3{
    margin-bottom:15px;
}

.modal p{
    margin:8px 0;
}

.close-btn{
    position:absolute;
    right:15px;
    top:15px;
    cursor:pointer;
    font-size:18px;
    color:#555;
}

.close-btn:hover{
    color:red;
}

.badge{
    padding:5px 10px;
    border-radius:10px;
    font-size:12px;
    background:#2563eb;
    color:#fff;
}

</style>

</head>

<body>

<?php include "admin_sidebar.php"; ?>
<?php include "admin_header.php"; ?>

<div class="content-area">

<h2>Orders</h2>

<div class="table-card">

<table>
<thead>
<tr>
    <th>Order ID</th>
    <th>Customer</th>
    <th>Date</th>
    <th>Items</th>
    <th>Total</th>
    <th>Status</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>

<?php while($row = $result->fetch_assoc()): ?>

<tr class="order-row">

<td>ORD-<?= $row['order_id'] ?></td>

<td>
<strong><?= $row['user_name'] ?></strong><br>
<small><?= $row['email'] ?></small>
</td>

<td><?= $row['order_date'] ?></td>

<td><?= $row['total_qty'] ?></td>

<td>RM <?= number_format($row['total_price'],2) ?></td>

<td><?= $row['status'] ?></td>

<td class="actions">

<!-- ✅ VIEW BUTTON -->
<i class="view"
   data-lucide="eye"
   onclick="openModal(
   '<?= $row['order_id'] ?>',
   '<?= addslashes($row['user_name']) ?>',
   '<?= $row['email'] ?>',
   '<?= $row['order_date'] ?>',
   '<?= $row['total_qty'] ?>',
   '<?= $row['total_price'] ?>',
   '<?= $row['status'] ?>'
   )"></i>

<a href="?delete=<?= $row['order_id'] ?>" onclick="return confirm('Delete?')">
<i class="delete" data-lucide="trash-2"></i>
</a>

</td>

</tr>

<?php endwhile; ?>

</tbody>
</table>

</div>

</div>

<!-- ✅ MODAL -->
<div class="modal" id="orderModal">
<div class="modal-content">

<span class="close-btn" onclick="closeModal()">✖</span>

<h3>Order <span id="m_id"></span></h3>

<p><strong>Customer:</strong> <span id="m_name"></span></p>
<p><strong>Email:</strong> <span id="m_email"></span></p>
<p><strong>Date:</strong> <span id="m_date"></span></p>
<p><strong>Items:</strong> <span id="m_items"></span></p>
<p><strong>Total:</strong> RM <span id="m_total"></span></p>
<p><strong>Status:</strong> <span class="badge" id="m_status"></span></p>

</div>
</div>
<script src="admin.js"></script>
<script>
lucide.createIcons();

/* OPEN MODAL */
function openModal(id,name,email,date,items,total,status){
    document.getElementById("orderModal").style.display="flex";

    document.getElementById("m_id").innerText="ORD-"+id;
    document.getElementById("m_name").innerText=name;
    document.getElementById("m_email").innerText=email;
    document.getElementById("m_date").innerText=date;
    document.getElementById("m_items").innerText=items;
    document.getElementById("m_total").innerText=parseFloat(total).toFixed(2);
    document.getElementById("m_status").innerText=status;
}

/* CLOSE MODAL */
function closeModal(){
    document.getElementById("orderModal").style.display="none";
}

/* CLICK OUTSIDE CLOSE */
window.onclick = function(e){
    let modal = document.getElementById("orderModal");
    if(e.target === modal){
        modal.style.display="none";
    }
}
</script>

</body>
</html>