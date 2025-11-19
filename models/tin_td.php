<?php
/**
 * Hàm thao tác với bảng tin_td (Tin tuyển dụng)
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

/**
 * Kiểm tra slug đã tồn tại chưa (loại trừ 1 id khi update)
 */
function isTinSlugExists($pdo, $slug, $exclude_id = null) {
    $sql = "SELECT COUNT(*) FROM tin_td WHERE slug = ?";
    $params = [$slug];
    if ($exclude_id) {
        $sql .= " AND tin_id != ?";
        $params[] = $exclude_id;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() > 0;
}

/**
 * Tạo slug unique: nếu đã tồn tại thì thêm hậu tố -2, -3...
 */
function makeUniqueTinSlug($pdo, $baseSlug) {
    $slug = $baseSlug;
    $i = 2;
    while (isTinSlugExists($pdo, $slug)) {
        $slug = $baseSlug . '-' . $i;
        $i++;
    }
    return $slug;
}

/**
 * Parse lương từ chuỗi "10-15 triệu" (tùy chọn).
 * Nếu bạn tách ô nhập luong_min/max thì KHÔNG cần hàm này.
 */
function parseSalaryRange($text) {
    if (!$text) return [null, null, 'VND'];
    $text = strtolower($text);
    $currency = 'VND';

    // tách theo dấu "-" hoặc "–"
    $parts = preg_split('/[-–]/', $text);
    $clean = function($s) {
        // lấy số (có thể chứa . ,), bỏ chữ
        $s = preg_replace('/[^0-9.,]/', '', $s);
        $s = str_replace(['.', ','], '', $s);
        return $s ? (float)$s : null;
    };

    $min = isset($parts[0]) ? $clean($parts[0]) : null;
    $max = isset($parts[1]) ? $clean($parts[1]) : null;

    // nếu ghi "triệu" thì coi là * 1,000,000 VND
    if (strpos($text, 'triệu') !== false) {
        if ($min !== null) $min *= 1000000;
        if ($max !== null) $max *= 1000000;
    }

    // đơn giản: nếu có "usd" trong chuỗi thì set USD
    if (strpos($text, 'usd') !== false || strpos($text, '$') !== false) {
        $currency = 'USD';
    }

    return [$min, $max, $currency];
}

/**
 * Tạo tin tuyển dụng mới
 * $data bao gồm các key khuyến nghị:
 * - cong_ty_id, nha_td_id (bắt buộc)
 * - tieu_de (bắt buộc), mo_ta (bắt buộc), yeu_cau (optional), noi_lam_viec
 * - hinh_thuc_lv, che_do_lv, cap_do_kn, so_luong, luong_min, luong_max, tien_te
 * - het_han_luc (YYYY-MM-DD), publish (0/1)
 * - (tùy chọn) muc_luong: "10-15 triệu" -> sẽ parse ra min/max nếu min/max chưa có
 */
function createTinTuyenDung($pdo, $data) {
    // 1) Lấy các giá trị an toàn, mặc định
    $cong_ty_id   = isset($data['cong_ty_id']) ? (int)$data['cong_ty_id'] : null;
    $nha_td_id    = isset($data['nha_td_id']) ? (int)$data['nha_td_id'] : null;
    $tieu_de      = trim($data['tieu_de'] ?? '');
    $mo_ta        = trim($data['mo_ta'] ?? '');
    $yeu_cau      = trim($data['yeu_cau'] ?? null);
    $noi_lam_viec = trim($data['noi_lam_viec'] ?? null);

    $hinh_thuc_lv = isset($data['hinh_thuc_lv']) ? (int)$data['hinh_thuc_lv'] : null;
    $che_do_lv    = isset($data['che_do_lv']) ? (int)$data['che_do_lv'] : null;
    $cap_do_kn    = isset($data['cap_do_kn']) ? (int)$data['cap_do_kn'] : null;

    $so_luong     = isset($data['so_luong']) ? max(1, (int)$data['so_luong']) : 1;

    // Lương: ưu tiên luong_min/max nếu có, ngược lại parse từ muc_luong
    $luong_min = isset($data['luong_min']) && $data['luong_min'] !== '' ? (float)$data['luong_min'] : null;
    $luong_max = isset($data['luong_max']) && $data['luong_max'] !== '' ? (float)$data['luong_max'] : null;
    $tien_te   = strtoupper(trim($data['tien_te'] ?? 'VND'));

    if ($luong_min === null && $luong_max === null && !empty($data['muc_luong'])) {
        list($luong_min, $luong_max, $tien_te_auto) = parseSalaryRange($data['muc_luong']);
        if (empty($data['tien_te']) && $tien_te_auto) {
            $tien_te = $tien_te_auto;
        }
    }

    // Hạn nộp -> het_han_luc (giữ định dạng Y-m-d)
    $het_han_luc = !empty($data['het_han_luc']) ? $data['het_han_luc'] : (!empty($data['han_nop']) ? $data['han_nop'] : null);

    // Trạng thái đăng - Luôn đặt trạng thái "Chờ duyệt" (0) khi tạo mới
    // Admin sẽ duyệt tin sau đó để chuyển sang trạng thái "Đã duyệt" (1)
    $publish = isset($data['publish']) ? (int)$data['publish'] : 0; // Giữ lại để tương thích, nhưng không dùng
    $trang_thai_tin = 0; // Luôn là "Chờ duyệt" khi tạo mới
    $dang_luc = null; // Chỉ set khi admin duyệt

    // 2) Kiểm tra thông tin bắt buộc
    if (!$cong_ty_id || !$nha_td_id) {
        return ['error' => 'Thiếu cong_ty_id hoặc nha_td_id.'];
    }
    if ($tieu_de === '' || $mo_ta === '') {
        return ['error' => 'Thiếu tiêu đề hoặc mô tả công việc.'];
    }
    // Check lương hợp lệ theo CHECK constraint (min <= max)
    if ($luong_min !== null && $luong_max !== null && $luong_min > $luong_max) {
        return ['error' => 'Lương tối thiểu không được lớn hơn lương tối đa.'];
    }

    // 3) Tạo slug unique từ tiêu đề
    $baseSlug = createSlug($tieu_de);
    $slug = makeUniqueTinSlug($pdo, $baseSlug);

    // 4) Thực hiện INSERT
    $sql = "INSERT INTO tin_td (
                cong_ty_id, nha_td_id, tieu_de, slug, mo_ta, yeu_cau, noi_lam_viec,
                hinh_thuc_lv, che_do_lv, luong_min, luong_max, tien_te,
                cap_do_kn, so_luong, trang_thai_tin, dang_luc, het_han_luc, tao_luc
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, NOW()
            )";
    $stmt = $pdo->prepare($sql);

    $ok = $stmt->execute([
        $cong_ty_id, $nha_td_id, $tieu_de, $slug, $mo_ta, $yeu_cau, $noi_lam_viec,
        $hinh_thuc_lv, $che_do_lv, $luong_min, $luong_max, $tien_te,
        $cap_do_kn, $so_luong, $trang_thai_tin, $dang_luc, $het_han_luc
    ]);

    if (!$ok) {
        return ['error' => 'Không thể lưu tin tuyển dụng.'];
    }
    return [
        'tin_id' => $pdo->lastInsertId(),
        'slug'   => $slug,
        'status' => 'success'
    ];
}

