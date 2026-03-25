<?php
include "../db.php";
session_start();

// check admin login
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

// DELETE PRODUCT (安全 + 删除图片)
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);

    // 先拿图片名
    $stmt = $conn->prepare("SELECT image FROM products WHERE product_id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $resultImg = $stmt->get_result();
    $data = $resultImg->fetch_assoc();

    // 删除图片文件
    if($data && !empty($data['image'])){
        $file = "../uploads/" . $data['image'];
        if(file_exists($file)){
            unlink($file);
        }
    }

    // 删除产品
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: manage_products.php");
    exit();
}

// GET PRODUCTS
$result = $conn->query("SELECT * FROM products ORDER BY product_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Products</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background:linear-gradient(135deg,#e0f7ff,#c2e9fb);
}

/* 不影响 header */
.main{
    margin-left:240px;
    margin-top:80px;
    padding:20px;
}

/* 页面标题 */
.page-title{
    color:#0072ff;
    font-weight:bold;
}

.card{
    border-radius:15px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

table img{
    border-radius:8px;
}
</style>
</head>

<body>

<!-- HEADER -->
<?php include "admin_header.php"; ?>

<!-- SIDEBAR -->
<?php include "admin_sidebar.php"; ?>

<div class="main">

<h2 class="page-title mb-4">📦 Product List</h2>

<div class="card p-3">

<table class="table table-striped align-middle">

<thead class="table-primary">
<tr>
    <th>ID</th>
    <th>Image</th>
    <th>Name</th>
    <th>Price</th>
    <th>Stock</th>
    <th>Description</th>
    <th>Action</th>
</tr>
</thead>

<tbody>
<?php while($row = $result->fetch_assoc()): ?>
<tr>

<td><?= $row['product_id'] ?></td>

<td>
<?php if(!empty($row['image'])): ?>
    <!-- ✅ 关键修复：路径 + 防缓存 -->
    <img src="../uploads/<?= htmlspecialchars($row['image']) ?>?v=<?= time() ?>" width="80">
<?php else: ?>
    No Image
<?php endif; ?>
</td>

<td><?= htmlspecialchars($row['product_name']) ?></td>

<td>RM <?= number_format($row['price'], 2) ?></td>

<td><?= htmlspecialchars($row['stock']) ?></td>

<td><?= htmlspecialchars($row['description'] ?? '-') ?></td>

<td>
<a href="?delete=<?= $row['product_id'] ?>"
   class="btn btn-danger btn-sm"
   onclick="return confirm('Are you sure to delete this product?')">
   Delete
</a>
</td>

</tr>
<?php endwhile; ?>
</tbody>

</table>

</div>

</div>

</body>
</html>