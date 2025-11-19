<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$role = $_GET['role'] ?? null;
$page = $_GET['page'] ?? 'home';

if (!$role && isLoggedIn()) {
    $vai_tro_id = $_SESSION['vai_tro_id'] ?? null;
    
    if ($vai_tro_id == ROLE_ADMIN) {
        $role = 'admin';
    } elseif ($vai_tro_id == ROLE_RECRUITER) {
        $role = 'recruiter';
    } elseif ($vai_tro_id == ROLE_CANDIDATE) {
        $role = 'candidate';
    }
}

if (!$role) {
    $role = 'public';
}

$valid_routes = [
    'public' => [
        'home' => __DIR__ . '/../pages/public/home.php',
        'login' => __DIR__ . '/../pages/public/login.php',
        'register' => __DIR__ . '/../pages/public/register.php',
        'forgot-password' => __DIR__ . '/../pages/public/forgot-password.php',
        'about' => __DIR__ . '/../pages/public/about.php',
        'service' => __DIR__ . '/../pages/public/service.php',
        'contact' => __DIR__ . '/../pages/public/contact.php',
    ],
    'admin' => [
        'dashboard' => __DIR__ . '/../pages/admin/dashboard/index.php',
        'accounts' => __DIR__ . '/../pages/admin/accounts/index.php',
        'admin_management' => __DIR__ . '/../pages/admin/admin_management/index.php',
        'recruiter_management' => __DIR__ . '/../pages/admin/recruiter_management/index.php',
        'candidate_management' => __DIR__ . '/../pages/admin/candidate_management/index.php',
        'company_management' => __DIR__ . '/../pages/admin/company_management/index.php',
        'job_management' => __DIR__ . '/../pages/admin/job_management/index.php',
        'applications_management' => __DIR__ . '/../pages/admin/applications_management/index.php',
        'attachments_management' => __DIR__ . '/../pages/admin/attachments_management/index.php',
        'messages_management' => __DIR__ . '/../pages/admin/messages_management/index.php',
        'notifications_management' => __DIR__ . '/../pages/admin/notifications_management/index.php',
        'categories_management' => __DIR__ . '/../pages/admin/categories_management/index.php',
        'skills_dictionary' => __DIR__ . '/../pages/admin/skills_dictionary/index.php',
    ],
    'recruiter' => [
        'dashboard' => __DIR__ . '/../pages/recruiter/layout.php',
        'jobs' => __DIR__ . '/../pages/recruiter/layout.php',
        'post-job' => __DIR__ . '/../pages/recruiter/layout.php',
        'applications' => __DIR__ . '/../pages/recruiter/layout.php',
        'company' => __DIR__ . '/../pages/recruiter/layout.php',
        'messages' => __DIR__ . '/../pages/recruiter/layout.php',
    ],
    'candidate' => [
        'dashboard' => __DIR__ . '/../pages/candidate/index.php',
        'job-detail' => __DIR__ . '/../pages/candidate/job-detail.php',
        'apply' => __DIR__ . '/../pages/candidate/apply.php',
        'applications' => __DIR__ . '/../pages/candidate/applications.php',
        'profile' => __DIR__ . '/../pages/candidate/profile.php',
        'skills' => __DIR__ . '/../pages/candidate/skills.php',
    ],
];

// Kiểm tra route hợp lệ
if (!isset($valid_routes[$role]) || !isset($valid_routes[$role][$page])) {
    // Route không hợp lệ, redirect về trang chủ
    header("Location: " . BASE_URL);
    exit();
}

// Kiểm tra quyền truy cập
$required_role = null;
switch ($role) {
    case 'admin':
        $required_role = ROLE_ADMIN;
        break;
    case 'recruiter':
        $required_role = ROLE_RECRUITER;
        break;
    case 'candidate':
        $required_role = null;
        break;
    case 'public':
        break;
}

// Nếu cần đăng nhập, kiểm tra quyền
if ($required_role !== null) {
    if (!isLoggedIn()) {
        // Chưa đăng nhập, redirect về trang login
        header("Location: " . BASE_URL . "router/index.php?role=public&page=login");
        exit();
    }
    
    // Kiểm tra role
    $user_role = $_SESSION['vai_tro_id'] ?? null;
    if ($user_role != $required_role) {
        // Không đủ quyền, redirect về trang chủ
        header("Location: " . BASE_URL);
        exit();
    }
}

// Lấy đường dẫn file
$file_path = $valid_routes[$role][$page];

// Kiểm tra file tồn tại
if (!file_exists($file_path)) {
    // File không tồn tại, redirect về trang chủ
    header("Location: " . BASE_URL);
    exit();
}

// Include file
require_once $file_path;