/**
 * Lấy tin theo ID
 */
function getTinById($pdo, $tin_id) {
    $stmt = $pdo->prepare("SELECT * FROM tin_td WHERE tin_id = ? AND xoa_luc IS NULL");
    $stmt->execute([(int)$tin_id]);
    return $stmt->fetch();
}

/**
 * Cập nhật tin (đơn giản)
 */
function updateTinTuyenDung($pdo, $tin_id, $data) {
    $sql = "UPDATE tin_td SET 
                tieu_de = ?, mo_ta = ?, yeu_cau = ?, noi_lam_viec = ?, 
                luong_min = ?, luong_max = ?, tien_te = ?, 
                cap_nhat_luc = NOW()
            WHERE tin_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        trim($data['tieu_de'] ?? ''),
        trim($data['mo_ta'] ?? ''),
        trim($data['yeu_cau'] ?? ''),
        trim($data['noi_lam_viec'] ?? ''),
        isset($data['luong_min']) ? (float)$data['luong_min'] : null,
        isset($data['luong_max']) ? (float)$data['luong_max'] : null,
        strtoupper(trim($data['tien_te'] ?? 'VND')),
        (int)$tin_id
    ]);
}

/**
 * Soft delete
 */
function deleteTin($pdo, $tin_id) {
    $stmt = $pdo->prepare("UPDATE tin_td SET xoa_luc = NOW() WHERE tin_id = ?");
    return $stmt->execute([(int)$tin_id]);
}

