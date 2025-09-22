<?php
session_start();

// Konfigurasi database
$host = 'localhost';
$dbname = 'input_grid';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    $_SESSION['error'] = 'Koneksi database gagal: ' . $e->getMessage();
    header('Location: register.php');
    exit();
}

// Fungsi untuk sanitasi input
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Fungsi untuk validasi email
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Fungsi untuk validasi username
function is_valid_username($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username);
}

// Fungsi untuk validasi password strength
function validate_password($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password harus mengandung huruf kecil';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password harus mengandung huruf besar';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password harus mengandung angka';
    }
    
    return $errors;
}

// Fungsi untuk log registrasi
function log_registration($username, $email, $success, $ip) {
    $log_file = 'registration_logs.txt';
    $timestamp = date('Y-m-d H:i:s');
    $status = $success ? 'SUCCESS' : 'FAILED';
    $log_entry = "[$timestamp] $status - Username: $username, Email: $email, IP: $ip\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Cek apakah request method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit();
}

// Ambil data dari form
$reg_username = sanitize_input($_POST['username'] ?? '');
$reg_email = sanitize_input($_POST['email'] ?? '');
$reg_full_name = sanitize_input($_POST['full_name'] ?? '');
$reg_password = $_POST['password'] ?? '';
$reg_confirm_password = $_POST['confirm_password'] ?? '';
$reg_terms = isset($_POST['terms']) ? true : false;

// Simpan form data untuk ditampilkan kembali jika error
$_SESSION['form_data'] = [
    'username' => $reg_username,
    'email' => $reg_email,
    'full_name' => $reg_full_name
];

// Array untuk menyimpan error
$errors = [];

// Validasi input
if (empty($reg_username)) {
    $errors[] = 'Username harus diisi';
} elseif (!is_valid_username($reg_username)) {
    $errors[] = 'Username hanya boleh huruf, angka, dan underscore (3-50 karakter)';
}

if (empty($reg_email)) {
    $errors[] = 'Email harus diisi';
} elseif (!is_valid_email($reg_email)) {
    $errors[] = 'Format email tidak valid';
}

if (empty($reg_full_name)) {
    $errors[] = 'Nama lengkap harus diisi';
} elseif (strlen($reg_full_name) < 2 || strlen($reg_full_name) > 100) {
    $errors[] = 'Nama lengkap harus 2-100 karakter';
}

if (empty($reg_password)) {
    $errors[] = 'Password harus diisi';
} else {
    $password_errors = validate_password($reg_password);
    $errors = array_merge($errors, $password_errors);
}

if ($reg_password !== $reg_confirm_password) {
    $errors[] = 'Konfirmasi password tidak cocok';
}

if (!$reg_terms) {
    $errors[] = 'Anda harus menyetujui syarat dan ketentuan';
}

// Rate limiting untuk registrasi
$ip_address = $_SERVER['REMOTE_ADDR'];
$max_reg_per_hour = 3;

if (!isset($_SESSION['registration_attempts'])) {
    $_SESSION['registration_attempts'] = [];
}

$current_time = time();
$hour_ago = $current_time - 3600;

// Bersihkan attempt lama
$_SESSION['registration_attempts'] = array_filter($_SESSION['registration_attempts'], function($timestamp) use ($hour_ago) {
    return $timestamp > $hour_ago;
});

// Cek limit registrasi
if (count($_SESSION['registration_attempts']) >= $max_reg_per_hour) {
    $errors[] = 'Terlalu banyak percobaan registrasi. Coba lagi dalam 1 jam.';
}

// Jika ada error, redirect kembali
if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    header('Location: register.php');
    exit();
}

try {
    // Cek apakah username sudah ada
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute([':username' => $reg_username]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = 'Username sudah digunakan. Silakan pilih username lain.';
        header('Location: register.php');
        exit();
    }
    
    // Cek apakah email sudah ada
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $reg_email]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = 'Email sudah terdaftar. Silakan gunakan email lain atau <a href="login.php">login</a>.';
        header('Location: register.php');
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($reg_password, PASSWORD_DEFAULT);
    
    // Insert user baru
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, full_name, created_at, is_active, role) 
        VALUES (:username, :email, :password, :full_name, NOW(), 1, 'user')
    ");
    
    $stmt->execute([
        ':username' => $reg_username,
        ':email' => $reg_email,
        ':password' => $hashed_password,
        ':full_name' => $reg_full_name
    ]);
    
    // Ambil user ID yang baru dibuat
    $user_id = $pdo->lastInsertId();
    
    // Log registrasi sukses
    log_registration($reg_username, $reg_email, true, $ip_address);
    
    // Tambah ke registration attempts
    $_SESSION['registration_attempts'][] = $current_time;
    
    // Hapus form data dari session
    unset($_SESSION['form_data']);
    
    // Optional: Auto login setelah registrasi
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $reg_username;
    $_SESSION['email'] = $reg_email;
    $_SESSION['full_name'] = $reg_full_name;
    $_SESSION['role'] = 'user';
    $_SESSION['login_time'] = time();
    
    // Update last login
    $update_stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
    $update_stmt->execute([':id' => $user_id]);
    
    // Redirect ke dashboard dengan pesan sukses
    $_SESSION['success'] = 'Registrasi berhasil! Selamat datang, ' . $reg_full_name . '!';
    header('Location: dashboard.php');
    exit();
    
    // Alternatif: Redirect ke login tanpa auto login
    /*
    $_SESSION['success'] = 'Registrasi berhasil! Silakan login dengan akun Anda.';
    header('Location: login.php');
    exit();
    */
    
} catch(PDOException $e) {
    // Log error
    error_log("Registration error: " . $e->getMessage());
    
    // Log registrasi gagal
    log_registration($reg_username, $reg_email, false, $ip_address);
    
    $_SESSION['error'] = 'Terjadi kesalahan sistem. Silakan coba lagi.';
    header('Location: register.php');
    exit();
}

// Fungsi untuk generate username suggestions
function generate_username_suggestions($base_username, $pdo) {
    $suggestions = [];
    
    for ($i = 1; $i <= 5; $i++) {
        $suggestion = $base_username . $i;
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute([':username' => $suggestion]);
        
        if ($stmt->rowCount() === 0) {
            $suggestions[] = $suggestion;
        }
    }
    
    // Tambah suggestions dengan random number
    for ($i = 0; $i < 3; $i++) {
        $suggestion = $base_username . rand(100, 999);
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute([':username' => $suggestion]);
        
        if ($stmt->rowCount() === 0 && !in_array($suggestion, $suggestions)) {
            $suggestions[] = $suggestion;
        }
    }
    
    return array_slice($suggestions, 0, 5);
}

// Fungsi untuk welcome email (bonus feature)
function send_welcome_email($email, $full_name) {
    $subject = 'Selamat Datang di System!';
    $message = "
    <html>
    <head>
        <title>Selamat Datang</title>
    </head>
    <body>
        <h2>Halo, $full_name!</h2>
        <p>Terima kasih telah mendaftar di sistem kami.</p>
        <p>Akun Anda sudah aktif dan siap digunakan.</p>
        <p>Jika ada pertanyaan, jangan ragu untuk menghubungi kami.</p>
        <br>
        <p>Salam,<br>Tim Support</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: noreply@yoursite.com' . "\r\n";
    
    // Uncomment untuk aktifkan email
    // return mail($email, $subject, $message, $headers);
    
    return true; // Return true for testing
}
?>