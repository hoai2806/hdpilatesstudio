<?php
require_once 'config.php';
require_once 'functions.php';

$error_message = '';
$success_message = '';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirect('login.php');
}

// Xử lý xóa lịch tập
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
        $stmt->execute([$id]);
        $success_message = 'Xóa lịch tập thành công!';
        redirect('schedules.php');
    } catch (PDOException $e) {
        $error_message = "Lỗi xóa lịch tập: " . $e->getMessage();
    }
}

// Xử lý sửa lịch tập
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $student_id = $_POST['student_id'];
    $instructor_id = $_POST['instructor_id'];
    $package_id = $_POST['package_id'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $type = $_POST['type'];
    $notes = $_POST['notes'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE schedules SET student_id = ?, instructor_id = ?, package_id = ?, date = ?, start_time = ?, end_time = ?, type = ?, notes = ?, status = ? WHERE id = ?");
        $stmt->execute([$student_id, $instructor_id, $package_id, $date, $start_time, $end_time, $type, $notes, $status, $id]);
        $success_message = 'Cập nhật lịch tập thành công!';
        redirect('schedules.php');
    } catch (PDOException $e) {
        $error_message = "Lỗi cập nhật lịch tập: " . $e->getMessage();
    }
}

// Xử lý thêm lịch tập
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $student_id = $_POST['student_id'];
    $instructor_id = $_POST['instructor_id'];
    $package_id = $_POST['package_id'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $type = $_POST['type'];
    $notes = $_POST['notes'];
    $repeat_type = $_POST['repeat_type'];
    $repeat_until = $_POST['repeat_until'] ?: null;
    
    try {
        // Kiểm tra xung đột lịch
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM schedules 
            WHERE instructor_id = ? 
            AND date = ? 
            AND ((start_time BETWEEN ? AND ?) OR (end_time BETWEEN ? AND ?))
        ");
        $stmt->execute([$instructor_id, $date, $start_time, $end_time, $start_time, $end_time]);
        $conflict = $stmt->fetchColumn() > 0;
        
        if ($conflict) {
            $error_message = 'Huấn luyện viên đã có lịch tập trong thời gian này!';
        } else {
            $stmt = $pdo->prepare("INSERT INTO schedules (student_id, instructor_id, package_id, date, start_time, end_time, type, notes, repeat_type, repeat_until, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')");
            $stmt->execute([$student_id, $instructor_id, $package_id, $date, $start_time, $end_time, $type, $notes, $repeat_type, $repeat_until]);
            $success_message = 'Thêm lịch tập thành công!';
            redirect('schedules.php');
        }
    } catch (PDOException $e) {
        $error_message = "Lỗi thêm lịch tập: " . $e->getMessage();
    }
}

// Lấy danh sách học viên
try {
    $stmt = $pdo->query("SELECT id, name FROM students ORDER BY name");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi truy vấn dữ liệu học viên: " . $e->getMessage());
}

// Lấy danh sách huấn luyện viên
try {
    $stmt = $pdo->query("SELECT id, name FROM instructors ORDER BY name");
    $instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi truy vấn dữ liệu huấn luyện viên: " . $e->getMessage());
}

