<?php
session_start();
include __DIR__ . "/../db.php";

$conn->query("
    UPDATE admin_notifications
    SET is_read = 1
");

echo json_encode([
    "success" => true
]);
?>