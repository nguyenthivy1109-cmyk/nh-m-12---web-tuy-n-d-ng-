<?php
/**
 * Hàm thao tác với bảng nha_tuyen_dungs
 */

/**
 * Lấy thông tin nhà tuyển dụng theo ID
 */
function getNhaTuyenDungById($pdo, $nha_td_id) {
    $stmt = $pdo->prepare("SELECT * FROM nha_tuyen_dungs WHERE nha_td_id = ?");
    $stmt->execute([$nha_td_id]);
    return $stmt->fetch();
}

/**
 * Lấy thông tin nhà tuyển dụng theo tài khoản ID
 */
function getNhaTuyenDungByTaiKhoanId($pdo, $tai_khoan_id) {
    $stmt = $pdo->prepare("SELECT * FROM nha_tuyen_dungs WHERE tai_khoan_id = ?");
    $stmt->execute([$tai_khoan_id]);
    return $stmt->fetch();
}

/**
 * Tạo nhà tuyển dụng mới
 */
function createNhaTuyenDung($pdo, $data) {
    $sql = "INSERT INTO nha_tuyen_dungs (tai_khoan_id, cong_ty_id, ho_ten, chuc_danh, email_cong_viec) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['tai_khoan_id'],
        $data['cong_ty_id'] ?? null,
        $data['ho_ten'] ?? null,
        $data['chuc_danh'] ?? null,
        $data['email_cong_viec'] ?? null
    ]);
    return $pdo->lastInsertId();
}

/**
 * Cập nhật thông tin nhà tuyển dụng
 */
function updateNhaTuyenDung($pdo, $nha_td_id, $data) {
    $sql = "UPDATE nha_tuyen_dungs SET 
            cong_ty_id = ?,
            ho_ten = ?,
            chuc_danh = ?,
            email_cong_viec = ?,
            cap_nhat_luc = NOW()
            WHERE nha_td_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['cong_ty_id'] ?? null,
        $data['ho_ten'] ?? null,
        $data['chuc_danh'] ?? null,
        $data['email_cong_viec'] ?? null,
        $nha_td_id
    ]);
}

/**
 * Cập nhật công ty cho nhà tuyển dụng
 */
function updateCongTyForNhaTuyenDung($pdo, $tai_khoan_id, $cong_ty_id) {
    $sql = "UPDATE nha_tuyen_dungs SET cong_ty_id = ?, cap_nhat_luc = NOW() WHERE tai_khoan_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$cong_ty_id, $tai_khoan_id]);
}

/**
 * Lấy thông tin công ty của nhà tuyển dụng
 */
function getCongTyOfNhaTuyenDung($pdo, $tai_khoan_id) {
    $stmt = $pdo->prepare("
        SELECT ct.* 
        FROM cong_tys ct
        INNER JOIN nha_tuyen_dungs ntd ON ct.cong_ty_id = ntd.cong_ty_id
        WHERE ntd.tai_khoan_id = ? AND ct.xoa_luc IS NULL
    ");
    $stmt->execute([$tai_khoan_id]);
    return $stmt->fetch();
}

?>

