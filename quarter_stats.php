<?php
require_once 'config.php';
require_once 'functions.php';

// Xử lý lọc theo năm
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Lấy thống kê theo quý
$sql = "SELECT 
        QUARTER(date) as quarter,
        COUNT(id) as total_sessions,
        COUNT(CASE WHEN type = 'private' THEN 1 END) as private_sessions,
        COUNT(CASE WHEN type = 'group' THEN 1 END) as group_sessions,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_sessions,
        COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_sessions,
        COUNT(DISTINCT student_id) as unique_students,
        COUNT(DISTINCT instructor_id) as unique_instructors,
        SUM(p.price) as total_revenue
        FROM schedules s
        LEFT JOIN packages p ON s.package_id = p.id
        WHERE YEAR(date) = ?
        GROUP BY QUARTER(date)
        ORDER BY quarter";

$stmt = $conn->prepare($sql);
$stmt->execute([$year]);
$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tạo dữ liệu cho tất cả các quý (1-4)
$all_quarters = [];
$quarter_names = ['Quý 1', 'Quý 2', 'Quý 3', 'Quý 4'];
for ($i = 1; $i <= 4; $i++) {
    $all_quarters[$i] = [
        'quarter' => $i,
        'quarter_name' => $quarter_names[$i-1],
        'total_sessions' => 0,
        'private_sessions' => 0,
        'group_sessions' => 0,
        'completed_sessions' => 0,
        'cancelled_sessions' => 0,
        'unique_students' => 0,
        'unique_instructors' => 0,
        'total_revenue' => 0
    ];
}

// Cập nhật dữ liệu cho các quý có thống kê
foreach ($stats as $stat) {
    $all_quarters[$stat['quarter']] = array_merge($all_quarters[$stat['quarter']], $stat);
}

// Tính tổng
$total_sessions = 0;
$total_completed = 0;
$total_cancelled = 0;
$total_revenue = 0;
foreach ($all_quarters as $quarter) {
    $total_sessions += $quarter['total_sessions'];
    $total_completed += $quarter['completed_sessions'];
    $total_cancelled += $quarter['cancelled_sessions'];
    $total_revenue += $quarter['total_revenue'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê theo quý - HD Pilates</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <a class="nav-link" href="instructor_stats.php">Thống kê HLV</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="package_stats.php">Thống kê gói tập</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="student_stats.php">Thống kê học viên</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="time_stats.php">Thống kê thời gian</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="hour_stats.php">Thống kê khung giờ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="day_stats.php">Thống kê ngày trong tuần</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="month_stats.php">Thống kê theo tháng</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="quarter_stats.php">Thống kê theo quý</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="year_stats.php">Thống kê theo năm</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Thống kê theo quý</h2>

        <!-- Form lọc -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Năm</label>
                        <select name="year" class="form-select">
                            <?php
                            $current_year = date('Y');
                            for ($i = $current_year; $i >= $current_year - 5; $i--) {
                                $selected = $i == $year ? 'selected' : '';
                                echo "<option value='$i' $selected>$i</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block">Lọc</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tổng quan -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Tổng số buổi tập</h5>
                        <h3 class="mb-0"><?php echo $total_sessions; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Buổi tập hoàn thành</h5>
                        <h3 class="mb-0"><?php echo $total_completed; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Buổi tập hủy</h5>
                        <h3 class="mb-0"><?php echo $total_cancelled; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Tổng doanh thu</h5>
                        <h3 class="mb-0"><?php echo number_format($total_revenue, 0, ',', '.'); ?>đ</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Số buổi tập theo quý</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="sessionsChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Doanh thu theo quý</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bảng chi tiết -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Chi tiết theo quý</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Quý</th>
                                <th>Tổng buổi</th>
                                <th>Cá nhân</th>
                                <th>Nhóm</th>
                                <th>Hoàn thành</th>
                                <th>Hủy</th>
                                <th>Số học viên</th>
                                <th>Số HLV</th>
                                <th>Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_quarters as $quarter): ?>
                            <tr>
                                <td><?php echo $quarter['quarter_name']; ?></td>
                                <td><?php echo $quarter['total_sessions']; ?></td>
                                <td><?php echo $quarter['private_sessions']; ?></td>
                                <td><?php echo $quarter['group_sessions']; ?></td>
                                <td><?php echo $quarter['completed_sessions']; ?></td>
                                <td><?php echo $quarter['cancelled_sessions']; ?></td>
                                <td><?php echo $quarter['unique_students']; ?></td>
                                <td><?php echo $quarter['unique_instructors']; ?></td>
                                <td><?php echo number_format($quarter['total_revenue'], 0, ',', '.'); ?>đ</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Dữ liệu cho biểu đồ
    const stats = <?php echo json_encode($all_quarters); ?>;
    
    // Biểu đồ số buổi tập
    new Chart(document.getElementById('sessionsChart'), {
        type: 'bar',
        data: {
            labels: Object.values(stats).map(item => item.quarter_name),
            datasets: [{
                label: 'Buổi tập cá nhân',
                data: Object.values(stats).map(item => item.private_sessions),
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgb(54, 162, 235)',
                borderWidth: 1
            }, {
                label: 'Buổi tập nhóm',
                data: Object.values(stats).map(item => item.group_sessions),
                backgroundColor: 'rgba(255, 206, 86, 0.5)',
                borderColor: 'rgb(255, 206, 86)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    stacked: true
                },
                x: {
                    stacked: true
                }
            }
        }
    });
    
    // Biểu đồ doanh thu
    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: Object.values(stats).map(item => item.quarter_name),
            datasets: [{
                label: 'Doanh thu',
                data: Object.values(stats).map(item => item.total_revenue),
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderColor: 'rgb(75, 192, 192)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('vi-VN') + 'đ';
                        }
                    }
                }
            }
        }
    });
    </script>
</body>
</html> 