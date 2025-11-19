<?php
/**
 * Header chung cho nhà tuyển dụng
 */
?>
<!-- Topbar -->
<div class="topbar">
    <h2><i class="fa fa-tachometer-alt"></i> <span id="page-title">Dashboard</span></h2>
    <div class="user-info">
        <span class="user-name">
            <i class="fa fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['ten_dn'] ?? 'Nhà tuyển dụng'); ?>
        </span>
        <a href="<?php echo BASE_URL; ?>logout.php" class="btn-logout">
            <i class="fa fa-sign-out-alt"></i> Đăng xuất
        </a>
    </div>
</div>

