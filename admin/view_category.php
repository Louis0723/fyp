<?php
session_start();
include "../db.php";

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

/* =========================
   UPDATE CATEGORY
========================= */

if(isset($_POST['update_category'])){

    $old_category = $_POST['old_category'];
    $new_category = trim($_POST['new_category']);

    $stmt = $conn->prepare("
        UPDATE products
        SET category=?
        WHERE category=?
    ");

    $stmt->bind_param("ss",$new_category,$old_category);
    $stmt->execute();

    echo "
    <script>
        alert('Category updated successfully');
        window.location='view_category.php';
    </script>
    ";
}

/* =========================
   GET CATEGORY
========================= */

$sql = "
    SELECT DISTINCT category
    FROM products
    WHERE category IS NOT NULL
    AND category != ''
    ORDER BY category ASC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>View Category</title>

<link rel="stylesheet" href="style.css?v=999">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#eef3f9;
    font-family:'Segoe UI',sans-serif;
    overflow-x:hidden;
}

/* =========================
   MAIN CONTENT
========================= */

.main-content{
    margin-left:260px;
    padding:120px 30px 40px;
    min-height:100vh;
    position:relative;
    z-index:1;
}

/* =========================
   PAGE TOP
========================= */

.page-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:35px;
    gap:20px;
}

.page-title{
    font-size:62px;
    font-weight:900;
    color:#0f172a;
    margin:0;
    line-height:1;
}

.page-sub{
    margin-top:12px;
    color:#64748b;
    font-size:18px;
}

/* =========================
   BUTTON
========================= */

.add-btn{
    background:#2563eb;
    color:#fff;
    padding:18px 30px;
    border-radius:20px;
    text-decoration:none;
    font-weight:700;
    display:flex;
    align-items:center;
    gap:10px;
    font-size:18px;
    transition:.2s;
    box-shadow:0 8px 20px rgba(37,99,235,.15);
}

.add-btn:hover{
    transform:translateY(-2px);
    background:#1d4ed8;
}

/* =========================
   CARD
========================= */

.category-card{
    background:#fff;
    border-radius:32px;
    padding:30px;
    box-shadow:0 10px 30px rgba(0,0,0,.05);
    overflow:hidden;
}

/* =========================
   TABLE
========================= */

.category-table{
    width:100%;
    border-collapse:collapse;
}

.category-table th{
    text-align:left;
    padding:22px 18px;
    color:#64748b;
    border-bottom:1px solid #e2e8f0;
    font-size:16px;
}

.category-table td{
    padding:26px 18px;
    border-bottom:1px solid #edf2f7;
    font-size:16px;
}

.category-table tr:last-child td{
    border-bottom:none;
}

/* =========================
   BADGE
========================= */

.category-badge{
    background:#eff6ff;
    color:#2563eb;
    padding:12px 18px;
    border-radius:999px;
    font-weight:700;
    display:inline-block;
}

/* =========================
   ACTION
========================= */

.action-group{
    display:flex;
    gap:14px;
}

.edit-btn{
    width:46px;
    height:46px;
    border:none;
    border-radius:15px;
    display:flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    font-size:18px;
    transition:.2s;
    text-decoration:none;
    background:#dbeafe;
    color:#2563eb;
}

.edit-btn:hover{
    background:#bfdbfe;
    transform:translateY(-2px);
}

/* =========================
   MODAL
========================= */

.modal{
    position:fixed;
    inset:0;
    background:rgba(15,23,42,.65);
    display:none;
    justify-content:center;
    align-items:center;
    z-index:99999;
}

.modal.show{
    display:flex;
}

.modal-box{
    width:100%;
    max-width:760px;
    background:#fff;
    border-radius:32px;
    padding:40px;
    position:relative;
}

.modal-title{
    font-size:54px;
    font-weight:900;
    color:#0f172a;
    margin-bottom:10px;
}

.modal-sub{
    color:#64748b;
    margin-bottom:35px;
}

.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:24px;
}

.form-group{
    display:flex;
    flex-direction:column;
}

