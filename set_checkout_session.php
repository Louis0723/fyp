<?php
session_start();

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['items']) || empty($data['items'])) {
    echo json_encode(["success" => false]);
    exit;
}

$_SESSION['checkout_items'] = array_map('intval', $data['items']);

echo json_encode(["success" => true]);