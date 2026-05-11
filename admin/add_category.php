<?php
include "../db.php";
session_start();

if(isset($_POST['add_category'])){

    $name = $_POST['category_name'];

    mysqli_query($conn,"INSERT INTO categories (category_name) VALUES ('$name')");

    header("Location: add_category.php");
    exit;
}
?>

<form method="POST">
    <input name="category_name" placeholder="Category Name">
    <button name="add_category">Add Category</button>
</form>