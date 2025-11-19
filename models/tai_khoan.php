<?php
/**
 * Hàm thao tác với bảng tai_khoans
 */

/**
 * Lấy thông tin tài khoản theo ID
 */
function getTaiKhoanById($pdo, $tai_khoan_id) {
    $stmt = $pdo->prepare("SELECT * FROM tai_khoans WHERE tai_khoan_id = ? AND xoa_luc IS NULL");
    $stmt->execute([$tai_khoan_id]);
    return $stmt->fetch();
}

/**
 * Lấy thông tin tài khoản theo tên đăng nhập
 */
function getTaiKhoanByTenDN($pdo, $ten_dn) {
    $stmt = $pdo->prepare("SELECT * FROM tai_khoans WHERE ten_dn = ? AND xoa_luc IS NULL");
    $stmt->execute([$ten_dn]);
    return $stmt->fetch();
}

/**
 * Lấy thông tin tài khoản theo email
 */
function getTaiKhoanByEmail($pdo, $email) {
    $stmt = $pdo->prepare("SELECT * FROM tai_khoans WHERE email = ? AND xoa_luc IS NULL");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

/**
 * Lấy thông tin tài khoản theo tên đăng nhập hoặc email
 */
function getTaiKhoanByTenDNHoacEmail($pdo, $username_or_email) {
    $stmt = $pdo->prepare("SELECT * FROM tai_khoans WHERE (ten_dn = ? OR email = ?) AND xoa_luc IS NULL");
    $stmt->execute([$username_or_email, $username_or_email]);
    return $stmt->fetch();
}

/**
 * Tạo tài khoản mới
 */
function createTaiKhoan($pdo, $data) {
    $sql = "INSERT INTO tai_khoans (ten_dn, mat_khau_hash, email, dien_thoai, vai_tro_id, kich_hoat) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['ten_dn'],
        $data['mat_khau_hash'],
        $data['email'],
        $data['dien_thoai'] ?? null,
        $data['vai_tro_id'],
        $data['kich_hoat'] ?? 1
    ]);
    return $pdo->lastInsertId();
}

/**
 * Cập nhật thông tin tài khoản
 */
function updateTaiKhoan($pdo, $tai_khoan_id, $data) {
    $sql = "UPDATE tai_khoans SET 
            email = ?, 
            dien_thoai = ?, 
            kich_hoat = ?,
            cap_nhat_luc = NOW()
            WHERE tai_khoan_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['email'],
        $data['dien_thoai'] ?? null,
        $data['kich_hoat'] ?? 1,
        $tai_khoan_id
    ]);
}

/**
 * Cập nhật mật khẩu
 */
function updatePassword($pdo, $tai_khoan_id, $new_password_hash) {
    $sql = "UPDATE tai_khoans SET mat_khau_hash = ?, cap_nhat_luc = NOW() WHERE tai_khoan_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$new_password_hash, $tai_khoan_id]);
}

/**
 * Xóa tài khoản (soft delete)
 */
function deleteTaiKhoan($pdo, $tai_khoan_id) {
    $sql = "UPDATE tai_khoans SET xoa_luc = NOW() WHERE tai_khoan_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$tai_khoan_id]);
}

/**
 * Cập nhật thời gian đăng nhập cuối
 */
function updateLastLogin($pdo, $tai_khoan_id) {
    $sql = "UPDATE tai_khoans SET dang_nhap_cuoi_luc = NOW() WHERE tai_khoan_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$tai_khoan_id]);
}

/**
 * Kiểm tra tên đăng nhập đã tồn tại chưa
 */
function isTenDNExists($pdo, $ten_dn, $exclude_id = null) {
    $sql = "SELECT COUNT(*) FROM tai_khoans WHERE ten_dn = ? AND xoa_luc IS NULL";
    $params = [$ten_dn];
    
    if ($exclude_id) {
        $sql .= " AND tai_khoan_id != ?";
        $params[] = $exclude_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() > 0;
}

/**
 * Kiểm tra email đã tồn tại chưa
 */
function isEmailExists($pdo, $email, $exclude_id = null) {
    $sql = "SELECT COUNT(*) FROM tai_khoans WHERE email = ? AND xoa_luc IS NULL";
    $params = [$email];
    
    if ($exclude_id) {
        $sql .= " AND tai_khoan_id != ?";
        $params[] = $exclude_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() > 0;
}

?>

