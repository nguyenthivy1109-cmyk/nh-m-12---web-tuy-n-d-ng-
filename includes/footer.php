<?php
/**
 * Footer chung cho toàn bộ website
 */

// Đảm bảo BASE_URL đã được định nghĩa
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/duantest2/');
}
?>
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
                  <a href="<?php echo BASE_URL; ?>">Trang chủ</a>
                  <a href="<?php echo BASE_URL; ?>pages/public/about.php">Về chúng tôi</a>
                  <a href="<?php echo BASE_URL; ?>pages/public/service.php">Dịch vụ</a>
                  <a href="<?php echo BASE_URL; ?>pages/public/contact.php">Liên hệ</a>
                  <a href="">FQAs</a>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-5">
            <div class="footer-newsletter">
              <h2>Newsletter</h2>
              <p>
                Đăng ký nhận thông tin về các cơ hội việc làm mới nhất từ chúng tôi
              </p>
              <div class="form">
                <input class="form-control" placeholder="Nhập email của bạn" />
                <button class="btn">Đăng ký</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="container copyright">
        <div class="row">
          <div class="col-md-6">
            <p>&copy; <a href="<?php echo BASE_URL; ?>">Job Portal</a>, All Right Reserved.</p>
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

    <!-- Contact Javascript File -->
    <script src="<?php echo BASE_URL; ?>includes/mail/jqBootstrapValidation.min.js"></script>
    <script src="<?php echo BASE_URL; ?>includes/mail/contact.js"></script>

    <!-- Template Javascript -->
    <script src="<?php echo BASE_URL; ?>assets/public/js/main.js"></script>
  </body>
</html>

