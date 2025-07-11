<?php
session_start();
include 'db.php';
include 'partials/header_login.php';
date_default_timezone_set('Asia/Jakarta');

// Set timezone di MySQL
$conn->query("SET time_zone = '+07:00'");

// Opsi jumlah data per halaman
$limit_options = [10, 50, 100, 500];
$limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], $limit_options) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter pencarian
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';

// Sorting
$sort = isset($_GET['sort']) && in_array($_GET['sort'], ['nama_kiriman', 'nomor_po', 'tanggal_export']) ? $_GET['sort'] : 'tanggal_export';
$order = isset($_GET['order']) && in_array($_GET['order'], ['ASC', 'DESC']) ? $_GET['order'] : 'DESC';

// Validasi tanggal
$where = [];
if ($start_date) {
    if (DateTime::createFromFormat('Y-m-d', $start_date)) {
        if ($end_date && DateTime::createFromFormat('Y-m-d', $end_date)) {
            // Filter rentang tanggal
            if (new DateTime($start_date) > new DateTime($end_date)) {
                $error_message = "Tanggal awal tidak boleh lebih besar dari tanggal akhir.";
            } else {
                $start_date_formatted = (new DateTime($start_date))->format('Y-m-d 00:00:00');
                $end_date_time = new DateTime($end_date);
                $end_date_time->setTime(23, 59, 59);
                $end_date_formatted = $end_date_time->format('Y-m-d H:i:s');
                $where[] = "tanggal_export BETWEEN '" . $conn->real_escape_string($start_date_formatted) . "' AND '" . $conn->real_escape_string($end_date_formatted) . "'";
            }
        } else {
            // Filter satu tanggal
            $start_date_formatted = (new DateTime($start_date))->format('Y-m-d 00:00:00');
            $end_date_formatted = (new DateTime($start_date))->format('Y-m-d 23:59:59');
            $where[] = "tanggal_export BETWEEN '" . $conn->real_escape_string($start_date_formatted) . "' AND '" . $conn->real_escape_string($end_date_formatted) . "'";
        }
    } else {
        $error_message = "Format tanggal awal tidak valid.";
    }
} elseif ($end_date) {
    $error_message = "Harap isi tanggal awal jika tanggal akhir diisi.";
}

// Filter pencarian
if ($search) {
    $where[] = "(nama_kiriman LIKE '%" . $conn->real_escape_string($search) . "%' OR nomor_po LIKE '%" . $conn->real_escape_string($search) . "%')";
}
$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Debug: Tampilkan query untuk cek
// echo "<pre>WHERE Clause: $where_clause</pre>"; // Uncomment untuk debug

// Query untuk hitung total data
$total_query = "SELECT COUNT(*) as total FROM riwayat_export $where_clause";
$total_result = $conn->query($total_query);
if (!$total_result) {
    die("Error querying database: " . $conn->error);
}
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Query untuk ambil data
$query = "SELECT nama_kiriman, nomor_po, tanggal_export, file_excel FROM riwayat_export $where_clause ORDER BY $sort $order LIMIT $limit OFFSET $offset";
$result = $conn->query($query);
if (!$result) {
    die("Error querying database: " . $conn->error);
}
?>

<div class="container">
    <h2 class="judul">📚 Riwayat <span>Kiriman</span></h2>

    <?php if (isset($error_message)): ?>
        <p class="no-data-text" style="color: #f43f5e; text-align: center;"><?php echo htmlspecialchars($error_message); ?></p>
    <?php elseif ($result->num_rows > 0): ?>
        <p class="success-text">Menampilkan <?php echo $result->num_rows; ?> hasil untuk filter yang dipilih.</p>
    <?php endif; ?>

    <form class="filter-form" method="GET">
        <input type="text" name="search" class="filter-input" placeholder="🔍 Cari Kiriman..." value="<?= htmlspecialchars($search) ?>">
        <input type="date" name="start_date" class="filter-input" value="<?= htmlspecialchars($start_date) ?>">
        <input type="date" name="end_date" class="filter-input" value="<?= htmlspecialchars($end_date) ?>">
        <select name="limit" class="filter-input" onchange="this.form.submit()">
            <?php foreach ($limit_options as $option): ?>
                <option value="<?= $option ?>" <?= $limit == $option ? 'selected' : '' ?>><?= $option ?> per halaman</option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="filter-btn">Filter</button>
        <a href="riwayat.php" class="reset-btn">Reset</a>
        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
        <input type="hidden" name="order" value="<?= htmlspecialchars($order) ?>">
    </form>

    <div class="button-row">
        <form action="export_filtered_excel.php" method="POST">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <input type="hidden" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
            <input type="hidden" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
        </form>
    </div>

    <div class="table-container">
        <table class="riwayat-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>
                        <a href="?page=<?= $page ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&sort=nama_kiriman&order=<?= $sort == 'nama_kiriman' && $order == 'ASC' ? 'DESC' : 'ASC' ?>">
                            Nama Kiriman <?= $sort == 'nama_kiriman' ? ($order == 'ASC' ? '↑' : '↓') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?page=<?= $page ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&sort=nomor_po&order=<?= $sort == 'nomor_po' && $order == 'ASC' ? 'DESC' : 'ASC' ?>">
                            Nomor PO <?= $sort == 'nomor_po' ? ($order == 'ASC' ? '↑' : '↓') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?page=<?= $page ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&sort=tanggal_export&order=<?= $sort == 'tanggal_export' && $order == 'ASC' ? 'DESC' : 'ASC' ?>">
                            Tanggal Export <?= $sort == 'tanggal_export' ? ($order == 'ASC' ? '↑' : '↓') : '' ?>
                        </a>
                    </th>
                    <th>File Excel</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php $no = $offset + 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                        $file_path = 'files/' . $row['file_excel'];
                        $file_exists = file_exists($file_path);
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama_kiriman']) ?></td>
                            <td><?= htmlspecialchars($row['nomor_po'] ?: '-') ?></td>
                            <td><?= htmlspecialchars(date('d M Y H:i', strtotime($row['tanggal_export']))) ?></td>
                            <td>
                                <?php if ($file_exists): ?>
                                    <a class="download-link" href="<?= htmlspecialchars($file_path) ?>" download>⬇️ Download</a>
                                <?php else: ?>
                                    <span class="no-data-text">File tidak ditemukan</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">
                            <div class="no-data-container">
                                <img src="assets/empty.png" alt="No Data" class="no-data-image">
                                <p class="no-data-text">Data tidak ditemukan.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1): ?>
        <div class="pagination" style="text-align: center; margin-top: 20px;">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&sort=<?= $sort ?>&order=<?= $order ?>" class="back-link">« Sebelumnya</a>
            <?php endif; ?>
            <span style="margin: 0 10px;">Halaman <?= $page ?> dari <?= $total_pages ?></span>
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&sort=<?= $sort ?>&order=<?= $order ?>" class="back-link">Berikutnya »</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="back-button">
        <a href="index.php" class="back-link">⬅️ Kembali</a>
    </div>
</div>

<script>
    document.querySelector('.filter-form').addEventListener('submit', function(e) {
        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;
        if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
            e.preventDefault();
            alert('Tanggal awal tidak boleh lebih besar dari tanggal akhir!');
        } else if (endDate && !startDate) {
            e.preventDefault();
            alert('Harap isi tanggal awal jika tanggal akhir diisi!');
        }
    });
</script>

<?php include 'partials/footer_login.php'; ?>