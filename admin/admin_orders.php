<?php
session_start();
include "../db.php";

if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit();
}

// update status
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE order_id=?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    $stmt->close();
}

// get orders
$sql = "SELECT 
            o.order_id,
            u.name AS user_name,
            GROUP_CONCAT(p.product_name SEPARATOR ', ') AS products,
            SUM(oi.quantity) AS total_qty,
            o.total_price,
            o.status
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN products p ON oi.product_id = p.product_id
        GROUP BY o.order_id
        ORDER BY o.order_id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin – Orders</title>

<!-- ✅ IMPORTANT -->
<link rel="stylesheet" href="style.css">

<script src="https://unpkg.com/lucide@latest"></script>

<style>
body{
    font-family: 'Inter', sans-serif;
    background: #f8fafc;
}

/* layout fix */
.main-layout{
    display:flex;
}

.content-area{
    margin-left:240px;
    margin-top:90px;
    padding:30px;
    width:100%;
}

/* collapse support */
.sidebar.collapsed ~ .main-layout .content-area{
    margin-left:70px;
}

/* table */
.table-container{
    background:#fff;
    padding:20px;
    border-radius:16px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    padding:12px;
    text-align:center;
}

th{
    background:#3b82f6;
    color:#fff;
}

tr:nth-child(even){
    background:#f9fafb;
}

/* status */
.status-Pending{
    color:#b45309;
    background:#fde68a;
    padding:4px 10px;
    border-radius:12px;
}

.status-Completed{
    color:#065f46;
    background:#a7f3d0;
    padding:4px 10px;
    border-radius:12px;
}

.status-Cancelled{
    color:#991b1b;
    background:#fecaca;
    padding:4px 10px;
    border-radius:12px;
}
</style>

</head>

<body>

<?php include "admin_header.php"; ?>
<?php include "admin_sidebar.php"; ?>

<div class="main-layout">

<div class="content-area">

<h2>🧾 Manage Orders</h2>

<div class="table-container">
<table>
<tr>
    <th>ID</th>
    <th>User</th>
    <th>Products</th>
    <th>Qty</th>
    <th>Total</th>
    <th>Status</th>
    <th>Update</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['order_id'] ?></td>
    <td><?= $row['user_name'] ?></td>
    <td><?= $row['products'] ?></td>
    <td><?= $row['total_qty'] ?></td>
    <td><?= $row['total_price'] ?></td>

    <td class="status-<?= $row['status'] ?>">
        <?= $row['status'] ?>
    </td>

    <td>
        <form method="post">
            <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">

            <select name="status">
                <option <?= $row['status']=="Pending"?"selected":"" ?>>Pending</option>
                <option <?= $row['status']=="Completed"?"selected":"" ?>>Completed</option>
                <option <?= $row['status']=="Cancelled"?"selected":"" ?>>Cancelled</option>
            </select>

            <button name="update_status">Update</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>

</table>
</div>

</div>
</div>

<script src="admin.js"></script>
<script>lucide.createIcons();</script>

</body>
</html>