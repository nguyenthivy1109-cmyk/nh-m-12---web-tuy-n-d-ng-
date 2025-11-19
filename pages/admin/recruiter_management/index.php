<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../controllers/recruiter_management_logic.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Nhà tuyển dụng - Hệ thống tuyển dụng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link href="<?php echo BASE_URL; ?>pages/admin/assets/css/common.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div class="username">Xin chào, <strong><?php echo htmlspecialchars($admin['ho_ten'] ?? $admin['ten_dn']); ?></strong></div>
            <a href="<?php echo BASE_URL; ?>logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
        </div>
        <?php include __DIR__ . '/../views/recruiter_management_content.php'; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>pages/admin/assets/js/admin.js"></script>
</body>
</html>

