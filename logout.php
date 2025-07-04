<?php
require_once 'config.php';

// Oturumu sonlandır
session_destroy();

// Giriş sayfasına yönlendir
header('Location: login.php');
exit;
?>