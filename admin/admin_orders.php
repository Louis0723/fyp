<?php
session_start();
include "../db.php";

if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit();
}

/* =========================
   UPDATE STATUS
========================= */
if(isset($_POST['update_status'])){

    $order_id = $_POST['order_id'];
    $status   = $_POST['status'];

    $stmt = $conn->prepare("
        UPDATE orders 
        SET status=? 
        WHERE order_id=?
    ");

    $stmt->bind_param("si",$status,$order_id);
    $stmt->execute();

    header("Location: admin_orders.php");
    exit();
}

/* =========================
   SEARCH + FILTER
========================= */

$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';

$sql = "
SELECT
    o.order_id,
    u.name AS user_name,
    u.email,
    DATE(o.created_at) AS order_date,

    SUM(oi.quantity) AS total_qty,

    (
        SUM(oi.price * oi.quantity)
        + (SUM(oi.price * oi.quantity) * 0.06)
        + 5
    ) AS total_price,

    o.status

FROM orders o

JOIN users u
ON o.user_id = u.user_id

LEFT JOIN order_items oi
ON o.order_id = oi.order_id

WHERE 1
";

if($search != ''){
    $search = $conn->real_escape_string($search);

    $sql .= "
    AND (
        o.order_id LIKE '%$search%'
        OR u.name LIKE '%$search%'
        OR u.email LIKE '%$search%'
    )
    ";
}

if($filter != ''){
    $filter = $conn->real_escape_string($filter);

    $sql .= "
    AND o.status='$filter'
    ";
}

$sql .= "
GROUP BY o.order_id
ORDER BY o.created_at DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<title>Orders</title>

<link rel="stylesheet" href="style.css?v=9">

<script src="https://unpkg.com/lucide@latest"></script>

<style>

/* =========================
   GOOGLE FONT
========================= */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

body{
    font-family:'Plus Jakarta Sans',sans-serif;
    background:#f4f7fb;
    color:#0f172a;
}

/* =========================
   PAGE
========================= */

.content-area{
    margin-left:260px;
    margin-top:95px;
    padding:30px;
    transition:.3s ease;
}

/* WHEN SIDEBAR COLLAPSED */

.sidebar.collapsed ~ .content-area{
    margin-left:90px;
}

.page-title{
    font-size:40px;
    font-weight:800;
    letter-spacing:-1.5px;
    color:#0f172a;
    margin-bottom:8px;
}

.page-sub{
    font-size:15px;
    color:#64748b;
    font-weight:500;
    margin-bottom:30px;
}

/* =========================
   FILTER BAR
========================= */

.filter-bar{
    display:flex;
    align-items:center;
    gap:14px;
    margin-bottom:28px;
}

.search-box{
    position:relative;
}

.search-box i{
    position:absolute;
    left:18px;
    top:50%;
    transform:translateY(-50%);
    color:#94a3b8;
    width:20px;
    height:20px;
}

.search-box input{
    width:460px;
    height:54px;

    padding:0 18px 0 50px;

    border-radius:14px;
    border:1px solid #dbe3ef;

    background:#fff;
    outline:none;

    font-size:15px;
    font-weight:500;
    font-family:'Plus Jakarta Sans',sans-serif;

    transition:.2s;
}

.search-box input::placeholder{
    color:#94a3b8;
}

.search-box input:focus{
    border-color:#2563eb;
    box-shadow:0 0 0 4px rgba(37,99,235,.08);
}

.filter-select{
    width:180px;
    height:54px;

    border-radius:14px;
    border:1px solid #dbe3ef;

    background:#fff;
    outline:none;

    padding:0 16px;

    font-size:14px;
    font-weight:600;
    font-family:'Plus Jakarta Sans',sans-serif;
}

.search-btn{
    height:54px;
    padding:0 28px;

    border:none;
    border-radius:14px;

    background:#2563eb;
    color:#fff;

    font-size:14px;
    font-weight:700;
    font-family:'Plus Jakarta Sans',sans-serif;

    cursor:pointer;
    transition:.2s;
}

.search-btn:hover{
    background:#1d4ed8;
}

/* =========================
   TABLE CARD
========================= */

.table-card{
    background:#fff;
    border-radius:24px;

    padding:10px 18px;

    border:1px solid #e5e7eb;

    box-shadow:0 5px 20px rgba(0,0,0,.03);
}

/* =========================
   TABLE
========================= */

table{
    width:100%;
    border-collapse:collapse;
}

th{
    padding:22px 14px;

    text-align:left;

    font-size:14px;
    font-weight:700;

    color:#64748b;
}

td{
    padding:20px 14px;

    border-top:1px solid #edf2f7;

    vertical-align:middle;

    font-size:14px;
    font-weight:500;
}

/* =========================
   ORDER ID
========================= */

.order-id{
    font-size:14px;
    font-weight:600;
    color:#0f172a;
}

/* =========================
   CUSTOMER
========================= */

.customer-name{
    font-size:15px;
    font-weight:700;
    color:#0f172a;
    line-height:1.2;
}

.customer-email{
    margin-top:4px;

    font-size:12px;
    font-weight:500;

    color:#64748b;
}

/* =========================
   DATE / ITEMS
========================= */

.order-date,
.order-items{
    font-size:13px;
    font-weight:500;
    color:#475569;
}

/* =========================
   TOTAL
========================= */

.total{
    font-size:15px;
    font-weight:700;
    color:#0f172a;
}

/* =========================
   STATUS
========================= */

.status-select{
    width:125px;
    height:40px;

    border-radius:12px;
    border:1px solid #dbe3ef;

    background:#fff;
    outline:none;

    padding:0 12px;

    font-size:13px;
    font-weight:600;
    font-family:'Plus Jakarta Sans',sans-serif;
}

.update-btn{
    height:40px;
    padding:0 15px;

    border:none;
    border-radius:12px;

    background:#2563eb;
    color:#fff;

    font-size:13px;
    font-weight:700;
    font-family:'Plus Jakarta Sans',sans-serif;

    cursor:pointer;
}

/* =========================
   ACTIONS
========================= */

.actions{
    display:flex;
    flex-direction:column;
    gap:10px;
}

.action-btn{
    display:flex;
    align-items:center;
    gap:6px;

    text-decoration:none;

    font-size:13px;
    font-weight:600;
    color:#0f172a;

    cursor:pointer;
}

.action-btn i{
    width:17px;
    height:17px;
}

.invoice-btn{
    color:#2563eb;
}

/* =========================
   MODAL
========================= */

.modal{
    position:fixed;
    inset:0;

    background:rgba(15,23,42,.55);

    display:none;
    align-items:center;
    justify-content:center;

    z-index:999;

    backdrop-filter:blur(5px);
}

.modal-box{
    width:520px;

    background:#fff;

    border-radius:24px;

    padding:32px;
}

@keyframes popup{
    from{
        opacity:0;
        transform:scale(.9);
    }
    to{
        opacity:1;
        transform:scale(1);
    }
}

.close-modal{
    position:absolute;
    top:22px;
    right:22px;

    width:42px;
    height:42px;

    border-radius:50%;
    border:2px solid #dbeafe;

    display:flex;
    align-items:center;
    justify-content:center;

    color:#2563eb;

    cursor:pointer;

    font-size:18px;
    font-weight:700;
}

.modal-title{
    font-size:42px;
    font-weight:800;
    letter-spacing:-1px;

    margin-bottom:30px;
}

.modal-row{
    display:flex;
    justify-content:space-between;
    align-items:center;

    margin-bottom:24px;
}

.modal-label{
    font-size:17px;
    font-weight:600;
    color:#64748b;
}

.modal-value{
    font-size:20px;
    font-weight:700;
    color:#0f172a;
}

.badge{
    padding:10px 18px;
    border-radius:12px;

    font-size:14px;
    font-weight:700;
}

.pending{
    background:#e2e8f0;
    color:#0f172a;
}

.shipped{
    background:#dbeafe;
    color:#2563eb;
}

.delivered{
    background:#2563eb;
    color:#fff;
}

.completed{
    background:#10b981;
    color:#fff;
}

/* =========================
   RESPONSIVE
========================= */

@media(max-width:1200px){

    .content-area{
        margin-left:90px;
    }

    .page-title{
        font-size:42px;
    }

    .search-box input{
        width:100%;
    }

}

@media(max-width:900px){

    .filter-bar{
        flex-direction:column;
        align-items:stretch;
    }

    .search-box input,
    .filter-select,
    .search-btn{
        width:100%;
    }

}

.refresh-btn{
    height:54px;
    padding:0 24px;

    border:none;
    border-radius:14px;

    background:#0f172a;
    color:#fff;

    font-size:14px;
    font-weight:700;
    font-family:'Plus Jakarta Sans',sans-serif;

    cursor:pointer;

    display:flex;
    align-items:center;
    gap:8px;

    transition:.2s;
}

.refresh-btn:hover{
    background:#1e293b;
}

.refresh-btn i{
    width:18px;
    height:18px;
}

/* FIX HEADER AVATAR */

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

/* DROPDOWN PROFILE AVATAR */

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
if(isset($_SESSION['role']) && $_SESSION['role']=="super_admin"){
    include "sadmin_sidebar.php";
}else{
    include "admin_sidebar.php";
}
?>
<?php include "admin_header.php"; ?>

<div class="content-area">

<div class="page-title">
    Orders
</div>

<div class="page-sub">
    Track and manage all customer orders.
</div>

<!-- ======================
     SEARCH + FILTER
====================== -->

<form method="GET" class="filter-bar">

    <div class="search-box">
        <i data-lucide="search"></i>

        <input
            type="text"
            name="search"
            placeholder="Search orders..."
            value="<?= htmlspecialchars($search) ?>"
        >
    </div>

    <select class="filter-select" name="filter">

        <option value="">All statuses</option>

        <option value="Pending"
        <?= $filter=="Pending" ? "selected" : "" ?>>
            Pending
        </option>

        <option value="Shipped"
        <?= $filter=="Shipped" ? "selected" : "" ?>>
            Shipped
        </option>

        <option value="Delivered"
        <?= $filter=="Delivered" ? "selected" : "" ?>>
            Delivered
        </option>

        <option value="Completed"
        <?= $filter=="Completed" ? "selected" : "" ?>>
            Completed
        </option>

    </select>

<div style="display:flex; gap:12px;">

    <button class="search-btn" type="submit">
        Search
    </button>

    <button 
        type="button"
        class="refresh-btn"
        onclick="window.location.href='admin_orders.php'">

        <i data-lucide="refresh-cw"></i>
        Refresh

    </button>

</div>

</form>

<!-- ======================
     TABLE
====================== -->

<div class="table-card">

<table>

<thead>

<tr>
    <th>Order ID</th>
    <th>Customer</th>
    <th>Date</th>
    <th>Items</th>
    <th>Total</th>
    <th>Status</th>
    <th>Actions</th>
</tr>

</thead>

<tbody>

<?php while($row = $result->fetch_assoc()): ?>

<tr>

<td>
    ORD-<?= $row['order_id'] ?>
</td>

<td>

    <div class="customer-name">
        <?= htmlspecialchars($row['user_name']) ?>
    </div>

    <div class="customer-email">
        <?= htmlspecialchars($row['email']) ?>
    </div>

</td>

<td>
    <?= $row['order_date'] ?>
</td>

<td>
    <?= $row['total_qty'] ?>
</td>

<td class="total">
    RM <?= number_format($row['total_price'],2) ?>
</td>

<td>

<form method="POST" style="display:flex; align-items:center; gap:10px;">

    <input type="hidden" name="order_id"
           value="<?= $row['order_id'] ?>">

    <select class="status-select" name="status">

        <option value="Pending"
        <?= $row['status']=="Pending" ? "selected" : "" ?>>
            Pending
        </option>

        <option value="Shipped"
        <?= $row['status']=="Shipped" ? "selected" : "" ?>>
            Shipped
        </option>

        <option value="Delivered"
        <?= $row['status']=="Delivered" ? "selected" : "" ?>>
            Delivered
        </option>

        <option value="Completed"
        <?= $row['status']=="Completed" ? "selected" : "" ?>>
            Completed
        </option>

    </select>

    <button
        class="update-btn"
        type="submit"
        name="update_status">

        Update

    </button>

</form>

</td>

<td>

<div class="actions">

    <!-- VIEW -->
<div class="action-btn"

onclick="openModal(
'<?= $row['order_id'] ?>',
'<?= addslashes($row['user_name']) ?>',
'<?= addslashes($row['email']) ?>',
'<?= $row['order_date'] ?>',
'<?= $row['status'] ?>'
)">

