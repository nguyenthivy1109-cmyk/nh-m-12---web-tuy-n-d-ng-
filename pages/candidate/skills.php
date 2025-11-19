<?php
/**
 * Trang quản lý kỹ năng ứng viên
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/ung_vien.php';
require_once __DIR__ . '/../../models/ky_nang.php';

// Kiểm tra đăng nhập và quyền ứng viên
requireRole(ROLE_CANDIDATE);

$ung_vien = getUngVienByTaiKhoanId($pdo, $_SESSION['user_id']);

if (!$ung_vien) {
    header("Location: " . BASE_URL . "candidate/profile.php");
    exit();
}

$error = '';
$success = '';

// Xử lý thêm kỹ năng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_skill') {
    $kn_id = isset($_POST['kn_id']) ? (int)$_POST['kn_id'] : 0;
    $muc_do = isset($_POST['muc_do']) ? (int)$_POST['muc_do'] : 3;
    
    if ($kn_id) {
        $result = addKyNangChoUngVien($pdo, $ung_vien['ung_vien_id'], $kn_id, $muc_do);
        if ($result) {
            $success = 'Thêm kỹ năng thành công!';
        } else {
            $error = 'Có lỗi xảy ra!';
        }
    }
}

// Xử lý xóa kỹ năng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_skill') {
    $kn_id = isset($_POST['kn_id']) ? (int)$_POST['kn_id'] : 0;
    
    if ($kn_id) {
        $result = removeKyNangCuaUngVien($pdo, $ung_vien['ung_vien_id'], $kn_id);
        if ($result) {
            $success = 'Xóa kỹ năng thành công!';
        } else {
            $error = 'Có lỗi xảy ra!';
        }
    }
}

// Lấy danh sách kỹ năng của ứng viên
$my_skills = getKyNangCuaUngVien($pdo, $ung_vien['ung_vien_id']);

// Lấy tất cả kỹ năng có sẵn
$all_skills = getAllKyNang($pdo);

// Lọc ra các kỹ năng chưa có
$my_skill_ids = array_column($my_skills, 'kn_id');
$available_skills = array_filter($all_skills, function($skill) use ($my_skill_ids) {
    return !in_array($skill['kn_id'], $my_skill_ids);
});

// Map mức độ
$muc_do_map = [
    1 => 'Cơ bản',
    2 => 'Trung bình',
    3 => 'Khá',
    4 => 'Tốt',
    5 => 'Xuất sắc'
];

$page_title = 'Quản lý kỹ năng';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Ứng viên</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Lato&family=Oswald:wght@200;300;400&display=swap" rel="stylesheet">
    <!-- Template Stylesheet -->
    <link href="../css/style.css" rel="stylesheet">
    
    <style>
        .skills-container {
            max-width: 1000px;
            margin: 40px auto;
        }
        .skills-box {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .skill-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .skill-item:hover {
            background: #f8f9fa;
        }
        .skill-level-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .level-1 { background: #ffebee; color: #c62828; }
        .level-2 { background: #fff3e0; color: #e65100; }
        .level-3 { background: #e3f2fd; color: #1565c0; }
        .level-4 { background: #e8f5e9; color: #2e7d32; }
        .level-5 { background: #f3e5f5; color: #6a1b9a; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../includes/header.php'; ?>
    
    <div class="container">
        <div class="skills-container">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <!-- Kỹ năng của tôi -->
            <div class="skills-box">
                <h3 style="font-family: 'Oswald', sans-serif; color: #092a49; margin-bottom: 20px;">
                    <i class="fa fa-list"></i> Kỹ năng của tôi (<?php echo count($my_skills); ?>)
                </h3>
                
                <?php if (empty($my_skills)): ?>
                    <p class="text-muted">Bạn chưa có kỹ năng nào. Hãy thêm kỹ năng của bạn bên dưới!</p>
                <?php else: ?>
                    <?php foreach ($my_skills as $skill): ?>
                        <div class="skill-item">
                            <div>
                                <strong><?php echo htmlspecialchars($skill['ten_kn']); ?></strong>
                                <br>
                                <span class="skill-level-badge level-<?php echo $skill['muc_do']; ?>">
                                    <?php echo $muc_do_map[$skill['muc_do']] ?? ''; ?>
                                </span>
                            </div>
                            <form method="POST" style="display: inline;" 
                                  onsubmit="return confirm('Bạn có chắc muốn xóa kỹ năng này?');">
                                <input type="hidden" name="action" value="remove_skill">
                                <input type="hidden" name="kn_id" value="<?php echo $skill['kn_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fa fa-trash"></i> Xóa
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Thêm kỹ năng mới -->
            <div class="skills-box">
                <h3 style="font-family: 'Oswald', sans-serif; color: #092a49; margin-bottom: 20px;">
                    <i class="fa fa-plus-circle"></i> Thêm kỹ năng mới
                </h3>
                
                <?php if (empty($available_skills)): ?>
                    <p class="text-muted">Bạn đã thêm tất cả kỹ năng có sẵn!</p>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_skill">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-cog"></i> Chọn kỹ năng</label>
                                    <select class="form-control" name="kn_id" required>
                                        <option value="">-- Chọn kỹ năng --</option>
                                        <?php foreach ($available_skills as $skill): ?>
                                            <option value="<?php echo $skill['kn_id']; ?>">
                                                <?php echo htmlspecialchars($skill['ten_kn']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fa fa-star"></i> Mức độ</label>
                                    <select class="form-control" name="muc_do" required>
                                        <option value="1">Cơ bản</option>
                                        <option value="2">Trung bình</option>
                                        <option value="3" selected>Khá</option>
                                        <option value="4">Tốt</option>
                                        <option value="5">Xuất sắc</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fa fa-plus"></i> Thêm
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>

