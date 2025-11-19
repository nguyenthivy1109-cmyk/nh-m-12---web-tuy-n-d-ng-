<?php
/**
 * Trang chính - Redirect đến router
 */

require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Kiểm tra đăng nhập và quyền nhà tuyển dụng
requireRole(ROLE_RECRUITER);

// Redirect đến router
$page = $_GET['page'] ?? 'dashboard';
header("Location: " . recruiterRoute($page));
exit();
