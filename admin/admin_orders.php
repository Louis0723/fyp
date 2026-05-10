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
    $stmt->bind_param("ss", $status, $order_id);
    $stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/* DELETE ORDER */
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $conn->query("DELETE FROM orders WHERE order_id=$id");
}

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
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        GROUP BY 
            o.order_id,
            u.name,
            u.email,
            o.created_at,
            o.total_price,
            o.status
        ORDER BY o.created_at DESC";

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

.actions{
    text-align:center;
}

.actions i{
    cursor:pointer;
    padding:8px;
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

.refresh-btn{
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 999;
}

.refresh-btn button{
    width: 50px;
    height: 50px;
    border: none;
    border-radius: 50%;
    
    background: linear-gradient(135deg,#00f0ff,#ff00ff);
    color: white;
    font-size: 22px;
    font-weight: bold;

    cursor: pointer;
    box-shadow: 0 5px 15px rgba(0,240,255,0.4);

    transition: 0.3s ease;
}

.refresh-btn button:hover{
    transform: rotate(180deg) scale(1.1);
    box-shadow: 0 0 20px #00f0ff, 0 0 30px #ff00ff;
}
.refresh-btn-inline{
    width: 42px;
    height: 42px;
    border: none;
    border-radius: 50%;

    background: linear-gradient(135deg,#00f0ff,#ff00ff);
    color: white;
    font-size: 18px;
    font-weight: bold;

    cursor: pointer;
    box-shadow: 0 5px 15px rgba(0,240,255,0.3);

    transition: 0.3s ease;
}

.refresh-btn-inline:hover{
    transform: rotate(180deg) scale(1.1);
    box-shadow: 0 0 15px #00f0ff, 0 0 25px #ff00ff;
}

</style>

</head>

<body>

<?php include "admin_sidebar.php"; ?>
<?php include "admin_header.php"; ?>

<div class="content-area">

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">

    <h2 style="margin:0;">Orders</h2>

    <button class="refresh-btn-inline" onclick="window.location.href=window.location.href + '?t=' + Date.now()">
        ⟳
    </button>

</div>

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

<td>

<form method="POST" style="display:flex; gap:5px; align-items:center;">

    <input type="hidden" name="order_id"
           value="<?= $row['order_id'] ?>">

    <select name="status" style="
        padding:6px 10px;
        border-radius:8px;
        border:1px solid #ddd;
    ">

        <option value="Pending"
        <?= $row['status'] == 'Pending' ? 'selected' : '' ?>>
        Pending
        </option>

        <option value="Shipped"
        <?= $row['status'] == 'Shipped' ? 'selected' : '' ?>>
        Shipped
        </option>

        <option value="Delivered"
        <?= $row['status'] == 'Delivered' ? 'selected' : '' ?>>
        Delivered
        </option>

        <option value="Completed"
        <?= $row['status'] == 'Completed' ? 'selected' : '' ?>>
        Completed
        </option>

    </select>

    <button type="submit" name="update_status" style="
        padding:6px 10px;
        border:none;
        border-radius:8px;
        background:#2563eb;
        color:white;
        cursor:pointer;
    ">
        Update
    </button>

</form>

</td>

<td class="actions" style="text-align:center;">

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