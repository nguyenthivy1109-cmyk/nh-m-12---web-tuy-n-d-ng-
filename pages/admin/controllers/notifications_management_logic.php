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
            case 'create_notification':
                $target_audience = $_POST['target_audience'];
                $notification_title = trim($_POST['tieu_de']);
                $notification_content = trim($_POST['noi_dung']);
                $notification_type = (int)$_POST['loai_tb'];

                if (empty($notification_title)) {
                    $message = 'Tiêu đề thông báo không được để trống!';
                    $messageType = 'danger';
                    break;
                }

                // Get target users based on audience type
                $target_users = [];
                switch ($target_audience) {
                    case 'all':
                        $result = $conn->query("SELECT tai_khoan_id FROM tai_khoans WHERE kich_hoat = 1");
                        while ($row = $result->fetch_assoc()) {
                            $target_users[] = $row['tai_khoan_id'];
                        }
                        $result->free();
                        break;
                    case 'candidates':
                        $result = $conn->query("SELECT tk.tai_khoan_id FROM tai_khoans tk JOIN ung_viens uv ON tk.tai_khoan_id = uv.tai_khoan_id WHERE tk.kich_hoat = 1");
                        while ($row = $result->fetch_assoc()) {
                            $target_users[] = $row['tai_khoan_id'];
                        }
                        $result->free();
                        break;
                    case 'recruiters':
                        $result = $conn->query("SELECT tk.tai_khoan_id FROM tai_khoans tk JOIN nha_tuyen_dungs ntd ON tk.tai_khoan_id = ntd.tai_khoan_id WHERE tk.kich_hoat = 1");
                        while ($row = $result->fetch_assoc()) {
                            $target_users[] = $row['tai_khoan_id'];
                        }
                        $result->free();
                        break;
                }

                // Insert notifications for each target user
                $stmt = $conn->prepare("INSERT INTO thong_baos (tai_khoan_id, loai_tb, tieu_de, noi_dung, da_doc) VALUES (?, ?, ?, ?, 0)");
                $inserted_count = 0;

                foreach ($target_users as $user_id) {
                    $stmt->bind_param("iiss", $user_id, $notification_type, $notification_title, $notification_content);
                    if ($stmt->execute()) {
                        $inserted_count++;
                    }
                }
                $stmt->close();

                $message = "Đã tạo thông báo thành công cho $inserted_count người dùng!";
                $messageType = 'success';
                break;

            case 'mark_as_read':
                $tb_id = (int)$_POST['tb_id'];

                $stmt = $conn->prepare("UPDATE thong_baos SET da_doc = 1 WHERE tb_id = ?");
                $stmt->bind_param("i", $tb_id);

                if ($stmt->execute()) {
                    $message = 'Đã đánh dấu thông báo đã đọc!';
                    $messageType = 'success';
                } else {
                    $message = 'Lỗi khi cập nhật trạng thái: ' . $conn->error;
                    $messageType = 'danger';
                }
                $stmt->close();
                break;

            case 'mark_as_unread':
                $tb_id = (int)$_POST['tb_id'];

                $stmt = $conn->prepare("UPDATE thong_baos SET da_doc = 0 WHERE tb_id = ?");
                $stmt->bind_param("i", $tb_id);

                if ($stmt->execute()) {
                    $message = 'Đã đánh dấu thông báo chưa đọc!';
                    $messageType = 'success';
                } else {
                    $message = 'Lỗi khi cập nhật trạng thái: ' . $conn->error;
                    $messageType = 'danger';
                }
                $stmt->close();
                break;

            case 'delete_notification':
                $tb_id = (int)$_POST['tb_id'];

                $stmt = $conn->prepare("DELETE FROM thong_baos WHERE tb_id = ?");
                $stmt->bind_param("i", $tb_id);

                if ($stmt->execute()) {
                    $message = 'Đã xóa thông báo thành công!';
                    $messageType = 'success';
                } else {
                    $message = 'Lỗi khi xóa thông báo: ' . $conn->error;
                    $messageType = 'danger';
                }
                $stmt->close();
                break;
        }
    }
}

