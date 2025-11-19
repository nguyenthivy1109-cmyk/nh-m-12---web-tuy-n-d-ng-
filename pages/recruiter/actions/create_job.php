<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/nha_tuyen_dung.php';
require_once __DIR__ . '/../../models/tai_khoan.php';
require_once __DIR__ . '/../../models/tin_td.php';

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
    if (empty($nha_td['cong_ty_id'])) {
        echo json_encode(['success' => false, 'message' => 'Bạn chưa được gán vào công ty nào']);
        exit;
    }

    $payload = [
        'cong_ty_id'   => (int)$nha_td['cong_ty_id'],
        'nha_td_id'    => (int)$nha_td['nha_td_id'],
        'tieu_de'      => $_POST['tieu_de'] ?? '',
        'mo_ta'        => $_POST['mo_ta'] ?? '',
        'yeu_cau'      => $_POST['yeu_cau'] ?? '',
        'noi_lam_viec' => $_POST['dia_diem'] ?? '',
        'hinh_thuc_lv' => $_POST['hinh_thuc_lv'] ?? null,
        'che_do_lv'    => $_POST['che_do_lv'] ?? null,
        'cap_do_kn'    => $_POST['cap_do_kn'] ?? null,
        'so_luong'     => $_POST['so_luong'] ?? 1,
        'luong_min'    => $_POST['luong_min'] ?? null,
        'luong_max'    => $_POST['luong_max'] ?? null,
        'tien_te'      => $_POST['tien_te'] ?? 'VND',
        'het_han_luc'  => $_POST['han_nop'] ?? null,
        'publish'      => isset($_POST['publish']) ? 1 : 0,
    ];

    $result = createTinTuyenDung($pdo, $payload);
    if (isset($result['error'])) {
        echo json_encode(['success' => false, 'message' => $result['error']]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Đăng tin thành công. Tin của bạn đang chờ admin duyệt.',
        'tin_id'  => $result['tin_id'] ?? null,
        'slug'    => $result['slug'] ?? null,
    ]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
