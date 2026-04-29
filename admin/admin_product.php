<?php
include "../db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

/* DELETE PRODUCT */
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("SELECT image FROM products WHERE product_id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $resultImg = $stmt->get_result();
    $data = $resultImg->fetch_assoc();

    if($data && !empty($data['image'])){
        $file = "../uploads/" . $data['image'];
        if(file_exists($file)){
            unlink($file);
        }
    }

    $stmt = $conn->prepare("DELETE FROM products WHERE product_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: manage_products.php");
    exit();
}

$result = $conn->query("SELECT * FROM products ORDER BY product_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Products</title>

<!-- ✅ IMPORTANT -->
<link rel="stylesheet" href="style.css">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest"></script>

<style>
body{
    background:#f8fafc;
}

/* ✅ layout FIX */
.main-layout{
    display:flex;
}

/* ✅ content FIX */
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

/* UI */
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

<?php include "admin_header.php"; ?>
<?php include "admin_sidebar.php"; ?>

<div class="main-layout">

<div class="content-area">

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
    <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" width="80">
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
   onclick="return confirm('Delete this product?')">
   Delete
</a>
</td>

</tr>
<?php endwhile; ?>
</tbody>

</table>

</div>

</div>
</div>

<script src="admin.js"></script>
<script>lucide.createIcons();</script>

</body>
</html>