<?php
session_start();
include 'db.php';
date_default_timezone_set('Asia/Jakarta');

define('MAX_INPUT', 1000);

// Cek $_POST['kiriman'] dan $_POST['precision'] sebelum output apa pun
if (!isset($_POST['kiriman']) || !isset($_POST['precision'])) {
    header("Location: index.php");
    exit;
}

// Inisialisasi session angka
if (!isset($_SESSION['angka'])) {
    $_SESSION['angka'] = [];
}

// Reset session jika kiriman, nomor_po, atau precision berubah
if (!isset($_SESSION['nama_kiriman']) || $_SESSION['nama_kiriman'] !== $_POST['kiriman'] || 
    $_SESSION['nomor_po'] !== ($_POST['nomor_po'] ?? '') || 
    $_SESSION['precision'] !== $_POST['precision']) {
    $_SESSION['angka'] = [];
    $_SESSION['nama_kiriman'] = $_POST['kiriman'];
    $_SESSION['nomor_po'] = $_POST['nomor_po'] ?? '';
    $_SESSION['precision'] = $_POST['precision'];
}

$jumlah_angka = count($_SESSION['angka']);
$kiriman = $_POST['kiriman'];
$nomor_po = $_POST['nomor_po'] ?? '';
$precision = $_POST['precision'];

$warna_progress = 'bg-red-500';
if ($jumlah_angka >= MAX_INPUT * 0.26 && $jumlah_angka <= MAX_INPUT * 0.75) {
    $warna_progress = 'bg-yellow-400';
} elseif ($jumlah_angka > MAX_INPUT * 0.75) {
    $warna_progress = 'bg-green-500';
}

// Baru include header setelah logika header selesai
echo '<link rel="stylesheet" href="assets/style.css" />';
include 'partials/header.php';
?>

