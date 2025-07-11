<?php
session_start();
include 'db.php';
include 'partials/header.php';
date_default_timezone_set('Asia/Jakarta');

define('MAX_INPUT', 1000);

if (!isset($_SESSION['angka'])) {
    $_SESSION['angka'] = [];
}

if (isset($_GET['kiriman'])) {
    if (!isset($_SESSION['nama_kiriman']) || $_SESSION['nama_kiriman'] !== $_GET['kiriman'] || $_SESSION['nomor_po'] !== ($_GET['nomor_po'] ?? '')) {
        $_SESSION['angka'] = [];
        $_SESSION['nama_kiriman'] = $_GET['kiriman'];
        $_SESSION['nomor_po'] = $_GET['nomor_po'] ?? '';
    }
} else {
    header("Location: index.php");
    exit;
}

$jumlah_angka = count($_SESSION['angka']);
$kiriman = $_GET['kiriman'];
$nomor_po = $_GET['nomor_po'] ?? '';

$warna_progress = 'bg-red-500';
if ($jumlah_angka >= MAX_INPUT * 0.26 && $jumlah_angka <= MAX_INPUT * 0.75) {
    $warna_progress = 'bg-yellow-400';
} elseif ($jumlah_angka > MAX_INPUT * 0.75) {
    $warna_progress = 'bg-green-500';
}
?>

<div class="input-wrapper">
    <h1 class="judul">📦 Input Angka</h1>
    <h2 class="subjudul">Kiriman: <span><?= htmlspecialchars($kiriman) ?></span> | Nomor PO: <span><?= htmlspecialchars($nomor_po ?: '-') ?></span></h2>

    <div class="angka-buttons">
        <?php
        for ($i = 40; $i <= 60; $i++) {
            $angka = number_format($i / 10, 1);
            echo "<button type='button' class='angka-btn' onclick=\"addAngka('$angka')\">$angka</button>";
        }
        ?>
    </div>

    <div class="progress-section">
        <div class="progress-info">
            📈 Angka Terkumpul: <span id="angka-count"><?= $jumlah_angka ?></span> / <?= MAX_INPUT ?>
        </div>
        <div class="progress-bar">
            <div id="progress-fill" class="progress-fill <?= $warna_progress ?>" 
                 style="width: <?= min(100, ($jumlah_angka / MAX_INPUT) * 100) ?>%; transition: width 0.5s ease-in-out;">
            </div>
        </div>
        <div class="progress-percentage">
            <span id="progress-text"><?= round(($jumlah_angka / MAX_INPUT) * 100, 1) ?>%</span>
        </div>
    </div>

    <div class="preview-section">
        <h3 class="preview-title">👀 Angka Terakhir</h3>
        <div id="preview-list" class="preview-list">
            <?php if (empty($_SESSION['angka'])): ?>
                <p class="no-data">Belum ada angka bro!</p>
            <?php else: ?>
                <p>Angka Terakhir: <?= htmlspecialchars(end($_SESSION['angka'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="action-buttons">
        <button type="button" class="delete-btn" onclick="deleteAngka()" <?= $jumlah_angka == 0 ? 'disabled' : '' ?>>
            🗑️ Hapus Terakhir
        </button>
        <button type="button" class="clear-all-btn" onclick="clearAllAngka()" <?= $jumlah_angka == 0 ? 'disabled' : '' ?>>
            🧹 Hapus Semua
        </button>
        <form action="export_excel.php" method="POST" class="export-form">
            <input type="hidden" name="kiriman" value="<?= htmlspecialchars($kiriman) ?>">
            <input type="hidden" name="nomor_po" value="<?= htmlspecialchars($nomor_po) ?>">
            <button type="submit" name="submit" value="1" class="export-btn" <?= $jumlah_angka == 0 ? 'disabled' : '' ?>>
                🏎️ Export Excel
            </button>
        </form>
    </div>

    <div class="back-section">
        <a href="index.php" class="back-link">⬅️ Kembali</a>
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

    // Update progress bar color
    progressFill.classList.remove('bg-red-500', 'bg-yellow-400', 'bg-green-500');
    if (count > MAX_INPUT * 0.75) {
        progressFill.classList.add('bg-green-500');
    } else if (count >= MAX_INPUT * 0.26) {
        progressFill.classList.add('bg-yellow-400');
    } else {
        progressFill.classList.add('bg-red-500');
    }

    // Update button states
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
    // Create a simple error notification
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
    
    // Auto remove after 5 seconds
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
    if (!confirm('Hapus angka terakhir?')) {
        return;
    }
    
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
    if (!confirm('Hapus SEMUA angka? Aksi ini tidak dapat dibatalkan!')) {
        return;
    }
    
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
        
        updateProgress(0);
        updatePreview();
    })
    .catch(error => {
        console.error('Error clearing all angka:', error);
        showErrorMessage('Gagal menghapus semua angka: ' + error.message);
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updatePreview();
});
</script>

<?php include 'partials/footer.php'; ?>