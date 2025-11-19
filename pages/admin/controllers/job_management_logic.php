<?php
// Job Management Logic
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../includes/db.php';

// Check if user is logged in and is admin
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

$message = '';
$messageType = 'success';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'approve_job':
            $tin_id = (int)$_POST['tin_id'];
            
            $stmt = $conn->prepare("UPDATE tin_td SET trang_thai_tin = 1, dang_luc = NOW() WHERE tin_id = ?");
            $stmt->bind_param("i", $tin_id);
            
            if ($stmt->execute()) {
                $message = 'Đã duyệt tin tuyển dụng';
            } else {
                $message = 'Lỗi: ' . $conn->error;
                $messageType = 'danger';
            }
            break;

        case 'reject_job':
            $tin_id = (int)$_POST['tin_id'];
            $ly_do = trim($_POST['ly_do'] ?? '');
            
            // Set status to rejected (0) and add note
            $stmt = $conn->prepare("UPDATE tin_td SET trang_thai_tin = 0 WHERE tin_id = ?");
            $stmt->bind_param("i", $tin_id);
            
            if ($stmt->execute()) {
                $message = 'Đã từ chối tin tuyển dụng';
            } else {
                $message = 'Lỗi: ' . $conn->error;
                $messageType = 'danger';
            }
            break;

        case 'lock_job':
            $tin_id = (int)$_POST['tin_id'];
            
            $stmt = $conn->prepare("UPDATE tin_td SET trang_thai_tin = 3 WHERE tin_id = ?");
            $stmt->bind_param("i", $tin_id);
            
            if ($stmt->execute()) {
                $message = 'Đã khóa tin tuyển dụng';
            } else {
                $message = 'Lỗi: ' . $conn->error;
                $messageType = 'danger';
            }
            break;

        case 'unlock_job':
            $tin_id = (int)$_POST['tin_id'];
            
            $stmt = $conn->prepare("UPDATE tin_td SET trang_thai_tin = 1 WHERE tin_id = ?");
            $stmt->bind_param("i", $tin_id);
            
            if ($stmt->execute()) {
                $message = 'Đã mở khóa tin tuyển dụng';
            } else {
                $message = 'Lỗi: ' . $conn->error;
                $messageType = 'danger';
            }
            break;

        case 'delete_job':
            $tin_id = (int)$_POST['tin_id'];
            
            $stmt = $conn->prepare("UPDATE tin_td SET xoa_luc = NOW() WHERE tin_id = ?");
            $stmt->bind_param("i", $tin_id);
            
            if ($stmt->execute()) {
                $message = 'Đã xóa tin tuyển dụng';
            } else {
                $message = 'Lỗi: ' . $conn->error;
                $messageType = 'danger';
            }
            break;

        case 'update_job':
            $tin_id = (int)$_POST['tin_id'];
            $tieu_de = trim($_POST['tieu_de']);
            $mo_ta = trim($_POST['mo_ta']);
            $yeu_cau = trim($_POST['yeu_cau']);
            $noi_lam_viec = trim($_POST['noi_lam_viec']);
            $luong_min = !empty($_POST['luong_min']) ? (float)$_POST['luong_min'] : null;
            $luong_max = !empty($_POST['luong_max']) ? (float)$_POST['luong_max'] : null;
            $so_luong = (int)$_POST['so_luong'];
            $het_han_luc = !empty($_POST['het_han_luc']) ? $_POST['het_han_luc'] : null;

            if (empty($tieu_de)) {
                $message = 'Tiêu đề là bắt buộc';
                $messageType = 'danger';
                break;
            }

            $stmt = $conn->prepare("UPDATE tin_td SET tieu_de = ?, mo_ta = ?, yeu_cau = ?, noi_lam_viec = ?, luong_min = ?, luong_max = ?, so_luong = ?, het_han_luc = ? WHERE tin_id = ?");
            $stmt->bind_param("ssssddisl", $tieu_de, $mo_ta, $yeu_cau, $noi_lam_viec, $luong_min, $luong_max, $so_luong, $het_han_luc, $tin_id);
            
            if ($stmt->execute()) {
                $message = 'Đã cập nhật tin tuyển dụng';
            } else {
                $message = 'Lỗi: ' . $conn->error;
                $messageType = 'danger';
            }
            break;
    }
}

