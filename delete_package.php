<?php
require_once 'config.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;

if ($id) {
    // Kiểm tra xem gói tập có tồn tại không
    $sql = "SELECT * FROM packages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $package = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($package) {
        // Xóa gói tập
        $sql = "DELETE FROM packages WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
    }
}

header('Location: packages.php');
exit(); 