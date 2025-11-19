<?php
/**
 * Upload ảnh cho logo và bìa công ty
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit();
}

// Kiểm tra quyền nhà tuyển dụng
requireRole(ROLE_RECRUITER);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$file = $_FILES['image'];
$type = $_POST['type'] ?? 'logo'; // 'logo' hoặc 'banner'

// Kiểm tra lỗi upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Lỗi upload file']);
    exit();
}

// Kiểm tra kích thước file (max 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File quá lớn (tối đa 5MB)']);
    exit();
}

// Kiểm tra loại file
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)']);
    exit();
}

// Tạo thư mục upload nếu chưa có
$upload_dir = __DIR__ . '/../../../uploads/';
if ($type === 'logo') {
    $upload_dir .= 'logos/';
} else {
    $upload_dir .= 'banners/';
}

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Tạo tên file unique
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('img_', true) . '_' . time() . '.' . $extension;
$filepath = $upload_dir . $filename;

// Upload file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode(['success' => false, 'message' => 'Không thể lưu file']);
    exit();
}

// Trả về đường dẫn tương đối để lưu DB (không kèm BASE_URL)
$url = BASE_URL . 'uploads/' . ($type === 'logo' ? 'logos/' : 'banners/') . $filename;

echo json_encode([
    'success' => true,
    'url' => $url,
    'message' => 'Upload thành công'
]);

