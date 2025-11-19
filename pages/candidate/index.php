<?php
/**
 * Trang ứng viên - Tìm kiếm việc làm
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/tin_td.php';
require_once __DIR__ . '/../../models/ky_nang.php';
require_once __DIR__ . '/../../models/ung_tuyen.php';
require_once __DIR__ . '/../../models/ung_vien.php';

// Nếu chưa đăng nhập, cho phép xem nhưng không thể ứng tuyển
$is_candidate = false;
$ung_vien = null;

if (isLoggedIn()) {
    $vai_tro_id = $_SESSION['vai_tro_id'] ?? null;
    
    // Nếu là candidate, lấy thông tin ứng viên
    if ($vai_tro_id == ROLE_CANDIDATE) {
        $is_candidate = true;
        $ung_vien = getUngVienByTaiKhoanId($pdo, $_SESSION['user_id']);
    }
    // Nếu là role khác, vẫn cho phép xem nhưng không thể ứng tuyển
}

$page_title = 'Tìm kiếm việc làm';

// $ung_vien đã được lấy ở trên

// Xử lý bộ lọc
$filters = [
    'keyword' => trim($_GET['keyword'] ?? ''),
    'location' => trim($_GET['location'] ?? ''),
    'work_type' => !empty($_GET['work_type']) ? (int)$_GET['work_type'] : '',
    'experience' => !empty($_GET['experience']) ? (int)$_GET['experience'] : '',
    'salary' => $_GET['salary'] ?? ''
];

// Parse salary filter
if (!empty($filters['salary'])) {
    if (strpos($filters['salary'], '+') !== false) {
        // Trên 30 triệu: lấy tin có lương tối thiểu >= 30 triệu
        $filters['salary_min'] = (float)str_replace('+', '', $filters['salary']) * 1000000;
    } else {
        // Khoảng lương: 0-10, 10-20, 20-30
        $salary_parts = explode('-', $filters['salary']);
        if (count($salary_parts) == 2) {
            $min = (float)$salary_parts[0] * 1000000;
            $max = (float)$salary_parts[1] * 1000000;
            
            if ($min == 0) {
                // Dưới 10 triệu: lấy tin có lương tối đa <= 10 triệu
                $filters['salary_max'] = $max;
            } else {
                // Khoảng lương: 10-20, 20-30
                $filters['salary_min'] = $min;
                $filters['salary_max'] = $max;
            }
        }
    }
}

// Phân trang
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Lấy danh sách tin tuyển dụng
$jobs = getTinTuyenDungActive($pdo, $filters, $per_page, $offset);
$total_jobs = countTinTuyenDungActive($pdo, $filters);
$total_pages = ceil($total_jobs / $per_page);

// Lấy kỹ năng cho mỗi tin
foreach ($jobs as &$job) {
    $job['ky_nang'] = getKyNangYeuCauCuaTin($pdo, $job['tin_id']);
    // Kiểm tra đã ứng tuyển chưa (chỉ nếu là candidate)
    if ($is_candidate && $ung_vien) {
        $job['da_ung_tuyen'] = hasUngTuyen($pdo, $job['tin_id'], $ung_vien['ung_vien_id']);
    } else {
        $job['da_ung_tuyen'] = false;
    }
}
unset($job);

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
    <link href="<?php echo BASE_URL; ?>css/style.css" rel="stylesheet">
    
    <!-- Đảm bảo header hiển thị đúng -->
    <style>
        /* Đồng bộ styling cho header */
        .navbar-brand {
            font-weight: 600;
            font-size: 24px;
        }
        .nav-link.active {
            color: #0796fe !important;
            font-weight: 600;
        }
        .top-bar {
            background: #092a49;
            color: #fff;
        }
        .top-bar .text h2 {
            color: #0796fe;
            font-size: 20px;
            margin: 0;
        }
        .top-bar .text p {
            margin: 0;
            font-size: 14px;
        }
        .top-bar .social a {
            color: #fff;
            margin: 0 5px;
            font-size: 18px;
        }
        .top-bar .social a:hover {
            color: #0796fe;
        }
        
        .candidate-header {
            background: #0796fe;
            color: #fff;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        
        .candidate-header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .candidate-header h2 {
            font-family: 'Oswald', sans-serif;
            margin: 0;
            font-size: 28px;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-menu .user-name {
            color: #fff;
            font-weight: 600;
        }
        
        .user-menu .btn-logout {
            padding: 8px 20px;
            background: rgba(255,255,255,0.2);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .user-menu .btn-logout:hover {
            background: rgba(255,255,255,0.3);
            color: #fff;
        }
        
        .search-section {
            background: #fff;
            padding: 24px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .search-section h3 {
            font-family: 'Oswald', sans-serif;
            color: #092a49;
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .search-section .form-group {
            margin-bottom: 15px;
        }
        
        .search-section .form-group label {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            display: block;
        }
        
        .search-section .form-group label i {
            color: #0796fe;
            margin-right: 6px;
            width: 16px;
        }
        
        .search-section .form-control {
            font-size: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            transition: all 0.3s;
            color: #333;
            background-color: #fff;
        }
        
        .search-section .form-control:focus {
            border-color: #0796fe;
            box-shadow: 0 0 0 0.2rem rgba(7, 150, 254, 0.15);
            outline: none;
            color: #333;
            background-color: #fff;
        }
        
        .search-section select.form-control {
            color: #333 !important;
            background-color: #fff !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23333' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
            padding-right: 2.5rem;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }
        
        .search-section select.form-control option {
            color: #333;
            background-color: #fff;
            padding: 8px;
        }
        
        .btn-search {
            background: #0796fe;
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
            font-size: 14px;
        }
        
        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(7, 150, 254, 0.3);
        }
        
        .job-list {
            margin-top: 0;
        }
        
        .job-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .job-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            transform: translateY(-3px);
        }
        
        .job-card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .job-card-header .company-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .company-logo {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #0796fe;
        }
        
        .company-details h4 {
            font-family: 'Oswald', sans-serif;
            color: #092a49;
            margin: 0 0 5px 0;
            font-size: 20px;
        }
        
        .company-details .company-name {
            color: #797979;
            font-size: 14px;
        }
        
        .job-badge {
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
        
        .job-details {
            margin: 15px 0;
        }
        
        .job-details .detail-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 10px;
            color: #797979;
            font-size: 14px;
        }
        
        .job-details .detail-item i {
            color: #0796fe;
            margin-right: 5px;
        }
        
        .job-description {
            color: #555;
            margin: 15px 0;
            line-height: 1.6;
        }
        
        .job-skills {
            margin: 15px 0;
        }
        
        .job-skills .skill-tag {
            display: inline-block;
            padding: 5px 12px;
            background: #f0f0f0;
            color: #092a49;
            border-radius: 20px;
            font-size: 12px;
            margin: 5px 5px 5px 0;
        }
        
        .job-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }
        
        .job-salary {
            font-size: 18px;
            font-weight: 600;
            color: #0796fe;
        }
        
        .btn-apply {
            background: #0796fe;
            color: #fff;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-apply:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(7, 150, 254, 0.3);
            color: #fff;
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #797979;
        }
        
        .no-results i {
            font-size: 64px;
            color: #e0e0e0;
            margin-bottom: 20px;
        }
        
        /* Sidebar Sticky - Search Form */
        .col-md-3 {
            position: relative;
        }
        
        .search-section {
            position: sticky;
            top: 100px;
            max-height: calc(100vh - 120px);
            overflow-y: auto;
            z-index: 100;
        }
        
        /* Scrollbar styling cho search section */
        .search-section::-webkit-scrollbar {
            width: 6px;
        }
        
        .search-section::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .search-section::-webkit-scrollbar-thumb {
            background: #0796fe;
            border-radius: 10px;
        }
        
        .search-section::-webkit-scrollbar-thumb:hover {
            background: #0684e0;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../includes/header.php'; ?>

    <div class="container">
        <div class="row">
            <!-- Sidebar - Search Form -->
            <div class="col-md-3">
                <div class="search-section">
                    <h3><i class="fa fa-search"></i> Tìm kiếm việc làm</h3>
                    <form class="search-form" method="GET" action="">
                        <div class="form-group">
                            <label><i class="fa fa-briefcase"></i> Từ khóa</label>
                            <input type="text" class="form-control" name="keyword" placeholder="VD: PHP Developer..." value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label><i class="fa fa-map-marker-alt"></i> Địa điểm</label>
                            <input type="text" class="form-control" name="location" placeholder="VD: Hà Nội, TP.HCM..." value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label><i class="fa fa-dollar-sign"></i> Mức lương</label>
                            <select class="form-control" name="salary">
                                <option value="" <?php echo empty($filters['salary']) ? 'selected' : ''; ?>>Tất cả</option>
                                <option value="0-10" <?php echo $filters['salary'] == '0-10' ? 'selected' : ''; ?>>Dưới 10 triệu</option>
                                <option value="10-20" <?php echo $filters['salary'] == '10-20' ? 'selected' : ''; ?>>10 - 20 triệu</option>
                                <option value="20-30" <?php echo $filters['salary'] == '20-30' ? 'selected' : ''; ?>>20 - 30 triệu</option>
                                <option value="30+" <?php echo $filters['salary'] == '30+' ? 'selected' : ''; ?>>Trên 30 triệu</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fa fa-clock"></i> Hình thức</label>
                            <select class="form-control" name="work_type">
                                <option value="" <?php echo empty($filters['work_type']) ? 'selected' : ''; ?>>Tất cả</option>
                                <option value="1" <?php echo (isset($_GET['work_type']) && $_GET['work_type'] == '1') || $filters['work_type'] == 1 ? 'selected' : ''; ?>>Full-time</option>
                                <option value="2" <?php echo (isset($_GET['work_type']) && $_GET['work_type'] == '2') || $filters['work_type'] == 2 ? 'selected' : ''; ?>>Part-time</option>
                                <option value="3" <?php echo (isset($_GET['work_type']) && $_GET['work_type'] == '3') || $filters['work_type'] == 3 ? 'selected' : ''; ?>>Remote</option>
                                <option value="4" <?php echo (isset($_GET['work_type']) && $_GET['work_type'] == '4') || $filters['work_type'] == 4 ? 'selected' : ''; ?>>Freelance</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fa fa-graduation-cap"></i> Kinh nghiệm</label>
                            <select class="form-control" name="experience">
                                <option value="" <?php echo empty($filters['experience']) ? 'selected' : ''; ?>>Tất cả</option>
                                <option value="1" <?php echo (isset($_GET['experience']) && $_GET['experience'] == '1') || $filters['experience'] == 1 ? 'selected' : ''; ?>>Mới tốt nghiệp</option>
                                <option value="2" <?php echo (isset($_GET['experience']) && $_GET['experience'] == '2') || $filters['experience'] == 2 ? 'selected' : ''; ?>>1-3 năm</option>
                                <option value="3" <?php echo (isset($_GET['experience']) && $_GET['experience'] == '3') || $filters['experience'] == 3 ? 'selected' : ''; ?>>3-5 năm</option>
                                <option value="4" <?php echo (isset($_GET['experience']) && $_GET['experience'] == '4') || $filters['experience'] == 4 ? 'selected' : ''; ?>>5+ năm</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-search">
                            <i class="fa fa-search"></i> Tìm kiếm
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9">
                <!-- Job List -->
                <div class="job-list">
                    <h4 style="font-family: 'Oswald', sans-serif; color: #092a49; margin-bottom: 20px;">
                        <i class="fa fa-list"></i> Danh sách việc làm 
                        <span class="badge badge-info"><?php echo $total_jobs; ?> tin</span>
                    </h4>
                    
                    <?php if (empty($jobs)): ?>
                        <div class="no-results" style="text-align: center; padding: 40px;">
                            <i class="fa fa-search" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
                            <h4>Không tìm thấy việc làm nào</h4>
                            <p>Hãy thử thay đổi từ khóa tìm kiếm hoặc bộ lọc</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($jobs as $job): ?>
                            <div class="job-card" style="margin-bottom: 20px;">
                                <div class="job-card-header">
                                    <div class="company-info">
                                        <div class="company-logo">
                                            <?php if ($job['logo_url']): ?>
                                                <img src="<?php echo BASE_URL . ltrim($job['logo_url'], '/'); ?>" alt="<?php echo htmlspecialchars($job['ten_cong_ty']); ?>" style="width: 60px; height: 60px; object-fit: contain;">
                                            <?php else: ?>
                                                <i class="fa fa-building"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="company-details">
                                            <h4>
                                                <a href="<?php echo candidateRoute('job-detail', ['id' => $job['tin_id']]); ?>" style="color: #092a49; text-decoration: none;">
                                                    <?php echo htmlspecialchars($job['tieu_de']); ?>
                                                </a>
                                            </h4>
                                            <div class="company-name"><?php echo htmlspecialchars($job['ten_cong_ty']); ?></div>
                                        </div>
                                    </div>
                                    <span class="job-badge badge-active">Đang tuyển</span>
                                </div>
                                
                                <div class="job-details">
                                    <?php if ($job['noi_lam_viec']): ?>
                                        <span class="detail-item">
                                            <i class="fa fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['noi_lam_viec']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($job['hinh_thuc_lv']): ?>
                                        <span class="detail-item">
                                            <i class="fa fa-clock"></i> <?php echo $work_type_map[$job['hinh_thuc_lv']] ?? ''; ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($job['cap_do_kn']): ?>
                                        <span class="detail-item">
                                            <i class="fa fa-graduation-cap"></i> <?php echo $exp_level_map[$job['cap_do_kn']] ?? ''; ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($job['dang_luc']): ?>
                                        <span class="detail-item">
                                            <i class="fa fa-calendar"></i> <?php echo formatDate($job['dang_luc'], 'd/m/Y'); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="job-description">
                                    <?php echo htmlspecialchars(mb_substr($job['mo_ta'], 0, 200)) . '...'; ?>
                                </div>
                                
                                <?php if (!empty($job['ky_nang'])): ?>
                                    <div class="job-skills">
                                        <?php foreach (array_slice($job['ky_nang'], 0, 8) as $kn): ?>
                                            <span class="skill-tag"><?php echo htmlspecialchars($kn['ten_kn']); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="job-card-footer">
                                    <div class="job-salary">
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
                                    </div>
                                    <?php if ($is_candidate && isset($job['da_ung_tuyen']) && $job['da_ung_tuyen']): ?>
                                        <a href="<?php echo candidateRoute('job-detail', ['id' => $job['tin_id']]); ?>" class="btn-apply" style="background: #28a745;">
                                            <i class="fa fa-check"></i> Đã ứng tuyển
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo candidateRoute('job-detail', ['id' => $job['tin_id']]); ?>" class="btn-apply">
                                            <i class="fa fa-eye"></i> <?php echo $is_candidate ? 'Xem chi tiết' : 'Xem tin tuyển dụng'; ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Phân trang -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Phân trang">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Trước</a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Sau</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer" style="margin-top: 50px;">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <p>&copy; 2024 Job Portal. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>

