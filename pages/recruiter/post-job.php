<?php
/**
 * post-job.php
 * Form Đăng tin tuyển dụng
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

?>
<div class="content-box">
    <form id="postJobForm" class="post-job-form">
        <!-- Tiêu đề -->
        <div class="form-group">
            <label><i class="fa fa-heading"></i> Tiêu đề tin tuyển dụng <span style="color: red;">*</span></label>
            <input type="text" class="form-control" name="tieu_de" required placeholder="VD: Lập trình viên PHP/Laravel">
        </div>

        <!-- Mô tả / Yêu cầu -->
        <div class="form-group">
            <label><i class="fa fa-file-alt"></i> Mô tả công việc <span style="color: red;">*</span></label>
            <textarea class="form-control" name="mo_ta" rows="5" required placeholder="Nhập mô tả chi tiết về công việc..."></textarea>
        </div>

        <div class="form-group">
            <label><i class="fa fa-list"></i> Yêu cầu công việc</label>
            <textarea class="form-control" name="yeu_cau" rows="5" placeholder="Nhập các yêu cầu về kinh nghiệm, kỹ năng..."></textarea>
        </div>

        <!-- Địa điểm + Hạn nộp -->
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label><i class="fa fa-map-marker-alt"></i> Địa điểm làm việc</label>
                    <input type="text" class="form-control" name="dia_diem" placeholder="VD: Hà Nội / TP.HCM / Remote">
                    <!-- map vào cột: noi_lam_viec -->
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label><i class="fa fa-calendar"></i> Hạn nộp hồ sơ</label>
                    <input type="date" class="form-control" name="han_nop">
                    <!-- map vào cột: het_han_luc -->
                </div>
            </div>
        </div>

        <!-- Hình thức làm việc / Chế độ / Cấp độ KN -->
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label><i class="fa fa-briefcase"></i> Hình thức làm việc</label>
                    <select class="form-control" name="hinh_thuc_lv">
                        <option value="">-- Chọn --</option>
                        <option value="1">Full-time</option>
                        <option value="2">Part-time</option>
                        <option value="3">Remote</option>
                        <option value="4">Freelance</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label><i class="fa fa-user-tie"></i> Chế độ làm việc</label>
                    <select class="form-control" name="che_do_lv">
                        <option value="">-- Chọn --</option>
                        <option value="1">Nhân viên</option>
                        <option value="2">Quản lý</option>
                        <option value="3">Giám đốc</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label><i class="fa fa-layer-group"></i> Cấp độ kinh nghiệm</label>
                    <select class="form-control" name="cap_do_kn">
                        <option value="">-- Chọn --</option>
                        <option value="1">Mới tốt nghiệp</option>
                        <option value="2">1 - 3 năm</option>
                        <option value="3">3 - 5 năm</option>
                        <option value="4">5+ năm</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Số lượng / Lương / Tiền tệ -->
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label><i class="fa fa-users"></i> Số lượng tuyển</label>
                    <input type="number" class="form-control" name="so_luong" min="1" value="1">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><i class="fa fa-money-bill-wave"></i> Lương tối thiểu</label>
                    <input type="number" class="form-control" name="luong_min" placeholder="VD: 10000000">
                    <small class="form-text text-muted">Nhập số tiền (VND). VD: 15000000</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><i class="fa fa-money-bill-wave"></i> Lương tối đa</label>
                    <input type="number" class="form-control" name="luong_max" placeholder="VD: 25000000">
                    <small class="form-text text-muted">Để trống nếu thương lượng</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label><i class="fa fa-coins"></i> Tiền tệ</label>
                    <select class="form-control" name="tien_te">
                        <option value="VND" selected>VND</option>
                        <option value="USD">USD</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Thông báo về trạng thái duyệt -->
        <div class="alert alert-info" role="alert" style="margin-bottom: 20px;">
            <i class="fa fa-info-circle"></i> 
            <strong>Lưu ý:</strong> Tin tuyển dụng của bạn sẽ được gửi để admin duyệt. Sau khi được duyệt, tin sẽ hiển thị công khai trên hệ thống.
        </div>

        <!-- Nút -->
        <div class="form-group">
            <button type="submit" class="btn btn-primary-custom action-btn">
                <i class="fa fa-save"></i> Đăng tin
            </button>
            <a href="#" class="btn btn-secondary-custom menu-link action-btn" data-page="jobs">
                <i class="fa fa-times"></i> Hủy
            </a>
        </div>
    </form>
</div>

<style>
.post-job-form .form-group {
    margin-bottom: 22px;
}

.post-job-form label {
    font-weight: 600;
    color: #092a49;
    margin-bottom: 8px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.post-job-form .form-control,
.post-job-form select.form-control {
    height: 48px;
    border: 1.5px solid #d9e2ef;
    border-radius: 8px;
    /* padding: 10px 14px; */
    color: #092a49;
    box-shadow: none;
    background-color: #fff;
}

