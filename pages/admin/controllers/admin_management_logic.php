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

// Lấy thông tin admin hiện tại
$stmt = $conn->prepare("SELECT a.ho_ten, tk.ten_dn FROM admins a JOIN tai_khoans tk ON a.tai_khoan_id = tk.tai_khoan_id WHERE a.tai_khoan_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

// Xử lý các action
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    switch ($action) {
        case 'add_admin':
            $ho_ten = trim($_POST['ho_ten']);
            $ghi_chu = trim($_POST['ghi_chu']);

            // Kiểm tra xem có chọn tài khoản có sẵn không
            if (!empty($_POST['existing_account_id'])) {
                $tai_khoan_id = intval($_POST['existing_account_id']);

                // Kiểm tra tài khoản có tồn tại và chưa phải admin
                $stmt = $conn->prepare("
                    SELECT tk.tai_khoan_id, tk.ten_dn, a.admin_id
                    FROM tai_khoans tk
                    LEFT JOIN admins a ON tk.tai_khoan_id = a.tai_khoan_id
                    WHERE tk.tai_khoan_id = ? AND tk.kich_hoat = 1
                ");
                $stmt->bind_param("i", $tai_khoan_id);
                $stmt->execute();
                $account = $stmt->get_result()->fetch_assoc();

                if (!$account) {
                    $message = "Tài khoản không tồn tại hoặc chưa được kích hoạt!";
                    $messageType = "danger";
                } elseif ($account['admin_id']) {
                    $message = "Tài khoản này đã là admin!";
                    $messageType = "warning";
                } else {
                    $stmt = $conn->prepare("INSERT INTO admins (tai_khoan_id, ho_ten, ghi_chu) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $tai_khoan_id, $ho_ten, $ghi_chu);
                    if ($stmt->execute()) {
                        $message = "Đã thêm admin thành công!";
                        $messageType = "success";
                    } else {
                        $message = "Lỗi khi thêm admin!";
                        $messageType = "danger";
                    }
                }
            } else {
                $message = "Vui lòng chọn tài khoản người dùng để gán quyền admin!";
                $messageType = "warning";
            }
            break;

        case 'update_admin':
            $admin_id = $_POST['admin_id'];
            $ho_ten = trim($_POST['ho_ten']);
            $ghi_chu = trim($_POST['ghi_chu']);

            $stmt = $conn->prepare("UPDATE admins SET ho_ten = ?, ghi_chu = ? WHERE admin_id = ?");
            $stmt->bind_param("ssi", $ho_ten, $ghi_chu, $admin_id);
            if ($stmt->execute()) {
                $message = "Đã cập nhật thông tin admin thành công!";
                $messageType = "success";
            } else {
                $message = "Lỗi khi cập nhật thông tin admin!";
                $messageType = "danger";
            }
            break;

        case 'delete_admin':
            $admin_id = $_POST['admin_id'];

            // Không cho phép xóa admin hiện tại
            if ($admin_id == $_SESSION['user_id']) {
                $message = "Không thể xóa tài khoản admin hiện tại!";
                $messageType = "danger";
            } else {
                // Kiểm tra số lượng admin còn lại
                $result = $conn->query("SELECT COUNT(*) as count FROM admins");
                $admin_count = $result->fetch_assoc()['count'];

                if ($admin_count <= 1) {
                    $message = "Không thể xóa admin cuối cùng trong hệ thống!";
                    $messageType = "danger";
                } else {
                    $stmt = $conn->prepare("DELETE FROM admins WHERE admin_id = ?");
                    $stmt->bind_param("i", $admin_id);
                    if ($stmt->execute()) {
                        $message = "Đã xóa admin thành công!";
                        $messageType = "success";
                    } else {
                        $message = "Lỗi khi xóa admin!";
                        $messageType = "danger";
                    }
                }
            }
            break;
    }
}

// Lấy danh sách admins
$query = "
    SELECT
        a.admin_id,
        a.tai_khoan_id,
        a.ho_ten,
        a.ghi_chu,
        a.tao_luc,
        tk.ten_dn,
        tk.email
    FROM admins a
    JOIN tai_khoans tk ON a.tai_khoan_id = tk.tai_khoan_id
    ORDER BY a.tao_luc DESC
";
$result = $conn->query($query);
$admins = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Lấy danh sách tài khoản có thể gán làm admin (tất cả user chưa phải admin)
$query = "
    SELECT tk.tai_khoan_id, tk.ten_dn, tk.email, tk.vai_tro_id,
           CASE
               WHEN tk.vai_tro_id = 2 THEN 'Nhà tuyển dụng'
               WHEN tk.vai_tro_id = 3 THEN 'Ứng viên'
               ELSE 'Không xác định'
           END as ten_vai_tro
    FROM tai_khoans tk
    LEFT JOIN admins a ON tk.tai_khoan_id = a.tai_khoan_id
    WHERE a.admin_id IS NULL AND tk.kich_hoat = 1
    ORDER BY tk.ten_dn
";
$result = $conn->query($query);
$available_accounts = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Thống kê
$stats = [
    'total_admins' => count($admins),
    'available_accounts' => count($available_accounts)
];
?>

