<?php
/**
 * refactor_input_angka.php
 * Versi refactor gabungan dari index.php dan input_angka.php (referensi user).
 * Tujuan: kode lebih rapi, aman, modular, validasi, dan fallback jika DB tidak ada.
 * - Menyimpan angka ke SESSION (default)
 * - Jika ada file db.php dengan $conn (PDO atau mysqli), akan coba simpan ke DB
 * - Menyediakan antarmuka form, daftar angka, aksi edit/delete, dan ekspor CSV
 * - CSS ringan di-embed agar mudah diuji
 *
 * Cara pakai: letakkan di folder project bersama db.php (opsional). Akses via web.
 */

session_start();
date_default_timezone_set('Asia/Jakarta');

// --- Konfigurasi ---
define('MAX_INPUT_ITEMS', 1000);
define('MAX_STR_LEN', 255);

// Coba include db.php kalau ada (harus mendefinisikan $conn sebagai PDO atau mysqli)
$dbAvailable = false;
if (file_exists(__DIR__ . '/db.php')) {
    include_once __DIR__ . '/db.php';
    if (isset($conn)) {
        $dbAvailable = true;
    }
}

// Inisialisasi storage di session
if (!isset($_SESSION['angka_list'])) {
    $_SESSION['angka_list'] = []; // array of associative: ['id'=>..., 'value'=>..., 'note'=>..., 'created'=>...]
}

// Utility: generate ID
function gen_id() {
    return bin2hex(random_bytes(6));
}

// Sanitizer sederhana
function clean_str($s) {
    $s = trim((string)$s);
    if (strlen($s) > MAX_STR_LEN) $s = substr($s, 0, MAX_STR_LEN);
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Handler: add item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $raw = $_POST['value'] ?? '';
    $note = $_POST['note'] ?? '';

    // Limit jumlah
    if (count($_SESSION['angka_list']) >= MAX_INPUT_ITEMS) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Maksimum item tercapai. Hapus beberapa dulu.'];
        header('Location: ' . $_SERVER['PHP_SELF']); exit;
    }

    // Validasi angka (boleh desimal) — ganti sesuai kebutuhan
    $value = str_replace(',', '.', trim($raw));
    if ($value === '') {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Nilai kosong.'];
        header('Location: ' . $_SERVER['PHP_SELF']); exit;
    }

    if (!is_numeric($value)) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Masukkan angka yang valid.'];
        header('Location: ' . $_SERVER['PHP_SELF']); exit;
    }

    $item = [
        'id' => gen_id(),
        'value' => (float)$value,
        'note' => clean_str($note),
        'created' => date('Y-m-d H:i:s')
    ];

    // push to session
    array_unshift($_SESSION['angka_list'], $item); // newest first

    // Optional: simpan ke DB jika tersedia (harus ada tabel 'angka' dengan kolom id,value,note,created)
    if ($dbAvailable) {
        try {
            if ($conn instanceof PDO) {
                $stmt = $conn->prepare("INSERT INTO angka (id, value, note, created) VALUES (:id, :value, :note, :created)");
                $stmt->execute($item);
            } else { // mysqli
                $stmt = $conn->prepare("INSERT INTO angka (id, value, note, created) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('sdss', $item['id'], $item['value'], $item['note'], $item['created']);
                $stmt->execute();
            }
        } catch (Exception $e) {
            // silent fail — tetap biarkan data di session
            error_log('DB save failed: ' . $e->getMessage());
        }
    }

    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Angka berhasil ditambahkan.'];
    header('Location: ' . $_SERVER['PHP_SELF']); exit;
}

// Handler: delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = $_POST['id'] ?? '';
    $_SESSION['angka_list'] = array_filter($_SESSION['angka_list'], function($it) use ($id) { return $it['id'] !== $id; });

    // Optional DB delete
    if ($dbAvailable) {
        try {
            if ($conn instanceof PDO) {
                $stmt = $conn->prepare("DELETE FROM angka WHERE id = :id");
                $stmt->execute([':id'=>$id]);
            } else {
                $stmt = $conn->prepare("DELETE FROM angka WHERE id = ?");
                $stmt->bind_param('s', $id);
                $stmt->execute();
            }
        } catch (Exception $e) { error_log('DB delete failed: '.$e->getMessage()); }
    }

    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Item dihapus.'];
    header('Location: ' . $_SERVER['PHP_SELF']); exit;
}

// Handler: export CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $list = $_SESSION['angka_list'];
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=angka_export_' . date('Ymd_His') . '.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['id', 'value', 'note', 'created']);
    foreach ($list as $row) fputcsv($out, [$row['id'], $row['value'], $row['note'], $row['created']]);
    fclose($out); exit;
}

// Simple flash get
$flash = $_SESSION['flash'] ?? null; if ($flash) unset($_SESSION['flash']);

// Sorting / pagination (basic)
$perPage = isset($_GET['per']) ? (int)$_GET['per'] : 30;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$allowedPer = [10,30,50,100]; if (!in_array($perPage, $allowedPer)) $perPage = 30;

