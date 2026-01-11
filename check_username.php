<?php
include 'config.php';

if (isset($_POST['username'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    
    // Kiểm tra trong bảng users
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo "exists"; // Tên đã tồn tại
    } else {
        echo "available"; // Có thể sử dụng
    }
}
?>