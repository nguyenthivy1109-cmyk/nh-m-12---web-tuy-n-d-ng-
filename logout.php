<?php
/**
 * Trang đăng xuất
 */

require_once 'config.php';
require_once 'includes/functions.php';

// Bắt đầu session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Xóa tất cả session
$_SESSION = array();

// Xóa session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Xóa remember token cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Hủy session
session_destroy();

// Chuyển về trang chủ
header("Location: " . publicRoute('home'));
exit();

?>