.post-job-form textarea.form-control {
    min-height: 140px;
}

.post-job-form .form-control:focus,
.post-job-form select.form-control:focus {
    border-color: #0796fe;
    box-shadow: 0 0 0 0.2rem rgba(7, 150, 254, 0.15);
}

.post-job-form select.form-control option {
    color: #092a49;
}

.post-job-form .form-text {
    margin-top: 6px;
    color: #6c7a89;
}

.post-job-form .form-check-input {
    width: 20px;
    height: 20px;
    margin: 0;
    position: static; /* override bootstrap absolute */
    margin-left: 0; /* override bootstrap negative margin */
    vertical-align: middle;
}

.post-job-form .publish-check {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 0;
}

.post-job-form .form-check-label {
    color: #092a49;
    font-weight: 500;
    margin: 0;
}

.post-job-form .action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 48px;
    padding: 0 28px;
    border-radius: 8px;
    transition: all 0.3s;
}

.post-job-form .btn-primary-custom.action-btn {
    background: #0796fe;
    border: none;
}

.post-job-form .btn-primary-custom.action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(7, 150, 254, 0.25);
}

.post-job-form .btn-secondary-custom {
    background: #f0f4fa;
    color: #092a49;
    border: 1px solid #d1d9e6;
    margin-left: 12px;
}

.post-job-form .btn-secondary-custom:hover {
    background: #e2e8f3;
    color: #092a49;
}

@media (max-width: 767px) {
    .post-job-form .action-btn {
        width: 100%;
        margin-left: 0;
    }

    .post-job-form .btn-secondary-custom {
        margin-top: 10px;
    }
    .post-job-form .publish-check {
        width: 100%;
        justify-content: flex-start;
    }
}
</style>

<script>
$(document).ready(function() {
    $('#postJobForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).addClass('disabled');

        $.ajax({
            url: '<?php echo BASE_URL; ?>pages/recruiter/actions/create_job.php',
            type: 'POST',
            data: formData,
            success: function(resp) {
                var data = resp;
                try { if (typeof resp === 'string') { data = JSON.parse(resp); } } catch (e) {}

                if (data && data.success) {
                    $('#content-area').prepend('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + (data.message || 'Đăng tin thành công. Tin của bạn đang chờ admin duyệt.') + '</div>');
                    // Auto hide
                    setTimeout(function(){ $('#content-area .alert-success').fadeOut(300, function(){ $(this).remove(); }); }, 3000);
                    // Điều hướng sang danh sách tin
                    if (typeof loadPage === 'function') {
                        setTimeout(function(){ loadPage('jobs'); }, 1000);
                    }
                } else {
                    var msg = (data && data.message) ? data.message : 'Không thể đăng tin.';
                    $('#content-area').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + msg + '</div>');
                }
            },
            error: function() {
                $('#content-area').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Lỗi kết nối máy chủ</div>');
            },
            complete: function() {
                $btn.prop('disabled', false).removeClass('disabled');
            }
        });
    });
});
</script>

