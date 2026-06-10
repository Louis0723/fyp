<?php
session_start();
include "../db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

/* =========================
   LOAD the CATEGORIES
========================= */
$categories = [];
$result = mysqli_query($conn, "
    SELECT category_id, category_name
    FROM category
    ORDER BY category_name ASC
");

while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}
if (isset($_POST['save_product'])) {

    $name        = trim($_POST['product_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $stock       = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $feature     = trim($_POST['feature'] ?? '');
    $spec        = trim($_POST['specification'] ?? '');
    $category_id  = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

    $cpu         = trim($_POST['cpu'] ?? '');
    $gpu         = trim($_POST['gpu'] ?? '');
    $ram         = trim($_POST['ram'] ?? '');
    $storage     = trim($_POST['storage'] ?? '');
    $motherboard = trim($_POST['motherboard'] ?? '');

    $switch_type      = trim($_POST['switch_type'] ?? '');
    $keyboard_size    = trim($_POST['keyboard_size'] ?? '');
    $keyboard_battery = trim($_POST['keyboard_battery'] ?? '');

    $dpi             = trim($_POST['dpi'] ?? '');
    $mouse_type      = trim($_POST['mouse_type'] ?? '');
    $mouse_battery   = trim($_POST['mouse_battery'] ?? '');

    $screen_size = trim($_POST['screen_size'] ?? '');

    if ($name === '') {
        echo "<script>alert('Product Name is required'); window.history.back();</script>";
        exit();
    }

    if ($price <= 0) {
        echo "<script>alert('Product Price must be more than RM 0.00'); window.history.back();</script>";
        exit();
    }

    if ($stock < 0) {
        echo "<script>alert('Product Stock cannot be less than 0'); window.history.back();</script>";
        exit();
    }

    if ($category_id <= 0) {
        echo "<script>alert('Product Type is required'); window.history.back();</script>";
        exit();
    }

    /* Get category name */
    $category_name = '';
    $stmtCat = $conn->prepare("SELECT category_name FROM category WHERE category_id = ? LIMIT 1");
    $stmtCat->bind_param("i", $category_id);
    $stmtCat->execute();
    $resultCat = $stmtCat->get_result();

    if ($resultCat && $resultCat->num_rows > 0) {
        $rowCat = $resultCat->fetch_assoc();
        $category_name = trim($rowCat['category_name']);
    } else {
        echo "<script>alert('Invalid category selected'); window.history.back();</script>";
        exit();
    }

    $category_key = strtolower($category_name);

    /* =========================
       VALIDATE BY PRODUCT TYPE
    ========================== */
    if (stripos($category_key, 'gaming pc') !== false) {
        if ($cpu === '' || $gpu === '' || $ram === '' || $storage === '' || $motherboard === '') {
            echo "<script>alert('Gaming PC requires CPU, GPU, RAM, Storage, and Motherboard'); window.history.back();</script>";
            exit();
        }
    }

    if (stripos($category_key, 'keyboard') !== false) {
        if ($switch_type === '' || $keyboard_size === '' || $keyboard_battery === '') {
            echo "<script>alert('Keyboard requires Switch Type, Keyboard Size, and Battery'); window.history.back();</script>";
            exit();
        }
    }

    if (stripos($category_key, 'mouse') !== false) {
        if ($dpi === '' || $mouse_type === '' || $mouse_battery === '') {
            echo "<script>alert('Mouse requires DPI, Mouse Type, and Battery'); window.history.back();</script>";
            exit();
        }
    }

    if (stripos($category_key, 'monitor') !== false) {
        if ($screen_size === '') {
            echo "<script>alert('Monitor requires Screen Size'); window.history.back();</script>";
            exit();
        }
    }

    /* Use one battery column in DB */
    $battery = '';
    if (stripos($category_key, 'keyboard') !== false) {
        $battery = $keyboard_battery;
    } elseif (stripos($category_key, 'mouse') !== false) {
        $battery = $mouse_battery;
    }

    /* Combine feature + spec into specs column */
    $combined_specs = '';
    if ($feature !== '') {
        $combined_specs .= "Features:\n" . $feature . "\n\n";
    }
    if ($spec !== '') {
        $combined_specs .= "Specification:\n" . $spec;
    }

    /* =========================
       IMAGE UPLOAD
    ========================== */
    $image_name = "";

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $target_dir = "../uploads/products/";

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $extension = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($extension, $allowed)) {
            $image_name = time() . '_' . uniqid() . '.' . $extension;

            move_uploaded_file(
                $_FILES['product_image']['tmp_name'],
                $target_dir . $image_name
            );
        }
    }

    /* =========================
       INSERT PRODUCT
    ========================== */
    $stmt = $conn->prepare("
        INSERT INTO products
        (
            product_name,
            description,
            price,
            stock,
            image,
            cpu,
            gpu,
            ram,
            storage,
            motherboard,
            category,
            switch_type,
            keyboard_size,
            dpi,
            mouse_type,
            specs,
            screen_size,
            battery
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssdissssssssssssss",
        $name,
        $description,
        $price,
        $stock,
        $image_name,
        $cpu,
        $gpu,
        $ram,
        $storage,
        $motherboard,
        $category_name,
        $switch_type,
        $keyboard_size,
        $dpi,
        $mouse_type,
        $combined_specs,
        $screen_size,
        $battery
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>

    <link rel="stylesheet" href="style.css?v=2">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        .main-content{
            margin-left:275px;
            margin-top:100px;
            padding:28px;
        }

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

        .product-card{
            background:#fff;
            border-radius:24px;
            padding:30px;
            box-shadow:0 10px 25px rgba(0,0,0,.05);
        }

        .form-grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:22px;
        }

        .full{
            grid-column:1/3;
        }

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

        .checkbox-group{
            display:flex;
            gap:22px;
            margin-top:10px;
            flex-wrap:wrap;
        }

        .checkbox-group label{
            display:flex;
            align-items:center;
            gap:8px;
            font-weight:600;
        }

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

        .conditional-group{
            display:none;
            grid-column:1/3;
            margin-top:6px;
        }

        .sub-grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:22px;
        }

        @media(max-width:900px){
            .form-grid,
            .sub-grid{
                grid-template-columns:1fr;
            }

            .full,
            .conditional-group{
                grid-column:auto;
            }

            .main-content{
                margin-left:0;
            }
        }

        .admin-header .avatar-btn{
            width:42px !important;
            height:42px !important;
            min-width:42px !important;
            min-height:42px !important;
            border-radius:50% !important;
            overflow:hidden !important;
            padding:0 !important;
        }

        .admin-header .avatar-btn img{
            width:100% !important;
            height:100% !important;
            object-fit:cover !important;
            object-position:center !important;
            border-radius:50% !important;
            display:block !important;
        }

        .admin-header .profile-avatar{
            width:52px !important;
            height:52px !important;
            border-radius:50% !important;
            overflow:hidden !important;
        }

        .admin-header .profile-avatar img{
            width:100% !important;
            height:100% !important;
            object-fit:cover !important;
            object-position:center !important;
            border-radius:50% !important;
            display:block !important;
        }
    </style>
</head>

<body>

<?php
if (isset($_SESSION['role']) && $_SESSION['role'] == "super_admin") {
    include "sadmin_sidebar.php";
} else {
    include "admin_sidebar.php";
}
?>

<?php include "admin_header.php"; ?>

<div class="main-content">

    <div class="page-title">Add Product</div>
    <div class="page-sub">Create and manage your gaming inventory.</div>

    <div class="product-card">
        <form method="POST" enctype="multipart/form-data" id="productForm">
            <div class="form-grid">

                <div class="form-group">
                    <label>Product Name <span style="color:red">*</span></label>
                    <input type="text" name="product_name" required>
                </div>

                <div class="form-group">
                    <label>Product Type <span style="color:red">*</span></label>
                    <select name="category_id" id="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['category_id'] ?>">
                                <?= htmlspecialchars($category['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Product Price (RM) <span style="color:red">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="price" required>
                </div>

                <div class="form-group">
                    <label>Product Stock <span style="color:red">*</span></label>
                    <input type="number" name="stock" min="0" required>
                </div>

                <div class="form-group full">
                    <label>Description</label>
                    <textarea name="description"></textarea>
                </div>

                <!-- GAMING PC -->
                <div id="gamingpc_fields" class="conditional-group">
                    <div class="sub-grid">
                        <div class="form-group">
                            <label>CPU <span style="color:red">*</span></label>
                            <input type="text" name="cpu">
                        </div>
                        <div class="form-group">
                            <label>GPU <span style="color:red">*</span></label>
                            <input type="text" name="gpu">
                        </div>
                        <div class="form-group">
                            <label>RAM <span style="color:red">*</span></label>
                            <input type="text" name="ram">
                        </div>
                        <div class="form-group">
                            <label>Storage <span style="color:red">*</span></label>
                            <input type="text" name="storage">
                        </div>
                        <div class="form-group">
                            <label>Motherboard <span style="color:red">*</span></label>
                            <input type="text" name="motherboard">
                        </div>
                    </div>
                </div>

                <!-- KEYBOARD -->
                <div id="keyboard_fields" class="conditional-group">
                    <div class="sub-grid">
                        <div class="form-group">
                            <label>Switch Type <span style="color:red">*</span></label>
                            <input type="text" name="switch_type">
                        </div>
                        <div class="form-group">
                            <label>Keyboard Size <span style="color:red">*</span></label>
                            <input type="text" name="keyboard_size">
                        </div>
                        <div class="form-group">
                            <label>Battery <span style="color:red">*</span></label>
                            <input type="text" name="keyboard_battery">
                        </div>
                    </div>
                </div>

                <!-- MOUSE -->
                <div id="mouse_fields" class="conditional-group">
                    <div class="sub-grid">
                        <div class="form-group">
                            <label>DPI <span style="color:red">*</span></label>
                            <input type="text" name="dpi">
                        </div>
                        <div class="form-group">
                            <label>Mouse Type <span style="color:red">*</span></label>
                            <input type="text" name="mouse_type">
                        </div>
                        <div class="form-group">
                            <label>Battery <span style="color:red">*</span></label>
                            <input type="text" name="mouse_battery">
                        </div>
                    </div>
                </div>

                <!-- MONITOR -->
                <div id="monitor_fields" class="conditional-group">
                    <div class="sub-grid">
                        <div class="form-group">
                            <label>Screen Size <span style="color:red">*</span></label>
                            <input type="text" name="screen_size">
                        </div>
                    </div>
                </div>

                <div class="form-group full">
                    <label>Product Features</label>
                    <textarea name="feature"></textarea>
                </div>

                <div class="form-group full">
                    <label>Product Specification</label>
                    <textarea name="specification"></textarea>
                </div>

                <div class="form-group full">
                    <label>Product Image</label>
                    <label class="image-upload">
                        <input type="file" name="product_image" id="imageInput" hidden>
                        <i data-lucide="image-plus"></i>
                        <p style="margin-top:10px;">Click to upload image</p>
                        <img id="preview" class="preview-image">
                    </label>
                </div>

                <div class="form-group full">
                    <button type="submit" name="save_product" class="submit-btn">
                        Add Product
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
lucide.createIcons();

const imageInput = document.getElementById("imageInput");
const preview = document.getElementById("preview");
const categorySelect = document.getElementById("category_id");
const productForm = document.getElementById("productForm");

const gamingFields = document.getElementById("gamingpc_fields");
const keyboardFields = document.getElementById("keyboard_fields");
const mouseFields = document.getElementById("mouse_fields");
const monitorFields = document.getElementById("monitor_fields");

const fieldsMap = {
    gamingpc: ["cpu", "gpu", "ram", "storage", "motherboard"],
    keyboard: ["switch_type", "keyboard_size", "keyboard_battery"],
    mouse: ["dpi", "mouse_type", "mouse_battery"],
    monitor: ["screen_size"]
};

function setRequired(fields, required) {
    fields.forEach(name => {
        const el = document.querySelector(`[name="${name}"]`);
        if (el) {
            el.required = required;
        }
    });
}

function hideAllFields() {
    gamingFields.style.display = "none";
    keyboardFields.style.display = "none";
    mouseFields.style.display = "none";
    monitorFields.style.display = "none";

    setRequired(fieldsMap.gamingpc, false);
    setRequired(fieldsMap.keyboard, false);
    setRequired(fieldsMap.mouse, false);
    setRequired(fieldsMap.monitor, false);
}

function updateFields() {
    hideAllFields();

    const selectedText =
        categorySelect.options[categorySelect.selectedIndex]?.text?.toLowerCase() || "";

    if (selectedText.includes("gaming pc")) {
        gamingFields.style.display = "block";
        setRequired(fieldsMap.gamingpc, true);
    }

    if (selectedText.includes("keyboard")) {
        keyboardFields.style.display = "block";
        setRequired(fieldsMap.keyboard, true);
    }

    if (selectedText.includes("mouse")) {
        mouseFields.style.display = "block";
        setRequired(fieldsMap.mouse, true);
    }

    if (selectedText.includes("monitor")) {
        monitorFields.style.display = "block";
        setRequired(fieldsMap.monitor, true);
    }
}

categorySelect.addEventListener("change", updateFields);

imageInput.addEventListener("change", function () {
    const file = this.files[0];

    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = "block";
        };
        reader.readAsDataURL(file);
    }
});

productForm.addEventListener("submit", function (e) {
    const priceInput = document.querySelector('[name="price"]');
    const price = parseFloat(priceInput.value);

    if (isNaN(price) || price <= 0) {
        alert("Product Price must be more than RM 0.00");
        e.preventDefault();
        return false;
    }
});

updateFields();
</script>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="bootstrap.bundle.js"></script>
<script src="admin.js"></script>
</body>
</html>