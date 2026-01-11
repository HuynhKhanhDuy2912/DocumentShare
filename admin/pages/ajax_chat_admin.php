<?php
require("../../config.php");

// 1. Load danh sách tất cả các User đang có hội thoại (chỉ hiện người đã nhắn tin)
if ($_GET['action'] == 'load_list') {
    $sql = "SELECT c.*, u.fullname, u.avatar, 
            (SELECT message FROM chat_messages WHERE conv_id = c.conv_id ORDER BY timestamp DESC LIMIT 1) as last_msg,
            (SELECT timestamp FROM chat_messages WHERE conv_id = c.conv_id ORDER BY timestamp DESC LIMIT 1) as last_time
            FROM conversations c 
            JOIN users u ON c.username = u.username 
            WHERE EXISTS (SELECT 1 FROM chat_messages cm WHERE cm.conv_id = c.conv_id)
            ORDER BY c.isReadByAdmin ASC, last_time DESC";

    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $activeClass = ($row['isReadByAdmin'] == 0) ? 'bg-warning-light' : '';
        $time = $row['last_time'] ? date('H:i', strtotime($row['last_time'])) : '';

        echo '<button onclick="openChat(' . $row['conv_id'] . ')" class="list-group-item list-group-item-action p-3 border-0 d-flex align-items-center ' . $activeClass . '">
                <img src="../uploads/users/' . $row['avatar'] . '" class="rounded-circle me-3 border" width="45" height="45" onerror="this.src=\'../assets/img/default-avatar.png\'">
                <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold" style="font-size: 0.95rem;">' . $row['fullname'] . '</span>
                        <span class="text-muted" style="font-size: 0.65rem;">' . $time . '</span>
                    </div>
                    <div class="text-muted small text-truncate">' . ($row['last_msg'] ?? 'Hội thoại mới') . '</div>
                </div>
                ' . ($row['isReadByAdmin'] == 0 ? '<span class="badge bg-danger rounded-circle p-1 ms-2" style="width: 8px; height: 8px;"> </span>' : '') . '
            </button>';
    }
    exit;
}

// 2. Mở khung chat chi tiết của một User
if ($_GET['action'] == 'open_chat') {
    $conv_id = (int)$_GET['conv_id'];
    // Đánh dấu Admin đã xem cuộc hội thoại này
    mysqli_query($conn, "UPDATE conversations SET isReadByAdmin = 1 WHERE conv_id = $conv_id");

    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT u.fullname FROM conversations c JOIN users u ON c.username = u.username WHERE c.conv_id = $conv_id"));
?>
    <div class="p-3 border-bottom d-flex align-items-center bg-white shadow-sm">
        <h6 class="mb-0 fw-bold small">Đang hỗ trợ: <?= $user['fullname'] ?></h6>
    </div>
    <div class="flex-grow-1 p-4 overflow-auto bg-light d-flex flex-column" id="admin-chat-body"></div>
    <div class="p-3 border-top bg-white">
        <div class="input-group align-items-center bg-light rounded-pill px-3 py-1">
            <input type="text" id="admin-chat-input" class="form-control border-0 bg-transparent shadow-none" placeholder="Nhập tin nhắn trả lời...">
            <button class="btn btn-link text-primary p-0 ms-2" onclick="sendAdminMsg()"><i class="fas fa-paper-plane fa-lg"></i></button>
        </div>
    </div>
<?php
    exit;
}

// 3. Load tin nhắn trong một hội thoại
if ($_GET['action'] == 'load_messages') {
    $conv_id = (int)$_GET['conv_id'];
    $res = mysqli_query($conn, "SELECT * FROM chat_messages WHERE conv_id = $conv_id ORDER BY timestamp ASC");
    while ($row = mysqli_fetch_assoc($res)) {
        $isAdmin = ($row['sender'] == 'admin');
        $align = $isAdmin ? 'justify-content-end' : 'justify-content-start';
        $bg = $isAdmin ? 'bg-primary text-white' : 'bg-white border text-dark';

        echo '<div class="d-flex ' . $align . ' mb-4">
                <div class="' . $bg . ' p-3 rounded-4 shadow-sm" style="max-width: 75%;">
                    <div class="small">' . htmlspecialchars($row['message']) . '</div>
                    <div class="mt-1 ' . ($isAdmin ? 'text-white-50' : 'text-muted') . '" style="font-size: 0.6rem;">' . date('H:i', strtotime($row['timestamp'])) . '</div>
                </div>
            </div>';
    }
    exit;
}

// 4. Admin gửi tin nhắn
if ($_POST['action'] == 'send') {
    $conv_id = (int)$_POST['conv_id'];
    $msg = mysqli_real_escape_string($conn, $_POST['message']);
    mysqli_query($conn, "INSERT INTO chat_messages (conv_id, sender, message) VALUES ($conv_id, 'admin', '$msg')");
    // User chưa đọc tin của Admin -> Hiện chấm đỏ bên phía User
    mysqli_query($conn, "UPDATE conversations SET isReadByUser = 0, isReadByAdmin = 1 WHERE conv_id = $conv_id");
    exit;
}
?>