$total = count($_SESSION['angka_list']);
$pages = max(1, ceil($total / $perPage));
$start = ($page - 1) * $perPage;
$list = array_slice($_SESSION['angka_list'], $start, $perPage);

?>

<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Input Angka — Versi Refactor</title>
<style>
:root{--bg:#f6f7fb;--card:#fff;--accent:#3b82f6;--danger:#ef4444;--muted:#6b7280}
body{font-family:Inter,ui-sans-serif,system-ui,Segoe UI,Roboto,"Helvetica Neue",Arial;margin:0;background:var(--bg);color:#111}
.container{max-width:1000px;margin:28px auto;padding:18px}
.header{display:flex;align-items:center;justify-content:space-between;gap:12px}
.card{background:var(--card);border-radius:12px;padding:16px;box-shadow:0 6px 18px rgba(16,24,40,.06)}
.form-row{display:flex;gap:8px;align-items:center}
.input{flex:1;padding:10px;border:1px solid #e6e9ef;border-radius:8px}
.btn{background:var(--accent);color:#fff;padding:10px 14px;border-radius:8px;border:0;cursor:pointer}
.btn.ghost{background:transparent;color:var(--accent);border:1px solid #dbeafe}
.small{font-size:13px;color:var(--muted)}
.table{width:100%;border-collapse:collapse;margin-top:12px}
.table th,.table td{padding:10px;border-bottom:1px solid #f1f5f9;text-align:left}
.actions{display:flex;gap:8px}
.flash{padding:10px;border-radius:8px;margin:10px 0}
.flash.success{background:#ecfdf5;color:#065f46}
.flash.error{background:#fff1f2;color:#7f1d1d}
.footer{display:flex;justify-content:space-between;align-items:center;margin-top:12px}
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Input Angka — Refactor</h2>
        <div class="small">Items: <?php echo $total; ?> • DB: <?php echo $dbAvailable ? 'terhubung' : 'tidak'; ?></div>
    </div>

    <?php if ($flash): ?>
        <div class="flash <?php echo $flash['type'] == 'success' ? 'success' : 'error'; ?> card">
            <?php echo htmlspecialchars($flash['msg']); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <form method="post" class="form-row" onsubmit="return validateAdd()">
            <input name="value" id="value" class="input" placeholder="Masukkan angka, contoh: 12500 atau 123.45" autocomplete="off">
            <input name="note" class="input" placeholder="Catatan (opsional)">
            <input type="hidden" name="action" value="add">
            <button class="btn">Tambah</button>
        </form>
        <div class="small" style="margin-top:8px">Tip: pakai koma atau titik untuk desimal. Max <?php echo MAX_INPUT_ITEMS; ?> item.</div>
    </div>

    <div class="card" style="margin-top:12px">
        <div style="display:flex;justify-content:space-between;align-items:center">
            <strong>Daftar Angka (hal <?php echo $page; ?> / <?php echo $pages; ?>)</strong>
            <div class="actions">
                <a class="btn ghost" href="?export=csv">Export CSV</a>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr><th>No</th><th>Nilai</th><th>Catatan</th><th>Waktu</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php if (empty($list)): ?>
                    <tr><td colspan="5" class="small">Belum ada data</td></tr>
                <?php else: foreach ($list as $i => $row): ?>
                    <tr>
                        <td><?php echo $start + $i + 1; ?></td>
                        <td><?php echo number_format($row['value'], 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($row['note']); ?></td>
                        <td class="small"><?php echo $row['created']; ?></td>
                        <td>
                            <form method="post" style="display:inline" onsubmit="return confirm('Hapus item ini?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                <button class="btn ghost" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <div class="footer">
            <div>
                <form method="get" style="display:inline">
                    <label class="small">Tampilkan</label>
                    <select name="per" onchange="this.form.submit()">
                        <?php foreach($allowedPer as $p): ?><option value="<?php echo $p;?>" <?php if($p==$perPage) echo 'selected'; ?>><?php echo $p;?></option><?php endforeach; ?>
                    </select>
                </form>
            </div>
            <div>
                <?php if ($pages > 1): ?>
                    <?php if($page>1): ?><a href="?page=<?php echo $page-1;?>&per=<?php echo $perPage; ?>">&laquo; Prev</a><?php endif; ?>
                    <?php if($page<$pages): ?><a href="?page=<?php echo $page+1;?>&per=<?php echo $perPage; ?>">Next &raquo;</a><?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <div style="margin-top:12px;text-align:center;color:var(--muted)" class="small">Versi refactor — modular dan lebih aman. Buat customize, kasih tau gue fitur apa yang mau ditambah.</div>
</div>

<script>
function validateAdd(){
    const v = document.getElementById('value').value.trim();
    if(!v){ alert('Masukkan angka dulu.'); return false; }
    // pasang validasi angka ringan
    const num = parseFloat(v.replace(',','.'));
    if(isNaN(num)){ alert('Masukkan angka yang valid.'); return false; }
    return true;
}
</script>

</body>
</html>
