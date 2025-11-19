<!-- CANDIDATE MANAGEMENT CONTENT -->
<div class="content-box">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fa-solid fa-users"></i> Quản lý Ứng viên</h3>
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
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h5><?php echo $stats['total']; ?></h5>
                    <small>Tổng ứng viên</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h5><?php echo $stats['total_active']; ?></h5>
                    <small>Tài khoản hoạt động</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h5><?php echo $stats['total_inactive']; ?></h5>
                    <small>Tài khoản bị khóa</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng danh sách ứng viên -->
    <div class="table-responsive">
        <table id="candidatesTable" class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Tên đăng nhập</th>
                    <th>Ngày sinh</th>
                    <th>Giới tính</th>
                    <th>Nơi ở</th>
                    <th>Trạng thái</th>
                    <th>Đăng nhập cuối</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($candidates as $candidate): ?>
                    <tr>
                        <td><?php echo $candidate['ung_vien_id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($candidate['ho_ten']); ?></strong></td>
                        <td><?php echo htmlspecialchars($candidate['email']); ?></td>
                        <td><?php echo htmlspecialchars($candidate['ten_dn']); ?></td>
                        <td><?php echo $candidate['ngay_sinh'] ? date('d/m/Y', strtotime($candidate['ngay_sinh'])) : 'N/A'; ?></td>
                        <td><?php echo htmlspecialchars($candidate['gioi_tinh'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($candidate['noi_o'] ?: 'N/A'); ?></td>
                        <td>
                            <?php if ($candidate['kich_hoat'] == 1): ?>
                                <span class="badge bg-success">Hoạt động</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Bị khóa</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $candidate['dang_nhap_cuoi_luc'] ? date('d/m/Y H:i', strtotime($candidate['dang_nhap_cuoi_luc'])) : 'Chưa đăng nhập'; ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-info me-1" onclick="viewCandidate(<?php echo $candidate['ung_vien_id']; ?>)">
                                    <i class="fa-solid fa-eye"></i> Xem
                                </button>
                                <button class="btn btn-sm btn-warning me-1" onclick="editCandidate(<?php echo $candidate['ung_vien_id']; ?>)">
                                    <i class="fa-solid fa-edit"></i> Sửa
                                </button>
                                <?php if ($candidate['kich_hoat'] == 1): ?>
                                    <button class="btn btn-sm btn-secondary me-1" onclick="toggleAccount(<?php echo $candidate['tai_khoan_id']; ?>, 1, '<?php echo htmlspecialchars($candidate['ho_ten']); ?>')">
                                        <i class="fa-solid fa-lock"></i> Khóa
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-success me-1" onclick="toggleAccount(<?php echo $candidate['tai_khoan_id']; ?>, 0, '<?php echo htmlspecialchars($candidate['ho_ten']); ?>')">
                                        <i class="fa-solid fa-unlock"></i> Mở khóa
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-danger" onclick="deleteCandidate(<?php echo $candidate['tai_khoan_id']; ?>, '<?php echo htmlspecialchars($candidate['ho_ten']); ?>')">
                                    <i class="fa-solid fa-trash"></i> Xóa
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Xem chi tiết ứng viên -->
<div class="modal fade" id="viewCandidateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết ứng viên</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="candidateDetails">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Chỉnh sửa ứng viên -->
<div class="modal fade" id="editCandidateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chỉnh sửa thông tin ứng viên</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body" id="editCandidateForm">
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
        $('#candidatesTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/vi.json"
            },
            "pageLength": 25,
            "order": [[ 0, "desc" ]]
        });
    });

    // View candidate details
    function viewCandidate(candidateId) {
        const candidate = <?php echo json_encode($candidates); ?>.find(c => c.ung_vien_id == candidateId);
        if (candidate) {
            let details = `
                <div class="row">
                    <div class="col-md-12">
                        <h4>${candidate.ho_ten}</h4>
                        <table class="table table-sm">
                            <tr><td><strong>Email:</strong></td><td>${candidate.email}</td></tr>
                            <tr><td><strong>Tên đăng nhập:</strong></td><td>${candidate.ten_dn}</td></tr>
                            <tr><td><strong>Ngày sinh:</strong></td><td>${candidate.ngay_sinh || 'Chưa cập nhật'}</td></tr>
                            <tr><td><strong>Giới tính:</strong></td><td>${candidate.gioi_tinh || 'Chưa cập nhật'}</td></tr>
                            <tr><td><strong>Nơi ở:</strong></td><td>${candidate.noi_o || 'Chưa cập nhật'}</td></tr>
                            <tr><td><strong>Tiêu đề CV:</strong></td><td>${candidate.tieu_de_cv || 'Chưa cập nhật'}</td></tr>
                            <tr><td><strong>Trạng thái:</strong></td><td>${candidate.kich_hoat == 1 ? '<span class="badge bg-success">Hoạt động</span>' : '<span class="badge bg-danger">Bị khóa</span>'}</td></tr>
                            <tr><td><strong>Ngày tạo:</strong></td><td>${new Date(candidate.tao_luc).toLocaleDateString('vi-VN')}</td></tr>
                        </table>
                        ${candidate.gioi_thieu ? `<div><strong>Giới thiệu:</strong><br><p>${candidate.gioi_thieu}</p></div>` : ''}
                    </div>
                </div>
            `;
            document.getElementById('candidateDetails').innerHTML = details;
            new bootstrap.Modal(document.getElementById('viewCandidateModal')).show();
        }
    }

    // Edit candidate
    function editCandidate(candidateId) {
        const candidate = <?php echo json_encode($candidates); ?>.find(c => c.ung_vien_id == candidateId);
        if (candidate) {
            let form = `
                <input type="hidden" name="action" value="update_candidate">
                <input type="hidden" name="ung_vien_id" value="${candidate.ung_vien_id}">

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Họ tên *</label>
                            <input type="text" class="form-control" name="ho_ten" value="${candidate.ho_ten}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" class="form-control" name="ngay_sinh" value="${candidate.ngay_sinh || ''}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Giới tính</label>
                            <select class="form-control" name="gioi_tinh">
                                <option value="">Chọn giới tính</option>
                                <option value="Nam" ${candidate.gioi_tinh == 'Nam' ? 'selected' : ''}>Nam</option>
                                <option value="Nữ" ${candidate.gioi_tinh == 'Nữ' ? 'selected' : ''}>Nữ</option>
                                <option value="Khác" ${candidate.gioi_tinh == 'Khác' ? 'selected' : ''}>Khác</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nơi ở</label>
                            <input type="text" class="form-control" name="noi_o" value="${candidate.noi_o || ''}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề CV</label>
                            <input type="text" class="form-control" name="tieu_de_cv" value="${candidate.tieu_de_cv || ''}">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label">Giới thiệu bản thân</label>
                            <textarea class="form-control" name="gioi_thieu" rows="4">${candidate.gioi_thieu || ''}</textarea>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('editCandidateForm').innerHTML = form;
            new bootstrap.Modal(document.getElementById('editCandidateModal')).show();
        }
    }

    // Toggle account status
    function toggleAccount(taiKhoanId, currentStatus, hoTen) {
        const action = currentStatus == 1 ? 'khóa' : 'mở khóa';
        if (confirm(`Bạn có chắc muốn ${action} tài khoản của "${hoTen}"?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="toggle_account">
                <input type="hidden" name="tai_khoan_id" value="${taiKhoanId}">
                <input type="hidden" name="current_status" value="${currentStatus}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Delete candidate
    function deleteCandidate(taiKhoanId, hoTen) {
        if (confirm(`Bạn có chắc muốn XÓA VĨNH VIỄN ứng viên "${hoTen}"?\n\nHành động này KHÔNG THỂ KHÔI PHỤC!`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete_candidate">
                <input type="hidden" name="tai_khoan_id" value="${taiKhoanId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
