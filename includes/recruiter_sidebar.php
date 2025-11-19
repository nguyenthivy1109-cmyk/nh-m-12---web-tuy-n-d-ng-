<?php
/**
 * Sidebar chung cho nhà tuyển dụng
 */
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="sidebar">
    <div class="logo">
        <h3><i class="fa fa-briefcase"></i> Nhà tuyển dụng</h3>
    </div>
    <ul class="menu">
        <li><a href="index.php" class="menu-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" data-page="index.php">
            <i class="fa fa-home"></i> Dashboard
        </a></li>
        <li><a href="jobs.php" class="menu-link <?php echo $current_page == 'jobs.php' ? 'active' : ''; ?>" data-page="jobs.php">
            <i class="fa fa-briefcase"></i> Quản lý tin tuyển dụng
        </a></li>
        <li><a href="post-job.php" class="menu-link <?php echo $current_page == 'post-job.php' ? 'active' : ''; ?>" data-page="post-job.php">
            <i class="fa fa-plus-circle"></i> Đăng tin tuyển dụng
        </a></li>
        <li><a href="applications.php" class="menu-link <?php echo $current_page == 'applications.php' ? 'active' : ''; ?>" data-page="applications.php">
            <i class="fa fa-file-alt"></i> Ứng tuyển
        </a></li>
        <li><a href="company.php" class="menu-link <?php echo $current_page == 'company.php' ? 'active' : ''; ?>" data-page="company.php">
            <i class="fa fa-building"></i> Thông tin công ty
        </a></li>
        <li><a href="messages.php" class="menu-link <?php echo $current_page == 'messages.php' ? 'active' : ''; ?>" data-page="messages.php">
            <i class="fa fa-envelope"></i> Tin nhắn
        </a></li>
        <li><a href="<?php echo BASE_URL . 'index.php?home=1'; ?>" class="menu-link">
            <i class="fa fa-globe"></i> Về trang chủ
        </a></li>
    </ul>
</div>

