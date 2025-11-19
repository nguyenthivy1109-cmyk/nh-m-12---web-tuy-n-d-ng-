<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/nha_tuyen_dung.php';

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

    $nha_td = getNhaTuyenDungByTaiKhoanId($pdo, $user_id);
    if (!$nha_td) {
        // Tạo mới nếu chưa có
        $nha_td_id = createNhaTuyenDung($pdo, [
            'tai_khoan_id' => $user_id,
            'cong_ty_id' => null,
            'ho_ten' => null,
            'chuc_danh' => null,
            'email_cong_viec' => null
        ]);
        $nha_td = getNhaTuyenDungById($pdo, $nha_td_id);
    }

    $data = [
        'cong_ty_id' => $nha_td['cong_ty_id'] ?? null,
        'ho_ten' => sanitize($_POST['ho_ten'] ?? ''),
        'chuc_danh' => sanitize($_POST['chuc_danh'] ?? ''),
        'email_cong_viec' => sanitize($_POST['email_cong_viec'] ?? ''),
    ];

    if (!updateNhaTuyenDung($pdo, $nha_td['nha_td_id'], $data)) {
        echo json_encode(['success' => false, 'message' => 'Không thể cập nhật thông tin nhà tuyển dụng']);
        exit;
    }

    $updated = getNhaTuyenDungById($pdo, $nha_td['nha_td_id']);

    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật thông tin nhà tuyển dụng thành công',
        'data' => $updated
    ]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
