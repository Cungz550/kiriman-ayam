<?php
session_start();

if (!isset($_SESSION['angka'])) {
    $_SESSION['angka'] = [];
}

if (count($_SESSION['angka']) > 0) {
    array_pop($_SESSION['angka']); // Hapus elemen terakhir
    // Pastikan semua angka tetap konsisten presisi setelah pop
    $precision = isset($_SESSION['precision']) ? (int)$_SESSION['precision'] : 2;
    $_SESSION['angka'] = array_map(function($a) use ($precision) {
        return number_format((float)$a, $precision, '.', '');
    }, $_SESSION['angka']);
    $total = count($_SESSION['angka']);
    error_log("Angka dihapus, Total: $total");
    echo $total; // Kirim jumlah data terbaru
} else {
    error_log("Tidak ada angka untuk dihapus");
    echo "ERROR: Tidak ada angka untuk dihapus";
}
?>