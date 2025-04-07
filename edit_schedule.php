<?php
require_once 'config.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $student_id = $_POST['student_id'];
    $instructor_id = $_POST['instructor_id'];
    $package_id = $_POST['package_id'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $type = $_POST['type'];
    $status = $_POST['status'];
    $notes = $_POST['notes'];
    
    $sql = "UPDATE schedules SET 
            student_id = ?, 
            instructor_id = ?, 
            package_id = ?, 
            date = ?, 
            start_time = ?, 
            end_time = ?, 
            type = ?, 
            status = ?,
            notes = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$student_id, $instructor_id, $package_id, $date, $start_time, $end_time, $type, $status, $notes, $id]);
    
    header('Location: schedules.php');
    exit();
}

// Lấy thông tin lịch tập
$sql = "SELECT * FROM schedules WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$schedule = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$schedule) {
    header('Location: schedules.php');
    exit();
}

// Lấy danh sách học viên
$sql = "SELECT id, name FROM students WHERE status = 'active' ORDER BY name";
$stmt = $conn->query($sql);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách huấn luyện viên
$sql = "SELECT id, name FROM instructors WHERE status = 'active' ORDER BY name";
$stmt = $conn->query($sql);
$instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách gói tập
$sql = "SELECT id, name FROM packages WHERE status = 'active' ORDER BY name";
$stmt = $conn->query($sql);
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa lịch tập - HD Pilates</title>
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
                        <a class="nav-link active" href="schedules.php">Lịch tập</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Sửa thông tin lịch tập</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Học viên</label>
                                        <select name="student_id" class="form-control" required>
                                            <option value="">Chọn học viên</option>
                                            <?php foreach ($students as $student): ?>
                                            <option value="<?php echo $student['id']; ?>" <?php echo $student['id'] == $schedule['student_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($student['name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Huấn luyện viên</label>
                                        <select name="instructor_id" class="form-control" required>
                                            <option value="">Chọn huấn luyện viên</option>
                                            <?php foreach ($instructors as $instructor): ?>
                                            <option value="<?php echo $instructor['id']; ?>" <?php echo $instructor['id'] == $schedule['instructor_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($instructor['name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Gói tập</label>
                                        <select name="package_id" class="form-control" required>
                                            <option value="">Chọn gói tập</option>
                                            <?php foreach ($packages as $package): ?>
                                            <option value="<?php echo $package['id']; ?>" <?php echo $package['id'] == $schedule['package_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($package['name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Ngày tập</label>
                                        <input type="date" name="date" class="form-control" value="<?php echo $schedule['date']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Giờ bắt đầu</label>
                                        <input type="time" name="start_time" class="form-control" value="<?php echo $schedule['start_time']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Giờ kết thúc</label>
                                        <input type="time" name="end_time" class="form-control" value="<?php echo $schedule['end_time']; ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Loại buổi tập</label>
                                        <select name="type" class="form-control" required>
                                            <option value="private" <?php echo $schedule['type'] == 'private' ? 'selected' : ''; ?>>Cá nhân</option>
                                            <option value="group" <?php echo $schedule['type'] == 'group' ? 'selected' : ''; ?>>Nhóm</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Trạng thái</label>
                                        <select name="status" class="form-control" required>
                                            <option value="scheduled" <?php echo $schedule['status'] == 'scheduled' ? 'selected' : ''; ?>>Đã đặt lịch</option>
                                            <option value="completed" <?php echo $schedule['status'] == 'completed' ? 'selected' : ''; ?>>Đã hoàn thành</option>
                                            <option value="cancelled" <?php echo $schedule['status'] == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Ghi chú</label>
                                        <textarea name="notes" class="form-control" rows="1"><?php echo htmlspecialchars($schedule['notes']); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="schedules.php" class="btn btn-secondary">Quay lại</a>
                                <button type="submit" name="update" class="btn btn-primary">Cập nhật</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 