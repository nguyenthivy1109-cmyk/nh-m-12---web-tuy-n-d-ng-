<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="utf-8" />
    <title>Liên hệ - Hệ thống tuyển dụng</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta content="Liên hệ với chúng tôi, hệ thống tuyển dụng" name="keywords" />
    <meta content="Liên hệ với chúng tôi để được hỗ trợ" name="description" />

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
  </head>

  <body class="page">
    <?php include __DIR__ . '/../../includes/header.php'; ?>

    <!-- Contact Start -->
    <div class="contact" style="padding-top: 40px;">
      <div class="container">
        <div class="section-header">
          <p>Liên hệ với chúng tôi</p>
          <h2>Liên hệ để được hỗ trợ</h2>
        </div>
        <div class="row align-items-center">
          <div class="col-md-5">
            <div class="contact-info">
              <div class="contact-icon">
                <i class="fa fa-map-marker-alt"></i>
              </div>
              <div class="contact-text">
                <h3>Văn phòng chính</h3>
                <p>123 Đường ABC, Quận XYZ, TP. Hồ Chí Minh</p>
              </div>
            </div>
            <div class="contact-info">
              <div class="contact-icon">
                <i class="fa fa-phone-alt"></i>
              </div>
              <div class="contact-text">
                <h3>Hotline hỗ trợ</h3>
                <p>+84 123 456 789</p>
              </div>
            </div>
            <div class="contact-info">
              <div class="contact-icon">
                <i class="fa fa-envelope"></i>
              </div>
              <div class="contact-text">
                <h3>Email liên hệ</h3>
                <p>info@tuyendung.com</p>
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
                    placeholder="Họ và tên"
                    required="required"
                    data-validation-required-message="Vui lòng nhập họ và tên"
                  />
                  <p class="help-block text-danger"></p>
                </div>
                <div class="control-group">
                  <input
                    type="email"
                    class="form-control"
                    id="email"
                    placeholder="Email của bạn"
                    required="required"
                    data-validation-required-message="Vui lòng nhập email"
                  />
                  <p class="help-block text-danger"></p>
                </div>
                <div class="control-group">
                  <input
                    type="text"
                    class="form-control"
                    id="subject"
                    placeholder="Chủ đề"
                    required="required"
                    data-validation-required-message="Vui lòng nhập chủ đề"
                  />
                  <p class="help-block text-danger"></p>
                </div>
                <div class="control-group">
                  <textarea
                    class="form-control"
                    id="message"
                    placeholder="Nội dung tin nhắn"
                    required="required"
                    data-validation-required-message="Vui lòng nhập nội dung"
                  ></textarea>
                  <p class="help-block text-danger"></p>
                </div>
                <div>
                  <button class="btn" type="submit" id="sendMessageButton">
                    Gửi tin nhắn
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
