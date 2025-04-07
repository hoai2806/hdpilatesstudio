<?php
require_once 'config.php';
require_once 'functions.php';

// Lấy thống kê theo học viên
$sql = "SELECT 
        s.id,
        s.name,
        s.email,
        s.phone,
        COUNT(sc.id) as total_sessions,
        COUNT(CASE WHEN sc.type = 'private' THEN 1 END) as private_sessions,
        COUNT(CASE WHEN sc.type = 'group' THEN 1 END) as group_sessions,
        COUNT(CASE WHEN sc.status = 'completed' THEN 1 END) as completed_sessions,
        COUNT(CASE WHEN sc.status = 'cancelled' THEN 1 END) as cancelled_sessions,
        COUNT(DISTINCT sc.package_id) as total_packages,
        SUM(p.price) as total_spent
        FROM students s
        LEFT JOIN schedules sc ON s.id = sc.student_id
        LEFT JOIN packages p ON sc.package_id = p.id
        GROUP BY s.id
        ORDER BY total_sessions DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tính tổng
$total_sessions = 0;
$total_completed = 0;
$total_cancelled = 0;
$total_spent = 0;
foreach ($stats as $stat) {
    $total_sessions += $stat['total_sessions'];
    $total_completed += $stat['completed_sessions'];
    $total_cancelled += $stat['cancelled_sessions'];
    $total_spent += $stat['total_spent'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê học viên - HD Pilates</title>
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
                        <a class="nav-link active" href="student_stats.php">Thống kê học viên</a>
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
                        <a class="nav-link" href="quarter_stats.php">Thống kê theo quý</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="year_stats.php">Thống kê theo năm</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Thống kê học viên</h2>

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
                        <h5 class="card-title">Tổng chi tiêu</h5>
                        <h3 class="mb-0"><?php echo number_format($total_spent, 0, ',', '.'); ?>đ</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Số buổi tập theo học viên</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="sessionsChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Chi tiêu theo học viên</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="spendingChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bảng chi tiết -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Chi tiết theo học viên</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tên học viên</th>
                                <th>Email</th>
                                <th>SĐT</th>
                                <th>Tổng buổi</th>
                                <th>Cá nhân</th>
                                <th>Nhóm</th>
                                <th>Hoàn thành</th>
                                <th>Hủy</th>
                                <th>Số gói</th>
                                <th>Tổng chi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats as $stat): ?>
                            <tr>
                                <td><?php echo $stat['name']; ?></td>
                                <td><?php echo $stat['email']; ?></td>
                                <td><?php echo $stat['phone']; ?></td>
                                <td><?php echo $stat['total_sessions']; ?></td>
                                <td><?php echo $stat['private_sessions']; ?></td>
                                <td><?php echo $stat['group_sessions']; ?></td>
                                <td><?php echo $stat['completed_sessions']; ?></td>
                                <td><?php echo $stat['cancelled_sessions']; ?></td>
                                <td><?php echo $stat['total_packages']; ?></td>
                                <td><?php echo number_format($stat['total_spent'], 0, ',', '.'); ?>đ</td>
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
    const stats = <?php echo json_encode($stats); ?>;
    
    // Biểu đồ số buổi tập
    new Chart(document.getElementById('sessionsChart'), {
        type: 'bar',
        data: {
            labels: stats.map(item => item.name),
            datasets: [{
                label: 'Buổi tập cá nhân',
                data: stats.map(item => item.private_sessions),
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgb(54, 162, 235)',
                borderWidth: 1
            }, {
                label: 'Buổi tập nhóm',
                data: stats.map(item => item.group_sessions),
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
    
    // Biểu đồ chi tiêu
    new Chart(document.getElementById('spendingChart'), {
        type: 'bar',
        data: {
            labels: stats.map(item => item.name),
            datasets: [{
                label: 'Chi tiêu',
                data: stats.map(item => item.total_spent),
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