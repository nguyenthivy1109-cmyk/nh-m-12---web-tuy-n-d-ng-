<?php
// Helper function để check active page
if (!function_exists('isActivePage')) {
    function isActivePage($page_name) {
        $current_uri = $_SERVER['REQUEST_URI'] ?? '';
        // Check nếu URI chứa tên page
        return strpos($current_uri, $page_name) !== false;
    }
}
?>
<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo">
        <h4><i class="fas fa-user-shield"></i> ADMIN</h4>
    </div>
    <nav class="nav flex-column mt-3">
        <a class="nav-link <?php echo isActivePage('dashboard') ? 'active' : ''; ?>" 
           href="<?php echo adminRoute('dashboard'); ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a class="nav-link <?php echo isActivePage('accounts') ? 'active' : ''; ?>" 
           href="<?php echo adminRoute('accounts'); ?>">
            <i class="fas fa-users-cog"></i> Quản lý Tài khoản
        </a>
        <a class="nav-link <?php echo isActivePage('admin_management') ? 'active' : ''; ?>" 
           href="<?php echo adminRoute('admin_management'); ?>">
            <i class="fas fa-user-shield"></i> Quản lý Admin
        </a>
        <a class="nav-link <?php echo isActivePage('company_management') ? 'active' : ''; ?>" 
           href="<?php echo adminRoute('company_management'); ?>">
            <i class="fas fa-building"></i> Quản lý Công ty
        </a>
        <a class="nav-link <?php echo isActivePage('recruiter_management') ? 'active' : ''; ?>" 
           href="<?php echo adminRoute('recruiter_management'); ?>">
            <i class="fas fa-user-tie"></i> Quản lý NTD
        </a>
        <a class="nav-link <?php echo isActivePage('candidate_management') ? 'active' : ''; ?>" 
           href="<?php echo adminRoute('candidate_management'); ?>">
            <i class="fas fa-user"></i> Quản lý Ứng viên
        </a>
        <a class="nav-link <?php echo isActivePage('job_management') ? 'active' : ''; ?>" 
           href="<?php echo adminRoute('job_management'); ?>">
            <i class="fas fa-briefcase"></i> Quản lý Tin tuyển dụng
        </a>
        <a class="nav-link <?php echo isActivePage('applications_management') ? 'active' : ''; ?>" 
           href="<?php echo adminRoute('applications_management'); ?>">
            <i class="fas fa-file-signature"></i> Quản lý Hồ sơ ứng tuyển
        </a>
        <a class="nav-link <?php echo isActivePage('messages_management') ? 'active' : ''; ?>" 
           href="<?php echo adminRoute('messages_management'); ?>">
            <i class="fas fa-comments"></i> Quản lý Tin nhắn
        </a>
        <a class="nav-link <?php echo isActivePage('notifications_management') ? 'active' : ''; ?>" 
           href="<?php echo adminRoute('notifications_management'); ?>">
            <i class="fas fa-bell"></i> Quản lý Thông báo
        </a>
        <a class="nav-link <?php echo isActivePage('attachments_management') ? 'active' : ''; ?>" 
           href="<?php echo adminRoute('attachments_management'); ?>">
            <i class="fas fa-paperclip"></i> Quản lý Tập tin đính kèm
        </a>
        <a class="nav-link <?php echo isActivePage('categories_management') ? 'active' : ''; ?>" 
           href="<?php echo adminRoute('categories_management'); ?>">
            <i class="fas fa-tags"></i> Quản lý Danh mục ngành nghề
        </a>
        <a class="nav-link <?php echo isActivePage('skills_dictionary') ? 'active' : ''; ?>" 
           href="<?php echo adminRoute('skills_dictionary'); ?>">
            <i class="fas fa-cogs"></i> Quản lý Từ điển kỹ năng
        </a>
        <a class="nav-link" href="<?php echo BASE_URL; ?>logout.php">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
    </nav>
</div>

<style>
    .sidebar {
        min-height: 100vh;
        background: #1f2937;
        color: white;
        position: fixed;
        left: 0;
        top: 0;
        width: 250px;
        z-index: 1000;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }

    .sidebar .logo {
        padding: 1.5rem;
        text-align: center;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        background: rgba(0,0,0,0.2);
    }

    .sidebar .logo h4 {
        margin: 0;
        font-weight: 700;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .sidebar .nav-link {
        color: rgba(255,255,255,0.8);
        padding: 0.8rem 1.5rem;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
        text-decoration: none;
    }

    .sidebar .nav-link:hover {
        background: rgba(255,255,255,0.1);
        color: #fff;
        border-left-color: #3b82f6;
        padding-left: 2rem;
    }

    .sidebar .nav-link.active {
        background: rgba(59, 130, 246, 0.2);
        color: #fff;
        border-left-color: #3b82f6;
        font-weight: 600;
    }

    .sidebar .nav-link i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }

    .main-content {
        margin-left: 250px;
        padding: 2rem;
    }
</style>
