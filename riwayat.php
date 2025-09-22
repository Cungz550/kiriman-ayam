<?php
session_start();
include 'db.php';
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
$sort = isset($_GET['sort']) && in_array($_GET['sort'], ['nama_kiriman', 'nomor_po', 'tanggal_export', 'total_ctn', 'total_reject']) ? $_GET['sort'] : 'tanggal_export';
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

// Query untuk hitung total data
$total_query = "SELECT COUNT(*) as total FROM riwayat_export $where_clause";
$total_result = $conn->query($total_query);
if (!$total_result) {
    die("Error querying database: " . $conn->error);
}
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Query untuk ambil data
$query = "SELECT nama_kiriman, nomor_po, tanggal_export, file_excel, total_ctn, total_reject FROM riwayat_export $where_clause ORDER BY $sort $order LIMIT $limit OFFSET $offset";
$result = $conn->query($query);
if (!$result) {
    die("Error querying database: " . $conn->error);
}
?>

<?php
// Tambahkan style.css dan header.php
echo '<link rel="stylesheet" href="assets/style.css" />';
include "partials/header.php";
?>

<!-- Main Content -->
<main>
    <div class="container">
        <div class="page-header">
            <h1 class="judul">📚 Riwayat Kiriman</h1>
            <p class="subtitle">Lihat semua histori pengiriman yang pernah dilakukan 📦</p>
        </div>

        <!-- Error/Success Messages -->
        <?php if (isset($error_message)): ?>
            <div class="error-text"><?php echo htmlspecialchars($error_message); ?></div>
        <?php elseif ($result->num_rows > 0): ?>
            <div class="success-text">✅ Menampilkan <?php echo $result->num_rows; ?> hasil dari <?php echo $total_rows; ?> total data</div>
        <?php endif; ?>

        <!-- Filter Card -->
        <div class="filter-card">
            <form class="filter-form" method="GET" id="filterForm">
                <div class="filter-group">
                    <label class="filter-label">🔍 Cari Kiriman</label>
                    <input type="text" name="search" class="filter-input" placeholder="Nama kiriman atau PO..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label ">📅 Tanggal Mulai</label>
                        <input type="date" name="start_date" class="filter-input" value="<?= htmlspecialchars($start_date) ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">📅 Tanggal Akhir</label>
                        <input type="date" name="end_date" class="filter-input" value="<?= htmlspecialchars($end_date) ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">📊 Tampilkan</label>
                        <select name="limit" class="filter-input">
                            <?php foreach ($limit_options as $option): ?>
                                <option value="<?= $option ?>" <?= $limit == $option ? 'selected' : '' ?>>
                                    <?= $option ?> data
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="filter-btn">
                            🔍 Filter
                        </button>
                    </div>
                    
                    <div class="filter-group">
                        <a href="riwayat.php" class="reset-btn">
                            🔄 Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table Card -->
            <div class="table-card">
                <?php if ($result->num_rows > 0): ?>
                    <div class="table-container">
                        <table class="riwayat-table">
                            <thead>
                                <tr>
                                    <th>
                                        <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'nama_kiriman', 'order' => ($sort == 'nama_kiriman' && $order == 'ASC') ? 'DESC' : 'ASC'])) ?>">
                                            📦 Nama Kiriman
                                            <?php if ($sort == 'nama_kiriman'): ?>
                                                <?= $order == 'ASC' ? '⬆️' : '⬇️' ?>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'nomor_po', 'order' => ($sort == 'nomor_po' && $order == 'ASC') ? 'DESC' : 'ASC'])) ?>">
                                            🔢 Nomor PO
                                            <?php if ($sort == 'nomor_po'): ?>
                                                <?= $order == 'ASC' ? '⬆️' : '⬇️' ?>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'tanggal_export', 'order' => ($sort == 'tanggal_export' && $order == 'ASC') ? 'DESC' : 'ASC'])) ?>">
                                            📅 Tanggal Export
                                            <?php if ($sort == 'tanggal_export'): ?>
                                                <?= $order == 'ASC' ? '⬆️' : '⬇️' ?>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'total_ctn', 'order' => ($sort == 'total_ctn' && $order == 'ASC') ? 'DESC' : 'ASC'])) ?>">
                                            📏 Total CTN
                                            <?php if ($sort == 'total_ctn'): ?>
                                                <?= $order == 'ASC' ? '⬆️' : '⬇️' ?>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'total_reject', 'order' => ($sort == 'total_reject' && $order == 'ASC') ? 'DESC' : 'ASC'])) ?>">
                                            ❌ Total Reject
                                            <?php if ($sort == 'total_reject'): ?>
                                                <?= $order == 'ASC' ? '⬆️' : '⬇️' ?>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>💾 File Excel</th>
                                </tr>
                            </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['nama_kiriman']) ?></td>
                                            <td><?= htmlspecialchars($row['nomor_po']) ?></td>
                                            <td>
                                                <?php 
                                                $tanggal = new DateTime($row['tanggal_export']);
                                                echo $tanggal->format('d/m/Y H:i:s');
                                                ?>
                                            </td>
                                            <td><?= htmlspecialchars($row['total_ctn']) ?></td>
                                            <td><?= htmlspecialchars($row['total_reject'] ?? 'N/A') ?></td>
                                            <td>
                                                <?php 
                                                if ($row['file_excel']): ?>
                                                    <a href="/files/<?= htmlspecialchars($row['file_excel']) ?>"
                                                    class="download-link"
                                                    download
                                                    title="Download File Excel">
                                                        📥 Download
                                                    </a>
                                                <?php else: ?>
                                                    <span style="color: rgba(255, 255, 255, 0.5);">❌ File tidak tersedia</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                        </table>
                    </div>

                    <!-- Pagination Info -->
                    <div style="margin-top: 20px; text-align: center; color: rgba(255, 255, 255, 0.8);">
                        📊 Halaman <?= $page ?> dari <?= $total_pages ?> | 
                        📈 Menampilkan data <?= (($page - 1) * $limit) + 1 ?> - <?= min($page * $limit, $total_rows) ?> dari <?= $total_rows ?> total
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" title="Halaman Pertama">
                                    ⏮️ Pertama
                                </a>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" title="Halaman Sebelumnya">
                                    ⬅️ Sebelumnya
                                </a>
                            <?php endif; ?>

                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <?php if ($i == $page): ?>
                                    <span><?= $i ?></span>
                                <?php else: ?>
                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" title="Halaman Selanjutnya">
                                    Selanjutnya ➡️
                                </a>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" title="Halaman Terakhir">
                                    Terakhir ⏭️
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="no-data-container">
                        <div style="font-size: 5rem; margin-bottom: 20px;">📭</div>
                        <div class="no-data-text">Oops! Tidak ada data riwayat kiriman</div>
                        <p style="color: rgba(255, 255, 255, 0.6); margin-bottom: 30px;">
                            <?php if ($search || $start_date || $end_date): ?>
                                Coba ubah filter pencarian atau reset untuk melihat semua data 🔍
                            <?php else: ?>
                                Belum ada riwayat kiriman yang tersimpan. Mulai buat kiriman pertama! 🚀
                            <?php endif; ?>
                        </p>
                        
                        <?php if ($search || $start_date || $end_date): ?>
                            <a href="riwayat.php" class="reset-btn" style="margin-top: 20px;">
                                🔄 Reset Filter
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- JavaScript untuk interaktivitas -->
    <script>
        // Dark mode toggle (meskipun udah dark theme, bisa ditambah variasi)
        document.getElementById('darkModeToggle').addEventListener('click', function() {
            const body = document.body;
            const button = this;
            
            // Toggle between moon and sun emoji
            if (button.textContent === '🌙') {
                button.textContent = '☀️';
                body.style.filter = 'brightness(1.2) contrast(0.9)';
            } else {
                button.textContent = '🌙';
                body.style.filter = 'brightness(1) contrast(1)';
            }
        });

        // Auto-submit form saat select limit berubah
        document.querySelector('select[name="limit"]').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });

        // Loading animation untuk tombol filter
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            const btn = this.querySelector('.filter-btn');
            btn.innerHTML = '⏳ Memproses...';
            btn.disabled = true;
        });

        // Smooth scroll untuk pagination
        document.querySelectorAll('.pagination a').forEach(link => {
            link.addEventListener('click', function(e) {
                // Tambah loading effect
                this.style.opacity = '0.6';
                this.innerHTML = '⏳';
            });
        });

        // Konfirmasi sebelum download
        document.querySelectorAll('.download-link').forEach(link => {
            link.addEventListener('click', function(e) {
                const filename = this.href.split('/').pop();
                if (!confirm(`Yakin mau download file: ${filename}? 📥`)) {
                    e.preventDefault();
                }
            });
        });

        // Animasi hover untuk table rows
        document.querySelectorAll('.riwayat-table tr').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.02)';
                this.style.zIndex = '10';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
                this.style.zIndex = 'auto';
            });
        });

        // Auto-refresh setiap 5 menit (opsional)
        // setInterval(() => {
        //     if (confirm('Refresh data untuk update terbaru? 🔄')) {
        //         location.reload();
        //     }
        // }, 300000); // 5 menit

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + F untuk fokus ke search
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                document.querySelector('input[name="search"]').focus();
            }
            
            // Ctrl + R untuk reset
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                window.location.href = 'riwayat.php';
            }
            
            // ESC untuk clear search
            if (e.key === 'Escape') {
                document.querySelector('input[name="search"]').value = '';
            }
        });

        // Toast notification untuk feedback
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 25px;
                background: ${type === 'success' ? 'linear-gradient(135deg, #10b981, #059669)' : 'linear-gradient(135deg, #3b82f6, #1d4ed8)'};
                color: white;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                z-index: 10000;
                animation: slideInRight 0.3s ease-out;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255,255,255,0.2);
            `;
            toast.textContent = message;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Welcome message
        window.addEventListener('load', function() {
            setTimeout(() => {
                showToast('Welcome! Riwayat kiriman siap ditampilkan 🚀', 'success');
            }, 500);
        });
    </script>

    <style>
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }

        /* Enhanced hover effects */
        .table-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 35px 90px rgba(0, 0, 0, 0.4);
        }

        .filter-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 35px 90px rgba(0, 0, 0, 0.4);
        }

        /* Scrollbar styling */
        .table-container::-webkit-scrollbar {
            height: 8px;
        }

        .table-container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: linear-gradient(90deg, #4facfe, #00f2fe);
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(90deg, #00f2fe, #4facfe);
        }

        /* Mobile enhancements */
        @media (max-width: 768px) {
            .floating-emoji {
                font-size: 2rem;
            }
            
            .pagination {
                flex-wrap: wrap;
                gap: 8px;
            }
            
            .pagination a,
            .pagination span {
                padding: 8px 12px;
                font-size: 0.85rem;
            }
            
            .no-data-container div:first-child {
                font-size: 3rem !important;
            }
        }
    </style>
</body>
</html>