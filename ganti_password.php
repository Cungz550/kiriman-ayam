<?php
session_start();
require 'db.php';

// Harus login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Ambil username aktif
$username = $_SESSION['username'];

// Handle form kirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_password = hash('sha256', $_POST['old_password']);
    $new_password = hash('sha256', $_POST['new_password']);

    // Cek password lama
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $old_password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Password cocok, update
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmt->bind_param("ss", $new_password, $username);
        $stmt->execute();
        $msg = "Password berhasil diubah!";
    } else {
        $msg = "Password lama salah!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ganti Password</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h2>Ganti Password</h2>
        <?php if (isset($msg)) echo "<p>$msg</p>"; ?>
        <form method="POST">
            <input type="password" name="old_password" placeholder="Password Lama" required><br><br>
            <input type="password" name="new_password" placeholder="Password Baru" required><br><br>
            <button type="submit">Ganti</button>
        </form>
        <br>
        <a href="index.php">← Kembali</a>
    </div>
</body>
</html>
