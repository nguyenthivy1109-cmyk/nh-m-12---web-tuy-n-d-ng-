<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/tai_khoan.php';

// Nếu đã đăng nhập, chuyển về trang chủ
if (isLoggedIn()) {
    header("Location: " . publicRoute('home'));
    exit();
}

$error = '';
$success = '';

// Xử lý form quên mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    
    if (empty($username)) {
        $error = 'Vui lòng nhập tên đăng nhập!';
    } else {
        // Tìm tài khoản theo tên đăng nhập hoặc email
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
            // Reset mật khẩu về 123456
            else {
                $new_password_hash = hashPassword('123456');
                if (updatePassword($pdo, $user['tai_khoan_id'], $new_password_hash)) {
                    $success = 'Mật khẩu đã được reset về: <strong>123456</strong>. Vui lòng đăng nhập và đổi mật khẩu mới!';
                } else {
                    $error = 'Có lỗi xảy ra khi reset mật khẩu. Vui lòng thử lại!';
                }
            }
        } else {
            $error = 'Không tìm thấy tài khoản với tên đăng nhập/email này!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Confer</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Lato&family=Oswald:wght@200;300;400&display=swap" rel="stylesheet">
    <!-- Template Stylesheet -->
    <link href="<?php echo BASE_URL; ?>assets/public/css/style.css" rel="stylesheet">
    
    <style>
        .forgot-password-page {
            min-height: calc(100vh - 70px);
            padding: 80px 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        
        .forgot-password-container {
            max-width: 450px;
            margin: 0 auto;
        }
        
        .forgot-password-card {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            transition: all 0.3s;
        }
        
        .forgot-password-card:hover {
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        }
        
        .forgot-password-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .forgot-password-header h2 {
            color: #092a49;
            font-size: 32px;
            font-weight: 400;
            margin-bottom: 10px;
            font-family: 'Oswald', sans-serif;
        }
        
        .forgot-password-header p {
            color: #797979;
            font-size: 14px;
            margin: 0;
        }
        
        .forgot-password-icon {
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
        
        .forgot-password-form .form-group {
            margin-bottom: 25px;
        }
        
        .forgot-password-form .form-control {
            height: 50px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            padding: 12px 20px;
            font-size: 16px;
            color: #092a49;
            transition: all 0.3s;
        }
        
        .forgot-password-form .form-control:focus {
            border-color: #0796fe;
            box-shadow: 0 0 0 0.2rem rgba(7, 150, 254, 0.15);
        }
        
        .forgot-password-form .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-right: none;
            border-radius: 5px 0 0 5px;
            color: #092a49;
        }
        
        .forgot-password-form .form-control.with-icon {
            border-left: none;
            border-radius: 0 5px 5px 0;
        }
        
        .btn-reset {
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
        
        .btn-reset:hover {
            background: #0796fe;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(7, 150, 254, 0.3);
        }
        
        .btn-reset:active {
            transform: translateY(0);
        }
        
        .forgot-password-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e0e0e0;
        }
        
        .forgot-password-footer a {
            color: #0796fe;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .forgot-password-footer a:hover {
            color: #092a49;
            text-decoration: underline;
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
        
        @media (max-width: 575.98px) {
            .forgot-password-page {
                padding: 40px 15px;
            }
            
            .forgot-password-card {
                padding: 30px 20px;
            }
            
            .forgot-password-header h2 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../includes/header.php'; ?>    

    <!-- Forgot Password Page Start -->
    <div class="forgot-password-page">
      <div class="container">
        <div class="forgot-password-container">
          <div class="forgot-password-card">
            <div class="forgot-password-header">
              <div class="forgot-password-icon">
                <i class="fa fa-key"></i>
              </div>
              <h2>Quên mật khẩu</h2>
              <p>Nhập tên đăng nhập hoặc email để reset mật khẩu</p>
            </div>
            
            <?php if ($error): ?>
              <div class="alert alert-danger" role="alert">
                <i class="fa fa-exclamation-circle"></i> <?php echo $error; ?>
              </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
              <div class="alert alert-success" role="alert">
                <i class="fa fa-check-circle"></i> <?php echo $success; ?>
              </div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
            <form class="forgot-password-form" method="POST" action="">
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
                    placeholder="Nhập tên đăng nhập hoặc email"
                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                    required
                    autofocus
                  />
                </div>
              </div>
              
              <button type="submit" class="btn btn-reset">
                <i class="fa fa-key"></i> Reset mật khẩu
              </button>
            </form>
            <?php endif; ?>
            
            <div class="forgot-password-footer">
              <p style="color: #797979; font-size: 14px; margin: 0;">
                <a href="login.php">
                  <i class="fa fa-arrow-left"></i> Quay lại đăng nhập
                </a>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Forgot Password Page End -->

    <?php include __DIR__ . '/../../includes/footer.php'; ?>

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
</body>
</html>

