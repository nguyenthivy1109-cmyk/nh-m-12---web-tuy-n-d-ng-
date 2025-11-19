<?php
/**
 * Header chung cho toàn bộ website
 * Sử dụng cho trang chủ và các trang candidate
 */

// Đảm bảo BASE_URL đã được định nghĩa
if (!defined('BASE_URL')) {
    // Nếu chưa có, định nghĩa tạm (nên include config.php trước khi include header)
    define('BASE_URL', 'http://localhost/duantest2/');
}

// Kiểm tra đăng nhập
$is_logged_in = isLoggedIn();
$user_role = $_SESSION['vai_tro_id'] ?? null;
$user_name = $_SESSION['ten_dn'] ?? '';
?>
<!-- Top Bar Start -->
<!-- <div class="top-bar d-none d-md-block">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-8">
        <div class="top-bar-left">
          <div class="text">
            <i class="far fa-clock"></i>
            <h2>8:00 - 18:00</h2>
            <p>Mon - Fri</p>
          </div>
          <div class="text">
            <i class="fa fa-phone-alt"></i>
            <h2>+84 123 456 789</h2>
            <p>Hỗ trợ 24/7</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="top-bar-right">
          <div class="social">
            <a href=""><i class="fab fa-twitter"></i></a>
            <a href=""><i class="fab fa-facebook-f"></i></a>
            <a href=""><i class="fab fa-linkedin-in"></i></a>
            <a href=""><i class="fab fa-instagram"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div> -->
<!-- Top Bar End -->

<style>
/* Header Fixed và Styling */
.main-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    background: #1e3c72 !important;
    opacity: 1 !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.main-header .navbar {
    background: #1e3c72 !important;
    padding: 0.8rem 0;
}

.main-header .navbar-brand {
    font-size: 1.5rem;
    font-weight: 700;
    color: #ffffff !important;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
}

.main-header .navbar-brand:hover {
    color: #0796fe !important;
    transform: scale(1.05);
}

.main-header .navbar-brand i {
    color: #0796fe;
    margin-right: 8px;
    font-size: 1.6rem;
}

.main-header .nav-link {
    color: rgba(255, 255, 255, 0.9) !important;
    font-weight: 500;
    padding: 0.5rem 1rem !important;
    margin: 0 0.2rem;
    border-radius: 5px;
    transition: all 0.3s ease;
    position: relative;
}

.main-header .nav-link:hover {
    color: #ffffff !important;
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}

/* Loại bỏ transform khi hover vào dropdown để tránh conflict */
.main-header .nav-item.dropdown .nav-link:hover {
    transform: none;
}

.main-header .nav-link.active {
    color: #0796fe !important;
    background: rgba(7, 150, 254, 0.15);
    font-weight: 600;
}

.main-header .nav-link.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 60%;
    height: 3px;
    background: #0796fe;
    border-radius: 2px;
}

.main-header .dropdown-toggle {
    color: rgba(255, 255, 255, 0.9) !important;
}

.main-header .nav-item.dropdown {
    position: relative;
}

.main-header .dropdown-menu {
    background: #ffffff;
    border: none;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
    border-radius: 8px;
    margin-top: 0;
    padding: 0.5rem 0;
    min-width: 220px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    pointer-events: none;
    position: absolute;
    top: 100%;
    right: 0;
    z-index: 1001;
}

/* Hiển thị dropdown khi hover vào nav-item hoặc dropdown-toggle */
.main-header .nav-item.dropdown:hover .dropdown-menu,
.main-header .nav-item.dropdown.show .dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
    pointer-events: auto;
}

/* Đảm bảo dropdown-toggle vẫn có hover effect */
.main-header .dropdown-toggle:hover {
    color: #ffffff !important;
    background: rgba(255, 255, 255, 0.1);
}

.main-header .dropdown-item {
    color: #333;
    padding: 0.6rem 1.5rem;
    transition: all 0.2s ease;
    display: block;
    text-decoration: none;
}

.main-header .dropdown-item:hover {
    background: #f8f9fa;
    color: #0796fe;
    padding-left: 2rem;
    text-decoration: none;
}

.main-header .dropdown-item i {
    margin-right: 8px;
    width: 20px;
    color: #0796fe;
}

.main-header .navbar-toggler {
    border: 2px solid rgba(255, 255, 255, 0.3);
    padding: 0.4rem 0.6rem;
}

.main-header .navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.9%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

/* Thêm padding-top cho body để tránh nội dung bị che bởi fixed header */
body {
    padding-top: 80px;
}

