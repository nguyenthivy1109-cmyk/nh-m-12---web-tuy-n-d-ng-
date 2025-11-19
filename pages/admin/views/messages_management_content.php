<!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-comments fa-fw"></i> Quản lý Tin nhắn
        </h1>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary" onclick="refreshMessages()">
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
                                Tổng tin nhắn
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($stats['total']); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-comments fa-2x text-gray-300"></i>
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
                                7 ngày gần nhất
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($stats['recent']); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                Ứng tuyển có tin nhắn
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($stats['applications']); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
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
                <div class="col-md-4">
                    <label for="filter_type" class="form-label">Loại bộ lọc</label>
                    <select class="form-select" id="filter_type" name="filter_type" onchange="updateFilterOptions()">
                        <option value="">Tất cả tin nhắn</option>
                        <option value="application" <?php echo ($filter_type === 'application') ? 'selected' : ''; ?>>Theo ứng tuyển</option>
                        <option value="sender" <?php echo ($filter_type === 'sender') ? 'selected' : ''; ?>>Theo người gửi</option>
                        <option value="receiver" <?php echo ($filter_type === 'receiver') ? 'selected' : ''; ?>>Theo người nhận</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter_id" class="form-label">Chi tiết bộ lọc</label>
                    <select class="form-select" id="filter_id" name="filter_id">
                        <option value="">Chọn...</option>
                        <?php if ($filter_type === 'application'): ?>
                            <?php foreach ($apps_with_msgs as $app): ?>
                            <option value="<?php echo $app['ung_tuyen_id']; ?>" <?php echo ($filter_id == $app['ung_tuyen_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($app['app_title']); ?> (<?php echo $app['msg_count']; ?> tin nhắn)
                            </option>
                            <?php endforeach; ?>
                        <?php elseif ($filter_type === 'sender'): ?>
                            <?php foreach ($senders as $sender): ?>
                            <option value="<?php echo $sender['tai_khoan_id']; ?>" <?php echo ($filter_id == $sender['tai_khoan_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sender['ten_dn']); ?> (<?php echo $sender['msg_count']; ?> tin nhắn)
                            </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
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

    <!-- Messages Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Danh sách Tin nhắn
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="messagesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ứng tuyển</th>
                            <th>Người gửi</th>
                            <th>Người nhận</th>
                            <th>Nội dung</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td><?php echo $msg['tn_id']; ?></td>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($msg['job_title']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($msg['candidate_name']); ?> → <?php echo htmlspecialchars($msg['company_name']); ?></small>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($msg['sender_username']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($msg['employer_name'] ?? $msg['candidate_name']); ?></small>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($msg['receiver_username']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($msg['candidate_name'] ?? $msg['employer_name']); ?></small>
                            </td>
                            <td>
                                <div class="message-content">
                                    <?php
                                    $content = htmlspecialchars($msg['noi_dung']);
                                    echo strlen($content) > 100 ? substr($content, 0, 100) . '...' : $content;
                                    ?>
                                </div>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($msg['gui_luc'])); ?></td>
                            <td>
                                <?php if ($msg['da_doc']): ?>
                                    <span class="badge bg-success">Đã đọc</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Chưa đọc</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-info" onclick="viewMessage(<?php echo $msg['tn_id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning" onclick="reportMessage(<?php echo $msg['tn_id']; ?>)">
                                        <i class="fas fa-flag"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteMessage(<?php echo $msg['tn_id']; ?>)">
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

<!-- View Message Modal -->
<div class="modal fade" id="viewMessageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết Tin nhắn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="messageDetails">
                <!-- Message details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" onclick="markAsRead()">Đánh dấu đã đọc</button>
            </div>
        </div>
    </div>
</div>

<!-- Report Message Modal -->
<div class="modal fade" id="reportMessageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Báo cáo Tin nhắn Vi phạm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reportForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="report_message">
                    <input type="hidden" name="tn_id" id="report_tn_id">
                    <div class="mb-3">
                        <label for="report_reason" class="form-label">Lý do báo cáo *</label>
                        <select class="form-select" id="report_reason" name="report_reason" required>
                            <option value="">Chọn lý do...</option>
                            <option value="spam">Spam</option>
                            <option value="offensive">Lăng mạ/xúc phạm</option>
                            <option value="inappropriate">Nội dung không phù hợp</option>
                            <option value="harassment">Quấy rối</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="report_details" class="form-label">Chi tiết bổ sung</label>
                        <textarea class="form-control" id="report_details" name="report_details" rows="3" placeholder="Mô tả chi tiết về vi phạm..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">Báo cáo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Message Modal -->
<div class="modal fade" id="deleteMessageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xóa Tin nhắn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete_message">
                    <input type="hidden" name="tn_id" id="delete_tn_id">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Bạn có chắc chắn muốn xóa tin nhắn này? Hành động này không thể hoàn tác.
                    </div>
                    <div class="mb-3">
                        <label for="delete_reason" class="form-label">Lý do xóa (tùy chọn)</label>
                        <textarea class="form-control" id="delete_reason" name="reason" rows="2" placeholder="Lý do xóa tin nhắn..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa tin nhắn</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Initialize DataTable
$(document).ready(function() {
    $('#messagesTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Vietnamese.json"
        },
        "order": [[ 0, "desc" ]],
        "pageLength": 25,
        "responsive": true
    });
});

// View message details
function viewMessage(tnId) {
    // For now, just show a placeholder. In a real implementation, you'd load the full message content via AJAX
    $('#messageDetails').html(`
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Tính năng xem chi tiết tin nhắn đang được phát triển.
            <br>Tin nhắn ID: ${tnId}
        </div>
    `);
    $('#viewMessageModal').data('tnId', tnId);
    $('#viewMessageModal').modal('show');
}

// Report message
function reportMessage(tnId) {
    $('#report_tn_id').val(tnId);
    $('#reportMessageModal').modal('show');
}

// Delete message
function deleteMessage(tnId) {
    $('#delete_tn_id').val(tnId);
    $('#deleteMessageModal').modal('show');
}

// Mark as read
function markAsRead() {
    const tnId = $('#viewMessageModal').data('tnId');
    if (tnId) {
        const form = $('<form method="POST" style="display: none;">');
        form.append('<input name="action" value="mark_as_read">');
        form.append('<input name="tn_id" value="' + tnId + '">');
        $('body').append(form);
        form.submit();
    }
}

// Update filter options based on filter type
function updateFilterOptions() {
    const filterType = $('#filter_type').val();
    const filterIdSelect = $('#filter_id');

    filterIdSelect.empty();
    filterIdSelect.append('<option value="">Chọn...</option>');

    if (filterType === 'application') {
        <?php foreach ($apps_with_msgs as $app): ?>
        filterIdSelect.append('<option value="<?php echo $app['ung_tuyen_id']; ?>"><?php echo htmlspecialchars($app['app_title']); ?> (<?php echo $app['msg_count']; ?> tin nhắn)</option>');
        <?php endforeach; ?>
    } else if (filterType === 'sender') {
        <?php foreach ($senders as $sender): ?>
        filterIdSelect.append('<option value="<?php echo $sender['tai_khoan_id']; ?>"><?php echo htmlspecialchars($sender['ten_dn']); ?> (<?php echo $sender['msg_count']; ?> tin nhắn)</option>');
        <?php endforeach; ?>
    }
}

// Refresh messages
function refreshMessages() {
    location.reload();
}
</script>