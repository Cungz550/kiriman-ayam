<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['angka'])) {
    echo json_encode(['angka' => []]);
    exit;
}

echo json_encode(['angka' => $_SESSION['angka']]);
?>