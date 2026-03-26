<?php
session_start();
include "../db.php";

// 检查 admin 登录
if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit();
}

// 更新订单状态
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE order_id=?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    $stmt->close();
}

// 获取订单
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
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin – Manage Orders</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>

/* ✅ 关键修复（防止 header 撑大） */
* {
    box-sizing: border-box;
}

body {
    margin:0;
    font-family:'Inter',sans-serif;
    background: linear-gradient(135deg, #f9d976, #f39f86);
}

/* layout */
.main-layout {
    display: flex;
    margin-top: 70px; /* match header */
}

.content-area {
    margin-left: 230px;
    flex:1;
    padding: 40px;
}

/* TABLE */
.table-container {
    overflow-x:auto;
    background: #fff;
    border-radius:16px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    padding: 20px;
}

table {
    width: 100%;
    border-collapse: separate;
}

th, td {
    padding: 12px;
    text-align: center;
}

th {
    background: linear-gradient(90deg, #6366F1, #4F46E5);
    color: #fff;
}

tr:nth-child(even) td { background: #f9fafb; }
tr:hover td { background: #e0e7ff; }

/* STATUS */
.status-Pending { color:#D97706; font-weight:600; background:#FEF3C7; padding:4px 10px; border-radius:12px; }
.status-Completed { color:#15803D; font-weight:600; background:#D1FAE5; padding:4px 10px; border-radius:12px; }
.status-Cancelled { color:#B91C1C; font-weight:600; background:#FECACA; padding:4px 10px; border-radius:12px; }

select {
    padding:6px 10px;
    border-radius:8px;
}

button {
    padding:6px 12px;
    background: linear-gradient(90deg, #6366F1, #4F46E5);
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
}

</style>
</head>

<body>

<!-- ✅ HEADER -->
<?php include "admin_header.php"; ?>

<div class="main-layout">

    <!-- ✅ SIDEBAR -->
    <?php include "admin_sidebar.php"; ?>

    <!-- CONTENT -->
    <div class="content-area">
        <h2>🧾 Admin – Manage Orders</h2>

        <div class="table-container">
        <table>
        <tr>
            <th>Order ID</th>
            <th>User</th>
            <th>Products</th>
            <th>Quantity</th>
            <th>Total (RM)</th>
            <th>Status</th>
            <th>Update</th>
        </tr>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['order_id'] ?></td>
                <td><?= htmlspecialchars($row['user_name']) ?></td>
                <td><?= $row['products'] ?></td>
                <td><?= $row['total_qty'] ?></td>
                <td><?= number_format($row['total_price'],2) ?></td>

                <td class="status-<?= $row['status'] ?>">
                    <?= $row['status'] ?>
                </td>

                <td>
                    <form method="post">
                        <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">

                        <select name="status">
                            <option value="Pending" <?= $row['status']=="Pending"?"selected":"" ?>>Pending</option>
                            <option value="Completed" <?= $row['status']=="Completed"?"selected":"" ?>>Completed</option>
                            <option value="Cancelled" <?= $row['status']=="Cancelled"?"selected":"" ?>>Cancelled</option>
                        </select>

                        <button name="update_status">Update</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
        <tr>
            <td colspan="7">No orders found.</td>
        </tr>
        <?php endif; ?>

        </table>
        </div>

    </div>

</div>

</body>
</html>

<?php $conn->close(); ?>