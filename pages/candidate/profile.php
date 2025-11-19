<?php
/**
 * Trang quản lý hồ sơ ứng viên
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/ung_vien.php';
require_once __DIR__ . '/../../models/dinh_kem.php';

// Kiểm tra đăng nhập và quyền ứng viên
requireRole(ROLE_CANDIDATE);

$ung_vien = getUngVienByTaiKhoanId($pdo, $_SESSION['user_id']);

// Nếu chưa có hồ sơ, tạo mới
if (!$ung_vien) {
    $ung_vien_id = createUngVien($pdo, [
        'tai_khoan_id' => $_SESSION['user_id'],
        'ho_ten' => null
    ]);
    $ung_vien = getUngVienById($pdo, $ung_vien_id);
}

$error = '';
$success = '';

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $ho_ten = sanitize($_POST['ho_ten'] ?? '');
    $ngay_sinh = $_POST['ngay_sinh'] ?? null;
    $gioi_tinh = sanitize($_POST['gioi_tinh'] ?? '');
    $noi_o = sanitize($_POST['noi_o'] ?? '');
    $tieu_de_cv = sanitize($_POST['tieu_de_cv'] ?? '');
    $gioi_thieu = sanitize($_POST['gioi_thieu'] ?? '');
    
    if (empty($ho_ten)) {
        $error = 'Vui lòng nhập họ và tên!';
    } else {
        $result = updateUngVien($pdo, $ung_vien['ung_vien_id'], [
            'ho_ten' => $ho_ten,
            'ngay_sinh' => $ngay_sinh ?: null,
            'gioi_tinh' => $gioi_tinh ?: null,
            'noi_o' => $noi_o ?: null,
            'tieu_de_cv' => $tieu_de_cv ?: null,
            'gioi_thieu' => $gioi_thieu ?: null
        ]);
        
        if ($result) {
            $success = 'Cập nhật thông tin thành công!';
            $ung_vien = getUngVienById($pdo, $ung_vien['ung_vien_id']);
        } else {
            $error = 'Có lỗi xảy ra khi cập nhật!';
        }
    }
}

// Xử lý upload CV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_cv') {
    if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['cv_file'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_type = $file['type'];
        
        // Kiểm tra loại file
        $allowed_types = ['application/pdf', 'application/msword', 
                         'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        
        if (!in_array($file_type, $allowed_types)) {
            $error = 'Chỉ chấp nhận file PDF, DOC, DOCX!';
        } elseif ($file_size > MAX_FILE_SIZE) {
            $error = 'File quá lớn! Kích thước tối đa: ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB';
        } else {
            // Tạo thư mục nếu chưa có
            $upload_dir = UPLOAD_DIR . 'cvs/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Tạo tên file unique
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_file_name = 'cv_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_ext;
            $file_path = $upload_dir . $new_file_name;
            $file_url = '/uploads/cvs/' . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $file_path)) {
                // Lưu vào database
                $dk_id = createDinhKem($pdo, [
                    'chu_so_huu_tai_khoan_id' => $_SESSION['user_id'],
                    'tep_url' => $file_url,
                    'ten_tep' => $file_name,
                    'mime_type' => $file_type,
                    'doi_tuong' => 'CV',
                    'doi_tuong_id' => $ung_vien['ung_vien_id']
                ]);
                
                if ($dk_id) {
                    $success = 'Upload CV thành công!';
                } else {
                    @unlink($file_path);
                    $error = 'Có lỗi xảy ra khi lưu thông tin file!';
                }
            } else {
                $error = 'Không thể upload file!';
            }
        }
    } else {
        $error = 'Vui lòng chọn file CV!';
    }
}

// Xử lý xóa CV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_cv') {
    $dk_id = isset($_POST['dk_id']) ? (int)$_POST['dk_id'] : 0;
    
    if ($dk_id) {
        $result = deleteDinhKem($pdo, $dk_id);
        if ($result) {
            $success = 'Xóa CV thành công!';
        } else {
            $error = 'Không thể xóa CV!';
        }
    }
}

// Lấy danh sách CV
$cvs = getCVCuaUngVien($pdo, $_SESSION['user_id']);

$page_title = 'Quản lý hồ sơ';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Ứng viên</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Lato&family=Oswald:wght@200;300;400&display=swap" rel="stylesheet">
    <!-- Template Stylesheet -->
    <link href="../css/style.css" rel="stylesheet">
    
    <style>
        .profile-container {
            max-width: 900px;
            margin: 40px auto;
        }
        .profile-box {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .cv-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cv-item:hover {
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../includes/header.php'; ?>
    
    <div class="container">
        <div class="profile-container">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <!-- Thông tin cá nhân -->
            <div class="profile-box">
                <h3 style="font-family: 'Oswald', sans-serif; color: #092a49; margin-bottom: 20px;">
                    <i class="fa fa-user"></i> Thông tin cá nhân
                </h3>
                
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-user"></i> Họ và tên <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" name="ho_ten" 
                                       value="<?php echo htmlspecialchars($ung_vien['ho_ten'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-calendar"></i> Ngày sinh</label>
                                <input type="date" class="form-control" name="ngay_sinh" 
                                       value="<?php echo $ung_vien['ngay_sinh'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-venus-mars"></i> Giới tính</label>
                                <select class="form-control" name="gioi_tinh">
                                    <option value="">Chọn giới tính</option>
                                    <option value="Nam" <?php echo ($ung_vien['gioi_tinh'] ?? '') == 'Nam' ? 'selected' : ''; ?>>Nam</option>
                                    <option value="Nữ" <?php echo ($ung_vien['gioi_tinh'] ?? '') == 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
                                    <option value="Khác" <?php echo ($ung_vien['gioi_tinh'] ?? '') == 'Khác' ? 'selected' : ''; ?>>Khác</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-map-marker-alt"></i> Nơi ở</label>
                                <input type="text" class="form-control" name="noi_o" 
                                       value="<?php echo htmlspecialchars($ung_vien['noi_o'] ?? ''); ?>" 
                                       placeholder="VD: Hà Nội, TP.HCM">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fa fa-briefcase"></i> Tiêu đề CV</label>
                        <input type="text" class="form-control" name="tieu_de_cv" 
                               value="<?php echo htmlspecialchars($ung_vien['tieu_de_cv'] ?? ''); ?>" 
                               placeholder="VD: PHP Developer với 3 năm kinh nghiệm">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fa fa-file-alt"></i> Giới thiệu bản thân</label>
                        <textarea class="form-control" name="gioi_thieu" rows="5" 
                                  placeholder="Viết giới thiệu ngắn gọn về bản thân, kinh nghiệm, kỹ năng..."><?php echo htmlspecialchars($ung_vien['gioi_thieu'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Lưu thông tin
                    </button>
                </form>
            </div>
            
            <!-- Quản lý CV -->
            <div class="profile-box">
                <h3 style="font-family: 'Oswald', sans-serif; color: #092a49; margin-bottom: 20px;">
                    <i class="fa fa-file-pdf"></i> Quản lý CV
                </h3>
                
                <!-- Form upload CV -->
                <form method="POST" enctype="multipart/form-data" style="margin-bottom: 30px;">
                    <input type="hidden" name="action" value="upload_cv">
                    
                    <div class="form-group">
                        <label><i class="fa fa-upload"></i> Upload CV mới</label>
                        <input type="file" class="form-control-file" name="cv_file" 
                               accept=".pdf,.doc,.docx" required>
                        <small class="form-text text-muted">
                            Chấp nhận file: PDF, DOC, DOCX. Kích thước tối đa: <?php echo MAX_FILE_SIZE / 1024 / 1024; ?>MB
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-upload"></i> Upload CV
                    </button>
                </form>
                
                <!-- Danh sách CV -->
                <h4>CV đã upload (<?php echo count($cvs); ?>)</h4>
                <?php if (empty($cvs)): ?>
                    <p class="text-muted">Chưa có CV nào. Hãy upload CV đầu tiên của bạn!</p>
                <?php else: ?>
                    <?php foreach ($cvs as $cv): ?>
                        <div class="cv-item">
                            <div>
                                <strong>
                                    <i class="fa fa-file-pdf"></i> <?php echo htmlspecialchars($cv['ten_tep']); ?>
                                </strong>
                                <br>
                                <small class="text-muted">
                                    Upload: <?php echo formatDate($cv['tao_luc'], 'd/m/Y H:i'); ?>
                                </small>
                            </div>
                            <div>
                                <a href="<?php echo BASE_URL . ltrim($cv['tep_url'], '/'); ?>" 
                                   target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fa fa-eye"></i> Xem
                                </a>
                                <form method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Bạn có chắc muốn xóa CV này?');">
                                    <input type="hidden" name="action" value="delete_cv">
                                    <input type="hidden" name="dk_id" value="<?php echo $cv['dk_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fa fa-trash"></i> Xóa
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>

