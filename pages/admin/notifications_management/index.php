<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../controllers/notifications_management_logic.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Thông báo - Hệ thống tuyển dụng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>pages/admin/assets/css/common.css" rel="stylesheet">
</head>
<body>
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="topbar">
                <div class="username">Xin chào, <strong><?php echo htmlspecialchars($admin['ho_ten'] ?? $admin['ten_dn']); ?></strong></div>
                <a href="<?php echo BASE_URL; ?>logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
            </div>
            <?php include __DIR__ . '/../views/notifications_management_content.php'; ?>
        </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="<?php echo BASE_URL; ?>pages/admin/assets/js/admin.js"></script>
</body>
</html>

