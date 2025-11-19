<?php
// Skills Dictionary Management Logic
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/functions.php';

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

// Initialize variables
$message = '';
$messageType = 'success';

// Function to generate slug from Vietnamese text
function generateSlug($text) {
    // Convert Vietnamese characters to ASCII
    $text = strtolower($text);
    $text = str_replace(
        ['à', 'á', 'ả', 'ã', 'ạ', 'ă', 'ằ', 'ắ', 'ẳ', 'ẵ', 'ặ', 'â', 'ầ', 'ấ', 'ẩ', 'ẫ', 'ậ'],
        'a',
        $text
    );
    $text = str_replace(
        ['è', 'é', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ề', 'ế', 'ể', 'ễ', 'ệ'],
        'e',
        $text
    );
    $text = str_replace(
        ['ì', 'í', 'ỉ', 'ĩ', 'ị'],
        'i',
        $text
    );
    $text = str_replace(
        ['ò', 'ó', 'ỏ', 'õ', 'ọ', 'ô', 'ồ', 'ố', 'ổ', 'ỗ', 'ộ', 'ơ', 'ờ', 'ớ', 'ở', 'ỡ', 'ợ'],
        'o',
        $text
    );
    $text = str_replace(
        ['ù', 'ú', 'ủ', 'ũ', 'ụ', 'ư', 'ừ', 'ứ', 'ử', 'ữ', 'ự'],
        'u',
        $text
    );
    $text = str_replace(
        ['ỳ', 'ý', 'ỷ', 'ỹ', 'ỵ'],
        'y',
        $text
    );
    $text = str_replace('đ', 'd', $text);

    // Remove special characters and replace spaces with hyphens
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/\s+/', '-', $text);
    $text = trim($text, '-');

    return $text;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        switch ($action) {
            case 'add_skill':
                $ten_kn = trim($_POST['ten_kn']);
                $slug = trim($_POST['slug']);

                if (empty($ten_kn)) {
                    $message = 'Tên kỹ năng không được để trống!';
                    $messageType = 'danger';
                } else {
                    // Generate slug if empty
                    if (empty($slug)) {
                        $slug = generateSlug($ten_kn);
                    }

                    // Check if skill name already exists
                    $stmt = $conn->prepare("SELECT kn_id FROM kn_tu_dien WHERE ten_kn = ?");
                    $stmt->bind_param("s", $ten_kn);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $message = 'Tên kỹ năng đã tồn tại!';
                        $messageType = 'danger';
                    } else {
                        // Check if slug already exists
                        $stmt = $conn->prepare("SELECT kn_id FROM kn_tu_dien WHERE slug = ?");
                        $stmt->bind_param("s", $slug);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            $message = 'Slug đã tồn tại!';
                            $messageType = 'danger';
                        } else {
                            // Insert new skill
                            $stmt = $conn->prepare("INSERT INTO kn_tu_dien (ten_kn, slug) VALUES (?, ?)");
                            $stmt->bind_param("ss", $ten_kn, $slug);

                            if ($stmt->execute()) {
                                $message = 'Thêm kỹ năng thành công!';
                                $messageType = 'success';
                            } else {
                                $message = 'Lỗi khi thêm kỹ năng: ' . $conn->error;
                                $messageType = 'danger';
                            }
                        }
                    }
                    $stmt->close();
                }
                break;

            case 'update_skill':
                $kn_id = (int)$_POST['kn_id'];
                $ten_kn = trim($_POST['ten_kn']);
                $slug = trim($_POST['slug']);

                if (empty($ten_kn)) {
                    $message = 'Tên kỹ năng không được để trống!';
                    $messageType = 'danger';
                } else {
                    // Generate slug if empty
                    if (empty($slug)) {
                        $slug = generateSlug($ten_kn);
                    }

                    // Check if skill name already exists (excluding current skill)
                    $stmt = $conn->prepare("SELECT kn_id FROM kn_tu_dien WHERE ten_kn = ? AND kn_id != ?");
                    $stmt->bind_param("si", $ten_kn, $kn_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $message = 'Tên kỹ năng đã tồn tại!';
                        $messageType = 'danger';
                    } else {
                        // Check if slug already exists (excluding current skill)
                        $stmt = $conn->prepare("SELECT kn_id FROM kn_tu_dien WHERE slug = ? AND kn_id != ?");
                        $stmt->bind_param("si", $slug, $kn_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            $message = 'Slug đã tồn tại!';
                            $messageType = 'danger';
                        } else {
                            // Update skill
                            $stmt = $conn->prepare("UPDATE kn_tu_dien SET ten_kn = ?, slug = ? WHERE kn_id = ?");
                            $stmt->bind_param("ssi", $ten_kn, $slug, $kn_id);

                            if ($stmt->execute()) {
                                $message = 'Cập nhật kỹ năng thành công!';
                                $messageType = 'success';
                            } else {
                                $message = 'Lỗi khi cập nhật kỹ năng: ' . $conn->error;
                                $messageType = 'danger';
                            }
                        }
                    }
                    $stmt->close();
                }
                break;

            case 'delete_skill':
                $kn_id = (int)$_POST['kn_id'];

                // Check if skill is being used in any CV or job requirements
                // You might need to add JOIN queries here depending on your database structure
                // For now, we'll allow deletion but you can add protection logic

                $stmt = $conn->prepare("DELETE FROM kn_tu_dien WHERE kn_id = ?");
                $stmt->bind_param("i", $kn_id);

                if ($stmt->execute()) {
                    $message = 'Xóa kỹ năng thành công!';
                    $messageType = 'success';
                } else {
                    $message = 'Lỗi khi xóa kỹ năng: ' . $conn->error;
                    $messageType = 'danger';
                }
                $stmt->close();
                break;
        }
    }
}

// Get all skills with usage count (you might need to adjust JOIN queries based on your actual table structure)
$query = "SELECT k.*,
          COALESCE(cv_count, 0) as cv_count,
          COALESCE(job_count, 0) as job_count
          FROM kn_tu_dien k
          LEFT JOIN (
              SELECT kn_id, COUNT(*) as cv_count
              FROM ung_vien_kn
              GROUP BY kn_id
          ) cv ON k.kn_id = cv.kn_id
          LEFT JOIN (
              SELECT kn_id, COUNT(*) as job_count
              FROM tin_kn
              GROUP BY kn_id
          ) jb ON k.kn_id = jb.kn_id
          ORDER BY k.ten_kn ASC";

$result = $conn->query($query);
$skills = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $skills[] = $row;
    }
    $result->free();
}

// Get statistics
$total_skills = count($skills);
$used_in_cv = 0;
$used_in_jobs = 0;
$unused_skills = 0;

foreach ($skills as $skill) {
    if ($skill['cv_count'] > 0) $used_in_cv++;
    if ($skill['job_count'] > 0) $used_in_jobs++;
    if ($skill['cv_count'] == 0 && $skill['job_count'] == 0) $unused_skills++;
}

$stats = [
    'total' => $total_skills,
    'used_in_cv' => $used_in_cv,
    'used_in_jobs' => $used_in_jobs,
    'unused' => $unused_skills
];

// Close connection
$conn->close();
?>