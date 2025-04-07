<?php
require_once 'config.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;

if ($id) {
    // Kiểm tra xem huấn luyện viên có tồn tại không
    $sql = "SELECT * FROM instructors WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $instructor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($instructor) {
        // Xóa huấn luyện viên
        $sql = "DELETE FROM instructors WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
    }
}

header('Location: instructors.php');
exit(); 