<?php
include "../db.php";

$categories = mysqli_query($conn,"SELECT * FROM categories");

if(isset($_POST['add_field'])){

    $category_id = $_POST['category_id'];
    $field_name = $_POST['field_name'];

    mysqli_query($conn,"
        INSERT INTO category_fields (category_id, field_name)
        VALUES ($category_id,'$field_name')
    ");

    header("Location: add_fields.php");
    exit;
}
?>

<form method="POST">

<select name="category_id">
<?php while($c = mysqli_fetch_assoc($categories)): ?>
<option value="<?= $c['category_id'] ?>">
    <?= $c['category_name'] ?>
</option>
<?php endwhile; ?>
</select>

<input name="field_name" placeholder="Field Name (e.g RAM)">
<button name="add_field">Add Field</button>

</form>