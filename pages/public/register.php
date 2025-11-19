<?php
/**
 * Trang đăng ký tài khoản
 */

// Nếu đã đăng nhập, chuyển về trang chủ
if (isset($_SESSION['user_id'])) {
    header("Location: " . publicRoute('home'));
    exit();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/tai_khoan.php';
require_once __DIR__ . '/../../models/ung_vien.php';
require_once __DIR__ . '/../../models/nha_tuyen_dung.php';

$error = '';
$success = '';

// Xử lý form đăng ký
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_dn = sanitize($_POST['ten_dn'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $dien_thoai = sanitize($_POST['dien_thoai'] ?? '');
    $vai_tro_id = isset($_POST['vai_tro_id']) ? (int)$_POST['vai_tro_id'] : 0;
    $ho_ten = sanitize($_POST['ho_ten'] ?? '');
    
    // Validation
    if (empty($ten_dn) || empty($email) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ!';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } elseif ($password !== $password_confirm) {
        $error = 'Mật khẩu xác nhận không khớp!';
    } elseif (!in_array($vai_tro_id, [ROLE_CANDIDATE, ROLE_RECRUITER])) {
        $error = 'Vai trò không hợp lệ!';
    } else {
        // Kiểm tra tên đăng nhập đã tồn tại
        if (isTenDNExists($pdo, $ten_dn)) {
            $error = 'Tên đăng nhập đã tồn tại!';
        }
        // Kiểm tra email đã tồn tại
        elseif (isEmailExists($pdo, $email)) {
            $error = 'Email đã được sử dụng!';
        } else {
            try {
                $pdo->beginTransaction();
                
                // Tạo tài khoản
                $tai_khoan_id = createTaiKhoan($pdo, [
                    'ten_dn' => $ten_dn,
                    'mat_khau_hash' => hashPassword($password),
                    'email' => $email,
                    'dien_thoai' => $dien_thoai ?: null,
                    'vai_tro_id' => $vai_tro_id,
                    'kich_hoat' => 1
                ]);
                
                // Tạo bản ghi tương ứng
                if ($vai_tro_id == ROLE_CANDIDATE) {
                    createUngVien($pdo, [
                        'tai_khoan_id' => $tai_khoan_id,
                        'ho_ten' => $ho_ten ?: null
                    ]);
                } elseif ($vai_tro_id == ROLE_RECRUITER) {
                    createNhaTuyenDung($pdo, [
                        'tai_khoan_id' => $tai_khoan_id,
                        'ho_ten' => $ho_ten ?: null,
                        'email_cong_viec' => $email
                    ]);
                }
                
                $pdo->commit();
                $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.';
                
                // Redirect sau 2 giây
                header("refresh:2;url=" . BASE_URL . "login.php");
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Có lỗi xảy ra: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Hệ thống tuyển dụng</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Lato&family=Oswald:wght@200;300;400&display=swap" rel="stylesheet">
    <!-- Template Stylesheet -->
    <link href="<?php echo BASE_URL; ?>assets/public/css/style.css" rel="stylesheet">
    
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
      .register-page {
        min-height: calc(100vh - 70px);
        padding: 80px 0;
        background: #ffffff;
      }
      
      .register-container {
        max-width: 550px;
        margin: 0 auto;
      }
      
      .register-card {
        background: #ffffff;
        border-radius: 10px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        padding: 40px;
        transition: all 0.3s;
        border: 1px solid #e0e0e0;
      }
      
      .register-card:hover {
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
      }
      
      .register-header {
        text-align: center;
        margin-bottom: 30px;
      }
      
      .register-header h2 {
        color: #092a49;
        font-size: 32px;
        font-weight: 400;
        margin-bottom: 10px;
      }
      
      .register-header p {
        color: #797979;
        font-size: 14px;
        margin: 0;
      }
      
      .register-icon {
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
      
      .register-form .form-group {
        margin-bottom: 25px;
      }
      
      .register-form .form-control {
        height: 50px;
        border: 2px solid #e0e0e0;
        border-radius: 5px;
        padding: 12px 20px;
        font-size: 16px;
        color: #092a49;
        transition: all 0.3s;
      }
      
      .register-form .form-control:focus {
        border-color: #0796fe;
        box-shadow: 0 0 0 0.2rem rgba(7, 150, 254, 0.15);
      }
      
      .register-form .input-group-text {
        background: #f8f9fa;
        border: 2px solid #e0e0e0;
        border-right: none;
        border-radius: 5px 0 0 5px;
        color: #092a49;
      }
      
      .register-form .form-control.with-icon {
        border-left: none;
        border-radius: 0 5px 5px 0;
      }
      
      .register-form .btn-register {
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
      
      .register-form .btn-register:hover {
        background: #0684e0;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(7, 150, 254, 0.3);
      }
      
      .register-form .btn-register:active {
        transform: translateY(0);
      }
      
      .register-footer {
        text-align: center;
        margin-top: 25px;
        padding-top: 25px;
        border-top: 1px solid #e0e0e0;
      }
      
      .register-footer a {
        color: #0796fe;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s;
      }
      
      .register-footer a:hover {
        color: #092a49;
        text-decoration: underline;
      }
      
      .role-selector {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
      }
      
      .role-option {
        flex: 1;
        padding: 20px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: #ffffff;
      }
      
      .role-option:hover {
        border-color: #0796fe;
        background: #f0f8ff;
      }
      
      .role-option input[type="radio"] {
        display: none;
      }
      
      .role-option.active {
        border-color: #0796fe;
        background: #0796fe;
        color: white;
      }
      
      .role-option i {
        font-size: 28px;
        display: block;
        margin-bottom: 10px;
      }
      
      .role-option div {
        font-weight: 600;
        font-size: 14px;
      }
      
      @media (max-width: 575.98px) {
        .register-page {
          padding: 40px 15px;
        }
        
        .register-card {
          padding: 30px 20px;
        }
        
        .register-header h2 {
          font-size: 28px;
        }
        
        .role-selector {
          flex-direction: column;
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
      
      .form-group label {
        color: #092a49;
        font-weight: 600;
        margin-bottom: 8px;
        display: block;
      }
    </style>
  </head>
  <body>
    <?php include __DIR__ . '/../../includes/header.php'; ?>

    <!-- Register Page Start -->
    <div class="register-page">
      <div class="container">
        <div class="register-container">
          <div class="register-card">
            <div class="register-header">
              <div class="register-icon">
                <i class="fa fa-user-plus"></i>
              </div>
              <h2>Đăng ký tài khoản</h2>
              <p>Tạo tài khoản mới để bắt đầu sử dụng hệ thống</p>
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
            <?php else: ?>
            
            <form class="register-form" id="registerForm" method="POST" action="">
                <!-- Chọn vai trò -->
                <div class="role-selector">
                    <label class="role-option" for="role_candidate">
                        <input type="radio" name="vai_tro_id" id="role_candidate" value="<?php echo ROLE_CANDIDATE; ?>" checked required>
                        <!-- <i class="fa fa-user"></i> -->
                        <div>Ứng viên</div>
                    </label>
                    <label class="role-option" for="role_recruiter">
                        <input type="radio" name="vai_tro_id" id="role_recruiter" value="<?php echo ROLE_RECRUITER; ?>" required>
                        <!-- <i class="fa fa-briefcase"></i> -->
                        <div>Nhà tuyển dụng</div>
                    </label>
                </div>
                
                <!-- Họ tên -->
                <div class="form-group">
                    <label><i class="fa fa-user"></i> Họ và tên</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fa fa-user"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control with-icon" name="ho_ten" value="<?php echo htmlspecialchars($_POST['ho_ten'] ?? ''); ?>" placeholder="Nhập họ và tên">
                    </div>
                </div>
                
                <!-- Tên đăng nhập -->
                <div class="form-group">
                    <label><i class="fa fa-user-circle"></i> Tên đăng nhập <span style="color: red;">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fa fa-user-circle"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control with-icon" name="ten_dn" value="<?php echo htmlspecialchars($_POST['ten_dn'] ?? ''); ?>" required placeholder="Nhập tên đăng nhập">
                    </div>
                </div>
                
                <!-- Email -->
                <div class="form-group">
                    <label><i class="fa fa-envelope"></i> Email <span style="color: red;">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fa fa-envelope"></i>
                            </span>
                        </div>
                        <input type="email" class="form-control with-icon" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required placeholder="Nhập email">
                    </div>
                </div>
                
                <!-- Số điện thoại -->
                <div class="form-group">
                    <label><i class="fa fa-phone"></i> Số điện thoại</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fa fa-phone"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control with-icon" name="dien_thoai" value="<?php echo htmlspecialchars($_POST['dien_thoai'] ?? ''); ?>" placeholder="Nhập số điện thoại">
                    </div>
                </div>
                
                <!-- Mật khẩu -->
                <div class="form-group">
                    <label><i class="fa fa-lock"></i> Mật khẩu <span style="color: red;">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fa fa-lock"></i>
                            </span>
                        </div>
                        <input type="password" class="form-control with-icon" name="password" required placeholder="Tối thiểu 6 ký tự">
                    </div>
                </div>
                
                <!-- Xác nhận mật khẩu -->
                <div class="form-group">
                    <label><i class="fa fa-lock"></i> Xác nhận mật khẩu <span style="color: red;">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fa fa-lock"></i>
                            </span>
                        </div>
                        <input type="password" class="form-control with-icon" name="password_confirm" required placeholder="Nhập lại mật khẩu">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-register">
                    <i class="fa fa-user-plus"></i> Đăng ký
                </button>
            </form>
            
            <?php endif; ?>
            
            <div class="register-footer">
              <p style="color: #797979; font-size: 14px; margin: 0;">
                Đã có tài khoản? 
                <a href="<?php echo BASE_URL; ?>login.php">Đăng nhập ngay</a>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Register Page End -->
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Xử lý role selector
        $('.role-option').click(function() {
            $('.role-option').removeClass('active');
            $(this).addClass('active');
            $(this).find('input[type="radio"]').prop('checked', true);
        });
        
        // Set active cho role mặc định
        $('input[type="radio"]:checked').closest('.role-option').addClass('active');
    </script>
</body>
</html>

