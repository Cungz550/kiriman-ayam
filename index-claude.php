<?php
session_start();
include 'db.php';
include 'partials/header.php';

// Redirect ke login kalau belum login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Data kota untuk dropdown (lebih terorganisir)
$kota_kiriman = [
    'Aceh', 'Ambon', 'Bangka', 'Banjarmasin', 'Batam', 'Belitung', 
    'Bengkulu', 'Bima', 'Bintan', 'CGL', 'Ende', 'Flores', 
    'Gorontalo', 'Jambi', 'Jayapura', 'Kendari', 'Kupang', 
    'Lampung', 'Lombok', 'Manado', 'Manokwari', 'Palembang', 
    'Pekanbaru', 'Pontianak', 'Samarinda', 'Sikka', 'Sorong', 
    'Sumba', 'Ternate'
];

// Hapus duplikat dan sort
$kota_kiriman = array_unique($kota_kiriman);
sort($kota_kiriman);
?>

<div class="container">
    <div class="welcome-section">
        <h1 class="judul">📦 Sistem Kiriman</h1>
        <p class="subtitle">Halo, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>! Siap kirim barang hari ini? 🚚</p>
    </div>

    <div class="main-card">
        <form action="input_angka.php" method="GET" class="kiriman-form" id="kirimanForm">
            <div class="form-group">
                <label for="kiriman">
                    <span class="icon">🏙️</span>
                    Nama Kiriman
                </label>
                <select id="kiriman" name="kiriman" required class="input-select">
                    <option value="">-- Pilih Destinasi --</option>
                    <?php foreach ($kota_kiriman as $kota): ?>
                        <option value="<?php echo htmlspecialchars($kota); ?>">
                            <?php echo htmlspecialchars($kota); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="help-text">Pilih kota tujuan pengiriman</small>
            </div>

            <div class="form-group">
                <label for="nomor_po">
                    <span class="icon">📋</span>
                    Nomor PO
                </label>
                <input type="text" 
                       id="nomor_po" 
                       name="nomor_po" 
                       placeholder="Contoh: PO12345" 
                       class="input-text"
                       pattern="[A-Za-z0-9]+"
                       title="Hanya huruf dan angka yang diperbolehkan">
                <small class="help-text">Opsional - Masukkan nomor Purchase Order</small>
            </div>

            <div class="button-group">
                <button type="submit" class="btn-primary">
                    <span class="btn-icon">🚀</span>
                    Mulai Input
                </button>
                <button type="reset" class="btn-secondary">
                    <span class="btn-icon">🔄</span>
                    Reset
                </button>
            </div>
        </form>
    </div>

    <div class="quick-actions">
        <a href="riwayat.php" class="action-link">
            <span class="icon">📚</span>
            <span>Lihat Riwayat</span>
        </a>
        <a href="#" class="action-link disabled" title="Coming Soon!">
            <span class="icon">📊</span>
            <span>Statistik</span>
            <small class="badge">Soon</small>
        </a>
    </div>
</div>

<style>
* {
    box-sizing: border-box;
}

.container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.welcome-section {
    text-align: center;
    margin-bottom: 30px;
}

.judul {
    color: #1e40af;
    font-size: 2.5em;
    margin-bottom: 10px;
    font-weight: bold;
}

.subtitle {
    color: #6b7280;
    font-size: 1.1em;
    margin-bottom: 0;
}

.main-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    padding: 30px;
    margin-bottom: 30px;
    border: 1px solid #e5e7eb;
}

.kiriman-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    font-size: 1.1em;
}

.icon {
    font-size: 1.2em;
}

.input-select, .input-text {
    padding: 12px 16px;
    border: 2px solid #d1d5db;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.3s ease;
    background-color: #ffffff;
}

.input-select:focus, .input-text:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.input-select {
    cursor: pointer;
}

.help-text {
    color: #6b7280;
    font-size: 0.9em;
    margin-top: 4px;
}

.button-group {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin-top: 10px;
}

.btn-primary, .btn-secondary {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
    border: 2px solid #d1d5db;
}

.btn-secondary:hover {
    background: #e5e7eb;
    transform: translateY(-1px);
}

.btn-icon {
    font-size: 1.1em;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.action-link.disabled {
    opacity: 0.6;
    cursor: not-allowed;
    position: relative;
}

.action-link.disabled:hover {
    transform: none;
    box-shadow: none;
    background: #f8fafc;
}

.badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #fbbf24;
    color: #92400e;
    font-size: 0.7em;
    padding: 2px 6px;
    border-radius: 10px;
    font-weight: bold;
}

.action-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    text-decoration: none;
    color: #475569;
    transition: all 0.3s ease;
    font-weight: 500;
}

.action-link:hover {
    background: #e2e8f0;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.action-link .icon {
    font-size: 1.4em;
}

/* Responsive Design */
@media (max-width: 600px) {
    .container {
        padding: 15px;
    }
    
    .main-card {
        padding: 20px;
    }
    
    .button-group {
        flex-direction: column;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
    
    .judul {
        font-size: 2em;
    }
}

/* Loading Animation */
.btn-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.btn-primary:disabled::after {
    content: "⏳";
    margin-left: 8px;
}

/* Form Validation Styles */
.input-select:invalid, .input-text:invalid {
    border-color: #ef4444;
}

.input-select:valid, .input-text:valid {
    border-color: #10b981;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('kirimanForm');
    const submitBtn = form.querySelector('.btn-primary');
    const kirimanSelect = document.getElementById('kiriman');
    const nomorPO = document.getElementById('nomor_po');
    
    // Form validation
    form.addEventListener('submit', function(e) {
        if (!kirimanSelect.value) {
            e.preventDefault();
            alert('❌ Ups! Pilih destinasi dulu dong!');
            kirimanSelect.focus();
            return;
        }
        
        // Loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="btn-icon">⏳</span> Memproses...';
        
        // Auto-enable after 3 seconds (fallback)
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span class="btn-icon">🚀</span> Mulai Input';
        }, 3000);
    });
    
    // Auto-format nomor PO
    nomorPO.addEventListener('input', function() {
        this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'Enter') {
            form.submit();
        }
    });
});
</script>

<?php include 'partials/footer.php'; ?>