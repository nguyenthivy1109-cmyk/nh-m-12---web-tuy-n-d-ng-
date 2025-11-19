<!-- Filters -->
<div class="content-card">
    <form method="GET" action="" id="filterForm">
        <div class="filter-section">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-building"></i> Công ty</label>
                    <select name="company" class="form-select" onchange="this.form.submit()">
                        <option value="">Tất cả công ty</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?php echo $company['cong_ty_id']; ?>" <?php echo $company_filter == $company['cong_ty_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($company['ten_cong_ty']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-user-tie"></i> Nhà tuyển dụng</label>
                    <select name="recruiter" class="form-select" onchange="this.form.submit()">
                        <option value="">Tất cả NTD</option>
                        <?php foreach ($recruiters as $recruiter): ?>
                            <option value="<?php echo $recruiter['nha_td_id']; ?>" <?php echo $recruiter_filter == $recruiter['nha_td_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($recruiter['ho_ten']); ?>
                                <?php if ($recruiter['ten_cong_ty']): ?>
                                    - <?php echo htmlspecialchars($recruiter['ten_cong_ty']); ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><i class="fas fa-filter"></i> Trạng thái</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Tất cả</option>
                        <option value="0" <?php echo $status_filter === 0 ? 'selected' : ''; ?>>Chờ duyệt</option>
                        <option value="1" <?php echo $status_filter === 1 ? 'selected' : ''; ?>>Đã duyệt</option>
                        <option value="2" <?php echo $status_filter === 2 ? 'selected' : ''; ?>>Hết hạn</option>
                        <option value="3" <?php echo $status_filter === 3 ? 'selected' : ''; ?>>Bị khóa</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="show_deleted" value="1" id="showDeleted" 
                               <?php echo $show_deleted ? 'checked' : ''; ?> onchange="this.form.submit()">
                        <label class="form-check-label" for="showDeleted">
                            Hiện đã xóa
                        </label>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-secondary w-100" onclick="clearFilters()">
                        <i class="fas fa-redo"></i> Làm mới
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Jobs Table -->
<div class="content-card">
    <h5 class="mb-3"><i class="fas fa-list"></i> Danh sách tin tuyển dụng</h5>
    <div class="table-responsive">
        <table id="jobsTable" class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Tiêu đề</th>
                    <th>Công ty</th>
                    <th>NTD</th>
                    <th>Địa điểm</th>
                    <th>Lương</th>
                    <th>Trạng thái</th>
                    <th>Đăng/Hết hạn</th>
                    <th>Ứng tuyển</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job): ?>
                    <?php 
                    $statusClass = [
                        0 => 'warning',
                        1 => 'success',
                        2 => 'secondary',
                        3 => 'danger'
                    ][$job['trang_thai_tin']] ?? 'secondary';
                    
                    $statusText = [
                        0 => 'Chờ duyệt',
                        1 => 'Đã duyệt',
                        2 => 'Hết hạn',
                        3 => 'Bị khóa'
                    ][$job['trang_thai_tin']] ?? 'Không xác định';

                    $isExpired = !empty($job['het_han_luc']) && strtotime($job['het_han_luc']) < time();
                    if ($isExpired && $job['trang_thai_tin'] == 1) {
                        $statusClass = 'secondary';
                        $statusText = 'Hết hạn';
                    }
                    ?>
                    <tr class="<?php echo $job['xoa_luc'] ? 'table-secondary' : ''; ?>">
                        <td><?php echo $job['tin_id']; ?></td>
                        <td>
                            <div class="job-title"><?php echo htmlspecialchars($job['tieu_de']); ?></div>
                            <?php if ($job['xoa_luc']): ?>
                                <small class="text-danger"><i class="fas fa-trash"></i> Đã xóa</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="company-name">
                                <?php if ($job['logo_url']): ?>
                                    <img src="<?php echo htmlspecialchars($job['logo_url']); ?>" 
                                         alt="" style="width: 20px; height: 20px; object-fit: cover; border-radius: 3px;">
                                <?php endif; ?>
                                <?php echo htmlspecialchars($job['ten_cong_ty']); ?>
                            </div>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($job['recruiter_name']); ?>
                            <br><small class="text-muted"><?php echo htmlspecialchars($job['recruiter_email']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($job['noi_lam_viec'] ?: 'Chưa cập nhật'); ?></td>
                        <td>
                            <?php if ($job['luong_min'] || $job['luong_max']): ?>
                                <span class="salary-range">
                                    <?php 
                                    if ($job['luong_min'] && $job['luong_max']) {
                                        echo number_format($job['luong_min']) . ' - ' . number_format($job['luong_max']);
                                    } elseif ($job['luong_min']) {
                                        echo 'Từ ' . number_format($job['luong_min']);
                                    } else {
                                        echo 'Lên đến ' . number_format($job['luong_max']);
                                    }
                                    echo ' ' . ($job['tien_te'] ?: 'VNĐ');
                                    ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">Thỏa thuận</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $statusClass; ?>">
                                <?php echo $statusText; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($job['dang_luc']): ?>
                                <small>
                                    <i class="fas fa-calendar-check text-success"></i> 
                                    <?php echo date('d/m/Y', strtotime($job['dang_luc'])); ?>
                                </small>
                            <?php endif; ?>
                            <br>
                            <?php if ($job['het_han_luc']): ?>
                                <small class="<?php echo $isExpired ? 'text-danger' : 'text-muted'; ?>">
                                    <i class="fas fa-calendar-times"></i> 
                                    <?php echo date('d/m/Y', strtotime($job['het_han_luc'])); ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                <?php echo number_format($job['total_applications']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary action-btn" onclick="viewJob(<?php echo $job['tin_id']; ?>)" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning action-btn" onclick="editJob(<?php echo $job['tin_id']; ?>)" title="Chỉnh sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <?php if (!$job['xoa_luc']): ?>
                                <?php if ($job['trang_thai_tin'] == 0): ?>
                                    <button class="btn btn-sm btn-success action-btn" onclick="approveJob(<?php echo $job['tin_id']; ?>)" title="Duyệt">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger action-btn" onclick="rejectJob(<?php echo $job['tin_id']; ?>)" title="Từ chối">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php elseif ($job['trang_thai_tin'] == 3): ?>
                                    <button class="btn btn-sm btn-success action-btn" onclick="unlockJob(<?php echo $job['tin_id']; ?>)" title="Mở khóa">
                                        <i class="fas fa-unlock"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary action-btn" onclick="lockJob(<?php echo $job['tin_id']; ?>)" title="Khóa">
                                        <i class="fas fa-lock"></i>
                                    </button>
                                <?php endif; ?>
                                
                                <button class="btn btn-sm btn-danger action-btn" onclick="deleteJob(<?php echo $job['tin_id']; ?>)" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- View Job Modal -->
<div class="modal fade" id="viewJobModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-briefcase"></i> Chi tiết tin tuyển dụng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewJobContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Job Modal -->
<div class="modal fade" id="editJobModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Chỉnh sửa tin tuyển dụng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editJobContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Job Modal -->
<div class="modal fade" id="rejectJobModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-times-circle"></i> Từ chối tin tuyển dụng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="reject_job">
                    <input type="hidden" name="tin_id" id="rejectJobId">
                    
                    <div class="mb-3">
                        <label class="form-label">Lý do từ chối (tùy chọn):</label>
                        <textarea name="ly_do" class="form-control" rows="3" 
                                  placeholder="Nhập lý do từ chối để gửi thông báo đến nhà tuyển dụng..."></textarea>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Tin tuyển dụng sẽ bị từ chối và không được hiển thị công khai.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Xác nhận từ chối
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#jobsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
        },
        order: [[0, 'desc']],
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: -1 }
        ]
    });
});

