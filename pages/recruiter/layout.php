<?php
/**
 * Layout chung cho nhà tuyển dụng
 * File này chứa sidebar và main content area
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Kiểm tra đăng nhập và quyền nhà tuyển dụng
requireRole(ROLE_RECRUITER);

$current_page = $_GET['page'] ?? 'dashboard';
$page_title = 'Dashboard - Nhà tuyển dụng';

// Map page names to file names
$page_file_map = [
    'dashboard' => 'index',
    'jobs' => 'jobs',
    'post-job' => 'post-job',
    'applications' => 'applications',
    'company' => 'company',
    'messages' => 'messages'
];

// Map page titles
$page_titles = [
    'dashboard' => 'Dashboard',
    'jobs' => 'Quản lý tin tuyển dụng',
    'post-job' => 'Đăng tin tuyển dụng',
    'applications' => 'Ứng tuyển',
    'company' => 'Thông tin',
    'messages' => 'Tin nhắn'
];

if (isset($page_titles[$current_page])) {
    $page_title = $page_titles[$current_page];
}

// Get file name from page name
$file_name = $page_file_map[$current_page] ?? 'index';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Nhà tuyển dụng</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Lato&family=Oswald:wght@200;300;400&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Lato', sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 250px;
            background: #0796fe;
            color: #fff;
            padding: 20px 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar .logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar .logo h3 {
            color: #fff;
            font-family: 'Oswald', sans-serif;
            font-weight: 400;
            margin: 0;
        }
        
        .sidebar .menu {
            list-style: none;
            padding: 0;
        }
        
        .sidebar .menu li {
            margin: 5px 0;
        }
        
        .sidebar .menu a {
            display: block;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            cursor: pointer;
        }
        
        .sidebar .menu a:hover,
        .sidebar .menu a.active {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border-left-color: #fff;
        }
        
        .sidebar .menu a i {
            width: 25px;
            margin-right: 10px;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }
        
        .topbar {
            background: #fff;
            padding: 15px 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .topbar h2 {
            font-family: 'Oswald', sans-serif;
            color: #092a49;
            margin: 0;
            font-size: 24px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info .user-name {
            color: #092a49;
            font-weight: 600;
        }
        
        .user-info .btn-logout {
            padding: 8px 20px;
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .user-info .btn-logout:hover {
            background: #c82333;
        }
        
        #content-area {
            min-height: calc(100vh - 200px);
        }
        
        .loading {
            text-align: center;
            padding: 50px;
            color: #797979;
        }
        
        .loading i {
            font-size: 48px;
            color: #0796fe;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .stat-card .icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #fff;
        }
        
        .stat-card .icon.blue {
            background: #0796fe;
        }
        
        .stat-card .icon.green {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        
        .stat-card .icon.orange {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        }
        
        .stat-card .icon.purple {
            background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
        }
        
        .stat-card .content h3 {
            font-size: 32px;
            color: #092a49;
            margin: 0;
            font-family: 'Oswald', sans-serif;
        }
        
        .stat-card .content p {
            color: #797979;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        
        .content-box {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .content-box h4 {
            font-family: 'Oswald', sans-serif;
            color: #092a49;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-group label {
            color: #092a49;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            /* padding: 12px 15px; */
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #0796fe;
            box-shadow: 0 0 0 0.2rem rgba(7, 150, 254, 0.15);
        }
        
        .btn-primary-custom {
            background: #0796fe;
            color: #fff;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        /* Dashboard specific styles */
        .dashboard-wrapper {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        
        .stat-card.primary .stat-icon,
        .stat-card.success .stat-icon,
        .stat-card.warning .stat-icon,
        .stat-card.secondary .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 24px;
        }
        
        .stat-card.primary .stat-icon { background: #0796fe; }
        .stat-card.success .stat-icon { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
        .stat-card.warning .stat-icon { background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); }
        .stat-card.secondary .stat-icon { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); }
        
        .stat-content {
            display: flex;
            flex-direction: column;
        }
        
        .stat-label {
            font-size: 14px;
            color: #6c7a89;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #092a49;
        }
        
        .quick-action-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }
        
        .quick-action-card .action-btn {
            padding: 0 28px;
            height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .dashboard-table th {
            background: #f8fafc;
            color: #092a49;
            font-weight: 600;
            border-top: none;
        }
        
        .dashboard-table td {
            vertical-align: middle;
            color: #092a49;
        }
        
        .dashboard-table .sub-meta {
            font-size: 12px;
            color: #6c7a89;
            margin-top: 4px;
        }
        
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success { background: rgba(40, 167, 69, 0.15); color: #218838; }
        .badge-danger { background: rgba(220, 53, 69, 0.15); color: #c82333; }
        .badge-warning { background: rgba(255, 193, 7, 0.15); color: #856404; }
        .badge-secondary { background: rgba(108, 117, 125, 0.15); color: #6c757d; }
        
        .view-all {
            font-size: 14px;
            color: #0796fe;
            text-decoration: none;
            font-weight: 600;
        }
        
        .view-all:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .quick-action-card {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .quick-action-card .action-btn {
                width: 100%;
            }
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(7, 150, 254, 0.3);
            color: #fff;
        }
        
        .table {
            margin: 0;
        }
        
        .table thead th {
            background: #f8f9fa;
            color: #092a49;
            font-weight: 600;
            border: none;
            padding: 12px;
        }
        
        .table tbody td {
            padding: 12px;
            vertical-align: middle;
        }
        
        .alert {
            border-radius: 5px;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: none;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h3><i class="fa fa-briefcase"></i> Nhà tuyển dụng</h3>
        </div>
        <ul class="menu">
            <li><a href="<?php echo recruiterRoute('dashboard'); ?>" class="menu-link <?php echo ($current_page == 'dashboard' || $current_page == 'index') ? 'active' : ''; ?>">
                <i class="fa fa-home"></i> Dashboard
            </a></li>
            <li><a href="<?php echo recruiterRoute('post-job'); ?>" class="menu-link <?php echo $current_page == 'post-job' ? 'active' : ''; ?>">
                <i class="fa fa-plus-circle"></i> Đăng tin tuyển dụng
            </a></li>
            <li><a href="<?php echo recruiterRoute('company'); ?>" class="menu-link <?php echo $current_page == 'company' ? 'active' : ''; ?>">
                <i class="fa fa-building"></i> Thông tin
            </a></li>
            <li><a href="<?php echo recruiterRoute('jobs'); ?>" class="menu-link <?php echo $current_page == 'jobs' ? 'active' : ''; ?>">
                <i class="fa fa-briefcase"></i> Quản lý tin tuyển dụng
            </a></li>
            <li><a href="<?php echo recruiterRoute('applications'); ?>" class="menu-link <?php echo $current_page == 'applications' ? 'active' : ''; ?>">
                <i class="fa fa-file-alt"></i> Ứng tuyển
            </a></li>
            <!-- <li><a href="?page=messages" class="menu-link <?php echo $current_page == 'messages' ? 'active' : ''; ?>" data-page="messages">
                <i class="fa fa-envelope"></i> Tin nhắn
            </a></li> -->
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <h2><span id="page-title"><?php echo $page_title; ?></span></h2>
            <div class="user-info">
                <span class="user-name">
                    <i class="fa fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['ten_dn'] ?? 'Nhà tuyển dụng'); ?>
                </span>
                <a href="<?php echo BASE_URL; ?>logout.php" class="btn-logout">
                    <i class="fa fa-sign-out-alt"></i> Đăng xuất
                </a>
            </div>
        </div>
        
        <!-- Content Area -->
        <div id="content-area">
            <?php
            // Load content dựa trên page
            $content_file = __DIR__ . '/' . $file_name . '.php';
            if (file_exists($content_file)) {
                include $content_file;
            } else {
                include __DIR__ . '/index.php';
            }
            ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Xử lý click menu link (delegation để hoạt động với nội dung load động)
            $(document).off('click', '.menu-link[data-page]').on('click', '.menu-link[data-page]', function(e) {
                e.preventDefault();
                
                var page = $(this).data('page');
                // Lấy link tương ứng trong sidebar để highlight
                var $sidebarLink = $('.sidebar .menu-link[data-page="' + page + '"]');
                var pageTitle = $sidebarLink.length ? $sidebarLink.text().trim() : $(this).text().trim();
                
                // Update active menu (chỉ highlight ở sidebar)
                $('.sidebar .menu-link').removeClass('active');
                if ($sidebarLink.length) {
                    $sidebarLink.addClass('active');
                }
                
                // Update page title
                $('#page-title').text(pageTitle);
                
                // Update URL without reload
                if (history.pushState) {
                    history.pushState(null, null, '?page=' + page);
                }
                
                // Show loading
                $('#content-area').html('<div class="loading"><i class="fa fa-spinner"></i><p>Đang tải...</p></div>');
                
                // Load content via AJAX
                $.ajax({
                    url: 'content/' + page + '.php',
                    type: 'GET',
                    success: function(response) {
                        $('#content-area').html(response);
                        // Re-init scripts if needed
                        initPageScripts();
                    },
                    error: function() {
                        $('#content-area').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Không thể tải nội dung. Vui lòng thử lại!</div>');
                    }
                });
            });
            
            // Function to load page (can be called from other scripts)
            window.loadPage = function(page) {
                $('.menu-link[data-page="' + page + '"]').click();
            };
            
            // Function to init page-specific scripts
            function initPageScripts() {
                // Gọi init function của trang jobs nếu có
                if (typeof window.initJobsPage === 'function') {
                    window.initJobsPage();
                }
                // Re-init form handlers cho cả 2 form
                $('#recruiterForm').off('submit').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var formData = $form.serialize();
                    var $btn = $form.find('button[type="submit"]');
                    $btn.prop('disabled', true);

                    $.ajax({
                        url: '<?php echo BASE_URL; ?>pages/recruiter/actions/save_recruiter.php',
                        type: 'POST',
                        data: formData,
                        success: function(response) {
                            var data = response;
                            try { if (typeof response === 'string') { data = JSON.parse(response); } } catch(e) {}

                            if (data && data.success) {
                                $('#content-area').prepend('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + (data.message || 'Cập nhật thành công') + '</div>');
                                setTimeout(function(){ loadPage('company'); }, 600);
                            } else {
                                var msg = (data && data.message) ? data.message : 'Không thể cập nhật thông tin nhà tuyển dụng';
                                $('#content-area').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + msg + '</div>');
                            }
                        },
                        error: function() {
                            $('#content-area').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Lỗi kết nối máy chủ</div>');
                        },
                        complete: function() {
                            $btn.prop('disabled', false);
                        }
                    });
                });
                
                $('#companyForm').off('submit').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var formData = $form.serialize();
                    var $btn = $form.find('button[type="submit"]');
                    $btn.prop('disabled', true);

                    $.ajax({
                        url: '<?php echo BASE_URL; ?>pages/recruiter/actions/save_company.php',
                        type: 'POST',
                        data: formData,
                        success: function(response) {
                            var data = response;
                            try { if (typeof response === 'string') { data = JSON.parse(response); } } catch(e) {}

                            if (data && data.success) {
                                $('#content-area').prepend('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + (data.message || 'Lưu thông tin thành công') + '</div>');
                                setTimeout(function(){ loadPage('company'); }, 600);
                            } else {
                                var msg = (data && data.message) ? data.message : 'Không thể lưu thông tin công ty';
                                $('#content-area').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + msg + '</div>');
                            }
                        },
                        error: function() {
                            $('#content-area').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Lỗi kết nối máy chủ</div>');
                        },
                        complete: function() {
                            $btn.prop('disabled', false);
                        }
                    });
                });
                
                // Re-init image upload handlers (for edit form)
                $('#logo-upload, #banner-upload').off('change').on('change', function() {
                    var file = this.files[0];
                    var type = $(this).data('type');
                    var previewId = type === 'logo' ? '#logo-preview' : '#banner-preview';
                    var inputId = type === 'logo' ? '#logo_url_input' : '#bia_url_input';
                    
                    if (!file) return;
                    
                    // Show preview
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $(previewId).html('<img src="' + e.target.result + '" alt="Preview">');
                    };
                    reader.readAsDataURL(file);
                    
                    // Upload file
                    var formData = new FormData();
                    formData.append('image', file);
                    formData.append('type', type);
                    
                    $.ajax({
                        url: '<?php echo BASE_URL; ?>pages/recruiter/upload_image.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            var data = response;
                            try { if (typeof response === 'string') { data = JSON.parse(response); } } catch(e) {}
                            if (data.success) {
                                $(inputId).val(data.url);
                                $(previewId).html('<img src="' + data.url + '" alt="Uploaded">');
                            } else {
                                alert('Lỗi: ' + data.message);
                            }
                        },
                        error: function() {
                            alert('Có lỗi xảy ra khi upload ảnh!');
                        }
                    });
                });
                
                // Image upload handlers for create company form
                $('#logo-upload-create, #banner-upload-create').off('change').on('change', function() {
                    var file = this.files[0];
                    var type = $(this).data('type');
                    var previewId = type === 'logo' ? '#logo-preview-create' : '#banner-preview-create';
                    var inputId = type === 'logo' ? '#logo_url_input_create' : '#bia_url_input_create';
                    
                    if (!file) return;
                    
                    // Show preview
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $(previewId).html('<img src="' + e.target.result + '" alt="Preview">');
                    };
                    reader.readAsDataURL(file);
                    
                    // Upload file
                    var formData = new FormData();
                    formData.append('image', file);
                    formData.append('type', type);
                    
                    $.ajax({
                        url: '<?php echo BASE_URL; ?>pages/recruiter/upload_image.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            var data = response;
                            try { if (typeof response === 'string') { data = JSON.parse(response); } } catch(e) {}
                            if (data.success) {
                                $(inputId).val(data.url);
                                $(previewId).html('<img src="' + data.url + '" alt="Uploaded">');
                            } else {
                                alert('Lỗi: ' + data.message);
                            }
                        },
                        error: function() {
                            alert('Có lỗi xảy ra khi upload ảnh!');
                        }
                    });
                });
                
                // Handle create company form submit
                $('#createCompanyForm').off('submit').on('submit', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var formData = $form.serialize();
                    var $btn = $form.find('button[type="submit"]');
                    $btn.prop('disabled', true);

                    $.ajax({
                        url: '<?php echo BASE_URL; ?>pages/recruiter/actions/save_company.php',
                        type: 'POST',
                        data: formData,
                        success: function(response) {
                            var data = response;
                            try { if (typeof response === 'string') { data = JSON.parse(response); } } catch(e) {}

                            if (data && data.success) {
                                $('#content-area').prepend('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + (data.message || 'Tạo công ty thành công') + '</div>');
                                setTimeout(function(){ loadPage('company'); }, 600);
                            } else {
                                var msg = (data && data.message) ? data.message : 'Không thể tạo công ty';
                                $('#content-area').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + msg + '</div>');
                            }
                        },
                        error: function() {
                            $('#content-area').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Lỗi kết nối máy chủ</div>');
                        },
                        complete: function() {
                            $btn.prop('disabled', false);
                        }
                    });
                });

                // Auto-dismiss success alerts after 3 seconds
                setTimeout(function() {
                    $('#content-area .alert-success').fadeOut(300, function() { $(this).remove(); });
                }, 3000);
            }
            
            // Init on page load
            initPageScripts();
            
            // Handle browser back/forward buttons
            window.addEventListener('popstate', function(e) {
                var urlParams = new URLSearchParams(window.location.search);
                var page = urlParams.get('page') || 'index';
                $('.menu-link[data-page="' + page + '"]').click();
            });
        });
    </script>
</body>
</html>

