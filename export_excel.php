<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;


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

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$kolom_per_baris = 20;
$baris_total = ceil($jumlah_data / $kolom_per_baris);

$index = 0;
for ($i = 1; $i <= $baris_total; $i++) {
    for ($j = 1; $j <= $kolom_per_baris; $j++) {
        if ($index >= $jumlah_data) {
            break;
        }
        $colLetter = Coordinate::stringFromColumnIndex($j);
        $sheet->setCellValue($colLetter . $i, $angka[$index]);
        error_log("Menulis data[$index] = {$angka[$index]} ke $colLetter$i");
        $index++;
    }
}

for ($col = 1; $col <= $kolom_per_baris; $col++) {
    $colLetter = Coordinate::stringFromColumnIndex($col);
    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
}

$nama_kiriman_safe = preg_replace('/[^a-zA-Z0-9-_]/', '', strtolower(str_replace(' ', '_', $nama_kiriman)));
$nama_file = $nama_kiriman_safe . '_' . date('ymd_his') . '.xlsx';
$path = 'files/' . $nama_file;

if (!is_dir('files')) {
    mkdir('files', 0777, true);
}

$sheet->setTitle(substr($nomor_po ?: 'Sheet1', 0, 31)); // Max 31 char

$writer = new Xlsx($spreadsheet);
$writer->save($path);

$tanggal_export = date('Y-m-d H:i:s');
$stmt = $conn->prepare("INSERT INTO riwayat_export (nama_kiriman, nomor_po, tanggal_export, file_excel) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nama_kiriman, $nomor_po, $tanggal_export, $nama_file);
$stmt->execute();

unset($_SESSION['angka']);
unset($_SESSION['nama_kiriman']);
unset($_SESSION['nomor_po']);

header('Location: riwayat.php');
exit;
?>