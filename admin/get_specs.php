<?php
include "../db.php";

$category = $_GET['category'];

$result = mysqli_query($conn, "
    SELECT * FROM category_specs 
    WHERE category='$category'
");

$data = [];

while($row = mysqli_fetch_assoc($result)){
    $data[] = $row;
}

echo json_encode($data);
?>
