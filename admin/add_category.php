<?php
session_start();
include "../db.php";

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

if(isset($_POST['save_category'])){

    $category_name = trim($_POST['category_name']);

    if($category_name != ""){

        $check = $conn->prepare("
            SELECT * FROM category
            WHERE category_name=?
        ");

        $check->bind_param("s",$category_name);
        $check->execute();

        $result = $check->get_result();

        if($result->num_rows > 0){

            echo "
            <script>
            alert('Category Already Exists');
            </script>
            ";

        }else{

            $stmt = $conn->prepare("
                INSERT INTO category(category_name)
                VALUES (?)
            ");

            $stmt->bind_param("s",$category_name);
            $stmt->execute();

            echo "
            <script>
            alert('Category Added Successfully');
            window.location='add_category.php';
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

<link rel="stylesheet" href="style.css?v=999">

<script src="https://unpkg.com/lucide@latest"></script>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial, Helvetica, sans-serif;
    background:#f1f5f9;
    overflow-x:hidden;
}

.main{
    margin-left:95px;
    transition:.3s ease;
    min-height:100vh;
}

.sidebar.expanded ~ .main{
    margin-left:250px;
}

.main-content{
    padding:130px 40px 40px;
}

.page-title{
    font-size:72px;
    font-weight:900;
    color:#0f172a;
    margin-bottom:10px;
    line-height:1;
}

.page-sub{
    color:#64748b;
    font-size:18px;
    margin-bottom:40px;
}

.category-card{
    width:100%;
    max-width:700px;
    background:#fff;
    border-radius:30px;
    padding:36px;
    box-shadow:0 5px 20px rgba(0,0,0,.04);
}

.form-group{
    margin-bottom:28px;
}

.form-group label{
    display:block;
    font-size:17px;
    font-weight:700;
    color:#0f172a;
    margin-bottom:14px;
}

.form-group input{
    width:100%;
    height:58px;
    border-radius:18px;
    border:1px solid #dbe2ea;
    background:#f8fafc;
    padding:0 20px;
    font-size:16px;
    outline:none;
    transition:.2s;
}

.form-group input:focus{
    border-color:#2563eb;
    background:#fff;
}

.btn-group{
    display:flex;
    gap:16px;
}

.add-btn{
    height:54px;
    padding:0 28px;
    border:none;
    border-radius:16px;
    background:#2563eb;
    color:#fff;
    font-size:15px;
    font-weight:700;
    cursor:pointer;
    transition:.2s;
}

.add-btn:hover{
    background:#1d4ed8;
}

.cancel-btn{
    height:54px;
    padding:0 28px;
    border:none;
    border-radius:16px;
    background:#e2e8f0;
    color:#111827;
    font-size:15px;
    font-weight:700;
    cursor:pointer;
    transition:.2s;
}

.cancel-btn:hover{
    background:#cbd5e1;
}

@media(max-width:900px){

    .main{
        margin-left:0;
    }

    .main-content{
        padding:120px 20px 20px;
    }

    .page-title{
        font-size:48px;
    }

    .page-sub{
        font-size:16px;
    }

    .category-card{
        padding:24px;
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


<div class="main">

    <?php include "admin_header.php"; ?>

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

                    <label>
                        Category Name
                    </label>

                    <input type="text"
                           name="category_name"
                           placeholder="Enter category name"
                           required>

                </div>

                <div class="btn-group">

                    <button type="submit"
                            name="save_category"
                            class="add-btn">

                        Add Category

                    </button>

                    <button type="button"
                            class="cancel-btn"
                            onclick="window.location='view_category.php'">

                        Cancel

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script src="https://unpkg.com/lucide@latest"></script>

<script>
lucide.createIcons();
</script>



</body>
</html>