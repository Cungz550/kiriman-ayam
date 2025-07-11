<?php
include 'db.php';

$id_kiriman = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nilai = $_POST['nilai'] ?? 0;
    if ($nilai >= 4.0 && $nilai <= 4.7) {
        $stmt = $conn->prepare("INSERT INTO data_input (id_kiriman, nilai) VALUES (?, ?)");
        $stmt->bind_param("id", $id_kiriman, $nilai);
        $stmt->execute();
    }
}
?>

<form method="POST">
    <button type="submit" name="nilai" value="4.0">4.0</button>
    <button type="submit" name="nilai" value="4.1">4.1</button>
    <button type="submit" name="nilai" value="4.2">4.2</button>
    <button type="submit" name="nilai" value="4.3">4.3</button>
    <button type="submit" name="nilai" value="4.4">4.4</button>
    <button type="submit" name="nilai" value="4.5">4.5</button>
    <button type="submit" name="nilai" value="4.6">4.6</button>
    <button type="submit" name="nilai" value="4.7">4.7</button>
</form>
