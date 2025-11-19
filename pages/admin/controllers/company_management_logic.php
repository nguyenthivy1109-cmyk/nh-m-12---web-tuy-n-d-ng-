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

// Xử lý upload file
function uploadImage($file, $type = 'logo') {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        return null;
    }

    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        return null;
    }

    $upload_dir = '../../img/companies/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $type . '_' . time() . '_' . uniqid() . '.' . $extension;
    $filepath = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'img/companies/' . $filename;
    }

    return null;
}

// Xử lý các action
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    switch ($action) {
        case 'verify_company':
            $cong_ty_id = $_POST['cong_ty_id'];
            $trang_thai = $_POST['trang_thai'];

            $stmt = $conn->prepare("UPDATE cong_tys SET trang_thai = ?, ngay_duyet = NOW() WHERE cong_ty_id = ?");
            $stmt->bind_param("si", $trang_thai, $cong_ty_id);
            if ($stmt->execute()) {
                $message = "Đã cập nhật trạng thái công ty thành công!";
                $messageType = "success";
            } else {
                $message = "Lỗi khi cập nhật trạng thái công ty!";
                $messageType = "danger";
            }
            break;

        case 'update_company':
            $cong_ty_id = $_POST['cong_ty_id'];
            $ten_cong_ty = trim($_POST['ten_cong_ty']);
            $mo_ta = trim($_POST['mo_ta']);
            $dia_chi = trim($_POST['dia_chi']);
            $website = trim($_POST['website']);
            $sdt = trim($_POST['sdt']);
            $email = trim($_POST['email']);

            // Upload logo nếu có
            $logo_path = null;
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $logo_path = uploadImage($_FILES['logo'], 'logo');
                if (!$logo_path) {
                    $message = "Lỗi upload logo! Chỉ chấp nhận JPG, PNG, GIF, WebP và tối đa 5MB.";
                    $messageType = "danger";
                    break;
                }
            }

            // Upload ảnh bìa nếu có
            $banner_path = null;
            if (isset($_FILES['anh_bia']) && $_FILES['anh_bia']['error'] !== UPLOAD_ERR_NO_FILE) {
                $banner_path = uploadImage($_FILES['anh_bia'], 'banner');
                if (!$banner_path) {
                    $message = "Lỗi upload ảnh bìa! Chỉ chấp nhận JPG, PNG, GIF, WebP và tối đa 5MB.";
                    $messageType = "danger";
                    break;
                }
            }

            // Cập nhật thông tin
            $sql = "UPDATE cong_tys SET ten_cong_ty = ?, mo_ta = ?, dia_chi = ?, website = ?, sdt = ?, email = ?";
            $params = [$ten_cong_ty, $mo_ta, $dia_chi, $website, $sdt, $email];
            $types = "ssssss";

            if ($logo_path) {
                $sql .= ", logo = ?";
                $params[] = $logo_path;
                $types .= "s";
            }

            if ($banner_path) {
                $sql .= ", anh_bia = ?";
                $params[] = $banner_path;
                $types .= "s";
            }

            $sql .= " WHERE cong_ty_id = ?";
            $params[] = $cong_ty_id;
            $types .= "i";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                $message = "Đã cập nhật thông tin công ty thành công!";
                $messageType = "success";
            } else {
                $message = "Lỗi khi cập nhật thông tin công ty!";
                $messageType = "danger";
            }
            break;

        case 'delete_company':
            $cong_ty_id = $_POST['cong_ty_id'];

            $stmt = $conn->prepare("UPDATE cong_tys SET xoa_luc = NOW() WHERE cong_ty_id = ?");
            $stmt->bind_param("i", $cong_ty_id);
            if ($stmt->execute()) {
                $message = "Đã ẩn công ty thành công!";
                $messageType = "success";
            } else {
                $message = "Lỗi khi ẩn công ty!";
                $messageType = "danger";
            }
            break;

        case 'restore_company':
            $cong_ty_id = $_POST['cong_ty_id'];

            $stmt = $conn->prepare("UPDATE cong_tys SET xoa_luc = NULL WHERE cong_ty_id = ?");
            $stmt->bind_param("i", $cong_ty_id);
            if ($stmt->execute()) {
                $message = "Đã khôi phục công ty thành công!";
                $messageType = "success";
            } else {
                $message = "Lỗi khi khôi phục công ty!";
                $messageType = "danger";
            }
            break;
    }
}

// Lấy danh sách công ty (bao gồm cả đã xóa)
$show_deleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] == '1';
$where_clause = $show_deleted ? "" : "WHERE c.xoa_luc IS NULL";

$query = "
    SELECT
        c.*,
        ntd.ho_ten as ten_nha_tuyen_dung,
        tk.ten_dn,
        tk.email as email_nha_tuyen_dung
    FROM cong_tys c
    LEFT JOIN nha_tuyen_dungs ntd ON c.cong_ty_id = ntd.cong_ty_id
    LEFT JOIN tai_khoans tk ON ntd.tai_khoan_id = tk.tai_khoan_id
    $where_clause
    ORDER BY c.tao_luc DESC
";

$result = $conn->query($query);
$companies = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Thống kê
$stats = [
    'total_active' => 0,
    'total_pending' => 0,
    'total_deleted' => 0
];

$query_stats = "SELECT
    COUNT(CASE WHEN xoa_luc IS NULL THEN 1 END) as active,
    COUNT(CASE WHEN xoa_luc IS NULL THEN 1 END) as pending,
    COUNT(CASE WHEN xoa_luc IS NOT NULL THEN 1 END) as deleted
FROM cong_tys";

$result_stats = $conn->query($query_stats);
if ($result_stats && $result_stats->num_rows > 0) {
    $stat_data = $result_stats->fetch_assoc();
    $stats['total_active'] = (int)($stat_data['active'] ?? 0);
    $stats['total_pending'] = (int)($stat_data['pending'] ?? 0);
    $stats['total_deleted'] = (int)($stat_data['deleted'] ?? 0);
} else {
    // Nếu query không trả về kết quả, đếm trực tiếp từ danh sách
    foreach ($companies as $company) {
        if (!empty($company['xoa_luc'])) {
            $stats['total_deleted']++;
        } else {
            $stats['total_active']++;
        }
    }
}
?>

