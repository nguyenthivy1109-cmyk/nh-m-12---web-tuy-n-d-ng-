<!-- RECRUITER MANAGEMENT CONTENT -->
<div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fa-solid fa-user-tie"></i> Quản lý Nhà tuyển dụng</h3>
    </div>

    <!-- Thông báo -->
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Thống kê nhanh -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h5><?php echo $stats['total_active']; ?></h5>
                    <small>Hoạt động</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h5><?php echo $stats['total_locked']; ?></h5>
                    <small>Đã khóa</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h5><?php echo $stats['linked_companies']; ?></h5>
                    <small>Có công ty</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h5><?php echo $stats['unlinked']; ?></h5>
                    <small>Chưa liên kết</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng danh sách nhà tuyển dụng -->
    <div class="table-responsive">
        <table id="recruitersTable" class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Họ tên</th>
                    <th>Tài khoản</th>
                    <th>Công ty</th>
                    <th>Chức danh</th>
                    <th>Email CV</th>
                    <th>Trạng thái</th>
                    <th>Tin đăng</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recruiters as $recruiter): ?>
                    <tr>
                        <td><?php echo $recruiter['nha_td_id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($recruiter['ho_ten'] ?: 'Chưa cập nhật'); ?></strong>
                            <?php if ($recruiter['dien_thoai']): ?>
                                <br><small class="text-muted"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($recruiter['dien_thoai']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div><strong><?php echo htmlspecialchars($recruiter['ten_dn']); ?></strong></div>
                            <small class="text-muted"><?php echo htmlspecialchars($recruiter['email']); ?></small>
                        </td>
                        <td>
                            <?php if ($recruiter['cong_ty_id']): ?>
                                <?php echo htmlspecialchars($recruiter['ten_cong_ty']); ?>
                            <?php else: ?>
                                <span class="badge bg-warning">Chưa liên kết</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($recruiter['chuc_danh'] ?: 'N/A'); ?></td>
                        <td><?php echo $recruiter['email_cong_viec'] ? htmlspecialchars($recruiter['email_cong_viec']) : 'N/A'; ?></td>
                        <td>
                            <?php if ($recruiter['kich_hoat'] == 1): ?>
                                <span class="badge bg-success">Hoạt động</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Bị khóa</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-primary"><?php echo $recruiter['total_jobs']; ?></span></td>
                        <td><?php echo date('d/m/Y', strtotime($recruiter['tao_luc'])); ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-info me-1" onclick="viewRecruiter(<?php echo $recruiter['nha_td_id']; ?>)">
                                    <i class="fa-solid fa-eye"></i> Xem
                                </button>
                                <button class="btn btn-sm btn-warning me-1" onclick="editRecruiter(<?php echo $recruiter['nha_td_id']; ?>)">
                                    <i class="fa-solid fa-edit"></i> Sửa
                                </button>
                                <?php if ($recruiter['kich_hoat'] == 1): ?>
                                    <button class="btn btn-sm btn-secondary me-1" onclick="toggleAccount(<?php echo $recruiter['tai_khoan_id']; ?>, 1, '<?php echo htmlspecialchars($recruiter['ho_ten'] ?: $recruiter['ten_dn']); ?>')">
                                        <i class="fa-solid fa-lock"></i> Khóa
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-success me-1" onclick="toggleAccount(<?php echo $recruiter['tai_khoan_id']; ?>, 0, '<?php echo htmlspecialchars($recruiter['ho_ten'] ?: $recruiter['ten_dn']); ?>')">
                                        <i class="fa-solid fa-unlock"></i> Mở
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-danger" onclick="deleteRecruiter(<?php echo $recruiter['nha_td_id']; ?>, '<?php echo htmlspecialchars($recruiter['ho_ten'] ?: $recruiter['ten_dn']); ?>')">
                                    <i class="fa-solid fa-trash"></i> Xóa
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<!-- Modal Xem chi tiết nhà tuyển dụng -->
<div class="modal fade" id="viewRecruiterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết nhà tuyển dụng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="recruiterDetails">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Chỉnh sửa nhà tuyển dụng -->
<div class="modal fade" id="editRecruiterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chỉnh sửa thông tin nhà tuyển dụng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body" id="editRecruiterForm">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
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

<script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#recruitersTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/vi.json"
            },
            "pageLength": 25,
            "order": [[ 0, "desc" ]]
        });
    });

    // View recruiter details
    function viewRecruiter(recruiterId) {
        const recruiter = <?php echo json_encode($recruiters); ?>.find(r => r.nha_td_id == recruiterId);
        if (recruiter) {
            let details = `
                <div class="row">
                    <div class="col-md-12">
                        <h4>${recruiter.ho_ten || 'Chưa cập nhật'}</h4>
                        <table class="table table-sm">
                            <tr><td><strong>Email:</strong></td><td>${recruiter.email}</td></tr>
                            <tr><td><strong>Tên đăng nhập:</strong></td><td>${recruiter.ten_dn}</td></tr>
                            <tr><td><strong>Số điện thoại:</strong></td><td>${recruiter.dien_thoai || 'Chưa cập nhật'}</td></tr>
                            <tr><td><strong>Chức danh:</strong></td><td>${recruiter.chuc_danh || 'Chưa cập nhật'}</td></tr>
                            <tr><td><strong>Email công việc:</strong></td><td>${recruiter.email_cong_viec || 'Chưa cập nhật'}</td></tr>
                            <tr><td><strong>Công ty:</strong></td><td>${recruiter.ten_cong_ty || 'Chưa liên kết'}</td></tr>
                            <tr><td><strong>Số tin đăng:</strong></td><td>${recruiter.total_jobs}</td></tr>
                            <tr><td><strong>Trạng thái:</strong></td><td>${recruiter.kich_hoat == 1 ? '<span class="badge bg-success">Hoạt động</span>' : '<span class="badge bg-danger">Bị khóa</span>'}</td></tr>
                            <tr><td><strong>Ngày tạo:</strong></td><td>${new Date(recruiter.tao_luc).toLocaleDateString('vi-VN')}</td></tr>
                        </table>
                    </div>
                </div>
            `;
            document.getElementById('recruiterDetails').innerHTML = details;
            new bootstrap.Modal(document.getElementById('viewRecruiterModal')).show();
        }
    }

    // Edit recruiter
    function editRecruiter(recruiterId) {
        const recruiter = <?php echo json_encode($recruiters); ?>.find(r => r.nha_td_id == recruiterId);
        if (recruiter) {
            let form = `
                <input type="hidden" name="action" value="update_recruiter">
                <input type="hidden" name="nha_td_id" value="${recruiter.nha_td_id}">

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Họ tên *</label>
                            <input type="text" class="form-control" name="ho_ten" value="${recruiter.ho_ten || ''}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Chức danh</label>
                            <input type="text" class="form-control" name="chuc_danh" value="${recruiter.chuc_danh || ''}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Email công việc</label>
                            <input type="email" class="form-control" name="email_cong_viec" value="${recruiter.email_cong_viec || ''}">
                            <small class="text-muted">Email nhận CV ứng tuyển</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Công ty</label>
                            <select class="form-control" name="cong_ty_id">
                                <option value="">Chưa liên kết</option>
                                <?php foreach ($companies as $company): ?>
                                    <option value="<?php echo $company['cong_ty_id']; ?>" \${recruiter.cong_ty_id == <?php echo $company['cong_ty_id']; ?> ? 'selected' : ''}>
                                        <?php echo htmlspecialchars($company['ten_cong_ty']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('editRecruiterForm').innerHTML = form;
            new bootstrap.Modal(document.getElementById('editRecruiterModal')).show();
        }
    }

    // Toggle account status
    function toggleAccount(taiKhoanId, currentStatus, name) {
        const action = currentStatus == 1 ? 'khóa' : 'mở khóa';
        if (confirm(`Bạn có chắc muốn ${action} tài khoản của "${name}"?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="toggle_account">
                <input type="hidden" name="tai_khoan_id" value="${taiKhoanId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Delete recruiter
    function deleteRecruiter(nha_td_id, name) {
        if (confirm(`Bạn có chắc muốn xóa nhà tuyển dụng "${name}"?\n\nLưu ý: Dữ liệu có thể được khôi phục.`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete_recruiter">
                <input type="hidden" name="nha_td_id" value="${nha_td_id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
