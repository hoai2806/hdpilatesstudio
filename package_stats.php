<?php
require_once 'config.php';
require_once 'functions.php';

// Lấy thống kê theo gói tập
$sql = "SELECT 
        p.id,
        p.name,
        p.type,
        p.price,
        p.sessions,
        p.duration,
        COUNT(s.id) as total_sold,
        COUNT(DISTINCT s.student_id) as unique_customers,
        COUNT(CASE WHEN s.status = 'completed' THEN 1 END) as completed_sessions,
        COUNT(CASE WHEN s.status = 'cancelled' THEN 1 END) as cancelled_sessions,
        SUM(p.price) as total_revenue
        FROM packages p
        LEFT JOIN schedules s ON p.id = s.package_id
        GROUP BY p.id
        ORDER BY total_sold DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tính tổng
$total_sold = 0;
$total_completed = 0;
$total_cancelled = 0;
$total_revenue = 0;
foreach ($stats as $stat) {
    $total_sold += $stat['total_sold'];
    $total_completed += $stat['completed_sessions'];
    $total_cancelled += $stat['cancelled_sessions'];
    $total_revenue += $stat['total_revenue'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê gói tập - HD Pilates</title>
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
                        <a class="nav-link active" href="package_stats.php">Thống kê gói tập</a>
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
        <h2>Thống kê gói tập</h2>

        <!-- Tổng quan -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Tổng số gói bán được</h5>
                        <h3 class="mb-0"><?php echo $total_sold; ?></h3>
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
                        <h5 class="mb-0">Số gói bán được theo loại</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="typeChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Doanh thu theo gói</h5>
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
                <h5 class="mb-0">Chi tiết theo gói</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tên gói</th>
                                <th>Loại</th>
                                <th>Giá</th>
                                <th>Số buổi</th>
                                <th>Thời hạn</th>
                                <th>Tổng bán</th>
                                <th>Số khách</th>
                                <th>Hoàn thành</th>
                                <th>Hủy</th>
                                <th>Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats as $stat): ?>
                            <tr>
                                <td><?php echo $stat['name']; ?></td>
                                <td><?php echo $stat['type'] == 'private' ? 'Cá nhân' : 'Nhóm'; ?></td>
                                <td><?php echo number_format($stat['price'], 0, ',', '.'); ?>đ</td>
                                <td><?php echo $stat['sessions']; ?></td>
                                <td><?php echo $stat['duration']; ?> tháng</td>
                                <td><?php echo $stat['total_sold']; ?></td>
                                <td><?php echo $stat['unique_customers']; ?></td>
                                <td><?php echo $stat['completed_sessions']; ?></td>
                                <td><?php echo $stat['cancelled_sessions']; ?></td>
                                <td><?php echo number_format($stat['total_revenue'], 0, ',', '.'); ?>đ</td>
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
    
    // Tính số gói bán được theo loại
    const privatePackages = stats.filter(item => item.type === 'private').reduce((sum, item) => sum + parseInt(item.total_sold), 0);
    const groupPackages = stats.filter(item => item.type === 'group').reduce((sum, item) => sum + parseInt(item.total_sold), 0);
    
    // Biểu đồ số gói bán được theo loại
    new Chart(document.getElementById('typeChart'), {
        type: 'pie',
        data: {
            labels: ['Cá nhân', 'Nhóm'],
            datasets: [{
                data: [privatePackages, groupPackages],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)'
                ],
                borderColor: [
                    'rgb(54, 162, 235)',
                    'rgb(255, 206, 86)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true
        }
    });
    
    // Biểu đồ doanh thu
    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: stats.map(item => item.name),
            datasets: [{
                label: 'Doanh thu',
                data: stats.map(item => item.total_revenue),
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