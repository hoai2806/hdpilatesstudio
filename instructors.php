<?php
require_once 'config.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirect('login.php');
}

$error_message = '';
$success_message = '';

// Lấy danh sách huấn luyện viên
try {
    $stmt = $pdo->query("SELECT * FROM instructors ORDER BY status ASC, created_at DESC");
    $instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi truy vấn dữ liệu: " . $e->getMessage());
}

// Xử lý thêm huấn luyện viên
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $specialization = $_POST['specialization'];
    $experience = $_POST['experience'];
    $status = 'active';

    try {
        $stmt = $pdo->prepare("INSERT INTO instructors (name, email, phone, specialization, experience, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $specialization, $experience, $status]);
        $success_message = 'Thêm huấn luyện viên thành công!';
        redirect('instructors.php');
    } catch (PDOException $e) {
        $error_message = "Lỗi thêm huấn luyện viên: " . $e->getMessage();
    }
}

// Xử lý xóa huấn luyện viên
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        // Kiểm tra xem huấn luyện viên có lịch tập không
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM schedules WHERE instructor_id = ?");
        $stmt->execute([$id]);
        $has_schedules = $stmt->fetchColumn() > 0;

        if ($has_schedules) {
            // Nếu có lịch tập, chỉ cập nhật trạng thái thành inactive
            $stmt = $pdo->prepare("UPDATE instructors SET status = 'inactive' WHERE id = ?");
            $stmt->execute([$id]);
            $success_message = 'Đã vô hiệu hóa huấn luyện viên!';
        } else {
            // Nếu không có lịch tập, xóa hoàn toàn
            $stmt = $pdo->prepare("DELETE FROM instructors WHERE id = ?");
            $stmt->execute([$id]);
            $success_message = 'Xóa huấn luyện viên thành công!';
        }
        redirect('instructors.php');
    } catch (PDOException $e) {
        $error_message = "Lỗi xóa huấn luyện viên: " . $e->getMessage();
    }
}

// Xử lý sửa huấn luyện viên
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $specialization = $_POST['specialization'];
    $experience = $_POST['experience'];
    $status = $_POST['status'];

    try {
        $stmt = $pdo->prepare("UPDATE instructors SET name = ?, email = ?, phone = ?, specialization = ?, experience = ?, status = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $specialization, $experience, $status, $id]);
        $success_message = 'Cập nhật huấn luyện viên thành công!';
        redirect('instructors.php');
    } catch (PDOException $e) {
        $error_message = "Lỗi cập nhật huấn luyện viên: " . $e->getMessage();
    }
}

// Lấy thông tin huấn luyện viên để sửa
$edit_instructor = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM instructors WHERE id = ?");
        $stmt->execute([$id]);
        $edit_instructor = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Lỗi lấy thông tin huấn luyện viên: " . $e->getMessage());
    }
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <?php if ($error_message): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo $error_message; ?>
    </div>
    <?php endif; ?>

    <?php if ($success_message): ?>
    <div class="alert alert-success" role="alert">
        <?php echo $success_message; ?>
    </div>
    <?php endif; ?>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý huấn luyện viên</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addInstructorModal">
            <i class="fas fa-plus"></i> Thêm huấn luyện viên
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>Điện thoại</th>
                            <th>Chuyên môn</th>
                            <th>Kinh nghiệm</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($instructors as $instructor): ?>
                        <tr>
                            <td><?php echo $instructor['id']; ?></td>
                            <td><?php echo htmlspecialchars($instructor['name']); ?></td>
                            <td><?php echo htmlspecialchars($instructor['email']); ?></td>
                            <td><?php echo htmlspecialchars($instructor['phone']); ?></td>
                            <td><?php echo htmlspecialchars($instructor['specialization']); ?></td>
                            <td><?php echo htmlspecialchars($instructor['experience']); ?></td>
                            <td>
                                <?php if ($instructor['status'] === 'active'): ?>
                                    <span class="badge badge-success">Đang làm việc</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Ngừng làm việc</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?edit=<?php echo $instructor['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?php echo $instructor['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa huấn luyện viên này?')">
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

<!-- Modal thêm huấn luyện viên -->
<div class="modal fade" id="addInstructorModal" tabindex="-1" role="dialog" aria-labelledby="addInstructorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addInstructorModalLabel">Thêm huấn luyện viên mới</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Tên huấn luyện viên</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Điện thoại</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="specialization">Chuyên môn</label>
                        <input type="text" class="form-control" id="specialization" name="specialization" required>
                    </div>
                    <div class="form-group">
                        <label for="experience">Kinh nghiệm</label>
                        <textarea class="form-control" id="experience" name="experience" rows="3"></textarea>
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

<!-- Modal sửa huấn luyện viên -->
<?php if ($edit_instructor): ?>
<div class="modal fade" id="editInstructorModal" tabindex="-1" role="dialog" aria-labelledby="editInstructorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editInstructorModalLabel">Sửa thông tin huấn luyện viên</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?php echo $edit_instructor['id']; ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_name">Tên huấn luyện viên</label>
                        <input type="text" class="form-control" id="edit_name" name="name" value="<?php echo htmlspecialchars($edit_instructor['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" value="<?php echo htmlspecialchars($edit_instructor['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_phone">Điện thoại</label>
                        <input type="text" class="form-control" id="edit_phone" name="phone" value="<?php echo htmlspecialchars($edit_instructor['phone']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_specialization">Chuyên môn</label>
                        <input type="text" class="form-control" id="edit_specialization" name="specialization" value="<?php echo htmlspecialchars($edit_instructor['specialization']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_experience">Kinh nghiệm</label>
                        <textarea class="form-control" id="edit_experience" name="experience" rows="3"><?php echo htmlspecialchars($edit_instructor['experience']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_status">Trạng thái</label>
                        <select class="form-control" id="edit_status" name="status" required>
                            <option value="active" <?php echo $edit_instructor['status'] === 'active' ? 'selected' : ''; ?>>Đang làm việc</option>
                            <option value="inactive" <?php echo $edit_instructor['status'] === 'inactive' ? 'selected' : ''; ?>>Ngừng làm việc</option>
                        </select>
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
        $('#editInstructorModal').modal('show');
    });
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?> 