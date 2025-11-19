<?php

require_once __DIR__ . '/../config.php';

// =====================================================
// KẾT NỐI PDO (Chính - cho duantest2)
// =====================================================
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    // Trong môi trường production, không hiển thị lỗi chi tiết
    error_log("PDO Database Error: " . $e->getMessage());
    die("Không thể kết nối đến database. Vui lòng thử lại sau.");
}

// =====================================================
// KẾT NỐI MYSQLI (Cho admin panel từ duantest2.1)
// =====================================================
mysqli_report(MYSQLI_REPORT_OFF);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, defined('DB_PORT') ? DB_PORT : 3306);

// Kiểm tra kết nối mysqli
if ($conn->connect_error) {
    error_log("MySQLi Database Error: " . $conn->connect_error);
    // Không die ở đây để không ảnh hưởng đến PDO
    // Các file dùng mysqli sẽ tự xử lý lỗi
}

// Đặt charset thành UTF-8 cho mysqli
if (!$conn->connect_error) {
    $conn->set_charset("utf8mb4");
}

?>

