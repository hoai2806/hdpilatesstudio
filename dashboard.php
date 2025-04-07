<?php
// Bật hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once 'config.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirect('login.php');
}

// Lấy thông tin thống kê
try {
    // Tổng số học viên
    $stmt = $pdo->query("SELECT COUNT(*) FROM students");
    $totalStudents = $stmt->fetchColumn();

    // Tổng số huấn luyện viên
    $stmt = $pdo->query("SELECT COUNT(*) FROM instructors");
    $totalInstructors = $stmt->fetchColumn();

    // Tổng số gói tập
    $stmt = $pdo->query("SELECT COUNT(*) FROM packages");
    $totalPackages = $stmt->fetchColumn();

    // Tổng số lịch tập trong tháng
    $stmt = $pdo->query("SELECT COUNT(*) FROM schedules WHERE MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())");
    $totalSchedules = $stmt->fetchColumn();

    // Doanh thu tháng này
    $stmt = $pdo->query("SELECT SUM(amount) FROM payments WHERE MONTH(payment_date) = MONTH(CURRENT_DATE()) AND YEAR(payment_date) = YEAR(CURRENT_DATE())");
    $monthlyRevenue = $stmt->fetchColumn() ?: 0;

    // Học viên mới trong tháng
    $stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $newStudents = $stmt->fetchColumn();

} catch (PDOException $e) {
    die("Lỗi truy vấn dữ liệu: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - HD Pilates</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>

                <!-- Thống kê -->
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Tổng số học viên</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalStudents; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-people-fill fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Tổng số HLV</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalInstructors; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-person-badge-fill fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Tổng số gói tập</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalPackages; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-box-seam-fill fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Lịch tập tháng này</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalSchedules; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-calendar-check-fill fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Biểu đồ và bảng -->
                <div class="row">
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Doanh thu tháng này</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-area">
                                    <canvas id="revenueChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Học viên mới</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-pie pt-4">
                                    <canvas id="newStudentsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/charts.js"></script>
    <script>
        // Khởi tạo biểu đồ doanh thu
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4'],
                datasets: [{
                    label: 'Doanh thu',
                    data: [<?php echo $monthlyRevenue/4; ?>, <?php echo $monthlyRevenue/2; ?>, <?php echo $monthlyRevenue*0.75; ?>, <?php echo $monthlyRevenue; ?>],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            }
        });

        // Khởi tạo biểu đồ học viên mới
        const studentsCtx = document.getElementById('newStudentsChart').getContext('2d');
        new Chart(studentsCtx, {
            type: 'doughnut',
            data: {
                labels: ['Học viên mới', 'Học viên cũ'],
                datasets: [{
                    data: [<?php echo $newStudents; ?>, <?php echo $totalStudents - $newStudents; ?>],
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)'
                    ]
                }]
            }
        });
    </script>
</body>
</html> 