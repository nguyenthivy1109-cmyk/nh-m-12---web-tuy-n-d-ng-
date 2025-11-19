<div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-paperclip me-2"></i>Quản lý Tập tin đính kèm</h2>
            <p class="text-muted">Xem và quản lý các file người dùng upload vào hệ thống</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Tổng số file</h5>
                    <h2 class="mb-0"><?php echo number_format($stats['total_files']); ?></h2>
                    <small>Files trong hệ thống</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Dung lượng</h5>
                    <h2 class="mb-0"><?php echo number_format($stats['total_storage'], 1); ?> MB</h2>
                    <small>Tổng dung lượng ước tính</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">CV/Hồ sơ</h5>
                    <h2 class="mb-0"><?php echo number_format($stats['by_type']['cv'] ?? 0); ?></h2>
                    <small>Files CV</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Người dùng</h5>
                    <h2 class="mb-0"><?php echo number_format($stats['total_users']); ?></h2>
                    <small>Đã upload file</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Loại file</label>
                    <select name="doi_tuong" class="form-select">
                        <option value="">Tất cả loại</option>
                        <?php foreach ($attachment_types as $type => $label): ?>
                            <option value="<?php echo $type; ?>" <?php echo ($filter_type === $type) ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Người upload</label>
                    <select name="tai_khoan_id" class="form-select">
                        <option value="">Tất cả người dùng</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['tai_khoan_id']; ?>" 
                                    <?php echo ($filter_user == $user['tai_khoan_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['ten_dn'] . ' - ' . $user['ho_ten']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tìm kiếm</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Tên file hoặc người dùng..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Lọc
                    </button>
                    <a href="?" class="btn btn-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Attachments Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Danh sách file đính kèm</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="attachmentsTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên file</th>
                            <th>Loại</th>
                            <th>Người upload</th>
                            <th>Mime Type</th>
                            <th>Thời gian</th>
                            <th>Đường dẫn</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attachments as $attachment): ?>
                            <tr>
                                <td><?php echo $attachment['dk_id']; ?></td>
                                <td>
                                    <i class="fas fa-file me-1"></i>
                                    <?php echo htmlspecialchars($attachment['ten_tep'] ?? 'Không có tên'); ?>
                                </td>
                                <td>
                                    <?php 
                                    $type_info = $file_type_labels[$attachment['doi_tuong']] ?? ['label' => 'Khác', 'class' => 'secondary'];
                                    ?>
                                    <span class="badge bg-<?php echo $type_info['class']; ?>">
                                        <?php echo $type_info['label']; ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($attachment['ten_dn']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($attachment['ho_ten']); ?></small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($attachment['mime_type'] ?? 'N/A'); ?>
                                    </small>
                                </td>
                                <td>
                                    <small><?php echo date('d/m/Y H:i', strtotime($attachment['tao_luc'])); ?></small>
                                </td>
                                <td>
                                    <small class="text-muted" style="word-break: break-all;">
                                        <?php echo htmlspecialchars(substr($attachment['tep_url'], 0, 50)); ?>
                                        <?php if (strlen($attachment['tep_url']) > 50) echo '...'; ?>
                                    </small>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info view-file" 
                                            data-url="<?php echo htmlspecialchars($attachment['tep_url']); ?>"
                                            data-name="<?php echo htmlspecialchars($attachment['ten_tep']); ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="../../<?php echo htmlspecialchars($attachment['tep_url']); ?>" 
                                       class="btn btn-sm btn-success" 
                                       download 
                                       target="_blank">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger delete-file" 
                                            data-id="<?php echo $attachment['dk_id']; ?>"
                                            data-name="<?php echo htmlspecialchars($attachment['ten_tep']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View File Modal -->
<div class="modal fade" id="viewFileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xem file</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="filePreview" class="text-center">
                    <p class="text-muted">Đang tải...</p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="downloadFileLink" class="btn btn-success" download>
                    <i class="fas fa-download"></i> Tải xuống
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa file <strong id="deleteFileName"></strong>?</p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Hành động này không thể hoàn tác!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#attachmentsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
        },
        order: [[0, 'desc']],
        pageLength: 25
    });

    // View file
    $('.view-file').click(function() {
        const fileUrl = $(this).data('url');
        const fileName = $(this).data('name');
        const fullUrl = '../../' + fileUrl;
        
        $('#viewFileModal .modal-title').text('Xem file: ' + fileName);
        $('#downloadFileLink').attr('href', fullUrl);
        
        const fileExt = fileUrl.split('.').pop().toLowerCase();
        let previewHtml = '';
        
        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt)) {
            previewHtml = `<img src="${fullUrl}" class="img-fluid" alt="${fileName}">`;
        } else if (fileExt === 'pdf') {
            previewHtml = `<embed src="${fullUrl}" type="application/pdf" width="100%" height="600px">`;
        } else {
            previewHtml = `
                <div class="alert alert-info">
                    <i class="fas fa-file fa-3x mb-3"></i>
                    <p>Không thể xem trước file này. Vui lòng tải xuống để xem.</p>
                    <p><strong>File:</strong> ${fileName}</p>
                    <p><strong>Đường dẫn:</strong> ${fileUrl}</p>
                </div>
            `;
        }
        
        $('#filePreview').html(previewHtml);
        $('#viewFileModal').modal('show');
    });

    // Delete file
    let deleteFileId = null;
    
    $('.delete-file').click(function() {
        deleteFileId = $(this).data('id');
        const fileName = $(this).data('name');
        $('#deleteFileName').text(fileName);
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').click(function() {
        if (deleteFileId) {
            $.ajax({
                url: window.location.pathname,
                method: 'POST',
                data: {
                    action: 'delete',
                    dk_id: deleteFileId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Xóa file thành công!');
                        location.reload();
                    } else {
                        alert('Lỗi: ' + response.message);
                    }
                },
                error: function() {
                    alert('Có lỗi xảy ra khi xóa file!');
                }
            });
        }
        $('#deleteModal').modal('hide');
    });
});
</script>
