<?php
include 'config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    exit();
}

$username = $_SESSION['username'];

// --- 1. LẤY HOẶC TẠO HỘI THOẠI CHO USER NÀY ---
// Dựa theo Schema bạn gửi, mỗi user có 1 conversation duy nhất với Admin
$check_conv = mysqli_query($conn, "SELECT conv_id FROM conversations WHERE username = '$username' LIMIT 1");
if (mysqli_num_rows($check_conv) == 0) {
    mysqli_query($conn, "INSERT INTO conversations (username, status, isReadByUser) VALUES ('$username', 'new', 1)");
    $conv_id = mysqli_insert_id($conn);
} else {
    $conv_id = mysqli_fetch_assoc($check_conv)['conv_id'];
}

// --- 2. XỬ LÝ GỬI TIN NHẮN (Gửi từ User sang Admin) ---
if (isset($_POST['action']) && $_POST['action'] == 'send') {
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    if (!empty($message)) {
        // Lưu tin nhắn mới vào bảng chi tiết
        $sql_send = "INSERT INTO chat_messages (conv_id, sender, message) VALUES ('$conv_id', 'user', '$message')";
        if (mysqli_query($conn, $sql_send)) {
            // Cập nhật trạng thái: Admin chưa đọc (hiện chấm đỏ bên Admin)
            mysqli_query($conn, "UPDATE conversations SET isReadByAdmin = 0, isReadByUser = 1 WHERE conv_id = '$conv_id'");
        }
    }
    exit;
}

// --- 3. XỬ LÝ TẢI TIN NHẮN (Lấy dữ liệu thật từ Database) ---
if (isset($_GET['action']) && $_GET['action'] == 'load') {
    $sql_load = "SELECT * FROM chat_messages WHERE conv_id = '$conv_id' ORDER BY timestamp ASC";
    $result = mysqli_query($conn, $sql_load);
    
    // Nếu chưa có tin nhắn nào
    if (mysqli_num_rows($result) == 0) {
        echo '<div class="text-center text-muted small mt-5">Bắt đầu cuộc trò chuyện với Admin DocumentShare</div>';
        exit;
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $isMe = ($row['sender'] == 'user');
        $time = date('H:i', strtotime($row['timestamp']));
        
        if ($isMe) {
            // Bong bóng tin nhắn bên PHẢI (User gửi) - Màu xanh Zalo
            echo '
            <div class="d-flex justify-content-end mb-4">
                <div class="bg-primary text-white p-3 rounded-4 shadow-sm" style="max-width: 75%; border-bottom-right-radius: 5px !important;">
                    <div class="small">' . htmlspecialchars($row['message']) . '</div>
                    <div class="text-white-50 mt-1 text-end" style="font-size: 0.6rem;">' . $time . '</div>
                </div>
            </div>';
        } else {
            // Bong bóng tin nhắn bên TRÁI (Admin gửi) - Màu trắng
            echo '
            <div class="d-flex justify-content-start mb-4">
                <div class="bg-white p-3 rounded-4 shadow-sm border" style="max-width: 75%; border-bottom-left-radius: 5px !important;">
                    <div class="small text-dark">' . htmlspecialchars($row['message']) . '</div>
                    <div class="text-muted mt-1" style="font-size: 0.6rem;">' . $time . '</div>
                </div>
            </div>';
        }
    }
    
    // Khi User xem tin nhắn, tự động làm mất chấm đỏ bên phía User
    mysqli_query($conn, "UPDATE conversations SET isReadByUser = 1 WHERE conv_id = '$conv_id'");
    exit;
}
?>