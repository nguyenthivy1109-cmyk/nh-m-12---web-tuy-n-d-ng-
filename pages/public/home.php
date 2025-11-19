<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/tin_td.php';
require_once __DIR__ . '/../../models/ky_nang.php';

// Kiểm tra nếu đã đăng nhập, redirect về dashboard theo vai trò
// Chỉ redirect khi không có tham số ?home=1 (khi người dùng click "Trang chủ" từ menu)
// Ứng viên (ROLE_CANDIDATE) sẽ luôn ở lại trang home
if (isLoggedIn() && !isset($_GET['home'])) {
    $vai_tro_id = $_SESSION['vai_tro_id'] ?? null;
    
    if ($vai_tro_id == ROLE_ADMIN) {
        header("Location: " . adminRoute('dashboard'));
        exit();
    } elseif ($vai_tro_id == ROLE_RECRUITER) {
        header("Location: " . recruiterRoute('dashboard'));
        exit();
    }
    // Ứng viên (ROLE_CANDIDATE) sẽ ở lại trang home index.php
}

// Lấy danh sách tin tuyển dụng mới nhất (6 tin)
$recent_jobs = getTinTuyenDungActive($pdo, [], 6, 0);

// Lấy kỹ năng cho mỗi tin
foreach ($recent_jobs as &$job) {
    $job['ky_nang'] = getKyNangYeuCauCuaTin($pdo, $job['tin_id']);
}
unset($job);

// Lấy thống kê
$stats = [
    'total_jobs' => 0,
    'total_companies' => 0,
    'total_candidates' => 0
];

// Đếm số việc làm đang active
$stmt = $pdo->query("SELECT COUNT(*) FROM tin_td WHERE xoa_luc IS NULL AND trang_thai_tin = 1 AND (het_han_luc IS NULL OR het_han_luc >= CURDATE())");
$stats['total_jobs'] = (int)$stmt->fetchColumn();

// Đếm số công ty
$stmt = $pdo->query("SELECT COUNT(*) FROM cong_tys WHERE xoa_luc IS NULL");
$stats['total_companies'] = (int)$stmt->fetchColumn();

// Đếm số ứng viên
$stmt = $pdo->query("SELECT COUNT(*) FROM ung_viens uv INNER JOIN tai_khoans tk ON uv.tai_khoan_id = tk.tai_khoan_id WHERE tk.xoa_luc IS NULL");
$stats['total_candidates'] = (int)$stmt->fetchColumn();

