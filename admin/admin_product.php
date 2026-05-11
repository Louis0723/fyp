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
        $imageName = time()."_".$_FILES['image']['name'];
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

    $stmt=$conn->prepare("
        UPDATE products 
        SET product_name=?, price=?, stock=?, category=?,
            cpu=?, gpu=?, ram=?, storage=?, motherboard=?,
            switch_type=?, keyboard_size=?, dpi=?, mouse_type=?
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
<th>Category</th>
<th>Price</th>
<th>Stock</th>
<th>Status</th>
<th>Actions</th>//
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
<?php if(!empty($row['image'])): ?>
    <img 
        src="../uploads/<?= htmlspecialchars(basename($row['image'])) ?>" 
        class="product-img"
    >
<?php else: ?>
    <img 
        src="https://via.placeholder.com/55" 
        class="product-img"
    >
<?php endif; ?>
<?= $row['product_name'] ?>
</td>

<td><?= $row['category'] ?></td>
<td>RM <?= number_format($row['price'],2) ?></td>
<td><?= $row['stock'] ?></td>

<td><span class="badge <?= $status ?>"><?= $text ?></span></td>

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

<!-- VIEW -->
<div class="modal" id="viewModal">
<div class="modal-box">
<h3>Product Details</h3>
<p id="v_name"></p>
<p id="v_category"></p>
<p id="v_price"></p>
<p id="v_stock"></p>

<div id="v_specs"></div>
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

<!-- EDIT -->
<div class="modal" id="editModal">
<div class="modal-box">
<h3>Edit Product</h3>

<form method="POST">
<input type="hidden" name="product_id" id="edit_id">
<input id="edit_name" name="name">
<input id="edit_price" name="price">
<input id="edit_stock" name="stock">

<input type="hidden" id="edit_category" name="category">

<div id="edit_pcFields">
    <input id="edit_cpu">
    <input id="edit_gpu">
    <input id="edit_ram">
    <input id="edit_storage">
    <input id="edit_motherboard">
</div>

<div id="edit_keyboardFields">
    <input id="edit_switch_type">
    <input id="edit_keyboard_size">
</div>

<div id="edit_mouseFields">
    <input id="edit_dpi">
    <input id="edit_mouse_type">
</div>

<div id="edit_laptopFields">
    <!-- laptop specs here -->
</div>

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
function openModal(id){
    document.getElementById(id).style.display="flex";

    if(id === 'addModal'){
        document.getElementById("category").value = "";
        toggleSpecs(); // reset view
    }
}
function closeModal(id){document.getElementById(id).style.display="none";}

/* VIEW */
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

document.getElementById("v_name").innerText = "Name: " + name;
document.getElementById("v_category").innerText = "Category: " + category;
document.getElementById("v_price").innerText = "Price: RM " + price;
document.getElementById("v_stock").innerText = "Stock: " + stock;

let specs = "";

if(category === "PC"){

    specs += "<p><b>CPU:</b> " + cpu + "</p>";
    specs += "<p><b>GPU:</b> " + gpu + "</p>";
    specs += "<p><b>RAM:</b> " + ram + "</p>";
    specs += "<p><b>Storage:</b> " + storage + "</p>";
    specs += "<p><b>Motherboard:</b> " + motherboard + "</p>";
}

else if(category === "Keyboard"){

    specs += "<p><b>Switch Type:</b> " + switch_type + "</p>";
    specs += "<p><b>Keyboard Size:</b> " + keyboard_size + "</p>";
}

else if(category === "Mouse"){

    specs += "<p><b>DPI:</b> " + dpi + "</p>";
    specs += "<p><b>Mouse Type:</b> " + mouse_type + "</p>";
}

document.getElementById("v_specs").innerHTML = specs;

}

/* EDIT */
function editProduct(
id,name,price,stock,category,
cpu,gpu,ram,storage,motherboard,
switch_type,keyboard_size,dpi,mouse_type
){
    openModal('editModal');

    edit_id.value = id;
    edit_name.value = name;
    edit_price.value = price;
    edit_stock.value = stock;

    document.getElementById("edit_category").value = category;

    // hide all first
    document.getElementById("edit_pcFields").style.display = "none";
    document.getElementById("edit_keyboardFields").style.display = "none";
    document.getElementById("edit_mouseFields").style.display = "none";
    document.getElementById("edit_laptopFields").style.display = "none";

    if(category === "PC"){
        document.getElementById("edit_pcFields").style.display = "block";

        edit_cpu.value = cpu;
        edit_gpu.value = gpu;
        edit_ram.value = ram;
        edit_storage.value = storage;
        edit_motherboard.value = motherboard;
    }

    else if(category === "Keyboard"){
        document.getElementById("edit_keyboardFields").style.display = "block";

        edit_switch_type.value = switch_type;
        edit_keyboard_size.value = keyboard_size;
    }

    else if(category === "Mouse"){
        document.getElementById("edit_mouseFields").style.display = "block";

        edit_dpi.value = dpi;
        edit_mouse_type.value = mouse_type;
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

</body>
</html>