<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../controllers/admin_management_logic.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Admin - Hệ thống tuyển dụng</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <!-- Admin Common CSS -->
    <link href="<?php echo BASE_URL; ?>pages/admin/assets/css/common.css" rel="stylesheet">

    <style>
        .modal-content {
            border-radius: 15px;
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <!-- TOP BAR -->
        <div class="topbar">
            <div class="username">Xin chào,
                <strong><?php echo htmlspecialchars($admin['ho_ten'] ?? $admin['ten_dn']); ?></strong>
            </div>

            <a href="<?php echo BASE_URL; ?>logout.php" class="logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
            </a>
        </div>

        <!-- ADMIN MANAGEMENT CONTENT -->
        <?php include __DIR__ . '/../views/admin_management_content.php'; ?>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Scripts -->
    <script src="<?php echo BASE_URL; ?>pages/admin/assets/js/admin.js"></script>

</body>
</html>

