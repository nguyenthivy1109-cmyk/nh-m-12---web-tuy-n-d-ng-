<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/nha_tuyen_dung.php';
require_once __DIR__ . '/../../models/tin_td.php';

header('Content-Type: application/json');

try {
    requireRole(ROLE_RECRUITER);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
        exit;
    }

    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
        exit;
    }

    $tin_id = isset($_POST['tin_id']) ? (int)$_POST['tin_id'] : 0;
    $new_status = isset($_POST['trang_thai_tin']) ? (int)$_POST['trang_thai_tin'] : null;

    // Kiểm tra trạng thái hợp lệ (0-3)
    $valid_statuses = [JOB_STATUS_DRAFT, JOB_STATUS_ACTIVE, JOB_STATUS_PAUSED, JOB_STATUS_CLOSED];
    if (!$tin_id || !in_array($new_status, $valid_statuses, true)) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    $nha_td = getNhaTuyenDungByTaiKhoanId($pdo, $user_id);
    if (!$nha_td) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin nhà tuyển dụng']);
        exit;
    }

    $updated = updateTrangThaiTin($pdo, $tin_id, $nha_td['nha_td_id'], $new_status);
    if (!$updated) {
        echo json_encode(['success' => false, 'message' => 'Không thể cập nhật trạng thái tin']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
