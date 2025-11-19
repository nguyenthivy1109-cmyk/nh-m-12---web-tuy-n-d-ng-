<?php
/**
 * Hàm thao tác với bảng ung_viens
 */

/**
 * Lấy thông tin ứng viên theo ID
 */
function getUngVienById($pdo, $ung_vien_id) {
    $stmt = $pdo->prepare("SELECT * FROM ung_viens WHERE ung_vien_id = ?");
    $stmt->execute([$ung_vien_id]);
    return $stmt->fetch();
}

/**
 * Lấy thông tin ứng viên theo tài khoản ID
 */
function getUngVienByTaiKhoanId($pdo, $tai_khoan_id) {
    $stmt = $pdo->prepare("SELECT * FROM ung_viens WHERE tai_khoan_id = ?");
    $stmt->execute([$tai_khoan_id]);
    return $stmt->fetch();
}

/**
 * Tạo ứng viên mới
 */
function createUngVien($pdo, $data) {
    $sql = "INSERT INTO ung_viens (tai_khoan_id, ho_ten, ngay_sinh, gioi_tinh, noi_o, tieu_de_cv, gioi_thieu) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['tai_khoan_id'],
        $data['ho_ten'] ?? null,
        $data['ngay_sinh'] ?? null,
        $data['gioi_tinh'] ?? null,
        $data['noi_o'] ?? null,
        $data['tieu_de_cv'] ?? null,
        $data['gioi_thieu'] ?? null
    ]);
    return $pdo->lastInsertId();
}

/**
 * Cập nhật thông tin ứng viên
 */
function updateUngVien($pdo, $ung_vien_id, $data) {
    $sql = "UPDATE ung_viens SET 
            ho_ten = ?,
            ngay_sinh = ?,
            gioi_tinh = ?,
            noi_o = ?,
            tieu_de_cv = ?,
            gioi_thieu = ?,
            cap_nhat_luc = NOW()
            WHERE ung_vien_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['ho_ten'] ?? null,
        $data['ngay_sinh'] ?? null,
        $data['gioi_tinh'] ?? null,
        $data['noi_o'] ?? null,
        $data['tieu_de_cv'] ?? null,
        $data['gioi_thieu'] ?? null,
        $ung_vien_id
    ]);
}

/**
 * Lấy kỹ năng của ứng viên
 */
function getKyNangCuaUngVien($pdo, $ung_vien_id) {
    $stmt = $pdo->prepare("
        SELECT kn.kn_id, kn.ten_kn, kn.slug, uvkn.muc_do
        FROM ung_vien_kn uvkn
        INNER JOIN kn_tu_dien kn ON uvkn.kn_id = kn.kn_id
        WHERE uvkn.ung_vien_id = ?
        ORDER BY kn.ten_kn
    ");
    $stmt->execute([$ung_vien_id]);
    return $stmt->fetchAll();
}

/**
 * Thêm kỹ năng cho ứng viên
 */
function addKyNangChoUngVien($pdo, $ung_vien_id, $kn_id, $muc_do = 3) {
    $stmt = $pdo->prepare("
        INSERT INTO ung_vien_kn (ung_vien_id, kn_id, muc_do) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE muc_do = ?
    ");
    return $stmt->execute([$ung_vien_id, $kn_id, $muc_do, $muc_do]);
}

/**
 * Xóa kỹ năng của ứng viên
 */
function removeKyNangCuaUngVien($pdo, $ung_vien_id, $kn_id) {
    $stmt = $pdo->prepare("DELETE FROM ung_vien_kn WHERE ung_vien_id = ? AND kn_id = ?");
    return $stmt->execute([$ung_vien_id, $kn_id]);
}


?>

