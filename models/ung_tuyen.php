<?php
/**
 * Hàm thao tác với bảng ung_tuyens
 */

/**
 * Lấy thông tin ứng tuyển theo ID
 */
function getUngTuyenById($pdo, $ung_tuyen_id) {
    $stmt = $pdo->prepare("
        SELECT ut.*, 
               t.tieu_de, t.slug as tin_slug,
               uv.ho_ten, uv.ung_vien_id,
               ct.ten_cong_ty
        FROM ung_tuyens ut
        INNER JOIN tin_td t ON ut.tin_id = t.tin_id
        INNER JOIN ung_viens uv ON ut.ung_vien_id = uv.ung_vien_id
        INNER JOIN cong_tys ct ON t.cong_ty_id = ct.cong_ty_id
        WHERE ut.ung_tuyen_id = ?
    ");
    $stmt->execute([$ung_tuyen_id]);
    return $stmt->fetch();
}

/**
 * Lấy danh sách ứng tuyển của ứng viên
 */
function getUngTuyenCuaUngVien($pdo, $ung_vien_id) {
    $stmt = $pdo->prepare("
        SELECT ut.*, 
               t.tieu_de, t.slug as tin_slug, t.noi_lam_viec,
               ct.ten_cong_ty, ct.logo_url,
               ntd.ho_ten as ntd_ho_ten
        FROM ung_tuyens ut
        INNER JOIN tin_td t ON ut.tin_id = t.tin_id
        INNER JOIN cong_tys ct ON t.cong_ty_id = ct.cong_ty_id
        INNER JOIN nha_tuyen_dungs ntd ON t.nha_td_id = ntd.nha_td_id
        WHERE ut.ung_vien_id = ?
        ORDER BY ut.nop_luc DESC
    ");
    $stmt->execute([$ung_vien_id]);
    return $stmt->fetchAll();
}

/**
 * Lấy danh sách ứng tuyển của tin tuyển dụng
 */
function getUngTuyenCuaTin($pdo, $tin_id) {
    $stmt = $pdo->prepare("
        SELECT ut.*, 
               uv.ho_ten, uv.ung_vien_id, uv.tieu_de_cv,
               tk.email, tk.dien_thoai,
               dk.tep_url as cv_url, dk.ten_tep as cv_ten
        FROM ung_tuyens ut
        INNER JOIN ung_viens uv ON ut.ung_vien_id = uv.ung_vien_id
        INNER JOIN tai_khoans tk ON uv.tai_khoan_id = tk.tai_khoan_id
        LEFT JOIN dinh_kems dk ON ut.cv_id = dk.dk_id
        WHERE ut.tin_id = ?
        ORDER BY ut.nop_luc DESC
    ");
    $stmt->execute([$tin_id]);
    return $stmt->fetchAll();
}

/**
 * Tạo ứng tuyển mới
 */
function createUngTuyen($pdo, $data) {
    // Kiểm tra đã ứng tuyển chưa
    $stmt = $pdo->prepare("SELECT ung_tuyen_id FROM ung_tuyens WHERE tin_id = ? AND ung_vien_id = ?");
    $stmt->execute([$data['tin_id'], $data['ung_vien_id']]);
    if ($stmt->fetch()) {
        return ['error' => 'Bạn đã ứng tuyển vào vị trí này rồi.'];
    }

    $sql = "INSERT INTO ung_tuyens (tin_id, ung_vien_id, cv_id, thu_ung_tuyen, nguon) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $data['tin_id'],
        $data['ung_vien_id'],
        $data['cv_id'] ?? null,
        $data['thu_ung_tuyen'] ?? null,
        $data['nguon'] ?? 'Website'
    ]);
    
    $ung_tuyen_id = $pdo->lastInsertId();
    
    // Tạo thông báo cho nhà tuyển dụng
    $stmt = $pdo->prepare("
        SELECT ntd.tai_khoan_id, t.tieu_de, ct.ten_cong_ty
        FROM tin_td t
        INNER JOIN nha_tuyen_dungs ntd ON t.nha_td_id = ntd.nha_td_id
        INNER JOIN cong_tys ct ON t.cong_ty_id = ct.cong_ty_id
        WHERE t.tin_id = ?
    ");
    $stmt->execute([$data['tin_id']]);
    $tin_info = $stmt->fetch();
    
    if ($tin_info) {
        $stmt = $pdo->prepare("
            INSERT INTO thong_baos (tai_khoan_id, loai_tb, tieu_de, noi_dung)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $tin_info['tai_khoan_id'],
            NOTIFICATION_TYPE_NEW_APPLICATION,
            'Có ứng viên mới ứng tuyển',
            "Có ứng viên mới ứng tuyển vào vị trí: " . $tin_info['tieu_de'] . " tại " . $tin_info['ten_cong_ty']
        ]);
    }
    
    return ['success' => true, 'ung_tuyen_id' => $ung_tuyen_id];
}

/**
 * Cập nhật trạng thái ứng tuyển
 */
function updateTrangThaiUngTuyen($pdo, $ung_tuyen_id, $trang_thai_ut) {
    $stmt = $pdo->prepare("
        UPDATE ung_tuyens 
        SET trang_thai_ut = ?, cap_nhat_tt_luc = NOW() 
        WHERE ung_tuyen_id = ?
    ");
    return $stmt->execute([$trang_thai_ut, $ung_tuyen_id]);
}

/**
 * Kiểm tra ứng viên đã ứng tuyển vào tin chưa
 */
function hasUngTuyen($pdo, $tin_id, $ung_vien_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ung_tuyens WHERE tin_id = ? AND ung_vien_id = ?");
    $stmt->execute([$tin_id, $ung_vien_id]);
    return $stmt->fetchColumn() > 0;
}

?>

