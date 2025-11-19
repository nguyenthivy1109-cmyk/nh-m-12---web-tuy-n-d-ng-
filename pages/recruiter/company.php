<?php
/**
 * Company Content - Thông tin nhà tuyển dụng và công ty (2 tab)
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/tai_khoan.php';
require_once __DIR__ . '/../../models/nha_tuyen_dung.php';
require_once __DIR__ . '/../../models/cong_ty.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
$active_tab = $_GET['tab'] ?? 'recruiter'; // 'recruiter' hoặc 'company'

// Lấy thông tin nhà tuyển dụng
$nha_td = getNhaTuyenDungByTaiKhoanId($pdo, $user_id);

// Nếu chưa có thông tin nhà tuyển dụng, tạo mới
if (!$nha_td) {
    $nha_td_data = [
        'tai_khoan_id' => $user_id,
        'cong_ty_id' => null,
        'ho_ten' => null,
        'chuc_danh' => null,
        'email_cong_viec' => null
    ];
    $nha_td_id = createNhaTuyenDung($pdo, $nha_td_data);
    $nha_td = getNhaTuyenDungById($pdo, $nha_td_id);
}

// Lấy thông tin công ty (nếu có)
$cong_ty = null;
if ($nha_td['cong_ty_id']) {
    $cong_ty = getCongTyById($pdo, $nha_td['cong_ty_id']);
}

// Xử lý form cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_recruiter') {
        // Cập nhật thông tin nhà tuyển dụng
        $data = [
            'cong_ty_id' => $nha_td['cong_ty_id'],
            'ho_ten' => sanitize($_POST['ho_ten'] ?? ''),
            'chuc_danh' => sanitize($_POST['chuc_danh'] ?? ''),
            'email_cong_viec' => sanitize($_POST['email_cong_viec'] ?? '')
        ];
        
        if (updateNhaTuyenDung($pdo, $nha_td['nha_td_id'], $data)) {
            $success = 'Cập nhật thông tin nhà tuyển dụng thành công!';
            // Reload lại data
            $nha_td = getNhaTuyenDungByTaiKhoanId($pdo, $user_id);
            $active_tab = 'recruiter';
        } else {
            $error = 'Có lỗi xảy ra khi cập nhật!';
        }
    } elseif ($action === 'update_company' && $cong_ty) {
        // Cập nhật thông tin công ty
        $ten_cong_ty = sanitize($_POST['ten_cong_ty'] ?? '');
        
        if (empty($ten_cong_ty)) {
            $error = 'Vui lòng nhập tên công ty!';
        } else {
            $slug = createSlug($ten_cong_ty);
            $original_slug = $slug;
            $counter = 1;
            while (isSlugExists($pdo, $slug, $cong_ty['cong_ty_id'])) {
                $slug = $original_slug . '-' . $counter;
                $counter++;
            }
            
            $new_logo = isset($_POST['logo_url']) ? trim((string)$_POST['logo_url']) : '';
            $new_banner = isset($_POST['bia_url']) ? trim((string)$_POST['bia_url']) : '';

            $cong_ty_data = [
                'ten_cong_ty' => $ten_cong_ty,
                'slug' => $slug,
                'ma_so_thue' => sanitize($_POST['ma_so_thue'] ?? ''),
                'website' => sanitize($_POST['website'] ?? ''),
                'nganh_nghe' => sanitize($_POST['nganh_nghe'] ?? ''),
                'quy_mo' => sanitize($_POST['quy_mo'] ?? ''),
                'logo_url' => $new_logo !== '' ? sanitize($new_logo) : ($cong_ty['logo_url'] ?? null),
                'bia_url' => $new_banner !== '' ? sanitize($new_banner) : ($cong_ty['bia_url'] ?? null),
                'gioi_thieu' => $_POST['gioi_thieu'] ?? '',
                'dia_chi_tru_so' => sanitize($_POST['dia_chi_tru_so'] ?? '')
            ];
            
            if (updateCongTy($pdo, $cong_ty['cong_ty_id'], $cong_ty_data)) {
                $success = 'Cập nhật thông tin công ty thành công!';
                // Reload lại data
                $nha_td = getNhaTuyenDungByTaiKhoanId($pdo, $user_id);
                $cong_ty = getCongTyById($pdo, $cong_ty['cong_ty_id']);
                $active_tab = 'company';
            } else {
                $error = 'Có lỗi xảy ra khi cập nhật!';
            }
        }
    } elseif ($action === 'create_company') {
        // Tạo công ty mới
        $ten_cong_ty = sanitize($_POST['ten_cong_ty'] ?? '');
        
        if (empty($ten_cong_ty)) {
            $error = 'Vui lòng nhập tên công ty!';
        } else {
            $slug = createSlug($ten_cong_ty);
            $original_slug = $slug;
            $counter = 1;
            while (isSlugExists($pdo, $slug)) {
                $slug = $original_slug . '-' . $counter;
                $counter++;
            }
            
            $cong_ty_data = [
                'ten_cong_ty' => $ten_cong_ty,
                'slug' => $slug,
                'ma_so_thue' => sanitize($_POST['ma_so_thue'] ?? ''),
                'website' => sanitize($_POST['website'] ?? ''),
                'nganh_nghe' => sanitize($_POST['nganh_nghe'] ?? ''),
                'quy_mo' => sanitize($_POST['quy_mo'] ?? ''),
                'logo_url' => sanitize($_POST['logo_url'] ?? ''),
                'bia_url' => sanitize($_POST['bia_url'] ?? ''),
                'gioi_thieu' => $_POST['gioi_thieu'] ?? '',
                'dia_chi_tru_so' => sanitize($_POST['dia_chi_tru_so'] ?? '')
            ];
            
            $cong_ty_id = createCongTy($pdo, $cong_ty_data);
            
            if ($cong_ty_id) {
                updateCongTyForNhaTuyenDung($pdo, $user_id, $cong_ty_id);
                $success = 'Tạo công ty thành công!';
                // Reload lại data
                $nha_td = getNhaTuyenDungByTaiKhoanId($pdo, $user_id);
                $cong_ty = getCongTyById($pdo, $cong_ty_id);
                $active_tab = 'company';
            } else {
                $error = 'Có lỗi xảy ra khi tạo công ty!';
            }
        }
    }
}

// Reload lại data sau khi update (đảm bảo có dữ liệu mới nhất)
$nha_td = getNhaTuyenDungByTaiKhoanId($pdo, $user_id);
if ($nha_td && $nha_td['cong_ty_id']) {
    $cong_ty = getCongTyById($pdo, $nha_td['cong_ty_id']);
} else {
    $cong_ty = null;
}
?>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success" role="alert">
        <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<!-- Tabs Navigation -->
<div class="content-box" style="padding: 0;">
    <ul class="nav nav-tabs" id="infoTabs" role="tablist" style="border-bottom: 2px solid #f0f0f0; padding: 0 25px; margin: 0;">
        <li class="nav-item">
            <a class="nav-link <?php echo $active_tab === 'recruiter' ? 'active' : ''; ?>" 
               id="recruiter-tab" data-toggle="tab" href="#recruiter" role="tab">
                <i class="fa fa-user"></i> Thông tin nhà tuyển dụng
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $active_tab === 'company' ? 'active' : ''; ?>" 
               id="company-tab" data-toggle="tab" href="#company" role="tab">
                <i class="fa fa-building"></i> Thông tin công ty
            </a>
        </li>
    </ul>
</div>

<!-- Tab Content -->
<div class="tab-content" id="infoTabContent">
    <!-- Tab 1: Thông tin nhà tuyển dụng -->
    <div class="tab-pane fade <?php echo $active_tab === 'recruiter' ? 'show active' : ''; ?>" 
         id="recruiter" role="tabpanel">
        <div class="content-box">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fa fa-user"></i> Thông tin nhà tuyển dụng</h4>
                <button type="button" class="btn btn-primary-custom" onclick="toggleEditMode('recruiter')">
                    <i class="fa fa-edit"></i> <span id="recruiter-edit-text">Sửa</span>
                </button>
            </div>
            
            <!-- View Mode -->
            <div id="recruiter-view-mode">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><i class="fa fa-user"></i> Họ tên:</strong> 
                            <span><?php echo htmlspecialchars($nha_td['ho_ten'] ?: 'Chưa cập nhật'); ?></span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="fa fa-briefcase"></i> Chức danh:</strong> 
                            <span><?php echo htmlspecialchars($nha_td['chuc_danh'] ?: 'Chưa cập nhật'); ?></span>
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><i class="fa fa-envelope"></i> Email công việc:</strong> 
                            <span><?php echo htmlspecialchars($nha_td['email_cong_viec'] ?: 'Chưa cập nhật'); ?></span>
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><i class="fa fa-calendar"></i> Ngày tham gia:</strong> 
                            <span><?php echo $nha_td['tao_luc'] ? formatDate($nha_td['tao_luc'], 'd/m/Y H:i') : 'Chưa có thông tin'; ?></span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="fa fa-calendar-check"></i> Cập nhật lần cuối:</strong> 
                            <span><?php echo $nha_td['cap_nhat_luc'] ? formatDate($nha_td['cap_nhat_luc'], 'd/m/Y H:i') : 'Chưa có thông tin'; ?></span>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Edit Mode -->
            <div id="recruiter-edit-mode" style="display: none;">
                <form method="POST" id="recruiterForm">
                    <input type="hidden" name="action" value="update_recruiter">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-user"></i> Họ tên</label>
                                <input type="text" class="form-control" name="ho_ten" 
                                       value="<?php echo htmlspecialchars($nha_td['ho_ten'] ?? ''); ?>" 
                                       placeholder="VD: Nguyễn Văn A">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-briefcase"></i> Chức danh</label>
                                <input type="text" class="form-control" name="chuc_danh" 
                                       value="<?php echo htmlspecialchars($nha_td['chuc_danh'] ?? ''); ?>" 
                                       placeholder="VD: Trưởng phòng Nhân sự">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-envelope"></i> Email công việc</label>
                                <input type="email" class="form-control" name="email_cong_viec" 
                                       value="<?php echo htmlspecialchars($nha_td['email_cong_viec'] ?? ''); ?>" 
                                       placeholder="VD: hr@company.com">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fa fa-save"></i> Lưu thay đổi
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="toggleEditMode('recruiter')" style="margin-left: 10px;">
                            <i class="fa fa-times"></i> Hủy
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Tab 2: Thông tin công ty -->
    <div class="tab-pane fade <?php echo $active_tab === 'company' ? 'show active' : ''; ?>" 
         id="company" role="tabpanel">
        <div class="content-box">
            <?php if ($cong_ty): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4><i class="fa fa-building"></i> Thông tin công ty</h4>
                    <button type="button" class="btn btn-primary-custom" onclick="toggleEditMode('company')">
                        <i class="fa fa-edit"></i> <span id="company-edit-text">Sửa</span>
                    </button>
                </div>
                
                <!-- View Mode -->
                <div id="company-view-mode">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <?php if ($cong_ty['bia_url']): ?>
                                <img src="<?php echo htmlspecialchars($cong_ty['bia_url']); ?>" 
                                     alt="Bìa công ty" class="company-banner">
                            <?php else: ?>
                                <div class="company-banner-placeholder">
                                    <i class="fa fa-image"></i> Chưa có ảnh bìa
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-3 text-center">
                            <?php if ($cong_ty['logo_url']): ?>
                                <img src="<?php echo htmlspecialchars($cong_ty['logo_url']); ?>" 
                                     alt="Logo công ty" class="company-logo">
                            <?php else: ?>
                                <div class="company-logo-placeholder">
                                    <i class="fa fa-building"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-9">
                            <h3><?php echo htmlspecialchars($cong_ty['ten_cong_ty']); ?></h3>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <p><strong><i class="fa fa-id-card"></i> Mã số thuế:</strong> 
                                        <?php echo htmlspecialchars($cong_ty['ma_so_thue'] ?: 'Chưa cập nhật'); ?>
                                    </p>
                                    <p><strong><i class="fa fa-globe"></i> Website:</strong> 
                                        <?php if ($cong_ty['website']): ?>
                                            <a href="<?php echo htmlspecialchars($cong_ty['website']); ?>" target="_blank">
                                                <?php echo htmlspecialchars($cong_ty['website']); ?>
                                            </a>
                                        <?php else: ?>
                                            Chưa cập nhật
                                        <?php endif; ?>
                                    </p>
                                    <p><strong><i class="fa fa-industry"></i> Ngành nghề:</strong> 
                                        <?php echo htmlspecialchars($cong_ty['nganh_nghe'] ?: 'Chưa cập nhật'); ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong><i class="fa fa-users"></i> Quy mô:</strong> 
                                        <?php echo htmlspecialchars($cong_ty['quy_mo'] ?: 'Chưa cập nhật'); ?>
                                    </p>
                                    <p><strong><i class="fa fa-map-marker-alt"></i> Địa chỉ:</strong> 
                                        <?php echo htmlspecialchars($cong_ty['dia_chi_tru_so'] ?: 'Chưa cập nhật'); ?>
                                    </p>
                                    <p><strong><i class="fa fa-calendar"></i> Ngày tạo:</strong> 
                                        <?php echo $cong_ty['tao_luc'] ? formatDate($cong_ty['tao_luc'], 'd/m/Y H:i') : 'Chưa có thông tin'; ?>
                                    </p>
                                    <p><strong><i class="fa fa-calendar-check"></i> Cập nhật lần cuối:</strong> 
                                        <?php echo $cong_ty['cap_nhat_luc'] ? formatDate($cong_ty['cap_nhat_luc'], 'd/m/Y H:i') : 'Chưa có thông tin'; ?>
                                    </p>
                                </div>
                            </div>
                            <?php if ($cong_ty['gioi_thieu']): ?>
                                <div class="mt-3">
                                    <strong><i class="fa fa-file-alt"></i> Giới thiệu:</strong>
                                    <p style="color: #555; line-height: 1.6; margin-top: 10px;">
                                        <?php echo nl2br(htmlspecialchars($cong_ty['gioi_thieu'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Edit Mode -->
                <div id="company-edit-mode" style="display: none;">
                    <form method="POST" id="companyForm" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_company">
                        <input type="hidden" name="logo_url" id="logo_url_input" value="<?php echo htmlspecialchars($cong_ty['logo_url'] ?? ''); ?>">
                        <input type="hidden" name="bia_url" id="bia_url_input" value="<?php echo htmlspecialchars($cong_ty['bia_url'] ?? ''); ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-building"></i> Tên công ty <span style="color: red;">*</span></label>
                                    <input type="text" class="form-control" name="ten_cong_ty" 
                                           value="<?php echo htmlspecialchars($cong_ty['ten_cong_ty']); ?>" 
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-id-card"></i> Mã số thuế</label>
                                    <input type="text" class="form-control" name="ma_so_thue" 
                                           value="<?php echo htmlspecialchars($cong_ty['ma_so_thue'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-globe"></i> Website</label>
                                    <input type="url" class="form-control" name="website" 
                                           value="<?php echo htmlspecialchars($cong_ty['website'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-industry"></i> Ngành nghề</label>
                                    <input type="text" class="form-control" name="nganh_nghe" 
                                           value="<?php echo htmlspecialchars($cong_ty['nganh_nghe'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-users"></i> Quy mô</label>
                                    <input type="text" class="form-control" name="quy_mo" 
                                           value="<?php echo htmlspecialchars($cong_ty['quy_mo'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-map-marker-alt"></i> Địa chỉ trụ sở</label>
                                    <input type="text" class="form-control" name="dia_chi_tru_so" 
                                           value="<?php echo htmlspecialchars($cong_ty['dia_chi_tru_so'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-image"></i> Logo công ty</label>
                                    <div class="image-upload-container">
                                        <div class="image-preview" id="logo-preview">
                                            <?php if ($cong_ty['logo_url']): ?>
                                                <img src="<?php echo htmlspecialchars($cong_ty['logo_url']); ?>" alt="Logo">
                                            <?php else: ?>
                                                <div class="no-image"><i class="fa fa-image"></i> Chưa có ảnh</div>
                                            <?php endif; ?>
                                        </div>
                                        <input type="file" class="form-control-file" id="logo-upload" accept="image/*" data-type="logo">
                                        <small class="form-text text-muted">Chọn ảnh logo (JPG, PNG, GIF, WEBP - tối đa 5MB)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-image"></i> Ảnh bìa</label>
                                    <div class="image-upload-container">
                                        <div class="image-preview" id="banner-preview">
                                            <?php if ($cong_ty['bia_url']): ?>
                                                <img src="<?php echo htmlspecialchars($cong_ty['bia_url']); ?>" alt="Bìa">
                                            <?php else: ?>
                                                <div class="no-image"><i class="fa fa-image"></i> Chưa có ảnh</div>
                                            <?php endif; ?>
                                        </div>
                                        <input type="file" class="form-control-file" id="banner-upload" accept="image/*" data-type="banner">
                                        <small class="form-text text-muted">Chọn ảnh bìa (JPG, PNG, GIF, WEBP - tối đa 5MB)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fa fa-file-alt"></i> Giới thiệu công ty</label>
                            <textarea class="form-control" name="gioi_thieu" rows="5"><?php echo htmlspecialchars($cong_ty['gioi_thieu'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary-custom">
                                <i class="fa fa-save"></i> Lưu thay đổi
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="toggleEditMode('company')" style="margin-left: 10px;">
                                <i class="fa fa-times"></i> Hủy
                            </button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <!-- Chưa có công ty - Hiển thị form tạo mới -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4><i class="fa fa-building"></i> Thông tin công ty</h4>
                </div>
                
                <div class="alert alert-info" role="alert">
                    <i class="fa fa-info-circle"></i> Bạn chưa có thông tin công ty. Vui lòng điền thông tin bên dưới để tạo công ty mới.
                </div>
                
                <form method="POST" id="createCompanyForm" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create_company">
                    <input type="hidden" name="logo_url" id="logo_url_input_create" value="">
                    <input type="hidden" name="bia_url" id="bia_url_input_create" value="">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-building"></i> Tên công ty <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" name="ten_cong_ty" 
                                       placeholder="Nhập tên công ty" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-id-card"></i> Mã số thuế</label>
                                <input type="text" class="form-control" name="ma_so_thue" 
                                       placeholder="Nhập mã số thuế">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-globe"></i> Website</label>
                                <input type="url" class="form-control" name="website" 
                                       placeholder="https://example.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-industry"></i> Ngành nghề</label>
                                <input type="text" class="form-control" name="nganh_nghe" 
                                       placeholder="VD: Công nghệ thông tin">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-users"></i> Quy mô</label>
                                <select class="form-control" name="quy_mo">
                                    <option value="">Chọn quy mô</option>
                                    <option value="1-10">1-10 nhân viên</option>
                                    <option value="11-50">11-50 nhân viên</option>
                                    <option value="51-200">51-200 nhân viên</option>
                                    <option value="201-500">201-500 nhân viên</option>
                                    <option value="501-1000">501-1000 nhân viên</option>
                                    <option value="1000+">Trên 1000 nhân viên</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fa fa-map-marker-alt"></i> Địa chỉ trụ sở</label>
                                <input type="text" class="form-control" name="dia_chi_tru_so" 
                                       placeholder="Nhập địa chỉ trụ sở">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fa fa-file-alt"></i> Giới thiệu công ty</label>
                        <textarea class="form-control" name="gioi_thieu" rows="5" 
                                  placeholder="Nhập giới thiệu về công ty"></textarea>
                    </div>
                    
                    <!-- Upload Logo -->
                    <div class="form-group">
                        <label><i class="fa fa-image"></i> Logo công ty</label>
                        <div class="image-upload-container">
                            <div class="image-preview" id="logo-preview-create">
                                <div class="no-image"><i class="fa fa-image"></i> Chưa có ảnh</div>
                            </div>
                            <input type="file" class="form-control-file" id="logo-upload-create" accept="image/*" data-type="logo">
                            <small class="form-text text-muted">Chọn ảnh logo (JPG, PNG, GIF, WEBP - tối đa 5MB)</small>
                        </div>
                    </div>
                    
                    <!-- Upload Banner -->
                    <div class="form-group">
                        <label><i class="fa fa-image"></i> Ảnh bìa công ty</label>
                        <div class="image-upload-container">
                            <div class="image-preview" id="banner-preview-create">
                                <div class="no-image"><i class="fa fa-image"></i> Chưa có ảnh</div>
                            </div>
                            <input type="file" class="form-control-file" id="banner-upload-create" accept="image/*" data-type="banner">
                            <small class="form-text text-muted">Chọn ảnh bìa (JPG, PNG, GIF, WEBP - tối đa 5MB)</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fa fa-plus-circle"></i> Tạo công ty
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.nav-tabs {
    border-bottom: 2px solid #f0f0f0;
}

.nav-tabs .nav-link {
    color: #797979;
    border: none;
    border-bottom: 3px solid transparent;
    padding: 15px 25px;
    transition: all 0.3s;
}

.nav-tabs .nav-link:hover {
    color: #0796fe;
    border-bottom-color: #0796fe;
}

.nav-tabs .nav-link.active {
    color: #0796fe;
    background: transparent;
    border-bottom-color: #0796fe;
    font-weight: 600;
}

.company-banner {
    width: 100%;
    max-height: 300px;
    object-fit: cover;
    border-radius: 10px;
}

.company-banner-placeholder {
    width: 100%;
    height: 200px;
    background: #f5f5f5;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ccc;
    font-size: 48px;
}

.company-logo {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 10px;
    border: 3px solid #f0f0f0;
}

.company-logo-placeholder {
    width: 150px;
    height: 150px;
    background: #f5f5f5;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ccc;
    font-size: 48px;
    border: 3px solid #f0f0f0;
}

.image-upload-container {
    margin-top: 10px;
}

.image-preview {
    width: 100%;
    max-width: 300px;
    height: 200px;
    border: 2px dashed #ddd;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    overflow: hidden;
    background: #f9f9f9;
}

.image-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.image-preview .no-image {
    color: #ccc;
    font-size: 48px;
}

.btn-secondary {
    background: #6c757d;
    color: #fff;
    border: none;
    padding: 12px 30px;
    border-radius: 5px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-secondary:hover {
    background: #5a6268;
    color: #fff;
}
</style>

<script>
function toggleEditMode(type) {
    var viewMode = document.getElementById(type + '-view-mode');
    var editMode = document.getElementById(type + '-edit-mode');
    var editText = document.getElementById(type + '-edit-text');
    
    if (viewMode && editMode && editText) {
        if (viewMode.style.display === 'none') {
            // Chuyển về view mode
            viewMode.style.display = 'block';
            editMode.style.display = 'none';
            editText.textContent = 'Sửa';
        } else {
            // Chuyển sang edit mode
            viewMode.style.display = 'none';
            editMode.style.display = 'block';
            editText.textContent = 'Hủy';
        }
    }
}
</script>