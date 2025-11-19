<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../controllers/dashboard_logic.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Hệ thống tuyển dụng</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Admin Common CSS -->
    <link href="<?php echo BASE_URL; ?>pages/admin/assets/css/common.css" rel="stylesheet">

    <style>
        .dashboard-box {
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .stat-card {
            background: #667eea;
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .stat-card.success {
            background: #28a745;
        }

        .stat-card.warning {
            background: #ffc107;
            color: #333;
        }

        .stat-card.info {
            background: #17a2b8;
        }

        .stat-card.danger {
            background: #dc3545;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
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

        <!-- DASHBOARD CONTENT -->
        <?php include __DIR__ . '/../views/dashboard_content.php'; ?>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Scripts -->
    <script src="<?php echo BASE_URL; ?>pages/admin/assets/js/admin.js"></script>

</body>
</html>

