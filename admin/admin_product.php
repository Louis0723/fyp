<?php
include "../db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

/* ADD */
if(isset($_POST['add_product'])){
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    $imageName = "";
    if(!empty($_FILES['image']['name'])){
        $imageName = time()."_".$_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/".$imageName);
    }

    $stmt = $conn->prepare("INSERT INTO products (product_name, price, stock, image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdis", $name, $price, $stock, $imageName);
    $stmt->execute();

    header("Location: manage_products.php");
    exit();
}

/* UPDATE */
if(isset($_POST['update_product'])){
    $id=$_POST['product_id'];
    $name=$_POST['name'];
    $price=$_POST['price'];
    $stock=$_POST['stock'];

    $stmt=$conn->prepare("UPDATE products SET product_name=?,price=?,stock=? WHERE product_id=?");
    $stmt->bind_param("sdii",$name,$price,$stock,$id);
    $stmt->execute();

    header("Location: manage_products.php");
    exit();
}

$result = $conn->query("SELECT * FROM products ORDER BY product_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Products</title>

<link rel="stylesheet" href="style.css?v=2">
<script src="https://unpkg.com/lucide@latest"></script>

<style>
body{font-family:Segoe UI;background:#f5f7fb;}

.content-area{
    margin-left:260px;
    margin-top:100px;
    padding:30px;
}

/* HEADER */
.page-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.btn-add{
    background:#2563eb;
    color:#fff;
    border:none;
    padding:12px 18px;
    border-radius:12px;
    cursor:pointer;
    font-weight:600;
    display:flex;
    gap:6px;
}

/* SEARCH */
.search-box{
    margin:20px 0;
    background:#fff;
    padding:12px 16px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

.search-box input{
    border:none;
    outline:none;
    width:100%;
}

/* TABLE */
.table-card{
    background:#fff;
    border-radius:16px;
    padding:20px;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
}

table{width:100%;border-collapse:collapse;}
th,td{padding:14px;text-align:left;}
tr:hover{background:#f9fbff;}

.product-img{
    width:55px;height:55px;border-radius:10px;
    object-fit:cover;
}

/* STATUS */
/* NORMAL BADGE (for table only) */
.badge{
    position:static;   /* 🔥 THIS FIXES YOUR BUG */
    padding:6px 12px;
    border-radius:12px;
    font-size:12px;
}

/* NOTIFICATION BADGE ONLY */
.notif .badge{
    position:absolute;
    top:-5px;
    right:-5px;
    background:red;
    color:#fff;
    font-size:10px;
    padding:3px 6px;
    border-radius:50%;
}
.active{background:#2563eb;color:#fff;}
.low{background:#facc15;}
.out{background:#ef4444;color:#fff;}

/* ICON */
.actions i{
    padding:8px;
    border-radius:8px;
    cursor:pointer;
    transition:0.2s;
}
.actions i:hover{
    background:#eef2ff;
    transform:scale(1.2);
}
.view{color:#111;}
.edit{color:#2563eb;}

/* MODAL */
.modal{
    display:none;
    position:fixed;
    width:100%;height:100%;
    background:rgba(0,0,0,0.6);
    top:0;left:0;
    justify-content:center;
    align-items:center;
}

.modal-box{
    background:#fff;
    padding:25px;
    border-radius:16px;
    width:420px;
    box-shadow:0 15px 40px rgba(0,0,0,0.2);
}

.modal-box h3{margin-bottom:15px;}

.modal-box input{
    width:100%;
    padding:10px;
    margin-bottom:12px;
    border-radius:8px;
    border:1px solid #ddd;
}

.modal-footer{
    display:flex;
    justify-content:flex-end;
    gap:10px;
}

.btn-primary{
    background:#2563eb;
    color:#fff;
    border:none;
    padding:8px 14px;
    border-radius:8px;
}

.btn-cancel{
    background:#ccc;
    border:none;
    padding:8px 14px;
    border-radius:8px;
}

/* IMAGE PREVIEW */
.preview{
    width:80px;height:80px;
    border-radius:10px;
    margin-bottom:10px;
    object-fit:cover;
    display:none;
}
</style>
</head>

<body>

<?php include "admin_sidebar.php"; ?>
<?php include "admin_header.php"; ?>

<div class="content-area">

<div class="page-header">
<h2>Products</h2>

<button class="btn-add" onclick="openModal('addModal')">
<i data-lucide="plus"></i> Add Product
</button>
</div>

<div class="search-box">
<input id="searchInput" placeholder="Search products...">
</div>

<div class="table-card">

<table>
<thead>
<tr>
<th>SKU</th>
<th>Product</th>
<th>Price</th>
<th>Stock</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>

<tbody>

<?php while($row=$result->fetch_assoc()):
$status="active";$text="Active";
if($row['stock']<=0){$status="out";$text="Out of Stock";}
elseif($row['stock']<10){$status="low";$text="Low Stock";}
?>

<tr class="product-row">
<td>PRD-<?= $row['product_id'] ?></td>

<td style="display:flex;gap:10px;align-items:center;">
<?php if($row['image']): ?>
<img src="../uploads/<?= $row['image'] ?>" class="product-img">
<?php endif; ?>
<?= $row['product_name'] ?>
</td>

<td>RM <?= number_format($row['price'],2) ?></td>
<td><?= $row['stock'] ?></td>

<td><span class="badge <?= $status ?>"><?= $text ?></span></td>

<td class="actions">

<i class="view" data-lucide="eye"
onclick="viewProduct('<?= $row['product_name'] ?>','<?= $row['price'] ?>','<?= $row['stock'] ?>')"></i>

<i class="edit" data-lucide="pencil"
onclick="editProduct('<?= $row['product_id'] ?>','<?= $row['product_name'] ?>','<?= $row['price'] ?>','<?= $row['stock'] ?>')"></i>

</td>
</tr>

<?php endwhile; ?>

</tbody>
</table>

</div>
</div>

<!-- VIEW -->
<div class="modal" id="viewModal">
<div class="modal-box">
<h3>Product Details</h3>
<p id="v_name"></p>
<p id="v_price"></p>
<p id="v_stock"></p>
<button class="btn-cancel" onclick="closeModal('viewModal')">Close</button>
</div>
</div>

<!-- ADD -->
<div class="modal" id="addModal">
<div class="modal-box">
<h3>Add Product</h3>

<form method="POST" enctype="multipart/form-data">

<img id="preview" class="preview">

<input name="name" placeholder="Name" required>
<input name="price" placeholder="Price" required>
<input name="stock" placeholder="Stock" required>

<input type="file" name="image" onchange="previewImage(event)">

<div class="modal-footer">
<button type="button" class="btn-cancel" onclick="closeModal('addModal')">Cancel</button>
<button class="btn-primary" name="add_product">Add</button>
</div>

</form>
</div>
</div>

<!-- EDIT -->
<div class="modal" id="editModal">
<div class="modal-box">
<h3>Edit Product</h3>

<form method="POST">
<input type="hidden" name="product_id" id="edit_id">
<input id="edit_name" name="name">
<input id="edit_price" name="price">
<input id="edit_stock" name="stock">

<div class="modal-footer">
<button type="button" class="btn-cancel" onclick="closeModal('editModal')">Cancel</button>
<button class="btn-primary" name="update_product">Save</button>
</div>

</form>
</div>
</div>

<script src="admin.js"></script>
<script>
lucide.createIcons();

/* MODAL */
function openModal(id){document.getElementById(id).style.display="flex";}
function closeModal(id){document.getElementById(id).style.display="none";}

/* VIEW */
function viewProduct(name,price,stock){
openModal('viewModal');
v_name.innerText="Name: "+name;
v_price.innerText="Price: RM "+price;
v_stock.innerText="Stock: "+stock;
}

/* EDIT */
function editProduct(id,name,price,stock){
openModal('editModal');
edit_id.value=id;
edit_name.value=name;
edit_price.value=price;
edit_stock.value=stock;
}

/* SEARCH */
searchInput.addEventListener("keyup",function(){
let val=this.value.toLowerCase();
document.querySelectorAll(".product-row").forEach(row=>{
row.style.display=row.innerText.toLowerCase().includes(val)?"":"none";
});
});

/* IMAGE PREVIEW */
function previewImage(e){
let reader=new FileReader();
reader.onload=function(){
let img=document.getElementById("preview");
img.src=reader.result;
img.style.display="block";
}
reader.readAsDataURL(e.target.files[0]);
}
</script>

</body>
</html>