@media (max-width: 991.98px) {
    .main-header .navbar-collapse {
        background: #1e3c72;
        margin-top: 1rem;
        padding: 1rem;
        border-radius: 8px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
    }
    
    body {
        padding-top: 60px;
    }
}

/* Scroll effect - header nhỏ lại khi scroll */
.main-header.scrolled {
    padding: 0.5rem 0;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.2);
}

.main-header.scrolled .navbar-brand {
    font-size: 1.3rem;
}
</style>

<script>
// Thêm class scrolled khi scroll
window.addEventListener('scroll', function() {
    const header = document.querySelector('.main-header');
    if (window.scrollY > 50) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});
</script>

<!-- Nav Bar Start -->
<div class="navbar navbar-expand-lg main-header">
  <div class="container-fluid">
    <a href="<?php echo publicRoute('home'); ?>" class="navbar-brand">
      <i class="fa fa-briefcase"></i> Tuyển Dụng
    </a>
    <button
      type="button"
      class="navbar-toggler"
      data-toggle="collapse"
      data-target="#navbarCollapse"
    >
      <span class="navbar-toggler-icon"></span>
    </button>

    <div
      class="collapse navbar-collapse justify-content-between"
      id="navbarCollapse"
    >
      <div class="navbar-nav ml-auto align-items-center">
        <a href="<?php echo publicRoute('home'); ?>" 
           class="nav-item nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['REQUEST_URI'], 'candidate') === false) ? 'active' : ''; ?>">
          Trang chủ
        </a>
        <a href="<?php echo candidateRoute('dashboard'); ?>" 
           class="nav-item nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'candidate') !== false || strpos($_SERVER['REQUEST_URI'], 'job-detail') !== false || strpos($_SERVER['REQUEST_URI'], 'apply') !== false) ? 'active' : ''; ?>">
          <i class="fa fa-search"></i> Ứng tuyển
        </a>
        <a href="<?php echo publicRoute('about'); ?>" class="nav-item nav-link">Giới thiệu</a>
        <a href="<?php echo publicRoute('service'); ?>" class="nav-item nav-link">Dịch vụ</a>
        <a href="<?php echo publicRoute('contact'); ?>" class="nav-item nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'contact') !== false) ? 'active' : ''; ?>">Liên hệ</a>
        
        <?php if ($is_logged_in): ?>
          <div class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-user"></i> <?php echo htmlspecialchars($user_name); ?>
            </a>
            <div class="dropdown-menu">
              <?php if ($user_role == ROLE_CANDIDATE): ?>
                <a href="<?php echo candidateRoute('dashboard'); ?>" class="dropdown-item">
                  <i class="fa fa-search"></i> Tìm việc làm
                </a>
                <a href="<?php echo candidateRoute('applications'); ?>" class="dropdown-item">
                  <i class="fa fa-file-alt"></i> Đơn ứng tuyển
                </a>
                <a href="<?php echo candidateRoute('profile'); ?>" class="dropdown-item">
                  <i class="fa fa-user-edit"></i> Hồ sơ
                </a>
                <a href="<?php echo candidateRoute('skills'); ?>" class="dropdown-item">
                  <i class="fa fa-cogs"></i> Kỹ năng
                </a>
              <?php elseif ($user_role == ROLE_RECRUITER): ?>
                <a href="<?php echo recruiterRoute('dashboard'); ?>" class="dropdown-item">
                  <i class="fa fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="<?php echo recruiterRoute('jobs'); ?>" class="dropdown-item">
                  <i class="fa fa-briefcase"></i> Quản lý tin tuyển dụng
                </a>
              <?php elseif ($user_role == ROLE_ADMIN): ?>
                <a href="<?php echo adminRoute('dashboard'); ?>" class="dropdown-item">
                  <i class="fa fa-tachometer-alt"></i> Admin Panel
                </a>
              <?php endif; ?>
              <div class="dropdown-divider"></div>
              <a href="<?php echo BASE_URL; ?>logout.php" class="dropdown-item">
                <i class="fa fa-sign-out-alt"></i> Đăng xuất
              </a>
            </div>
          </div>
        <?php else: ?>
          <a href="<?php echo publicRoute('login'); ?>" 
             class="nav-item nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'login.php') ? 'active' : ''; ?>">
            <i class="fa fa-sign-in-alt"></i> Đăng nhập
          </a>
          <a href="<?php echo publicRoute('register'); ?>" 
             class="nav-item nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'register.php') ? 'active' : ''; ?>">
            <i class="fa fa-user-plus"></i> Đăng ký
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<!-- Nav Bar End -->

