<?php
session_start();
include "../db.php";

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

/* =========================
   ADD PRODUCT
========================= */

if(isset($_POST['save_product'])){

    $name        = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price       = $_POST['price'];
    $stock       = $_POST['stock'];
    $feature     = trim($_POST['feature']);
    $spec        = trim($_POST['specification']);
    $category_id = intval($_POST['category_id']);

    $status = ($stock > 0)
        ? "In-Stock"
        : "Out-Stock";

    /* PRODUCT TAG */

    $product_category = "";

    if(isset($_POST['product_category'])){
        $product_category =
        implode(",", $_POST['product_category']);
    }

    /* =========================
       IMAGE UPLOAD
    ========================== */

    $image_name = "";

    if(isset($_FILES['product_image']) &&
       $_FILES['product_image']['error'] == 0){

        $target_dir = "../uploads/products/";

        if(!is_dir($target_dir)){
            mkdir($target_dir,0777,true);
        }

        $extension = strtolower(
            pathinfo(
                $_FILES['product_image']['name'],
                PATHINFO_EXTENSION
            )
        );

        $allowed = ['jpg','jpeg','png','gif','webp'];

        if(in_array($extension,$allowed)){

            $image_name =
            time().'_'.uniqid().'.'.$extension;

            move_uploaded_file(
                $_FILES['product_image']['tmp_name'],
                $target_dir.$image_name
            );
        }
    }

    /* =========================
       INSERT PRODUCT
    ========================== */

    $stmt = $conn->prepare("
        INSERT INTO products
        (
            product_category,
            category_id,
            product_name,
            product_description,
            product_price,
            product_stock,
            product_status,
            product_feature,
            product_specification,
            image,
            active
        )

        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");

    $stmt->bind_param(
        "sissdissss",
        $product_category,
        $category_id,
        $name,
        $description,
        $price,
        $stock,
        $status,
        $feature,
        $spec,
        $image_name
    );

    $stmt->execute();

    echo "
    <script>
        alert('Product Added Successfully');
        window.location='add_product.php';
    </script>
    ";
}
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Add Product</title>

<link rel="stylesheet" href="style.css?v=2">

<script src="https://unpkg.com/lucide@latest"></script>

<style>

.main-content{
    margin-left:275px;
    margin-top:100px;
    padding:28px;
}

/* TITLE */

.page-title{
    font-size:40px;
    font-weight:800;
    color:#0f172a;
}

.page-sub{
    color:#64748b;
    margin-top:6px;
    margin-bottom:28px;
}

/* CARD */

.product-card{
    background:#fff;
    border-radius:24px;
    padding:30px;
    box-shadow:0 10px 25px rgba(0,0,0,.05);
}

/* GRID */

.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:22px;
}

.full{
    grid-column:1/3;
}

/* GROUP */

.form-group{
    display:flex;
    flex-direction:column;
}

.form-group label{
    margin-bottom:8px;
    font-size:14px;
    font-weight:700;
    color:#0f172a;
}

/* INPUT */

.form-group input,
.form-group textarea,
.form-group select{
    width:100%;
    padding:14px;
    border-radius:14px;
    border:1px solid #dbe2ea;
    background:#f8fafc;
    font-size:14px;
    outline:none;
}

.form-group textarea{
    min-height:130px;
    resize:vertical;
}

/* CHECKBOX */

.checkbox-group{
    display:flex;
    gap:22px;
    margin-top:10px;
}

.checkbox-group label{
    display:flex;
    align-items:center;
    gap:8px;
    font-weight:600;
}

/* IMAGE */

.image-upload{
    border:2px dashed #cbd5e1;
    border-radius:18px;
    padding:35px;
    text-align:center;
    background:#f8fafc;
    cursor:pointer;
}

.image-upload:hover{
    border-color:#2563eb;
}

.image-upload i{
    width:50px;
    height:50px;
    color:#2563eb;
}

.preview-image{
    width:100%;
    max-height:280px;
    object-fit:contain;
    border-radius:18px;
    margin-top:20px;
    display:none;
}

/* BUTTON */

