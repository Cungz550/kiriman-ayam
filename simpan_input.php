<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_kiriman = $_POST['id_kiriman'] ?? 0;
    $angka = $_POST['angka'] ?? [];

    foreach ($angka as $nilai) {
        if ($nilai !== '' && $nilai >= 4.0 && $nilai <= 4.7) {
            $stmt = $conn->prepare("INSERT INTO data_input (id_kiriman, nilai) VALUES (?, ?)");
            $stmt->bind_param("id", $id_kiriman, $nilai);
            $stmt->execute();
        }
    }
}

header("Location: riwayat.php");
exit;
?>
