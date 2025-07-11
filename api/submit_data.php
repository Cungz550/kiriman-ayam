<?php
session_start();
header('Content-Type: application/json');
require '../db.php'; // path ini sesuaikan

$kiriman = $_SESSION['nama_kiriman'] ?? null;
$angka = $_SESSION['angka'] ?? [];

if (!$kiriman || count($angka) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Tidak ada data untuk disimpan']);
    exit;
}

// Simpan ke database
$stmt = $conn->prepare("INSERT INTO data_input (kiriman, angka) VALUES (?, ?)");
foreach ($angka as $nilai) {
    $stmt->bind_param("ss", $kiriman, $nilai);
    $stmt->execute();
}

// Kosongkan session angka
$_SESSION['angka'] = [];

echo json_encode(['status' => 'ok', 'message' => 'Data berhasil disimpan']);
