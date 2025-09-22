<?php
session_start();

// Kalo udah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Ambil pesan error/success
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['error']);

$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['success']);

// Simpan form data kalo ada error
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
unset($_SESSION['form_data']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-header h1 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .register-header p {
            color: #666;
            font-size: 0.9rem;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            flex: 1;
            margin-bottom: 1.5rem;
        }

        .form-row .form-group {
            margin-bottom: 0;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }

        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.8rem;
        }

        .strength-bar {
            height: 4px;
            background: #e1e5e9;
            border-radius: 2px;
            margin-top: 0.5rem;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            transition: width 0.3s ease, background-color 0.3s ease;
            width: 0%;
            background: #e74c3c;
        }

        .strength-weak { background: #e74c3c; width: 25%; }
        .strength-fair { background: #f39c12; width: 50%; }
        .strength-good { background: #f1c40f; width: 75%; }
        .strength-strong { background: #27ae60; width: 100%; }

        .checkbox-group {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            gap: 0.5rem;
        }

        .checkbox-group input[type="checkbox"] {
            margin-top: 0.2rem;
        }

        .checkbox-group label {
            margin-bottom: 0;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .checkbox-group a {
            color: #667eea;
            text-decoration: none;
        }

        .checkbox-group a:hover {
            text-decoration: underline;
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .alert-error {
            background: #fee;
            color: #c53030;
            border: 1px solid #feb2b2;
        }

        .alert-success {
            background: #f0fff4;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }

        .links {
            text-align: center;
            margin-top: 1rem;
        }

        .links a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .field-error {
            border-color: #e74c3c !important;
        }

        .error-message {
            color: #e74c3c;
            font-size: 0.8rem;
            margin-top: 0.3rem;
        }

        @media (max-width: 600px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .form-row .form-group {
                margin-bottom: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>🚀 Register</h1>
            <p>Buat akun baru untuk memulai</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form action="proses_register.php" method="POST" id="registerForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($form_data['username'] ?? ''); ?>"
                           placeholder="Minimal 3 karakter">
                    <div class="error-message" id="username-error"></div>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                           placeholder="contoh@email.com">
                    <div class="error-message" id="email-error"></div>
                </div>
            </div>

            <div class="form-group">
                <label for="full_name">Nama Lengkap</label>
                <input type="text" id="full_name" name="full_name" required 
                       value="<?php echo htmlspecialchars($form_data['full_name'] ?? ''); ?>"
                       placeholder="Nama lengkap Anda">
                <div class="error-message" id="fullname-error"></div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Minimal 8 karakter">
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strength-fill"></div>
                        </div>
                        <div id="strength-text">Password strength: Weak</div>
                    </div>
                    <div class="error-message" id="password-error"></div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           placeholder="Ulangi password">
                    <div class="error-message" id="confirm-error"></div>
                </div>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">
                    Saya setuju dengan <a href="terms.php" target="_blank">Syarat & Ketentuan</a> 
                    dan <a href="privacy.php" target="_blank">Kebijakan Privasi</a>
                </label>
            </div>

            <button type="submit" class="btn" id="submitBtn" disabled>
                Daftar Sekarang
            </button>
        </form>

        <div class="links">
            <a href="login.php">Sudah punya akun? Login</a>
        </div>
    </div>

    <script>
        const form = document.getElementById('registerForm');
        const submitBtn = document.getElementById('submitBtn');
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');
        const strengthFill = document.getElementById('strength-fill');
        const strengthText = document.getElementById('strength-text');
        const termsCheckbox = document.getElementById('terms');

        // Real-time validation
        function validateForm() {
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const fullName = document.getElementById('full_name').value;
            const password = passwordInput.value;
            const confirmPassword = confirmInput.value;
            const termsAccepted = termsCheckbox.checked;

            let isValid = true;

            // Clear previous errors
            document.querySelectorAll('.field-error').forEach(el => el.classList.remove('field-error'));
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');

            // Username validation
            if (username.length < 3) {
                showError('username', 'Username minimal 3 karakter');
                isValid = false;
            } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                showError('username', 'Username hanya boleh huruf, angka, dan underscore');
                isValid = false;
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showError('email', 'Format email tidak valid');
                isValid = false;
            }

            // Full name validation
            if (fullName.length < 2) {
                showError('full_name', 'Nama lengkap minimal 2 karakter');
                isValid = false;
            }

            // Password validation
            if (password.length < 8) {
                showError('password', 'Password minimal 8 karakter');
                isValid = false;
            }

            // Confirm password validation
            if (password !== confirmPassword) {
                showError('confirm_password', 'Password tidak cocok');
                isValid = false;
            }

            // Terms validation
            if (!termsAccepted) {
                isValid = false;
            }

            // Enable/disable submit button
            submitBtn.disabled = !isValid;
            
            return isValid;
        }

        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            const errorElement = document.getElementById(fieldId.replace('_', '') + '-error');
            
            field.classList.add('field-error');
            errorElement.textContent = message;
        }

        // Password strength checker
        function checkPasswordStrength(password) {
            let strength = 0;
            let strengthText = 'Weak';
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]+/)) strength++;
            if (password.match(/[A-Z]+/)) strength++;
            if (password.match(/[0-9]+/)) strength++;
            if (password.match(/[^a-zA-Z0-9]+/)) strength++;

            strengthFill.className = 'strength-fill';
            
            if (strength <= 2) {
                strengthFill.classList.add('strength-weak');
                strengthText = 'Weak';
            } else if (strength === 3) {
                strengthFill.classList.add('strength-fair');
                strengthText = 'Fair';
            } else if (strength === 4) {
                strengthFill.classList.add('strength-good');
                strengthText = 'Good';
            } else {
                strengthFill.classList.add('strength-strong');
                strengthText = 'Strong';
            }

            document.getElementById('strength-text').textContent = `Password strength: ${strengthText}`;
        }

        // Event listeners
        passwordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
            validateForm();
        });

        [document.getElementById('username'), document.getElementById('email'), 
         document.getElementById('full_name'), confirmInput, termsCheckbox].forEach(input => {
            input.addEventListener('input', validateForm);
            input.addEventListener('change', validateForm);
        });

        // Form submission
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Mendaftar...';
        });

        // Auto focus ke username
        document.getElementById('username').focus();
    </script>
</body>
</html>