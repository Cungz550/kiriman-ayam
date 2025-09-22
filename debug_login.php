<?php
// Debug detail untuk cek exact problem
$host = 'localhost';
$dbname = 'input_grid';  // Sesuai dengan database yang lu punya
$username = 'root';
$password = '';

echo "<h2>🔍 Detailed Debug</h2>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to database '$dbname'<br>";
} catch(PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit();
}

// Test exact login process
$test_username = 'admin';
$test_password = 'admin123';

echo "<h3>🧪 Testing Login Process</h3>";
echo "Testing username: <strong>$test_username</strong><br>";
echo "Testing password: <strong>$test_password</strong><br><br>";

// Step 1: Cari user
echo "<strong>Step 1: Find user</strong><br>";
$stmt = $pdo->prepare("
    SELECT id, username, email, password, full_name, role, is_active 
    FROM users 
    WHERE (username = :login OR email = :login) AND is_active = 1
");

$stmt->execute([':login' => $test_username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "✅ User found!<br>";
    echo "- ID: " . $user['id'] . "<br>";
    echo "- Username: " . $user['username'] . "<br>";
    echo "- Email: " . $user['email'] . "<br>";
    echo "- Full Name: " . $user['full_name'] . "<br>";
    echo "- Role: " . $user['role'] . "<br>";
    echo "- Active: " . ($user['is_active'] ? 'Yes' : 'No') . "<br>";
    echo "- Password hash: " . substr($user['password'], 0, 50) . "...<br><br>";
    
    // Step 2: Verify password
    echo "<strong>Step 2: Verify password</strong><br>";
    $verify_result = password_verify($test_password, $user['password']);
    echo "Password verification: " . ($verify_result ? "✅ SUCCESS" : "❌ FAILED") . "<br>";
    
    if (!$verify_result) {
        echo "<br><strong>🔧 Password Debug:</strong><br>";
        
        // Test dengan password lain
        $other_passwords = ['password123', 'admin', '123456', 'admin123'];
        foreach ($other_passwords as $test_pass) {
            $result = password_verify($test_pass, $user['password']);
            echo "- Testing '$test_pass': " . ($result ? "✅ MATCH" : "❌ No match") . "<br>";
        }
        
        // Generate hash baru
        echo "<br><strong>🔄 Generating new hash:</strong><br>";
        $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
        echo "New hash for '$test_password': " . $new_hash . "<br>";
        
        // Update password
        echo "<br><strong>🔧 Fixing password:</strong><br>";
        $update_stmt = $pdo->prepare("UPDATE users SET password = :new_pass WHERE username = :username");
        $update_stmt->execute([
            ':new_pass' => $new_hash,
            ':username' => $test_username
        ]);
        echo "✅ Password updated for user '$test_username'<br>";
        
        // Test lagi
        $verify_again = password_verify($test_password, $new_hash);
        echo "Verification after update: " . ($verify_again ? "✅ SUCCESS" : "❌ STILL FAILED") . "<br>";
    }
    
} else {
    echo "❌ User not found!<br>";
    echo "Checking all users:<br>";
    
    $all_users = $pdo->query("SELECT username, email, is_active FROM users");
    while ($row = $all_users->fetch(PDO::FETCH_ASSOC)) {
        echo "- Username: '{$row['username']}', Email: '{$row['email']}', Active: " . ($row['is_active'] ? 'Yes' : 'No') . "<br>";
    }
}

// Test input sanitization
echo "<h3>🧹 Input Sanitization Test</h3>";
function test_sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

$sanitized = test_sanitize($test_username);
echo "Original: '$test_username'<br>";
echo "Sanitized: '$sanitized'<br>";
echo "Same? " . ($test_username === $sanitized ? "✅ Yes" : "❌ No") . "<br>";

echo "<h3>📝 Quick Fix SQL</h3>";
echo "Run this SQL to fix password:<br>";
echo "<code>UPDATE users SET password = '" . password_hash('admin123', PASSWORD_DEFAULT) . "' WHERE username = 'admin';</code><br><br>";

echo "<p><strong>After running this debug, try login again!</strong></p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
code { background: #f4f4f4; padding: 10px; display: block; margin: 10px 0; word-break: break-all; }
</style>