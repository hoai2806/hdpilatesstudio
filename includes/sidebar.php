<?php
if (!isLoggedIn()) {
    redirect('login.php');
}
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="students.php">
                    <i class="bi bi-people"></i>
                    Học viên
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="instructors.php">
                    <i class="bi bi-person-badge"></i>
                    Huấn luyện viên
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="packages.php">
                    <i class="bi bi-box-seam"></i>
                    Gói tập
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="schedules.php">
                    <i class="bi bi-calendar-check"></i>
                    Lịch tập
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="payments.php">
                    <i class="bi bi-cash"></i>
                    Thanh toán
                </a>
            </li>
            <?php if (isAdmin()): ?>
            <li class="nav-item">
                <a class="nav-link" href="users.php">
                    <i class="bi bi-person-gear"></i>
                    Quản lý người dùng
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="settings.php">
                    <i class="bi bi-gear"></i>
                    Cài đặt
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</nav> 