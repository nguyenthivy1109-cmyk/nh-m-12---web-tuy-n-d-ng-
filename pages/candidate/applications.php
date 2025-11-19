<?php
/**
 * Trang xem đơn ứng tuyển của ứng viên
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/ung_vien.php';
require_once __DIR__ . '/../../models/ung_tuyen.php';

// Kiểm tra đăng nhập và quyền ứng viên
requireRole(ROLE_CANDIDATE);

$ung_vien = getUngVienByTaiKhoanId($pdo, $_SESSION['user_id']);

if (!$ung_vien) {
    header("Location: " . BASE_URL . "candidate/profile.php");
    exit();
}

// Lấy danh sách đơn ứng tuyển
$applications = getUngTuyenCuaUngVien($pdo, $ung_vien['ung_vien_id']);

// Map trạng thái
$status_map = [
    0 => ['text' => 'Chờ xem xét', 'class' => 'warning'],
    1 => ['text' => 'Đã xem', 'class' => 'info'],
    2 => ['text' => 'Phù hợp', 'class' => 'success'],
    3 => ['text' => 'Không phù hợp', 'class' => 'danger'],
    4 => ['text' => 'Phỏng vấn', 'class' => 'primary'],
    5 => ['text' => 'Từ chối', 'class' => 'secondary']
];

$page_title = 'Đơn ứng tuyển của tôi';
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
        .applications-container {
            max-width: 1200px;
            margin: 40px auto;
        }
        .application-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-left: 4px solid #0796fe;
        }
        .application-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        .company-logo-small {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 5px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../includes/header.php'; ?>
    
    <div class="container">
        <div class="applications-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 style="font-family: 'Oswald', sans-serif; color: #092a49;">
                    Tổng cộng: <?php echo count($applications); ?> đơn ứng tuyển
                </h3>
                <a href="index.php" class="btn btn-primary">
                    <i class="fa fa-search"></i> Tìm việc làm
                </a>
            </div>
            
            <?php if (empty($applications)): ?>
                <div class="text-center" style="padding: 60px 20px;">
                    <i class="fa fa-file-alt" style="font-size: 64px; color: #ccc; margin-bottom: 20px;"></i>
                    <h4>Bạn chưa có đơn ứng tuyển nào</h4>
                    <p class="text-muted">Hãy tìm kiếm và ứng tuyển vào các vị trí phù hợp với bạn!</p>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fa fa-search"></i> Tìm việc làm ngay
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($applications as $app): ?>
                    <div class="application-card">
                        <div class="row align-items-center">
                            <div class="col-md-1 text-center">
                                <?php if ($app['logo_url']): ?>
                                    <img src="<?php echo BASE_URL . ltrim($app['logo_url'], '/'); ?>" 
                                         alt="<?php echo htmlspecialchars($app['ten_cong_ty']); ?>" 
                                         class="company-logo-small">
                                <?php else: ?>
                                    <div class="company-logo-small" style="display: flex; align-items: center; justify-content: center;">
                                        <i class="fa fa-building" style="font-size: 24px; color: #ccc;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-7">
                                <h4 style="margin: 0 0 10px 0;">
                                    <a href="job-detail.php?id=<?php echo $app['tin_id']; ?>" 
                                       style="color: #092a49; text-decoration: none;">
                                        <?php echo htmlspecialchars($app['tieu_de']); ?>
                                    </a>
                                </h4>
                                <p style="color: #797979; margin: 0;">
                                    <i class="fa fa-building"></i> <?php echo htmlspecialchars($app['ten_cong_ty']); ?>
                                    <?php if ($app['noi_lam_viec']): ?>
                                        | <i class="fa fa-map-marker-alt"></i> <?php echo htmlspecialchars($app['noi_lam_viec']); ?>
                                    <?php endif; ?>
                                </p>
                                <p style="color: #797979; margin: 5px 0 0 0; font-size: 14px;">
                                    <i class="fa fa-calendar"></i> Nộp đơn: <?php echo formatDate($app['nop_luc'], 'd/m/Y H:i'); ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-right">
                                <span class="badge badge-<?php echo $status_map[$app['trang_thai_ut']]['class']; ?> badge-lg" 
                                      style="font-size: 14px; padding: 8px 15px;">
                                    <?php echo $status_map[$app['trang_thai_ut']]['text']; ?>
                                </span>
                                <br>
                                <?php if ($app['cap_nhat_tt_luc']): ?>
                                    <small class="text-muted">
                                        Cập nhật: <?php echo formatDate($app['cap_nhat_tt_luc'], 'd/m/Y'); ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($app['thu_ung_tuyen']): ?>
                            <div class="mt-3" style="border-top: 1px solid #e0e0e0; padding-top: 15px;">
                                <strong>Thư ứng tuyển:</strong>
                                <p style="color: #555; margin: 5px 0 0 0;">
                                    <?php echo nl2br(htmlspecialchars(mb_substr($app['thu_ung_tuyen'], 0, 200))); ?>
                                    <?php if (mb_strlen($app['thu_ung_tuyen']) > 200): ?>...<?php endif; ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>