.submit-btn{
    width:100%;
    height:54px;
    border:none;
    border-radius:16px;
    background:#2563eb;
    color:#fff;
    font-size:15px;
    font-weight:700;
    cursor:pointer;
}

.submit-btn:hover{
    background:#1d4ed8;
}

@media(max-width:900px){

    .form-grid{
        grid-template-columns:1fr;
    }

    .full{
        grid-column:auto;
    }

    .main-content{
        margin-left:0;
    }
}

</style>

</head>

<body>

<!-- SIDEBAR -->

<?php
if(isset($_SESSION['role']) &&
$_SESSION['role']=="super_admin"){

    include "sadmin_sidebar.php";

}else{

    include "admin_sidebar.php";
}
?>

<!-- HEADER -->

<?php include "admin_header.php"; ?>

<!-- MAIN -->

<div class="main-content">

<div class="page-title">
    Add Product
</div>

<div class="page-sub">
    Create and manage your gaming inventory.
</div>

<div class="product-card">

<form method="POST"
      enctype="multipart/form-data">

<div class="form-grid">

    <!-- NAME -->
    <div class="form-group">

        <label>Product Name</label>

        <input type="text"
               name="product_name"
               required>

    </div>

    <!-- CATEGORY -->
    <div class="form-group">

        <label>Product Type</label>

        <select name="category" required>

    <option value="">
        Select Category
    </option>

    <option value="CPU">
        CPU
    </option>

    <option value="GPU">
        GPU
    </option>

    <option value="PC">
        PC
    </option>

    <option value="Keyboard">
        Keyboard
    </option>

    <option value="Mouse">
        Mouse
    </option>

    <option value="RAM">
        RAM
    </option>

    <option value="Storage">
        Storage
    </option>

</select>

    </div>

    <!-- PRICE -->
    <div class="form-group">

        <label>Product Price (RM)</label>

        <input type="number"
               step="0.01"
               name="price"
               required>

    </div>

    <!-- STOCK -->
    <div class="form-group">

        <label>Product Stock</label>

        <input type="number"
               name="stock"
               required>

    </div>

    <!-- TAG -->
    <div class="form-group full">

        <label>Product Category</label>

        <div class="checkbox-group">

            <label>
                <input type="checkbox"
                       name="product_category[]"
                       value="Hot">
                Hot
            </label>

            <label>
                <input type="checkbox"
                       name="product_category[]"
                       value="Famous">
                Famous
            </label>

            <label>
                <input type="checkbox"
                       name="product_category[]"
                       value="New">
                New
            </label>

        </div>

    </div>

    <!-- DESCRIPTION -->
    <div class="form-group full">

        <label>Description</label>

        <textarea name="description"
                  required></textarea>

    </div>

    <!-- FEATURE -->
    <div class="form-group full">

        <label>Product Features</label>

        <textarea name="feature"
                  required></textarea>

    </div>

    <!-- SPEC -->
    <div class="form-group full">

        <label>Product Specification</label>

        <textarea name="specification"
                  required></textarea>

    </div>

    <!-- IMAGE -->
    <div class="form-group full">

        <label>Product Image</label>

        <label class="image-upload">

            <input type="file"
                   name="product_image"
                   id="imageInput"
                   hidden>

            <i data-lucide="image-plus"></i>

            <p style="margin-top:10px;">
                Click to upload image
            </p>

            <img id="preview"
                 class="preview-image">

        </label>

    </div>

    <!-- BUTTON -->
    <div class="form-group full">

        <button type="submit"
                name="save_product"
                class="submit-btn">

            Add Product

        </button>

    </div>

</div>

</form>

</div>

</div>

<script>

lucide.createIcons();

/* IMAGE PREVIEW */

const imageInput =
document.getElementById("imageInput");

const preview =
document.getElementById("preview");

imageInput.addEventListener("change", function(){

    const file = this.files[0];

    if(file){

        const reader = new FileReader();

        reader.onload = function(e){

            preview.src = e.target.result;

            preview.style.display = "block";
        }

        reader.readAsDataURL(file);
    }
});

</script>
<script src="https://unpkg.com/lucide@latest"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="bootstrap.bundle.js"></script>

<script src="admin.js"></script>
</body>
</html>