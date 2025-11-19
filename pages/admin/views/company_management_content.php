        <!-- COMPANY MANAGEMENT CONTENT -->
        <div class="content-box">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fa-solid fa-building"></i> Quản lý Công ty</h3>
                <div class="d-flex gap-2">
                    <a href="?show_deleted=1" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-trash"></i> Xem đã xóa (<?php echo $stats['total_deleted']; ?>)
                    </a>
                    <a href="?" class="btn btn-outline-primary">
                        <i class="fa-solid fa-list"></i> Xem hoạt động
                    </a>
                </div>
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
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h5><?php echo $stats['total_active']; ?></h5>
                            <small>Công ty hoạt động</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h5><?php echo $stats['total_pending']; ?></h5>
                            <small>Chờ duyệt</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h5><?php echo $stats['total_deleted']; ?></h5>
                            <small>Đã xóa</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bảng danh sách công ty -->
            <div class="table-responsive">
                <table id="companiesTable" class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Logo</th>
                            <th>Tên công ty</th>
                            <th>Người đại diện</th>
                            <th>Email</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($companies as $company): ?>
                            <tr>
                                <td>
                                    <?php if ($company['logo_url']): ?>
                                        <img src="../../<?php echo htmlspecialchars($company['logo_url']); ?>"
                                             alt="Logo" class="company-logo" style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="no-logo" style="width: 50px; height: 50px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 5px;">
                                            <i class="fa-solid fa-building text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($company['ten_cong_ty']); ?></strong>
                                    <?php if ($company['website']): ?>
                                        <br><small class="text-muted">
                                            <a href="<?php echo htmlspecialchars($company['website']); ?>" target="_blank">
                                                <i class="fa-solid fa-globe"></i> <?php echo htmlspecialchars($company['website']); ?>
                                            </a>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($company['ten_nha_tuyen_dung'] ?? 'Chưa cập nhật'); ?></td>
                                <td><?php echo htmlspecialchars($company['email_nha_tuyen_dung'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if (!empty($company['xoa_luc'])): ?>
                                        <span class="badge bg-danger">Đã xóa</span>
                                    <?php elseif (isset($company['trang_thai']) && $company['trang_thai'] == 0): ?>
                                        <span class="badge bg-warning">Chờ duyệt</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Hoạt động</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($company['tao_luc'])); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-info me-1" onclick="viewCompany(<?php echo $company['cong_ty_id']; ?>)">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning me-1" onclick="editCompany(<?php echo $company['cong_ty_id']; ?>)">
                                            <i class="fa-solid fa-edit"></i>
                                        </button>
                                        <?php if (empty($company['xoa_luc'])): ?>
                                            <?php if (isset($company['trang_thai']) && ($company['trang_thai'] == 0 || $company['trang_thai'] === 'pending')): ?>
                                                <button class="btn btn-sm btn-success me-1" onclick="verifyCompany(<?php echo $company['cong_ty_id']; ?>, 'active')">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger me-1" onclick="verifyCompany(<?php echo $company['cong_ty_id']; ?>, 'rejected')">
                                                    <i class="fa-solid fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-danger" onclick="deleteCompany(<?php echo $company['cong_ty_id']; ?>, '<?php echo htmlspecialchars($company['ten_cong_ty']); ?>')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-success" onclick="restoreCompany(<?php echo $company['cong_ty_id']; ?>, '<?php echo htmlspecialchars($company['ten_cong_ty']); ?>')">
                                                <i class="fa-solid fa-undo"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Xem chi tiết công ty -->
        <div class="modal fade" id="viewCompanyModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Chi tiết công ty</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="companyDetails">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Chỉnh sửa công ty -->
        <div class="modal fade" id="editCompanyModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Chỉnh sửa công ty</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="modal-body" id="editCompanyForm">
                            <!-- Form will be loaded here -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Xác thực công ty -->
        <div class="modal fade" id="verifyCompanyModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Xác thực công ty</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="verify_company">
                            <input type="hidden" name="cong_ty_id" id="verify_cong_ty_id">
                            <input type="hidden" name="trang_thai" id="verify_trang_thai">

                            <p>Bạn có chắc muốn <strong id="verify_action_text"></strong> công ty <strong id="verify_company_name"></strong>?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary" id="verify_button">Xác nhận</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Xóa công ty -->
        <div class="modal fade" id="deleteCompanyModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Xác nhận ẩn công ty</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="delete_company">
                            <input type="hidden" name="cong_ty_id" id="delete_cong_ty_id">

                            <p>Bạn có chắc muốn ẩn công ty <strong id="delete_company_name"></strong>?</p>
                            <div class="alert alert-warning">
                                <i class="fa-solid fa-exclamation-triangle"></i>
                                Công ty sẽ bị ẩn khỏi hệ thống nhưng có thể khôi phục lại sau.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-danger">Ẩn công ty</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Khôi phục công ty -->
        <div class="modal fade" id="restoreCompanyModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Khôi phục công ty</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="restore_company">
                            <input type="hidden" name="cong_ty_id" id="restore_cong_ty_id">

                            <p>Bạn có chắc muốn khôi phục công ty <strong id="restore_company_name"></strong>?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-success">Khôi phục</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#companiesTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/vi.json"
                },
                "pageLength": 25,
                "order": [[ 5, "desc" ]]
            });
        });

        // View company details
        function viewCompany(companyId) {
            // Load company details via AJAX (simplified - you might want to implement this)
            fetch(`get_company_details.php?id=${companyId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('companyDetails').innerHTML = data;
                    new bootstrap.Modal(document.getElementById('viewCompanyModal')).show();
                })
                .catch(error => {
                    alert('Lỗi khi tải thông tin công ty');
                });
        }

        // Edit company
        function editCompany(companyId) {
            // Load edit form via AJAX (simplified - you might want to implement this)
            fetch(`get_company_edit_form.php?id=${companyId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('editCompanyForm').innerHTML = data;
                    new bootstrap.Modal(document.getElementById('editCompanyModal')).show();
                })
                .catch(error => {
                    alert('Lỗi khi tải form chỉnh sửa');
                });
        }

        // Verify company
        function verifyCompany(companyId, status) {
            const companyName = event.target.closest('tr').querySelector('td:nth-child(2) strong').textContent;

            document.getElementById('verify_cong_ty_id').value = companyId;
            document.getElementById('verify_trang_thai').value = status;
            document.getElementById('verify_company_name').textContent = companyName;

            if (status === 'active') {
                document.getElementById('verify_action_text').textContent = 'duyệt';
                document.getElementById('verify_button').className = 'btn btn-success';
                document.getElementById('verify_button').textContent = 'Duyệt';
            } else {
                document.getElementById('verify_action_text').textContent = 'từ chối';
                document.getElementById('verify_button').className = 'btn btn-danger';
                document.getElementById('verify_button').textContent = 'Từ chối';
            }

            new bootstrap.Modal(document.getElementById('verifyCompanyModal')).show();
        }

        // Delete company
        function deleteCompany(companyId, companyName) {
            document.getElementById('delete_cong_ty_id').value = companyId;
            document.getElementById('delete_company_name').textContent = companyName;

            new bootstrap.Modal(document.getElementById('deleteCompanyModal')).show();
        }

        // Restore company
        function restoreCompany(companyId, companyName) {
            document.getElementById('restore_cong_ty_id').value = companyId;
            document.getElementById('restore_company_name').textContent = companyName;

            new bootstrap.Modal(document.getElementById('restoreCompanyModal')).show();
        }
    </script>