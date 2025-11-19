<?php
/**
 * Trang đăng ký công ty cho nhà tuyển dụng
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/tai_khoan.php';
require_once __DIR__ . '/../../models/cong_ty.php';
require_once __DIR__ . '/../../models/nha_tuyen_dung.php';

// Kiểm tra đăng nhập và quyền nhà tuyển dụng
requireRole(ROLE_RECRUITER);

// Kiểm tra đã có công ty chưa
$nha_td = getNhaTuyenDungByTaiKhoanId($pdo, $_SESSION['user_id']);

if ($nha_td && $nha_td['cong_ty_id']) {
    header("Location: " . recruiterRoute('dashboard'));
    exit();
}

$error = '';
$success = '';

// Xử lý form đăng ký công ty
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_cong_ty = sanitize($_POST['ten_cong_ty'] ?? '');
    $ma_so_thue = sanitize($_POST['ma_so_thue'] ?? '');
    $website = sanitize($_POST['website'] ?? '');
    $nganh_nghe = sanitize($_POST['nganh_nghe'] ?? '');
    $quy_mo = sanitize($_POST['quy_mo'] ?? '');
    $dia_chi_tru_so = sanitize($_POST['dia_chi_tru_so'] ?? '');
    $gioi_thieu = sanitize($_POST['gioi_thieu'] ?? '');
    
    // Validation
    if (empty($ten_cong_ty)) {
        $error = 'Vui lòng nhập tên công ty!';
    } else {
        // Tạo slug từ tên công ty
        $slug = createSlug($ten_cong_ty);
        
        // Kiểm tra slug đã tồn tại chưa
        $counter = 1;
        $original_slug = $slug;
        while (isSlugExists($pdo, $slug)) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }
        
        try {
            $pdo->beginTransaction();
            
            // Tạo công ty
            $cong_ty_id = createCongTy($pdo, [
                'ten_cong_ty' => $ten_cong_ty,
                'slug' => $slug,
                'ma_so_thue' => $ma_so_thue ?: null,
                'website' => $website ?: null,
                'nganh_nghe' => $nganh_nghe ?: null,
                'quy_mo' => $quy_mo ?: null,
                'dia_chi_tru_so' => $dia_chi_tru_so ?: null,
                'gioi_thieu' => $gioi_thieu ?: null
            ]);
            
            // Cập nhật công ty cho nhà tuyển dụng
            if (!$nha_td) {
                // Tạo nhà tuyển dụng nếu chưa có
                createNhaTuyenDung($pdo, [
                    'tai_khoan_id' => $_SESSION['user_id'],
                    'cong_ty_id' => $cong_ty_id,
                    'ho_ten' => null,
                    'email_cong_viec' => $_SESSION['email']
                ]);
            } else {
                // Cập nhật công ty cho nhà tuyển dụng hiện tại
                updateNhaTuyenDung($pdo, $nha_td['nha_td_id'], [
                    'cong_ty_id' => $cong_ty_id,
                    'ho_ten' => $nha_td['ho_ten'],
                    'chuc_danh' => $nha_td['chuc_danh'],
                    'email_cong_viec' => $nha_td['email_cong_viec'] ?: $_SESSION['email']
                ]);
            }
            
            $pdo->commit();
            $success = 'Đăng ký công ty thành công! Bạn sẽ được chuyển đến trang quản lý.';
            
            // Redirect sau 2 giây
            header("refresh:2;url=" . recruiterRoute('dashboard'));
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
    }
}

$page_title = 'Đăng ký công ty';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Hệ thống tuyển dụng</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Lato&family=Oswald:wght@200;300;400&display=swap" rel="stylesheet">
    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        .register-company-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
        }
        .register-company-box {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 700px;
            width: 100%;
        }
        .register-company-box h2 {
            color: #092a49;
            font-family: 'Oswald', sans-serif;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-group label {
            color: #092a49;
            font-weight: 600;
        }
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
        }
        .form-control:focus {
            border-color: #0796fe;
            box-shadow: 0 0 0 0.2rem rgba(7, 150, 254, 0.25);
        }
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="register-company-container">
        <div class="register-company-box">
            <h2><i class="fa fa-building"></i> Đăng Ký Công Ty</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php else: ?>
            
            <form method="POST" id="registerCompanyForm">
                <!-- Tên công ty -->
                <div class="form-group">
                    <label><i class="fa fa-building"></i> Tên công ty <span style="color: red;">*</span></label>
                    <input type="text" class="form-control" name="ten_cong_ty" 
                           value="<?php echo htmlspecialchars($_POST['ten_cong_ty'] ?? ''); ?>" 
                           required placeholder="Nhập tên công ty">
                </div>
                
                <!-- Mã số thuế -->
                <div class="form-group">
                    <label><i class="fa fa-id-card"></i> Mã số thuế</label>
                    <input type="text" class="form-control" name="ma_so_thue" 
                           value="<?php echo htmlspecialchars($_POST['ma_so_thue'] ?? ''); ?>" 
                           placeholder="Nhập mã số thuế (nếu có)">
                </div>
                
                <!-- Website -->
                <div class="form-group">
                    <label><i class="fa fa-globe"></i> Website</label>
                    <input type="url" class="form-control" name="website" 
                           value="<?php echo htmlspecialchars($_POST['website'] ?? ''); ?>" 
                           placeholder="https://example.com">
                </div>
                
                <!-- Ngành nghề -->
                <div class="form-group">
                    <label><i class="fa fa-industry"></i> Ngành nghề</label>
                    <input type="text" class="form-control" name="nganh_nghe" 
                           value="<?php echo htmlspecialchars($_POST['nganh_nghe'] ?? ''); ?>" 
                           placeholder="VD: Công nghệ thông tin, Marketing...">
                </div>
                
                <!-- Quy mô -->
                <div class="form-group">
                    <label><i class="fa fa-users"></i> Quy mô</label>
                    <select class="form-control" name="quy_mo">
                        <option value="">Chọn quy mô</option>
                        <option value="1-10" <?php echo (isset($_POST['quy_mo']) && $_POST['quy_mo'] == '1-10') ? 'selected' : ''; ?>>1-10 nhân viên</option>
                        <option value="11-50" <?php echo (isset($_POST['quy_mo']) && $_POST['quy_mo'] == '11-50') ? 'selected' : ''; ?>>11-50 nhân viên</option>
                        <option value="51-200" <?php echo (isset($_POST['quy_mo']) && $_POST['quy_mo'] == '51-200') ? 'selected' : ''; ?>>51-200 nhân viên</option>
                        <option value="201-500" <?php echo (isset($_POST['quy_mo']) && $_POST['quy_mo'] == '201-500') ? 'selected' : ''; ?>>201-500 nhân viên</option>
                        <option value="500+" <?php echo (isset($_POST['quy_mo']) && $_POST['quy_mo'] == '500+') ? 'selected' : ''; ?>>Trên 500 nhân viên</option>
                    </select>
                </div>
                
                <!-- Địa chỉ trụ sở -->
                <div class="form-group">
                    <label><i class="fa fa-map-marker-alt"></i> Địa chỉ trụ sở</label>
                    <input type="text" class="form-control" name="dia_chi_tru_so" 
                           value="<?php echo htmlspecialchars($_POST['dia_chi_tru_so'] ?? ''); ?>" 
                           placeholder="Nhập địa chỉ trụ sở chính">
                </div>
                
                <!-- Giới thiệu -->
                <div class="form-group">
                    <label><i class="fa fa-file-alt"></i> Giới thiệu công ty</label>
                    <textarea class="form-control" name="gioi_thieu" rows="5" 
                              placeholder="Viết giới thiệu về công ty, lịch sử, văn hóa, môi trường làm việc..."><?php echo htmlspecialchars($_POST['gioi_thieu'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-register">
                    <i class="fa fa-check"></i> Đăng ký công ty
                </button>
            </form>
            
            <div class="text-center mt-3">
                <a href="<?php echo recruiterRoute('dashboard'); ?>" style="color: #0796fe;">
                    <i class="fa fa-arrow-left"></i> Quay lại trang quản lý
                </a>
            </div>
            
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>

