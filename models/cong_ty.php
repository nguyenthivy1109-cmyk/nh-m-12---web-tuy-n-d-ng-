<?php
/**
 * Hàm thao tác với bảng cong_tys
 */

/**
 * Lấy thông tin công ty theo ID
 */
function getCongTyById($pdo, $cong_ty_id) {
    $stmt = $pdo->prepare("SELECT * FROM cong_tys WHERE cong_ty_id = ? AND xoa_luc IS NULL");
    $stmt->execute([$cong_ty_id]);
    return $stmt->fetch();
}

/**
 * Lấy thông tin công ty theo slug
 */
function getCongTyBySlug($pdo, $slug) {
    $stmt = $pdo->prepare("SELECT * FROM cong_tys WHERE slug = ? AND xoa_luc IS NULL");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

/**
 * Lấy tất cả công ty (có phân trang)
 */
function getAllCongTy($pdo, $limit = 20, $offset = 0) {
    $stmt = $pdo->prepare("SELECT * FROM cong_tys WHERE xoa_luc IS NULL ORDER BY tao_luc DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    return $stmt->fetchAll();
}

/**
 * Tạo công ty mới
 */
function createCongTy($pdo, $data) {
    $sql = "INSERT INTO cong_tys (ten_cong_ty, slug, ma_so_thue, website, nganh_nghe, quy_mo, logo_url, bia_url, gioi_thieu, dia_chi_tru_so) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['ten_cong_ty'],
        $data['slug'] ?? null,
        $data['ma_so_thue'] ?? null,
        $data['website'] ?? null,
        $data['nganh_nghe'] ?? null,
        $data['quy_mo'] ?? null,
        $data['logo_url'] ?? null,
        $data['bia_url'] ?? null,
        $data['gioi_thieu'] ?? null,
        $data['dia_chi_tru_so'] ?? null
    ]);
    return $pdo->lastInsertId();
}

/**
 * Cập nhật thông tin công ty
 */
function updateCongTy($pdo, $cong_ty_id, $data) {
    $sql = "UPDATE cong_tys SET 
            ten_cong_ty = ?,
            slug = ?,
            ma_so_thue = ?,
            website = ?,
            nganh_nghe = ?,
            quy_mo = ?,
            logo_url = ?,
            bia_url = ?,
            gioi_thieu = ?,
            dia_chi_tru_so = ?,
            cap_nhat_luc = NOW()
            WHERE cong_ty_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['ten_cong_ty'],
        $data['slug'] ?? null,
        $data['ma_so_thue'] ?? null,
        $data['website'] ?? null,
        $data['nganh_nghe'] ?? null,
        $data['quy_mo'] ?? null,
        $data['logo_url'] ?? null,
        $data['bia_url'] ?? null,
        $data['gioi_thieu'] ?? null,
        $data['dia_chi_tru_so'] ?? null,
        $cong_ty_id
    ]);
}

/**
 * Xóa công ty (soft delete)
 */
function deleteCongTy($pdo, $cong_ty_id) {
    $sql = "UPDATE cong_tys SET xoa_luc = NOW() WHERE cong_ty_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$cong_ty_id]);
}

/**
 * Kiểm tra slug đã tồn tại chưa
 */
function isSlugExists($pdo, $slug, $exclude_id = null) {
    $sql = "SELECT COUNT(*) FROM cong_tys WHERE slug = ? AND xoa_luc IS NULL";
    $params = [$slug];
    
    if ($exclude_id) {
        $sql .= " AND cong_ty_id != ?";
        $params[] = $exclude_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() > 0;
}

?>

