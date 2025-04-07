<?php
require_once 'config.php';
require_once 'functions.php';

// Xử lý lọc theo khoảng thời gian
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Lấy doanh thu
$revenue_data = getRevenue($start_date, $end_date);

// Tính tổng
$total_revenue = 0;
$total_sessions = 0;
$total_private = 0;
$total_group = 0;

foreach ($revenue_data as $data) {
    $total_revenue += $data['total_revenue'];
    $total_sessions += $data['total_sessions'];
    $total_private += $data['private_sessions'];
    $total_group += $data['group_sessions'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê doanh thu - HD Pilates</title>
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
                        <a class="nav-link active" href="revenue.php">Doanh thu</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Thống kê doanh thu</h2>
        
        <!-- Form lọc -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
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
                        <h5 class="card-title">Tổng doanh thu</h5>
                        <h3 class="mb-0"><?php echo number_format($total_revenue, 0, ',', '.'); ?>đ</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Tổng buổi tập</h5>
                        <h3 class="mb-0"><?php echo $total_sessions; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Buổi tập cá nhân</h5>
                        <h3 class="mb-0"><?php echo $total_private; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Buổi tập nhóm</h5>
                        <h3 class="mb-0"><?php echo $total_group; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ -->
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Biểu đồ doanh thu theo tháng</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Tỷ lệ loại buổi tập</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="sessionTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bảng chi tiết -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Chi tiết doanh thu theo tháng</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tháng</th>
                                <th>Tổng buổi tập</th>
                                <th>Buổi tập cá nhân</th>
                                <th>Buổi tập nhóm</th>
                                <th>Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($revenue_data as $data): ?>
                            <tr>
                                <td><?php echo date('m/Y', strtotime($data['month'] . '-01')); ?></td>
                                <td><?php echo $data['total_sessions']; ?></td>
                                <td><?php echo $data['private_sessions']; ?></td>
                                <td><?php echo $data['group_sessions']; ?></td>
                                <td><?php echo number_format($data['total_revenue'], 0, ',', '.'); ?>đ</td>
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
    const revenueData = <?php echo json_encode($revenue_data); ?>;
    
    // Biểu đồ doanh thu
    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: revenueData.map(item => {
                const date = new Date(item.month + '-01');
                return date.toLocaleDateString('vi-VN', { month: 'short', year: 'numeric' });
            }),
            datasets: [{
                label: 'Doanh thu',
                data: revenueData.map(item => item.total_revenue),
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgb(54, 162, 235)',
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
                            return new Intl.NumberFormat('vi-VN', {
                                style: 'currency',
                                currency: 'VND',
                                maximumFractionDigits: 0
                            }).format(value);
                        }
                    }
                }
            }
        }
    });
    
    // Biểu đồ tỷ lệ loại buổi tập
    new Chart(document.getElementById('sessionTypeChart'), {
        type: 'pie',
        data: {
            labels: ['Cá nhân', 'Nhóm'],
            datasets: [{
                data: [<?php echo $total_private; ?>, <?php echo $total_group; ?>],
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
    </script>
</body>
</html> 