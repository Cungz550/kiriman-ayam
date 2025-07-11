<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kiriman = trim($_POST['nama_kiriman'] ?? '');

    if (!empty($nama_kiriman)) {
        // Reset angka lama kalau ada
        unset($_SESSION['angka']);

        // Set nama kiriman ke session
        $_SESSION['nama_kiriman'] = $nama_kiriman;

        // Insert kiriman baru ke database
        $stmt = $conn->prepare("INSERT INTO riwayat_export (nama_kiriman, tanggal_export) VALUES (?, NOW())");
        if ($stmt) {
            $stmt->bind_param("s", $nama_kiriman);
            $stmt->execute();
            $stmt->close();

            $last_id = $conn->insert_id;

            // Redirect ke halaman input angka
            header("Location: input_angka.php?id=$last_id&kiriman=" . urlencode($nama_kiriman));
            exit;
        } else {
            echo "❌ Error prepare statement: " . $conn->error;
        }
    } else {
        echo "⚠️ Nama kiriman tidak boleh kosong!";
    }
}
?>

<!-- Form Input -->
<form method="POST" action="">
    <input type="text" name="nama_kiriman" placeholder="Masukkan Nama Kiriman" required>
    <button type="submit">🚀 Set Kiriman</button>
</form>