// Map trạng thái
$work_type_map = [
    1 => 'Full-time',
    2 => 'Part-time',
    3 => 'Remote',
    4 => 'Freelance'
];
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>Tìm việc làm nhanh chóng - Job Portal</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta content="tìm việc làm, tuyển dụng, việc làm, job portal, recruitment" name="keywords" />
    <meta
      content="Tìm kiếm việc làm phù hợp với kỹ năng và kinh nghiệm của bạn. Hàng nghìn cơ hội việc làm từ các công ty hàng đầu."
      name="description"
    />

    <!-- Favicon -->
    <link href="<?php echo BASE_URL; ?>assets/public/img/favicon.ico" rel="icon" />

    <!-- Google Font -->
    <link
      href="https://fonts.googleapis.com/css2?family=Lato&family=Oswald:wght@200;300;400&display=swap"
      rel="stylesheet"
    />

    <!-- CSS Libraries -->
    <link
      href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css"
      rel="stylesheet"
    />
    <link href="<?php echo BASE_URL; ?>assets/public/lib/animate/animate.min.css" rel="stylesheet" />
    <link href="<?php echo BASE_URL; ?>assets/public/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet" />

    <!-- Template Stylesheet -->
    <link href="<?php echo BASE_URL; ?>assets/public/css/style.css" rel="stylesheet" />
    
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
        
        /* Fix navbar đè lên container - navbar có position absolute nên cần điều chỉnh */
        @media (min-width: 992px) {
            /* Navbar absolute với top: 35px và có padding, cần tính chiều cao navbar
               Top bar: 35px, Navbar: ~80px (với padding), tổng ~115px */
            /* Đảm bảo carousel không bị đè bởi navbar absolute */
            .carousel .container-fluid {
                position: relative;
                z-index: 1;
            }
        }
        
        @media (max-width: 991.98px) {
            /* Trên mobile, navbar là relative nên không cần margin */
            .carousel {
                margin-top: 0;
            }
        }
        
        /* Đảm bảo các section sau carousel không bị đè */
        .fact,
        .about,
        .service,
        .feature,
        .testimonial,
        .team,
        .blog,
        .contact {
            position: relative;
            z-index: 1;
        }
        
        /* Hover effects cho job cards */
        .job-card-modern:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
            border-color: #2563eb !important;
        }
        
        .job-card-modern:hover h3 a {
            color: #2563eb !important;
        }
        
        /* Hover effects cho feature cards */
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
            border-color: #e5e7eb !important;
        }
        
        /* Button hover effects */
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.4) !important;
        }
        
        /* Input focus effects */
        .form-control:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
        }
        
        /* Responsive cho hero section */
        @media (max-width: 991.98px) {
            .hero-section {
                padding: 80px 0 60px !important;
            }
            .hero-content h1 {
                font-size: 36px !important;
            }
            .hero-content p {
                font-size: 18px !important;
            }
            .hero-stats {
                gap: 20px !important;
            }
            .hero-stats h3 {
                font-size: 28px !important;
            }
        }
        
        @media (max-width: 767.98px) {
            .hero-content h1 {
                font-size: 28px !important;
            }
            .hero-search-box {
                padding: 25px !important;
                margin-top: 30px;
            }
        }
        
        /* Section backgrounds */
        .why-choose-us {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .jobs-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .contact {
            display: none;
        }
        
        /* Section header styles */
        .section-header.text-center {
            margin-bottom: 60px;
        }
        
        .section-header.text-center > p:first-child {
            color: #2563eb;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }
        
        .section-header.text-center > h2 {
            color: #092a49;
            font-size: 40px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .section-header.text-center > p:last-child {
            color: #797979;
            font-size: 16px;
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Feature card styles */
        .feature-card {
            background: white;
            padding: 40px 30px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .feature-card .icon-box {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
        }
        
        .feature-card .icon-box.blue {
            background: #2563eb;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.3);
        }
        
        .feature-card .icon-box.pink {
            background: #ec4899;
            box-shadow: 0 10px 25px -5px rgba(236, 72, 153, 0.3);
        }
        
        .feature-card .icon-box.cyan {
            background: #06b6d4;
            box-shadow: 0 10px 25px -5px rgba(6, 182, 212, 0.3);
        }
        
        .feature-card .icon-box.green {
            background: #10b981;
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.3);
        }
        
        .feature-card .icon-box i {
            font-size: 36px;
            color: white;
        }
        
        .feature-card h4 {
            color: #092a49;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .feature-card p {
            color: #797979;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }
        
        .how-it-works {
            padding: 80px 0;
            background: white;
        }
        
        /* Step card styles */
        .step-card {
            text-align: center;
            position: relative;
        }
        
        .step-card .step-number {
            width: 100px;
            height: 100px;
            background: #2563eb;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            position: relative;
            z-index: 2;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.3);
        }
        
        .step-card .step-number span {
            font-size: 48px;
            font-weight: 700;
            color: white;
        }
        
        .step-card h4 {
            color: #092a49;
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .step-card p {
            color: #797979;
            font-size: 15px;
            line-height: 1.6;
            max-width: 280px;
            margin: 0 auto;
        }
        
        /* Decorative background with SVG wave */
        .svg-wave-bg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;charset=utf-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
            opacity: 0.3;
        }
    </style>
  </head>

  <body>
    <?php include __DIR__ . '/../../includes/header.php'; ?>

    <!-- Hero Section Start -->
    <div class="hero-section" style="background: #2563eb; padding: 120px 0 80px; position: relative; overflow: hidden;">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6">
            <div class="hero-content" style="color: white; z-index: 2; position: relative;">
              <h1 style="font-size: 48px; font-weight: 700; margin-bottom: 20px; line-height: 1.2;">
                Tìm việc làm mơ ước của bạn
              </h1>
              <p style="font-size: 20px; margin-bottom: 30px; opacity: 0.95;">
                Hàng nghìn cơ hội việc làm từ các công ty hàng đầu. Tìm kiếm và ứng tuyển ngay hôm nay!
              </p>
              <div class="hero-stats" style="display: flex; gap: 30px; margin-bottom: 40px; flex-wrap: wrap;">
                <div>
                  <h3 style="font-size: 36px; font-weight: 700; margin: 0;"><?php echo number_format($stats['total_jobs']); ?>+</h3>
                  <p style="margin: 5px 0 0 0; opacity: 0.9;">Việc làm</p>
                </div>
                <div>
                  <h3 style="font-size: 36px; font-weight: 700; margin: 0;"><?php echo number_format($stats['total_companies']); ?>+</h3>
                  <p style="margin: 5px 0 0 0; opacity: 0.9;">Công ty</p>
                </div>
                <div>
                  <h3 style="font-size: 36px; font-weight: 700; margin: 0;"><?php echo number_format($stats['total_candidates']); ?>+</h3>
                  <p style="margin: 5px 0 0 0; opacity: 0.9;">Ứng viên</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="hero-search-box" style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); z-index: 2; position: relative;">
              <h3 style="color: #092a49; margin-bottom: 25px; font-size: 24px; font-weight: 600;">
                <i class="fa fa-search" style="color: #2563eb; margin-right: 10px;"></i>Tìm kiếm việc làm
              </h3>
              <form action="<?php echo BASE_URL; ?>candidate/index.php" method="GET">
                <div class="form-group">
                  <label style="color: #092a49; font-weight: 600; margin-bottom: 8px;">
                    <i class="fa fa-briefcase" style="color: #2563eb; margin-right: 5px;"></i>Từ khóa
                  </label>
                  <input type="text" name="keyword" class="form-control" placeholder="VD: Lập trình viên, Marketing..." 
                         style="height: 50px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 15px; transition: all 0.3s;"
                         value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
                </div>
                <div class="form-group">
                  <label style="color: #092a49; font-weight: 600; margin-bottom: 8px;">
                    <i class="fa fa-map-marker-alt" style="color: #2563eb; margin-right: 5px;"></i>Địa điểm
                  </label>
                  <input type="text" name="location" class="form-control" placeholder="VD: Hà Nội, TP.HCM..." 
                         style="height: 50px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 15px; transition: all 0.3s;"
                         value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>">
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label style="color: #092a49; font-weight: 600; margin-bottom: 8px;">
                        <i class="fa fa-clock" style="color: #2563eb; margin-right: 5px;"></i>Hình thức
                      </label>
                      <select name="work_type" class="form-control" style="height: 50px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 15px; transition: all 0.3s;">
                        <option value="">Tất cả</option>
                        <option value="1" <?php echo (isset($_GET['work_type']) && $_GET['work_type'] == '1') ? 'selected' : ''; ?>>Full-time</option>
                        <option value="2" <?php echo (isset($_GET['work_type']) && $_GET['work_type'] == '2') ? 'selected' : ''; ?>>Part-time</option>
                        <option value="3" <?php echo (isset($_GET['work_type']) && $_GET['work_type'] == '3') ? 'selected' : ''; ?>>Remote</option>
                        <option value="4" <?php echo (isset($_GET['work_type']) && $_GET['work_type'] == '4') ? 'selected' : ''; ?>>Freelance</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label style="color: #092a49; font-weight: 600; margin-bottom: 8px;">
                        <i class="fa fa-money-bill-wave" style="color: #2563eb; margin-right: 5px;"></i>Mức lương
                      </label>
                      <select name="salary" class="form-control" style="height: 50px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 15px; transition: all 0.3s;">
                        <option value="">Tất cả</option>
                        <option value="0-10" <?php echo (isset($_GET['salary']) && $_GET['salary'] == '0-10') ? 'selected' : ''; ?>>Dưới 10 triệu</option>
                        <option value="10-20" <?php echo (isset($_GET['salary']) && $_GET['salary'] == '10-20') ? 'selected' : ''; ?>>10 - 20 triệu</option>
                        <option value="20-30" <?php echo (isset($_GET['salary']) && $_GET['salary'] == '20-30') ? 'selected' : ''; ?>>20 - 30 triệu</option>
                        <option value="30+" <?php echo (isset($_GET['salary']) && $_GET['salary'] == '30+') ? 'selected' : ''; ?>>Trên 30 triệu</option>
                      </select>
                    </div>
                  </div>
                </div>
                <button type="submit" class="btn btn-block" 
                        style="background: #2563eb; color: white; height: 55px; font-size: 16px; font-weight: 600; border-radius: 12px; margin-top: 10px; border: none; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3); transition: all 0.3s;">
                  <i class="fa fa-search"></i> Tìm kiếm ngay
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
      <div class="svg-wave-bg"></div>
    </div>
    <!-- Hero Section End -->

    <!-- Why Choose Us Start -->
    <div class="why-choose-us">
      <div class="container">
        <div class="section-header text-center">
          <p>TẠI SAO CHỌN CHÚNG TÔI</p>
          <h2>Nền tảng tuyển dụng hàng đầu</h2>
          <p>
            Kết nối ứng viên với cơ hội việc làm tốt nhất từ các công ty uy tín
          </p>
        </div>
        <div class="row">
          <div class="col-lg-3 col-md-6 mb-4">
            <div class="feature-card">
              <div class="icon-box blue">
                <i class="fa fa-briefcase"></i>
              </div>
              <h4>Hàng nghìn việc làm</h4>
              <p>
                Cập nhật liên tục các vị trí tuyển dụng mới từ các công ty hàng đầu
              </p>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 mb-4">
            <div class="feature-card">
              <div class="icon-box pink">
                <i class="fa fa-building"></i>
              </div>
              <h4>Công ty uy tín</h4>
              <p>
                Kết nối với các công ty hàng đầu trong nhiều lĩnh vực khác nhau
              </p>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 mb-4">
            <div class="feature-card">
              <div class="icon-box cyan">
                <i class="fa fa-search"></i>
              </div>
              <h4>Tìm kiếm thông minh</h4>
              <p>
                Bộ lọc mạnh mẽ giúp bạn tìm việc phù hợp nhất với kỹ năng và kinh nghiệm
              </p>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 mb-4">
            <div class="feature-card">
              <div class="icon-box green">
                <i class="fa fa-user-check"></i>
              </div>
              <h4>Ứng tuyển dễ dàng</h4>
              <p>
                Quy trình ứng tuyển đơn giản, nhanh chóng chỉ với vài cú click
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Why Choose Us End -->

    <!-- How It Works Start -->
    <div class="how-it-works">
      <div class="container">
        <div class="section-header text-center">
          <p>CÁCH THỨC HOẠT ĐỘNG</p>
          <h2>Tìm việc làm chỉ trong 3 bước</h2>
        </div>
        <div class="row">
          <div class="col-lg-4 col-md-6 mb-4">
            <div class="step-card">
              <div class="step-number">
                <span>1</span>
              </div>
              <h4>Tạo tài khoản</h4>
              <p>
                Đăng ký tài khoản miễn phí và tạo hồ sơ cá nhân của bạn
              </p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 mb-4">
            <div class="step-card">
              <div class="step-number">
                <span>2</span>
              </div>
              <h4>Tìm kiếm việc làm</h4>
              <p>
                Sử dụng bộ lọc thông minh để tìm việc làm phù hợp với bạn
              </p>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 mb-4">
            <div class="step-card">
              <div class="step-number">
                <span>3</span>
              </div>
              <h4>Ứng tuyển ngay</h4>
              <p>
                Gửi hồ sơ ứng tuyển và chờ phản hồi từ nhà tuyển dụng
              </p>
            </div>
          </div>
        </div>
        <div class="text-center mt-5">
          <?php if (!isLoggedIn()): ?>
            <a href="<?php echo BASE_URL; ?>register.php" class="btn" style="background: #2563eb; color: white; padding: 15px 40px; font-size: 16px; font-weight: 600; border-radius: 12px; border: none; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3); transition: all 0.3s;">
              <i class="fa fa-user-plus"></i> Đăng ký ngay
            </a>
          <?php else: ?>
            <a href="<?php echo BASE_URL; ?>candidate/index.php" class="btn" style="background: #2563eb; color: white; padding: 15px 40px; font-size: 16px; font-weight: 600; border-radius: 12px; border: none; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3); transition: all 0.3s;">
              <i class="fa fa-search"></i> Tìm việc làm ngay
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <!-- How It Works End -->




    <!-- Jobs Start -->
    <div class="jobs-section">
      <div class="container">
        <div class="section-header text-center">
          <p>VIỆC LÀM MỚI NHẤT</p>
          <h2>Tin tuyển dụng nổi bật</h2>
          <p>
            Khám phá các cơ hội việc làm hấp dẫn từ các công ty hàng đầu
          </p>
        </div>
        <?php if (!empty($recent_jobs)): ?>
          <div class="row">
            <?php foreach ($recent_jobs as $job): ?>
              <div class="col-lg-4 col-md-6 mb-4">
                <div class="job-card-modern" style="height: 100%; background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); transition: all 0.3s ease; border: 1px solid #e5e7eb;">
                  <div style="padding: 25px;">
                    <div class="d-flex align-items-center mb-3">
                      <?php if ($job['logo_url']): ?>
                        <img src="<?php echo BASE_URL . ltrim($job['logo_url'], '/'); ?>" 
                             alt="<?php echo htmlspecialchars($job['ten_cong_ty']); ?>" 
                             style="width: 60px; height: 60px; object-fit: contain; margin-right: 15px; border: 1px solid #e0e0e0; border-radius: 10px; padding: 8px; background: #fafafa;">
                      <?php else: ?>
                        <div style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #2563eb; border-radius: 12px; margin-right: 15px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);">
                          <i class="fa fa-building" style="font-size: 28px; color: white;"></i>
                        </div>
                      <?php endif; ?>
                      <div style="flex: 1;">
                        <h5 style="margin: 0; color: #092a49; font-size: 16px; font-weight: 600;">
                          <?php echo htmlspecialchars($job['ten_cong_ty']); ?>
                        </h5>
                        <?php if ($job['noi_lam_viec']): ?>
                          <p style="margin: 5px 0 0 0; color: #797979; font-size: 13px;">
                            <i class="fa fa-map-marker-alt" style="color: #2563eb;"></i> <?php echo htmlspecialchars($job['noi_lam_viec']); ?>
                          </p>
                        <?php endif; ?>
                      </div>
                    </div>
                    <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 12px; line-height: 1.4;">
                      <a href="<?php echo BASE_URL; ?>candidate/job-detail.php?id=<?php echo $job['tin_id']; ?>" 
                         style="color: #092a49; text-decoration: none; transition: color 0.3s;">
                        <?php echo htmlspecialchars($job['tieu_de']); ?>
                      </a>
                    </h3>
                    <div style="margin-bottom: 15px; font-size: 14px; color: #555; line-height: 1.6; min-height: 60px;">
                      <?php echo htmlspecialchars(mb_substr(strip_tags($job['mo_ta']), 0, 100)) . '...'; ?>
                    </div>
                    <?php if (!empty($job['ky_nang'])): ?>
                      <div style="margin-bottom: 15px; display: flex; flex-wrap: wrap; gap: 6px;">
                        <?php foreach (array_slice($job['ky_nang'], 0, 3) as $kn): ?>
                          <span style="display: inline-block; padding: 5px 12px; background: #eff6ff; border: 1px solid #2563eb; border-radius: 15px; font-size: 12px; color: #2563eb; font-weight: 500;">
                            <?php echo htmlspecialchars($kn['ten_kn']); ?>
                          </span>
                        <?php endforeach; ?>
                        <?php if (count($job['ky_nang']) > 3): ?>
                          <span style="display: inline-block; padding: 5px 12px; background: #f5f5f5; border-radius: 15px; font-size: 12px; color: #797979;">
                            +<?php echo count($job['ky_nang']) - 3; ?>
                          </span>
                        <?php endif; ?>
                      </div>
                    <?php endif; ?>
                    <div style="padding-top: 15px; border-top: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center;">
                      <div>
                        <div style="color: #2563eb; font-weight: 700; font-size: 16px; margin-bottom: 5px;">
                          <?php 
                          if ($job['luong_min'] && $job['luong_max']) {
                              echo formatCurrency($job['luong_min'], $job['tien_te']) . ' - ' . formatCurrency($job['luong_max'], $job['tien_te']);
                          } elseif ($job['luong_min']) {
                              echo 'Từ ' . formatCurrency($job['luong_min'], $job['tien_te']);
                          } else {
                              echo 'Thỏa thuận';
                          }
                          ?>
                        </div>
                        <?php if ($job['hinh_thuc_lv']): ?>
                          <span style="font-size: 12px; color: #797979;">
                            <i class="fa fa-clock"></i> <?php echo $work_type_map[$job['hinh_thuc_lv']] ?? ''; ?>
                          </span>
                        <?php endif; ?>
                      </div>
                      <a class="btn" href="<?php echo BASE_URL; ?>candidate/job-detail.php?id=<?php echo $job['tin_id']; ?>" 
                         style="background: #2563eb; color: white; padding: 10px 24px; font-size: 14px; font-weight: 600; border-radius: 10px; border: none; transition: all 0.3s; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);">
                        Xem chi tiết <i class="fa fa-arrow-right" style="margin-left: 5px;"></i>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="text-center mt-5">
            <a href="<?php echo BASE_URL; ?>candidate/index.php" class="btn" 
               style="background: #2563eb; color: white; padding: 15px 50px; font-size: 16px; font-weight: 600; border-radius: 12px; border: none; display: inline-block; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3); transition: all 0.3s;">
              <i class="fa fa-briefcase"></i> Xem tất cả việc làm
            </a>
          </div>
        <?php else: ?>
          <div class="text-center" style="padding: 60px 20px;">
            <div style="width: 120px; height: 120px; background: #f0f0f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px;">
              <i class="fa fa-briefcase" style="font-size: 60px; color: #ccc;"></i>
            </div>
            <h3 style="color: #092a49; font-size: 24px; font-weight: 600; margin-bottom: 15px;">Chưa có tin tuyển dụng</h3>
            <p style="color: #797979; font-size: 16px;">Hiện tại chưa có tin tuyển dụng nào. Vui lòng quay lại sau!</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <!-- Jobs End -->

    <!-- CTA Section Start -->
    <div class="cta-section" style="padding: 80px 0; background: #1e40af; position: relative; overflow: hidden;">
      <div class="container text-center" style="position: relative; z-index: 2;">
        <h2 style="color: white; font-size: 42px; font-weight: 700; margin-bottom: 20px;">
          Sẵn sàng tìm việc làm mơ ước?
        </h2>
        <p style="color: white; font-size: 20px; margin-bottom: 40px; opacity: 0.95;">
          Tham gia cùng hàng nghìn ứng viên đã tìm được công việc phù hợp
        </p>
        <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
          <?php if (!isLoggedIn()): ?>
            <a href="<?php echo BASE_URL; ?>register.php" class="btn" 
               style="background: white; color: #1e40af; padding: 15px 40px; font-size: 16px; font-weight: 600; border-radius: 12px; border: none; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2); transition: all 0.3s;">
              <i class="fa fa-user-plus"></i> Đăng ký ngay
            </a>
            <a href="<?php echo BASE_URL; ?>candidate/index.php" class="btn" 
               style="background: rgba(255, 255, 255, 0.1); color: white; padding: 15px 40px; font-size: 16px; font-weight: 600; border-radius: 12px; border: 2px solid white; backdrop-filter: blur(10px); transition: all 0.3s;">
              <i class="fa fa-search"></i> Tìm việc làm
            </a>
          <?php else: ?>
            <a href="<?php echo BASE_URL; ?>candidate/index.php" class="btn" 
               style="background: white; color: #1e40af; padding: 15px 40px; font-size: 16px; font-weight: 600; border-radius: 12px; border: none; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2); transition: all 0.3s;">
              <i class="fa fa-search"></i> Tìm việc làm ngay
            </a>
          <?php endif; ?>
        </div>
      </div>
      <div class="svg-wave-bg"></div>
    </div>
    <!-- CTA Section End -->
    
    <!-- Contact Start (Hidden) -->
    <div class="contact">
      <div class="container">
        <div class="section-header">
          <p>Get In Touch</p>
          <h2>Get In Touch For Any Query</h2>
        </div>
        <div class="row align-items-center">
          <div class="col-md-5">
            <div class="contact-info">
              <div class="contact-icon">
                <i class="fa fa-map-marker-alt"></i>
              </div>
              <div class="contact-text">
                <h3>Our Head Office</h3>
                <p>123 Street, New York, USA</p>
              </div>
            </div>
            <div class="contact-info">
              <div class="contact-icon">
                <i class="fa fa-phone-alt"></i>
              </div>
              <div class="contact-text">
                <h3>Call for Help</h3>
                <p>+012 345 6789</p>
              </div>
            </div>
            <div class="contact-info">
              <div class="contact-icon">
                <i class="fa fa-envelope"></i>
              </div>
              <div class="contact-text">
                <h3>Email for Information</h3>
                <p>info@example.com</p>
              </div>
            </div>
          </div>
          <div class="col-md-7">
            <div class="contact-form">
              <div id="success"></div>
              <form name="sentMessage" id="contactForm" novalidate="novalidate">
                <div class="control-group">
                  <input
                    type="text"
                    class="form-control"
                    id="name"
                    placeholder="Your Name"
                    required="required"
                    data-validation-required-message="Please enter your name"
                  />
                  <p class="help-block text-danger"></p>
                </div>
                <div class="control-group">
                  <input
                    type="email"
                    class="form-control"
                    id="email"
                    placeholder="Your Email"
                    required="required"
                    data-validation-required-message="Please enter your email"
                  />
                  <p class="help-block text-danger"></p>
                </div>
                <div class="control-group">
                  <input
                    type="text"
                    class="form-control"
                    id="subject"
                    placeholder="Subject"
                    required="required"
                    data-validation-required-message="Please enter a subject"
                  />
                  <p class="help-block text-danger"></p>
                </div>
                <div class="control-group">
                  <textarea
                    class="form-control"
                    id="message"
                    placeholder="Message"
                    required="required"
                    data-validation-required-message="Please enter your message"
                  ></textarea>
                  <p class="help-block text-danger"></p>
                </div>
                <div>
                  <button class="btn" type="submit" id="sendMessageButton">
                    Send Message
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Contact End -->

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
