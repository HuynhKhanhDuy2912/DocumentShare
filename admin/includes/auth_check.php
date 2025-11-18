<?php
session_start();

// Nếu chưa đăng nhập
if (!isset($_SESSION['emailUser'])) {
    header("Location: ../login.php");
    exit();
}

// Nếu không phải admin
if ($_SESSION['role'] != 1) {
    echo "<h2 style='color:red; text-align:center; margin-top:50px;'>Bạn không có quyền truy cập trang Admin</h2>";
    exit();
}

