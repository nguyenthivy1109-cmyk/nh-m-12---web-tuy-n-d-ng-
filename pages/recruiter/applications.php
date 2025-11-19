<?php
/**
 * Applications Content - Quản lý ứng tuyển
 * Hiển thị danh sách ứng viên đã ứng tuyển vào các tin tuyển dụng của nhà tuyển dụng
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/nha_tuyen_dung.php';
require_once __DIR__ . '/../../models/tin_td.php';
require_once __DIR__ . '/../../models/ung_tuyen.php';

$user_id = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Lấy thông tin nhà tuyển dụng
$nha_td = getNhaTuyenDungByTaiKhoanId($pdo, $user_id);
if (!$nha_td) {
    $message = 'Không tìm thấy thông tin nhà tuyển dụng.';
    $messageType = 'danger';
} else {
    $nha_td_id = $nha_td['nha_td_id'];
    
    // Xử lý cập nhật trạng thái ứng tuyển
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
        $ung_tuyen_id = (int)($_POST['ung_tuyen_id'] ?? 0);
        $trang_thai_ut = (int)($_POST['trang_thai_ut'] ?? 0);
        
        if ($ung_tuyen_id > 0) {
            // Kiểm tra ứng tuyển có thuộc về nhà tuyển dụng này không
            $stmt = $pdo->prepare("
                SELECT ut.ung_tuyen_id 
                FROM ung_tuyens ut
                INNER JOIN tin_td t ON ut.tin_id = t.tin_id
                WHERE ut.ung_tuyen_id = ? AND t.nha_td_id = ?
            ");
            $stmt->execute([$ung_tuyen_id, $nha_td_id]);
            
            if ($stmt->fetch()) {
                if (updateTrangThaiUngTuyen($pdo, $ung_tuyen_id, $trang_thai_ut)) {
                    $message = 'Cập nhật trạng thái ứng tuyển thành công!';
                    $messageType = 'success';
                } else {
                    $message = 'Không thể cập nhật trạng thái.';
                    $messageType = 'danger';
                }
            } else {
                $message = 'Không có quyền cập nhật ứng tuyển này.';
                $messageType = 'danger';
            }
        }
    }
    
    // Lấy tất cả ứng tuyển cho các tin của nhà tuyển dụng này
    $query = "
        SELECT 
            ut.ung_tuyen_id,
            ut.tin_id,
            ut.ung_vien_id,
            ut.cv_id,
            ut.trang_thai_ut,
            ut.nop_luc,
            ut.thu_ung_tuyen,
            t.tieu_de as job_title,
            t.luong_min,
            t.luong_max,
            t.tien_te,
            uv.ho_ten as candidate_name,
            uv.tieu_de_cv,
            uv.gioi_thieu,
            tk.email,
            tk.dien_thoai,
            dk.tep_url as cv_url,
            dk.ten_tep as cv_name
        FROM ung_tuyens ut
        INNER JOIN tin_td t ON ut.tin_id = t.tin_id
        INNER JOIN ung_viens uv ON ut.ung_vien_id = uv.ung_vien_id
        INNER JOIN tai_khoans tk ON uv.tai_khoan_id = tk.tai_khoan_id
        LEFT JOIN dinh_kems dk ON ut.cv_id = dk.dk_id
        WHERE t.nha_td_id = ? AND t.xoa_luc IS NULL
        ORDER BY ut.nop_luc DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$nha_td_id]);
    $applications = $stmt->fetchAll();
}

// Định nghĩa trạng thái
$status_labels = [
    0 => ['label' => 'Chưa xử lý', 'class' => 'secondary'],
    1 => ['label' => 'Đã xem', 'class' => 'info'],
    2 => ['label' => 'Phù hợp', 'class' => 'success'],
    3 => ['label' => 'Không phù hợp', 'class' => 'danger'],
    4 => ['label' => 'Đã mời phỏng vấn', 'class' => 'primary'],
    5 => ['label' => 'Đã từ chối', 'class' => 'warning']
];
?>

<div class="content-box">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 style="margin:0;"><i class="fa fa-file-alt"></i> Quản lý ứng tuyển</h4>
    </div>
    
    <?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php endif; ?>
    
    <?php if (empty($applications)): ?>
    <div class="text-center text-muted" style="padding: 30px 0;">
        <i class="fa fa-info-circle" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
        <p>Chưa có ứng viên nào ứng tuyển vào các tin tuyển dụng của bạn.</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover dashboard-table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Ứng viên</th>
                    <th>Vị trí ứng tuyển</th>
                    <th>Thông tin liên hệ</th>
                    <th>Ngày ứng tuyển</th>
                    <th>Trạng thái</th>
                    <th>CV</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $index => $app): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($app['candidate_name']); ?></strong>
                        <?php if ($app['tieu_de_cv']): ?>
                            <br><small class="text-muted"><?php echo htmlspecialchars($app['tieu_de_cv']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($app['job_title']); ?></strong>
                        <?php if ($app['luong_min'] || $app['luong_max']): ?>
                            <br><small class="text-muted">
                                Lương: 
                                <?php 
                                if ($app['luong_min'] && $app['luong_max']) {
                                    echo number_format($app['luong_min']) . ' - ' . number_format($app['luong_max']);
                                } elseif ($app['luong_min']) {
                                    echo 'Từ ' . number_format($app['luong_min']);
                                } elseif ($app['luong_max']) {
                                    echo 'Đến ' . number_format($app['luong_max']);
                                }
                                echo ' ' . htmlspecialchars($app['tien_te'] ?? 'VND');
                                ?>
                            </small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($app['email']): ?>
                            <i class="fa fa-envelope"></i> <?php echo htmlspecialchars($app['email']); ?><br>
                        <?php endif; ?>
                        <?php if ($app['dien_thoai']): ?>
                            <i class="fa fa-phone"></i> <?php echo htmlspecialchars($app['dien_thoai']); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($app['nop_luc'])); ?></td>
                    <td>
                        <span class="badge badge-status badge-<?php echo isset($status_labels[$app['trang_thai_ut']]) ? $status_labels[$app['trang_thai_ut']]['class'] : 'secondary'; ?>">
                            <?php echo isset($status_labels[$app['trang_thai_ut']]) ? $status_labels[$app['trang_thai_ut']]['label'] : 'Không xác định'; ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($app['cv_url']): ?>
                            <a href="<?php echo BASE_URL . ltrim($app['cv_url'], '/'); ?>" 
                               target="_blank" 
                               class="btn-ghost">
                                <i class="fa fa-file-pdf"></i> Xem CV
                            </a>
                        <?php else: ?>
                            <span class="text-muted">Chưa có CV</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <button type="button" 
                                    class="btn-ghost" 
                                    data-toggle="modal" 
                                    data-target="#updateStatusModal<?php echo $app['ung_tuyen_id']; ?>">
                                <i class="fa fa-edit"></i> Cập nhật
                            </button>
                            <button type="button" 
                                    class="btn-ghost" 
                                    data-toggle="modal" 
                                    data-target="#viewDetailModal<?php echo $app['ung_tuyen_id']; ?>">
                                <i class="fa fa-eye"></i> Chi tiết
                            </button>
                        </div>
                    </td>
                </tr>
                
                <!-- Modal Cập nhật trạng thái -->
                <div class="modal fade" id="updateStatusModal<?php echo $app['ung_tuyen_id']; ?>" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Cập nhật trạng thái ứng tuyển</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="ung_tuyen_id" value="<?php echo $app['ung_tuyen_id']; ?>">
                                    
                                    <div class="form-group">
                                        <label>Ứng viên:</label>
                                        <p><strong><?php echo htmlspecialchars($app['candidate_name']); ?></strong></p>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Vị trí:</label>
                                        <p><?php echo htmlspecialchars($app['job_title']); ?></p>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="trang_thai_ut_<?php echo $app['ung_tuyen_id']; ?>">Trạng thái mới:</label>
                                        <select class="form-control" name="trang_thai_ut" id="trang_thai_ut_<?php echo $app['ung_tuyen_id']; ?>" required>
                                            <?php foreach ($status_labels as $value => $status): ?>
                                                <option value="<?php echo $value; ?>" <?php echo $app['trang_thai_ut'] == $value ? 'selected' : ''; ?>>
                                                    <?php echo $status['label']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                                    <button type="submit" class="btn btn-primary-custom">Cập nhật</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Chi tiết -->
                <div class="modal fade" id="viewDetailModal<?php echo $app['ung_tuyen_id']; ?>" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Chi tiết ứng tuyển</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="fa fa-user"></i> Thông tin ứng viên</h6>
                                        <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($app['candidate_name']); ?></p>
                                        <?php if ($app['tieu_de_cv']): ?>
                                            <p><strong>Tiêu đề CV:</strong> <?php echo htmlspecialchars($app['tieu_de_cv']); ?></p>
                                        <?php endif; ?>
                                        <?php if ($app['email']): ?>
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($app['email']); ?></p>
                                        <?php endif; ?>
                                        <?php if ($app['dien_thoai']): ?>
                                            <p><strong>Điện thoại:</strong> <?php echo htmlspecialchars($app['dien_thoai']); ?></p>
                                        <?php endif; ?>
                                        <?php if ($app['gioi_thieu']): ?>
                                            <p><strong>Giới thiệu:</strong></p>
                                            <p><?php echo nl2br(htmlspecialchars($app['gioi_thieu'])); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="fa fa-briefcase"></i> Thông tin vị trí</h6>
                                        <p><strong>Vị trí:</strong> <?php echo htmlspecialchars($app['job_title']); ?></p>
                                        <?php if ($app['luong_min'] || $app['luong_max']): ?>
                                            <p><strong>Mức lương:</strong>
                                                <?php 
                                                if ($app['luong_min'] && $app['luong_max']) {
                                                    echo number_format($app['luong_min']) . ' - ' . number_format($app['luong_max']);
                                                } elseif ($app['luong_min']) {
                                                    echo 'Từ ' . number_format($app['luong_min']);
                                                } elseif ($app['luong_max']) {
                                                    echo 'Đến ' . number_format($app['luong_max']);
                                                }
                                                echo ' ' . htmlspecialchars($app['tien_te'] ?? 'VND');
                                                ?>
                                            </p>
                                        <?php endif; ?>
                                        <p><strong>Ngày ứng tuyển:</strong> <?php echo date('d/m/Y H:i', strtotime($app['nop_luc'])); ?></p>
                                        <p><strong>Trạng thái:</strong> 
                                            <span class="badge badge-<?php echo isset($status_labels[$app['trang_thai_ut']]) ? $status_labels[$app['trang_thai_ut']]['class'] : 'secondary'; ?>">
                                                <?php echo isset($status_labels[$app['trang_thai_ut']]) ? $status_labels[$app['trang_thai_ut']]['label'] : 'Không xác định'; ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <?php if ($app['thu_ung_tuyen']): ?>
                                <div class="mt-3">
                                    <h6><i class="fa fa-envelope-open-text"></i> Thư ứng tuyển</h6>
                                    <div class="border p-3 rounded">
                                        <?php echo nl2br(htmlspecialchars($app['thu_ung_tuyen'])); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($app['cv_url']): ?>
                                <div class="mt-3">
                                    <h6><i class="fa fa-file-pdf"></i> CV</h6>
                                    <a href="<?php echo BASE_URL . ltrim($app['cv_url'], '/'); ?>" 
                                       target="_blank" 
                                       class="btn btn-primary-custom">
                                        <i class="fa fa-download"></i> Tải xuống CV
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<style>
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

.badge-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.badge-secondary { background: rgba(108, 117, 125, 0.15); color: #6c757d; }
.badge-info { background: rgba(23, 162, 184, 0.15); color: #138496; }
.badge-success { background: rgba(40, 167, 69, 0.15); color: #218838; }
.badge-danger { background: rgba(220, 53, 69, 0.15); color: #c82333; }
.badge-primary { background: rgba(7, 150, 254, 0.15); color: #0a6cd6; }
.badge-warning { background: rgba(255, 193, 7, 0.15); color: #856404; }

.btn-ghost {
    border: 1px solid #092a49;
    background: #fff;
    color: #092a49;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 13px;
    transition: all .2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
}

.btn-ghost:hover {
    background: #092a49;
    color: #0796fe;
    text-decoration: none;
}

.btn-primary-custom {
    background: #0796fe;
    color: #fff;
    border: none;
    padding: 12px 30px;
    border-radius: 5px;
    font-weight: 600;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary-custom:hover {
    background: #0684e0;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(7, 150, 254, 0.3);
}

.btn-secondary {
    background: #6c757d;
    color: #fff;
    border: none;
    padding: 12px 30px;
    border-radius: 5px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-secondary:hover {
    background: #5a6268;
    color: #fff;
}
</style>
