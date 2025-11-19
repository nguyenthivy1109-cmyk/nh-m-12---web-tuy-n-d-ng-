<?php
/**
 * Dashboard dành cho nhà tuyển dụng
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/nha_tuyen_dung.php';
require_once __DIR__ . '/../../models/tin_td.php';

$user_id = $_SESSION['user_id'] ?? null;
$stats = ['total' => 0, 'active' => 0, 'expired' => 0, 'draft' => 0];
$recent_jobs = [];

if ($user_id) {
    $nha_td = getNhaTuyenDungByTaiKhoanId($pdo, $user_id);
    if ($nha_td) {
        $stats = getTinTuyenDungStats($pdo, $nha_td['nha_td_id']);
        $recent_jobs = getRecentTinByNhaTD($pdo, $nha_td['nha_td_id'], 10);
    }
}

function renderJobStatusBadge($job) {
    $status = (int)($job['trang_thai_tin'] ?? 0);
    $het_han = $job['het_han_luc'] ?? null;
    $now = new DateTime();
    $badgeClass = 'badge-warning';
    $label = 'Chờ duyệt';

    if ($status === 1) {
        if ($het_han && new DateTime($het_han) < $now) {
            $badgeClass = 'badge-danger';
            $label = 'Đã hết hạn';
        } else {
            $badgeClass = 'badge-success';
            $label = 'Đang tuyển';
        }
    } elseif ($status === 0) {
        $badgeClass = 'badge-warning';
        $label = 'Chờ duyệt';
    } else {
        $badgeClass = 'badge-secondary';
        $label = 'Khác';
    }

    return '<span class="badge ' . $badgeClass . '"><i class="fa fa-circle"></i> ' . $label . '</span>';
}

function renderSalaryRange($job) {
    $min = $job['luong_min'];
    $max = $job['luong_max'];
    $currency = $job['tien_te'] ?? 'VND';

    if ($min === null && $max === null) {
        return 'Thỏa thuận';
    }

    $format = function($value) use ($currency) {
        if ($value === null) return '';
        $formatted = number_format((float)$value, 0, ',', '.');
        return $formatted . ' ' . $currency;
    };

    if ($min !== null && $max !== null) {
        return $format($min) . ' - ' . $format($max);
    }

    if ($min !== null) {
        return 'Từ ' . $format($min);
    }

    return 'Đến ' . $format($max);
}
?>

<div class="dashboard-wrapper">
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon"><i class="fa fa-briefcase"></i></div>
            <div class="stat-content">
                <span class="stat-label">Tổng số tin đăng tuyển</span>
                <span class="stat-value"><?php echo $stats['total']; ?></span>
            </div>
        </div>
        <div class="stat-card success">
            <div class="stat-icon"><i class="fa fa-bullhorn"></i></div>
            <div class="stat-content">
                <span class="stat-label">Tin đang tuyển</span>
                <span class="stat-value"><?php echo $stats['active']; ?></span>
            </div>
        </div>
        <div class="stat-card warning">
            <div class="stat-icon"><i class="fa fa-clock"></i></div>
            <div class="stat-content">
                <span class="stat-label">Tin hết hạn</span>
                <span class="stat-value"><?php echo $stats['expired']; ?></span>
            </div>
        </div>
        <div class="stat-card secondary">
            <div class="stat-icon"><i class="fa fa-edit"></i></div>
            <div class="stat-content">
                <span class="stat-label">Tin nháp</span>
                <span class="stat-value"><?php echo $stats['draft']; ?></span>
            </div>
        </div>
    </div>

    <div class="quick-action-card content-box">
        <div>
            <h4><i class="fa fa-bolt"></i> Thao tác nhanh</h4>
            <p>Đăng tin tuyển dụng mới để tiếp cận ứng viên phù hợp.</p>
        </div>
        <a href="<?php echo recruiterRoute('post-job'); ?>" class="btn btn-primary-custom action-btn">
            <i class="fa fa-plus"></i> Đăng tin tuyển dụng
        </a>
    </div>

    <div class="content-box">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4><i class="fa fa-briefcase"></i> Tin tuyển dụng gần đây</h4>
            <a href="<?php echo recruiterRoute('jobs'); ?>" class="view-all"><i class="fa fa-list"></i> Xem tất cả</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover dashboard-table">
                <thead>
                    <tr>
                        <th>Tiêu đề</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Hạn nộp</th>
                        <th>Mức lương</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_jobs)): ?>
                        <?php foreach ($recent_jobs as $job): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($job['tieu_de']); ?></strong>
                                    <div class="sub-meta">Mã tin: #<?php echo $job['tin_id']; ?></div>
                                </td>
                                <td><?php echo renderJobStatusBadge($job); ?></td>
                                <td><?php echo formatDate($job['tao_luc'], 'd/m/Y H:i'); ?></td>
                                <td>
                                    <?php echo $job['het_han_luc'] ? formatDate($job['het_han_luc'], 'd/m/Y') : 'Không giới hạn'; ?>
                                </td>
                                <td><?php echo renderSalaryRange($job); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">Bạn chưa có tin tuyển dụng nào. Bắt đầu bằng cách đăng tin mới.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.dashboard-wrapper {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

.stat-card {
    background: #fff;
    border-radius: 14px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 18px;
    box-shadow: 0 12px 30px rgba(9, 42, 73, 0.08);
    border: 1px solid rgba(9, 42, 73, 0.08);
}

.stat-card .stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
}

.stat-card.primary .stat-icon { background: #0796fe; }
.stat-card.success .stat-icon { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
.stat-card.warning .stat-icon { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); }
.stat-card.secondary .stat-icon { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); }

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-label {
    font-size: 14px;
    color: #6c7a89;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #092a49;
}

.quick-action-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.quick-action-card p {
    margin: 6px 0 0;
    color: #6c7a89;
}

.quick-action-card .action-btn {
    padding: 0 28px;
    height: 48px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.dashboard-table th {
    background: #f8fafc;
    color: #092a49;
    font-weight: 600;
    border-top: none;
}

.dashboard-table td {
    vertical-align: middle;
    color: #092a49;
}

.dashboard-table .sub-meta {
    font-size: 12px;
    color: #6c7a89;
    margin-top: 4px;
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.badge-success { background: rgba(40, 167, 69, 0.15); color: #218838; }
.badge-danger { background: rgba(220, 53, 69, 0.15); color: #c82333; }
.badge-secondary { background: rgba(108, 117, 125, 0.15); color: #6c757d; }

.view-all {
    font-size: 14px;
    color: #0796fe;
    text-decoration: none;
    font-weight: 600;
}

.view-all:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .quick-action-card {
        flex-direction: column;
        align-items: flex-start;
    }

    .quick-action-card .action-btn {
        width: 100%;
    }
}
</style>


