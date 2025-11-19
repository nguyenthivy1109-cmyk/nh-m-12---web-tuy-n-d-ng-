<?php
/**
 * Trang ứng tuyển
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/tin_td.php';
require_once __DIR__ . '/../../models/ung_tuyen.php';
require_once __DIR__ . '/../../models/ung_vien.php';
require_once __DIR__ . '/../../models/dinh_kem.php';

// Kiểm tra đăng nhập và quyền ứng viên
requireRole(ROLE_CANDIDATE);

$tin_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$tin_id) {
    header("Location: " . BASE_URL . "candidate/index.php");
    exit();
}

// Lấy thông tin tin tuyển dụng
$job = getTinTuyenDungDetail($pdo, $tin_id);

if (!$job || $job['trang_thai_tin'] != 1) {
    header("Location: " . BASE_URL . "candidate/index.php");
    exit();
}

// Lấy thông tin ứng viên
$ung_vien = getUngVienByTaiKhoanId($pdo, $_SESSION['user_id']);

if (!$ung_vien) {
    header("Location: " . BASE_URL . "candidate/profile.php");
    exit();
}

// Kiểm tra đã ứng tuyển chưa
$da_ung_tuyen = hasUngTuyen($pdo, $tin_id, $ung_vien['ung_vien_id']);

// Lấy CV của ứng viên
$cvs = getCVCuaUngVien($pdo, $_SESSION['user_id']);

$error = '';
$success = '';

// Xử lý form ứng tuyển
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$da_ung_tuyen) {
    $thu_ung_tuyen = sanitize($_POST['thu_ung_tuyen'] ?? '');
    $cv_id = isset($_POST['cv_id']) ? (int)$_POST['cv_id'] : null;
    $nguon = sanitize($_POST['nguon'] ?? 'Website');
    
    if (empty($thu_ung_tuyen)) {
        $error = 'Vui lòng viết thư ứng tuyển!';
    } else {
        try {
            $result = createUngTuyen($pdo, [
                'tin_id' => $tin_id,
                'ung_vien_id' => $ung_vien['ung_vien_id'],
                'cv_id' => $cv_id,
                'thu_ung_tuyen' => $thu_ung_tuyen,
                'nguon' => $nguon
            ]);
            
            if (isset($result['error'])) {
                $error = $result['error'];
            } else {
                $success = 'Ứng tuyển thành công! Nhà tuyển dụng sẽ xem xét hồ sơ của bạn.';
                $da_ung_tuyen = true;
            }
        } catch (Exception $e) {
            $error = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
    }
}

$page_title = 'Ứng tuyển: ' . htmlspecialchars($job['tieu_de']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Lato&family=Oswald:wght@200;300;400&display=swap" rel="stylesheet">
    <!-- Template Stylesheet -->
    <link href="../css/style.css" rel="stylesheet">
    
    <style>
        .apply-container {
            max-width: 800px;
            margin: 40px auto;
        }
        .apply-box {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .job-info-box {
            background: #f8f9fa;
            border-left: 4px solid #0796fe;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .cv-option {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .cv-option:hover {
            border-color: #0796fe;
            background: #f0f8ff;
        }
        .cv-option.selected {
            border-color: #0796fe;
            background: #e3f2fd;
        }
        .cv-option input[type="radio"] {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../includes/header.php'; ?>
    
    <div class="container">
        <div class="apply-container">
            <div class="apply-box">
                <h2 style="font-family: 'Oswald', sans-serif; color: #092a49; margin-bottom: 30px;">
                    <i class="fa fa-paper-plane"></i> Ứng tuyển
                </h2>
                
                <!-- Thông tin công việc -->
                <div class="job-info-box">
                    <h4 style="color: #092a49; margin-bottom: 10px;">
                        <?php echo htmlspecialchars($job['tieu_de']); ?>
                    </h4>
                    <p style="color: #797979; margin: 0;">
                        <i class="fa fa-building"></i> <?php echo htmlspecialchars($job['ten_cong_ty']); ?>
                    </p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                    <div class="text-center mt-4">
                        <a href="applications.php" class="btn btn-primary">
                            <i class="fa fa-list"></i> Xem đơn ứng tuyển của tôi
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                <?php elseif ($da_ung_tuyen): ?>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> Bạn đã ứng tuyển vào vị trí này rồi.
                    </div>
                    <div class="text-center mt-4">
                        <a href="applications.php" class="btn btn-primary">
                            <i class="fa fa-list"></i> Xem đơn ứng tuyển của tôi
                        </a>
                        <a href="job-detail.php?id=<?php echo $tin_id; ?>" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                <?php else: ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <!-- Chọn CV -->
                    <?php if (!empty($cvs)): ?>
                        <div class="form-group">
                            <label><i class="fa fa-file-pdf"></i> Chọn CV đính kèm</label>
                            <?php foreach ($cvs as $cv): ?>
                                <div class="cv-option">
                                    <label style="cursor: pointer; margin: 0; width: 100%;">
                                        <input type="radio" name="cv_id" value="<?php echo $cv['dk_id']; ?>" required>
                                        <strong><?php echo htmlspecialchars($cv['ten_tep']); ?></strong>
                                        <span style="color: #797979; font-size: 12px; margin-left: 10px;">
                                            (<?php echo formatDate($cv['tao_luc'], 'd/m/Y'); ?>)
                                        </span>
                                        <a href="<?php echo BASE_URL . ltrim($cv['tep_url'], '/'); ?>" target="_blank" class="btn btn-sm btn-outline-primary float-right">
                                            <i class="fa fa-eye"></i> Xem
                                        </a>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-muted" style="font-size: 14px;">
                            <i class="fa fa-info-circle"></i> Chưa có CV? 
                            <a href="profile.php">Upload CV tại đây</a>
                        </p>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i> Bạn chưa có CV. 
                            <a href="profile.php">Vui lòng upload CV trước khi ứng tuyển</a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Thư ứng tuyển -->
                    <div class="form-group">
                        <label><i class="fa fa-envelope"></i> Thư ứng tuyển <span style="color: red;">*</span></label>
                        <textarea class="form-control" name="thu_ung_tuyen" rows="8" required 
                                  placeholder="Viết thư giới thiệu bản thân và lý do bạn phù hợp với vị trí này..."><?php echo htmlspecialchars($_POST['thu_ung_tuyen'] ?? ''); ?></textarea>
                        <small class="form-text text-muted">
                            Thư ứng tuyển giúp bạn thể hiện sự quan tâm và phù hợp với vị trí
                        </small>
                    </div>
                    
                    <!-- Nguồn -->
                    <div class="form-group">
                        <label><i class="fa fa-globe"></i> Nguồn</label>
                        <select class="form-control" name="nguon">
                            <option value="Website" selected>Website</option>
                            <option value="Facebook">Facebook</option>
                            <option value="LinkedIn">LinkedIn</option>
                            <option value="Bạn bè giới thiệu">Bạn bè giới thiệu</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                            <i class="fa fa-paper-plane"></i> Gửi đơn ứng tuyển
                        </button>
                    </div>
                </form>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Xử lý chọn CV
        $('.cv-option').click(function() {
            $('.cv-option').removeClass('selected');
            $(this).addClass('selected');
            $(this).find('input[type="radio"]').prop('checked', true);
        });
        
        $('.cv-option input[type="radio"]').click(function(e) {
            e.stopPropagation();
        });
    </script>
</body>
</html>

