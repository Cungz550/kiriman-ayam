<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
include 'db.php';
date_default_timezone_set('Asia/Jakarta');

$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
$end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';

$where = [];
if ($search) {
    $where[] = "(nama_kiriman LIKE '%" . $conn->real_escape_string($search) . "%' OR nomor_po LIKE '%" . $conn->real_escape_string($search) . "%')";
}
if ($start_date && $end_date) {
    $end_date_time = new DateTime($end_date);
    $end_date_time->setTime(23, 59, 59);
    $end_date_formatted = $end_date_time->format('Y-m-d H:i:s');
    $where[] = "tanggal_export BETWEEN '" . $conn->real_escape_string($start_date) . " 00:00:00' AND '" . $conn->real_escape_string($end_date_formatted) . "'";
}
$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "SELECT nama_kiriman, nomor_po, tanggal_export, file_excel FROM riwayat_export $where_clause ORDER BY tanggal_export DESC";
$result = $conn->query($query);
if (!$result) {
    die("Error querying database: " . $conn->error);
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'No');
$sheet->setCellValue('B1', 'Nama Kiriman');
$sheet->setCellValue('C1', 'Nomor PO');
$sheet->setCellValue('D1', 'Tanggal Export');
$sheet->setCellValue('E1', 'File Excel');

$rowNumber = 2;
$no = 1;
while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowNumber, $no++);
    $sheet->setCellValue('B' . $rowNumber, $row['nama_kiriman']);
    $sheet->setCellValue('C' . $rowNumber, $row['nomor_po'] ?: '-');
    $sheet->setCellValue('D' . $rowNumber, date('d M Y H:i', strtotime($row['tanggal_export'])));
    $sheet->setCellValue('E' . $rowNumber, $row['file_excel']);
    $rowNumber++;
}

$writer = new Xlsx($spreadsheet);
$filename = 'Riwayat_Kiriman_' . date('Ymd_His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
exit;
?>