// Notification types
$notification_types = [
    1 => ['label' => 'Thông báo hệ thống', 'class' => 'primary'],
    2 => ['label' => 'Cập nhật chính sách', 'class' => 'info'],
    3 => ['label' => 'Thông báo bảo trì', 'class' => 'warning'],
    4 => ['label' => 'Thông báo quan trọng', 'class' => 'danger'],
    5 => ['label' => 'Khuyến mãi', 'class' => 'success']
];

// Get filter parameters
$filter_type = $_GET['filter_type'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';
$filter_user = (int)($_GET['filter_user'] ?? 0);

// Build query with filters
$query = "SELECT tb.tb_id, tb.tai_khoan_id, tb.loai_tb, tb.tieu_de, tb.noi_dung, tb.da_doc, tb.tao_luc,
                 tk.ten_dn, tk.vai_tro_id,
                 CASE
                     WHEN tk.vai_tro_id = 2 THEN ntd.ho_ten
                     WHEN tk.vai_tro_id = 3 THEN uv.ho_ten
                     ELSE 'Admin'
                 END as ho_ten
          FROM thong_baos tb
          LEFT JOIN tai_khoans tk ON tb.tai_khoan_id = tk.tai_khoan_id
          LEFT JOIN nha_tuyen_dungs ntd ON tk.tai_khoan_id = ntd.tai_khoan_id
          LEFT JOIN ung_viens uv ON tk.tai_khoan_id = uv.tai_khoan_id
          WHERE 1=1";

$params = [];
$param_types = "";

// Add filters
if ($filter_status === 'read') {
    $query .= " AND tb.da_doc = 1";
} elseif ($filter_status === 'unread') {
    $query .= " AND tb.da_doc = 0";
}

if ($filter_type !== '' && isset($notification_types[$filter_type])) {
    $query .= " AND tb.loai_tb = ?";
    $params[] = $filter_type;
    $param_types .= "i";
}

if ($filter_user > 0) {
    $query .= " AND tb.tai_khoan_id = ?";
    $params[] = $filter_user;
    $param_types .= "i";
}

$query .= " ORDER BY tb.tao_luc DESC";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
if (!empty($param_types) && !empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];

while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

// Get statistics
$total_notifications = count($notifications);
$read_count = 0;
$unread_count = 0;
$type_counts = array_fill(1, 5, 0);

foreach ($notifications as $notif) {
    if ($notif['da_doc']) {
        $read_count++;
    } else {
        $unread_count++;
    }

    if (isset($type_counts[$notif['loai_tb']])) {
        $type_counts[$notif['loai_tb']]++;
    }
}

// Get users for filter dropdown
$users_query = "SELECT DISTINCT tk.tai_khoan_id, tk.ten_dn,
                       CASE
                           WHEN tk.vai_tro_id = 2 THEN ntd.ho_ten
                           WHEN tk.vai_tro_id = 3 THEN uv.ho_ten
                           ELSE 'Admin'
                       END as ho_ten,
                       vr.ten_vai_tro
                FROM tai_khoans tk
                LEFT JOIN vai_tros vr ON tk.vai_tro_id = vr.vai_tro_id
                LEFT JOIN nha_tuyen_dungs ntd ON tk.tai_khoan_id = ntd.tai_khoan_id
                LEFT JOIN ung_viens uv ON tk.tai_khoan_id = uv.tai_khoan_id
                WHERE tk.kich_hoat = 1
                ORDER BY tk.ten_dn";
$users_result = $conn->query($users_query);
$users = [];
if ($users_result) {
    while ($row = $users_result->fetch_assoc()) {
        $users[] = $row;
    }
    $users_result->free();
}

$stats = [
    'total' => $total_notifications,
    'read' => $read_count,
    'unread' => $unread_count,
    'by_type' => $type_counts
];

// Don't close connection - it will be used by the view
// Connection will be closed at the end of the page
?>