<div class="container px-4 py-6">
    <div class="page-card bg-[rgba(255,255,255,0.1)] backdrop-blur-xl rounded-2xl shadow-[0_20px_60px_rgba(0,0,0,0.2)] border border-[rgba(255,255,255,0.2)] p-6">
        <h1 class="page-title text-2xl font-bold text-white text-center mb-2">📦 Input Angka</h1>
        <h2 class="page-subtitle text-base text-[rgba(255,255,255,0.9)] text-center mb-4">
            Kiriman: <span class="text-blue-300"><?= htmlspecialchars($kiriman) ?></span> | 
            Nomor PO: <span class="text-blue-300"><?= htmlspecialchars($nomor_po ?: '-') ?></span>
        </h2>

        <?php if ($precision == '2'): ?>
            <div class="scrollable-inputs flex flex-col gap-3 mb-4">
                <div class="scrollable-box">
                    <label class="block text-white font-bold text-sm mb-1">Angka Utama</label>
                    <div class="scrollable-container bg-[rgba(255,255,255,0.1)] backdrop-blur-xl rounded-lg border-2 border-[rgba(255,255,255,0.2)] max-h-32 overflow-y-auto snap-y snap-mandatory touch-pan-y">
                        <?php for ($i = 4; $i <= 6; $i++): ?>
                            <div class="scrollable-item px-3 py-3 text-white text-base hover:bg-[rgba(255,255,255,0.2)] active:bg-[rgba(255,255,255,0.3)] cursor-pointer transition duration-200 snap-start" 
                                 onclick="setMainNumber(<?= $i ?>)" ontouchstart="this.classList.add('active');" ontouchend="this.classList.remove('active');">
                                <?= $i ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="scrollable-box">
                    <label class="block text-white font-bold text-sm mb-1">Desimal</label>
                    <div class="scrollable-container bg-[rgba(255,255,255,0.1)] backdrop-blur-xl rounded-lg border-2 border-[rgba(255,255,255,0.2)] max-h-32 overflow-y-auto snap-y snap-mandatory touch-pan-y">
                        <?php for ($i = 0; $i <= 99; $i++): ?>
                            <div class="scrollable-item px-3 py-3 text-white text-base hover:bg-[rgba(255,255,255,0.2)] active:bg-[rgba(255,255,255,0.3)] cursor-pointer transition duration-200 snap-start" 
                                 onclick="setDecimal(<?= sprintf('%02d', $i) ?>)" ontouchstart="this.classList.add('active');" ontouchend="this.classList.remove('active');">
                                .<?= sprintf('%02d', $i) ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            <div class="selected-number mb-4 flex flex-col items-center justify-center">
                <span id="selected-number" class="inline-block text-white text-base bg-[rgba(255,255,255,0.1)] backdrop-blur-lg px-4 py-2 rounded-lg mb-2">Pilih angka...</span>
                <button type="button" class="next-btn btn btn-primary flex items-center justify-center mt-3 w-full max-w-xs h-12 text-base bg-[linear-gradient(135deg,#4facfe_0%,#00f2fe_100%)] text-white rounded-xl hover:bg-[linear-gradient(135deg,#00f2fe_0%,#4facfe_100%)] active:scale-95 transition duration-300 ease-in-out" 
                        onclick="submitNumber()" disabled>
                    <span class="btn-icon mr-2">🚀</span> Next
                </button>
            </div>
        <?php else: ?>
            <div class="angka-buttons grid grid-cols-3 gap-2 mb-4">
                <?php
                for ($i = 40; $i <= 60; $i++) {
                    $angka = number_format($i / 10, 1);
                    echo "<button type='button' class='angka-btn btn btn-secondary flex justify-center items-center h-12 text-base bg-[rgba(255,255,255,0.1)] backdrop-blur-lg text-white rounded-lg hover:bg-[rgba(255,255,255,0.2)] active:bg-[rgba(255,255,255,0.3)] transition duration-300 ease-in-out' onclick=\"addAngka('$angka')\">$angka</button>";
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="progress-section mb-4">
            <div class="progress-info text-white text-base mb-1">
                📈 Angka Terkumpul: <span id="angka-count"><?= $jumlah_angka ?></span> / <?= MAX_INPUT ?>
            </div>
            <div class="progress-bar bg-[rgba(255,255,255,0.1)] rounded-full overflow-hidden h-5">
                <div id="progress-fill" class="progress-fill <?= $warna_progress ?> h-full transition-all duration-500 ease-in-out" 
                     style="width: <?= min(100, ($jumlah_angka / MAX_INPUT) * 100) ?>%;">
                </div>
            </div>
            <div class="progress-percentage text-white text-sm text-right mt-1">
                <span id="progress-text"><?= round(($jumlah_angka / MAX_INPUT) * 100, 1) ?>%</span>
            </div>
        </div>

        <div class="preview-section mb-4 flex flex-col items-center justify-center">
            <h3 class="page-subtitle text-base text-[rgba(255,255,255,0.9)] text-center">👀 Angka Terakhir</h3>
            <div id="preview-list" class="preview-list bg-[rgba(255,255,255,0.1)] backdrop-blur-lg p-3 rounded-lg text-white text-sm w-full max-w-xs mx-auto text-center">
                <?php if (empty($_SESSION['angka'])): ?>
                    <p class="no-data">Belum ada angka bro!</p>
                <?php else: ?>
                    <p>Angka Terakhir: <?= htmlspecialchars(end($_SESSION['angka'])) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="action-buttons flex flex-col gap-3 items-center mb-4">
            <button type="button" class="delete-btn btn btn-warning flex items-center justify-center h-12 text-base bg-red-500 text-white rounded-xl hover:bg-red-600 active:scale-95 transition duration-300 ease-in-out w-full max-w-xs mx-auto" 
                    onclick="deleteAngka()" <?= $jumlah_angka == 0 ? 'disabled' : '' ?> >
                <span class="btn-icon mr-2">🗑️</span> Hapus Terakhir
            </button>
            <button type="button" class="clear-all-btn btn btn-warning flex items-center justify-center h-12 text-base bg-red-500 text-white rounded-xl hover:bg-red-600 active:scale-95 transition duration-300 ease-in-out w-full max-w-xs mx-auto" 
                    onclick="clearAllAngka()" <?= $jumlah_angka == 0 ? 'disabled' : '' ?> >
                <span class="btn-icon mr-2">🧹</span> Hapus Semua
            </button>
            <form action="export_excel.php" method="POST" class="export-form w-full max-w-xs mx-auto">
                <input type="hidden" name="kiriman" value="<?= htmlspecialchars($kiriman) ?>">
                <input type="hidden" name="nomor_po" value="<?= htmlspecialchars($nomor_po) ?>">
                <button type="submit" name="submit" value="1" class="export-btn btn btn-success flex items-center justify-center h-12 text-base bg-green-500 text-white rounded-xl hover:bg-green-600 active:scale-95 transition duration-300 ease-in-out w-full" 
                        <?= $jumlah_angka == 0 ? 'disabled' : '' ?> >
                    <span class="btn-icon mr-2">🏎️</span> Export Excel
                </button>
            </form>
        </div>

        <div class="back-section text-center">
            <a href="index.php" class="back-link btn btn-secondary flex items-center justify-center mx-auto h-12 text-base bg-[rgba(255,255,255,0.1)] backdrop-blur-lg text-white rounded-xl hover:bg-[rgba(255,255,255,0.2)] active:scale-95 transition duration-300 ease-in-out w-full max-w-xs">
                <span class="btn-icon mr-2">⬅️</span> Kembali
            </a>
        </div>
    </div>
</div>

<style>
.scrollable-container {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(20px);
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    max-height: 120px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(255, 255, 255, 0.3) rgba(255, 255, 255, 0.1);
    -webkit-overflow-scrolling: touch;
}
.scrollable-container::-webkit-scrollbar {
    width: 6px;
}
.scrollable-container::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
}
.scrollable-container::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 12px;
}
.scrollable-item {
    min-height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.scrollable-item:hover, .scrollable-item.active {
    background: rgba(255, 255, 255, 0.2);
    transform: translateX(3px);
}
.next-btn:disabled, .delete-btn:disabled, .clear-all-btn:disabled, .export-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
.selected-number,
.preview-section,
.action-buttons {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.action-buttons > * {
    margin-left: auto;
    margin-right: auto;
}
.export-form,
.preview-list,
.next-btn,
.delete-btn,
.clear-all-btn {
    margin-left: auto;
    margin-right: auto;
}
@media (min-width: 640px) {
    .scrollable-inputs {
        flex-direction: row;
        gap: 12px;
    }
    .scrollable-box {
        flex: 1;
    }
}
</style>

<script>
const MAX_INPUT = <?= MAX_INPUT ?>;
let mainNumber = null;
let decimalNumber = null;

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
        top: 16px;
        right: 16px;
        background: #f56565;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        font-size: 14px;
    `;
    document.body.appendChild(errorDiv);
    setTimeout(() => {
        if (errorDiv.parentNode) {
            errorDiv.parentNode.removeChild(errorDiv);
        }
    }, 4000);
}

function setMainNumber(number) {
    mainNumber = number;
    updateSelectedNumber();
}

function setDecimal(decimal) {
    decimalNumber = decimal;
    updateSelectedNumber();
}

function updateSelectedNumber() {
    const selectedNumberEl = document.getElementById('selected-number');
    const nextBtn = document.querySelector('.next-btn');
    if (mainNumber !== null && decimalNumber !== null) {
        selectedNumberEl.textContent = `${mainNumber}.${decimalNumber}`;
        nextBtn.removeAttribute('disabled');
    } else {
        selectedNumberEl.textContent = mainNumber !== null ? `${mainNumber}.00` : 'Pilih angka...';
        nextBtn.setAttribute('disabled', '');
    }
}

function submitNumber() {
    if (mainNumber === null || decimalNumber === null) return;
    const angka = `${mainNumber}.${decimalNumber}`;
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
        mainNumber = null;
        decimalNumber = null;
        updateSelectedNumber();
    })
    .catch(error => {
        console.error('Error adding angka:', error);
        showErrorMessage('Gagal menambah angka: ' + error.message);
    });
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