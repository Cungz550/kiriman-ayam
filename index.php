<!DOCTYPE html>
<html lang="id">
<?php
session_start();
// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kedatangan Ayam</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --accent-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-light: 0 8px 32px rgba(0, 0, 0, 0.1);
            --shadow-heavy: 0 20px 60px rgba(0, 0, 0, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* Floating Background Elements */
        .floating-shapes {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: -1;
        }

        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            top: 20%;
            left: 10%;
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            top: 60%;
            right: 20%;
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 30%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            bottom: 30%;
            left: 20%;
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 20%;
            animation-delay: 4s;
        }

        .shape:nth-child(4) {
            top: 10%;
            right: 40%;
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            animation-delay: 1s;
        }

        .shape:nth-child(5) {
            bottom: 60%;
            right: 10%;
            width: 70px;
            height: 70px;
            background: white;
            border-radius: 40%;
            animation-delay: 3s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        /* Modern Header */
        .modern-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-light);
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-wrapper {
            width: 90px;          /* dari 60 → 90 px (bebas tweak)  */
            height: 54px;         /* jagain rasio 5:3 biar header ga tinggi */
            padding: 4px;         /* tipis—buat jarak sama border */
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(10px);
            transition: transform .3s ease, box-shadow .3s ease;
            animation: pulse 3s infinite;
        }

        .logo-wrapper img {
            width: 100%;          /* penuhi lebar kotak */
            height: 100%;         /* penuhi tinggi kotak */
            object-fit: contain;  /* tetap jaga rasio, ga kepotong */
        }

        .logo-wrapper:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: var(--shadow-heavy);
        }

         /* Floating elements for extra vibes */
        .floating-emoji {
            position: absolute;
            animation: float 6s ease-in-out infinite;
            font-size: 3rem;
            opacity: 0.7;
        }

        .floating-emoji:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-emoji:nth-child(2) {
            top: 40%;
            right: 15%;
            animation-delay: 2s;
        }

        .floating-emoji:nth-child(3) {
            bottom: 10%;
            left: 10%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .logo {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }

        .brand-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .brand-title {
            display: flex;          /* taro judul + logo dalam satu baris */
            align-items: center;    /* rapihin vertikal */
            gap: .5rem;             /* jarak antar elemen */
            font-size: 1.5rem;
            color: white;
            margin: 0;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 0.8s ease-out;
        }

        .brand-subtitle {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            margin: 0;
            font-weight: 400;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .modern-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .modern-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.5s ease;
        }

        .modern-btn:hover::before {
            left: 100%;
        }

        .dark-mode-btn {
            background: var(--glass-bg);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .dark-mode-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.2) rotate(360deg);
        }

        .password-btn {
            background: var(--accent-gradient);
            color: white;
        }

        .password-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(79, 172, 254, 0.4);
        }

        .logout-btn {
            background: var(--secondary-gradient);
            color: white;
        }

        .logout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(240, 147, 251, 0.4);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--glass-bg);
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
        }

        .status-indicator {
            width: 10px;
            height: 10px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }

        /* Main Content */
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .welcome-section {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInUp 0.8s ease-out;
        }

        .judul {
            color: white;
            font-size: 3rem;
            margin-bottom: 15px;
            font-weight: 800;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            animation: bounce 1s ease-out;
        }

        .subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.3rem;
            margin-bottom: 0;
            font-weight: 400;
        }

        .main-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: var(--shadow-heavy);
            padding: 40px;
            margin-bottom: 40px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            animation: slideInUp 0.6s ease-out;
        }

        .main-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.3);
        }

        .kiriman-form {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: white;
            margin-bottom: 12px;
            font-size: 1.2rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .icon {
            font-size: 1.5rem;
            animation: bounce 2s infinite;
        }

        .input-select, .input-text {
            padding: 18px 24px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            color: white;
            font-weight: 500;
        }

        .input-select::placeholder, .input-text::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .input-select:focus, .input-text:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .input-select {
            cursor: pointer;
        }

        .help-text {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-top: 8px;
            font-style: italic;
        }

        .button-group {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 20px;
        }

        .btn-primary, .btn-secondary {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 18px 40px;
            border: none;
            border-radius: 25px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: var(--accent-gradient);
            color: white;
            box-shadow: 0 8px 25px rgba(79, 172, 254, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 40px rgba(79, 172, 254, 0.6);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-3px);
        }

        .btn-icon {
            font-size: 1.3rem;
            animation: bounce 1.5s infinite;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }

        .action-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 25px 30px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 1.1rem;
            position: relative;
            overflow: hidden;
        }

        .action-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: all 0.5s ease;
        }

        .action-link:hover::before {
            left: 100%;
        }

        .action-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .action-link .icon {
            font-size: 2rem;
            animation: bounce 2s infinite;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }
            
            .main-card {
                padding: 25px;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
            
            .judul {
                font-size: 2.5rem;
            }

            .header-container {
                padding: 1rem;
                flex-wrap: wrap;
                gap: 1rem;
            }
            
            .brand-title {
                font-size: 1.4rem;
            }
            
            .brand-subtitle {
                display: none;
            }
            
            .header-actions {
                gap: 0.5rem;
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

        /* Choices.js Styling */
        .choices {
            position: relative;
        }

        .choices__inner {
            background: rgba(255, 255, 255, 0.1) !important;
            backdrop-filter: blur(10px) !important;
            border: 2px solid rgba(255, 255, 255, 0.3) !important;
            border-radius: 15px !important;
            padding: 18px 24px !important;
            color: white !important;
            font-size: 16px !important;
            font-weight: 500 !important;
            min-height: auto !important;
        }

        .choices__inner:focus {
            border-color: rgba(255, 255, 255, 0.8) !important;
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.2) !important;
        }

        .choices__list--dropdown {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            border-radius: 15px !important;
            margin-top: 5px !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2) !important;
        }

        .choices__item--choice {
            color: #333 !important;
            padding: 12px 20px !important;
            font-weight: 500 !important;
            transition: all 0.2s ease !important;
        }

        .choices__item--choice:hover {
            background: rgba(79, 172, 254, 0.1) !important;
            color: #4facfe !important;
        }

        .choices__placeholder {
            color: rgba(255, 255, 255, 0.6) !important;
        }

        .choices__item--selectable.is-highlighted {
            background: rgba(79, 172, 254, 0.2) !important;
            color: #4facfe !important;
        }
    </style>
