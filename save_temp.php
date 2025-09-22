<?php
session_start();

if (!isset($_SESSION['angka'])) {
    $_SESSION['angka'] = [];
}

define('MAX_INPUT', 1000);
$precision = isset($_SESSION['precision']) ? (int)$_SESSION['precision'] : 2;

if (isset($_GET['angka'])) {
    if (count($_SESSION['angka']) < MAX_INPUT) {
        $angka = floatval($_GET['angka']);
        $angka = number_format($angka, $precision, '.', '');
        $_SESSION['angka'][] = $angka;
        $total = count($_SESSION['angka']);
        error_log("Angka ditambah: $angka, Total: $total");
        echo $total;
    } else {
        error_log("Batas maksimal " . MAX_INPUT . " angka tercapai");
        echo "ERROR: Batas maksimal " . MAX_INPUT . " angka tercapai";
    }
} else {
    error_log("Parameter angka tidak ditemukan");
    echo "ERROR: Parameter angka tidak ditemukan";
}
?>