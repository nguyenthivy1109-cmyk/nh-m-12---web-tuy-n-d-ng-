<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
    if (!$tin_id) {
        echo json_encode(['success' => false, 'message' => 'ID tin tuyển dụng không hợp lệ']);
        exit;
    }

    // Kiểm tra tin có thuộc về nhà tuyển dụng này không
    $nha_td = getNhaTuyenDungByTaiKhoanId($pdo, $user_id);
    if (!$nha_td) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin nhà tuyển dụng']);
        exit;
    }

    // Kiểm tra tin có thuộc về nhà tuyển dụng này
    $stmt = $pdo->prepare("SELECT tin_id, nha_td_id FROM tin_td WHERE tin_id = ? AND nha_td_id = ? AND xoa_luc IS NULL");
    $stmt->execute([$tin_id, $nha_td['nha_td_id']]);
    $job = $stmt->fetch();
    
    if (!$job) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy tin tuyển dụng hoặc bạn không có quyền xóa tin này']);
        exit;
    }

    // Xóa mềm tin tuyển dụng - dùng PDO trực tiếp để có thể kiểm tra lỗi
    $stmt = $pdo->prepare("UPDATE tin_td SET xoa_luc = NOW() WHERE tin_id = ?");
    $result = $stmt->execute([(int)$tin_id]);
    
    if (!$result) {
        $errorInfo = $stmt->errorInfo();
        echo json_encode(['success' => false, 'message' => 'Không thể xóa tin tuyển dụng: ' . ($errorInfo[2] ?? 'Lỗi không xác định')]);
        exit;
    }
    
    // Kiểm tra xem có dòng nào bị ảnh hưởng không
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy tin tuyển dụng để xóa']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Đã xóa tin tuyển dụng thành công']);
} catch (Throwable $e) {
    error_log("Delete job error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}

