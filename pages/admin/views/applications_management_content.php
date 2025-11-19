<!-- Applications Management Content -->
<div class="content-card">
    <!-- Filters -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-file-signature"></i> Danh sách Hồ sơ ứng tuyển</h4>
        <div class="d-flex gap-2">
            <select id="filterType" class="form-select form-select-sm" style="width: auto;">
                <option value="all" <?php echo $filter_type === 'all' ? 'selected' : ''; ?>>Tất cả ứng tuyển</option>
                <option value="job" <?php echo $filter_type === 'job' ? 'selected' : ''; ?>>Lọc theo tin tuyển dụng</option>
                <option value="candidate" <?php echo $filter_type === 'candidate' ? 'selected' : ''; ?>>Lọc theo ứng viên</option>
            </select>
            <select id="filterId" class="form-select form-select-sm" style="width: 250px; <?php echo $filter_type === 'all' ? 'display: none;' : ''; ?>">
                <?php if ($filter_type === 'job'): ?>
                    <?php foreach ($jobs_with_apps as $job): ?>
                        <option value="<?php echo $job['tin_id']; ?>" <?php echo $filter_id == $job['tin_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($job['tieu_de']); ?> (<?php echo $job['app_count']; ?> ứng tuyển)
                        </option>
                    <?php endforeach; ?>
                <?php elseif ($filter_type === 'candidate'): ?>
                    <?php foreach ($candidates_with_apps as $candidate): ?>
                        <option value="<?php echo $candidate['ung_vien_id']; ?>" <?php echo $filter_id == $candidate['ung_vien_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($candidate['ho_ten']); ?> (<?php echo $candidate['app_count']; ?> ứng tuyển)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="table-responsive">
        <table id="applicationsTable" class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Tin tuyển dụng</th>
                    <th>Ứng viên</th>
                    <th>Công ty</th>
                    <th>Trạng thái</th>
                    <th>Ngày nộp</th>
                    <th>CV</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                <tr>
                    <td><?php echo htmlspecialchars($app['ung_tuyen_id']); ?></td>
                    <td>
                        <div>
                            <strong><?php echo htmlspecialchars($app['job_title']); ?></strong>
                            <br>
                            <small class="text-muted">
                                <?php if ($app['luong_min'] && $app['luong_max']): ?>
                                    Lương: <?php echo number_format($app['luong_min']); ?> - <?php echo number_format($app['luong_max']); ?> <?php echo htmlspecialchars($app['tien_te']); ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    </td>
                    <td>
                        <div>
                            <strong><?php echo htmlspecialchars($app['candidate_name']); ?></strong>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($app['company_name']); ?></td>
                    <td>
                        <span class="badge bg-<?php echo isset($status_labels[$app['trang_thai_ut']]) ? $status_labels[$app['trang_thai_ut']]['class'] : 'secondary'; ?>">
                            <?php echo isset($status_labels[$app['trang_thai_ut']]) ? $status_labels[$app['trang_thai_ut']]['label'] : 'Không xác định'; ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($app['nop_luc'])); ?></td>
                    <td>
                        <?php if ($app['cv_title']): ?>
                            <button type="button" class="btn btn-sm btn-outline-info"
                                    onclick="viewCV('<?php echo htmlspecialchars($app['cv_title']); ?>', '<?php echo htmlspecialchars($app['cv_url']); ?>')">
                                <i class="fas fa-eye"></i> Xem CV
                            </button>
                        <?php else: ?>
                            <span class="text-muted">Chưa có CV</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="viewApplication(<?php echo $app['ung_tuyen_id']; ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning"
                                    onclick="updateStatus(<?php echo $app['ung_tuyen_id']; ?>, <?php echo $app['trang_thai_ut']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info"
                                    onclick="addNote(<?php echo $app['ung_tuyen_id']; ?>)">
                                <i class="fas fa-sticky-note"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- View Application Modal -->
<div class="modal fade" id="viewApplicationModal" tabindex="-1" aria-labelledby="viewApplicationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewApplicationModalLabel"><i class="fas fa-eye"></i> Chi tiết hồ sơ ứng tuyển</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="applicationDetails">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel"><i class="fas fa-edit"></i> Cập nhật trạng thái</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="ung_tuyen_id" id="status_ung_tuyen_id">

                    <div class="mb-3">
                        <label class="form-label">Trạng thái mới</label>
                        <select class="form-select" name="trang_thai_ut" id="status_select" required>
                            <?php foreach ($status_labels as $value => $status): ?>
                                <option value="<?php echo $value; ?>"><?php echo $status['label']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ghi chú (tùy chọn)</label>
                        <textarea class="form-control" name="ghi_chu" rows="3" placeholder="Nhập ghi chú về việc thay đổi trạng thái..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addNoteModalLabel"><i class="fas fa-sticky-note"></i> Thêm ghi chú</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_note">
                    <input type="hidden" name="ung_tuyen_id" id="note_ung_tuyen_id">

                    <div class="mb-3">
                        <label class="form-label">Nội dung ghi chú</label>
                        <textarea class="form-control" name="ghi_chu" rows="4" required
                                  placeholder="Nhập ghi chú về hồ sơ ứng tuyển..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm ghi chú</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View CV Modal -->
<div class="modal fade" id="viewCVModal" tabindex="-1" aria-labelledby="viewCVModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewCVModalLabel"><i class="fas fa-file-alt"></i> Xem CV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="cvContent">
                    <!-- CV content will be loaded here -->
                </div>
                <div class="mt-3">
                    <a id="downloadCVLink" href="#" class="btn btn-primary" target="_blank">
                        <i class="fas fa-download"></i> Tải xuống CV
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize DataTable
$(document).ready(function() {
    $('#applicationsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
        },
        pageLength: 25,
        order: [[5, 'desc']], // Sort by application date descending
        columnDefs: [
            { orderable: false, targets: [6, 7] }
        ]
    });

    // Filter functionality
    $('#filterType').change(function() {
        var filterType = $(this).val();
        var filterIdSelect = $('#filterId');

        if (filterType === 'all') {
            filterIdSelect.hide();
            window.location.href = 'index.php';
        } else {
            filterIdSelect.show();
            // Load options based on filter type
            loadFilterOptions(filterType);
        }
    });

    $('#filterId').change(function() {
        var filterType = $('#filterType').val();
        var filterId = $(this).val();
        window.location.href = 'index.php?filter_type=' + filterType + '&filter_id=' + filterId;
    });
});