<i data-lucide="eye"></i>
View

</div>

    <!-- DOWNLOAD -->
        <a
        class="action-btn download"
        href="admin_invoice.php?id=<?= $row['order_id'] ?>"
        target="_blank">

        <i data-lucide="download"></i>
        Invoice

    </a>

</div>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

<!-- =========================
     ORDER DETAIL MODAL
========================= -->

<!-- =========================
     ORDER DETAIL MODAL
========================= -->

<div class="modal" id="modal">

    <div class="order-detail-modal">

        <!-- CLOSE -->

        <div class="close-modal"
             onclick="closeModal()">
            ✕
        </div>

        <!-- TOP -->

        <div class="order-top">

            <div>

                <div class="order-title">
                    Order #<span id="m_order"></span>
                </div>

                <div class="order-subtitle">
                    Customer purchase details
                </div>

            </div>

            <div class="status-badge"
                 id="m_status_badge">
            </div>

        </div>

        <!-- INFO -->

        <div class="info-grid">

            <div class="info-card">

                <div class="info-label">
                    Customer Name
                </div>

                <div class="info-value"
                     id="m_customer">
                </div>

            </div>

            <div class="info-card">

                <div class="info-label">
                    Email Address
                </div>

                <div class="info-value"
                     id="m_email">
                </div>

            </div>

            <div class="info-card">

                <div class="info-label">
                    Order Date
                </div>

                <div class="info-value"
                     id="m_date">
                </div>

            </div>

          <div class="info-card">

    <div class="info-label">
        Payment Method
    </div>

    <div class="info-value">
        Online Banking
    </div>

