<?php
session_start();
include 'db.php';
date_default_timezone_set('Asia/Jakarta');

define('MAX_INPUT', 1000);

// Cek $_POST['kiriman'] sebelum output apa pun
if (!isset($_POST['kiriman'])) {
    header("Location: index.php");
    exit;
}

// Inisialisasi session angka
if (!isset($_SESSION['angka'])) {
    $_SESSION['angka'] = [];
}

// Reset session jika kiriman atau nomor_po berubah
if (!isset($_SESSION['nama_kiriman']) || $_SESSION['nama_kiriman'] !== $_POST['kiriman'] || $_SESSION['nomor_po'] !== ($_POST['nomor_po'] ?? '')) {
    $_SESSION['angka'] = [];
    $_SESSION['nama_kiriman'] = $_POST['kiriman'];
    $_SESSION['nomor_po'] = $_POST['nomor_po'] ?? '';
}

$jumlah_angka = count($_SESSION['angka']);
$kiriman = $_POST['kiriman'];
$nomor_po = $_POST['nomor_po'] ?? '';

$warna_progress = 'bg-red-500';
if ($jumlah_angka >= MAX_INPUT * 0.26 && $jumlah_angka <= MAX_INPUT * 0.75) {
    $warna_progress = 'bg-yellow-400';
} elseif ($jumlah_angka > MAX_INPUT * 0.75) {
    $warna_progress = 'bg-green-500';
}

// Baru include header setelah logika header selesai
include 'partials/header.php';
?>

<div class="container">
    <div class="page-card">
        <h1 class="page-title">📦 Input Angka</h1>
        <h2 class="page-subtitle">Kiriman: <span class="text-blue-300"><?= htmlspecialchars($kiriman) ?></span> | Nomor PO: <span class="text-blue-300"><?= htmlspecialchars($nomor_po ?: '-') ?></span></h2>

        <div class="angka-buttons grid grid-cols-3 sm:grid-cols-4 md:grid-cols-7 gap-2 mb-6">
            <?php
            for ($i = 40; $i <= 60; $i++) {
                $angka = number_format($i / 10, 1);
                echo "<button type='button' class='angka-btn btn btn-secondary flex justify-center items-center px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition duration-300 ease-in-out' onclick=\"addAngka('$angka')\">$angka</button>";
            }
            ?>
        </div>

        <div class="progress-section mb-6">
            <div class="progress-info text-white text-lg mb-2">
                📈 Angka Terkumpul: <span id="angka-count"><?= $jumlah_angka ?></span> / <?= MAX_INPUT ?>
            </div>
            <div class="progress-bar bg-gray-800 rounded-full overflow-hidden h-6">
                <div id="progress-fill" class="progress-fill <?= $warna_progress ?> h-full transition-all duration-500 ease-in-out" 
                     style="width: <?= min(100, ($jumlah_angka / MAX_INPUT) * 100) ?>%;">
                </div>
            </div>
            <div class="progress-percentage text-white text-right mt-2">
                <span id="progress-text"><?= round(($jumlah_angka / MAX_INPUT) * 100, 1) ?>%</span>
            </div>
        </div>

        <div class="preview-section mb-6">
            <h3 class="page-subtitle">👀 Angka Terakhir</h3>
            <div id="preview-list" class="preview-list bg-gray-800 p-4 rounded-lg text-white">
                <?php if (empty($_SESSION['angka'])): ?>
                    <p class="no-data">Belum ada angka bro!</p>
                <?php else: ?>
                    <p>Angka Terakhir: <?= htmlspecialchars(end($_SESSION['angka'])) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="action-buttons flex flex-wrap gap-4 justify-center">
            <button type="button" class="delete-btn btn btn-warning flex items-center px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition duration-300 ease-in-out" onclick="deleteAngka()" <?= $jumlah_angka == 0 ? 'disabled' : '' ?>>
                <span class="btn-icon mr-2">🗑️</span> Hapus Terakhir
            </button>
            <button type="button" class="clear-all-btn btn btn-warning flex items-center px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition duration-300 ease-in-out" onclick="clearAllAngka()" <?= $jumlah_angka == 0 ? 'disabled' : '' ?>>
                <span class="btn-icon mr-2">🧹</span> Hapus Semua
            </button>
            <form action="export_excel.php" method="POST" class="export-form">
                <input type="hidden" name="kiriman" value="<?= htmlspecialchars($kiriman) ?>">
                <input type="hidden" name="nomor_po" value="<?= htmlspecialchars($nomor_po) ?>">
                <button type="submit" name="submit" value="1" class="export-btn btn btn-success flex items-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition duration-300 ease-in-out" <?= $jumlah_angka == 0 ? 'disabled' : '' ?>>
                    <span class="btn-icon mr-2">🏎️</span> Export Excel
                </button>
            </form>
        </div>

        <div class="back-section mt-6 text-center">
            <a href="index.php" class="back-link btn btn-secondary flex items-center justify-center mx-auto px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition duration-300 ease-in-out">
                <span class="btn-icon mr-2">⬅️</span> Kembali
            </a>
        </div>
    </div>
