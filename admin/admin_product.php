<?php
include "../db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

/* ADD */
if(isset($_POST['add_product'])){
    $name  = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $desc  = $_POST['description'];
    $category = $_POST['category'];

    $cpu = $_POST['cpu'] ?? '';
    $gpu = $_POST['gpu'] ?? '';
    $ram = $_POST['ram'] ?? '';
    $storage = $_POST['storage'] ?? '';
    $motherboard = $_POST['motherboard'] ?? '';
    
    $switch_type = $_POST['switch_type'] ?? '';
    $keyboard_size = $_POST['keyboard_size'] ?? '';

    $dpi = $_POST['dpi'] ?? '';
    $mouse_type = $_POST['mouse_type'] ?? '';

    $imageName = NULL;
    if(!empty($_FILES['image']['name'])){
       $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

$imageName = time() . "_" . uniqid() . "." . $ext;
        if(!is_dir("../uploads")){
    mkdir("../uploads",0777,true);
}

move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/".$imageName);
    }

    $stmt = $conn->prepare("INSERT INTO products 
(product_name, category, price, stock, description, image,
cpu, gpu, ram, storage, motherboard,
switch_type, keyboard_size, dpi, mouse_type)

VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "ssdisssssssssss",
    $name,
    $category,
    $price,
    $stock,
    $desc,
    $imageName,
    $cpu,
    $gpu,
    $ram,
    $storage,
    $motherboard,
    $switch_type,
    $keyboard_size,
    $dpi,
    $mouse_type
);
    $stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

/* UPDATE */
if(isset($_POST['update_product'])){

    $id=$_POST['product_id'];
    $name=$_POST['name'];
    $price=$_POST['price'];
    $stock=$_POST['stock'];
    $category=$_POST['category'];

    $cpu=$_POST['cpu'] ?? '';
    $gpu=$_POST['gpu'] ?? '';
    $ram=$_POST['ram'] ?? '';
    $storage=$_POST['storage'] ?? '';
    $motherboard=$_POST['motherboard'] ?? '';

    $switch_type=$_POST['switch_type'] ?? '';
    $keyboard_size=$_POST['keyboard_size'] ?? '';

    $dpi=$_POST['dpi'] ?? '';
    $mouse_type=$_POST['mouse_type'] ?? '';

    $imageQuery = "";

    if(!empty($_FILES['image']['name'])){

  $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

  $newImage = time() . "_" . uniqid() . "." . $ext;

    move_uploaded_file(
        $_FILES['image']['tmp_name'],
        "../uploads/".$newImage
    );

    $imageQuery = ", image='$newImage'";
    }

    $stmt=$conn->prepare("
    UPDATE products 
    SET product_name=?, price=?, stock=?, category=?,
        cpu=?, gpu=?, ram=?, storage=?, motherboard=?,
        switch_type=?, keyboard_size=?, dpi=?, mouse_type=?
        $imageQuery
    WHERE product_id=?
");

    $stmt->bind_param(
        "sdissssssssssi",
        $name,$price,$stock,$category,
        $cpu,$gpu,$ram,$storage,$motherboard,
        $switch_type,$keyboard_size,$dpi,$mouse_type,
        $id
    );

    $stmt->execute();

    header("Location: ".$_SERVER['PHP_SELF']);
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

.main-content{
    margin-left:270px;
    margin-top:100px;
    padding:30px;
    transition:.3s ease;
}
.top-header{
    position:relative;
    z-index:1000;
}
/* SIDEBAR COLLAPSE */

.sidebar.collapsed ~ .main-content{
    margin-left:95px;
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
.product-search-box{
    margin:20px 0;
    background:#fff;
    padding:12px 16px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

.product-search-box input{
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
    width:55px;
    height:55px;
    border-radius:10px;
    object-fit:cover;
    border:1px solid #ddd;
    background:#fff;
    display:block;
}

/* STATUS */
/* NORMAL BADGE (for table only) */
.table-card .badge{
    position:relative !important;
    top:auto !important;
    right:auto !important;
    left:auto !important;
    bottom:auto !important;

    display:inline-flex;
    align-items:center;
    justify-content:center;

    min-width:120px;

    padding:10px 16px;

    border-radius:999px;

    font-size:12px;
    font-weight:700;

    line-height:1;

    text-align:center;
}
/* NOTIFICATION BADGE ONLY */
/* =========================
NOTIFICATION BADGE ONLY
========================= */

.notification-btn,
.notif{
    position:relative;
}

.notification-btn .badge,
.notif .badge{
    position:absolute !important;

    top:-8px !important;
    right:-8px !important;

    min-width:auto !important;
    width:20px !important;
    height:20px !important;

    padding:0 !important;

    display:flex !important;
    align-items:center !important;
    justify-content:center !important;

    border-radius:50% !important;

    background:#ef233c !important;
    color:#fff !important;

    font-size:10px !important;
    font-weight:700 !important;

    line-height:1 !important;

    z-index:99999 !important;
}
/* ACTIVE */
.stock-active{
    background:#22c55e !important;
    color:#ffffff !important;
}

/* LOW STOCK */
.low{
    background:#facc15 !important;
    color:#111827 !important;
}

/* OUT OF STOCK */
.out{
    background:#ef233c !important;
    color:#ffffff !important;
}

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

/* =========================
   REPLACE YOUR OLD MODAL CSS
========================= */

.modal{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;

    background:rgba(15,23,42,.65);
    backdrop-filter:blur(5px);

    justify-content:center;
    align-items:center;

    z-index:999999;

    padding:20px;

    overflow-y:auto;
}

.modal-box{
    width:100%;
    max-width:720px;
    background:#ffffff;
    border-radius:28px;
    padding:34px;
    box-shadow:0 25px 60px rgba(0,0,0,.25);
    animation:modalFade .25s ease;
    max-height:90vh;
    overflow-y:auto;
}

@keyframes modalFade{
    from{
        opacity:0;
        transform:translateY(20px) scale(.98);
    }
    to{
        opacity:1;
        transform:translateY(0) scale(1);
    }
}

.modal-box h3{
    font-size:38px;
    font-weight:800;
    color:#0f172a;
    margin-bottom:10px;
}

.modal-subtitle{
    color:#64748b;
    font-size:15px;
    margin-bottom:28px;
}

.detail-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
    margin-bottom:25px;
}

.detail-card{
    background:#f8fafc;
    border-radius:18px;
    padding:20px;
}

.detail-label{
    font-size:13px;
    color:#64748b;
    margin-bottom:8px;
}

.detail-value{
    font-size:18px;
    font-weight:700;
    color:#0f172a;
}

.spec-box{
    background:#f8fafc;
    border-radius:18px;
    padding:24px;
    margin-top:10px;
}

.spec-title{
    font-size:28px;
    font-weight:800;
    margin-bottom:18px;
    color:#0f172a;
}

.spec-item{
    display:flex;
    justify-content:space-between;
    padding:12px 0;
    border-bottom:1px solid #e2e8f0;
}

.spec-item:last-child{
    border-bottom:none;
}

.spec-name{
    color:#64748b;
    font-weight:600;
}

.spec-value{
    color:#0f172a;
    font-weight:700;
}

.modal-footer{
    margin-top:25px;
    display:flex;
    justify-content:flex-end;
    gap:12px;
}

.btn-primary{
    background:#2563eb;
    color:#fff;
    border:none;
    padding:12px 22px;
    border-radius:14px;
    cursor:pointer;
    font-weight:700;
    transition:.2s;
}

.btn-primary:hover{
    background:#1d4ed8;
}

.btn-cancel{
    background:#e2e8f0;
    color:#0f172a;
    border:none;
    padding:12px 22px;
    border-radius:14px;
    cursor:pointer;
    font-weight:700;
}

.modal-box input,
.modal-box textarea,
.modal-box select{
    width:100%;
    background:#f8fafc;
    border:1px solid #dbe2ea;
    border-radius:14px;
    padding:14px 16px;
    margin-bottom:14px;
    font-size:15px;
    outline:none;
}

.modal-box input:focus,
.modal-box textarea:focus,
.modal-box select:focus{
    border-color:#2563eb;
    background:#fff;
}

.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:16px;
}

.form-full{
    grid-column:1 / -1;
}

.preview{
    width:110px;
    height:110px;
    border-radius:18px;
    object-fit:cover;
    border:2px solid #dbeafe;
    margin-bottom:18px;
    display:none;
}

.form-control{
    width:100%;
    padding:10px;
    margin-bottom:10px;
    border-radius:8px;
    border:1px solid #ddd;
}
</style>
</head>

<body>

<?php
if(isset($_SESSION['role']) && $_SESSION['role']=="super_admin"){
    include "sadmin_sidebar.php";
}else{
    include "admin_sidebar.php";
}
?>
<div class="top-header">
<?php include "admin_header.php"; ?>
</div>

<div class="main-content">

<div class="page-header">
<h2>Products</h2>



</div>

<div class="product-search-box">
<input id="searchInput" placeholder="Search products...">
</div>

<div class="table-card">

<table>
<thead>
<tr>
<th>Product ID</th>
<th>Product</th>
<th>Category</th>
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
<td>Product #<?= $row['product_id'] ?></td>

<td style="display:flex;gap:10px;align-items:center;">

<?php

$image = trim($row['image']);

?>

<?php if(!empty($image)){ ?>

<img 
    src="../uploads/<?= rawurlencode($image) ?>"
    class="product-img"
    onerror="this.src='https://via.placeholder.com/55';"
>

<?php } else { ?>

<img 
    src="https://via.placeholder.com/55"
    class="product-img"
>

<?php } ?>

<?= htmlspecialchars($row['product_name']) ?>

</td>

<td><?= $row['category'] ?></td>
<td>RM <?= number_format($row['price'],2) ?></td>
<td><?= $row['stock'] ?></td>

<td>
    <span class="badge <?= $status == 'active' ? 'stock-active' : $status ?>">
        <?= $text ?>
    </span>
</td>

<td class="actions">

<i class="view" data-lucide="eye"
onclick="viewProduct(
'<?= $row['product_name'] ?>',
'<?= $row['category'] ?>',
'<?= $row['price'] ?>',
'<?= $row['stock'] ?>',
'<?= $row['cpu'] ?>',
'<?= $row['gpu'] ?>',
'<?= $row['ram'] ?>',
'<?= $row['storage'] ?>',
'<?= $row['motherboard'] ?>',
'<?= $row['switch_type'] ?>',
'<?= $row['keyboard_size'] ?>',
'<?= $row['dpi'] ?>',
'<?= $row['mouse_type'] ?>'
)"
></i>

<i class="edit" data-lucide="pencil"
onclick="editProduct(
'<?= $row['product_id'] ?>',
'<?= $row['product_name'] ?>',
'<?= $row['price'] ?>',
'<?= $row['stock'] ?>',
'<?= $row['category'] ?>',
'<?= $row['cpu'] ?>',
'<?= $row['gpu'] ?>',
'<?= $row['ram'] ?>',
'<?= $row['storage'] ?>',
'<?= $row['motherboard'] ?>',
'<?= $row['switch_type'] ?>',
'<?= $row['keyboard_size'] ?>',
'<?= $row['dpi'] ?>',
'<?= $row['mouse_type'] ?>'
)"
></i>

</td>
</tr>

<?php endwhile; ?>

</tbody>
</table>

</div>
</div>

<!-- =========================
VIEW MODAL REPLACE FULL
========================= -->

<div class="modal" id="viewModal">

<div class="modal-box">

<h3>Product Details</h3>

<div class="modal-subtitle">
View complete product information
</div>

<div class="detail-grid">

<div class="detail-card">
<div class="detail-label">Product Name</div>
<div class="detail-value" id="v_name"></div>
</div>

<div class="detail-card">
<div class="detail-label">Category</div>
<div class="detail-value" id="v_category"></div>
</div>

<div class="detail-card">
<div class="detail-label">Price</div>
<div class="detail-value" id="v_price"></div>
</div>

<div class="detail-card">
<div class="detail-label">Stock</div>
<div class="detail-value" id="v_stock"></div>
</div>

</div>

<div class="spec-box">

<div class="spec-title">
Specifications
</div>

<div id="v_specs"></div>

</div>

<div class="modal-footer">

<button class="btn-cancel"
onclick="closeModal('viewModal')">
Close
</button>

</div>

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

<div class="mb-3">
<select 
    name="category" 
    id="category"
    onchange="toggleSpecs()"
    style="
        width:100%;
        padding:10px;
        border-radius:8px;
        border:1px solid #ddd;
    "
    required
>
    <option value="">Select Category</option>
    <option value="PC">PC</option>
    <option value="Laptop">Laptop</option>
    <option value="Keyboard">Keyboard</option>
    <option value="Mouse">Mouse</option>
</select>
</div>

<div id="pcFields" style="display:none; margin-top:10px;">

    <div class="mb-3">
        <input name="cpu" class="form-control" placeholder="CPU">
    </div>

    <div class="mb-3">
        <input name="gpu" class="form-control" placeholder="GPU">
    </div>

    <div class="mb-3">
        <input name="ram" class="form-control" placeholder="RAM">
    </div>

    <div class="mb-3">
        <input name="storage" class="form-control" placeholder="Storage">
    </div>

    <div class="mb-3">
        <input name="motherboard" class="form-control" placeholder="Motherboard">
    </div>

</div>



<!-- KEYBOARD SPECS -->
<div id="keyboardFields" style="display:none; margin-top:10px;">

    <div class="mb-3">
        <input name="switch_type" class="form-control" placeholder="Switch Type">
    </div>

    <div class="mb-3">
        <input name="keyboard_size" class="form-control" placeholder="Keyboard Size">
    </div>

</div>

<!-- MOUSE SPECS -->
<div id="mouseFields" style="display:none; margin-top:10px;">

    <div class="mb-3">
        <input name="dpi" class="form-control" placeholder="DPI">
    </div>

    <div class="mb-3">
        <input name="mouse_type" class="form-control" placeholder="Mouse Type">
    </div>

</div>

<div id="laptopFields" style="display:none; margin-top:10px;">
    <!-- laptop specs here -->
</div>

<textarea 
name="description"
placeholder="Description"
style="
width:100%;
padding:10px;
margin-bottom:12px;
border-radius:8px;
border:1px solid #ddd;
"
></textarea>

<input type="file" name="image" onchange="previewImage(event)">

<div class="modal-footer">
<button type="button" class="btn-cancel" onclick="closeModal('addModal')">Cancel</button>
<button class="btn-primary" name="add_product">Add</button>
</div>

</form>
</div>
</div>

<!-- =========================
EDIT PRODUCT MODAL
========================= -->

<div class="modal" id="editModal">

<div class="modal-box">

<h3>Edit Product</h3>

<div class="modal-subtitle">
Update product information
</div>

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="product_id" id="edit_id">

<!-- TOP INFO -->
<div class="form-grid">

<div>
<label class="detail-label">Product Name</label>

<input
id="edit_name"
name="name"
placeholder="Product Name"
required>
</div>

<div>
<label class="detail-label">Category</label>

<select
id="edit_category"
name="category"
onchange="toggleEditSpecs()"
required>

<option value="PC">PC</option>
<option value="Laptop">Laptop</option>
<option value="Keyboard">Keyboard</option>
<option value="Mouse">Mouse</option>

</select>
</div>

<div>
<label class="detail-label">Price (RM)</label>

<input
id="edit_price"
name="price"
type="number"
step="0.01"
placeholder="0.00"
required>
</div>

<div>
<label class="detail-label">Stock Quantity</label>

<input
id="edit_stock"
name="stock"
type="number"
placeholder="0"
required>
</div>

<div class="form-full">
<label class="detail-label">Product Image</label>

<input type="file" name="image">
</div>

</div>

<!-- PC -->
<div id="edit_pcFields" class="spec-box">

<div class="spec-title">
PC Specifications
</div>

<div class="form-grid">

<div>
<label class="detail-label">CPU</label>
<input id="edit_cpu" name="cpu" placeholder="CPU">
</div>

<div>
<label class="detail-label">GPU</label>
<input id="edit_gpu" name="gpu" placeholder="GPU">
</div>

<div>
<label class="detail-label">RAM</label>
<input id="edit_ram" name="ram" placeholder="RAM">
</div>

<div>
<label class="detail-label">Storage</label>
<input id="edit_storage" name="storage" placeholder="Storage">
</div>

<div class="form-full">
<label class="detail-label">Motherboard</label>
<input id="edit_motherboard" name="motherboard" placeholder="Motherboard">
</div>

</div>

</div>

<!-- KEYBOARD -->
<div id="edit_keyboardFields" class="spec-box">

<div class="spec-title">
Keyboard Specifications
</div>

<div class="form-grid">

<div>
<label class="detail-label">Switch Type</label>
<input id="edit_switch_type" name="switch_type" placeholder="Switch Type">
</div>

<div>
<label class="detail-label">Keyboard Size</label>
<input id="edit_keyboard_size" name="keyboard_size" placeholder="Keyboard Size">
</div>

</div>

</div>

<!-- MOUSE -->
<div id="edit_mouseFields" class="spec-box">

<div class="spec-title">
Mouse Specifications
</div>

<div class="form-grid">

<div>
<label class="detail-label">DPI</label>
<input id="edit_dpi" name="dpi" placeholder="DPI">
</div>

<div>
<label class="detail-label">Mouse Type</label>
<input id="edit_mouse_type" name="mouse_type" placeholder="Mouse Type">
</div>

</div>

</div>

<!-- LAPTOP -->
<div id="edit_laptopFields" class="spec-box">

<div class="spec-title">
Laptop Specifications
</div>

<div class="form-grid">

<div>
<label class="detail-label">CPU</label>
<input name="cpu" placeholder="CPU">
</div>

<div>
<label class="detail-label">GPU</label>
<input name="gpu" placeholder="GPU">
</div>

<div>
<label class="detail-label">RAM</label>
<input name="ram" placeholder="RAM">
</div>

<div>
<label class="detail-label">Storage</label>
<input name="storage" placeholder="Storage">
</div>

</div>

</div>

<div class="modal-footer">

<button
type="button"
class="btn-cancel"
onclick="closeModal('editModal')">
Cancel
</button>

<button
class="btn-primary"
name="update_product">
Save Changes
</button>

</div>

</form>

</div>
</div>

<script src="admin.js"></script>
<script>
lucide.createIcons();

/* MODAL */
function openModal(id){

    let modal = document.getElementById(id);

    modal.style.display = "flex";

    document.body.style.overflow = "hidden";

    /* HIDE HEADER */
    let header = document.querySelector(".top-header");

    if(header){
        header.style.zIndex = "1";
    }

    if(id === 'addModal'){
        document.getElementById("category").value = "";
        toggleSpecs();
    }
}

function closeModal(id){

    let modal = document.getElementById(id);

    modal.style.display = "none";

    document.body.style.overflow = "auto";

    /* RESTORE HEADER */
    let header = document.querySelector(".top-header");

    if(header){
        header.style.zIndex = "1000";
    }
}

/* =========================
REPLACE viewProduct()
========================= */

function viewProduct(
name,
category,
price,
stock,
cpu,
gpu,
ram,
storage,
motherboard,
switch_type,
keyboard_size,
dpi,
mouse_type
){

openModal('viewModal');

document.getElementById("v_name").innerHTML = name;
document.getElementById("v_category").innerHTML = category;
document.getElementById("v_price").innerHTML = "RM " + parseFloat(price).toLocaleString();
document.getElementById("v_stock").innerHTML = stock;

let specs = "";

if(category === "PC"){

specs += `
<div class="spec-item">
<div class="spec-name">CPU</div>
<div class="spec-value">${cpu}</div>
</div>

<div class="spec-item">
<div class="spec-name">GPU</div>
<div class="spec-value">${gpu}</div>
</div>

<div class="spec-item">
<div class="spec-name">RAM</div>
<div class="spec-value">${ram}</div>
</div>

<div class="spec-item">
<div class="spec-name">Storage</div>
<div class="spec-value">${storage}</div>
</div>

<div class="spec-item">
<div class="spec-name">Motherboard</div>
<div class="spec-value">${motherboard}</div>
</div>
`;

}

else if(category === "Keyboard"){

specs += `
<div class="spec-item">
<div class="spec-name">Switch Type</div>
<div class="spec-value">${switch_type}</div>
</div>

<div class="spec-item">
<div class="spec-name">Keyboard Size</div>
<div class="spec-value">${keyboard_size}</div>
</div>
`;

}

else if(category === "Mouse"){

specs += `
<div class="spec-item">
<div class="spec-name">DPI</div>
<div class="spec-value">${dpi}</div>
</div>

<div class="spec-item">
<div class="spec-name">Mouse Type</div>
<div class="spec-value">${mouse_type}</div>
</div>
`;

}

document.getElementById("v_specs").innerHTML = specs;

}

/* =========================
EDIT PRODUCT
========================= */

function editProduct(
id,name,price,stock,category,
cpu,gpu,ram,storage,motherboard,
switch_type,keyboard_size,dpi,mouse_type
){

openModal('editModal');

document.getElementById("edit_id").value = id;
document.getElementById("edit_name").value = name || "";
document.getElementById("edit_price").value = price || "";
document.getElementById("edit_stock").value = stock || "";

document.getElementById("edit_category").value = category || "";

document.getElementById("edit_cpu").value = cpu || "";
document.getElementById("edit_gpu").value = gpu || "";
document.getElementById("edit_ram").value = ram || "";
document.getElementById("edit_storage").value = storage || "";
document.getElementById("edit_motherboard").value = motherboard || "";

document.getElementById("edit_switch_type").value = switch_type || "";
document.getElementById("edit_keyboard_size").value = keyboard_size || "";

document.getElementById("edit_dpi").value = dpi || "";
document.getElementById("edit_mouse_type").value = mouse_type || "";

toggleEditSpecs();

}

/* =========================
TOGGLE EDIT CATEGORY SPECS
========================= */

function toggleEditSpecs(){

let category =
document.getElementById("edit_category").value;

/* HIDE ALL */
document.getElementById("edit_pcFields").style.display = "none";
document.getElementById("edit_keyboardFields").style.display = "none";
document.getElementById("edit_mouseFields").style.display = "none";
document.getElementById("edit_laptopFields").style.display = "none";

/* SHOW SELECTED */
if(category === "PC"){

document.getElementById("edit_pcFields").style.display = "block";

}

else if(category === "Keyboard"){

document.getElementById("edit_keyboardFields").style.display = "block";

}

else if(category === "Mouse"){

document.getElementById("edit_mouseFields").style.display = "block";

}

else if(category === "Laptop"){

document.getElementById("edit_laptopFields").style.display = "block";

}

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
function toggleSpecs(){

    let category = document.getElementById("category").value;

    // hide all first
    document.getElementById("pcFields").style.display = "none";
    document.getElementById("keyboardFields").style.display = "none";
    document.getElementById("mouseFields").style.display = "none";
    document.getElementById("laptopFields").style.display = "none";

    // show based on selection
    if(category === "PC"){
        document.getElementById("pcFields").style.display = "block";
    }
    else if(category === "Keyboard"){
        document.getElementById("keyboardFields").style.display = "block";
    }
    else if(category === "Mouse"){
        document.getElementById("mouseFields").style.display = "block";
    }
    else if(category === "Laptop"){
        document.getElementById("laptopFields").style.display = "block";
    }
}
</script>
<script src="https://unpkg.com/lucide@latest"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="bootstrap.bundle.js"></script>

</body>
</html>