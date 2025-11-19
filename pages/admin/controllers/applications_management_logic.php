<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/functions.php';

// Check if user is admin
// Hỗ trợ cả $_SESSION['vai_tro'] và $_SESSION['vai_tro_id']
$vai_tro = isset($_SESSION['vai_tro_id']) ? $_SESSION['vai_tro_id'] : (isset($_SESSION['vai_tro']) ? $_SESSION['vai_tro'] : 0);
if (!isset($_SESSION['user_id']) || $vai_tro != ROLE_ADMIN) {
    header('Location: ' . publicRoute('login'));
    exit();
}

// Lấy thông tin admin
$stmt = $conn->prepare("SELECT a.ho_ten, tk.ten_dn FROM admins a JOIN tai_khoans tk ON a.tai_khoan_id = tk.tai_khoan_id WHERE a.tai_khoan_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

// Handle POST requests
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                $ung_tuyen_id = (int)$_POST['ung_tuyen_id'];
                $new_status = (int)$_POST['trang_thai'];
                $ghi_chu = trim($_POST['ghi_chu'] ?? '');

                // Get current status for history
                $stmt = $conn->prepare("SELECT trang_thai_ut FROM ung_tuyens WHERE ung_tuyen_id = ?");
                $stmt->bind_param("i", $ung_tuyen_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $current_status = $result->fetch_assoc()['trang_thai_ut'];
                $stmt->close();

                // Update status
                $stmt = $conn->prepare("UPDATE ung_tuyens SET trang_thai_ut = ? WHERE ung_tuyen_id = ?");
                $stmt->bind_param("ii", $new_status, $ung_tuyen_id);

                if ($stmt->execute()) {
                    // Add to status history if table exists
                    $history_query = "INSERT INTO lich_su_trang_thai_ut (ung_tuyen_id, tu_trang_thai, sang_trang_thai, id_nguoi_cap_nhat, ghi_chu, ngay_cap_nhat)
                                    VALUES (?, ?, ?, ?, ?, NOW())";
                    $stmt_history = $conn->prepare($history_query);
                    if ($stmt_history) {
                        $stmt_history->bind_param("iiiis", $ung_tuyen_id, $current_status, $new_status, $_SESSION['id'], $ghi_chu);
                        $stmt_history->execute();
                        $stmt_history->close();
                    }

                    $message = 'Cập nhật trạng thái ứng tuyển thành công!';
                    $messageType = 'success';
                } else {
                    $message = 'Lỗi khi cập nhật trạng thái: ' . $conn->error;
                    $messageType = 'danger';
                }
                $stmt->close();
                break;

            case 'add_note':
                $ung_tuyen_id = (int)$_POST['ung_tuyen_id'];
                $ghi_chu = trim($_POST['ghi_chu']);

                if (!empty($ghi_chu)) {
                    // Get current status
                    $stmt = $conn->prepare("SELECT trang_thai_ut FROM ung_tuyens WHERE ung_tuyen_id = ?");
                    $stmt->bind_param("i", $ung_tuyen_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $current_status = $result->fetch_assoc()['trang_thai_ut'];
                    $stmt->close();

                    // Add note to history
                    $history_query = "INSERT INTO lich_su_trang_thai_ut (ung_tuyen_id, tu_trang_thai, sang_trang_thai, id_nguoi_cap_nhat, ghi_chu, ngay_cap_nhat)
                                    VALUES (?, ?, ?, ?, ?, NOW())";
                    $stmt_history = $conn->prepare($history_query);
                    if ($stmt_history) {
                        $stmt_history->bind_param("iiiis", $ung_tuyen_id, $current_status, $current_status, $_SESSION['id'], $ghi_chu);
                        $stmt_history->execute();
                        $stmt_history->close();
                    }

                    $message = 'Thêm ghi chú thành công!';
                    $messageType = 'success';
                }
                break;
        }
    }
}

// Status labels definition
$status_labels = [
    0 => ['label' => 'Chưa xử lý', 'class' => 'secondary'],
    1 => ['label' => 'Chờ duyệt', 'class' => 'warning'],
    2 => ['label' => 'Đã duyệt', 'class' => 'info'],
    3 => ['label' => 'Phỏng vấn', 'class' => 'primary'],
    4 => ['label' => 'Đỗ', 'class' => 'success'],
    5 => ['label' => 'Trượt', 'class' => 'danger'],
    6 => ['label' => 'Hủy', 'class' => 'secondary'],
    7 => ['label' => 'Chờ bổ sung', 'class' => 'warning'],
    8 => ['label' => 'Đã bổ sung', 'class' => 'info']
];

