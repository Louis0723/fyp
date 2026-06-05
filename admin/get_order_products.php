<?php

include "../db.php";

$order_id = mysqli_real_escape_string(
    $conn,
    $_GET['id']
);

$sql = "

SELECT

p.product_name,
p.image,
p.category,

oi.quantity,
oi.price,

(oi.quantity * oi.price) AS total

FROM order_items oi

LEFT JOIN products p
ON oi.product_id = p.product_id

WHERE oi.order_id = '$order_id'

";

$result = $conn->query($sql);

$data = [];

while($row = $result->fetch_assoc()){

$data[] = [

    "product_name" => $row['product_name'],
    "image"        => $row['image'],
    "category"     => $row['category'],
    "quantity"     => (int)$row['quantity'],

    "price"        => (float)$row['price'],
    "total"        => (float)$row['total']

];

}

echo json_encode($data);

?>