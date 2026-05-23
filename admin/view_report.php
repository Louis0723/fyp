<?php
include "../db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

/* =========================
DAILY SALES
========================= */

$selected_date = isset($_GET['date'])
    ? $_GET['date']
    : date('Y-m-d');

$daily_total = 0;

$daily_query = mysqli_query($conn,"
SELECT 
    o.order_id,
    o.total_price,
    o.created_at,
    u.name
FROM orders o
LEFT JOIN users u ON o.user_id = u.user_id
WHERE DATE(o.created_at) = '$selected_date'
ORDER BY o.created_at DESC
");

while($row = mysqli_fetch_assoc($daily_query)){
    $daily_total += $row['total_price'];
}

/* =========================
MONTHLY SALES
========================= */

$current_month = date('Y-m', strtotime($selected_date));

$monthly_total = 0;

$monthly_query = mysqli_query($conn,"
SELECT 
    o.order_id,
    o.total_price,
    o.created_at,
    u.name
FROM orders o
LEFT JOIN users u ON o.user_id = u.user_id
WHERE DATE_FORMAT(o.created_at,'%Y-%m') = '$current_month'
ORDER BY o.created_at DESC
");

while($mrow = mysqli_fetch_assoc($monthly_query)){
    $monthly_total += $mrow['total_price'];
}
?>

<!DOCTYPE html>
<html>
<head>

<title>View Report</title>

<link rel="stylesheet" href="style.css?v=100">

<script src="https://unpkg.com/lucide@latest"></script>

<style>

body{
    background:#eef2f7;
    font-family:Segoe UI;
}

/* MAIN */
.main-content{
    margin-left:270px;
    margin-top:100px;
    padding:30px;
    transition:.3s;
}

.sidebar.collapsed ~ .main-content{
    margin-left:95px;
}

/* TITLE */
.report-title{
    font-size:48px;
    font-weight:800;
    color:#0f172a;
    margin-bottom:30px;
}

/* FILTER */
.filter-box{
    display:flex;
    gap:12px;
    align-items:center;
    margin-bottom:30px;
}

.filter-box input{
    height:52px;
    padding:0 18px;
    border:none;
    border-radius:14px;
    background:#fff;
    box-shadow:0 5px 15px rgba(0,0,0,.06);
    font-size:15px;
}

.btn-primary{
    height:52px;
    padding:0 24px;
    border:none;
    border-radius:14px;
    background:#4f46e5;
    color:#fff;
    font-weight:700;
    cursor:pointer;
}

.btn-primary:hover{
    background:#4338ca;
}

/* GRID */
.report-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:28px;
    margin-top:10px;
}

/* CARD */
.report-card{
    background:#fff;
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 10px 30px rgba(0,0,0,.06);
}

/* CARD HEADER */
.card-header{
    padding:20px 24px;
    border-bottom:1px solid #e5e7eb;
    font-size:22px;
    font-weight:700;
    color:#0f172a;
}

/* CARD BODY */
.card-body{
    padding:24px;
    min-height:350px;
}

/* TOTAL */
.total-box{
    background:#f8fafc;
    border-radius:16px;
    padding:24px;
    margin-bottom:20px;
}

.total-label{
    color:#64748b;
    font-size:14px;
    margin-bottom:8px;
}

.total-value{
    font-size:40px;
    font-weight:800;
    color:#2563eb;
}

/* SALES TABLE */
.sales-table{
    width:100%;
    border-collapse:collapse;
}

.sales-table th{
    text-align:left;
    padding:12px;
    background:#f8fafc;
    color:#64748b;
    font-size:14px;
}

.sales-table td{
    padding:14px 12px;
    border-bottom:1px solid #f1f5f9;
}

.sales-table tr:hover{
    background:#f8fbff;
}

/* BUTTONS */
.bottom-actions{
    display:flex;
    justify-content:center;
    gap:16px;
    margin-top:40px;
}

.btn-print{
    background:#2563eb;
    color:#fff;
    border:none;
    border-radius:14px;
    padding:14px 28px;
    font-weight:700;
    cursor:pointer;
}

.btn-pdf{
    background:#0f172a;
    color:#fff;
    border:none;
    border-radius:14px;
    padding:14px 28px;
    font-weight:700;
    cursor:pointer;
}

/* RESPONSIVE */
@media(max-width:1100px){

.report-grid{
    grid-template-columns:1fr;
}

}

@media print{

.sidebar,
.top-header,
.filter-box,
.bottom-actions{
    display:none !important;
}

.main-content{
    margin:0 !important;
    padding:0 !important;
}

body{
    background:#fff;
}

.report-card{
    box-shadow:none;
}

}
</style>

</head>

<body>

<?php include "admin_sidebar.php"; ?>

<div class="top-header">
<?php include "admin_header.php"; ?>
</div>

<div class="main-content">

<div class="report-title">
View Report
</div>

<form method="GET" class="filter-box">

<input 
type="date"
name="date"
value="<?= $selected_date ?>"
required>

<button class="btn-primary">
Get Report
</button>

</form>

<div id="reportArea">

<div class="report-grid">

    <!-- DAILY -->
    <div class="report-card">

        <div class="card-header">
            Daily Sales
        </div>

        <div class="card-body">

            <div class="total-box">

                <div class="total-label">
                    Total Daily Sales
                </div>

                <div class="total-value">
                    RM <?= number_format($daily_total,2) ?>
                </div>

            </div>

            <table class="sales-table">

                <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                </tr>
                </thead>

                <tbody>

                <?php
                mysqli_data_seek($daily_query,0);

                while($row = mysqli_fetch_assoc($daily_query)):
                ?>

                <tr>
                    <td>#<?= $row['order_id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td>
                        RM <?= number_format($row['total_price'],2) ?>
                    </td>
                </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    </div>

    <!-- MONTHLY -->
    <div class="report-card">

        <div class="card-header">
            Monthly Sales
        </div>

        <div class="card-body">

            <div class="total-box">

                <div class="total-label">
                    Total Monthly Sales
                </div>

                <div class="total-value">
                    RM <?= number_format($monthly_total,2) ?>
                </div>

            </div>

            <table class="sales-table">

                <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                </tr>
                </thead>

                <tbody>

                <?php
                mysqli_data_seek($monthly_query,0);

                while($mrow = mysqli_fetch_assoc($monthly_query)):
                ?>

                <tr>
                    <td>#<?= $mrow['order_id'] ?></td>
                    <td><?= htmlspecialchars($mrow['name']) ?></td>
                    <td>
                        RM <?= number_format($mrow['total_price'],2) ?>
                    </td>
                </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</div>

<!-- BUTTONS -->
<div class="bottom-actions">

<button class="btn-print" onclick="printReport()">
Print
</button>

<button class="btn-pdf" onclick="downloadPDF()">
Download PDF
</button>

</div>

</div>

<!-- html2pdf -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
lucide.createIcons();

/* PRINT */
function printReport(){
    window.print();
}

/* PDF */
function downloadPDF(){

    const element =
    document.getElementById("reportArea");

    const opt = {

        margin:0.5,

        filename:
        'sales-report.pdf',

        image:{
            type:'jpeg',
            quality:1
        },

        html2canvas:{
            scale:2
        },

        jsPDF:{
            unit:'in',
            format:'a4',
            orientation:'landscape'
        }
    };

    html2pdf()
    .set(opt)
    .from(element)
    .save();
}
</script>

<script src="admin.js"></script>

</body>
</html>