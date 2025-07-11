<!DOCTYPE html>
<html lang="id">
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
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-light: 0 8px 32px rgba(0, 0, 0, 0.1);
            --shadow-heavy: 0 20px 60px rgba(0, 0, 0, 0.2);
            --dark-glass-bg: rgba(0, 0, 0, 0.2);
            --dark-glass-border: rgba(255, 255, 255, 0.1);
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
            transition: all 0.3s ease;
        }

        /* Dark Mode */
        body.dark-mode {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 50%, #2d1b69 100%);
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

        .dark-mode .shape {
            background: rgba(255, 255, 255, 0.05);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        /* Modern Header */
        .modern-header {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-light);
        }

        .dark-mode .modern-header {
            background: var(--dark-glass-bg);
            border-bottom: 1px solid var(--dark-glass-border);
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
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            background: var(--glass-bg);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            transition: all 0.3s ease;
            animation: pulse 3s infinite;
        }

        .dark-mode .logo-wrapper {
            background: var(--dark-glass-bg);
            border: 1px solid var(--dark-glass-border);
        }

        .logo-wrapper:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: var(--shadow-heavy);
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
            font-size: 1.8rem;
            font-weight: 800;
            color: white;
            margin: 0;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            letter-spacing: 1px;
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
            border: 1px solid var(--glass-border);
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

        .dark-mode .dark-mode-btn {
            background: var(--dark-glass-bg);
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

        .dark-mode .user-info {
            background: var(--dark-glass-bg);
            border: 1px solid var(--dark-glass-border);
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

        /* Responsive Design */
        @media (max-width: 768px) {
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
            
            .modern-btn {
                padding: 0.6rem 1rem;
                font-size: 0.8rem;
            }
        }

        /* Global Styles for All Pages */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .page-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: var(--shadow-heavy);
            padding: 40px;
            margin-bottom: 40px;
            border: 1px solid var(--glass-border);
            transition: all 0.3s ease;
            animation: slideInUp 0.6s ease-out;
        }

        .dark-mode .page-card {
            background: var(--dark-glass-bg);
            border: 1px solid var(--dark-glass-border);
        }

        .page-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.3);
        }

        .page-title {
            color: white;
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 800;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.2rem;
            margin-bottom: 30px;
            font-weight: 400;
            text-align: center;
        }

        /* Form Styles */
        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: white;
            margin-bottom: 12px;
            font-size: 1.1rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .form-icon {
            font-size: 1.3rem;
            animation: bounce 2s infinite;
        }

        .form-input {
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

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .form-input:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .help-text {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-top: 8px;
            font-style: italic;
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 18px 30px;
            border: none;
            border-radius: 15px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
        }

        .btn-primary {
            background: var(--accent-gradient);
            color: white;
            box-shadow: 0 8px 25px rgba(79, 172, 254, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 40px rgba(79, 172, 254, 0.6);
        }

        .btn-secondary {
            background: var(--glass-bg);
            color: white;
            border: 2px solid var(--glass-border);
        }

        .dark-mode .btn-secondary {
            background: var(--dark-glass-bg);
            border: 2px solid var(--dark-glass-border);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }

        .btn-success {
            background: var(--success-gradient);
            color: white;
            box-shadow: 0 8px 25px rgba(17, 153, 142, 0.4);
        }

        .btn-success:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 40px rgba(17, 153, 142, 0.6);
        }

        .btn-warning {
            background: var(--warning-gradient);
            color: white;
            box-shadow: 0 8px 25px rgba(240, 147, 251, 0.4);
        }

        .btn-warning:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 40px rgba(240, 147, 251, 0.6);
        }

        .btn-icon {
            font-size: 1.2rem;
        }

        /* Table Styles */
        .modern-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow-light);
        }

        .modern-table th,
        .modern-table td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        .modern-table th {
            background: rgba(255, 255, 255, 0.2);
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .modern-table tr:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Animations */
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
            50% { transform: translateY(-5px); }
        }

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
                
                <form action="logout.php" method="post" style="display: inline;">
                    <button type="submit" class="modern-btn logout-btn" title="Logout">
                        🚪 Logout
                    </button>
                </form>
            </div>
        </div>
    </header>
    <main>