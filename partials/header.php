<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kedatangan Ayam</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

</head>
<body>
    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
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
                    <img src="assets/yummy_choice.png" alt="Logo" class="logo" onerror="this.style.display='none'; this.parentElement.innerHTML='🐔';">
                </div>
                <div class="brand-info">
                    <h1 class="brand-title">KIRIMAN AYAM</h1>
                    <p class="brand-subtitle">Sistem Manajemen Data</p>
                </div>
            </div>

            <div class="header-actions">                
                <button id="darkModeToggle" class="modern-btn dark-mode-btn" title="Toggle Dark Mode">
                    🌙
                </button>

                <a href="index.php" class="modern-btn home-btn" title="Kembali ke Home">
                    🏠 Home
                </a>

                <a href="ganti_password.php" class="modern-btn password-btn" title="Ganti Password">
                    🔐 Password
                </a>

                <form action="logout.php" method="post" style="display: inline;">
                    <button type="submit" class="modern-btn logout-btn" title="Logout">
                        🚪 Logout
                    </button>
                </form>
            </div>
        </div>
    </header>
    <main>