</div>

<div class="info-card">

    <div class="info-label">
        Shipping Address
    </div>

    <div class="info-value"
         id="m_address">
        Loading...
    </div>

</div>

        </div>

        <!-- PRODUCTS -->

        <div class="section-title">
            Purchased Products
        </div>

        <div id="product_list"></div>

        <!-- SUMMARY -->

        <div class="summary-box">

            <div class="summary-row">

                <span>Subtotal</span>

                <span id="subtotal">
                    RM 0.00
                </span>

            </div>

            <div class="summary-row">

                <span>SST (6%)</span>

                <span id="sst">
                    RM 0.00
                </span>

            </div>

            <div class="summary-row">

                <span>Shipping Fee</span>

                <span>RM 5.00</span>

            </div>

            <div class="summary-total">

                <span>Total Amount</span>

                <span id="grand_total">
                    RM 0.00
                </span>

            </div>

        </div>

    </div>

</div>

<style>

/* =========================
   MODAL BACKGROUND
========================= */

.modal{

    position:fixed;
    inset:0;

    background:rgba(15,23,42,.65);

    display:none;

    justify-content:center;
    align-items:flex-start;

    overflow-y:auto;

    padding:110px 20px 40px;

    z-index:999999;

    backdrop-filter:blur(5px);
}

