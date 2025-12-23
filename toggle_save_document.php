<?php
require "config.php";

header('Content-Type: application/json; charset=utf-8');

// Chưa đăng nhập
if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "login"]);
    exit;
}

if (!isset($_POST['document_id'])) {
    echo json_encode(["status" => "error"]);
    exit;
}

$username = $_SESSION['username'];
$document_id = (int)$_POST['document_id'];

$check = mysqli_query($conn, "
    SELECT id FROM saved_documents
    WHERE username = '$username' AND document_id = $document_id
    LIMIT 1
");

if ($check && mysqli_num_rows($check) > 0) {
    mysqli_query($conn, "
        DELETE FROM saved_documents
        WHERE username = '$username' AND document_id = $document_id
    ");
    echo json_encode(["status" => "unsaved"]);
} else {
    mysqli_query($conn, "
        INSERT INTO saved_documents (username, document_id)
        VALUES ('$username', $document_id)
    ");
    echo json_encode(["status" => "saved"]);
}