.form-group.full{
    grid-column:1 / -1;
}

.form-label{
    margin-bottom:10px;
    color:#64748b;
    font-size:15px;
}

.form-input{
    height:58px;
    border:1px solid #dbe3ef;
    border-radius:18px;
    padding:0 18px;
    font-size:16px;
    outline:none;
}

.form-input:focus{
    border-color:#2563eb;
}

.button-group{
    margin-top:35px;
    display:flex;
    justify-content:flex-end;
    gap:14px;
}

.cancel-btn{
    border:none;
    background:#e2e8f0;
    color:#0f172a;
    height:54px;
    padding:0 28px;
    border-radius:16px;
    font-weight:700;
    cursor:pointer;
}

.save-btn{
    border:none;
    background:#2563eb;
    color:#fff;
    height:54px;
    padding:0 28px;
    border-radius:16px;
    font-weight:700;
    cursor:pointer;
}

.save-btn:hover{
    background:#1d4ed8;
}

/* =========================
   RESPONSIVE
========================= */

@media(max-width:900px){

    .main-content{
        margin-left:0;
        padding:110px 15px 30px;
    }

    .page-top{
        flex-direction:column;
        align-items:flex-start;
    }

    .page-title{
        font-size:40px;
    }

    .form-grid{
        grid-template-columns:1fr;
    }
}

</style>

</head>
<body>

<?php
if(isset($_SESSION['role']) &&
$_SESSION['role']=="super_admin"){

    include "sadmin_sidebar.php";

}else{

    include "admin_sidebar.php";
}
?>
<?php include "admin_header.php"; ?>

<div class="main-content">

    <div class="page-top">

        <div>

            <h1 class="page-title">View Category</h1>

            <div class="page-sub">
                Manage all product categories.
            </div>

        </div>

        <a href="add_category.php" class="add-btn">
            <i class="fa-solid fa-plus"></i>
            Add Category
        </a>

    </div>

    <div class="category-card">

        <table class="category-table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

            <?php
            $id = 1;

            while($row = $result->fetch_assoc()):
            ?>

            <tr>

                <td>
                    <strong style="color:#2563eb;">
                        #<?= $id++ ?>
                    </strong>
                </td>

                <td>

                    <span class="category-badge">
                        <?= htmlspecialchars($row['category']) ?>
                    </span>

                </td>

                <td>

                    <div class="action-group">

                        <button
                            class="edit-btn"
                            onclick="openEditModal('<?= htmlspecialchars($row['category']) ?>')">

                            <i class="fa-solid fa-pen"></i>

                        </button>

                    </div>

                </td>

            </tr>

            <?php endwhile; ?>

            </tbody>

        </table>

    </div>

</div>

<!-- MODAL -->

<div class="modal" id="editModal">

    <div class="modal-box">

        <div class="modal-title">
            Edit Category
        </div>

        <div class="modal-sub">
            Update category information
        </div>

        <form method="POST">

            <input type="hidden"
                   name="old_category"
                   id="oldCategory">

            <div class="form-grid">

                <div class="form-group full">

                    <label class="form-label">
                        Category Name
                    </label>

                    <input
                        type="text"
                        name="new_category"
                        id="newCategory"
                        class="form-input"
                        required
                    >

                </div>

            </div>

            <div class="button-group">

                <button
                    type="button"
                    class="cancel-btn"
                    onclick="closeEditModal()">

                    Cancel

                </button>

                <button
                    type="submit"
                    name="update_category"
                    class="save-btn">

                    Save Changes

                </button>

            </div>

        </form>

    </div>

</div>

<script>

function openEditModal(category){

    document.getElementById("editModal")
    .classList.add("show");

    document.getElementById("oldCategory")
    .value = category;

    document.getElementById("newCategory")
    .value = category;
}

function closeEditModal(){

    document.getElementById("editModal")
    .classList.remove("show");
}

window.onclick = function(e){

    const modal =
    document.getElementById("editModal");

    if(e.target === modal){
        closeEditModal();
    }
}

</script>

</body>
</html>