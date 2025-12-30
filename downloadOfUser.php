<?php
include 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Yêu cầu không hợp lệ");
}

$id = (int)$_GET['id'];

// Lấy đường dẫn file từ database
$sql = "SELECT file_path, shares FROM document_uploads WHERE document_id = $id AND status = 1 LIMIT 1";
$result = mysqli_query($conn, $sql);
$doc = mysqli_fetch_assoc($result);

if (!$doc) {
    die("Không tìm thấy tài liệu");
}

$filePath = $doc['file_path']; // Đường dẫn đã lưu trong DB (ví dụ: uploads/documents/abc.pdf)

if (file_exists($filePath)) {
    // 1. TĂNG LƯỢT TẢI (Trong bảng của bạn cột lượt tải là 'shares')
    mysqli_query($conn, "UPDATE document_uploads SET shares = shares + 1 WHERE document_id = $id");

    // 2. ÉP TẢI FILE
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    
    ob_clean();
    flush();
    readfile($filePath);
    exit;
} else {
    die("File không tồn tại trên máy chủ.");
}
?>