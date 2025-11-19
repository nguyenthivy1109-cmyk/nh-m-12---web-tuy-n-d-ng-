<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../includes/db.php';

// Check if user is admin
// Hỗ trợ cả $_SESSION['vai_tro'] và $_SESSION['vai_tro_id']
require_once __DIR__ . '/../../../includes/functions.php';
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $dk_id = intval($_POST['dk_id'] ?? 0);
        
        if ($dk_id > 0) {
            // Get file path before deleting
            $stmt = $conn->prepare("SELECT tep_url FROM dinh_kems WHERE dk_id = ?");
            $stmt->bind_param("i", $dk_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $file_path = __DIR__ . '/../../' . $row['tep_url'];
                
                // Delete from database
                $delete_stmt = $conn->prepare("DELETE FROM dinh_kems WHERE dk_id = ?");
                $delete_stmt->bind_param("i", $dk_id);
                
                if ($delete_stmt->execute()) {
                    // Try to delete physical file
                    if (file_exists($file_path)) {
                        @unlink($file_path);
                    }
                    echo json_encode(['success' => true, 'message' => 'Xóa file thành công!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa file!']);
                }
                $delete_stmt->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy file!']);
            }
            $stmt->close();
        }
        exit();
    }
}

// Get filter parameters
$filter_type = $_GET['doi_tuong'] ?? '';
$filter_user = $_GET['tai_khoan_id'] ?? '';
$search = $_GET['search'] ?? '';

// Build query with filters
$query = "SELECT dk.dk_id, dk.chu_so_huu_tai_khoan_id, dk.tep_url, dk.ten_tep, dk.mime_type, 
                 dk.doi_tuong, dk.doi_tuong_id, dk.tao_luc,
                 tk.ten_dn, tk.email,
                 CASE
                     WHEN tk.vai_tro_id = 2 THEN ntd.ho_ten
                     WHEN tk.vai_tro_id = 3 THEN uv.ho_ten
                     ELSE 'Admin'
                 END as ho_ten
          FROM dinh_kems dk
          LEFT JOIN tai_khoans tk ON dk.chu_so_huu_tai_khoan_id = tk.tai_khoan_id
          LEFT JOIN nha_tuyen_dungs ntd ON tk.tai_khoan_id = ntd.tai_khoan_id
          LEFT JOIN ung_viens uv ON tk.tai_khoan_id = uv.tai_khoan_id
          WHERE 1=1";

$params = [];
$types = "";

if (!empty($filter_type)) {
    $query .= " AND dk.doi_tuong = ?";
    $params[] = $filter_type;
    $types .= "s";
}

if (!empty($filter_user)) {
    $query .= " AND dk.chu_so_huu_tai_khoan_id = ?";
    $params[] = intval($filter_user);
    $types .= "i";
}

if (!empty($search)) {
    $query .= " AND (dk.ten_tep LIKE ? OR tk.ten_dn LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

$query .= " ORDER BY dk.tao_luc DESC";

// Execute query
if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}

$attachments = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $attachments[] = $row;
    }
    if (isset($stmt)) {
        $stmt->close();
    }
}

// Get statistics
$stats = [];

// Total files
$result = $conn->query("SELECT COUNT(*) as total FROM dinh_kems");
$stats['total_files'] = $result->fetch_assoc()['total'];
$result->free();

// Total storage (estimate based on file count, would need actual file sizes)
$stats['total_storage'] = $stats['total_files'] * 2.5; // MB estimate

// Files by type
$result = $conn->query("SELECT doi_tuong, COUNT(*) as count FROM dinh_kems GROUP BY doi_tuong");
$stats['by_type'] = [];
while ($row = $result->fetch_assoc()) {
    $stats['by_type'][$row['doi_tuong']] = $row['count'];
}
$result->free();

// Total users with files
$result = $conn->query("SELECT COUNT(DISTINCT chu_so_huu_tai_khoan_id) as user_count FROM dinh_kems");
$stats['total_users'] = $result->fetch_assoc()['user_count'];
$result->free();

// Get unique users for filter
$users_result = $conn->query("SELECT DISTINCT tk.tai_khoan_id, tk.ten_dn, 
                                      CASE
                                          WHEN tk.vai_tro_id = 2 THEN ntd.ho_ten
                                          WHEN tk.vai_tro_id = 3 THEN uv.ho_ten
                                          ELSE 'Admin'
                                      END as ho_ten
                               FROM dinh_kems dk
                               LEFT JOIN tai_khoans tk ON dk.chu_so_huu_tai_khoan_id = tk.tai_khoan_id
                               LEFT JOIN nha_tuyen_dungs ntd ON tk.tai_khoan_id = ntd.tai_khoan_id
                               LEFT JOIN ung_viens uv ON tk.tai_khoan_id = uv.tai_khoan_id
                               ORDER BY tk.ten_dn");
$users = [];
if ($users_result) {
    while ($row = $users_result->fetch_assoc()) {
        $users[] = $row;
    }
    $users_result->free();
}

// Get attachment types
$attachment_types = [
    'cv' => 'CV/Hồ sơ',
    'company_logo' => 'Logo công ty',
    'company_banner' => 'Banner công ty',
    'job_attachment' => 'Tài liệu tin tuyển dụng',
    'other' => 'Khác'
];

// File type labels
$file_type_labels = [
    'cv' => ['label' => 'CV/Hồ sơ', 'class' => 'primary'],
    'company_logo' => ['label' => 'Logo', 'class' => 'info'],
    'company_banner' => ['label' => 'Banner', 'class' => 'success'],
    'job_attachment' => ['label' => 'Tài liệu', 'class' => 'warning'],
    'other' => ['label' => 'Khác', 'class' => 'secondary']
];
?>