<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../includes/db.php';

// Kiểm tra đăng nhập và vai trò admin
// Hỗ trợ cả $_SESSION['vai_tro'] và $_SESSION['vai_tro_id']
require_once __DIR__ . '/../../../includes/functions.php';
$vai_tro = isset($_SESSION['vai_tro_id']) ? $_SESSION['vai_tro_id'] : (isset($_SESSION['vai_tro']) ? $_SESSION['vai_tro'] : 0);
if (!isset($_SESSION['user_id']) || $vai_tro != ROLE_ADMIN) {
    header("Location: " . publicRoute('login'));
    exit();
}

// Lấy thông tin admin
$stmt = $conn->prepare("SELECT a.ho_ten, tk.ten_dn FROM admins a JOIN tai_khoans tk ON a.tai_khoan_id = tk.tai_khoan_id WHERE a.tai_khoan_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

// Thống kê tổng quan
$stats = [];

// Số lượng ứng viên
$result = $conn->query("SELECT COUNT(*) as total FROM ung_viens");
$stats['total_candidates'] = $result->fetch_assoc()['total'];

// Số lượng nhà tuyển dụng
$result = $conn->query("SELECT COUNT(*) as total FROM nha_tuyen_dungs");
$stats['total_recruiters'] = $result->fetch_assoc()['total'];

// Số lượng công ty
$result = $conn->query("SELECT COUNT(*) as total FROM cong_tys");
$stats['total_companies'] = $result->fetch_assoc()['total'];

// Số lượng tin tuyển dụng
$result = $conn->query("SELECT COUNT(*) as total FROM tin_td");
$stats['total_jobs'] = $result->fetch_assoc()['total'];

// Tin tuyển dụng đang hiển thị (active)
$result = $conn->query("SELECT COUNT(*) as total FROM tin_td WHERE trang_thai_tin = 1");
$stats['active_jobs'] = $result->fetch_assoc()['total'];

// Tin tuyển dụng chờ duyệt (pending)
$result = $conn->query("SELECT COUNT(*) as total FROM tin_td WHERE trang_thai_tin = 0");
$stats['pending_jobs'] = $result->fetch_assoc()['total'];

// Số lượng hồ sơ ứng tuyển
$result = $conn->query("SELECT COUNT(*) as total FROM ung_tuyens");
$stats['total_applications'] = $result->fetch_assoc()['total'];

// Dữ liệu cho biểu đồ tin tuyển dụng theo tháng (6 tháng gần nhất)
$jobs_chart_data = [
    'labels' => [],
    'active' => [],
    'pending' => [],
    'inactive' => []
];

for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $label = date('m/Y', strtotime("-$i months"));
    $jobs_chart_data['labels'][] = $label;

    // Active jobs
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tin_td WHERE DATE_FORMAT(dang_luc, '%Y-%m') = ? AND trang_thai_tin = 1");
    $stmt->bind_param("s", $month);
    $stmt->execute();
    $jobs_chart_data['active'][] = $stmt->get_result()->fetch_assoc()['count'];

    // Pending jobs
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tin_td WHERE DATE_FORMAT(dang_luc, '%Y-%m') = ? AND trang_thai_tin = 0");
    $stmt->bind_param("s", $month);
    $stmt->execute();
    $jobs_chart_data['pending'][] = $stmt->get_result()->fetch_assoc()['count'];

    // Inactive jobs
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tin_td WHERE DATE_FORMAT(dang_luc, '%Y-%m') = ? AND trang_thai_tin = 2");
    $stmt->bind_param("s", $month);
    $stmt->execute();
    $jobs_chart_data['inactive'][] = $stmt->get_result()->fetch_assoc()['count'];
}

// Dữ liệu cho biểu đồ ứng tuyển theo tháng (6 tháng gần nhất)
$applications_chart_data = [
    'labels' => [],
    'values' => []
];

for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $label = date('m/Y', strtotime("-$i months"));
    $applications_chart_data['labels'][] = $label;

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM ung_tuyens WHERE DATE_FORMAT(nop_luc, '%Y-%m') = ?");
    $stmt->bind_param("s", $month);
    $stmt->execute();
    $applications_chart_data['values'][] = $stmt->get_result()->fetch_assoc()['count'];
}
?>




