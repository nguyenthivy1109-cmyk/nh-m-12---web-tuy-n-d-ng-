<?php
// Recruiter Management Logic
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
        case 'update_recruiter':
            $nha_td_id = (int)$_POST['nha_td_id'];
            $ho_ten = trim($_POST['ho_ten']);
            $chuc_danh = trim($_POST['chuc_danh']);
            $email_cong_viec = trim($_POST['email_cong_viec']);
            $cong_ty_id = !empty($_POST['cong_ty_id']) ? (int)$_POST['cong_ty_id'] : null;

            if (empty($ho_ten)) {
                $message = 'Họ tên là bắt buộc';
                $messageType = 'danger';
                break;
            }

            if (!empty($email_cong_viec) && !filter_var($email_cong_viec, FILTER_VALIDATE_EMAIL)) {
                $message = 'Email công việc không hợp lệ';
                $messageType = 'danger';
                break;
            }

            $stmt = $conn->prepare("UPDATE nha_tuyen_dungs SET ho_ten = ?, chuc_danh = ?, email_cong_viec = ?, cong_ty_id = ? WHERE nha_td_id = ?");
            $stmt->bind_param("sssii", $ho_ten, $chuc_danh, $email_cong_viec, $cong_ty_id, $nha_td_id);
            
            if ($stmt->execute()) {
                $message = 'Cập nhật nhà tuyển dụng thành công';
            } else {
                $message = 'Lỗi: ' . $conn->error;
                $messageType = 'danger';
            }
            break;

        case 'toggle_account':
            $tai_khoan_id = (int)$_POST['tai_khoan_id'];
            
            // Get current status
            $stmt = $conn->prepare("SELECT kich_hoat FROM tai_khoans WHERE tai_khoan_id = ?");
            $stmt->bind_param("i", $tai_khoan_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $current_status = 0;
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $current_status = $row['kich_hoat'] ?? 0;
            }
            
            // Toggle status
            $new_status = $current_status ? 0 : 1;
            $stmt = $conn->prepare("UPDATE tai_khoans SET kich_hoat = ? WHERE tai_khoan_id = ?");
            $stmt->bind_param("ii", $new_status, $tai_khoan_id);
            
            if ($stmt->execute()) {
                $message = $new_status ? 'Đã mở khóa tài khoản' : 'Đã khóa tài khoản';
            } else {
                $message = 'Lỗi: ' . $conn->error;
                $messageType = 'danger';
            }
            break;

        case 'delete_recruiter':
            $nha_td_id = (int)$_POST['nha_td_id'];

            // Get tai_khoan_id
            $stmt = $conn->prepare("SELECT tai_khoan_id FROM nha_tuyen_dungs WHERE nha_td_id = ?");
            $stmt->bind_param("i", $nha_td_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $tai_khoan_id = null;
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $tai_khoan_id = $row['tai_khoan_id'] ?? null;
            }

            if ($tai_khoan_id) {
                // Soft delete - set deleted timestamp in tai_khoans
                $stmt = $conn->prepare("UPDATE tai_khoans SET xoa_luc = NOW() WHERE tai_khoan_id = ?");
                $stmt->bind_param("i", $tai_khoan_id);
                
                if ($stmt->execute()) {
                    $message = 'Đã xóa nhà tuyển dụng';
                } else {
                    $message = 'Lỗi: ' . $conn->error;
                    $messageType = 'danger';
                }
            }
            break;

        case 'restore_recruiter':
            $tai_khoan_id = (int)$_POST['tai_khoan_id'];

            $stmt = $conn->prepare("UPDATE tai_khoans SET xoa_luc = NULL WHERE tai_khoan_id = ?");
            $stmt->bind_param("i", $tai_khoan_id);
            
            if ($stmt->execute()) {
                $message = 'Đã khôi phục nhà tuyển dụng';
            } else {
                $message = 'Lỗi: ' . $conn->error;
                $messageType = 'danger';
            }
            break;
    }
}

// Get filter parameters
$show_deleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] === '1';
$company_filter = isset($_GET['company']) ? (int)$_GET['company'] : null;
$status_filter = $_GET['status'] ?? 'all';

// Build query conditions
$conditions = [];
$bind_types = '';
$bind_values = [];

if (!$show_deleted) {
    $conditions[] = "tk.xoa_luc IS NULL";
}

if ($company_filter) {
    $conditions[] = "nt.cong_ty_id = ?";
    $bind_types .= 'i';
    $bind_values[] = $company_filter;
}

if ($status_filter !== 'all') {
    if ($status_filter === 'active') {
        $conditions[] = "tk.kich_hoat = 1";
    } else if ($status_filter === 'locked') {
        $conditions[] = "tk.kich_hoat = 0";
    }
}

$whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// Get recruiters with company and account info
$query = "
    SELECT nt.*, tk.ten_dn, tk.email, tk.dien_thoai, tk.kich_hoat, tk.xoa_luc,
           c.ten_cong_ty, c.logo_url as company_logo,
           COUNT(t.tin_id) as total_jobs
    FROM nha_tuyen_dungs nt
    INNER JOIN tai_khoans tk ON nt.tai_khoan_id = tk.tai_khoan_id
    LEFT JOIN cong_tys c ON nt.cong_ty_id = c.cong_ty_id
    LEFT JOIN tin_td t ON nt.nha_td_id = t.nha_td_id
    $whereClause
    GROUP BY nt.nha_td_id, tk.ten_dn, tk.email, tk.dien_thoai, tk.kich_hoat, tk.xoa_luc,
             c.ten_cong_ty, c.logo_url
    ORDER BY nt.tao_luc DESC
";

if (!empty($bind_values)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($bind_types, ...$bind_values);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}

$recruiters = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recruiters[] = $row;
    }
}

// Get statistics
$stats = [
    'total_active' => 0,
    'total_locked' => 0,
    'total_deleted' => 0,
    'linked_companies' => 0,
    'unlinked' => 0
];

foreach ($recruiters as $recruiter) {
    if ($recruiter['xoa_luc']) {
        $stats['total_deleted']++;
    } elseif (!$recruiter['kich_hoat']) {
        $stats['total_locked']++;
    } else {
        $stats['total_active']++;
    }

    if ($recruiter['cong_ty_id']) {
        $stats['linked_companies']++;
    } else {
        $stats['unlinked']++;
    }
}

// Get companies for dropdown
$companies_result = $conn->query("
    SELECT cong_ty_id, ten_cong_ty
    FROM cong_tys
    WHERE xoa_luc IS NULL
    ORDER BY ten_cong_ty
");

$companies = [];
if ($companies_result && $companies_result->num_rows > 0) {
    while ($row = $companies_result->fetch_assoc()) {
        $companies[] = $row;
    }
}
?>

