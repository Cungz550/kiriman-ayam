<?php
session_start();
header('Content-Type: application/json');

$angka = $_SESSION['angka'] ?? [];

echo json_encode([
    'status' => 'ok',
    'data' => $angka,
    'jumlah' => count($angka)
]);
