<?php
session_start();
include "config.php";

// 1. Kiểm tra quyền Admin (Giả sử role 1 là admin như logic trước đó)
if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
    die("Bạn không có quyền thực hiện thao tác này.");
}

// 2. Lấy dữ liệu từ URL
$action = $_GET['action'] ?? '';
$comment_id = (int)($_GET['id'] ?? 0);
$doc_id = (int)($_GET['doc_id'] ?? 0);

if ($action === 'toggle_status' && $comment_id > 0) {
    // 3. Lấy trạng thái hiện tại của bình luận
    $query = mysqli_query($conn, "SELECT status FROM comments WHERE comment_id = $comment_id LIMIT 1");
    $comment = mysqli_fetch_assoc($query);

    if ($comment) {
        // Đảo ngược trạng thái: 0 thành 1, 1 thành 0
        $newStatus = ($comment['status'] == 0) ? 1 : 0;

        // 4. Cập nhật vào cơ sở dữ liệu
        $updateSql = "UPDATE comments SET status = $newStatus WHERE comment_id = $comment_id";
        
        if (mysqli_query($conn, $updateSql)) {
            // Quay lại trang chi tiết tài liệu sau khi thành công
            header("Location: document_detail.php?id=" . $doc_id);
            exit;
        } else {
            echo "Lỗi cập nhật: " . mysqli_error($conn);
        }
    }
} else {
    echo "Yêu cầu không hợp lệ.";
}
?>