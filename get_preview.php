<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['angka'])) {
    echo json_encode(['angka' => []]);
    exit;
}
$precision = isset($_SESSION['precision']) ? (int)$_SESSION['precision'] : 2;
$angka_formatted = array_map(function($a) use ($precision) {
    return number_format((float)$a, $precision, '.', '');
}, $_SESSION['angka']);

echo json_encode(['angka' => $angka_formatted]);
?>