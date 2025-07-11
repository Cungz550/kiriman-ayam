<?php
session_start();

// Reset semua angka di session
$_SESSION['angka'] = [];
error_log("Semua angka dihapus");
echo 0; // Kirim jumlah data terbaru (0)
?>
