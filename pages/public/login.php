<?php
/**
 * Trang đăng nhập
 */

// Nếu đã đăng nhập, chuyển về trang chủ
if (isset($_SESSION['user_id'])) {
    header("Location: " . publicRoute('home'));
    exit();
}

// Include các file cần thiết
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/tai_khoan.php';

$error = '';
$success = '';

// Xử lý form đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validation
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin đăng nhập!';
    } else {
        // Lấy thông tin tài khoản (theo tên đăng nhập hoặc email)
        $user = getTaiKhoanByTenDNHoacEmail($pdo, $username);
        
        if ($user) {
            // Kiểm tra tài khoản có bị xóa không
            if ($user['xoa_luc'] !== null) {
                $error = 'Tài khoản đã bị xóa!';
            }
            // Kiểm tra tài khoản có bị khóa không
            elseif ($user['kich_hoat'] == 0) {
                $error = 'Tài khoản đã bị khóa! Vui lòng liên hệ quản trị viên.';
            }
            // Kiểm tra mật khẩu
            elseif (verifyPassword($password, $user['mat_khau_hash'])) {
                // Đăng nhập thành công
                $_SESSION['user_id'] = $user['tai_khoan_id'];
                $_SESSION['ten_dn'] = $user['ten_dn'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['vai_tro_id'] = $user['vai_tro_id'];
                
                // Cập nhật thời gian đăng nhập cuối
                updateLastLogin($pdo, $user['tai_khoan_id']);
                
                // Nếu chọn "Ghi nhớ đăng nhập", set cookie (30 ngày)
                if ($remember) {
                    $token = base64_encode($user['tai_khoan_id'] . '|' . $user['ten_dn']);
                    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true); // httpOnly = true để bảo mật
                }
                
                // Chuyển hướng theo vai trò
                $redirect_url = publicRoute('home');
                if ($user['vai_tro_id'] == ROLE_ADMIN) {
                    $redirect_url = adminRoute('dashboard');
                } elseif ($user['vai_tro_id'] == ROLE_RECRUITER) {
                    $redirect_url = recruiterRoute('dashboard');
                }
                // Ứng viên (ROLE_CANDIDATE) sẽ ở lại trang home
                
                header("Location: " . $redirect_url);
                exit();
            } else {
                $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
            }
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>Đăng nhập - Confer</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta content="Đăng nhập vào hệ thống" name="keywords" />
    <meta content="Đăng nhập vào hệ thống" name="description" />

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
    </style>
    
    <style>
      .login-page {
        min-height: calc(100vh - 70px);
        padding: 80px 0;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      }
      
      .login-container {
        max-width: 450px;
        margin: 0 auto;
      }
      
      .login-card {
        background: #ffffff;
        border-radius: 10px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        padding: 40px;
        transition: all 0.3s;
      }
      
      .login-card:hover {
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
      }
      
      .login-header {
        text-align: center;
        margin-bottom: 30px;
      }
      
      .login-header h2 {
        color: #092a49;
        font-size: 32px;
        font-weight: 400;
        margin-bottom: 10px;
      }
      
      .login-header p {
        color: #797979;
        font-size: 14px;
        margin: 0;
      }
      
      .login-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: #0796fe;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 35px;
      }
      
      .login-form .form-group {
        margin-bottom: 25px;
      }
      
      .login-form .form-control {
        height: 50px;
        border: 2px solid #e0e0e0;
        border-radius: 5px;
        padding: 12px 20px;
        font-size: 16px;
        color: #092a49;
        transition: all 0.3s;
      }
      
      .login-form .form-control:focus {
        border-color: #0796fe;
        box-shadow: 0 0 0 0.2rem rgba(7, 150, 254, 0.15);
      }
      
      .login-form .input-group-text {
        background: #f8f9fa;
        border: 2px solid #e0e0e0;
        border-right: none;
        border-radius: 5px 0 0 5px;
        color: #092a49;
      }
      
      .login-form .form-control.with-icon {
        border-left: none;
        border-radius: 0 5px 5px 0;
      }
      
      .login-form .btn-login {
        width: 100%;
        padding: 15px;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 1px;
        color: #ffffff;
        background: #0796fe;
        border: none;
        border-radius: 5px;
        transition: all 0.3s;
        margin-top: 10px;
      }
      
      .login-form .btn-login:hover {
        background: #0796fe;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(7, 150, 254, 0.3);
      }
      
      .login-form .btn-login:active {
        transform: translateY(0);
      }
      
      .login-footer {
        text-align: center;
        margin-top: 25px;
        padding-top: 25px;
        border-top: 1px solid #e0e0e0;
      }
      
      .login-footer a {
        color: #0796fe;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s;
      }
      
      .login-footer a:hover {
        color: #092a49;
        text-decoration: underline;
      }
      
      .remember-forgot {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
      }
      
      .remember-forgot .form-check {
        margin: 0;
      }
      
      .remember-forgot .form-check-input {
        margin-top: 0.3rem;
      }
      
      .remember-forgot .form-check-label {
        color: #797979;
        font-size: 14px;
        margin-left: 5px;
      }
      
      .forgot-password {
        color: #0796fe;
        font-size: 14px;
        text-decoration: none;
      }
      
      .forgot-password:hover {
        color: #092a49;
        text-decoration: underline;
      }
      
      @media (max-width: 575.98px) {
        .login-page {
          padding: 40px 15px;
        }
        
        .login-card {
          padding: 30px 20px;
        }
        
        .login-header h2 {
          font-size: 28px;
        }
      }
      
      .alert {
        border-radius: 5px;
        padding: 12px 15px;
        margin-bottom: 20px;
        border: none;
      }
      
      .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
      }
      
      .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
      }
      
      .alert i {
        margin-right: 8px;
      }
    </style>
  </head>

  <body>

    <!-- Login Page Start -->
    <div class="login-page">
      <div class="container">
        <div class="login-container">
          <div class="login-card">
            <div class="login-header">
              <div class="login-icon">
                <i class="fa fa-user-lock"></i>
              </div>
              <h2>Đăng nhập</h2>
              <p>Vui lòng đăng nhập vào tài khoản của bạn</p>
            </div>
            
            <?php if ($error): ?>
              <div class="alert alert-danger" role="alert">
                <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
              </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
              <div class="alert alert-success" role="alert">
                <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
              </div>
            <?php endif; ?>
            
            <form class="login-form" id="loginForm" method="POST" action="">
              <div class="form-group">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">
                      <i class="fa fa-user"></i>
                    </span>
                  </div>
                  <input
                    type="text"
                    class="form-control with-icon"
                    id="username"
                    name="username"
                    placeholder="Tên đăng nhập hoặc Email"
                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                    required
                    autofocus
                  />
                </div>
              </div>
              
              <div class="form-group">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">
                      <i class="fa fa-lock"></i>
                    </span>
                  </div>
                  <input
                    type="password"
                    class="form-control with-icon"
                    id="password"
                    name="password"
                    placeholder="Mật khẩu"
                    required
                  />
                </div>
              </div>
              
              <div class="remember-forgot">
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="remember"
                    name="remember"
                    <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>
                  />
                  <label class="form-check-label" for="remember">
                    Ghi nhớ đăng nhập
                  </label>
                </div>
                <a href="<?php echo publicRoute('forgot-password'); ?>" class="forgot-password">Quên mật khẩu?</a>
              </div>
              
              <button type="submit" class="btn btn-login">
                <i class="fa fa-sign-in-alt"></i> Đăng nhập
              </button>
            </form>
            
            <div class="login-footer">
              <p style="color: #797979; font-size: 14px; margin: 0;">
                Chưa có tài khoản? 
                <a href="<?php echo BASE_URL; ?>register.php">Đăng ký ngay</a>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Login Page End -->

    <!-- Footer Start -->
    <div class="footer">
      <div class="container">
        <div class="row">
          <div class="col-lg-7">
            <div class="row">
              <div class="col-md-6">
                <div class="footer-contact">
                  <h2>Our Head Office</h2>
                  <p>
                    <i class="fa fa-map-marker-alt"></i>123 Street, New York,
                    USA
                  </p>
                  <p><i class="fa fa-phone-alt"></i>+012 345 67890</p>
                  <p><i class="fa fa-envelope"></i>info@example.com</p>
                  <div class="footer-social">
                    <a href=""><i class="fab fa-twitter"></i></a>
                    <a href=""><i class="fab fa-facebook-f"></i></a>
                    <a href=""><i class="fab fa-youtube"></i></a>
                    <a href=""><i class="fab fa-instagram"></i></a>
                    <a href=""><i class="fab fa-linkedin-in"></i></a>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="footer-link">
                  <h2>Quick Links</h2>
                  <a href="">Terms of use</a>
                  <a href="">Privacy policy</a>
                  <a href="">Cookies</a>
                  <a href="">Help</a>
                  <a href="">FQAs</a>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-5">
            <div class="footer-newsletter">
              <h2>Newsletter</h2>
              <p>
                Lorem ipsum dolor sit amet elit. Quisque eu lectus a leo dictum
                nec non quam. Tortor eu placerat rhoncus, lorem quam iaculis
                felis, sed lacus neque id eros.
              </p>
              <div class="form">
                <input class="form-control" placeholder="Email goes here" />
                <button class="btn">Submit</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="container copyright">
        <div class="row">
          <div class="col-md-6">
            <p>&copy; <a href="#">Your Site Name</a>, All Right Reserved.</p>
          </div>
          <div class="col-md-6">
            <p>Designed By <a href="https://htmlcodex.com">HTML Codex</a></p>
          </div>
        </div>
      </div>
    </div>
    <!-- Footer End -->

    <a href="#" class="back-to-top"><i class="fa fa-chevron-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/public/lib/easing/easing.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/public/lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/public/lib/waypoints/waypoints.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/public/lib/counterup/counterup.min.js"></script>

    <!-- Template Javascript -->
    <script src="<?php echo BASE_URL; ?>assets/public/js/main.js"></script>
    
    <script>
      // Form validation
      $(document).ready(function() {
        $('#loginForm').on('submit', function(e) {
          var username = $('#username').val().trim();
          var password = $('#password').val();
          
          if (username === '' || password === '') {
            e.preventDefault();
            alert('Vui lòng điền đầy đủ thông tin đăng nhập!');
            return false;
          }
          
          // Form sẽ submit bình thường để xử lý ở server
        });
      });
    </script>
  </body>
</html>

