<?php
// test_login.php - Script buat debug login
session_start();
include 'db.php';

// Test 1: Cek koneksi database
echo "=== DATABASE CONNECTION TEST ===\n";
if ($conn->connect_error) {
    echo "❌ Connection failed: " . $conn->connect_error . "\n";
} else {
    echo "✅ Database connected successfully\n";
}

// Test 2: Cek apakah table users ada
echo "\n=== TABLE STRUCTURE TEST ===\n";
$result = $conn->query("DESCRIBE users");
if ($result) {
    echo "✅ Table 'users' exists\n";
    while ($row = $result->fetch_assoc()) {
        echo "Column: {$row['Field']} | Type: {$row['Type']}\n";
    }
} else {
    echo "❌ Table 'users' not found or error: " . $conn->error . "\n";
}

// Test 3: Cek apakah ada user
echo "\n=== USER DATA TEST ===\n";
$result = $conn->query("SELECT username, LEFT(password, 20) as password_preview FROM users LIMIT 5");
if ($result && $result->num_rows > 0) {
    echo "✅ Found users:\n";
    while ($row = $result->fetch_assoc()) {
        echo "Username: {$row['username']} | Password hash: {$row['password_preview']}...\n";
    }
} else {
    echo "❌ No users found\n";
}

// Test 4: Test password hashing
echo "\n=== PASSWORD HASHING TEST ===\n";
$test_password = "testpassword";
$hashed = password_hash($test_password, PASSWORD_DEFAULT);
echo "Original: $test_password\n";
echo "Hashed: $hashed\n";
echo "Verification: " . (password_verify($test_password, $hashed) ? "✅ SUCCESS" : "❌ FAILED") . "\n";

// Test 5: Cek session
echo "\n=== SESSION TEST ===\n";
echo "Session status: " . session_status() . "\n";
echo "Session ID: " . session_id() . "\n";
$_SESSION['test'] = 'working';
echo "Session test: " . (isset($_SESSION['test']) ? "✅ Working" : "❌ Not working") . "\n";

// Test 6: Input dari form (jika ada)
if ($_POST) {
    echo "\n=== FORM INPUT TEST ===\n";
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    echo "Received username: '$username'\n";
    echo "Received password: '$password'\n";
    
    if ($username && $password) {
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "✅ User found in database\n";
            echo "Password verification: " . (password_verify($password, $user['password']) ? "✅ SUCCESS" : "❌ FAILED") . "\n";
        } else {
            echo "❌ User not found\n";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Debug</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #00ff00; padding: 20px; }
        form { background: #333; padding: 20px; border-radius: 8px; margin-top: 20px; }
        input { padding: 10px; margin: 5px; border: none; border-radius: 4px; }
        button { padding: 10px 20px; background: #00ff00; color: #000; border: none; border-radius: 4px; cursor: pointer; }
        pre { background: #222; padding: 15px; border-radius: 8px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔧 Login Debug Tool</h1>
    <pre><?php echo ob_get_clean(); ?></pre>
    
    <form method="POST" style="color: #fff;">
        <h3>Test Login Form</h3>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Test Login</button>
    </form>
</body>
</html>