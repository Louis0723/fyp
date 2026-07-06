<?php
session_start();

include __DIR__ . '/../db.php';

header('Content-Type: application/json');

$sql = "
SELECT
    n.id,
    n.order_id,
    n.is_read,
    n.created_at,
    oi.quantity,
    oi.price,
    p.product_name,
    p.image
FROM admin_notifications n
LEFT JOIN order_items oi
    ON n.order_id = oi.order_id
LEFT JOIN products p
    ON oi.product_id = p.product_id
WHERE oi.id = (
    SELECT MIN(id)
    FROM order_items
    WHERE order_id = n.order_id
)
ORDER BY n.id DESC
LIMIT 10
";

$result = mysqli_query($conn, $sql);

$data = [];

while($row = mysqli_fetch_assoc($result)) {

 if(empty($row['image'])) {
    $row['image'] = "../assets/images/no-image.png";
}
elseif(
    strpos($row['image'],'http') !== 0 &&
    strpos($row['image'],'uploads/') !== 0
){
    $row['image'] = "../uploads/products/".$row['image'];
}

    $data[] = $row;
}

echo json_encode($data);

exit;
?>