/* =========================
   MODAL BOX
========================= */

.order-detail-modal{

    width:900px;
    max-width:100%;

    background:#fff;

    border-radius:30px;

    padding:35px;

    position:relative;

    margin:auto;

    animation:popup .25s ease;

    box-shadow:0 20px 60px rgba(0,0,0,.2);
}

/* =========================
   ANIMATION
========================= */

@keyframes popup{

    from{
        opacity:0;
        transform:translateY(25px) scale(.96);
    }

    to{
        opacity:1;
        transform:translateY(0) scale(1);
    }

}

/* =========================
   CLOSE BUTTON
========================= */

.close-modal{

    position:absolute;

    top:20px;
    right:20px;

    width:44px;
    height:44px;

    border-radius:50%;

    background:#f1f5f9;

    display:flex;
    align-items:center;
    justify-content:center;

    cursor:pointer;

    font-size:18px;
    font-weight:700;

    color:#0f172a;

    transition:.2s;
}

.close-modal:hover{

    background:#e2e8f0;

    transform:rotate(90deg);
}

/* =========================
   TOP
========================= */

.order-top{

    display:flex;
    justify-content:space-between;
    align-items:flex-start;

    gap:20px;

    margin-bottom:35px;
}

.order-title{

    font-size:42px;
    font-weight:900;

    color:#0f172a;

    line-height:1.1;
}

