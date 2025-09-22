<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

session_start();
date_default_timezone_set('Asia/Jakarta');
require 'db.php';
require 'vendor/autoload.php';

$angka = $_SESSION['angka'] ?? [];
$jumlah_data = count($angka);
$nama_kiriman = $_POST['kiriman'] ?? $_SESSION['nama_kiriman'] ?? 'TanpaNama';
$nomor_po = $_POST['nomor_po'] ?? $_SESSION['nomor_po'] ?? '';

if (empty($angka)) {
    error_log("Session angka kosong!");
    die('Tidak ada data untuk diexport.');
}

error_log("Jumlah data: $jumlah_data, Data: " . json_encode($angka));

// Hitung jumlah reject (< 4.8 atau >= 5.4)
$total_reject = count(array_filter($angka, function($value) {
    $val = floatval($value);
    return $val < 4.8 || $val >= 5.4;
}));

$spreadsheet = new Spreadsheet();

// Sheet 1: Semua data
$sheet1 = $spreadsheet->getActiveSheet();
$sheet1->setTitle(substr($nomor_po ?: 'All_Data', 0, 31)); // Max 31 char

$kolom_per_baris = 20;
$baris_total = ceil($jumlah_data / $kolom_per_baris);

$index = 0;
for ($i = 1; $i <= $baris_total; $i++) {
    for ($j = 1; $j <= $kolom_per_baris; $j++) {
        if ($index >= $jumlah_data) {
            break;
        }
        $colLetter = Coordinate::stringFromColumnIndex($j);
        $cell = $colLetter . $i;
        $sheet1->setCellValue($cell, $angka[$index]);
        
        // Warna berdasarkan batas: kuning untuk < 4.8, biru untuk >= 5.4
        $value = floatval($angka[$index]);
        if ($value < 4.8) {
            $sheet1->getStyle($cell)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFF00'); // Kuning cerah
        } elseif ($value >= 5.4) {
            $sheet1->getStyle($cell)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('0000FF'); // Biru tua
        }
        
        error_log("Menulis data[$index] = {$angka[$index]} ke $colLetter$i (Sheet 1)");
        $index++;
    }
}

for ($col = 1; $col <= $kolom_per_baris; $col++) {
    $colLetter = Coordinate::stringFromColumnIndex($col);
    $sheet1->getColumnDimension($colLetter)->setAutoSize(true);
}

// Sheet 2: Hanya data reject (< 4.8 atau >= 5.4)
$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('Reject_Data');

$reject_data = array_filter($angka, function($value) {
    $val = floatval($value);
    return $val < 4.8 || $val >= 5.4;
});
$reject_data = array_values($reject_data); // Reindex array
$jumlah_reject = count($reject_data);

error_log("Jumlah data reject: $jumlah_reject, Data: " . json_encode($reject_data));

$baris_total_reject = ceil($jumlah_reject / $kolom_per_baris);
$index = 0;
for ($i = 1; $i <= $baris_total_reject; $i++) {
    for ($j = 1; $j <= $kolom_per_baris; $j++) {
        if ($index >= $jumlah_reject) {
            break;
        }
        $colLetter = Coordinate::stringFromColumnIndex($j);
        $cell = $colLetter . $i;
        $sheet2->setCellValue($cell, $reject_data[$index]);
        
        // Warna berdasarkan batas: kuning untuk < 4.8, biru untuk >= 5.4
        $value = floatval($reject_data[$index]);
        if ($value < 4.8) {
            $sheet2->getStyle($cell)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFF00'); // Kuning cerah
        } elseif ($value >= 5.4) {
            $sheet2->getStyle($cell)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('0000FF'); // Biru tua
        }
        
        error_log("Menulis reject_data[$index] = {$reject_data[$index]} ke $colLetter$i (Sheet 2)");
        $index++;
    }
}

for ($col = 1; $col <= $kolom_per_baris; $col++) {
    $colLetter = Coordinate::stringFromColumnIndex($col);
    $sheet2->getColumnDimension($colLetter)->setAutoSize(true);
}

$nama_kiriman_safe = preg_replace('/[^a-zA-Z0-9-_]/', '', strtolower(str_replace(' ', '_', $nama_kiriman)));
$nama_file = $nama_kiriman_safe . '_' . date('d_F_Y') . '.xlsx';
$path = 'files/' . $nama_file;

if (!is_dir('files')) {
    mkdir('files', 0777, true);
}

$writer = new Xlsx($spreadsheet);
$writer->save($path);

$tanggal_export = date('Y-m-d H:i:s');
$stmt = $conn->prepare("INSERT INTO riwayat_export (nama_kiriman, nomor_po, tanggal_export, file_excel, total_ctn, total_reject) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssii", $nama_kiriman, $nomor_po, $tanggal_export, $nama_file, $jumlah_data, $total_reject);
$stmt->execute();

unset($_SESSION['angka']);
unset($_SESSION['nama_kiriman']);
unset($_SESSION['nomor_po']);

header('Location: riwayat.php');
exit;
?>