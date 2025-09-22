<?php
session_start();

// Reset semua angka di session
$_SESSION['angka'] = [];
// Reset juga presisi ke default (biar ga nyangkut kalau user balik ke index)
// $_SESSION['precision'] = 2; // Optional, bisa diaktifin kalau mau
error_log("Semua angka dihapus");
echo 0; // Kirim jumlah data terbaru (0)
?>
