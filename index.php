<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Sistem Kedatangan Ayam - monitoring dan pencatatan data ayam masuk." />
    <title>Kedatangan Ayam</title>

    <!-- Favicon -->
    <link rel="icon" href="assets/favicon.png" type="image/png" />

    <!-- Preconnect untuk CDN -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" />

    <!-- CSS Choices.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

    <!-- CSS Custom -->
    <link rel="stylesheet" href="assets/style.css" />

    <!-- JS Choices.js (pakai defer biar gak block render) -->
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js" defer></script>
</head>

<body>
    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="floating-emoji">🐔</div>
        <div class="floating-emoji">🍕</div>
        <div class="floating-emoji">📦</div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Header -->
    <?php include 'partials/header.php'; ?>

    <!-- Main Content -->
    <main>
        <div class="container">
            <div class="welcome-section">
                <h1 class="judul">📦 Sistem Kiriman</h1>
                <p class="subtitle">Halo, <strong>Admin</strong>! Siap kirim barang hari ini? 🚚</p>
            </div>

            <div class="main-card">
                <form class="kiriman-form" id="kirimanForm" action="input_angka.php" method="post">
                    <div class="form-group">
                        <label for="kiriman">
                            <span class="icon">🏙️</span>
                            Nama Kiriman
                        </label>
                        <select id="kiriman" name="kiriman" required class="input-select">
                            <option value="">-- Pilih Destinasi --</option>
                            <option value="Aceh">Aceh</option>
                            <option value="Ambon">Ambon</option>
                            <option value="Bangka">Bangka</option>
                            <option value="Banjarmasin">Banjarmasin</option>
                            <option value="Batam">Batam</option>
                            <option value="Belitung">Belitung</option>
                            <option value="Bengkulu">Bengkulu</option>
                            <option value="Bima">Bima</option>
                            <option value="Bintan">Bintan</option>
                            <option value="CGL">CGL</option>
                            <option value="CPI">CPI</option>
                            <option value="CBN 1">CBN 1</option>
                            <option value="Bangkit">Bangkit</option>
                            <option value="Ende">Ende</option>
                            <option value="Flores">Flores</option>
                            <option value="Gorontalo">Gorontalo</option>
                            <option value="Jambi">Jambi</option>
                            <option value="Jayapura">Jayapura</option>
                            <option value="Kendari">Kendari</option>
                            <option value="Kupang">Kupang</option>
                            <option value="Lampung">Lampung</option>
                            <option value="Lombok">Lombok</option>
                            <option value="Manado">Manado</option>
                            <option value="Manokwari">Manokwari</option>
                            <option value="Palembang">Palembang</option>
                            <option value="Pekanbaru">Pekanbaru</option>
                            <option value="Pontianak">Pontianak</option>
                            <option value="Samarinda">Samarinda</option>
                            <option value="Sikka">Sikka</option>
                            <option value="Sorong">Sorong</option>
                            <option value="Sumba">Sumba</option>
                            <option value="Ternate">Ternate</option>
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
                    <div class="form-group">
                        <label for="precision">
                            <span class="icon">🔢</span>
                            Presisi Angka
                        </label>
                        <select id="precision" name="precision" required class="input-select">
                            <option value="">-- Pilih --</option>
                            <option value="1">1 angka di belakang koma (x.x)</option>
                            <option value="2">2 angka di belakang koma (x.xx)</option>
                        </select>
                        <small class="help-text">CPI pilih 2 angka di belakang koma bro.</small>
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
                <a href="statistik.php" class="action-link">
                    <span class="icon">📊</span>
                    <span>Statistik</span>
                </a>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('kirimanForm');
            const submitBtn = form.querySelector('.btn-primary');
            const kirimanSelect = document.getElementById('kiriman');
            const nomorPO = document.getElementById('nomor_po');
            
            // Initialize Choices.js with iOS optimizations
            const choices = new Choices('#kiriman', {
                searchEnabled: true,
                itemSelectText: '',
                noResultsText: 'Tidak ada hasil ditemukan',
                noChoicesText: 'Tidak ada pilihan tersedia',
                loadingText: 'Memuat...',
                shouldSort: false,
                placeholderValue: '-- Pilih Destinasi --',
                searchPlaceholderValue: 'Cari kota...',
                // iOS specific options
                removeItemButton: false,
                duplicateItemsAllowed: false,
                paste: false
            });
            
            // Form validation
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!kirimanSelect.value) {
                    alert('⚠️ Ups! Pilih destinasi dulu dong!');
                    return;
                }
                
                // Loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="btn-icon">⏳</span> Memproses...';
                
                // Simulate processing
                setTimeout(() => {
                    form.submit();
                }, 500);
            });

            // Auto-format nomor PO
            nomorPO.addEventListener('input', function() {
                this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            });
            
            // Keyboard shortcuts (disabled on mobile for better UX)
            if (!('ontouchstart' in window)) {
                document.addEventListener('keydown', function(e) {
                    if (e.ctrlKey && e.key === 'Enter') {
                        form.dispatchEvent(new Event('submit'));
                    }
                });
            }

            // Dark mode toggle
            const darkModeToggle = document.getElementById('darkModeToggle');
            darkModeToggle.addEventListener('click', function() {
                document.body.classList.toggle('dark-mode');
                this.textContent = document.body.classList.contains('dark-mode') ? '☀️' : '🌙';
            });

            // iOS Safari specific fixes
            if (/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream) {
                // Force repaint untuk iOS
                document.body.style.transform = 'translateZ(0)';
                
                // Fix untuk viewport scrolling di iOS
                document.addEventListener('touchstart', function(){}, {passive: true});
                
                // Prevent zoom on input focus
                const inputs = document.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    input.addEventListener('focus', function() {
                        const viewport = document.querySelector('meta[name=viewport]');
                        viewport.content = 'width=device-width, initial-scale=1, maximum-scale=1';
                    });
                    
                    input.addEventListener('blur', function() {
                        const viewport = document.querySelector('meta[name=viewport]');
                        viewport.content = 'width=device-width, initial-scale=1';
                    });
                });
            }

            // Add some interactive effects (optimized for mobile)
            const cards = document.querySelectorAll('.main-card, .action-link');
            cards.forEach(card => {
                let isTouch = false;
                
                card.addEventListener('touchstart', function() {
                    isTouch = true;
                }, {passive: true});
                
                card.addEventListener('mouseenter', function() {
                    if (!isTouch) {
                        this.style.transform = 'translateY(-10px) scale(1.02)';
                    }
                });
                
                card.addEventListener('mouseleave', function() {
                    if (!isTouch) {
                        this.style.transform = 'translateY(0) scale(1)';
                    }
                });
                
                // Reset touch flag after some time
                card.addEventListener('touchend', function() {
                    setTimeout(() => { isTouch = false; }, 300);
                }, {passive: true});
            });
        });
    </script>
</body>
</html>