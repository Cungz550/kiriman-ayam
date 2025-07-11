<?php
session_start();

if (!isset($_SESSION['angka'])) {
    $_SESSION['angka'] = [];
}

if (count($_SESSION['angka']) > 0) {
    array_pop($_SESSION['angka']); // Hapus elemen terakhir
    $total = count($_SESSION['angka']);
    error_log("Angka dihapus, Total: $total");
    echo $total; // Kirim jumlah data terbaru
} else {
    error_log("Tidak ada angka untuk dihapus");
    echo "ERROR: Tidak ada angka untuk dihapus";
}
?>