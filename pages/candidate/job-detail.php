<?php
/**
 * Trang chi tiết tin tuyển dụng
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/tin_td.php';
require_once __DIR__ . '/../../models/ky_nang.php';
require_once __DIR__ . '/../../models/ung_tuyen.php';
require_once __DIR__ . '/../../models/ung_vien.php';

// Cho phép xem ngay cả khi chưa đăng nhập
$tin_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$is_candidate = false;
$ung_vien = null;
$da_ung_tuyen = false;

if (isLoggedIn()) {
    $vai_tro_id = $_SESSION['vai_tro_id'] ?? null;
    
    if ($vai_tro_id == ROLE_CANDIDATE) {
        $is_candidate = true;
        $ung_vien = getUngVienByTaiKhoanId($pdo, $_SESSION['user_id']);
        
        if ($ung_vien && $tin_id) {
            $da_ung_tuyen = hasUngTuyen($pdo, $tin_id, $ung_vien['ung_vien_id']);
        }
    }
}

if (!$tin_id) {
    header("Location: " . candidateRoute('dashboard'));
    exit();
}

// Lấy chi tiết tin tuyển dụng
$job = getTinTuyenDungDetail($pdo, $tin_id);

if (!$job || $job['trang_thai_tin'] != 1) {
    header("Location: " . candidateRoute('dashboard'));
    exit();
}

// Lấy kỹ năng yêu cầu
$ky_nang = getKyNangYeuCauCuaTin($pdo, $tin_id);

// $ung_vien và $da_ung_tuyen đã được xử lý ở trên

// Map trạng thái
$work_type_map = [
    1 => 'Full-time',
    2 => 'Part-time',
    3 => 'Remote',
    4 => 'Freelance'
];

$exp_level_map = [
    1 => 'Mới tốt nghiệp',
    2 => '1-3 năm',
    3 => '3-5 năm',
    4 => '5+ năm'
];

$skill_importance_map = [
    1 => 'Bắt buộc',
    2 => 'Quan trọng',
    3 => 'Mong muốn'
];

$page_title = htmlspecialchars($job['tieu_de']);
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
    <link href="../css/style.css" rel="stylesheet">
    
    <style>
        .job-detail-content {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .company-logo-large {
            width: 100px;
            height: 100px;
            object-fit: contain;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 10px;
        }
        .skill-badge {
            display: inline-block;
            padding: 8px 15px;
            margin: 5px;
            background: #f0f8ff;
            border: 1px solid #0796fe;
            border-radius: 20px;
            font-size: 14px;
        }
        .skill-badge.required {
            background: #fff0f0;
            border-color: #dc3545;
        }
        .skill-badge.important {
            background: #fff8e1;
            border-color: #ffc107;
        }
        .btn-apply-large {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-apply-large:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        /* Thông tin chi tiết - không xuống dòng */
        .job-detail-content table {
            width: 100%;
        }
        
        .job-detail-content table tr {
            white-space: nowrap;
        }
        
        .job-detail-content table td {
            white-space: nowrap;
            padding: 10px 5px;
            vertical-align: middle;
        }
        
        .job-detail-content table td:first-child {
            width: 40%;
            min-width: 140px;
            padding-right: 15px;
        }
        
        .job-detail-content table td:last-child {
            width: 60%;
            white-space: normal;
            word-break: break-word;
        }
        
        .job-detail-content table td i {
            margin-right: 8px;
            width: 20px;
            text-align: center;
        }
    </style>
  </head>
  <body>
    <?php include __DIR__ . '/../../includes/header.php'; ?>
    
    <div class="container">
        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <h1 style="font-family: 'Oswald', sans-serif; margin: 0; color: #1e3c72;">
                    <?php echo htmlspecialchars($job['tieu_de']); ?>
                </h1>
                <p style="margin: 10px 0 0 0; font-size: 18px; color: #6b7280;">
                    <i class="fa fa-building"></i> <?php echo htmlspecialchars($job['ten_cong_ty']); ?>
                </p>
            </div>
            <div class="col-md-4 text-right">
                <?php if (!$is_candidate): ?>
                    <a href="<?php echo publicRoute('login'); ?>" class="btn-apply-large">
                        <i class="fa fa-sign-in-alt"></i> Đăng nhập để ứng tuyển
                    </a>
                <?php elseif ($da_ung_tuyen): ?>
                    <a href="<?php echo candidateRoute('apply', ['id' => $tin_id]); ?>" class="btn-apply-large" style="background: #28a745;">
                        <i class="fa fa-check"></i> Đã ứng tuyển
                    </a>
                <?php else: ?>
                    <a href="<?php echo candidateRoute('apply', ['id' => $tin_id]); ?>" class="btn-apply-large">
                        <i class="fa fa-paper-plane"></i> Ứng tuyển ngay
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <!-- Main Content -->
            <div class="col-md-8">
                <!-- Thông tin công việc -->
                <div class="job-detail-content">
                    <h3 style="font-family: 'Oswald', sans-serif; color: #092a49; margin-bottom: 20px;">
                        <i class="fa fa-info-circle"></i> Mô tả công việc
                    </h3>
                    <div style="line-height: 1.8; color: #555;">
                        <?php echo nl2br(htmlspecialchars($job['mo_ta'])); ?>
                    </div>
                </div>
                
                <!-- Yêu cầu -->
                <?php if ($job['yeu_cau']): ?>
                <div class="job-detail-content">
                    <h3 style="font-family: 'Oswald', sans-serif; color: #092a49; margin-bottom: 20px;">
                        <i class="fa fa-list-check"></i> Yêu cầu công việc
                    </h3>
                    <div style="line-height: 1.8; color: #555;">
                        <?php echo nl2br(htmlspecialchars($job['yeu_cau'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Kỹ năng yêu cầu -->
                <?php if (!empty($ky_nang)): ?>
                <div class="job-detail-content">
                    <h3 style="font-family: 'Oswald', sans-serif; color: #092a49; margin-bottom: 20px;">
                        <i class="fa fa-cogs"></i> Kỹ năng yêu cầu
                    </h3>
                    <div>
                        <?php foreach ($ky_nang as $kn): ?>
                            <span class="skill-badge <?php 
                                echo $kn['muc_quan_trong'] == 1 ? 'required' : 
                                    ($kn['muc_quan_trong'] == 2 ? 'important' : ''); 
                            ?>">
                                <?php echo htmlspecialchars($kn['ten_kn']); ?>
                                <?php if ($kn['muc_quan_trong'] == 1): ?>
                                    <span style="color: #dc3545;">*</span>
                                <?php endif; ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Thông tin công ty -->
                <div class="job-detail-content">
                    <h4 style="font-family: 'Oswald', sans-serif; color: #092a49; margin-bottom: 20px;">
                        <i class="fa fa-building"></i> Thông tin công ty
                    </h4>
                    <div class="text-center mb-3">
                        <?php if ($job['logo_url']): ?>
                            <img src="<?php echo BASE_URL . ltrim($job['logo_url'], '/'); ?>" 
                                 alt="<?php echo htmlspecialchars($job['ten_cong_ty']); ?>" 
                                 class="company-logo-large">
                        <?php else: ?>
                            <div class="company-logo-large" style="display: flex; align-items: center; justify-content: center;">
                                <i class="fa fa-building" style="font-size: 48px; color: #ccc;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h5 style="text-align: center; color: #092a49;">
                        <?php echo htmlspecialchars($job['ten_cong_ty']); ?>
                    </h5>
                    <?php if ($job['cong_ty_gioi_thieu']): ?>
                        <p style="color: #797979; font-size: 14px; text-align: center;">
                            <?php echo htmlspecialchars(mb_substr($job['cong_ty_gioi_thieu'], 0, 150)) . '...'; ?>
                        </p>
                    <?php endif; ?>
                    <?php if ($job['website']): ?>
                        <p class="text-center">
                            <a href="<?php echo htmlspecialchars($job['website']); ?>" target="_blank" style="color: #0796fe;">
                                <i class="fa fa-globe"></i> Website công ty
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
                
                <!-- Thông tin chi tiết -->
                <div class="job-detail-content">
                    <h4 style="font-family: 'Oswald', sans-serif; color: #092a49; margin-bottom: 20px;">
                        <i class="fa fa-info"></i> Thông tin chi tiết
                    </h4>
                    <table class="table table-borderless">
                        <?php if ($job['noi_lam_viec']): ?>
                        <tr>
                            <td><i class="fa fa-map-marker-alt text-primary"></i> <strong>Địa điểm:</strong></td>
                            <td><?php echo htmlspecialchars($job['noi_lam_viec']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($job['hinh_thuc_lv']): ?>
                        <tr>
                            <td><i class="fa fa-clock text-primary"></i> <strong>Hình thức:</strong></td>
                            <td><?php echo $work_type_map[$job['hinh_thuc_lv']] ?? ''; ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($job['cap_do_kn']): ?>
                        <tr>
                            <td><i class="fa fa-graduation-cap text-primary"></i> <strong>Kinh nghiệm:</strong></td>
                            <td><?php echo $exp_level_map[$job['cap_do_kn']] ?? ''; ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td><i class="fa fa-dollar-sign text-primary"></i> <strong>Mức lương:</strong></td>
                            <td>
                                <?php 
                                if ($job['luong_min'] && $job['luong_max']) {
                                    echo formatCurrency($job['luong_min'], $job['tien_te']) . ' - ' . formatCurrency($job['luong_max'], $job['tien_te']);
                                } elseif ($job['luong_min']) {
                                    echo 'Từ ' . formatCurrency($job['luong_min'], $job['tien_te']);
                                } elseif ($job['luong_max']) {
                                    echo 'Đến ' . formatCurrency($job['luong_max'], $job['tien_te']);
                                } else {
                                    echo 'Thỏa thuận';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php if ($job['so_luong']): ?>
                        <tr>
                            <td><i class="fa fa-users text-primary"></i> <strong>Số lượng:</strong></td>
                            <td><?php echo $job['so_luong']; ?> người</td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($job['dang_luc']): ?>
                        <tr>
                            <td><i class="fa fa-calendar text-primary"></i> <strong>Ngày đăng:</strong></td>
                            <td><?php echo formatDate($job['dang_luc'], 'd/m/Y H:i'); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($job['het_han_luc']): ?>
                        <tr>
                            <td><i class="fa fa-calendar-times text-primary"></i> <strong>Hạn nộp:</strong></td>
                            <td><?php echo formatDate($job['het_han_luc'], 'd/m/Y'); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
                
                <!-- Nút ứng tuyển -->
                <div class="text-center">
                    <?php if (!$is_candidate): ?>
                        <div class="alert alert-warning" style="margin-bottom: 15px;">
                            <i class="fa fa-info-circle"></i> Bạn cần <a href="<?php echo publicRoute('login'); ?>" style="color: #0796fe; font-weight: 600;">đăng nhập</a> với tài khoản ứng viên để ứng tuyển.
                        </div>
                        <a href="<?php echo publicRoute('login'); ?>" class="btn-apply-large" style="width: 100%;">
                            <i class="fa fa-sign-in-alt"></i> Đăng nhập để ứng tuyển
                        </a>
                    <?php elseif ($da_ung_tuyen): ?>
                        <a href="<?php echo candidateRoute('apply', ['id' => $tin_id]); ?>" class="btn-apply-large" style="background: #28a745; width: 100%;">
                            <i class="fa fa-check"></i> Xem đơn ứng tuyển
                        </a>
                    <?php else: ?>
                        <a href="<?php echo candidateRoute('apply', ['id' => $tin_id]); ?>" class="btn-apply-large" style="width: 100%;">
                            <i class="fa fa-paper-plane"></i> Ứng tuyển ngay
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Back button -->
        <div class="text-center mt-4 mb-4">
            <a href="<?php echo candidateRoute('dashboard'); ?>" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>

