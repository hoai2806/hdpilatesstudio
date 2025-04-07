<?php
require_once 'config.php';

/**
 * Kiểm tra xung đột lịch tập
 */
function checkScheduleConflict($instructor_id, $date, $start_time, $end_time, $exclude_id = null) {
    global $conn;
    
    $sql = "SELECT * FROM schedules 
            WHERE instructor_id = ? 
            AND date = ? 
            AND status != 'cancelled'
            AND ((start_time <= ? AND end_time > ?) 
                OR (start_time < ? AND end_time >= ?) 
                OR (start_time >= ? AND end_time <= ?))";
    
    $params = [
        $instructor_id, 
        $date, 
        $end_time, 
        $start_time, 
        $end_time, 
        $start_time, 
        $start_time, 
        $end_time
    ];
    
    if ($exclude_id) {
        $sql .= " AND id != ?";
        $params[] = $exclude_id;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Lấy số buổi tập còn lại của học viên trong gói tập
 */
function getRemainingSessionsCount($student_id, $package_id) {
    global $conn;
    
    // Lấy thông tin gói tập
    $sql = "SELECT sessions FROM packages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$package_id]);
    $package = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$package) {
        return 0;
    }
    
    // Đếm số buổi đã tập (không tính buổi hủy)
    $sql = "SELECT COUNT(*) as used_sessions 
            FROM schedules 
            WHERE student_id = ? 
            AND package_id = ? 
            AND status != 'cancelled'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$student_id, $package_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $package['sessions'] - $result['used_sessions'];
}

/**
 * Kiểm tra xem học viên có đủ buổi tập trong gói không
 */
function hasAvailableSessions($student_id, $package_id) {
    return getRemainingSessionsCount($student_id, $package_id) > 0;
}

/**
 * Lấy thông tin chi tiết gói tập của học viên
 */
function getStudentPackageDetails($student_id) {
    global $conn;
    
    $sql = "SELECT DISTINCT p.*, 
            (SELECT COUNT(*) 
             FROM schedules s 
             WHERE s.student_id = ? 
             AND s.package_id = p.id 
             AND s.status != 'cancelled') as used_sessions
            FROM packages p
            JOIN schedules s ON s.package_id = p.id
            WHERE s.student_id = ?
            AND p.status = 'active'";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$student_id, $student_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Tính doanh thu theo khoảng thời gian
 */
function getRevenue($start_date, $end_date) {
    global $conn;
    
    $sql = "SELECT 
            DATE_FORMAT(s.date, '%Y-%m') as month,
            COUNT(*) as total_sessions,
            COUNT(CASE WHEN s.type = 'private' THEN 1 END) as private_sessions,
            COUNT(CASE WHEN s.type = 'group' THEN 1 END) as group_sessions,
            SUM(p.price) as total_revenue
            FROM schedules s
            JOIN packages p ON s.package_id = p.id
            WHERE s.date BETWEEN ? AND ?
            AND s.status != 'cancelled'
            GROUP BY DATE_FORMAT(s.date, '%Y-%m')
            ORDER BY month DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$start_date, $end_date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Gửi email thông báo lịch tập
 */
function sendScheduleNotification($schedule_id) {
    global $conn;
    
    // Lấy thông tin lịch tập
    $sql = "SELECT s.*, 
            st.name as student_name, st.email as student_email,
            i.name as instructor_name,
            p.name as package_name
            FROM schedules s
            JOIN students st ON s.student_id = st.id
            JOIN instructors i ON s.instructor_id = i.id
            JOIN packages p ON s.package_id = p.id
            WHERE s.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$schedule_id]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$schedule) {
        return false;
    }
    
    // Chuẩn bị nội dung email
    $to = $schedule['student_email'];
    $subject = 'Thông báo lịch tập - HD Pilates';
    $message = "Xin chào {$schedule['student_name']},\n\n";
    $message .= "Bạn có lịch tập mới tại HD Pilates:\n\n";
    $message .= "Ngày: " . date('d/m/Y', strtotime($schedule['date'])) . "\n";
    $message .= "Thời gian: " . date('H:i', strtotime($schedule['start_time'])) . " - " . date('H:i', strtotime($schedule['end_time'])) . "\n";
    $message .= "Huấn luyện viên: {$schedule['instructor_name']}\n";
    $message .= "Gói tập: {$schedule['package_name']}\n";
    $message .= "Loại buổi tập: " . ($schedule['type'] == 'private' ? 'Cá nhân' : 'Nhóm') . "\n\n";
    $message .= "Vui lòng đến đúng giờ. Nếu cần hủy hoặc đổi lịch, vui lòng thông báo trước 24 giờ.\n\n";
    $message .= "Trân trọng,\nHD Pilates";
    
    $headers = "From: no-reply@hdpilates.com\r\n";
    $headers .= "Reply-To: contact@hdpilates.com\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Xuất báo cáo lịch tập theo khoảng thời gian
 */
function generateScheduleReport($start_date, $end_date) {
    global $conn;
    
    $sql = "SELECT s.*, 
            st.name as student_name,
            i.name as instructor_name,
            p.name as package_name,
            p.price
            FROM schedules s
            JOIN students st ON s.student_id = st.id
            JOIN instructors i ON s.instructor_id = i.id
            JOIN packages p ON s.package_id = p.id
            WHERE s.date BETWEEN ? AND ?
            ORDER BY s.date ASC, s.start_time ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$start_date, $end_date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Tạo lịch tập định kỳ
 */
function createRecurringSchedules($data, $repeat_type, $repeat_until) {
    global $conn;
    
    $student_id = $data['student_id'];
    $instructor_id = $data['instructor_id'];
    $package_id = $data['package_id'];
    $start_time = $data['start_time'];
    $end_time = $data['end_time'];
    $type = $data['type'];
    $notes = $data['notes'];
    
    $current_date = new DateTime($data['date']);
    $end_date = new DateTime($repeat_until);
    $interval = null;
    
    switch ($repeat_type) {
        case 'daily':
            $interval = new DateInterval('P1D');
            break;
        case 'weekly':
            $interval = new DateInterval('P1W');
            break;
        case 'monthly':
            $interval = new DateInterval('P1M');
            break;
    }
    
    $schedules_created = 0;
    
    while ($current_date <= $end_date) {
        // Kiểm tra xung đột lịch
        $conflict = checkScheduleConflict(
            $instructor_id, 
            $current_date->format('Y-m-d'), 
            $start_time, 
            $end_time
        );
        
        // Kiểm tra số buổi tập còn lại
        $has_sessions = hasAvailableSessions($student_id, $package_id);
        
        if (!$conflict && $has_sessions) {
            $sql = "INSERT INTO schedules (
                    student_id, instructor_id, package_id, 
                    date, start_time, end_time, 
                    type, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $student_id, $instructor_id, $package_id,
                $current_date->format('Y-m-d'), $start_time, $end_time,
                $type, $notes
            ]);
            
            $schedules_created++;
            
            // Gửi email thông báo
            sendScheduleNotification($conn->lastInsertId());
        }
        
        $current_date->add($interval);
    }
    
    return $schedules_created;
} 