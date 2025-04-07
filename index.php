<?php
// Bật hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Kiểm tra file config
if (!file_exists('config.php')) {
    die('Lỗi: Không tìm thấy file config.php');
}

require_once 'config.php';

// Kiểm tra kết nối database
try {
    $pdo->query("SELECT 1");
} catch (PDOException $e) {
    die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
}

// Chuyển hướng về trang đăng nhập
header("Location: login.php");
exit();
?> 