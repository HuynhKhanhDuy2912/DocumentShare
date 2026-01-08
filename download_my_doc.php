<?php
include "config.php";

/* =====================
1. KIỂM TRA ĐĂNG NHẬP
===================== */
if (!isset($_SESSION['username'])) {
    echo "<script>
        alert('Vui lòng đăng nhập để tải tài liệu!');
        window.location.href = 'login.php';
    </script>";
    exit;
}

$current_user = $_SESSION['username'];

/* =====================
2. KIỂM TRA ID
===================== */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Tài liệu không hợp lệ");
}

$document_id = (int)$_GET['id'];

/* =====================
3. LẤY FILE CỦA CHÍNH USER
===================== */
$sql = "SELECT file_path 
        FROM documents 
        WHERE document_id = ? AND username = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $document_id, $current_user);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Bạn không có quyền tải tài liệu này");
}

$doc = $result->fetch_assoc();
$filePath = "uploads/documents/" . $doc['file_path'];

/* =====================
4. KIỂM TRA FILE
===================== */
if (!file_exists($filePath)) {
    die("File không tồn tại trên hệ thống");
}

/* =====================
5. FORCE DOWNLOAD
===================== */
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
