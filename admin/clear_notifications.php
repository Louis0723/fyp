<?php
session_start();
include __DIR__ . "/../db.php";

$conn->query("
    DELETE FROM admin_notifications
");

echo json_encode([
    "success" => true
]);
?>