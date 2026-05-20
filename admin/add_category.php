<?php
session_start();
include "../db.php";

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

/* =========================
   ADD CATEGORY
========================= */

if(isset($_POST['add_category'])){

    $category_name = trim($_POST['category_name']);

    if($category_name != ""){

        /* CHECK USING PRODUCTS TABLE */

        $check = $conn->prepare("
            SELECT *
            FROM products
            WHERE category=?
            LIMIT 1
        ");

        $check->bind_param("s",$category_name);
        $check->execute();

        $result = $check->get_result();

        if($result->num_rows > 0){

            echo "
            <script>
                alert('Category already exists');
            </script>
            ";

        }else{

            echo "
            <script>
                alert('Category added successfully');
                window.location='view_category.php';
            </script>
            ";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Category</title>

<link rel="stylesheet" href="style.css?v=500">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

body{
    margin:0;
    background:#eef3f9;
    font-family:'Segoe UI',sans-serif;
    overflow-x:hidden;
}

/* FIX SIDEBAR */

.sidebar{
    z-index:9999 !important;
}

/* MAIN CONTENT */

.main-content{

    margin-left:275px;

    padding:120px 30px 40px;

    min-height:100vh;

    box-sizing:border-box;
}

/* TITLE */

.page-title{
    font-size:58px;
    font-weight:800;
    color:#0f172a;
    margin-bottom:10px;
}

.page-sub{
    color:#64748b;
    margin-bottom:35px;
    font-size:17px;
}

/* CARD */

.category-card{

    width:100%;
    max-width:760px;

    background:#fff;

    border-radius:32px;

    padding:40px;

    box-shadow:0 10px 30px rgba(0,0,0,.05);

    box-sizing:border-box;
}

/* FORM */

.form-group{
    margin-bottom:28px;
}

.form-label{
    display:block;

    font-size:16px;
    font-weight:700;

    margin-bottom:12px;

    color:#0f172a;
}

.form-input{

    width:100%;
    height:60px;

    border:1px solid #dbe3ef;
    border-radius:18px;

    padding:0 20px;

    font-size:15px;

    outline:none;

    box-sizing:border-box;

    transition:0.2s;
}

.form-input:focus{
    border-color:#2563eb;
}

/* BUTTONS */

.button-group{
    display:flex;
    gap:16px;
    margin-top:10px;
}

.save-btn{

    height:54px;
    padding:0 30px;

    border:none;
    border-radius:16px;

    background:#2563eb;
    color:#fff;

    font-size:15px;
    font-weight:700;

    cursor:pointer;

    transition:0.2s;
}

.save-btn:hover{
    background:#1d4ed8;
}

.cancel-btn{

    height:54px;
    padding:0 30px;

    border:none;
    border-radius:16px;

    background:#e2e8f0;
    color:#0f172a;

    font-size:15px;
    font-weight:700;

    cursor:pointer;

    transition:0.2s;
}

.cancel-btn:hover{
    background:#cbd5e1;
}

/* RESPONSIVE */

@media(max-width:900px){

    .main-content{
        margin-left:0;
        padding:110px 18px 30px;
    }

    .page-title{
        font-size:42px;
    }

    .category-card{
        padding:25px;
    }

    .button-group{
        flex-direction:column;
    }

    .save-btn,
    .cancel-btn{
        width:100%;
    }
}

</style>

</head>

<body>

<!-- SIDEBAR -->
<?php include "admin_sidebar.php"; ?>

<!-- HEADER -->
<div class="admin-header">

    <div class="header-left">

        <button class="toggle-btn" id="toggleSidebar">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="search-box">

            <i class="fa-solid fa-magnifying-glass"></i>

            <input type="text" placeholder="Search...">

        </div>

    </div>

    <div class="header-right">

        <div class="notif">

            <i class="fa-regular fa-bell"></i>

            <span class="badge">24</span>

        </div>

        <div class="avatar">
            ZI
        </div>

    </div>

</div>

<!-- MAIN -->
<div class="main-content">

    <div class="page-title">
        Add Category
    </div>

    <div class="page-sub">
        Create new product category.
    </div>

    <div class="category-card">

        <form method="POST">

            <div class="form-group">

                <label class="form-label">
                    Category Name
                </label>

                <input
                    type="text"
                    name="category_name"
                    class="form-input"
                    placeholder="Enter category name"
                    required
                >

            </div>

            <div class="button-group">

                <button
                    type="submit"
                    name="add_category"
                    class="save-btn">

                    Add Category

                </button>

                <button
                    type="button"
                    class="cancel-btn"
                    onclick="window.location='view_category.php'">

                    Cancel

                </button>

            </div>

        </form>

    </div>

</div>

<script>

const toggleBtn =
document.getElementById("toggleSidebar");

const sidebar =
document.getElementById("sidebar");

toggleBtn.onclick = function(){

    sidebar.classList.toggle("collapsed");

};

</script>

</body>
</html>