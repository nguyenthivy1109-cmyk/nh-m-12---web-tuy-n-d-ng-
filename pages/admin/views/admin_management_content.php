        <!-- ADMIN MANAGEMENT CONTENT -->
        <div class="content-box">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fa-solid fa-user-shield"></i> Quản lý Admin</h3>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                    <i class="fa-solid fa-plus"></i> Thêm Admin mới
                </button>
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
                <div class="col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h5><?php echo $stats['total_admins']; ?></h5>
                            <small>Tổng Admin</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h5><?php echo $stats['available_accounts']; ?></h5>
                            <small>Tài khoản người dùng khả dụng</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bảng danh sách admins -->
            <div class="table-responsive">
                <table id="adminsTable" class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Tên đăng nhập</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Ghi chú</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td><?php echo $admin['admin_id']; ?></td>
                                <td><?php echo htmlspecialchars($admin['ten_dn']); ?></td>
                                <td><?php echo htmlspecialchars($admin['ho_ten']); ?></td>
                                <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                <td>
                                    <span title="<?php echo htmlspecialchars($admin['ghi_chu'] ?? ''); ?>">
                                        <?php echo htmlspecialchars(substr($admin['ghi_chu'] ?? '', 0, 30)); ?>
                                        <?php if (strlen($admin['ghi_chu'] ?? '') > 30): ?>...<?php endif; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($admin['tao_luc'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning me-1" onclick="editAdmin(<?php echo $admin['admin_id']; ?>, '<?php echo htmlspecialchars($admin['ho_ten']); ?>', '<?php echo htmlspecialchars($admin['ghi_chu'] ?? ''); ?>')">
                                        <i class="fa-solid fa-edit"></i> Sửa
                                    </button>
                                    <?php if ($admin['tai_khoan_id'] != $_SESSION['user_id']): ?>
                                        <button class="btn btn-sm btn-danger" onclick="deleteAdmin(<?php echo $admin['admin_id']; ?>, '<?php echo htmlspecialchars($admin['ho_ten']); ?>')">
                                            <i class="fa-solid fa-trash"></i> Xóa
                                        </button>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Admin hiện tại</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Thêm Admin -->
        <div class="modal fade" id="addAdminModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Thêm Admin mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="add_admin">

                            <div class="mb-3">
                                <label class="form-label">Chọn tài khoản người dùng <span class="text-danger">*</span></label>
                                <select name="existing_account_id" class="form-select" required>
                                    <option value="">-- Chọn tài khoản --</option>
                                    <?php foreach ($available_accounts as $account): ?>
                                        <option value="<?php echo $account['tai_khoan_id']; ?>">
                                            <?php echo htmlspecialchars($account['ten_dn'] . ' (' . $account['email'] . ') - ' . $account['ten_vai_tro']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (empty($available_accounts)): ?>
                                    <small class="text-muted">Không có tài khoản người dùng nào khả dụng để gán quyền admin.</small>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                                <input type="text" name="ho_ten" class="form-control" required placeholder="Nhập họ tên đầy đủ">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ghi chú nội bộ</label>
                                <textarea name="ghi_chu" class="form-control" rows="3" placeholder="Ghi chú nội bộ về admin này..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary">Thêm Admin</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Sửa Admin -->
        <div class="modal fade" id="editAdminModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cập nhật thông tin Admin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="update_admin">
                            <input type="hidden" name="admin_id" id="edit_admin_id">

                            <div class="mb-3">
                                <label class="form-label">Họ tên</label>
                                <input type="text" name="ho_ten" id="edit_ho_ten" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ghi chú nội bộ</label>
                                <textarea name="ghi_chu" id="edit_ghi_chu" class="form-control" rows="3" placeholder="Ghi chú nội bộ về admin này..."></textarea>
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

        <!-- Modal Xóa Admin -->
        <div class="modal fade" id="deleteAdminModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Xác nhận xóa Admin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="delete_admin">
                            <input type="hidden" name="admin_id" id="delete_admin_id">

                            <p>Bạn có chắc muốn xóa admin <strong id="delete_admin_name"></strong>?</p>
                            <div class="alert alert-warning">
                                <i class="fa-solid fa-exclamation-triangle"></i>
                                Hành động này không thể hoàn tác!
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-danger">Xóa Admin</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#adminsTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/vi.json"
                },
                "pageLength": 25,
                "order": [[ 5, "desc" ]]
            });
        });

        // Edit admin function
        function editAdmin(adminId, hoTen, ghiChu) {
            document.getElementById('edit_admin_id').value = adminId;
            document.getElementById('edit_ho_ten').value = hoTen;
            document.getElementById('edit_ghi_chu').value = ghiChu;

            new bootstrap.Modal(document.getElementById('editAdminModal')).show();
        }

        // Delete admin function
        function deleteAdmin(adminId, hoTen) {
            document.getElementById('delete_admin_id').value = adminId;
            document.getElementById('delete_admin_name').textContent = hoTen;

            new bootstrap.Modal(document.getElementById('deleteAdminModal')).show();
        }
    </script>