<!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-bell fa-fw"></i> Quản lý Thông báo hệ thống
        </h1>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success" onclick="createNotification()">
                <i class="fas fa-plus"></i> Tạo thông báo
            </button>
            <button type="button" class="btn btn-outline-primary" onclick="refreshNotifications()">
                <i class="fas fa-sync-alt"></i> Làm mới
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng thông báo
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($stats['total']); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bell fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Đã đọc
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($stats['read']); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Chưa đọc
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($stats['unread']); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-envelope fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Loại phổ biến nhất
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php
                                $max_type = array_keys($stats['by_type'], max($stats['by_type']))[0];
                                echo htmlspecialchars($notification_types[$max_type]['label']);
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Bộ lọc
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="filter_status" class="form-label">Trạng thái</label>
                    <select class="form-select" id="filter_status" name="filter_status">
                        <option value="">Tất cả</option>
                        <option value="read" <?php echo ($filter_status === 'read') ? 'selected' : ''; ?>>Đã đọc</option>
                        <option value="unread" <?php echo ($filter_status === 'unread') ? 'selected' : ''; ?>>Chưa đọc</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_type" class="form-label">Loại thông báo</label>
                    <select class="form-select" id="filter_type" name="filter_type">
                        <option value="">Tất cả</option>
                        <?php foreach ($notification_types as $type_id => $type_info): ?>
                        <option value="<?php echo $type_id; ?>" <?php echo ($filter_type == $type_id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type_info['label']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_user" class="form-label">Người nhận</label>
                    <select class="form-select" id="filter_user" name="filter_user">
                        <option value="">Tất cả</option>
                        <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['tai_khoan_id']; ?>" <?php echo ($filter_user == $user['tai_khoan_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['ten_dn']); ?> (<?php echo htmlspecialchars($user['ten_vai_tro']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Lọc
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Xóa bộ lọc
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Notifications Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Danh sách Thông báo
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="notificationsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Người nhận</th>
                            <th>Loại</th>
                            <th>Tiêu đề</th>
                            <th>Nội dung</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notifications as $notif): ?>
                        <tr>
                            <td><?php echo $notif['tb_id']; ?></td>
                            <td>
                                <div><?php echo htmlspecialchars($notif['ten_dn']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($notif['ho_ten'] ?? 'N/A'); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $notification_types[$notif['loai_tb']]['class']; ?>">
                                    <?php echo htmlspecialchars($notification_types[$notif['loai_tb']]['label']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($notif['tieu_de']); ?></td>
                            <td>
                                <div class="notification-content">
                                    <?php
                                    $content = htmlspecialchars($notif['noi_dung'] ?? '');
                                    echo strlen($content) > 100 ? substr($content, 0, 100) . '...' : $content;
                                    ?>
                                </div>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($notif['tao_luc'])); ?></td>
                            <td>
                                <?php if ($notif['da_doc']): ?>
                                    <span class="badge bg-success">Đã đọc</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Chưa đọc</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-info" onclick="viewNotification(<?php echo $notif['tb_id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($notif['da_doc']): ?>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="markAsUnread(<?php echo $notif['tb_id']; ?>)">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                    <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-success" onclick="markAsRead(<?php echo $notif['tb_id']; ?>)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteNotification(<?php echo $notif['tb_id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Notification Modal -->
<div class="modal fade" id="createNotificationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tạo Thông báo mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createNotificationForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_notification">
                    <div class="mb-3">
                        <label for="target_audience" class="form-label">Đối tượng nhận *</label>
                        <select class="form-select" id="target_audience" name="target_audience" required>
                            <option value="">Chọn đối tượng...</option>
                            <option value="all">Tất cả người dùng</option>
                            <option value="candidates">Chỉ ứng viên</option>
                            <option value="recruiters">Chỉ nhà tuyển dụng</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="loai_tb" class="form-label">Loại thông báo *</label>
                        <select class="form-select" id="loai_tb" name="loai_tb" required>
                            <option value="">Chọn loại...</option>
                            <?php foreach ($notification_types as $type_id => $type_info): ?>
                            <option value="<?php echo $type_id; ?>"><?php echo htmlspecialchars($type_info['label']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tieu_de" class="form-label">Tiêu đề *</label>
                        <input type="text" class="form-control" id="tieu_de" name="tieu_de" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label for="noi_dung" class="form-label">Nội dung</label>
                        <textarea class="form-control" id="noi_dung" name="noi_dung" rows="4" placeholder="Nội dung thông báo..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Tạo thông báo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Notification Modal -->
<div class="modal fade" id="viewNotificationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết Thông báo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="notificationDetails">
                <!-- Notification details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Notification Modal -->
<div class="modal fade" id="deleteNotificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xóa Thông báo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteNotificationForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete_notification">
                    <input type="hidden" name="tb_id" id="delete_tb_id">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Bạn có chắc chắn muốn xóa thông báo này? Hành động này không thể hoàn tác.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa thông báo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Initialize DataTable
$(document).ready(function() {
    $('#notificationsTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Vietnamese.json"
        },
        "order": [[ 0, "desc" ]],
        "pageLength": 25,
        "responsive": true
    });
});

// Create notification
function createNotification() {
    $('#createNotificationModal').modal('show');
}

// View notification details
function viewNotification(tbId) {
    // For now, just show a placeholder. In a real implementation, you'd load the full notification content
    $('#notificationDetails').html(`
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Tính năng xem chi tiết thông báo đang được phát triển.
            <br>Thông báo ID: ${tbId}
        </div>
    `);
    $('#viewNotificationModal').modal('show');
}

// Mark as read
function markAsRead(tbId) {
    const form = $('<form method="POST" style="display: none;">');
    form.append('<input name="action" value="mark_as_read">');
    form.append('<input name="tb_id" value="' + tbId + '">');
    $('body').append(form);
    form.submit();
}

// Mark as unread
function markAsUnread(tbId) {
    const form = $('<form method="POST" style="display: none;">');
    form.append('<input name="action" value="mark_as_unread">');
    form.append('<input name="tb_id" value="' + tbId + '">');
    $('body').append(form);
    form.submit();
}

// Delete notification
function deleteNotification(tbId) {
    $('#delete_tb_id').val(tbId);
    $('#deleteNotificationModal').modal('show');
}

// Refresh notifications
function refreshNotifications() {
    location.reload();
}
</script>