/**
 * Lấy thống kê tin tuyển dụng theo nhà tuyển dụng
 */
function getTinTuyenDungStats($pdo, $nha_td_id) {
    $sql = "
        SELECT
            COALESCE(SUM(CASE WHEN xoa_luc IS NULL THEN 1 ELSE 0 END), 0) AS total,
            COALESCE(SUM(CASE WHEN xoa_luc IS NULL AND trang_thai_tin = 1 AND (het_han_luc IS NULL OR het_han_luc >= CURDATE()) THEN 1 ELSE 0 END), 0) AS active,
            COALESCE(SUM(CASE WHEN xoa_luc IS NULL AND trang_thai_tin = 0 THEN 1 ELSE 0 END), 0) AS draft,
            COALESCE(SUM(CASE WHEN xoa_luc IS NULL AND trang_thai_tin = 1 AND het_han_luc IS NOT NULL AND het_han_luc < CURDATE() THEN 1 ELSE 0 END), 0) AS expired
        FROM tin_td
        WHERE nha_td_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([(int)$nha_td_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return ['total' => 0, 'active' => 0, 'draft' => 0, 'expired' => 0];
    }
    return [
        'total' => (int)($row['total'] ?? 0),
        'active' => (int)($row['active'] ?? 0),
        'draft' => (int)($row['draft'] ?? 0),
        'expired' => (int)($row['expired'] ?? 0),
    ];
}

/**
 * Lấy danh sách tin gần đây của nhà tuyển dụng
 */
function getRecentTinByNhaTD($pdo, $nha_td_id, $limit = 10) {
    $limit = (int)$limit;
    if ($limit <= 0) {
        $limit = 10;
    }
    $sql = "
        SELECT tin_id, tieu_de, trang_thai_tin, dang_luc, het_han_luc, tao_luc, luong_min, luong_max, tien_te
        FROM tin_td
        WHERE nha_td_id = ? AND xoa_luc IS NULL
        ORDER BY tao_luc DESC
        LIMIT ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([(int)$nha_td_id, $limit]);
    return $stmt->fetchAll();
}

/**
 * Lấy danh sách tin theo nhà tuyển dụng (có join tên công ty & số lượng ứng tuyển)
 */
function getTinTuyenDungByNhaTD($pdo, $nha_td_id, $cong_ty_id = null) {
    $sql = "
        SELECT 
            t.tin_id, t.tieu_de, t.slug, t.mo_ta, t.yeu_cau, t.noi_lam_viec,
            t.hinh_thuc_lv, t.che_do_lv, t.cap_do_kn, t.so_luong,
            t.luong_min, t.luong_max, t.tien_te,
            t.trang_thai_tin, t.dang_luc, t.het_han_luc, t.tao_luc,
            c.ten_cong_ty,
            COALESCE(u.cnt, 0) AS so_ung_tuyen
        FROM tin_td t
        LEFT JOIN cong_tys c ON c.cong_ty_id = t.cong_ty_id
        LEFT JOIN (
            SELECT tin_id, COUNT(*) AS cnt
            FROM ung_tuyens
            GROUP BY tin_id
        ) u ON u.tin_id = t.tin_id
        WHERE t.xoa_luc IS NULL AND t.nha_td_id = ?
    ";

    $params = [(int)$nha_td_id];
    if ($cong_ty_id !== null) {
        $sql .= " AND t.cong_ty_id = ?";
        $params[] = (int)$cong_ty_id;
    }

    $sql .= " ORDER BY COALESCE(t.dang_luc, t.tao_luc) DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Cập nhật trạng thái tin (0: nháp, 1: đang tuyển)
 */
function updateTrangThaiTin($pdo, $tin_id, $nha_td_id, $trang_thai_tin) {
    $sql = "UPDATE tin_td SET trang_thai_tin = ?, dang_luc = CASE WHEN ? = 1 THEN NOW() ELSE dang_luc END WHERE tin_id = ? AND nha_td_id = ?";
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([(int)$trang_thai_tin, (int)$trang_thai_tin, (int)$tin_id, (int)$nha_td_id]);
    if (!$ok) {
        return false;
    }
    return $stmt->rowCount() > 0;
}

/**
 * Lấy danh sách tin tuyển dụng đang active (cho candidate/public)
 */
function getTinTuyenDungActive($pdo, $filters = [], $limit = 20, $offset = 0) {
    $sql = "
        SELECT 
            t.tin_id, t.tieu_de, t.slug, t.mo_ta, t.noi_lam_viec,
            t.hinh_thuc_lv, t.che_do_lv, t.cap_do_kn,
            t.luong_min, t.luong_max, t.tien_te,
            t.dang_luc, t.het_han_luc,
            ct.cong_ty_id, ct.ten_cong_ty, ct.logo_url,
            ntd.ho_ten as ntd_ho_ten
        FROM tin_td t
        INNER JOIN cong_tys ct ON t.cong_ty_id = ct.cong_ty_id
        INNER JOIN nha_tuyen_dungs ntd ON t.nha_td_id = ntd.nha_td_id
        WHERE t.xoa_luc IS NULL 
        AND t.trang_thai_tin = 1
        AND (t.het_han_luc IS NULL OR t.het_han_luc >= CURDATE())
    ";
    
    $params = [];
    
    // Lọc theo từ khóa
    if (!empty($filters['keyword'])) {
        $sql .= " AND (t.tieu_de LIKE ? OR t.mo_ta LIKE ? OR ct.ten_cong_ty LIKE ?)";
        $keyword = '%' . $filters['keyword'] . '%';
        $params[] = $keyword;
        $params[] = $keyword;
        $params[] = $keyword;
    }
    
    // Lọc theo địa điểm
    if (!empty($filters['location'])) {
        $sql .= " AND t.noi_lam_viec LIKE ?";
        $params[] = '%' . $filters['location'] . '%';
    }
    
    // Lọc theo hình thức làm việc
    if (!empty($filters['work_type'])) {
        $sql .= " AND t.hinh_thuc_lv = ?";
        $params[] = (int)$filters['work_type'];
    }
    
    // Lọc theo cấp độ kinh nghiệm
    if (!empty($filters['experience'])) {
        $sql .= " AND t.cap_do_kn = ?";
        $params[] = (int)$filters['experience'];
    }
    
    // Lọc theo mức lương
    if (!empty($filters['salary_max']) && empty($filters['salary_min'])) {
        // Dưới X triệu: lấy tin có lương tối đa <= X triệu
        $sql .= " AND (t.luong_max <= ? OR (t.luong_min IS NULL AND t.luong_max IS NULL))";
        $params[] = (float)$filters['salary_max'];
    } elseif (!empty($filters['salary_min'])) {
        if (!empty($filters['salary_max'])) {
            // Khoảng lương: lấy tin có lương trong khoảng
            // Tin có luong_min hoặc luong_max nằm trong khoảng [salary_min, salary_max]
            $sql .= " AND (t.luong_min BETWEEN ? AND ? OR t.luong_max BETWEEN ? AND ? OR (t.luong_min <= ? AND t.luong_max >= ?))";
            $params[] = (float)$filters['salary_min'];
            $params[] = (float)$filters['salary_max'];
            $params[] = (float)$filters['salary_min'];
            $params[] = (float)$filters['salary_max'];
            $params[] = (float)$filters['salary_min'];
            $params[] = (float)$filters['salary_max'];
        } else {
            // Trên X triệu: lấy tin có lương tối thiểu >= X triệu
            $sql .= " AND t.luong_min >= ?";
            $params[] = (float)$filters['salary_min'];
        }
    }
    
    $sql .= " ORDER BY COALESCE(t.dang_luc, t.tao_luc) DESC LIMIT ? OFFSET ?";
    $params[] = (int)$limit;
    $params[] = (int)$offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Đếm số lượng tin tuyển dụng active (cho phân trang)
 */
function countTinTuyenDungActive($pdo, $filters = []) {
    $sql = "
        SELECT COUNT(*)
        FROM tin_td t
        INNER JOIN cong_tys ct ON t.cong_ty_id = ct.cong_ty_id
        WHERE t.xoa_luc IS NULL 
        AND t.trang_thai_tin = 1
        AND (t.het_han_luc IS NULL OR t.het_han_luc >= CURDATE())
    ";
    
    $params = [];
    
    if (!empty($filters['keyword'])) {
        $sql .= " AND (t.tieu_de LIKE ? OR t.mo_ta LIKE ? OR ct.ten_cong_ty LIKE ?)";
        $keyword = '%' . $filters['keyword'] . '%';
        $params[] = $keyword;
        $params[] = $keyword;
        $params[] = $keyword;
    }
    
    if (!empty($filters['location'])) {
        $sql .= " AND t.noi_lam_viec LIKE ?";
        $params[] = '%' . $filters['location'] . '%';
    }
    
    if (!empty($filters['work_type'])) {
        $sql .= " AND t.hinh_thuc_lv = ?";
        $params[] = (int)$filters['work_type'];
    }
    
    if (!empty($filters['experience'])) {
        $sql .= " AND t.cap_do_kn = ?";
        $params[] = (int)$filters['experience'];
    }
    
    if (!empty($filters['salary_max']) && empty($filters['salary_min'])) {
        // Dưới X triệu: lấy tin có lương tối đa <= X triệu
        $sql .= " AND (t.luong_max <= ? OR (t.luong_min IS NULL AND t.luong_max IS NULL))";
        $params[] = (float)$filters['salary_max'];
    } elseif (!empty($filters['salary_min'])) {
        if (!empty($filters['salary_max'])) {
            // Khoảng lương: lấy tin có lương trong khoảng
            $sql .= " AND (t.luong_min BETWEEN ? AND ? OR t.luong_max BETWEEN ? AND ? OR (t.luong_min <= ? AND t.luong_max >= ?))";
            $params[] = (float)$filters['salary_min'];
            $params[] = (float)$filters['salary_max'];
            $params[] = (float)$filters['salary_min'];
            $params[] = (float)$filters['salary_max'];
            $params[] = (float)$filters['salary_min'];
            $params[] = (float)$filters['salary_max'];
        } else {
            // Trên X triệu: lấy tin có lương tối thiểu >= X triệu
            $sql .= " AND t.luong_min >= ?";
            $params[] = (float)$filters['salary_min'];
        }
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

/**
 * Lấy chi tiết tin tuyển dụng (với đầy đủ thông tin)
 */
function getTinTuyenDungDetail($pdo, $tin_id) {
    $stmt = $pdo->prepare("
        SELECT 
            t.*,
            ct.ten_cong_ty, ct.slug as cong_ty_slug, ct.logo_url, ct.bia_url, 
            ct.gioi_thieu as cong_ty_gioi_thieu, ct.website, ct.dia_chi_tru_so,
            ntd.ho_ten as ntd_ho_ten, ntd.email_cong_viec
        FROM tin_td t
        INNER JOIN cong_tys ct ON t.cong_ty_id = ct.cong_ty_id
        INNER JOIN nha_tuyen_dungs ntd ON t.nha_td_id = ntd.nha_td_id
        WHERE t.tin_id = ? AND t.xoa_luc IS NULL
    ");
    $stmt->execute([$tin_id]);
    return $stmt->fetch();
}