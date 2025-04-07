<?php
// Bật hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/config.php';

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

include 'includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Earnings (Monthly) Card Example -->
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
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings (Monthly) Card Example -->
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
                        <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings (Monthly) Card Example -->
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
                        <i class="fas fa-box fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Requests Card Example -->
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
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Area Chart -->
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

    <!-- Pie Chart -->
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

</div> <!-- Đóng container-fluid -->

<?php include 'includes/footer.php'; ?>

<!-- Page level plugins -->
<script src="vendor/chart.js/Chart.min.js"></script>

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