<?php
session_start();
include "../db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function isPcCategory(string $category): bool
{
    $c = strtolower(trim($category));
    return $c === 'pc'
        || $c === 'laptop'
        || stripos($c, 'gaming pc') !== false
        || (stripos($c, 'pc') !== false && stripos($c, 'monitor') === false);
}

function isKeyboardCategory(string $category): bool
{
    return stripos(strtolower(trim($category)), 'keyboard') !== false;
}

function isMouseCategory(string $category): bool
{
    return stripos(strtolower(trim($category)), 'mouse') !== false;
}

function isMonitorCategory(string $category): bool
{
    return stripos(strtolower(trim($category)), 'monitor') !== false;
}

function normalizeProductStatus(string $status): string
{
    $status = ucfirst(strtolower(trim($status)));
    return in_array($status, ['Active', 'Inactive'], true) ? $status : 'Active';
}

function productStatusClass(string $status): string
{
    $status = strtolower(trim($status));
    return $status === 'inactive' ? 'inactive' : 'active';
}

function jsAlertReload(string $message): void
{
    echo "<script>alert(" . json_encode($message) . "); window.location.href = " . json_encode($_SERVER['PHP_SELF']) . ";</script>";
    exit();
}

/* =========================
   ENSURE STATUS COLUMN EXISTS
========================= */
$checkStatusColumn = $conn->query("SHOW COLUMNS FROM products LIKE 'status'");
if ($checkStatusColumn && $checkStatusColumn->num_rows == 0) {
    $conn->query("
        ALTER TABLE products
        ADD status VARCHAR(20) NOT NULL DEFAULT 'Active'
        AFTER battery
    ");
}

/* =========================
   LOAD CATEGORIES
========================= */
$categories = [];
$resultCategories = mysqli_query(
    $conn,
    "SELECT category_name FROM category ORDER BY category_name ASC"
);

if ($resultCategories) {
    while ($row = mysqli_fetch_assoc($resultCategories)) {
        $categories[] = $row['category_name'];
    }
}

/* =========================
   ADD PRODUCT
========================= */
if (isset($_POST['add_product'])) {
    $name        = trim($_POST['product_name'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $price       = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $stock       = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $description = trim($_POST['description'] ?? '');
    $specs       = trim($_POST['specs'] ?? '');
    $status      = normalizeProductStatus($_POST['status'] ?? 'Active');

    $cpu         = '';
    $gpu         = '';
    $ram         = '';
    $storage     = '';
    $motherboard = '';
    $switch_type = '';
    $keyboard_size = '';
    $dpi         = '';
    $mouse_type  = '';
    $screen_size = '';
    $battery     = '';

    if ($name === '') {
        jsAlertReload("Product Name is required");
    }

    if ($category === '') {
        jsAlertReload("Category is required");
    }

    if (!in_array($category, $categories, true)) {
        jsAlertReload("Invalid category selected");
    }

    if (!in_array($status, ['Active', 'Inactive'], true)) {
        jsAlertReload("Invalid status selected");
    }

    if ($price <= 0) {
        jsAlertReload("Product Price must be more than RM 0.00");
    }

    if ($stock < 0) {
        jsAlertReload("Product Stock cannot be less than 0");
    }

    if (isPcCategory($category)) {
        $cpu         = trim($_POST['cpu'] ?? '');
        $gpu         = trim($_POST['gpu'] ?? '');
        $ram         = trim($_POST['ram'] ?? '');
        $storage     = trim($_POST['storage'] ?? '');
        $motherboard = trim($_POST['motherboard'] ?? '');

        if ($cpu === '' || $gpu === '' || $ram === '' || $storage === '' || $motherboard === '') {
            jsAlertReload("CPU, GPU, RAM, Storage, and Motherboard are required for this category");
        }
    } elseif (isKeyboardCategory($category)) {
        $switch_type   = trim($_POST['switch_type'] ?? '');
        $keyboard_size = trim($_POST['keyboard_size'] ?? '');
        $battery       = trim($_POST['keyboard_battery'] ?? '');

        if ($switch_type === '' || $keyboard_size === '' || $battery === '') {
            jsAlertReload("Switch Type, Keyboard Size, and Battery are required for Keyboard");
        }
    } elseif (isMouseCategory($category)) {
        $dpi         = trim($_POST['dpi'] ?? '');
        $mouse_type  = trim($_POST['mouse_type'] ?? '');
        $battery     = trim($_POST['mouse_battery'] ?? '');

        if ($dpi === '' || $mouse_type === '' || $battery === '') {
            jsAlertReload("DPI, Mouse Type, and Battery are required for Mouse");
        }
    } elseif (isMonitorCategory($category)) {
        $screen_size = trim($_POST['screen_size'] ?? '');

        if ($screen_size === '') {
            jsAlertReload("Screen Size is required for Monitor");
        }
    }

    /* IMAGE UPLOAD */
    $image_name = "";

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
        $target_dir = "../uploads/products/";

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $extension = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($extension, $allowed, true)) {
            $image_name = time() . '_' . uniqid() . '.' . $extension;
            move_uploaded_file($_FILES['product_image']['tmp_name'], $target_dir . $image_name);
        }
    }

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
            battery,
            status
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssdisssssssssssssss",
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
        $category,
        $switch_type,
        $keyboard_size,
        $dpi,
        $mouse_type,
        $specs,
        $screen_size,
        $battery,
        $status
    );

    $stmt->execute();

    jsAlertReload("Product Added Successfully");
}