.order-subtitle{

    margin-top:10px;

    color:#64748b;

    font-size:15px;
}

/* =========================
   STATUS
========================= */

.status-badge{

    padding:12px 20px;

    border-radius:14px;

    font-size:14px;
    font-weight:700;
}

/* =========================
   INFO GRID
========================= */

.info-grid{

    display:grid;

    grid-template-columns:repeat(2,1fr);

    gap:20px;

    margin-bottom:35px;
}

.info-card{

    background:#f8fafc;

    border-radius:20px;

    padding:22px;

    border:1px solid #e2e8f0;
}

.info-label{

    color:#64748b;

    font-size:14px;

    margin-bottom:8px;
}

.info-value{

    color:#0f172a;

    font-size:20px;
    font-weight:700;

    word-break:break-word;
}

/* =========================
   TITLE
========================= */

.section-title{

    font-size:26px;
    font-weight:800;

    margin-bottom:22px;

    color:#0f172a;
}

/* =========================
   PRODUCT CARD
========================= */

.product-card{

    display:flex;
    justify-content:space-between;
    align-items:center;

    gap:20px;

    padding:18px 20px;

    border:1px solid #e2e8f0;

    border-radius:20px;

    margin-bottom:15px;

    background:#fff;
}

.product-left{

    display:flex;
    align-items:center;

    gap:18px;

    flex:1;
}

.product-img{

    width:72px;
    height:72px;

    border-radius:14px;

    object-fit:cover;

    border:1px solid #e2e8f0;

    background:#fff;
}

.product-name{

    font-size:18px;
    font-weight:700;

    color:#0f172a;

    line-height:1.4;
}

.product-category{

    margin-top:5px;

    color:#64748b;

    font-size:14px;
}

.product-right{

    text-align:right;
}

.product-qty{

    color:#64748b;

    margin-bottom:6px;

    font-size:14px;
}

.product-price{

    font-size:28px;
    font-weight:800;

    color:#2563eb;
}

/* =========================
   SUMMARY
========================= */

.summary-box{

    margin-top:35px;

    background:#f8fafc;

    border-radius:22px;

    padding:25px;

    border:1px solid #e2e8f0;
}

.summary-row{

    display:flex;
    justify-content:space-between;

    margin-bottom:15px;

    color:#475569;

    font-size:17px;
}

.summary-total{

    margin-top:20px;

    padding-top:20px;

    border-top:1px solid #cbd5e1;

    display:flex;
    justify-content:space-between;

    font-size:30px;
    font-weight:900;

    color:#0f172a;
}

/* =========================
   RESPONSIVE
========================= */

@media(max-width:900px){

    .modal{
        padding:90px 15px 30px;
    }

    .order-detail-modal{
        padding:25px;
    }

    .order-top{
        flex-direction:column;
    }

    .order-title{
        font-size:30px;
    }

    .info-grid{
        grid-template-columns:1fr;
    }

    .product-card{
        flex-direction:column;
        align-items:flex-start;
    }

    .product-right{
        width:100%;
        text-align:left;
    }

    .summary-total{
        font-size:24px;
    }

}

</style>

<script>

/* =========================
   OPEN MODAL
========================= */

