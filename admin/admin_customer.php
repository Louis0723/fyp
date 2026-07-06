<?php
include "../db.php";
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$checkColumn = $conn->query("
    SHOW COLUMNS FROM users LIKE 'status'
");

if ($checkColumn->num_rows == 0) {
    $conn->query("
        ALTER TABLE users
        ADD status VARCHAR(50)
        NOT NULL DEFAULT 'Active'
    ");
}

function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function getCustomerPhotoUrl(array $u): string
{
    $baseDir = "../uploads/profile/";

    // 1) If DB has a stored file name/path, try it first
    if (!empty($u['profile_image'])) {
        $img = trim((string)$u['profile_image']);

        if (filter_var($img, FILTER_VALIDATE_URL)) {
            return $img;
        }

        $directPath = $baseDir . $img;
        if (is_file($directPath)) {
            return $directPath;
        }

        // If the DB value is just a filename but stored elsewhere, still return it as folder path
        return $directPath;
    }

    // 2) Fallback to common upload naming pattern
    $exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    foreach ($exts as $ext) {
        $path = $baseDir . "profile_" . $u['user_id'] . "." . $ext;
        if (is_file($path)) {
            return $path;
        }
    }

    return "";
}

if (isset($_POST['update_status'])) {
    $user_id = intval($_POST['user_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("
        UPDATE users
        SET status = ?
        WHERE user_id = ?
    ");

    $stmt->bind_param("si", $status, $user_id);
    $stmt->execute();

    header("Location: admin_customer.php");
    exit();
}

$users = $conn->query("
    SELECT 
        u.user_id,
        u.name,
        u.email,
        
        u.created_at,
        u.status,

        COUNT(o.order_id) AS total_orders,
        IFNULL(SUM(o.total_price), 0) AS total_spent,
        YEAR(u.created_at) AS joined_year

    FROM users u
    LEFT JOIN orders o
        ON u.user_id = o.user_id
    GROUP BY u.user_id
    ORDER BY u.user_id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Customers</title>

    <link rel="stylesheet" href="style.css?v=11">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Inter',sans-serif;
        }

        body{
            background:#f3f7fb;
        }

        .main{
            margin-left:270px;
            margin-top:95px;
            padding:28px;
            transition:.3s ease;
        }

        .main.expanded{
            margin-left:95px;
        }

        .sidebar.collapsed ~ .main{
            margin-left:95px;
        }

        .page-title{
            font-size:42px;
            font-weight:800;
            color:#0f172a;
        }

        .subtitle{
            margin-top:5px;
            color:#64748b;
            font-size:15px;
        }

        .search-wrapper{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin:24px 0;
        }

        .search-box{
            width:390px;
            height:50px;
            background:#fff;
            border-radius:15px;
            border:1px solid #dbe2ea;
            display:flex;
            align-items:center;
            gap:12px;
            padding:0 18px;
        }

        .search-box input{
            width:100%;
            border:none;
            outline:none;
            background:none;
            font-size:15px;
        }

        .customer-table-wrapper{
            margin-top:25px;
            background:#fff;
            border-radius:20px;
            overflow:hidden;
            box-shadow:0 4px 18px rgba(0,0,0,.05);
        }

        .customer-table{
            width:100%;
            border-collapse:collapse;
        }

        .customer-table thead{
            background:#f8fafc;
        }

        .customer-table th{
            text-align:left;
            padding:18px;
            font-size:13px;
            font-weight:700;
            color:#64748b;
            border-bottom:1px solid #e5e7eb;
        }

        .customer-table td{
            padding:18px;
            border-bottom:1px solid #f1f5f9;
            vertical-align:middle;
        }

        .customer-table tr:hover{
            background:#f8fbff;
        }

        .customer-profile{
            display:flex;
            align-items:center;
            gap:12px;
        }

        .avatar{
            width:46px;
            height:46px;
            border-radius:50%;
            background:#2563eb;
            color:#fff;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:16px;
            font-weight:700;
            overflow:hidden;
            flex-shrink:0;
        }

        .avatar img{
            width:100%;
            height:100%;
            object-fit:cover;
            display:block;
        }

        .customer-name{
            font-size:15px;
            font-weight:700;
            color:#0f172a;
        }

        .customer-email{
            color:#64748b;
            font-size:13px;
            margin-top:3px;
        }

        .customer-status{
            width:fit-content;
            padding:7px 14px;
            border-radius:999px;
            font-size:12px;
            font-weight:700;
        }

        .status-active{
            background:#dcfce7;
            color:#166534;
        }

        .status-suspended{
            background:#fef3c7;
            color:#92400e;
        }

        .status-banned{
            background:#fee2e2;
            color:#991b1b;
        }

        .table-actions{
            display:flex;
            align-items:center;
            gap:12px;
        }

        .action-btn{
            width:36px;
            height:36px;
            border-radius:10px;
            display:flex;
            align-items:center;
            justify-content:center;
            cursor:pointer;
            transition:.2s ease;
        }

        .action-btn:hover{
            background:#eff6ff;
        }

        .edit{
            color:#2563eb;
        }

        .customer-modal{
            position:fixed;
            inset:0;
            background:rgba(15,23,42,.65);
            display:none;
            align-items:flex-start;
            justify-content:center;
            overflow-y:auto;
            padding:120px 20px 40px;
            z-index:99999;
            backdrop-filter:blur(5px);
        }

        .customer-modal-box{
            width:520px;
            max-width:100%;
            background:#fff;
            border-radius:24px;
            padding:28px;
            position:relative;
            margin:auto;
        }

        .modal-close{
            position:absolute;
            right:20px;
            top:20px;
            cursor:pointer;
            width:34px;
            height:34px;
            border-radius:50%;
            background:#f1f5f9;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:14px;
            font-weight:700;
        }

        .modal-title{
            font-size:24px;
            font-weight:800;
            margin-bottom:24px;
        }

        .modal-avatar{
            width:90px;
            height:90px;
            margin:auto;
            margin-bottom:24px;
            border-radius:50%;
            background:#eff6ff;
            display:flex;
            align-items:center;
            justify-content:center;
            overflow:hidden;
        }

        .modal-avatar i{
            width:40px;
            height:40px;
            color:#2563eb;
        }

        .modal-avatar img{
            width:100%;
            height:100%;
            object-fit:cover;
            display:block;
        }

        .modal-section{
            margin-top:20px;
        }

        .modal-section-title{
            font-size:14px;
            font-weight:700;
            color:#ef4444;
            border-bottom:1px solid #e5e7eb;
            padding-bottom:8px;
            margin-bottom:10px;
        }

        .modal-row{
            display:flex;
            justify-content:space-between;
            padding:10px 0;
            border-bottom:1px solid #f1f5f9;
        }

        .modal-row span{
            color:#64748b;
            font-size:14px;
        }

        .modal-row strong{
            color:#0f172a;
            font-size:14px;
        }

        .status-select{
            width:100%;
            height:42px;
            border-radius:12px;
            border:1px solid #dbe2ea;
            padding:0 12px;
            margin-top:14px;
            font-size:14px;
            font-weight:600;
        }

        .update-btn{
            width:100%;
            height:44px;
            margin-top:14px;
            border:none;
            border-radius:12px;
            background:#2563eb;
            color:#fff;
            font-size:14px;
            font-weight:700;
            cursor:pointer;
        }

        @media(max-width:900px){
            .main{
                margin-left:0;
            }
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

<div class="main">

    <div class="page-title">Customers</div>
    <div class="subtitle">Your loyal builders and gamers.</div>

    <div class="search-wrapper">
        <div class="search-box">
            <i data-lucide="search"></i>
            <input type="text" id="searchInput" placeholder="Search name or email...">
        </div>
    </div>

    <div class="customer-table-wrapper">
        <table class="customer-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Joined</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
            <?php while ($u = $users->fetch_assoc()): ?>
                <?php
                    $initials = strtoupper(substr(trim($u['name']), 0, 2));
                    $profilePhoto = getCustomerPhotoUrl($u);

                    $statusClass = "status-active";
                    if ($u['status'] == "Suspended") {
                        $statusClass = "status-suspended";
                    }
                    if ($u['status'] == "Banned") {
                        $statusClass = "status-banned";
                    }

                    $modalData = [
                        'user_id' => (int)$u['user_id'],
                        'username' => explode('@', $u['email'])[0],
                        'first' => explode(' ', trim($u['name']))[0] ?? '',
                        'last' => (function ($name) {
                            $parts = array_values(array_filter(explode(' ', trim($name))));
                            return count($parts) ? end($parts) : '';
                        })($u['name']),
                        'full' => $u['name'],
                        'dob' => '2000-01-01',
                        'gender' => 'Male',
                        'email' => $u['email'],
                        'phone' => '012-3456789',
                        'status' => $u['status'],
                        'registered' => date('Y-m-d h:i A', strtotime($u['created_at'])),
                        'photo' => $profilePhoto
                    ];
                ?>

                <tr class="customer-item">
                    <td>
                        <div class="customer-profile">
                            <div class="avatar">
                                <?php if ($profilePhoto !== ''): ?>
                                    <img src="<?= h($profilePhoto) ?>?v=<?= time() ?>" alt="Customer photo">
                                <?php else: ?>
                                    <?= h($initials) ?>
                                <?php endif; ?>
                            </div>

                            <div>
                                <div class="customer-name"><?= h($u['name']) ?></div>
                                <div class="customer-email"><?= h($u['email']) ?></div>
                            </div>
                        </div>
                    </td>

                    <td><?= h($u['email']) ?></td>
                    <td><?= h($u['total_orders']) ?></td>
                    <td>RM <?= number_format((float)$u['total_spent'], 2) ?></td>
                    <td><?= h($u['joined_year']) ?></td>

                    <td>
                        <div class="customer-status <?= h($statusClass) ?>">
                            <?= h($u['status']) ?>
                        </div>
                    </td>

                    <td>
                        <div class="table-actions">
                            <div
                                class="action-btn edit"
                                onclick='openCustomerModal(<?= json_encode($modalData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>)'>
                                <i data-lucide="eye"></i>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="customer-modal" id="customerModal">
    <div class="customer-modal-box">
        <div class="modal-close" onclick="closeCustomerModal()">✕</div>

        <div class="modal-title">Customer Details</div>

        <div class="modal-avatar" id="modalAvatarWrap">
            <i data-lucide="user" id="modalUserIcon"></i>
            <img id="modalCustomerPhoto" alt="Customer photo" style="display:none;">
        </div>

        <div class="modal-section">
            <div class="modal-section-title">Personal Information</div>

            <div class="modal-row">
                <span>Customer ID:</span>
                <strong id="m_id"></strong>
            </div>

            <div class="modal-row">
                <span>Username:</span>
                <strong id="m_username"></strong>
            </div>

            <div class="modal-row">
                <span>First Name:</span>
                <strong id="m_first"></strong>
            </div>

            <div class="modal-row">
                <span>Last Name:</span>
                <strong id="m_last"></strong>
            </div>

            <div class="modal-row">
                <span>Full Name:</span>
                <strong id="m_full"></strong>
            </div>

            <div class="modal-row">
                <span>Date of Birth:</span>
                <strong id="m_dob"></strong>
            </div>

            <div class="modal-row">
                <span>Gender:</span>
                <strong id="m_gender"></strong>
            </div>
        </div>

        <div class="modal-section">
            <div class="modal-section-title">Contact Information</div>

            <div class="modal-row">
                <span>Email:</span>
                <strong id="m_email"></strong>
            </div>

            <div class="modal-row">
                <span>Phone:</span>
                <strong id="m_phone"></strong>
            </div>
        </div>

        <div class="modal-section">
            <div class="modal-section-title">Account Information</div>

            <div class="modal-row">
                <span>Registered At:</span>
                <strong id="m_registered"></strong>
            </div>
        </div>

        <form method="POST">
            <input type="hidden" name="user_id" id="form_user_id">

            <select class="status-select" name="status" id="form_status">
                <option value="Active">Active</option>
                <option value="Suspended">Suspended</option>
                <option value="Banned">Banned</option>
            </select>

            <button type="submit" name="update_status" class="update-btn">Update Status</button>
        </form>
    </div>
</div>

<script>
lucide.createIcons();

function openCustomerModal(data){
    document.getElementById("customerModal").style.display = "flex";

    document.getElementById("m_id").innerText = data.user_id ?? "";
    document.getElementById("m_username").innerText = data.username ?? "";
    document.getElementById("m_first").innerText = data.first ?? "";
    document.getElementById("m_last").innerText = data.last ?? "";
    document.getElementById("m_full").innerText = data.full ?? "";
    document.getElementById("m_dob").innerText = data.dob ?? "";
    document.getElementById("m_gender").innerText = data.gender ?? "";
    document.getElementById("m_email").innerText = data.email ?? "";
    document.getElementById("m_phone").innerText = data.phone ?? "";
    document.getElementById("m_registered").innerText = data.registered ?? "";

    document.getElementById("form_user_id").value = data.user_id ?? "";
    document.getElementById("form_status").value = data.status ?? "Active";

    const img = document.getElementById("modalCustomerPhoto");
    const icon = document.getElementById("modalUserIcon");

    if (data.photo && data.photo.trim() !== "") {
        img.src = data.photo;
        img.style.display = "block";
        icon.style.display = "none";
    } else {
        img.removeAttribute("src");
        img.style.display = "none";
        icon.style.display = "block";
    }
}

function closeCustomerModal(){
    document.getElementById("customerModal").style.display = "none";
}

document.getElementById("customerModal").addEventListener("click", function(e){
    if (e.target === this) {
        closeCustomerModal();
    }
});

document.getElementById("searchInput").addEventListener("keyup", function(){
    const value = this.value.toLowerCase();

    document.querySelectorAll(".customer-item").forEach(row => {
        const name = row.querySelector(".customer-name")?.innerText.toLowerCase() || "";
        const email = row.querySelector(".customer-email")?.innerText.toLowerCase() || "";

        row.style.display = (name.includes(value) || email.includes(value)) ? "" : "none";
    });
});
</script>

</body>
</html>