<?php
require_once 'config.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirect('login.php');
}

// Lấy danh sách gói tập
try {
    $stmt = $pdo->query("SELECT * FROM packages ORDER BY created_at DESC");
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi truy vấn dữ liệu: " . $e->getMessage());
}

// Xử lý thêm gói tập
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    $sessions = $_POST['sessions'];

    try {
        $stmt = $pdo->prepare("INSERT INTO packages (name, description, price, duration, sessions) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $duration, $sessions]);
        redirect('packages.php');
    } catch (PDOException $e) {
        die("Lỗi thêm gói tập: " . $e->getMessage());
    }
}

// Xử lý xóa gói tập
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM packages WHERE id = ?");
        $stmt->execute([$id]);
        redirect('packages.php');
    } catch (PDOException $e) {
        die("Lỗi xóa gói tập: " . $e->getMessage());
    }
}

// Xử lý sửa gói tập
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    $sessions = $_POST['sessions'];

    try {
        $stmt = $pdo->prepare("UPDATE packages SET name = ?, description = ?, price = ?, duration = ?, sessions = ? WHERE id = ?");
        $stmt->execute([$name, $description, $price, $duration, $sessions, $id]);
        redirect('packages.php');
    } catch (PDOException $e) {
        die("Lỗi cập nhật gói tập: " . $e->getMessage());
    }
}

// Lấy thông tin gói tập để sửa
$edit_package = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
        $stmt->execute([$id]);
        $edit_package = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Lỗi lấy thông tin gói tập: " . $e->getMessage());
    }
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý gói tập</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPackageModal">
            <i class="fas fa-plus"></i> Thêm gói tập
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên gói</th>
                            <th>Mô tả</th>
                            <th>Giá</th>
                            <th>Thời hạn (ngày)</th>
                            <th>Số buổi</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($packages as $package): ?>
                        <tr>
                            <td><?php echo $package['id']; ?></td>
                            <td><?php echo htmlspecialchars($package['name']); ?></td>
                            <td><?php echo htmlspecialchars($package['description']); ?></td>
                            <td><?php echo number_format($package['price'], 0, ',', '.'); ?> VNĐ</td>
                            <td><?php echo $package['duration']; ?></td>
                            <td><?php echo $package['sessions']; ?></td>
                            <td>
                                <a href="?edit=<?php echo $package['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?php echo $package['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa gói tập này?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal thêm gói tập -->
<div class="modal fade" id="addPackageModal" tabindex="-1" role="dialog" aria-labelledby="addPackageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPackageModalLabel">Thêm gói tập mới</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Tên gói</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Mô tả</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="price">Giá (VNĐ)</label>
                        <input type="number" class="form-control" id="price" name="price" required>
                    </div>
                    <div class="form-group">
                        <label for="duration">Thời hạn (ngày)</label>
                        <input type="number" class="form-control" id="duration" name="duration" required>
                    </div>
                    <div class="form-group">
                        <label for="sessions">Số buổi</label>
                        <input type="number" class="form-control" id="sessions" name="sessions" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal sửa gói tập -->
<?php if ($edit_package): ?>
<div class="modal fade" id="editPackageModal" tabindex="-1" role="dialog" aria-labelledby="editPackageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPackageModalLabel">Sửa thông tin gói tập</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?php echo $edit_package['id']; ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_name">Tên gói</label>
                        <input type="text" class="form-control" id="edit_name" name="name" value="<?php echo htmlspecialchars($edit_package['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Mô tả</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"><?php echo htmlspecialchars($edit_package['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_price">Giá (VNĐ)</label>
                        <input type="number" class="form-control" id="edit_price" name="price" value="<?php echo $edit_package['price']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_duration">Thời hạn (ngày)</label>
                        <input type="number" class="form-control" id="edit_duration" name="duration" value="<?php echo $edit_package['duration']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_sessions">Số buổi</label>
                        <input type="number" class="form-control" id="edit_sessions" name="sessions" value="<?php echo $edit_package['sessions']; ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#editPackageModal').modal('show');
    });
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?> 