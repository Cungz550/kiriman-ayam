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
    header('Location: login.php');
    exit();
}

// Fungsi untuk sanitasi input
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Fungsi untuk log aktivitas login
function log_login_attempt($username, $success, $ip) {
    $log_file = 'login_logs.txt';
    $timestamp = date('Y-m-d H:i:s');
    $status = $success ? 'SUCCESS' : 'FAILED';
    $log_entry = "[$timestamp] $status - Username: $username, IP: $ip\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Cek apakah request method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

// Ambil data dari form
$login_username = sanitize_input($_POST['username'] ?? '');
$login_password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']) ? true : false;

// Validasi input
if (empty($login_username) || empty($login_password)) {
    $_SESSION['error'] = 'Username dan password harus diisi!';
    header('Location: login.php');
    exit();
}

// Rate limiting sederhana (anti brute force)
$max_attempts = 5;
$lockout_time = 300; // 5 menit
$ip_address = $_SERVER['REMOTE_ADDR'];

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = [];
}

$current_time = time();
$attempts_key = md5($ip_address . $login_username);

// Bersihkan attempt lama
if (isset($_SESSION['login_attempts'][$attempts_key])) {
    if ($current_time - $_SESSION['login_attempts'][$attempts_key]['last_attempt'] > $lockout_time) {
        unset($_SESSION['login_attempts'][$attempts_key]);
    }
}

// Cek apakah masih dalam lockout
if (isset($_SESSION['login_attempts'][$attempts_key])) {
    if ($_SESSION['login_attempts'][$attempts_key]['count'] >= $max_attempts) {
        $remaining_time = $lockout_time - ($current_time - $_SESSION['login_attempts'][$attempts_key]['last_attempt']);
        $_SESSION['error'] = "Terlalu banyak percobaan login. Coba lagi dalam " . ceil($remaining_time / 60) . " menit.";
        header('Location: login.php');
        exit();
    }
}

try {
    // Query untuk mencari user berdasarkan username atau email
    $stmt = $pdo->prepare("
        SELECT id, username, email, password, full_name, role, is_active 
        FROM users 
        WHERE (username = :login OR email = :login) AND is_active = 1
    ");
    
    $stmt->execute([':login' => $login_username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($login_password, $user['password'])) {
        // Login berhasil
        
        // Reset login attempts
        unset($_SESSION['login_attempts'][$attempts_key]);
        
        // Set session data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();
        
        // Update last login di database
        $update_stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
        $update_stmt->execute([':id' => $user['id']]);
        
        // Set cookie untuk remember me
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (86400 * 30), '/', '', false, true); // 30 hari
            
            // Simpan token di database (optional, untuk keamanan lebih)
            $token_stmt = $pdo->prepare("UPDATE users SET remember_token = :token WHERE id = :id");
            $token_stmt->execute([':token' => password_hash($token, PASSWORD_DEFAULT), ':id' => $user['id']]);
        }
        
        // Log login sukses
        log_login_attempt($login_username, true, $ip_address);
        
        // Redirect berdasarkan role
        if ($user['role'] === 'admin') {
            header('Location: index.php');
        } else {
            header('Location: index.php');
        }
        exit();
        
    } else {
        // Login gagal
        
        // Increment login attempts
        if (!isset($_SESSION['login_attempts'][$attempts_key])) {
            $_SESSION['login_attempts'][$attempts_key] = ['count' => 0, 'last_attempt' => 0];
        }
        
        $_SESSION['login_attempts'][$attempts_key]['count']++;
        $_SESSION['login_attempts'][$attempts_key]['last_attempt'] = $current_time;
        
        // Log login gagal
        log_login_attempt($login_username, false, $ip_address);
        
        $remaining_attempts = $max_attempts - $_SESSION['login_attempts'][$attempts_key]['count'];
        
        if ($remaining_attempts > 0) {
            $_SESSION['error'] = "Username/email atau password salah! Sisa percobaan: $remaining_attempts";
        } else {
            $_SESSION['error'] = "Terlalu banyak percobaan login. Akun dikunci selama 5 menit.";
        }
        
        header('Location: login.php');
        exit();
    }
    
} catch(PDOException $e) {
    $_SESSION['error'] = 'Terjadi kesalahan sistem. Silakan coba lagi.';
    error_log("Login error: " . $e->getMessage());
    header('Location: login.php');
    exit();
}

// Fungsi untuk cek remember me token (bonus)
function check_remember_token($pdo) {
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        
        $stmt = $pdo->prepare("SELECT id, username, email, full_name, role, remember_token FROM users WHERE remember_token IS NOT NULL");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($users as $user) {
            if (password_verify($token, $user['remember_token'])) {
                // Auto login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['login_time'] = time();
                
                return true;
            }
        }
    }
    return false;
}

// Fungsi logout untuk clear session dan cookie
function logout() {
    session_start();
    session_destroy();
    
    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    header('Location: login.php');
    exit();
}

// Fungsi untuk cek apakah user sudah login
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk require login
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

// Fungsi untuk require admin
function require_admin() {
    require_login();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: dashboard.php');
        exit();
    }
}
?>