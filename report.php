<?php
require_once 'config.php';
require_once 'functions.php';

// Xử lý lọc theo khoảng thời gian
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Lấy dữ liệu báo cáo
$report_data = generateScheduleReport($start_date, $end_date);

// Xử lý xuất Excel
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    // Tên file
    $filename = "bao-cao-lich-tap-" . date('Y-m-d') . ".csv";
    
    // Header cho file CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Mở output stream
    $output = fopen('php://output', 'w');
    
    // UTF-8 BOM
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Header của bảng
    fputcsv($output, [
        'Ngày',
        'Thời gian',
        'Học viên',
        'Huấn luyện viên',
        'Gói tập',
        'Loại buổi tập',
        'Trạng thái',
        'Ghi chú'
    ]);
    
    // Dữ liệu
    foreach ($report_data as $row) {
        fputcsv($output, [
            date('d/m/Y', strtotime($row['date'])),
            date('H:i', strtotime($row['start_time'])) . ' - ' . date('H:i', strtotime($row['end_time'])),
            $row['student_name'],
            $row['instructor_name'],
            $row['package_name'],
            $row['type'] == 'private' ? 'Cá nhân' : 'Nhóm',
            $row['status'] == 'scheduled' ? 'Đã đặt lịch' : ($row['status'] == 'completed' ? 'Đã hoàn thành' : 'Đã hủy'),
            $row['notes']
        ]);
    }
    
    fclose($output);
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo lịch tập - HD Pilates</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">HD Pilates</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="students.php">Học viên</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="instructors.php">Huấn luyện viên</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="packages.php">Gói tập</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="schedules.php">Lịch tập</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="revenue.php">Doanh thu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="report.php">Báo cáo</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Báo cáo lịch tập</h2>
            <a href="?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&export=excel" class="btn btn-success">
                Xuất Excel
            </a>
        </div>
        
        <!-- Form lọc -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block w-100">Lọc</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bảng báo cáo -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th>Thời gian</th>
                                <th>Học viên</th>
                                <th>Huấn luyện viên</th>
                                <th>Gói tập</th>
                                <th>Loại</th>
                                <th>Trạng thái</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report_data as $schedule): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($schedule['date'])); ?></td>
                                <td><?php echo date('H:i', strtotime($schedule['start_time'])) . ' - ' . date('H:i', strtotime($schedule['end_time'])); ?></td>
                                <td><?php echo htmlspecialchars($schedule['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['instructor_name']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['package_name']); ?></td>
                                <td><?php echo $schedule['type'] == 'private' ? 'Cá nhân' : 'Nhóm'; ?></td>
                                <td>
                                    <?php
                                    switch($schedule['status']) {
                                        case 'scheduled':
                                            echo '<span class="badge bg-primary">Đã đặt lịch</span>';
                                            break;
                                        case 'completed':
                                            echo '<span class="badge bg-success">Đã hoàn thành</span>';
                                            break;
                                        case 'cancelled':
                                            echo '<span class="badge bg-danger">Đã hủy</span>';
                                            break;
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($schedule['notes']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 