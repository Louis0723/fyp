<?php
include "../db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

// ADD PRODUCT
if(isset($_POST['add'])){
    $name  = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $desc  = $_POST['description'];

    $imageName = "";

    // 上传图片
    if(!empty($_FILES['image']['name'])){
        $imageName = time() . "_" . $_FILES['image']['name'];

        if(!is_dir("../uploads")){
            mkdir("../uploads", 0777, true);
        }

        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $imageName);
    }

    // insert
    $stmt = $conn->prepare("INSERT INTO products (product_name, price, stock, description, image)
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sdiss", $name, $price, $stock, $desc, $imageName);
    $stmt->execute();

    header("Location: add_product.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Product</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* ✅ 不影响 header */
.main{
    margin-left:240px;
    margin-top:80px;
    padding:30px;
    background: linear-gradient(135deg, #eef2ff, #f8fbff);
    min-height:100vh;
}

/* 标题 */
.main .page-title{
    font-size:28px;
    font-weight:700;
    color:#2c3e50;
    margin-bottom:20px;
}

/* 卡片（玻璃感✨） */
.main .product-card{
    width:420px;
    border-radius:20px;
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(10px);
    box-shadow:0 15px 35px rgba(0,0,0,0.1);
    border:1px solid rgba(255,255,255,0.3);
    transition:0.3s;
}

.main .product-card:hover{
    transform:translateY(-5px);
    box-shadow:0 20px 45px rgba(0,0,0,0.15);
}

/* 输入框 */
.main input,
.main textarea{
    border-radius:12px;
    padding:12px;
    border:1px solid #ddd;
    transition:0.25s;
    font-size:14px;
}

.main input:focus,
.main textarea:focus{
    border-color:#4facfe;
    box-shadow:0 0 8px rgba(79,172,254,0.3);
    outline:none;
}

/* 文件上传 */
.main input[type="file"]{
    background:#f1f6ff;
    cursor:pointer;
}

/* 按钮 */
.main .btn-gradient{
    background: linear-gradient(135deg, #4facfe, #00c6ff);
    border:none;
    padding:12px;
    border-radius:12px;
    font-weight:600;
    color:white;
    transition:0.3s;
}

.main .btn-gradient:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 20px rgba(0,198,255,0.4);
}

/* alert */
.main .alert{
    border-radius:12px;
    font-weight:500;
}
</style>
</head>

<body>

<?php include "admin_header.php"; ?>
<?php include "admin_sidebar.php"; ?>

<div class="main">

<h2 class="page-title">➕ Add Product</h2>

<?php if(isset($_GET['success'])): ?>
<div class="alert alert-success">Product added successfully!</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="card product-card p-4">

    <div class="mb-3">
        <input name="name" class="form-control" placeholder="Product Name" required>
    </div>

    <div class="mb-3">
        <input name="price" type="number" step="0.01" class="form-control" placeholder="Price (RM)" required>
    </div>

    <div class="mb-3">
        <input name="stock" type="number" class="form-control" placeholder="Stock" required>
    </div>

    <div class="mb-3">
        <textarea name="description" class="form-control" rows="3" placeholder="Description"></textarea>
    </div>

    <div class="mb-3">
        <input type="file" name="image" class="form-control">
    </div>

    <button name="add" class="btn btn-gradient w-100">Add Product</button>

</form>

</div>

</body>
</html>