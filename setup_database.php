<?php
// Setup database otomatis
// Jalanin ini sekali aja buat bikin database sama table

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'input_grid'; // Ganti nama database sesuai kebutuhan

echo "<h2>🚀 Setting up Database...</h2>";

try {
    // Koneksi ke MySQL tanpa specify database dulu
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to MySQL<br>";
    
    // Bikin database kalo belum ada
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    echo "✅ Database '$dbname' created/exists<br>";
    
    // Pindah ke database yang baru
    $pdo->exec("USE $dbname");
    echo "✅ Using database '$dbname'<br>";
    
    // Bikin table users
    $createTable = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        is_active TINYINT(1) DEFAULT 1,
        role ENUM('admin', 'user') DEFAULT 'user'
    )";
    
    $pdo->exec($createTable);
    echo "✅ Table 'users' created<br>";
    
    // Cek apakah udah ada data
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insert sample data
        $insertData = "
        INSERT INTO users (username, email, password, full_name, role) VALUES 
        ('admin', 'admin@example.com', :admin_pass, 'Administrator', 'admin'),
        ('user1', 'user1@example.com', :user_pass, 'User Pertama', 'user'),
        ('demo', 'demo@example.com', :demo_pass, 'Demo User', 'user')";
        
        $stmt = $pdo->prepare($insertData);
        $stmt->execute([
            ':admin_pass' => password_hash('admin123', PASSWORD_DEFAULT),
            ':user_pass' => password_hash('user123', PASSWORD_DEFAULT),
            ':demo_pass' => password_hash('demo123', PASSWORD_DEFAULT)
        ]);
        
        echo "✅ Sample users inserted<br>";
    } else {
        echo "⚠️ Users already exist ($count users)<br>";
    }
    
    // Bikin index
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_username ON users(username)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_email ON users(email)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_active ON users(is_active)");
    echo "✅ Indexes created<br>";
    
    echo "<br><h3>🎉 Setup Complete!</h3>";
    echo "<p><strong>Test accounts:</strong></p>";
    echo "<ul>";
    echo "<li>Admin: username = <code>admin</code>, password = <code>admin123</code></li>";
    echo "<li>User: username = <code>user1</code>, password = <code>user123</code></li>";
    echo "<li>Demo: username = <code>demo</code>, password = <code>demo123</code></li>";
    echo "</ul>";
    
    echo "<p>Sekarang lu bisa coba login di <a href='login.php'>login.php</a></p>";
    
    // Tampilkan data users
    echo "<h3>📊 Users in Database:</h3>";
    $stmt = $pdo->query("SELECT id, username, email, full_name, role, is_active FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Role</th><th>Active</th></tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . $user['username'] . "</td>";
        echo "<td>" . $user['email'] . "</td>";
        echo "<td>" . $user['full_name'] . "</td>";
        echo "<td>" . $user['role'] . "</td>";
        echo "<td>" . ($user['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "<p>Check your database configuration:</p>";
    echo "<ul>";
    echo "<li>MySQL server running?</li>";
    echo "<li>Username/password correct?</li>";
    echo "<li>Permission to create database?</li>";
    echo "</ul>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
code { background: #f4f4f4; padding: 2px 4px; }
ul { margin: 10px 0; }
</style>