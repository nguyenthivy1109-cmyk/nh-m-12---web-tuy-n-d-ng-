<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/nha_tuyen_dung.php';
require_once __DIR__ . '/../../models/cong_ty.php';

header('Content-Type: application/json');

try {
    requireRole(ROLE_RECRUITER);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
        exit;
    }

    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
        exit;
    }

    $nha_td = getNhaTuyenDungByTaiKhoanId($pdo, $user_id);
    if (!$nha_td) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin nhà tuyển dụng']);
        exit;
    }

    $ten_cong_ty = sanitize($_POST['ten_cong_ty'] ?? '');
    if ($ten_cong_ty === '') {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tên công ty']);
        exit;
    }

    $base_slug = createSlug($ten_cong_ty);
    if ($base_slug === '') {
        $base_slug = 'cong-ty-' . time();
    }

    $existing_company = null;
    if (!empty($nha_td['cong_ty_id'])) {
        $existing_company = getCongTyById($pdo, $nha_td['cong_ty_id']);
    }

    if ($existing_company) {
        $slug = $base_slug;
        if (isSlugExists($pdo, $slug, $existing_company['cong_ty_id'])) {
            $suffix = 2;
            while (isSlugExists($pdo, $base_slug . '-' . $suffix, $existing_company['cong_ty_id'])) {
                $suffix++;
            }
            $slug = $base_slug . '-' . $suffix;
        }

        $new_logo = trim((string)($_POST['logo_url'] ?? ''));
        $new_banner = trim((string)($_POST['bia_url'] ?? ''));

        $cong_ty_data = [
            'ten_cong_ty' => $ten_cong_ty,
            'slug' => $slug,
            'ma_so_thue' => sanitize($_POST['ma_so_thue'] ?? ''),
            'website' => sanitize($_POST['website'] ?? ''),
            'nganh_nghe' => sanitize($_POST['nganh_nghe'] ?? ''),
            'quy_mo' => sanitize($_POST['quy_mo'] ?? ''),
            'logo_url' => $new_logo !== '' ? sanitize($new_logo) : ($existing_company['logo_url'] ?? null),
            'bia_url' => $new_banner !== '' ? sanitize($new_banner) : ($existing_company['bia_url'] ?? null),
            'gioi_thieu' => $_POST['gioi_thieu'] ?? '',
            'dia_chi_tru_so' => sanitize($_POST['dia_chi_tru_so'] ?? '')
        ];

        if (!updateCongTy($pdo, $existing_company['cong_ty_id'], $cong_ty_data)) {
            echo json_encode(['success' => false, 'message' => 'Không thể cập nhật thông tin công ty']);
            exit;
        }

        $updated = getCongTyById($pdo, $existing_company['cong_ty_id']);
        echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin công ty thành công', 'data' => $updated]);
        exit;
    }

    // Tạo mới
    $slug = $base_slug;
    if (isSlugExists($pdo, $slug)) {
        $suffix = 2;
        while (isSlugExists($pdo, $base_slug . '-' . $suffix)) {
            $suffix++;
        }
        $slug = $base_slug . '-' . $suffix;
    }

    $new_logo = trim((string)($_POST['logo_url'] ?? ''));
    $new_banner = trim((string)($_POST['bia_url'] ?? ''));

    $cong_ty_data = [
        'ten_cong_ty' => $ten_cong_ty,
        'slug' => $slug,
        'ma_so_thue' => sanitize($_POST['ma_so_thue'] ?? ''),
        'website' => sanitize($_POST['website'] ?? ''),
        'nganh_nghe' => sanitize($_POST['nganh_nghe'] ?? ''),
        'quy_mo' => sanitize($_POST['quy_mo'] ?? ''),
        'logo_url' => $new_logo !== '' ? sanitize($new_logo) : null,
        'bia_url' => $new_banner !== '' ? sanitize($new_banner) : null,
        'gioi_thieu' => $_POST['gioi_thieu'] ?? '',
        'dia_chi_tru_so' => sanitize($_POST['dia_chi_tru_so'] ?? '')
    ];

    $cong_ty_id = createCongTy($pdo, $cong_ty_data);
    if (!$cong_ty_id) {
        echo json_encode(['success' => false, 'message' => 'Không thể tạo công ty mới']);
        exit;
    }

    updateCongTyForNhaTuyenDung($pdo, $user_id, $cong_ty_id);
    $updated = getCongTyById($pdo, $cong_ty_id);

    echo json_encode(['success' => true, 'message' => 'Tạo công ty thành công', 'data' => $updated]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
