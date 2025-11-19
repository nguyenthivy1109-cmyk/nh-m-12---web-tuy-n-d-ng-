        <!-- ACCOUNTS MANAGEMENT CONTENT -->
        <div class="content-box">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fa-solid fa-users"></i> Quản lý tài khoản</h3>
                <div class="d-flex gap-2">
                    <input type="text" id="searchInput" class="form-control" placeholder="Tìm kiếm theo tên đăng nhập hoặc email..." style="width: 300px;">
                    <button class="btn btn-primary" onclick="searchAccounts()">
                        <i class="fa-solid fa-search"></i> Tìm kiếm
                    </button>
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
                <div class="col-md-2">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h5><?php echo $stats['total']; ?></h5>
                            <small>Tổng tài khoản</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h5><?php echo $stats['active']; ?></h5>
                            <small>Đang hoạt động</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h5><?php echo $stats['locked']; ?></h5>
                            <small>Đã khóa</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h5><?php echo $stats['admins']; ?></h5>
                            <small>Admin</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h5><?php echo $stats['recruiters']; ?></h5>
                            <small>Nhà tuyển dụng</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-secondary text-white">
                        <div class="card-body text-center">
                            <h5><?php echo $stats['candidates']; ?></h5>
                            <small>Ứng viên</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bảng danh sách tài khoản -->
            <div class="table-responsive">
                <table id="accountsTable" class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Tên đăng nhập</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Đăng nhập cuối</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accounts as $account): ?>
                            <tr>
                                <td><?php echo $account['tai_khoan_id']; ?></td>
                                <td><?php echo htmlspecialchars($account['ten_dn']); ?></td>
                                <td><?php echo htmlspecialchars($account['ho_ten'] ?? 'Chưa cập nhật'); ?></td>
                                <td><?php echo htmlspecialchars($account['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php
                                        echo $account['vai_tro_id'] == 1 ? 'admin' :
                                             ($account['vai_tro_id'] == 2 ? 'recruiter' : 'candidate');
                                    ?>">
                                        <?php echo htmlspecialchars($account['ten_vai_tro']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $account['kich_hoat'] == 1 ? 'active' : 'locked'; ?>">
                                        <?php echo $account['kich_hoat'] == 1 ? 'Hoạt động' : 'Đã khóa'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($account['ngay_tao'])); ?></td>
                                <td>
                                    <?php
                                    if ($account['lan_dang_nhap_cuoi']) {
                                        echo date('d/m/Y H:i', strtotime($account['lan_dang_nhap_cuoi']));
                                    } else {
                                        echo '<em class="text-muted">Chưa đăng nhập</em>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <button class="action-btn btn-edit" onclick="changeRole(<?php echo $account['tai_khoan_id']; ?>, <?php echo $account['vai_tro_id']; ?>)">
                                        <i class="fa-solid fa-edit"></i> Vai trò
                                    </button>
                                    <?php if ($account['kich_hoat'] == 1): ?>
                                        <button class="action-btn btn-lock" onclick="toggleLock(<?php echo $account['tai_khoan_id']; ?>, 'active')">
                                            <i class="fa-solid fa-lock"></i> Khóa
                                        </button>
                                    <?php else: ?>
                                        <button class="action-btn btn-unlock" onclick="toggleLock(<?php echo $account['tai_khoan_id']; ?>, 'locked')">
                                            <i class="fa-solid fa-unlock"></i> Mở khóa
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <script>
        // Search functionality
        function searchAccounts() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const table = document.getElementById('accountsTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const username = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();
                const email = rows[i].getElementsByTagName('td')[3].textContent.toLowerCase();

                if (username.includes(searchTerm) || email.includes(searchTerm)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }

        // Enter key support for search
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchAccounts();
            }
        });
    </script>