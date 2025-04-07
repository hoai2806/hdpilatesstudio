<?php
require_once 'config.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $specialization = $_POST['specialization'];
    $experience = $_POST['experience'];
    
    $sql = "UPDATE instructors SET name = ?, email = ?, phone = ?, specialization = ?, experience = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$name, $email, $phone, $specialization, $experience, $id]);
    
    header('Location: instructors.php');
    exit();
}

// Lấy thông tin huấn luyện viên
$sql = "SELECT * FROM instructors WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$instructor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$instructor) {
    header('Location: instructors.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa thông tin huấn luyện viên - HD Pilates</title>
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
                        <a class="nav-link active" href="instructors.php">Huấn luyện viên</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="packages.php">Gói tập</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="schedules.php">Lịch tập</a>
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
                        <h5 class="mb-0">Sửa thông tin huấn luyện viên</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Họ tên</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($instructor['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($instructor['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($instructor['phone']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Chuyên môn</label>
                                <input type="text" name="specialization" class="form-control" value="<?php echo htmlspecialchars($instructor['specialization']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số năm kinh nghiệm</label>
                                <input type="number" name="experience" class="form-control" value="<?php echo htmlspecialchars($instructor['experience']); ?>" required>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="instructors.php" class="btn btn-secondary">Quay lại</a>
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