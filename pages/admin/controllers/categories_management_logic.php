<?php
// Categories Management Logic
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../includes/db.php';

// Check if user is logged in and is admin
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

$message = '';
$messageType = 'success';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_category':
            $ten_nhom = trim($_POST['ten_nhom']);
            $slug = trim($_POST['slug']);

            if (empty($ten_nhom)) {
                $message = 'Tên nhóm ngành nghề là bắt buộc';
                $messageType = 'danger';
                break;
            }

            // Generate slug if empty
            if (empty($slug)) {
                $slug = generateSlug($ten_nhom);
            }

            // Check if slug already exists
            $stmt = $conn->prepare("SELECT nhom_id FROM nhom_nganhs WHERE slug = ?");
            $stmt->bind_param("s", $slug);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $message = 'Slug đã tồn tại, vui lòng chọn slug khác';
                $messageType = 'danger';
                break;
            }

            $stmt = $conn->prepare("INSERT INTO nhom_nganhs (ten_nhom, slug) VALUES (?, ?)");
            $stmt->bind_param("ss", $ten_nhom, $slug);

            if ($stmt->execute()) {
                $message = 'Đã thêm nhóm ngành nghề mới';
            } else {
                $message = 'Lỗi: ' . $conn->error;
                $messageType = 'danger';
            }
            break;

        case 'update_category':
            $nhom_id = (int)$_POST['nhom_id'];
            $ten_nhom = trim($_POST['ten_nhom']);
            $slug = trim($_POST['slug']);

            if (empty($ten_nhom)) {
                $message = 'Tên nhóm ngành nghề là bắt buộc';
                $messageType = 'danger';
                break;
            }

            // Generate slug if empty
            if (empty($slug)) {
                $slug = generateSlug($ten_nhom);
            }

            // Check if slug already exists (excluding current category)
            $stmt = $conn->prepare("SELECT nhom_id FROM nhom_nganhs WHERE slug = ? AND nhom_id != ?");
            $stmt->bind_param("si", $slug, $nhom_id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $message = 'Slug đã tồn tại, vui lòng chọn slug khác';
                $messageType = 'danger';
                break;
            }

            $stmt = $conn->prepare("UPDATE nhom_nganhs SET ten_nhom = ?, slug = ? WHERE nhom_id = ?");
            $stmt->bind_param("ssi", $ten_nhom, $slug, $nhom_id);

            if ($stmt->execute()) {
                $message = 'Đã cập nhật nhóm ngành nghề';
            } else {
                $message = 'Lỗi: ' . $conn->error;
                $messageType = 'danger';
            }
            break;

        case 'delete_category':
            $nhom_id = (int)$_POST['nhom_id'];

            // Check if category is being used in tin_nhom table
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tin_nhom WHERE nhom_id = ?");
            $stmt->bind_param("i", $nhom_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = 0;
            if ($result && $result->num_rows > 0) {
                $count = $result->fetch_assoc()['count'];
            }

            if ($count > 0) {
                $message = 'Không thể xóa nhóm ngành nghề này vì đang được sử dụng trong ' . $count . ' tin tuyển dụng';
                $messageType = 'danger';
                break;
            }

            $stmt = $conn->prepare("DELETE FROM nhom_nganhs WHERE nhom_id = ?");
            $stmt->bind_param("i", $nhom_id);

            if ($stmt->execute()) {
                $message = 'Đã xóa nhóm ngành nghề';
            } else {
                $message = 'Lỗi: ' . $conn->error;
                $messageType = 'danger';
            }
            break;
    }
}

// Get all categories
$query = "
    SELECT n.*,
           COUNT(tn.tin_id) as job_count
    FROM nhom_nganhs n
    LEFT JOIN tin_nhom tn ON n.nhom_id = tn.nhom_id
    GROUP BY n.nhom_id, n.ten_nhom, n.slug
    ORDER BY n.ten_nhom ASC
";

$result = $conn->query($query);
$categories = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Function to generate slug
function generateSlug($text) {
    // Convert to lowercase
    $text = strtolower($text);

    // Replace Vietnamese characters
    $text = str_replace(
        ['à', 'á', 'ả', 'ã', 'ạ', 'ă', 'ằ', 'ắ', 'ẳ', 'ẵ', 'ặ', 'â', 'ầ', 'ấ', 'ẩ', 'ẫ', 'ậ', 'đ', 'è', 'é', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ề', 'ế', 'ể', 'ễ', 'ệ', 'ì', 'í', 'ỉ', 'ĩ', 'ị', 'ò', 'ó', 'ỏ', 'õ', 'ọ', 'ô', 'ồ', 'ố', 'ổ', 'ỗ', 'ộ', 'ơ', 'ờ', 'ớ', 'ở', 'ỡ', 'ợ', 'ù', 'ú', 'ủ', 'ũ', 'ụ', 'ư', 'ừ', 'ứ', 'ử', 'ữ', 'ự', 'ỳ', 'ý', 'ỷ', 'ỹ', 'ỵ'],
        ['a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'd', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'y', 'y', 'y', 'y', 'y'],
        $text
    );

    // Replace non-alphanumeric characters with hyphens
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);

    // Remove leading/trailing hyphens
    $text = trim($text, '-');

    // Ensure unique slug
    $original_slug = $text;
    $counter = 1;
    while (true) {
        global $conn;
        $stmt = $conn->prepare("SELECT nhom_id FROM nhom_nganhs WHERE slug = ?");
        $stmt->bind_param("s", $text);
        $stmt->execute();
        if ($stmt->get_result()->num_rows == 0) {
            break;
        }
        $text = $original_slug . '-' . $counter;
        $counter++;
    }

    return $text;
}
?>
