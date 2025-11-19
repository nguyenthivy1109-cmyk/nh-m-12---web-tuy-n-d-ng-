<?php
/**
 * Hàm thao tác với bảng kn_tu_dien (Từ điển kỹ năng)
 */

/**
 * Lấy tất cả kỹ năng
 */
function getAllKyNang($pdo) {
    $stmt = $pdo->query("SELECT * FROM kn_tu_dien ORDER BY ten_kn ASC");
    return $stmt->fetchAll();
}

/**
 * Lấy kỹ năng theo ID
 */
function getKyNangById($pdo, $kn_id) {
    $stmt = $pdo->prepare("SELECT * FROM kn_tu_dien WHERE kn_id = ?");
    $stmt->execute([$kn_id]);
    return $stmt->fetch();
}

/**
 * Lấy kỹ năng yêu cầu của tin tuyển dụng
 */
function getKyNangYeuCauCuaTin($pdo, $tin_id) {
    $stmt = $pdo->prepare("
        SELECT kn.kn_id, kn.ten_kn, kn.slug, tkn.muc_quan_trong
        FROM tin_kn tkn
        INNER JOIN kn_tu_dien kn ON tkn.kn_id = kn.kn_id
        WHERE tkn.tin_id = ?
        ORDER BY tkn.muc_quan_trong ASC, kn.ten_kn ASC
    ");
    $stmt->execute([$tin_id]);
    return $stmt->fetchAll();
}

/**
 * Thêm kỹ năng yêu cầu cho tin tuyển dụng
 */
function addKyNangYeuCauChoTin($pdo, $tin_id, $kn_id, $muc_quan_trong = 2) {
    $stmt = $pdo->prepare("
        INSERT INTO tin_kn (tin_id, kn_id, muc_quan_trong) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE muc_quan_trong = ?
    ");
    return $stmt->execute([$tin_id, $kn_id, $muc_quan_trong, $muc_quan_trong]);
}

/**
 * Xóa kỹ năng yêu cầu của tin
 */
function removeKyNangYeuCauCuaTin($pdo, $tin_id, $kn_id) {
    $stmt = $pdo->prepare("DELETE FROM tin_kn WHERE tin_id = ? AND kn_id = ?");
    return $stmt->execute([$tin_id, $kn_id]);
}

/**
 * Tìm kiếm kỹ năng theo tên
 */
function searchKyNang($pdo, $keyword) {
    $stmt = $pdo->prepare("
        SELECT * FROM kn_tu_dien 
        WHERE ten_kn LIKE ? 
        ORDER BY ten_kn ASC
        LIMIT 20
    ");
    $stmt->execute(['%' . $keyword . '%']);
    return $stmt->fetchAll();
}

?>

