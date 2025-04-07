<?php
require_once 'config.php';

// Xóa tất cả session
session_destroy();

// Chuyển hướng về trang đăng nhập
redirect('login.php'); 