</head>
<body>
    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="floating-emoji">🍔</div>
        <div class="floating-emoji">🍕</div>
        <div class="floating-emoji">🐔</div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Header -->
    <header class="modern-header">
        <div class="header-container">
            <div class="logo-section">
                <div class="logo-wrapper">
                    <div class="logo"><img src="assets/Yummy_choice.png" alt="Logo"></div>
                </div>
                <div class="brand-info">
                    <h1 class="brand-title">KIRIMAN AYAM</h1>
                    <p class="brand-subtitle">Yummy Choice</p>
                </div>
            </div>

            <div class="header-actions">
                <div class="user-info">
                    <div class="status-indicator"></div>
                    <span>Online</span>
                </div>
                
                <button id="darkModeToggle" class="modern-btn dark-mode-btn" title="Toggle Dark Mode">
                    🌙
                </button>
                
                <a href="ganti_password.php" class="modern-btn password-btn" title="Ganti Password">
                    🔐 Password
                </a>
                
                <a href="logout.php" class="modern-btn logout-btn" title="Logout">
                    🚪 Logout
                </a>
            </div>
        </div>
    </header>

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
            
            // Initialize Choices.js
            const choices = new Choices('#kiriman', {
                searchEnabled: true,
                itemSelectText: '',
                noResultsText: 'Tidak ada hasil ditemukan',
                noChoicesText: 'Tidak ada pilihan tersedia',
                loadingText: 'Memuat...',
                shouldSort: false,
                placeholderValue: '-- Pilih Destinasi --',
                searchPlaceholderValue: 'Cari kota...'
            });
            
            // Form validation
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!kirimanSelect.value) {
                    alert('❌ Ups! Pilih destinasi dulu dong!');
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
            
            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'Enter') {
                    form.dispatchEvent(new Event('submit'));
                }
            });

            // Dark mode toggle
            const darkModeToggle = document.getElementById('darkModeToggle');
            darkModeToggle.addEventListener('click', function() {
                document.body.classList.toggle('dark-mode');
                this.textContent = document.body.classList.contains('dark-mode') ? '☀️' : '🌙';
            });

            // Add some interactive effects
            const cards = document.querySelectorAll('.main-card, .action-link');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-10px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });
    </script>
</body>
</html>