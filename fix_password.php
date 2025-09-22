<?php
// fix_passwords.php - Script buat fix password hash
include 'db.php';

echo "<h2>🔐 Password Hash Fixer</h2>";
echo "<pre style='background: #1a1a1a; color: #00ff00; padding: 20px; border-radius: 8px;'>";

// Cek current hash method
echo "=== CURRENT PASSWORD ANALYSIS ===\n";
$result = $conn->query("SELECT username, password FROM users LIMIT 5");
while ($row = $result->fetch_assoc()) {
    $hash = $row['password'];
    $username = $row['username'];
    
    echo "User: $username\n";
    echo "Hash: $hash\n";
    
    // Deteksi hash type
    if (strlen($hash) == 32) {
        echo "Type: MD5 (32 chars)\n";
    } elseif (strlen($hash) == 40) {
        echo "Type: SHA1 (40 chars)\n";
    } elseif (strlen($hash) == 60 && substr($hash, 0, 3) == '$2y') {
        echo "Type: bcrypt (correct!)\n";
    } else {
        echo "Type: Unknown (length: " . strlen($hash) . ")\n";
    }
    echo "---\n";
}

// Option 1: Update existing passwords to bcrypt
if (isset($_POST['update_passwords'])) {
    echo "\n=== UPDATING PASSWORDS ===\n";
    
    $passwords = [
        'admin' => 'rahasia123',
        'adm-cbn1' => 'password123',  // Ganti sesuai password asli
        'adm-cbn' => 'password123',   // Ganti sesuai password asli
        'adm-po' => 'password123'     // Ganti sesuai password asli
    ];
    
    foreach ($passwords as $username => $plaintext) {
        $hashed = password_hash($plaintext, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmt->bind_param("ss", $hashed, $username);
        
        if ($stmt->execute()) {
            echo "✅ Updated password for: $username\n";
        } else {
            echo "❌ Failed to update: $username\n";
        }
    }
    
    echo "\n🎉 All passwords updated to bcrypt!\n";
}

// Option 2: Temporary fix in login process
if (isset($_POST['create_temp_fix'])) {
    echo "\n=== CREATING TEMPORARY FIX ===\n";
    
    $temp_fix = '<?php
// temp_login_fix.php - Include ini di proses_login.php
function verifyPasswordCompat($password, $hash) {
    // Coba bcrypt dulu
    if (password_verify($password, $hash)) {
        return true;
    }
    
    // Fallback ke MD5 (untuk backward compatibility)
    if (md5($password) === $hash) {
        return true;
    }
    
    // Fallback ke SHA1
    if (sha1($password) === $hash) {
        return true;
    }
    
    return false;
}
?>';
    
    file_put_contents('temp_login_fix.php', $temp_fix);
    echo "✅ Created temp_login_fix.php\n";
    echo "📝 Add this to your proses_login.php:\n";
    echo "   include 'temp_login_fix.php';\n";
    echo "   // Replace password_verify() with verifyPasswordCompat()\n";
}

echo "</pre>";

// Form untuk actions
echo '<form method="POST" style="background: #333; padding: 20px; border-radius: 8px; margin-top: 20px;">';
echo '<h3 style="color: #fff;">Choose Fix Method:</h3>';
echo '<p style="color: #ccc;"><strong>Option 1 (Recommended):</strong> Update all passwords to bcrypt</p>';
echo '<button type="submit" name="update_passwords" style="background: #00ff00; color: #000; padding: 10px 20px; border: none; border-radius: 4px; margin: 5px;">Update Passwords</button>';
echo '<br><br>';
echo '<p style="color: #ccc;"><strong>Option 2:</strong> Create temporary compatibility fix</p>';
echo '<button type="submit" name="create_temp_fix" style="background: #ff9900; color: #000; padding: 10px 20px; border: none; border-radius: 4px; margin: 5px;">Create Temp Fix</button>';
echo '</form>';

echo '<div style="background: #ff3333; color: #fff; padding: 15px; border-radius: 8px; margin-top: 20px;">';
echo '<h3>⚠️ IMPORTANT NOTES:</h3>';
echo '<ul>';
echo '<li>Backup database lu dulu sebelum update passwords!</li>';
echo '<li>Pastiin lu tau password plaintext semua user</li>';
echo '<li>Option 1 lebih secure, Option 2 cuma temporary</li>';
echo '</ul>';
echo '</div>';
?>