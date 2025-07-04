<?php
// Güvenlik ayarları
session_start();

// Veritabanı ayarları (isteğe bağlı - şimdilik dosya tabanlı)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123'); // Şifrenizi buradan değiştirebilirsiniz

// Site ayarları
define('SITE_NAME', 'SON-ARE Yönetim Paneli');
define('SITE_VERSION', '1.0');

// Güvenlik fonksiyonları
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>