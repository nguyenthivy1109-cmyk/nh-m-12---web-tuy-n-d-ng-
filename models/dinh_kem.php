<?php
/**
 * Hàm thao tác với bảng dinh_kems (File đính kèm)
 */

/**
 * Lấy thông tin file đính kèm theo ID
 */
function getDinhKemById($pdo, $dk_id) {
    $stmt = $pdo->prepare("SELECT * FROM dinh_kems WHERE dk_id = ?");
    $stmt->execute([$dk_id]);
    return $stmt->fetch();
}

/**
 * Lấy danh sách file đính kèm của tài khoản
 */
function getDinhKemCuaTaiKhoan($pdo, $tai_khoan_id, $doi_tuong = null) {
    $sql = "SELECT * FROM dinh_kems WHERE chu_so_huu_tai_khoan_id = ?";
    $params = [$tai_khoan_id];
    
    if ($doi_tuong) {
        $sql .= " AND doi_tuong = ?";
        $params[] = $doi_tuong;
    }
    
    $sql .= " ORDER BY tao_luc DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Tạo file đính kèm mới
 */
function createDinhKem($pdo, $data) {
    $sql = "INSERT INTO dinh_kems (chu_so_huu_tai_khoan_id, tep_url, ten_tep, mime_type, doi_tuong, doi_tuong_id) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['chu_so_huu_tai_khoan_id'],
        $data['tep_url'],
        $data['ten_tep'] ?? null,
        $data['mime_type'] ?? null,
        $data['doi_tuong'],
        $data['doi_tuong_id']
    ]);
    return $pdo->lastInsertId();
}

/**
 * Xóa file đính kèm
 */
function deleteDinhKem($pdo, $dk_id) {
    // Lấy thông tin file trước khi xóa
    $file = getDinhKemById($pdo, $dk_id);
    
    $stmt = $pdo->prepare("DELETE FROM dinh_kems WHERE dk_id = ?");
    $result = $stmt->execute([$dk_id]);
    
    // Xóa file vật lý nếu tồn tại
    if ($result && $file && file_exists(__DIR__ . '/../' . $file['tep_url'])) {
        @unlink(__DIR__ . '/../' . $file['tep_url']);
    }
    
    return $result;
}

/**
 * Lấy CV của ứng viên
 */
function getCVCuaUngVien($pdo, $tai_khoan_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM dinh_kems 
        WHERE chu_so_huu_tai_khoan_id = ? AND doi_tuong = 'CV'
        ORDER BY tao_luc DESC
    ");
    $stmt->execute([$tai_khoan_id]);
    return $stmt->fetchAll();
}

?>