function clearFilters() {
    window.location.href = 'index.php';
}

function viewJob(jobId) {
    $('#viewJobModal').modal('show');
    $('#viewJobContent').html('<div class="text-center"><div class="spinner-border"></div></div>');
    
    $.get('get_job_details.php', { id: jobId }, function(data) {
        $('#viewJobContent').html(data);
    }).fail(function() {
        $('#viewJobContent').html('<div class="alert alert-danger">Không thể tải thông tin tin tuyển dụng</div>');
    });
}

function editJob(jobId) {
    $('#editJobModal').modal('show');
    $('#editJobContent').html('<div class="text-center"><div class="spinner-border"></div></div>');
    
    $.get('get_job_edit_form.php', { id: jobId }, function(data) {
        $('#editJobContent').html(data);
    }).fail(function() {
        $('#editJobContent').html('<div class="alert alert-danger">Không thể tải form chỉnh sửa</div>');
    });
}

function approveJob(jobId) {
    if (confirm('Xác nhận duyệt tin tuyển dụng này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="approve_job">
            <input type="hidden" name="tin_id" value="${jobId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function rejectJob(jobId) {
    $('#rejectJobId').val(jobId);
    $('#rejectJobModal').modal('show');
}

function lockJob(jobId) {
    if (confirm('Xác nhận khóa tin tuyển dụng này?\n\nTin sẽ không hiển thị công khai nữa.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="lock_job">
            <input type="hidden" name="tin_id" value="${jobId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function unlockJob(jobId) {
    if (confirm('Xác nhận mở khóa tin tuyển dụng này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="unlock_job">
            <input type="hidden" name="tin_id" value="${jobId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteJob(jobId) {
    if (confirm('Xác nhận xóa tin tuyển dụng này?\n\nTin sẽ bị xóa mềm và có thể khôi phục sau.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_job">
            <input type="hidden" name="tin_id" value="${jobId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
