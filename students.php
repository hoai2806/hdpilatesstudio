<?php
require_once 'config.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirect('login.php');
}

// Lấy danh sách học viên
try {
    $stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi truy vấn dữ liệu: " . $e->getMessage());
}

// Xử lý thêm học viên
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $birth_date = !empty($_POST['birth_date']) ? $_POST['birth_date'] : null;
    $gender = !empty($_POST['gender']) ? $_POST['gender'] : 'other';

    try {
        $stmt = $pdo->prepare("INSERT INTO students (name, email, phone, address, birth_date, gender) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $address, $birth_date, $gender]);
        redirect('students.php');
    } catch (PDOException $e) {
        die("Lỗi thêm học viên: " . $e->getMessage());
    }
}

// Xử lý xóa học viên
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$id]);
        redirect('students.php');
    } catch (PDOException $e) {
        die("Lỗi xóa học viên: " . $e->getMessage());
    }
}

// Xử lý sửa học viên
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $birth_date = !empty($_POST['birth_date']) ? $_POST['birth_date'] : null;
    $gender = !empty($_POST['gender']) ? $_POST['gender'] : 'other';

    try {
        $stmt = $pdo->prepare("UPDATE students SET name = ?, email = ?, phone = ?, address = ?, birth_date = ?, gender = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $address, $birth_date, $gender, $id]);
        redirect('students.php');
    } catch (PDOException $e) {
        die("Lỗi cập nhật học viên: " . $e->getMessage());
    }
}

// Lấy thông tin học viên để sửa
$edit_student = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $edit_student = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Lỗi lấy thông tin học viên: " . $e->getMessage());
    }
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý học viên</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addStudentModal">
            <i class="fas fa-plus"></i> Thêm học viên
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
                            <th>Địa chỉ</th>
                            <th>Ngày sinh</th>
                            <th>Giới tính</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo $student['id']; ?></td>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td><?php echo htmlspecialchars($student['phone']); ?></td>
                            <td><?php echo htmlspecialchars($student['address'] ?? ''); ?></td>
                            <td><?php echo $student['birth_date'] ?? ''; ?></td>
                            <td><?php echo $student['gender'] ?? 'other'; ?></td>
                            <td>
                                <a href="?edit=<?php echo $student['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?php echo $student['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa học viên này?')">
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

<!-- Modal thêm học viên -->
<div class="modal fade" id="addStudentModal" tabindex="-1" role="dialog" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStudentModalLabel">Thêm học viên mới</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Tên học viên</label>
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
                        <label for="address">Địa chỉ</label>
                        <textarea class="form-control" id="address" name="address"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="birth_date">Ngày sinh</label>
                        <input type="date" class="form-control" id="birth_date" name="birth_date">
                    </div>
                    <div class="form-group">
                        <label for="gender">Giới tính</label>
                        <select class="form-control" id="gender" name="gender">
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                            <option value="other">Khác</option>
                        </select>
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

<!-- Modal sửa học viên -->
<?php if ($edit_student): ?>
<div class="modal fade" id="editStudentModal" tabindex="-1" role="dialog" aria-labelledby="editStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStudentModalLabel">Sửa thông tin học viên</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?php echo $edit_student['id']; ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_name">Tên học viên</label>
                        <input type="text" class="form-control" id="edit_name" name="name" value="<?php echo htmlspecialchars($edit_student['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" value="<?php echo htmlspecialchars($edit_student['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_phone">Điện thoại</label>
                        <input type="text" class="form-control" id="edit_phone" name="phone" value="<?php echo htmlspecialchars($edit_student['phone']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_address">Địa chỉ</label>
                        <textarea class="form-control" id="edit_address" name="address"><?php echo htmlspecialchars($edit_student['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_birth_date">Ngày sinh</label>
                        <input type="date" class="form-control" id="edit_birth_date" name="birth_date" value="<?php echo $edit_student['birth_date'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="edit_gender">Giới tính</label>
                        <select class="form-control" id="edit_gender" name="gender">
                            <option value="male" <?php echo ($edit_student['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Nam</option>
                            <option value="female" <?php echo ($edit_student['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Nữ</option>
                            <option value="other" <?php echo ($edit_student['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Khác</option>
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
        $('#editStudentModal').modal('show');
    });
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?> 