// Get filter parameters
$filter_type = $_GET['filter_type'] ?? '';
$filter_id = (int)($_GET['filter_id'] ?? 0);

// Build query with filters - full JOIN for complete data
$query = "SELECT ut.ung_tuyen_id, ut.tin_id, ut.ung_vien_id, ut.cv_id, ut.trang_thai_ut, ut.nop_luc,
                 td.tieu_de as job_title,
                 td.luong_min, td.luong_max, td.tien_te,
                 uv.ho_ten as candidate_name,
                 c.ten_cong_ty as company_name,
                 cv.ten_tep as cv_title,
                 cv.tep_url as cv_url
          FROM ung_tuyens ut
          LEFT JOIN tin_td td ON ut.tin_id = td.tin_id
          LEFT JOIN ung_viens uv ON ut.ung_vien_id = uv.ung_vien_id
          LEFT JOIN cong_tys c ON td.cong_ty_id = c.cong_ty_id
          LEFT JOIN dinh_kems cv ON ut.cv_id = cv.dk_id
          WHERE 1=1";

$params = [];
$param_types = "";

// Add filters
if ($filter_type === 'job' && $filter_id > 0) {
    $query .= " AND ut.tin_id = ?";
    $params[] = $filter_id;
    $param_types .= "i";
}

if ($filter_type === 'candidate' && $filter_id > 0) {
    $query .= " AND ut.ung_vien_id = ?";
    $params[] = $filter_id;
    $param_types .= "i";
}

$query .= " ORDER BY ut.nop_luc DESC";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
if (!empty($param_types) && !empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$applications = [];

while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}
$stmt->close();

// Get statistics
$total_applications = count($applications);
$status_counts = array_fill(0, 9, 0); // 1-8 status

foreach ($applications as $app) {
    if ($app['trang_thai_ut'] >= 1 && $app['trang_thai_ut'] <= 8) {
        $status_counts[$app['trang_thai_ut']]++;
    }
}

// Get recent applications (last 7 days)
$recent_query = "SELECT COUNT(*) as count FROM ung_tuyens WHERE nop_luc >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$recent_result = $conn->query($recent_query);
if ($recent_result) {
    $recent_count = $recent_result->fetch_assoc()['count'];
    $recent_result->free();
} else {
    $recent_count = 0;
}

// Get jobs with applications for filter dropdown
$jobs_query = "SELECT DISTINCT td.tin_id, td.tieu_de, COUNT(ut.ung_tuyen_id) as app_count
               FROM tin_td td
               LEFT JOIN ung_tuyens ut ON td.tin_id = ut.tin_id
               GROUP BY td.tin_id, td.tieu_de
               HAVING app_count > 0
               ORDER BY td.tieu_de";
$jobs_result = $conn->query($jobs_query);
$jobs_with_apps = [];
if ($jobs_result) {
    while ($row = $jobs_result->fetch_assoc()) {
        $jobs_with_apps[] = $row;
    }
    $jobs_result->free();
}

// Get candidates with applications for filter dropdown
$candidates_query = "SELECT DISTINCT uv.ung_vien_id, uv.ho_ten, COUNT(ut.ung_tuyen_id) as app_count
                     FROM ung_viens uv
                     LEFT JOIN ung_tuyens ut ON uv.ung_vien_id = ut.ung_vien_id
                     GROUP BY uv.ung_vien_id, uv.ho_ten
                     HAVING app_count > 0
                     ORDER BY uv.ho_ten";
$candidates_result = $conn->query($candidates_query);
$candidates_with_apps = [];
if ($candidates_result) {
    while ($row = $candidates_result->fetch_assoc()) {
        $candidates_with_apps[] = $row;
    }
    $candidates_result->free();
}

$stats = [
    'total' => $total_applications,
    'recent' => $recent_count,
    'by_status' => $status_counts
];

// Don't close connection - it will be used by the view
// Connection will be closed at the end of the page
?>