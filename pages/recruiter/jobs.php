<?php
/**
 * Danh sách tin tuyển dụng của nhà tuyển dụng
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/nha_tuyen_dung.php';
require_once __DIR__ . '/../../models/tin_td.php';

$user_id = $_SESSION['user_id'] ?? null;
$jobs = [];
$maps = [
    'hinh_thuc' => [1 => 'Full-time', 2 => 'Part-time', 3 => 'Remote', 4 => 'Freelance'],
    'che_do'    => [1 => 'Nhân viên', 2 => 'Quản lý', 3 => 'Giám đốc'],
    'cap_do'    => [1 => 'Mới tốt nghiệp', 2 => '1-3 năm', 3 => '3-5 năm', 4 => '5+ năm'],
];

$status_map = [
    JOB_STATUS_DRAFT => ['label' => 'Chờ duyệt', 'icon' => 'fa-clock', 'class' => 'badge-pending'],
    JOB_STATUS_ACTIVE => ['label' => 'Đang tuyển', 'icon' => 'fa-bullhorn', 'class' => 'badge-dangtuyen'],
    JOB_STATUS_PAUSED => ['label' => 'Tạm dừng', 'icon' => 'fa-pause', 'class' => 'badge-paused'],
];

if ($user_id) {
    $nha_td = getNhaTuyenDungByTaiKhoanId($pdo, $user_id);
    if ($nha_td) {
        $jobs = getTinTuyenDungByNhaTD($pdo, $nha_td['nha_td_id'], $nha_td['cong_ty_id'] ?? null);
    }
}

function format_money($amount, $currency = 'VND') {
    if ($amount === null || $amount === '') return '';
    if (strtoupper($currency) === 'USD') {
        return number_format((float)$amount, 0) . ' USD';
    }
    return number_format((float)$amount, 0, ',', '.') . ' VND';
}

function renderStatusBadge($job, $status_map) {
    $status = (int)($job['trang_thai_tin'] ?? 0);
    $status_info = $status_map[$status] ?? $status_map[JOB_STATUS_DRAFT];
    return '<span class="badge-status ' . $status_info['class'] . '"><i class="fa ' . $status_info['icon'] . '"></i> ' . $status_info['label'] . '</span>';
}
?>

<style>
.job-card {
    border: 1px solid #dce5f3;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 14px;
    transition: box-shadow .2s ease;
    background: #fff;
}
.job-card:hover {
    box-shadow: 0 10px 24px rgba(7, 150, 254, 0.08);
}
.job-card .job-title {
    font-weight: 600;
    color: #092a49;
    font-size: 18px;
    margin-bottom: 6px;
    line-height: 1.3;
}
.job-card .company {
    color: #6b7280;
    font-size: 14px;
}
.job-card .chips {
    margin-top: 6px;
    display: flex;
    flex-wrap: wrap;
    gap: 2px 6px;
}
.job-card .chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    margin-right: 0;
    margin-bottom: 8px;
    font-size: 12px;
    color: #092a49;
    background: #f9fbff;
}
.job-card .meta {
    color: #6b7280;
    font-size: 13px;
    margin-top: 8px;
}
.job-card .right-col {
    text-align: right;
}
.job-card .salary {
    color: #0796fe;
    font-weight: 700;
    white-space: nowrap;
    font-size: 16px;
}
.badge-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 600;
}
.badge-dangtuyen { background: rgba(7, 150, 254, 0.12); color: #0a6cd6; border: 1px solid rgba(7, 150, 254, 0.3); }
.badge-pending { background: rgba(255, 193, 7, 0.15); color: #a56d00; border: 1px solid rgba(255, 193, 7, 0.35); }
.badge-nhap { background: rgba(255, 193, 7, 0.15); color: #a56d00; border: 1px solid rgba(255, 193, 7, 0.35); } /* Giữ lại để tương thích */
.badge-paused { background: rgba(255, 152, 0, 0.15); color: #e65100; border: 1px solid rgba(255, 152, 0, 0.35); }
.badge-closed { background: rgba(108, 117, 125, 0.15); color: #495057; border: 1px solid rgba(108, 117, 125, 0.35); }
.btn-ghost {
    border: 1px solid #dc3545;
    background: #fff;
    color: #dc3545;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 13px;
    transition: all .2s ease;
    cursor: pointer;
}
.btn-ghost:hover {
    background: #dc3545;
    color: #fff;
}
.btn-ghost:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.job-detail {
    display: none;
    padding: 12px 14px 6px 14px;
    border-top: 1px dashed #e5e7eb;
    margin-top: 10px;
    background: #fbfdff;
}
</style>

<div class="content-box">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 style="margin:0;"><i class="fa fa-briefcase"></i> Tin tuyển dụng</h4>
        <a href="#" class="btn btn-primary-custom menu-link" data-page="post-job">
            <i class="fa fa-plus"></i> Đăng tin mới
        </a>
    </div>

    <?php if (empty($jobs)): ?>
        <div class="text-center text-muted" style="padding: 30px 0;">
            Bạn chưa có tin tuyển dụng nào. Hãy bắt đầu bằng cách đăng tin mới.
        </div>
    <?php else: ?>
        <?php foreach ($jobs as $job):
            $salary_text = 'Thỏa thuận';
            if ($job['luong_min'] !== null || $job['luong_max'] !== null) {
                $min = $job['luong_min'] ? format_money($job['luong_min'], $job['tien_te']) : null;
                $max = $job['luong_max'] ? format_money($job['luong_max'], $job['tien_te']) : null;
                if ($min && $max) {
                    $salary_text = $min . ' - ' . $max;
                } elseif ($min) {
                    $salary_text = 'Từ ' . $min;
                } elseif ($max) {
                    $salary_text = 'Đến ' . $max;
                }
            }
            $statusBadge = renderStatusBadge($job, $status_map);
            $postedAt = $job['dang_luc'] ?: $job['tao_luc'];
            $current_status = (int)$job['trang_thai_tin'];
        ?>
        <div class="job-card" data-id="<?php echo (int)$job['tin_id']; ?>">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="job-title">
                        <a href="#" class="js-open-detail" style="color: inherit; text-decoration:none;">
                            <?php echo htmlspecialchars($job['tieu_de']); ?>
                        </a>
                    </div>
                    <div class="company">
                        <?php echo htmlspecialchars($job['ten_cong_ty'] ?: 'Công ty'); ?>
                    </div>
                    <div class="chips">
                        <?php if (!empty($job['noi_lam_viec'])): ?>
                            <span class="chip"><i class="fa fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['noi_lam_viec']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($job['cap_do_kn'])): ?>
                            <span class="chip"><i class="fa fa-briefcase"></i> <?php echo $maps['cap_do'][(int)$job['cap_do_kn']] ?? '—'; ?></span>
                        <?php endif; ?>
                        <?php if (!empty($job['hinh_thuc_lv'])): ?>
                            <span class="chip"><i class="fa fa-clock"></i> <?php echo $maps['hinh_thuc'][(int)$job['hinh_thuc_lv']] ?? '—'; ?></span>
                        <?php endif; ?>
                        <?php if (!empty($job['che_do_lv'])): ?>
                            <span class="chip"><i class="fa fa-user-tie"></i> <?php echo $maps['che_do'][(int)$job['che_do_lv']] ?? '—'; ?></span>
                        <?php endif; ?>
                        <?php if (!empty($job['so_luong'])): ?>
                            <span class="chip"><i class="fa fa-users"></i> <?php echo (int)$job['so_luong']; ?> người</span>
                        <?php endif; ?>
                    </div>
                    <div class="meta">
                        <?php echo $statusBadge; ?> &nbsp;•&nbsp;
                        Đăng: <?php echo $postedAt ? formatDate($postedAt, 'd/m/Y') : '—'; ?> &nbsp;•&nbsp;
                        Ứng tuyển: <strong><?php echo (int)$job['so_ung_tuyen']; ?></strong>
                    </div>
                </div>
                <div class="col-md-4 right-col">
                    <div class="salary"><?php echo $salary_text; ?></div>
                    <div style="margin-top:10px;">
                        <button class="btn-ghost js-delete-job" data-tin-id="<?php echo (int)$job['tin_id']; ?>" data-job-title="<?php echo htmlspecialchars($job['tieu_de']); ?>">
                            <i class="fa fa-trash"></i> Xóa tin
                        </button>
                    </div>
                </div>
            </div>

            <div class="job-detail">
                <div class="row">
                    <div class="col-md-8">
                        <h5 style="margin:8px 0 6px 0;">Mô tả công việc</h5>
                        <div style="white-space:pre-line;"><?php echo nl2br(htmlspecialchars($job['mo_ta'])); ?></div>

                        <?php if (!empty($job['yeu_cau'])): ?>
                            <h5 style="margin:16px 0 6px 0;">Yêu cầu</h5>
                            <div style="white-space:pre-line;"><?php echo nl2br(htmlspecialchars($job['yeu_cau'])); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <h5 style="margin:8px 0 10px 0;">Thông tin nhanh</h5>
                        <ul class="ul-group">
                            <li><strong>Trạng thái:</strong> <?php echo $status_map[$current_status]['label'] ?? '—'; ?></li>
                            <li><strong>Đăng lúc:</strong> <?php echo $job['dang_luc'] ? formatDate($job['dang_luc'], 'd/m/Y H:i') : '—'; ?></li>
                            <li><strong>Hạn nộp:</strong> <?php echo $job['het_han_luc'] ? formatDate($job['het_han_luc'], 'd/m/Y') : 'Không giới hạn'; ?></li>
                            <li><strong>Ứng tuyển:</strong> <?php echo (int)$job['so_ung_tuyen']; ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
// Đảm bảo script chạy sau khi jQuery và DOM sẵn sàng
(function($) {
    'use strict';
    
    // Function để init event handlers
    function initJobHandlers() {
        // Xử lý click để xem chi tiết
        $(document).off('click', '.js-open-detail').on('click', '.js-open-detail', function(e) {
            e.preventDefault();
            const $card = $(this).closest('.job-card');
            $card.find('.job-detail').slideToggle(150);
        });

        // Xóa tin tuyển dụng
        $(document).off('click', '.js-delete-job').on('click', '.js-delete-job', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $btn = $(this);
            const tinId = $btn.data('tin-id');
            const jobTitle = $btn.data('job-title');
            
            if (!confirm('Bạn có chắc chắn muốn xóa tin tuyển dụng "' + jobTitle + '" không?\n\nLưu ý: Tin sẽ bị xóa và không thể khôi phục.')) {
                return;
            }
            
            $btn.prop('disabled', true);
            
            $.ajax({
                url: '<?php echo BASE_URL; ?>pages/recruiter/actions/delete_job.php',
                type: 'POST',
                data: { tin_id: tinId },
                dataType: 'json',
                success: function(res) {
                    var data = res;
                    try { 
                        if (typeof res === 'string') { 
                            data = JSON.parse(res); 
                        } 
                    } catch(e) {
                        console.error('Parse error:', e, res);
                    }
                    
                    if (data && data.success) {
                        // Ẩn card với hiệu ứng
                        const $card = $btn.closest('.job-card');
                        $card.fadeOut(300, function() {
                            $(this).remove();
                            
                            // Kiểm tra nếu không còn tin nào, reload trang
                            if ($('.job-card').length === 0) {
                                location.reload();
                            }
                        });
                        
                        // Hiển thị thông báo thành công
                        $('#content-area').prepend('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + (data.message || 'Đã xóa tin tuyển dụng thành công') + '</div>');
                        setTimeout(function() {
                            $('.alert-success').fadeOut(300, function() { $(this).remove(); });
                        }, 3000);
                    } else {
                        const errorMsg = (data && data.message) ? data.message : 'Không thể xóa tin. Vui lòng thử lại.';
                        console.error('Delete failed:', data);
                        alert(errorMsg);
                        $btn.prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error, xhr.responseText);
                    let errorMsg = 'Không thể kết nối máy chủ.';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMsg = response.message;
                        }
                    } catch(e) {
                        // Nếu không parse được JSON, dùng message mặc định
                    }
                    alert(errorMsg);
                    $btn.prop('disabled', false);
                }
            });
        });
    }
    
    // Chạy ngay khi script được load (cho trường hợp load trực tiếp)
    if (typeof $ !== 'undefined' && $.fn.jquery) {
        initJobHandlers();
    } else {
        // Nếu jQuery chưa sẵn sàng, đợi document ready
        $(document).ready(function() {
            initJobHandlers();
        });
    }
    
    // Export function để có thể gọi lại từ layout.php nếu cần
    if (typeof window !== 'undefined') {
        window.initJobsPage = initJobHandlers;
    }
})(jQuery || $);
</script>

