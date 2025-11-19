<?php
/**
 * File chứa các hàm tiện ích chung
 */

/**
 * Hash mật khẩu
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_ALGO, ['cost' => PASSWORD_COST]);
}

/**
 * Verify mật khẩu
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Tạo slug từ chuỗi
 */
function createSlug($string) {
    $str = (string)$string;
    // Lowercase (fallback nếu không có mbstring)
    if (function_exists('mb_strtolower')) {
        $str = mb_strtolower($str, 'UTF-8');
    } else {
        $str = strtolower($str);
    }
    // Chuẩn hóa: bỏ dấu/ký tự có dấu nếu có iconv
    if (function_exists('iconv')) {
        $trans = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
        if ($trans !== false) {
            $str = $trans;
        }
    }
    // Loại bỏ ký tự không phải a-z, 0-9, khoảng trắng, hoặc '-'
    $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
    // Thay khoảng trắng/liên tiếp bằng một '-'
    $str = preg_replace('/[\s-]+/', '-', $str);
    // Cắt '-' ở đầu/cuối
    $str = trim($str, '-');
    return $str;
}

/**
 * Format ngày tháng
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    $dateObj = new DateTime($date);
    return $dateObj->format($format);
}

/**
 * Format tiền tệ
 */
function formatCurrency($amount, $currency = 'VND') {
    if ($currency === 'VND') {
        return number_format($amount, 0, ',', '.') . ' ₫';
    }
    return number_format($amount, 2, '.', ',') . ' ' . $currency;
}

/**
 * Sanitize input
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check user role
 */
function hasRole($role) {
    return isset($_SESSION['vai_tro_id']) && $_SESSION['vai_tro_id'] == $role;
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return hasRole(ROLE_ADMIN);
}

/**
 * Check if user is recruiter
 */
function isRecruiter() {
    return hasRole(ROLE_RECRUITER);
}

/**
 * Check if user is candidate
 */
function isCandidate() {
    return hasRole(ROLE_CANDIDATE);
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect(BASE_URL . 'login.php');
    }
}

/**
 * Require role
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        redirect(BASE_URL . 'unauthorized.php');
    }
}

/**
 * Kiểm tra và tự động đăng nhập từ remember token
 */
function checkRememberMe() {
    // Nếu đã đăng nhập rồi thì không cần check
    if (isLoggedIn()) {
        return;
    }
    
    // Kiểm tra cookie remember_token
    if (isset($_COOKIE['remember_token']) && !empty($_COOKIE['remember_token'])) {
        try {
            // Kiểm tra xem $pdo đã được khởi tạo chưa
            global $pdo;
            if (!isset($pdo)) {
                // Nếu chưa có $pdo, require db.php
                require_once __DIR__ . '/db.php';
            }
            
            // Decode token
            $token_data = base64_decode($_COOKIE['remember_token']);
            $parts = explode('|', $token_data);
            
            if (count($parts) === 2) {
                $tai_khoan_id = $parts[0];
                $ten_dn = $parts[1];
                
                // Kiểm tra tài khoản có tồn tại và hợp lệ không
                require_once __DIR__ . '/../models/tai_khoan.php';
                $user = getTaiKhoanById($pdo, $tai_khoan_id);
                
                if ($user && $user['ten_dn'] === $ten_dn && $user['xoa_luc'] === null && $user['kich_hoat'] == 1) {
                    // Tự động đăng nhập
                    $_SESSION['user_id'] = $user['tai_khoan_id'];
                    $_SESSION['ten_dn'] = $user['ten_dn'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['vai_tro_id'] = $user['vai_tro_id'];
                    
                    // Cập nhật thời gian đăng nhập cuối
                    updateLastLogin($pdo, $user['tai_khoan_id']);
                } else {
                    // Token không hợp lệ, xóa cookie
                    setcookie('remember_token', '', time() - 3600, '/');
                }
            }
        } catch (Exception $e) {
            // Token không hợp lệ, xóa cookie
            setcookie('remember_token', '', time() - 3600, '/');
        }
    }
}

/**
 * Hiển thị thông báo lỗi
 */
function showError($message) {
    return '<div class="alert alert-danger">' . htmlspecialchars($message) . '</div>';
}

/**
 * Hiển thị thông báo thành công
 */
function showSuccess($message) {
    return '<div class="alert alert-success">' . htmlspecialchars($message) . '</div>';
}

/**
 * Tạo URL cho router
 * @param string $role Role: admin, recruiter, candidate, public
 * @param string $page Tên trang
 * @param array $params Các tham số query string bổ sung
 * @return string URL đầy đủ
 */
function route($role, $page, $params = []) {
    $url = BASE_URL . 'router/index.php?role=' . urlencode($role) . '&page=' . urlencode($page);
    if (!empty($params)) {
        $url .= '&' . http_build_query($params);
    }
    return $url;
}

/**
 * Tạo URL cho trang công khai
 */
function publicRoute($page, $params = []) {
    return route('public', $page, $params);
}

/**
 * Tạo URL cho trang admin
 */
function adminRoute($page, $params = []) {
    return route('admin', $page, $params);
}

/**
 * Tạo URL cho trang recruiter
 */
function recruiterRoute($page, $params = []) {
    return route('recruiter', $page, $params);
}

/**
 * Tạo URL cho trang candidate
 */
function candidateRoute($page, $params = []) {
    return route('candidate', $page, $params);
}

?>

