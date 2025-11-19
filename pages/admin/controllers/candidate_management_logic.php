<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../includes/db.php';

// Kiểm tra đăng nhập và vai trò admin
// Hỗ trợ cả $_SESSION['vai_tro'] và $_SESSION['vai_tro_id']
$vai_tro = isset($_SESSION['vai_tro_id']) ? $_SESSION['vai_tro_id'] : (isset($_SESSION['vai_tro']) ? $_SESSION['vai_tro'] : 0);
if (!isset($_SESSION['user_id']) || $vai_tro != ROLE_ADMIN) {
    header('Location: ' . publicRoute('login'));
    exit();
}

// Lấy thông tin admin hiện tại
$stmt = $conn->prepare("SELECT a.ho_ten, tk.ten_dn FROM admins a JOIN tai_khoans tk ON a.tai_khoan_id = tk.tai_khoan_id WHERE a.tai_khoan_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

// Khởi tạo biến
$message = '';
$messageType = '';

// Xử lý các action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    switch ($action) {
        case 'update_candidate':
            $ung_vien_id = $_POST['ung_vien_id'];
            $ho_ten = trim($_POST['ho_ten']);
            $ngay_sinh = $_POST['ngay_sinh'];
            $gioi_tinh = $_POST['gioi_tinh'];
            $noi_o = trim($_POST['noi_o']);
            $tieu_de_cv = trim($_POST['tieu_de_cv']);
            $gioi_thieu = trim($_POST['gioi_thieu']);

            $stmt = $conn->prepare("UPDATE ung_viens SET ho_ten = ?, ngay_sinh = ?, gioi_tinh = ?, noi_o = ?, tieu_de_cv = ?, gioi_thieu = ?, cap_nhat_luc = NOW() WHERE ung_vien_id = ?");
            $stmt->bind_param("ssssssi", $ho_ten, $ngay_sinh, $gioi_tinh, $noi_o, $tieu_de_cv, $gioi_thieu, $ung_vien_id);
            
            if ($stmt->execute()) {
                $message = "Đã cập nhật thông tin ứng viên thành công!";
                $messageType = "success";
            } else {
                $message = "Lỗi khi cập nhật thông tin ứng viên!";
                $messageType = "danger";
            }
            break;

        case 'toggle_account':
            $tai_khoan_id = $_POST['tai_khoan_id'];
            $current_status = $_POST['current_status'];
            $new_status = $current_status == 1 ? 0 : 1;

            $stmt = $conn->prepare("UPDATE tai_khoans SET kich_hoat = ? WHERE tai_khoan_id = ?");
            $stmt->bind_param("ii", $new_status, $tai_khoan_id);
            
            if ($stmt->execute()) {
                $status_text = $new_status == 1 ? "kích hoạt" : "khóa";
                $message = "Đã {$status_text} tài khoản thành công!";
                $messageType = "success";
            } else {
                $message = "Lỗi khi thay đổi trạng thái tài khoản!";
                $messageType = "danger";
            }
            break;

        case 'delete_candidate':
            $tai_khoan_id = $_POST['tai_khoan_id'];
            
            // Xóa ứng viên và tài khoản
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("DELETE FROM ung_viens WHERE tai_khoan_id = ?");
                $stmt->bind_param("i", $tai_khoan_id);
                $stmt->execute();
                
                $stmt = $conn->prepare("DELETE FROM tai_khoans WHERE tai_khoan_id = ?");
                $stmt->bind_param("i", $tai_khoan_id);
                $stmt->execute();
                
                $conn->commit();
                $message = "Đã xóa ứng viên thành công!";
                $messageType = "success";
            } catch (Exception $e) {
                $conn->rollback();
                $message = "Lỗi khi xóa ứng viên!";
                $messageType = "danger";
            }
            break;
    }
}

// Lấy danh sách ứng viên
$query = "
    SELECT 
        uv.*,
        tk.ten_dn,
        tk.email,
        tk.kich_hoat,
        tk.dang_nhap_cuoi_luc
    FROM ung_viens uv
    INNER JOIN tai_khoans tk ON uv.tai_khoan_id = tk.tai_khoan_id
    ORDER BY uv.tao_luc DESC
";

$result = $conn->query($query);
$candidates = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Thống kê
$stats = [
    'total_active' => 0,
    'total_inactive' => 0,
    'total' => 0
];

$query_stats = "SELECT
    COUNT(*) as total,
    COUNT(CASE WHEN tk.kich_hoat = 1 THEN 1 END) as active,
    COUNT(CASE WHEN tk.kich_hoat = 0 THEN 1 END) as inactive
FROM ung_viens uv
INNER JOIN tai_khoans tk ON uv.tai_khoan_id = tk.tai_khoan_id";

$result_stats = $conn->query($query_stats);
if ($result_stats) {
    $stat_data = $result_stats->fetch_assoc();
    $stats['total'] = $stat_data['total'] ?? 0;
    $stats['total_active'] = $stat_data['active'] ?? 0;
    $stats['total_inactive'] = $stat_data['inactive'] ?? 0;
}
?>
