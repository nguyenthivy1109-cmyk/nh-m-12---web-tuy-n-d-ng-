<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/functions.php';

// Kiểm tra đăng nhập và vai trò admin
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

// Xử lý các action
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    $action = $_POST['action'] ?? $_GET['action'];
    $accountId = $_POST['account_id'] ?? $_GET['account_id'];

    switch ($action) {
        case 'change_role':
            $newRole = $_POST['new_role'] ?? $_GET['new_role'];
            if ($newRole && in_array($newRole, [1, 2, 3])) {
                $stmt = $conn->prepare("UPDATE tai_khoans SET vai_tro_id = ? WHERE tai_khoan_id = ?");
                $stmt->bind_param("ii", $newRole, $accountId);
                if ($stmt->execute()) {
                    $message = "Đã thay đổi vai trò thành công!";
                    $messageType = "success";
                } else {
                    $message = "Lỗi khi thay đổi vai trò!";
                    $messageType = "danger";
                }
            }
            break;

        case 'lock':
            $stmt = $conn->prepare("UPDATE tai_khoans SET kich_hoat = 0 WHERE tai_khoan_id = ?");
            $stmt->bind_param("i", $accountId);
            if ($stmt->execute()) {
                $message = "Đã khóa tài khoản thành công!";
                $messageType = "success";
            } else {
                $message = "Lỗi khi khóa tài khoản!";
                $messageType = "danger";
            }
            break;

        case 'unlock':
            $stmt = $conn->prepare("UPDATE tai_khoans SET kich_hoat = 1 WHERE tai_khoan_id = ?");
            $stmt->bind_param("i", $accountId);
            if ($stmt->execute()) {
                $message = "Đã mở khóa tài khoản thành công!";
                $messageType = "success";
            } else {
                $message = "Lỗi khi mở khóa tài khoản!";
                $messageType = "danger";
            }
            break;
    }
}

// Lấy danh sách tài khoản với thông tin chi tiết
$query = "
    SELECT
        tk.tai_khoan_id,
        tk.ten_dn,
        tk.email,
        tk.vai_tro_id,
        CASE
            WHEN tk.vai_tro_id = 1 THEN 'Admin'
            WHEN tk.vai_tro_id = 2 THEN 'Nhà tuyển dụng'
            WHEN tk.vai_tro_id = 3 THEN 'Ứng viên'
            ELSE 'Không xác định'
        END as ten_vai_tro,
        tk.kich_hoat,
        tk.tao_luc as ngay_tao,
        tk.dang_nhap_cuoi_luc as lan_dang_nhap_cuoi,
        CASE
            WHEN tk.vai_tro_id = 1 THEN a.ho_ten
            WHEN tk.vai_tro_id = 2 THEN ntd.ho_ten
            WHEN tk.vai_tro_id = 3 THEN uv.ho_ten
            ELSE tk.ten_dn
        END as ho_ten
    FROM tai_khoans tk
    LEFT JOIN admins a ON tk.tai_khoan_id = a.tai_khoan_id
    LEFT JOIN nha_tuyen_dungs ntd ON tk.tai_khoan_id = ntd.tai_khoan_id
    LEFT JOIN ung_viens uv ON tk.tai_khoan_id = uv.tai_khoan_id
    WHERE tk.xoa_luc IS NULL
    ORDER BY tk.tao_luc DESC
";

$result = $conn->query($query);
$accounts = [];
if ($result && $result->num_rows > 0) {
    $accounts = $result->fetch_all(MYSQLI_ASSOC);
} elseif ($result === false) {
    // Log error nếu query lỗi
    error_log("Query error: " . $conn->error);
}

// Thống kê tài khoản
$stats = [
    'total' => count($accounts),
    'active' => 0,
    'locked' => 0,
    'admins' => 0,
    'recruiters' => 0,
    'candidates' => 0
];

foreach ($accounts as $account) {
    if ($account['kich_hoat'] == 1) {
        $stats['active']++;
    } else {
        $stats['locked']++;
    }

    switch ($account['vai_tro_id']) {
        case 1:
            $stats['admins']++;
            break;
        case 2:
            $stats['recruiters']++;
            break;
        case 3:
            $stats['candidates']++;
            break;
    }
}
?>

