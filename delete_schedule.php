<?php
require_once 'config.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;

if ($id) {
    // Kiểm tra xem lịch tập có tồn tại không
    $sql = "SELECT * FROM schedules WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($schedule) {
        // Xóa lịch tập
        $sql = "DELETE FROM schedules WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
    }
}

header('Location: schedules.php');
exit(); 