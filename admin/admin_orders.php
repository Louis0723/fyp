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
    DATE(o.created_at) as order_date,
    SUM(oi.quantity) AS total_qty,
    o.total_price,
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

    <button class="search-btn">
        Search
    </button>

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
         '<?= $row['total_qty'] ?>',
         '<?= number_format($row['total_price'],2) ?>',
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

<!-- ======================
     MODAL
====================== -->

<div class="modal" id="modal">

<div class="modal-box">

<div class="close-modal"
     onclick="closeModal()">

    ✕

</div>

<div class="modal-title">
    Order <span id="m_order"></span>
</div>

<div class="modal-row">
    <div class="modal-label">Customer</div>
    <div class="modal-value" id="m_customer"></div>
</div>

<div class="modal-row">
    <div class="modal-label">Email</div>
    <div class="modal-value" id="m_email"></div>
</div>

<div class="modal-row">
    <div class="modal-label">Date</div>
    <div class="modal-value" id="m_date"></div>
</div>

<div class="modal-row">
    <div class="modal-label">Items</div>
    <div class="modal-value" id="m_items"></div>
</div>

<div class="modal-row">
    <div class="modal-label">Total</div>
    <div class="modal-value">
        RM <span id="m_total"></span>
    </div>
</div>

<div class="modal-row">
    <div class="modal-label">Status</div>

    <div class="badge" id="m_status">
    </div>
</div>

</div>

</div>



<script>

lucide.createIcons();

/* ======================
   OPEN MODAL
====================== */

function openModal(id,name,email,date,items,total,status){

    document.getElementById("modal").style.display="flex";

    document.getElementById("m_order").innerText =
        "ORD-"+id;

    document.getElementById("m_customer").innerText =
        name;

    document.getElementById("m_email").innerText =
        email;

    document.getElementById("m_date").innerText =
        date;

    document.getElementById("m_items").innerText =
        items;

    document.getElementById("m_total").innerText =
        total;

    let badge =
        document.getElementById("m_status");

    badge.innerText = status;

    badge.className = "badge";

    if(status=="Pending"){
        badge.classList.add("pending");
    }

    if(status=="Shipped"){
        badge.classList.add("shipped");
    }

    if(status=="Delivered"){
        badge.classList.add("delivered");
    }

    if(status=="Completed"){
        badge.classList.add("completed");
    }

}

/* ======================
   CLOSE MODAL
====================== */

function closeModal(){

    document.getElementById("modal").style.display =
    "none";

}

window.onclick = function(e){

    let modal = document.getElementById("modal");

    if(e.target == modal){
        modal.style.display = "none";
    }

}

/* ======================
   SIDEBAR TOGGLE
====================== */

const sidebar =
document.querySelector(".sidebar");

const contentArea =
document.querySelector(".content-area");

const toggleBtn =
document.querySelector(".toggle-btn");

if(toggleBtn){

    toggleBtn.addEventListener("click", ()=>{

        sidebar.classList.toggle("collapsed");

        contentArea.classList.toggle("expanded");

    });

}

</script>

</body>
</html>