function loadFilterOptions(filterType) {
    // This would typically make an AJAX call to load options
    // For now, we'll reload the page with the filter
    window.location.href = 'index.php?filter_type=' + filterType;
}

function viewApplication(ung_tuyen_id) {
    // Load application details via AJAX or show modal with data
    $('#viewApplicationModal').modal('show');
    // For now, just show the modal - you can enhance this with AJAX
}

function updateStatus(ung_tuyen_id, current_status) {
    $('#status_ung_tuyen_id').val(ung_tuyen_id);
    $('#status_select').val(current_status);
    $('#updateStatusModal').modal('show');
}

function addNote(ung_tuyen_id) {
    $('#note_ung_tuyen_id').val(ung_tuyen_id);
    $('#addNoteModal').modal('show');
}

function viewCV(cv_title, cv_url) {
    $('#viewCVModalLabel').text('Xem CV: ' + cv_title);
    $('#downloadCVLink').attr('href', cv_url);

    // For demo purposes, show a placeholder
    $('#cvContent').html(`
        <div class="alert alert-info">
            <i class="fas fa-file-pdf"></i>
            <strong>${cv_title}</strong>
            <br>
            <small>CV sẽ được hiển thị ở đây. Trong phiên bản thực, bạn có thể nhúng PDF viewer hoặc hiển thị nội dung CV.</small>
        </div>
    `);

    $('#viewCVModal').modal('show');
}
</script>