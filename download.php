<?php
include "config.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Tài liệu không tồn tại");
}

$document_id = (int)$_GET['id'];

// Lấy thông tin file
$sql = "SELECT file_path FROM documents WHERE document_id = $document_id AND status = 0 LIMIT 1";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    die("Không tìm thấy tài liệu");
}

$doc = mysqli_fetch_assoc($result);
$filePath = "uploads/documents/" . $doc['file_path'];

if (!file_exists($filePath)) {
    die("File không tồn tại");
}

/* TĂNG LƯỢT TẢI */
mysqli_query($conn, "UPDATE documents SET downloads = downloads + 1 WHERE document_id = $document_id");

/* FORCE DOWNLOAD */
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
