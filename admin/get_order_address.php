<?php
include "../db.php";

if (!isset($_GET['id'])) {
    echo "No address available";
    exit;
}

$order_id = intval($_GET['id']);

$sql = "
SELECT
    u.address
FROM orders o
INNER JOIN users u
    ON o.user_id = u.user_id
WHERE o.order_id = ?
LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    if (!empty($row['address'])) {
        echo htmlspecialchars($row['address']);
    } else {
        echo "No address provided";
    }

} else {
    echo "No address available";
}
?>