function openModal(id,name,email,date,status){

    const modal = document.getElementById("modal");

    modal.style.display = "flex";
    document.body.style.overflow = "hidden";

    document.getElementById("m_order").innerText = id;
    document.getElementById("m_customer").innerText = name;
    document.getElementById("m_email").innerText = email;
    document.getElementById("m_date").innerText = date;

    document.getElementById("m_address").innerText = "Loading...";

    /* STATUS */

    const badge = document.getElementById("m_status_badge");

    badge.innerText = status;
    badge.style.background = "#e2e8f0";
    badge.style.color = "#0f172a";

    if(status === "Pending"){
        badge.style.background = "#fef3c7";
        badge.style.color = "#92400e";
    }

    if(status === "Shipped"){
        badge.style.background = "#dbeafe";
        badge.style.color = "#1d4ed8";
    }

    if(status === "Delivered"){
        badge.style.background = "#ddd6fe";
        badge.style.color = "#6d28d9";
    }

    if(status === "Completed"){
        badge.style.background = "#dcfce7";
        badge.style.color = "#166534";
    }

    /* ADDRESS */

    fetch("get_order_address.php?id=" + id)
    .then(response => response.text())
    .then(address => {

        document.getElementById("m_address").innerText =
            address || "No address available";

    })
    .catch(() => {

        document.getElementById("m_address").innerText =
            "No address available";

    });

    /* PRODUCTS */

    fetch("get_order_products.php?id=" + id)
    .then(response => response.json())
    .then(data => {

    let html = "";
    let subtotal = 0;

    document.getElementById("product_list").innerHTML = "";

    data.forEach(item => {

        const itemTotal =
            parseFloat(item.price) * parseInt(item.quantity);

        subtotal += itemTotal;

        let imagePath = item.image || "";

        if(
            imagePath &&
            !imagePath.startsWith("http") &&
            !imagePath.startsWith("../")
        ){
            imagePath = "../" + imagePath;
        }

        if(!imagePath){
            imagePath = "../uploads/no-image.png";
        }

        html += `
        <div class="product-card">

            <div class="product-left">

                <img
                    src="${imagePath}"
                    class="product-img"
                    onerror="this.src='../uploads/no-image.png'">

                <div>

                    <div class="product-name">
                        ${item.product_name}
                    </div>

                    <div class="product-category">
                        ${item.category}
                    </div>

                </div>

            </div>

            <div class="product-right">

                <div class="product-qty">
                    Qty: ${item.quantity}
                </div>

                <div class="product-price">
                    RM ${itemTotal.toLocaleString('en-MY',{
                        minimumFractionDigits:2,
                        maximumFractionDigits:2
                    })}
                </div>

            </div>

        </div>
        `;
    });

    document.getElementById("product_list").innerHTML = html;

    const sst = subtotal * 0.06;
    const shipping = 5;
    const grand = subtotal + sst + shipping;

    document.getElementById("subtotal").innerText =
        "RM " + subtotal.toLocaleString('en-MY',{
            minimumFractionDigits:2,
            maximumFractionDigits:2
        });

    document.getElementById("sst").innerText =
        "RM " + sst.toLocaleString('en-MY',{
            minimumFractionDigits:2,
            maximumFractionDigits:2
        });

    document.getElementById("grand_total").innerText =
        "RM " + grand.toLocaleString('en-MY',{
            minimumFractionDigits:2,
            maximumFractionDigits:2
        });

})
    .catch(error => {

        console.log(error);

        document.getElementById("product_list").innerHTML = `
            <div style="
                padding:30px;
                text-align:center;
                color:red;
            ">
                Failed to load order products
            </div>
        `;
    });

}



/* =========================
   CLOSE
========================= */

function closeModal(){

document.getElementById("modal")
.style.display = "none";

document.body.style.overflow = "auto";

}

/* =========================
   CLOSE OUTSIDE
========================= */

window.onclick = function(event){

const modal =
document.getElementById("modal");

if(event.target == modal){

    closeModal();

}

}

</script>
<script src="https://unpkg.com/lucide@latest"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="bootstrap.bundle.js"></script>


</body>
</html>