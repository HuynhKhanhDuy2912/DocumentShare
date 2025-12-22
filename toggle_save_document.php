<?php
require "config.php";

header('Content-Type: application/json; charset=utf-8');
error_reporting(0); // CHẶN warning phá JSON

// Chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "login"]);
    exit;
}

if (!isset($_POST['document_id'])) {
    echo json_encode(["status" => "error"]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$document_id = (int)$_POST['document_id'];

// Kiểm tra đã lưu chưa
$check = mysqli_query($conn, "
    SELECT id FROM saved_documents
    WHERE user_id = $user_id AND document_id = $document_id
    LIMIT 1
");

if ($check && mysqli_num_rows($check) > 0) {
    // Đã lưu → bỏ lưu
    mysqli_query($conn, "
        DELETE FROM saved_documents
        WHERE user_id = $user_id AND document_id = $document_id
    ");
    echo json_encode(["status" => "unsaved"]);
} else {
    // Chưa lưu → lưu
    mysqli_query($conn, "
        INSERT INTO saved_documents (user_id, document_id)
        VALUES ($user_id, $document_id)
    ");
    echo json_encode(["status" => "saved"]);
}