</div>

<script>
const MAX_INPUT = <?= MAX_INPUT ?>;

function updatePreview() {
    fetch('get_preview.php')
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        const previewList = document.getElementById('preview-list');
        if (data.angka.length === 0) {
            previewList.innerHTML = '<p class="no-data">Belum ada angka bro!</p>';
        } else {
            previewList.innerHTML = `<p>Angka Terakhir: ${data.angka[data.angka.length - 1]}</p>`;
        }
    })
    .catch(error => {
        console.error('Error fetch preview:', error);
        showErrorMessage('Gagal load preview: ' + error.message);
    });
}

function updateProgress(count) {
    const countEl = document.getElementById('angka-count');
    const progressFill = document.getElementById('progress-fill');
    const progressText = document.getElementById('progress-text');
    const exportBtn = document.querySelector('.export-btn');
    const deleteBtn = document.querySelector('.delete-btn');
    const clearAllBtn = document.querySelector('.clear-all-btn');

    countEl.textContent = count;
    const percent = Math.min(100, (count / MAX_INPUT) * 100);
    progressFill.style.width = percent + '%';
    progressText.textContent = percent.toFixed(1) + '%';

    progressFill.classList.remove('bg-red-500', 'bg-yellow-400', 'bg-green-500');
    if (count > MAX_INPUT * 0.75) {
        progressFill.classList.add('bg-green-500');
    } else if (count >= MAX_INPUT * 0.26) {
        progressFill.classList.add('bg-yellow-400');
    } else {
        progressFill.classList.add('bg-red-500');
    }

    if (count > 0) {
        exportBtn.removeAttribute('disabled');
        deleteBtn.removeAttribute('disabled');
        clearAllBtn.removeAttribute('disabled');
    } else {
        exportBtn.setAttribute('disabled', '');
        deleteBtn.setAttribute('disabled', '');
        clearAllBtn.setAttribute('disabled', '');
    }
}

function showErrorMessage(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-notification';
    errorDiv.textContent = message;
    errorDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #f56565;
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    `;
    document.body.appendChild(errorDiv);
    setTimeout(() => {
        if (errorDiv.parentNode) {
            errorDiv.parentNode.removeChild(errorDiv);
        }
    }, 5000);
}

function addAngka(angka) {
    fetch('save_temp.php?angka=' + encodeURIComponent(angka))
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.text();
    })
    .then(data => {
        if (data.startsWith('ERROR')) {
            showErrorMessage(data);
            return;
        }
        const count = parseInt(data);
        updateProgress(count);
        updatePreview();
    })
    .catch(error => {
        console.error('Error adding angka:', error);
        showErrorMessage('Gagal menambah angka: ' + error.message);
    });
}

function deleteAngka() {
    if (!confirm('Hapus angka terakhir?')) return;
    fetch('delete_temp.php')
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.text();
    })
    .then(data => {
        if (data.startsWith('ERROR')) {
            showErrorMessage(data);
            return;
        }
        const count = parseInt(data);
        updateProgress(count);
        updatePreview();
    })
    .catch(error => {
        console.error('Error deleting angka:', error);
        showErrorMessage('Gagal menghapus angka: ' + error.message);
    });
}

function clearAllAngka() {
    if (!confirm('Hapus SEMUA angka? Aksi ini tidak dapat dibatalkan!')) return;
    fetch('clear_all_temp.php')
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.text();
    })
    .then(data => {
        if (data.startsWith('ERROR')) {
            showErrorMessage(data);
            return;
        }
        const count = parseInt(data);
        updateProgress(isNaN(count) ? 0 : count);
        updatePreview();
    })
    .catch(error => {
        console.error('Error clearing all angka:', error);
        showErrorMessage('Gagal menghapus semua angka: ' + error.message);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    updatePreview();
});
</script>

<?php include 'partials/footer.php'; ?>