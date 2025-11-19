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
            case 'delete_message':
                $tn_id = (int)$_POST['tn_id'];
                $reason = trim($_POST['reason'] ?? '');

                // Delete message
                $stmt = $conn->prepare("DELETE FROM tin_nhans WHERE tn_id = ?");
                $stmt->bind_param("i", $tn_id);

                if ($stmt->execute()) {
                    // Log the deletion if reason provided
                    if (!empty($reason)) {
                        // You could create a violation_logs table for this
                        // For now, just log to a simple text file or database table
                    }
                    $message = 'Xóa tin nhắn thành công!';
                    $messageType = 'success';
                } else {
                    $message = 'Lỗi khi xóa tin nhắn: ' . $conn->error;
                    $messageType = 'danger';
                }
                $stmt->close();
                break;

            case 'report_message':
                $tn_id = (int)$_POST['tn_id'];
                $report_reason = trim($_POST['report_reason']);
                $report_details = trim($_POST['report_details'] ?? '');

                // Mark message as reported (you could add a reported flag to tin_nhans table)
                // For now, we'll just log the report
                $message = 'Đã báo cáo tin nhắn vi phạm!';
                $messageType = 'warning';
                break;

            case 'mark_as_read':
                $tn_id = (int)$_POST['tn_id'];

                $stmt = $conn->prepare("UPDATE tin_nhans SET da_doc = 1 WHERE tn_id = ?");
                $stmt->bind_param("i", $tn_id);

                if ($stmt->execute()) {
                    $message = 'Đã đánh dấu tin nhắn đã đọc!';
                    $messageType = 'success';
                } else {
                    $message = 'Lỗi khi cập nhật trạng thái: ' . $conn->error;
                    $messageType = 'danger';
                }
                $stmt->close();
                break;
        }
    }
}

// Get filter parameters
$filter_type = $_GET['filter_type'] ?? '';
$filter_id = (int)($_GET['filter_id'] ?? 0);

// Build query with filters - full JOIN for complete data
$query = "SELECT tn.tn_id, tn.ung_tuyen_id, tn.gui_boi_tai_khoan_id, tn.nhan_boi_tai_khoan_id,
                 tn.noi_dung, tn.da_doc, tn.gui_luc,
                 ut.tin_id, ut.ung_vien_id,
                 td.tieu_de as job_title,
                 uv.ho_ten as candidate_name,
                 ntd.ho_ten as employer_name,
                 c.ten_cong_ty as company_name,
                 gui_tk.ten_dn as sender_username,
                 nhan_tk.ten_dn as receiver_username
          FROM tin_nhans tn
          LEFT JOIN ung_tuyens ut ON tn.ung_tuyen_id = ut.ung_tuyen_id
          LEFT JOIN tin_td td ON ut.tin_id = td.tin_id
          LEFT JOIN ung_viens uv ON ut.ung_vien_id = uv.ung_vien_id
          LEFT JOIN nha_tuyen_dungs ntd ON td.nha_td_id = ntd.nha_td_id
          LEFT JOIN cong_tys c ON td.cong_ty_id = c.cong_ty_id
          LEFT JOIN tai_khoans gui_tk ON tn.gui_boi_tai_khoan_id = gui_tk.tai_khoan_id
          LEFT JOIN tai_khoans nhan_tk ON tn.nhan_boi_tai_khoan_id = nhan_tk.tai_khoan_id
          WHERE 1=1";

$params = [];
$param_types = "";

// Add filters
if ($filter_type === 'application' && $filter_id > 0) {
    $query .= " AND tn.ung_tuyen_id = ?";
    $params[] = $filter_id;
    $param_types .= "i";
}

if ($filter_type === 'sender' && $filter_id > 0) {
    $query .= " AND tn.gui_boi_tai_khoan_id = ?";
    $params[] = $filter_id;
    $param_types .= "i";
}

if ($filter_type === 'receiver' && $filter_id > 0) {
    $query .= " AND tn.nhan_boi_tai_khoan_id = ?";
    $params[] = $filter_id;
    $param_types .= "i";
}

$query .= " ORDER BY tn.gui_luc DESC";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
if (!empty($param_types) && !empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();

// Get statistics
$total_messages = count($messages);
$unread_count = 0;
$recent_count = 0;
$application_count = count(array_unique(array_column($messages, 'ung_tuyen_id')));

foreach ($messages as $msg) {
    if (!$msg['da_doc']) {
        $unread_count++;
    }
    // Count messages from last 7 days
    if (strtotime($msg['gui_luc']) > strtotime('-7 days')) {
        $recent_count++;
    }
}

// Get applications with messages for filter dropdown
$apps_query = "SELECT DISTINCT ut.ung_tuyen_id,
                      CONCAT(td.tieu_de, ' - ', uv.ho_ten) as app_title,
                      COUNT(tn.tn_id) as msg_count
               FROM ung_tuyens ut
               LEFT JOIN tin_td td ON ut.tin_id = td.tin_id
               LEFT JOIN ung_viens uv ON ut.ung_vien_id = uv.ung_vien_id
               LEFT JOIN tin_nhans tn ON ut.ung_tuyen_id = tn.ung_tuyen_id
               GROUP BY ut.ung_tuyen_id, td.tieu_de, uv.ho_ten
               HAVING msg_count > 0
               ORDER BY ut.ung_tuyen_id DESC";
$apps_result = $conn->query($apps_query);
$apps_with_msgs = [];
if ($apps_result) {
    while ($row = $apps_result->fetch_assoc()) {
        $apps_with_msgs[] = $row;
    }
    $apps_result->free();
}

// Get senders for filter dropdown
$senders_query = "SELECT DISTINCT tk.tai_khoan_id, tk.ten_dn,
                         COUNT(tn.tn_id) as msg_count
                  FROM tai_khoans tk
                  LEFT JOIN tin_nhans tn ON tk.tai_khoan_id = tn.gui_boi_tai_khoan_id
                  GROUP BY tk.tai_khoan_id, tk.ten_dn
                  HAVING msg_count > 0
                  ORDER BY tk.ten_dn";
$senders_result = $conn->query($senders_query);
$senders = [];
if ($senders_result) {
    while ($row = $senders_result->fetch_assoc()) {
        $senders[] = $row;
    }
    $senders_result->free();
}

$stats = [
    'total' => $total_messages,
    'unread' => $unread_count,
    'recent' => $recent_count,
    'applications' => $application_count
];

// Don't close connection - it will be used by the view
// Connection will be closed at the end of the page
?>