/* =========================
   UPDATE PRODUCT
========================= */
if (isset($_POST['update_product'])) {
    $product_id  = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $name        = trim($_POST['product_name'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $price       = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $stock       = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $description = trim($_POST['description'] ?? '');
    $specs       = trim($_POST['specs'] ?? '');
    $current_img = trim($_POST['current_image'] ?? '');
    $status      = normalizeProductStatus($_POST['status'] ?? 'Active');

    if ($product_id <= 0) {
        jsAlertReload("Invalid product selected");
    }

    if ($name === '') {
        jsAlertReload("Product Name is required");
    }

    if ($category === '') {
        jsAlertReload("Category is required");
    }

    if (!in_array($category, $categories, true)) {
        jsAlertReload("Invalid category selected");
    }

    if (!in_array($status, ['Active', 'Inactive'], true)) {
        jsAlertReload("Invalid status selected");
    }

    if ($price <= 0) {
        jsAlertReload("Product Price must be more than RM 0.00");
    }

    if ($stock < 0) {
        jsAlertReload("Product Stock cannot be less than 0");
    }

    $cpu         = '';
    $gpu         = '';
    $ram         = '';
    $storage     = '';
    $motherboard = '';
    $switch_type = '';
    $keyboard_size = '';
    $dpi         = '';
    $mouse_type  = '';
    $screen_size = '';
    $battery     = '';

    if (isPcCategory($category)) {
        $cpu         = trim($_POST['cpu'] ?? '');
        $gpu         = trim($_POST['gpu'] ?? '');
        $ram         = trim($_POST['ram'] ?? '');
        $storage     = trim($_POST['storage'] ?? '');
        $motherboard = trim($_POST['motherboard'] ?? '');

        if ($cpu === '' || $gpu === '' || $ram === '' || $storage === '' || $motherboard === '') {
            jsAlertReload("CPU, GPU, RAM, Storage, and Motherboard are required for this category");
        }
    } elseif (isKeyboardCategory($category)) {
        $switch_type   = trim($_POST['switch_type'] ?? '');
        $keyboard_size = trim($_POST['keyboard_size'] ?? '');
        $battery       = trim($_POST['keyboard_battery'] ?? '');

        if ($switch_type === '' || $keyboard_size === '' || $battery === '') {
            jsAlertReload("Switch Type, Keyboard Size, and Battery are required for Keyboard");
        }
    } elseif (isMouseCategory($category)) {
        $dpi         = trim($_POST['dpi'] ?? '');
        $mouse_type  = trim($_POST['mouse_type'] ?? '');
        $battery     = trim($_POST['mouse_battery'] ?? '');

        if ($dpi === '' || $mouse_type === '' || $battery === '') {
            jsAlertReload("DPI, Mouse Type, and Battery are required for Mouse");
        }
    } elseif (isMonitorCategory($category)) {
        $screen_size = trim($_POST['screen_size'] ?? '');

        if ($screen_size === '') {
            jsAlertReload("Screen Size is required for Monitor");
        }
    }

    /* IMAGE */
    $image_name = $current_img;

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
        $target_dir = "../uploads/products/";

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $extension = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($extension, $allowed, true)) {
            $image_name = time() . '_' . uniqid() . '.' . $extension;
            move_uploaded_file($_FILES['product_image']['tmp_name'], $target_dir . $image_name);
        }
    }

    $stmt = $conn->prepare("
        UPDATE products
        SET
            product_name = ?,
            description = ?,
            price = ?,
            stock = ?,
            image = ?,
            cpu = ?,
            gpu = ?,
            ram = ?,
            storage = ?,
            motherboard = ?,
            category = ?,
            switch_type = ?,
            keyboard_size = ?,
            dpi = ?,
            mouse_type = ?,
            specs = ?,
            screen_size = ?,
            battery = ?,
            status = ?
        WHERE product_id = ?
    ");

    $stmt->bind_param(
        "ssdisssssssssssssssi",
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
        $category,
        $switch_type,
        $keyboard_size,
        $dpi,
        $mouse_type,
        $specs,
        $screen_size,
        $battery,
        $status,
        $product_id
    );

    $stmt->execute();

    jsAlertReload("Product Updated Successfully");
}

$result = $conn->query("SELECT * FROM products ORDER BY product_id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>

    <link rel="stylesheet" href="style.css?v=2">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body{
            font-family:Segoe UI, sans-serif;
            background:#f5f7fb;
        }

        .main-content{
            margin-left:270px;
            margin-top:100px;
            padding:30px;
            transition:.3s ease;
        }

        .sidebar.collapsed ~ .main-content{
            margin-left:95px;
        }

        .page-header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:16px;
            margin-bottom:20px;
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
            align-items:center;
            gap:6px;
            text-decoration:none;
        }

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
            background:transparent;
        }

        .table-card{
            background:#fff;
            border-radius:16px;
            padding:20px;
            box-shadow:0 10px 30px rgba(0,0,0,0.08);
            overflow-x:auto;
        }

        table{
            width:100%;
            border-collapse:separate;
            border-spacing:0;
            table-layout:auto;
        }

        th,td{
            padding:14px;
            text-align:left;
            vertical-align:middle;
        }

        th:nth-child(1){width:130px;}
        th:nth-child(2){width:470px;}
        th:nth-child(3){width:140px;}
        th:nth-child(4){width:130px;}
        th:nth-child(5){width:100px;}
        th:nth-child(6){width:200px;}
        th:nth-child(7){width:120px;}

        tr:hover{
            background:#f9fbff;
        }

        .product-img{
            width:55px;
            height:55px;
            border-radius:10px;
            object-fit:cover;
            border:1px solid #ddd;
            background:#fff;
            display:block;
            flex-shrink:0;
        }

        .product-info{
            display:flex;
            align-items:center;
            gap:12px;
            min-width:0;
        }

        .product-info span{
            display:block;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .status-wrapper{
            display:flex;
            justify-content:center;
            align-items:center;
            width:100%;
        }

        .status-badge{
            min-width:130px;
            height:34px;
            display:flex;
            justify-content:center;
            align-items:center;
            border-radius:999px;
            font-size:12px;
            font-weight:700;
            color:#fff;
            white-space:nowrap;
        }

        .status-active{
            background:#22c55e;
        }

        .status-inactive{
            background:#94a3b8;
        }

        .actions{
            white-space:nowrap;
        }

        .actions i{
            padding:8px;
            border-radius:8px;
            cursor:pointer;
            transition:0.2s;
            margin-right:4px;
        }

        .actions i:hover{
            background:#eef2ff;
            transform:scale(1.1);
        }

        .view{color:#111;}
        .edit{color:#2563eb;}

        .modal{
            display:none;
            position:fixed;
            inset:0;
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
            max-width:920px;
            background:#fff;
            border-radius:28px;
            padding:34px;
            box-shadow:0 25px 60px rgba(0,0,0,.25);
            max-height:92vh;
            overflow-y:auto;
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
            margin-bottom:18px;
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
            white-space:pre-wrap;
        }

        .spec-box{
            background:#f8fafc;
            border-radius:18px;
            padding:24px;
            margin-top:16px;
        }

        .spec-title{
            font-size:28px;
            font-weight:800;
            margin-bottom:18px;
            color:#0f172a;
        }

        .spec-list{
            display:grid;
            gap:10px;
        }

        .spec-item{
            display:flex;
            justify-content:space-between;
            gap:12px;
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
            text-align:right;
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
            box-sizing:border-box;
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

        .conditional-group{
            display:none;
            grid-column:1 / -1;
            margin-top:0;
        }

        .sub-grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:16px;
        }

        .sub-grid .form-full{
            grid-column:1 / -1;
        }

        .muted{
            color:#64748b;
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

        @media(max-width:900px){
            .main-content{
                margin-left:0;
            }

            .detail-grid,
            .form-grid,
            .sub-grid{
                grid-template-columns:1fr;
            }

            .modal-box h3{
                font-size:28px;
            }
        }
    </style>
</head>
<body>

<?php
if (isset($_SESSION['role']) && $_SESSION['role'] === "super_admin") {
    include "sadmin_sidebar.php";
} else {
    include "admin_sidebar.php";
}
?>

<?php include "admin_header.php"; ?>

<div class="main-content">
    <div class="page-header">
        <div>
            <h2 style="margin:0;font-size:38px;font-weight:800;color:#0f172a;">Products</h2>
            <div style="color:#64748b;margin-top:6px;">Manage product information</div>
        </div>

        <a class="btn-add" href="add_product.php">
            <i data-lucide="plus"></i>
            Add Product
        </a>
    </div>

    <div class="product-search-box" style="display:flex; gap:12px; align-items:center;">
        <input
            id="searchInput"
            placeholder="Search products..."
            style="flex:1; border:none; outline:none; background:transparent;"
        >

        <select id="categoryFilter" style="width:180px; padding:12px 15px; border:1px solid #dbe2ea; border-radius:12px; outline:none; background:white;">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= h($cat) ?>"><?= h($cat) ?></option>
            <?php endforeach; ?>
        </select>
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
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                    $image = trim((string)($row['image'] ?? ''));
                    if ($image !== '' && filter_var($image, FILTER_VALIDATE_URL)) {
                        $imageSrc = $image;
                    } elseif ($image !== '') {
                        $imageSrc = "../uploads/products/" . $image;
                    } else {
                        $imageSrc = "https://via.placeholder.com/55";
                    }

                    $statusText = normalizeProductStatus($row['status'] ?? 'Active');
                    $statusClass = productStatusClass($statusText);

                    $data = [
                        'product_id'    => $row['product_id'],
                        'product_name'  => $row['product_name'] ?? '',
                        'category'      => $row['category'] ?? '',
                        'price'         => $row['price'] ?? '',
                        'stock'         => $row['stock'] ?? '',
                        'description'   => $row['description'] ?? '',
                        'specs'         => $row['specs'] ?? '',
                        'image'         => $row['image'] ?? '',
                        'cpu'           => $row['cpu'] ?? '',
                        'gpu'           => $row['gpu'] ?? '',
                        'ram'           => $row['ram'] ?? '',
                        'storage'       => $row['storage'] ?? '',
                        'motherboard'   => $row['motherboard'] ?? '',
                        'switch_type'   => $row['switch_type'] ?? '',
                        'keyboard_size' => $row['keyboard_size'] ?? '',
                        'dpi'           => $row['dpi'] ?? '',
                        'mouse_type'    => $row['mouse_type'] ?? '',
                        'screen_size'   => $row['screen_size'] ?? '',
                        'battery'       => $row['battery'] ?? '',
                        'status'        => $statusText,
                    ];
                ?>
                <tr class="product-row" data-category="<?= strtolower($row['category'] ?? '') ?>">
                    <td>Product #<?= (int)$row['product_id'] ?></td>

                    <td>
                        <div class="product-info">
                            <img
                                src="<?= h($imageSrc) ?>"
                                class="product-img"
                                onerror="this.src='https://via.placeholder.com/55';"
                                alt="Product image"
                            >
                            <span><?= h($row['product_name'] ?? '') ?></span>
                        </div>
                    </td>

                    <td><?= h($row['category'] ?? '') ?></td>
                    <td>RM <?= number_format((float)$row['price'], 2) ?></td>
                    <td><?= (int)$row['stock'] ?></td>

                    <td>
                        <div class="status-wrapper">
                            <span class="status-badge status-<?= h($statusClass) ?>">
                                <?= h($statusText) ?>
                            </span>
                        </div>
                    </td>

                    <td class="actions">
                        <i
                            class="view"
                            data-lucide="eye"
                            onclick='viewProduct(<?= json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>)'
                            title="View"
                        ></i>

                        <i
                            class="edit"
                            data-lucide="pencil"
                            onclick='editProduct(<?= json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>)'
                            title="Edit"
                        ></i>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- VIEW MODAL -->
<div class="modal" id="viewModal">
    <div class="modal-box">
        <h3>Product Details</h3>
        <div class="modal-subtitle">View complete product information</div>

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
            <div class="spec-title">Description</div>
            <div class="detail-value" id="v_description" style="font-size:15px;font-weight:600;"></div>
        </div>

        <div class="spec-box">
            <div class="spec-title">Specifications</div>
            <div class="detail-value" id="v_specs" style="font-size:15px;font-weight:600;"></div>
        </div>

        <div class="spec-box">
            <div class="spec-title">Technical Details</div>
            <div class="spec-list" id="v_tech"></div>
        </div>

        <div class="modal-footer">
            <button class="btn-cancel" type="button" onclick="closeModal('viewModal')">Close</button>
        </div>
    </div>
</div>

<!-- ADD MODAL -->
<div class="modal" id="addModal">
    <div class="modal-box">
        <h3>Add Product</h3>
        <div class="modal-subtitle">Create a new product</div>

        <form method="POST" enctype="multipart/form-data" id="addForm">
            <div class="form-grid">
                <div>
                    <label class="detail-label">Product Name <span style="color:red">*</span></label>
                    <input type="text" name="product_name" id="add_product_name" required>
                </div>

                <div>
                    <label class="detail-label">Category <span style="color:red">*</span></label>
                    <select name="category" id="add_category" required onchange="toggleAddFields()">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $catName): ?>
                            <option value="<?= h($catName) ?>"><?= h($catName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="detail-label">Price (RM) <span style="color:red">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="price" id="add_price" required>
                </div>

                <div>
                    <label class="detail-label">Stock <span style="color:red">*</span></label>
                    <input type="number" min="0" name="stock" id="add_stock" required>
                </div>

                <div>
                    <label class="detail-label">Status <span style="color:red">*</span></label>
                    <select name="status" id="add_status" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <div class="form-full">
                    <label class="detail-label">Description</label>
                    <textarea name="description" id="add_description" rows="4"></textarea>
                </div>

                <div class="form-full">
                    <label class="detail-label">Specs</label>
                    <textarea name="specs" id="add_specs" rows="4"></textarea>
                </div>

                <div id="add_pcFields" class="conditional-group">
                    <div class="spec-box" style="margin-top:0;">
                        <div class="spec-title" style="font-size:22px;margin-bottom:14px;">Gaming PC / PC / Laptop Specifications</div>
                        <div class="sub-grid">
                            <div>
                                <label class="detail-label">CPU <span style="color:red">*</span></label>
                                <input type="text" name="cpu" id="add_cpu">
                            </div>
                            <div>
                                <label class="detail-label">GPU <span style="color:red">*</span></label>
                                <input type="text" name="gpu" id="add_gpu">
                            </div>
                            <div>
                                <label class="detail-label">RAM <span style="color:red">*</span></label>
                                <input type="text" name="ram" id="add_ram">
                            </div>
                            <div>
                                <label class="detail-label">Storage <span style="color:red">*</span></label>
                                <input type="text" name="storage" id="add_storage">
                            </div>
                            <div class="form-full">
                                <label class="detail-label">Motherboard <span style="color:red">*</span></label>
                                <input type="text" name="motherboard" id="add_motherboard">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="add_keyboardFields" class="conditional-group">
                    <div class="spec-box" style="margin-top:0;">
                        <div class="spec-title" style="font-size:22px;margin-bottom:14px;">Keyboard Specifications</div>
                        <div class="sub-grid">
                            <div>
                                <label class="detail-label">Switch Type <span style="color:red">*</span></label>
                                <input type="text" name="switch_type" id="add_switch_type">
                            </div>
                            <div>
                                <label class="detail-label">Keyboard Size <span style="color:red">*</span></label>
                                <input type="text" name="keyboard_size" id="add_keyboard_size">
                            </div>
                            <div class="form-full">
                                <label class="detail-label">Battery <span style="color:red">*</span></label>
                                <input type="text" name="keyboard_battery" id="add_keyboard_battery">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="add_mouseFields" class="conditional-group">
                    <div class="spec-box" style="margin-top:0;">
                        <div class="spec-title" style="font-size:22px;margin-bottom:14px;">Mouse Specifications</div>
                        <div class="sub-grid">
                            <div>
                                <label class="detail-label">DPI <span style="color:red">*</span></label>
                                <input type="text" name="dpi" id="add_dpi">
                            </div>
                            <div>
                                <label class="detail-label">Mouse Type <span style="color:red">*</span></label>
                                <input type="text" name="mouse_type" id="add_mouse_type">
                            </div>
                            <div class="form-full">
                                <label class="detail-label">Battery <span style="color:red">*</span></label>
                                <input type="text" name="mouse_battery" id="add_mouse_battery">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="add_monitorFields" class="conditional-group">
                    <div class="spec-box" style="margin-top:0;">
                        <div class="spec-title" style="font-size:22px;margin-bottom:14px;">Monitor Specifications</div>
                        <div class="sub-grid">
                            <div class="form-full">
                                <label class="detail-label">Screen Size <span style="color:red">*</span></label>
                                <input type="text" name="screen_size" id="add_screen_size">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-full">
                    <label class="detail-label">Product Image</label>
                    <input type="file" name="product_image" id="add_image">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" name="add_product" class="btn-primary">Add Product</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal" id="editModal">
    <div class="modal-box">
        <h3>Edit Product</h3>
        <div class="modal-subtitle">Update product information</div>

        <form method="POST" enctype="multipart/form-data" id="editForm">
            <input type="hidden" name="product_id" id="edit_product_id">
            <input type="hidden" name="current_image" id="edit_current_image">

            <div class="form-grid">
                <div>
                    <label class="detail-label">Product Name <span style="color:red">*</span></label>
                    <input type="text" name="product_name" id="edit_product_name" required>
                </div>

                <div>
                    <label class="detail-label">Category <span style="color:red">*</span></label>
                    <select name="category" id="edit_category" required onchange="toggleEditFields()">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $catName): ?>
                            <option value="<?= h($catName) ?>"><?= h($catName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="detail-label">Price (RM) <span style="color:red">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="price" id="edit_price" required>
                </div>

                <div>
                    <label class="detail-label">Stock <span style="color:red">*</span></label>
                    <input type="number" min="0" name="stock" id="edit_stock" required>
                </div>

                <div>
                    <label class="detail-label">Status <span style="color:red">*</span></label>
                    <select name="status" id="edit_status" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <div class="form-full">
                    <label class="detail-label">Description</label>
                    <textarea name="description" id="edit_description" rows="4"></textarea>
                </div>

                <div class="form-full">
                    <label class="detail-label">Specs</label>
                    <textarea name="specs" id="edit_specs" rows="4"></textarea>
                </div>

                <div id="edit_pcFields" class="conditional-group">
                    <div class="spec-box" style="margin-top:0;">
                        <div class="spec-title" style="font-size:22px;margin-bottom:14px;">Gaming PC / PC / Laptop Specifications</div>
                        <div class="sub-grid">
                            <div>
                                <label class="detail-label">CPU <span style="color:red">*</span></label>
                                <input type="text" name="cpu" id="edit_cpu">
                            </div>
                            <div>
                                <label class="detail-label">GPU <span style="color:red">*</span></label>
                                <input type="text" name="gpu" id="edit_gpu">
                            </div>
                            <div>
                                <label class="detail-label">RAM <span style="color:red">*</span></label>
                                <input type="text" name="ram" id="edit_ram">
                            </div>
                            <div>
                                <label class="detail-label">Storage <span style="color:red">*</span></label>
                                <input type="text" name="storage" id="edit_storage">
                            </div>
                            <div class="form-full">
                                <label class="detail-label">Motherboard <span style="color:red">*</span></label>
                                <input type="text" name="motherboard" id="edit_motherboard">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="edit_keyboardFields" class="conditional-group">
                    <div class="spec-box" style="margin-top:0;">
                        <div class="spec-title" style="font-size:22px;margin-bottom:14px;">Keyboard Specifications</div>
                        <div class="sub-grid">
                            <div>
                                <label class="detail-label">Switch Type <span style="color:red">*</span></label>
                                <input type="text" name="switch_type" id="edit_switch_type">
                            </div>
                            <div>
                                <label class="detail-label">Keyboard Size <span style="color:red">*</span></label>
                                <input type="text" name="keyboard_size" id="edit_keyboard_size">
                            </div>
                            <div class="form-full">
                                <label class="detail-label">Battery <span style="color:red">*</span></label>
                                <input type="text" name="keyboard_battery" id="edit_keyboard_battery">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="edit_mouseFields" class="conditional-group">
                    <div class="spec-box" style="margin-top:0;">
                        <div class="spec-title" style="font-size:22px;margin-bottom:14px;">Mouse Specifications</div>
                        <div class="sub-grid">
                            <div>
                                <label class="detail-label">DPI <span style="color:red">*</span></label>
                                <input type="text" name="dpi" id="edit_dpi">
                            </div>
                            <div>
                                <label class="detail-label">Mouse Type <span style="color:red">*</span></label>
                                <input type="text" name="mouse_type" id="edit_mouse_type">
                            </div>
                            <div class="form-full">
                                <label class="detail-label">Battery <span style="color:red">*</span></label>
                                <input type="text" name="mouse_battery" id="edit_mouse_battery">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="edit_monitorFields" class="conditional-group">
                    <div class="spec-box" style="margin-top:0;">
                        <div class="spec-title" style="font-size:22px;margin-bottom:14px;">Monitor Specifications</div>
                        <div class="sub-grid">
                            <div class="form-full">
                                <label class="detail-label">Screen Size <span style="color:red">*</span></label>
                                <input type="text" name="screen_size" id="edit_screen_size">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-full">
                    <label class="detail-label">Product Image</label>
                    <input type="file" name="product_image" id="edit_image">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" name="update_product" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
lucide.createIcons();

function openModal(id){
    const modal = document.getElementById(id);
    modal.style.display = "flex";
    document.body.style.overflow = "hidden";
}

function closeModal(id){
    const modal = document.getElementById(id);
    modal.style.display = "none";
    document.body.style.overflow = "auto";
}

function escapeHtml(text){
    return String(text ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function normalizeCategory(category){
    return String(category ?? '').toLowerCase().trim();
}

function isPcCategoryJs(category){
    const c = normalizeCategory(category);
    return c === 'pc' || c === 'laptop' || c.includes('gaming pc') || (c.includes('pc') && !c.includes('monitor'));
}

function isKeyboardCategoryJs(category){
    return normalizeCategory(category).includes('keyboard');
}

function isMouseCategoryJs(category){
    return normalizeCategory(category).includes('mouse');
}

function isMonitorCategoryJs(category){
    return normalizeCategory(category).includes('monitor');
}

function clearRequired(form, names){
    names.forEach(name => {
        const el = form.querySelector(`[name="${name}"]`);
        if (el) el.required = false;
    });
}

function setRequired(form, names, required){
    names.forEach(name => {
        const el = form.querySelector(`[name="${name}"]`);
        if (el) el.required = required;
    });
}

function hideAllSections(prefix){
    document.getElementById(prefix + '_pcFields').style.display = 'none';
    document.getElementById(prefix + '_keyboardFields').style.display = 'none';
    document.getElementById(prefix + '_mouseFields').style.display = 'none';
    document.getElementById(prefix + '_monitorFields').style.display = 'none';
}

function toggleFields(prefix){
    const form = document.getElementById(prefix + 'Form');
    const category = document.getElementById(prefix + '_category').value;

    hideAllSections(prefix);

    clearRequired(form, [
        'cpu','gpu','ram','storage','motherboard',
        'switch_type','keyboard_size','keyboard_battery',
        'dpi','mouse_type','mouse_battery',
        'screen_size'
    ]);

    if (isPcCategoryJs(category)) {
        document.getElementById(prefix + '_pcFields').style.display = 'block';
        setRequired(form, ['cpu','gpu','ram','storage','motherboard'], true);
    } else if (isKeyboardCategoryJs(category)) {
        document.getElementById(prefix + '_keyboardFields').style.display = 'block';
        setRequired(form, ['switch_type','keyboard_size','keyboard_battery'], true);
    } else if (isMouseCategoryJs(category)) {
        document.getElementById(prefix + '_mouseFields').style.display = 'block';
        setRequired(form, ['dpi','mouse_type','mouse_battery'], true);
    } else if (isMonitorCategoryJs(category)) {
        document.getElementById(prefix + '_monitorFields').style.display = 'block';
        setRequired(form, ['screen_size'], true);
    }
}

function toggleAddFields(){
    toggleFields('add');
}

function toggleEditFields(){
    toggleFields('edit');
}

function viewProduct(data){
    openModal('viewModal');

    document.getElementById('v_name').textContent = data.product_name || '';
    document.getElementById('v_category').textContent = data.category || '';
    document.getElementById('v_price').textContent = 'RM ' + Number(data.price || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('v_stock').textContent = data.stock || '0';

    document.getElementById('v_description').innerHTML = data.description
        ? escapeHtml(data.description).replace(/\n/g, '<br>')
        : '<span class="muted">No description</span>';

    document.getElementById('v_specs').innerHTML = data.specs
        ? escapeHtml(data.specs).replace(/\n/g, '<br>')
        : '<span class="muted">No specs</span>';

    const tech = [];

    const addTech = (label, value) => {
        if (String(value ?? '').trim() !== '') {
            tech.push(`
                <div class="spec-item">
                    <div class="spec-name">${escapeHtml(label)}</div>
                    <div class="spec-value">${escapeHtml(value)}</div>
                </div>
            `);
        }
    };

    addTech('Status', data.status);

    if (isPcCategoryJs(data.category)) {
        addTech('CPU', data.cpu);
        addTech('GPU', data.gpu);
        addTech('RAM', data.ram);
        addTech('Storage', data.storage);
        addTech('Motherboard', data.motherboard);
    } else if (isKeyboardCategoryJs(data.category)) {
        addTech('Switch Type', data.switch_type);
        addTech('Keyboard Size', data.keyboard_size);
        addTech('Battery', data.battery);
    } else if (isMouseCategoryJs(data.category)) {
        addTech('DPI', data.dpi);
        addTech('Mouse Type', data.mouse_type);
        addTech('Battery', data.battery);
    } else if (isMonitorCategoryJs(data.category)) {
        addTech('Screen Size', data.screen_size);
    }

    if (tech.length === 0) {
        tech.push('<div class="muted">No technical details available</div>');
    }

    document.getElementById('v_tech').innerHTML = tech.join('');
}

function editProduct(data){
    openModal('editModal');

    document.getElementById('edit_product_id').value = data.product_id || '';
    document.getElementById('edit_current_image').value = data.image || '';
    document.getElementById('edit_product_name').value = data.product_name || '';
    document.getElementById('edit_category').value = String(data.category || '').trim();
    document.getElementById('edit_price').value = data.price || '';
    document.getElementById('edit_stock').value = data.stock || '';
    document.getElementById('edit_status').value = data.status || 'Active';
    document.getElementById('edit_description').value = data.description || '';
    document.getElementById('edit_specs').value = data.specs || '';
    document.getElementById('edit_cpu').value = data.cpu || '';
    document.getElementById('edit_gpu').value = data.gpu || '';
    document.getElementById('edit_ram').value = data.ram || '';
    document.getElementById('edit_storage').value = data.storage || '';
    document.getElementById('edit_motherboard').value = data.motherboard || '';
    document.getElementById('edit_switch_type').value = data.switch_type || '';
    document.getElementById('edit_keyboard_size').value = data.keyboard_size || '';
    document.getElementById('edit_keyboard_battery').value = data.battery || '';
    document.getElementById('edit_dpi').value = data.dpi || '';
    document.getElementById('edit_mouse_type').value = data.mouse_type || '';
    document.getElementById('edit_mouse_battery').value = data.battery || '';
    document.getElementById('edit_screen_size').value = data.screen_size || '';

    setTimeout(() => {
        toggleEditFields();
    }, 0);
}

function filterProducts(){
    const search =
        document.getElementById('searchInput')
        .value
        .toLowerCase();

    const category =
        document.getElementById('categoryFilter')
        .value
        .toLowerCase();

    document.querySelectorAll('.product-row').forEach(row => {
        const text = row.innerText.toLowerCase();
        const rowCategory = row.dataset.category;
        const matchSearch = text.includes(search);
        const matchCategory = category === '' || rowCategory === category;

        row.style.display = (matchSearch && matchCategory) ? '' : 'none';
    });
}

document.getElementById('searchInput').addEventListener('keyup', filterProducts);
document.getElementById('categoryFilter').addEventListener('change', filterProducts);

document.getElementById('addForm').addEventListener('submit', function(e){
    const price = parseFloat(this.querySelector('[name="price"]').value);
    if (isNaN(price) || price <= 0) {
        alert('Product Price must be more than RM 0.00');
        e.preventDefault();
    }
});

document.getElementById('editForm').addEventListener('submit', function(e){
    const price = parseFloat(this.querySelector('[name="price"]').value);
    if (isNaN(price) || price <= 0) {
        alert('Product Price must be more than RM 0.00');
        e.preventDefault();
    }
});

document.getElementById('add_category').addEventListener('change', toggleAddFields);
document.getElementById('edit_category').addEventListener('change', toggleEditFields);

document.addEventListener('DOMContentLoaded', function () {
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
    toggleAddFields();
    toggleEditFields();
});
</script>

</body>
</html>