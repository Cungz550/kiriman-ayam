<?php
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
?>