<?php
session_start();
include "config.php";

if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "login"]);
    exit;
}

$username = $_SESSION['username'];
$docId = (int)($_POST['document_id'] ?? 0);

if ($docId <= 0) {
    echo json_encode(["status" => "error"]);
    exit;
}

// Kiểm tra đã lưu chưa
$check = mysqli_query($conn, "
    SELECT 1 FROM saved_documents
    WHERE username = '$username' AND document_id = $docId
");

if (mysqli_num_rows($check) > 0) {
    // BỎ LƯU
    mysqli_query($conn, "
        DELETE FROM saved_documents
        WHERE username = '$username' AND document_id = $docId
    ");
    echo json_encode(["status" => "unsaved"]);
} else {
    // LƯU
    mysqli_query($conn, "
        INSERT INTO saved_documents(username, document_id)
        VALUES('$username', $docId)
    ");
    echo json_encode(["status" => "saved"]);
}
