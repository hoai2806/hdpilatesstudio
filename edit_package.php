<?php
require_once 'config.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $sessions = $_POST['sessions'];
    $duration = $_POST['duration'];
    $type = $_POST['type'];
    
    $sql = "UPDATE packages SET name = ?, description = ?, price = ?, sessions = ?, duration = ?, type = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$name, $description, $price, $sessions, $duration, $type, $id]);
    
    header('Location: packages.php');
    exit();
}

// Lấy thông tin gói tập
$sql = "SELECT * FROM packages WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    header('Location: packages.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa thông tin gói tập - HD Pilates</title>
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
                        <a class="nav-link active" href="packages.php">Gói tập</a>
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
                        <h5 class="mb-0">Sửa thông tin gói tập</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Tên gói tập</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($package['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Loại gói tập</label>
                                <select name="type" class="form-control" required>
                                    <option value="private" <?php echo $package['type'] == 'private' ? 'selected' : ''; ?>>Cá nhân</option>
                                    <option value="group" <?php echo $package['type'] == 'group' ? 'selected' : ''; ?>>Nhóm</option>
                                    <option value="both" <?php echo $package['type'] == 'both' ? 'selected' : ''; ?>>Cả hai</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Giá (VNĐ)</label>
                                <input type="number" name="price" class="form-control" value="<?php echo htmlspecialchars($package['price']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số buổi tập</label>
                                <input type="number" name="sessions" class="form-control" value="<?php echo htmlspecialchars($package['sessions']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Thời hạn (tháng)</label>
                                <input type="number" name="duration" class="form-control" value="<?php echo htmlspecialchars($package['duration']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($package['description']); ?></textarea>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="packages.php" class="btn btn-secondary">Quay lại</a>
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