// Get filter parameters
$company_filter = isset($_GET['company']) ? (int)$_GET['company'] : null;
$recruiter_filter = isset($_GET['recruiter']) ? (int)$_GET['recruiter'] : null;
$status_filter = isset($_GET['status']) ? (int)$_GET['status'] : null;
$show_deleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] === '1';

// Build query conditions
$conditions = [];
$bind_types = '';
$bind_values = [];

if (!$show_deleted) {
    $conditions[] = "t.xoa_luc IS NULL";
}

if ($company_filter) {
    $conditions[] = "t.cong_ty_id = ?";
    $bind_types .= 'i';
    $bind_values[] = $company_filter;
}

if ($recruiter_filter) {
    $conditions[] = "t.nha_td_id = ?";
    $bind_types .= 'i';
    $bind_values[] = $recruiter_filter;
}

if ($status_filter !== null) {
    $conditions[] = "t.trang_thai_tin = ?";
    $bind_types .= 'i';
    $bind_values[] = $status_filter;
}

$whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// Get jobs with company and recruiter info
$query = "
    SELECT t.*, 
           c.ten_cong_ty, c.logo_url,
           nt.ho_ten as recruiter_name,
           tk.email as recruiter_email,
           COUNT(DISTINCT u.ung_tuyen_id) as total_applications
    FROM tin_td t
    INNER JOIN cong_tys c ON t.cong_ty_id = c.cong_ty_id
    INNER JOIN nha_tuyen_dungs nt ON t.nha_td_id = nt.nha_td_id
    INNER JOIN tai_khoans tk ON nt.tai_khoan_id = tk.tai_khoan_id
    LEFT JOIN ung_tuyens u ON t.tin_id = u.tin_id
    $whereClause
    GROUP BY t.tin_id, c.ten_cong_ty, c.logo_url, nt.ho_ten, tk.email
    ORDER BY t.tao_luc DESC
";

if (!empty($bind_values)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($bind_types, ...$bind_values);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}

$jobs = [];
while ($row = $result->fetch_assoc()) {
    $jobs[] = $row;
}

// Get statistics
$stats = [
    'total' => 0,
    'pending' => 0,
    'approved' => 0,
    'expired' => 0,
    'locked' => 0
];

$stats_query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN trang_thai_tin = 0 THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN trang_thai_tin = 1 THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN trang_thai_tin = 2 THEN 1 ELSE 0 END) as expired,
        SUM(CASE WHEN trang_thai_tin = 3 THEN 1 ELSE 0 END) as locked
    FROM tin_td
    WHERE xoa_luc IS NULL
";

$stats_result = $conn->query($stats_query);
$stats = $stats_result && $stats_result->num_rows > 0 ? $stats_result->fetch_assoc() : [];

// Get companies for dropdown
$companies_result = $conn->query("
    SELECT cong_ty_id, ten_cong_ty
    FROM cong_tys
    WHERE trang_thai = 1 AND xoa_luc IS NULL
    ORDER BY ten_cong_ty
");

$companies = [];
if ($companies_result && $companies_result->num_rows > 0) {
    while ($row = $companies_result->fetch_assoc()) {
        $companies[] = $row;
    }
}

// Get recruiters for dropdown
$recruiters_result = $conn->query("
    SELECT nt.nha_td_id, nt.ho_ten, c.ten_cong_ty
    FROM nha_tuyen_dungs nt
    INNER JOIN tai_khoans tk ON nt.tai_khoan_id = tk.tai_khoan_id
    LEFT JOIN cong_tys c ON nt.cong_ty_id = c.cong_ty_id
    WHERE tk.xoa_luc IS NULL AND tk.kich_hoat = 1
    ORDER BY nt.ho_ten
");

$recruiters = [];
if ($recruiters_result && $recruiters_result->num_rows > 0) {
    while ($row = $recruiters_result->fetch_assoc()) {
        $recruiters[] = $row;
    }
}
?>
