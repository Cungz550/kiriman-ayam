<?php
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

$kiriman = $data['kiriman'] ?? '';
$angka = $data['angka'] ?? [];

if (!$kiriman || !is_array($angka)) {
    http_response_code(400);
    echo json_encode(['error' => 'Data tidak valid']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO data_input (kiriman, nilai) VALUES (?, ?)");
foreach ($angka as $nilai) {
    $stmt->bind_param("sd", $kiriman, $nilai);
    $stmt->execute();
}

echo json_encode(['status' => 'success']);