// Lấy danh sách gói tập
$sql = "SELECT id, name FROM packages WHERE status = 'active' ORDER BY name";
$stmt = $pdo->query($sql);
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách lịch tập
try {
    $stmt = $pdo->query("
        SELECT s.*, st.name as student_name, i.name as instructor_name, p.name as package_name 
        FROM schedules s 
        LEFT JOIN students st ON s.student_id = st.id 
        LEFT JOIN instructors i ON s.instructor_id = i.id 
        LEFT JOIN packages p ON s.package_id = p.id 
        ORDER BY s.date DESC, s.start_time DESC
    ");
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi truy vấn dữ liệu lịch tập: " . $e->getMessage());
}

// Lấy thông tin số buổi tập còn lại cho mỗi học viên
$student_packages = [];
foreach ($students as $student) {
    $student_packages[$student['id']] = getStudentPackageDetails($student['id']);
}

// Lấy thông tin lịch tập để sửa
$edit_schedule = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    try {
        $stmt = $pdo->prepare("
            SELECT s.*, st.name as student_name, i.name as instructor_name, p.name as package_name 
            FROM schedules s 
            LEFT JOIN students st ON s.student_id = st.id 
            LEFT JOIN instructors i ON s.instructor_id = i.id 
            LEFT JOIN packages p ON s.package_id = p.id 
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        $edit_schedule = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Lỗi lấy thông tin lịch tập: " . $e->getMessage());
    }
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý lịch tập</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addScheduleModal">
            <i class="fas fa-plus"></i> Thêm lịch tập
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Học viên</th>
                            <th>Huấn luyện viên</th>
                            <th>Ngày</th>
                            <th>Giờ</th>
                            <th>Loại</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $schedule): ?>
                        <tr>
                            <td><?php echo $schedule['id']; ?></td>
                            <td><?php echo htmlspecialchars($schedule['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($schedule['instructor_name']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($schedule['date'])); ?></td>
                            <td><?php echo date('H:i', strtotime($schedule['start_time'])) . ' - ' . date('H:i', strtotime($schedule['end_time'])); ?></td>
                            <td><?php echo $schedule['type'] === 'private' ? 'Cá nhân' : 'Nhóm'; ?></td>
                            <td>
                                <?php
                                switch($schedule['status']) {
                                    case 'scheduled':
                                        echo '<span class="badge bg-primary">Đã đặt lịch</span>';
                                        break;
                                    case 'completed':
                                        echo '<span class="badge bg-success">Đã hoàn thành</span>';
                                        break;
                                    case 'cancelled':
                                        echo '<span class="badge bg-danger">Đã hủy</span>';
                                        break;
                                }
                                ?>
                            </td>
                            <td>
                                <a href="?edit=<?php echo $schedule['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?php echo $schedule['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa lịch tập này?')">
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

<!-- Modal thêm lịch tập -->
<div class="modal fade" id="addScheduleModal" tabindex="-1" role="dialog" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addScheduleModalLabel">Thêm lịch tập mới</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="student_id">Học viên</label>
                        <select class="form-control" id="student_id" name="student_id" required>
                            <option value="">Chọn học viên</option>
                            <?php foreach ($students as $student): ?>
                            <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="instructor_id">Huấn luyện viên</label>
                        <select class="form-control" id="instructor_id" name="instructor_id" required>
                            <option value="">Chọn huấn luyện viên</option>
                            <?php foreach ($instructors as $instructor): ?>
                            <option value="<?php echo $instructor['id']; ?>"><?php echo htmlspecialchars($instructor['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="package_id">Gói tập</label>
                        <select class="form-control" id="package_id" name="package_id" required>
                            <option value="">Chọn gói tập</option>
                            <?php foreach ($packages as $package): ?>
                            <option value="<?php echo $package['id']; ?>"><?php echo htmlspecialchars($package['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted" id="remaining_sessions"></small>
                    </div>
                    <div class="form-group">
                        <label for="date">Ngày</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
                    <div class="form-group">
                        <label for="start_time">Giờ bắt đầu</label>
                        <input type="time" class="form-control" id="start_time" name="start_time" required>
                    </div>
                    <div class="form-group">
                        <label for="end_time">Giờ kết thúc</label>
                        <input type="time" class="form-control" id="end_time" name="end_time" required>
                    </div>
                    <div class="form-group">
                        <label for="type">Loại</label>
                        <select class="form-control" id="type" name="type" required>
                            <option value="private">Cá nhân</option>
                            <option value="group">Nhóm</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="notes">Ghi chú</label>
                        <textarea class="form-control" id="notes" name="notes" rows="1"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="repeat_type">Lặp lại</label>
                        <select class="form-control" id="repeat_type" name="repeat_type">
                            <option value="">Không lặp lại</option>
                            <option value="daily">Hàng ngày</option>
                            <option value="weekly">Hàng tuần</option>
                            <option value="monthly">Hàng tháng</option>
                        </select>
                    </div>
                    <div class="form-group" id="repeat_until_container" style="display: none;">
                        <label for="repeat_until">Lặp lại đến ngày</label>
                        <input type="date" class="form-control" id="repeat_until" name="repeat_until">
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

<!-- Modal sửa lịch tập -->
<?php if (isset($_GET['edit'])): ?>
<div class="modal fade" id="editScheduleModal" tabindex="-1" role="dialog" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editScheduleModalLabel">Sửa thông tin lịch tập</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?php echo $_GET['edit']; ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_student_id">Học viên</label>
                        <select class="form-control" id="edit_student_id" name="student_id" required>
                            <option value="">Chọn học viên</option>
                            <?php foreach ($students as $student): ?>
                            <option value="<?php echo $student['id']; ?>" <?php echo $student['id'] == $edit_schedule['student_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($student['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_instructor_id">Huấn luyện viên</label>
                        <select class="form-control" id="edit_instructor_id" name="instructor_id" required>
                            <option value="">Chọn huấn luyện viên</option>
                            <?php foreach ($instructors as $instructor): ?>
                            <option value="<?php echo $instructor['id']; ?>" <?php echo $instructor['id'] == $edit_schedule['instructor_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($instructor['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_package_id">Gói tập</label>
                        <select class="form-control" id="edit_package_id" name="package_id" required>
                            <option value="">Chọn gói tập</option>
                            <?php foreach ($packages as $package): ?>
                            <option value="<?php echo $package['id']; ?>" <?php echo $package['id'] == $edit_schedule['package_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($package['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted" id="edit_remaining_sessions"></small>
                    </div>
                    <div class="form-group">
                        <label for="edit_date">Ngày</label>
                        <input type="date" class="form-control" id="edit_date" name="date" value="<?php echo date('Y-m-d', strtotime($edit_schedule['date'])); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_start_time">Giờ bắt đầu</label>
                        <input type="time" class="form-control" id="edit_start_time" name="start_time" value="<?php echo date('H:i', strtotime($edit_schedule['start_time'])); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_end_time">Giờ kết thúc</label>
                        <input type="time" class="form-control" id="edit_end_time" name="end_time" value="<?php echo date('H:i', strtotime($edit_schedule['end_time'])); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_type">Loại</label>
                        <select class="form-control" id="edit_type" name="type" required>
                            <option value="private" <?php echo $edit_schedule['type'] === 'private' ? 'selected' : ''; ?>>Cá nhân</option>
                            <option value="group" <?php echo $edit_schedule['type'] === 'group' ? 'selected' : ''; ?>>Nhóm</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_notes">Ghi chú</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="1"><?php echo htmlspecialchars($edit_schedule['notes']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_repeat_type">Lặp lại</label>
                        <select class="form-control" id="edit_repeat_type" name="repeat_type">
                            <option value="">Không lặp lại</option>
                            <option value="daily" <?php echo $edit_schedule['repeat_type'] === 'daily' ? 'selected' : ''; ?>>Hàng ngày</option>
                            <option value="weekly" <?php echo $edit_schedule['repeat_type'] === 'weekly' ? 'selected' : ''; ?>>Hàng tuần</option>
                            <option value="monthly" <?php echo $edit_schedule['repeat_type'] === 'monthly' ? 'selected' : ''; ?>>Hàng tháng</option>
                        </select>
                    </div>
                    <div class="form-group" id="edit_repeat_until_container" style="display: none;">
                        <label for="edit_repeat_until">Lặp lại đến ngày</label>
                        <input type="date" class="form-control" id="edit_repeat_until" name="repeat_until" value="<?php echo date('Y-m-d', strtotime($edit_schedule['repeat_until'])); ?>">
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
        $('#editScheduleModal').modal('show');
    });
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

<script>
// Lưu trữ thông tin gói tập của học viên
const studentPackages = <?php echo json_encode($student_packages); ?>;

// Cập nhật thông tin số buổi tập còn lại
function updateRemainingSessionsInfo() {
    const studentId = document.getElementById('student_id').value;
    const packageId = document.getElementById('package_id').value;
    const remainingSessionsElement = document.getElementById('remaining_sessions');
    
    if (studentId && packageId && studentPackages[studentId]) {
        const package = studentPackages[studentId].find(p => p.id == packageId);
        if (package) {
            const remaining = package.sessions - package.used_sessions;
            remainingSessionsElement.textContent = `Còn lại ${remaining} buổi tập`;
            remainingSessionsElement.className = remaining > 0 ? 'text-success' : 'text-danger';
        } else {
            remainingSessionsElement.textContent = '';
        }
    } else {
        remainingSessionsElement.textContent = '';
    }
}

// Thêm sự kiện lắng nghe cho các select
document.getElementById('student_id').addEventListener('change', updateRemainingSessionsInfo);
document.getElementById('package_id').addEventListener('change', updateRemainingSessionsInfo);

// Xử lý hiển thị trường lặp lại
const repeatTypeSelect = document.getElementById('repeat_type');
const repeatUntilContainer = document.getElementById('repeat_until_container');

repeatTypeSelect.addEventListener('change', function() {
    if (this.value) {
        repeatUntilContainer.style.display = 'block';
    } else {
        repeatUntilContainer.style.display = 'none';
    }
});
</script> 