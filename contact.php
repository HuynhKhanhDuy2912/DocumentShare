<?php
include 'config.php';
include 'header.php';

if (!isset($_SESSION['username'])) {
    echo "<script>window.location.assign('login.php');</script>";
    exit();
}

$username = $_SESSION['username'];

// Kiểm tra hội thoại hiện tại, nếu chưa có thì tạo mới
$sql_check = "SELECT conv_id FROM conversations WHERE username = '$username' LIMIT 1";
$res_check = mysqli_query($conn, $sql_check);

if (mysqli_num_rows($res_check) == 0) {
    mysqli_query($conn, "INSERT INTO conversations (username, status, isReadByUser) VALUES ('$username', 'new', 1)");
    $conv_id = mysqli_insert_id($conn);
} else {
    $conv_id = mysqli_fetch_assoc($res_check)['conv_id'];
}
?>

<div class="container-fluid chat-wrapper bg-white shadow-sm rounded-4 overflow-hidden" style="height: 500px; margin-top: 95px; max-width: 1200px;">
    <div class="row h-100 g-0">
        <div class="col-md-4 col-lg-3 border-end h-100 d-flex flex-column bg-light">
            <div class="p-3 border-bottom bg-white">
                <h6 class="mb-0 fw-bold"><i class="fas fa-comments me-2"></i>Tin nhắn</h6>
            </div>
            <div class="list-group list-group-flush overflow-auto flex-grow-1">
                <div class="list-group-item list-group-item-action p-3 border-0 d-flex align-items-center active">
                    <img src="assets/img/logo.jpg" class="rounded-circle me-3 border" width="45" height="45">
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold small">Admin DocumentShare</span>
                            <span id="last-time" class="text-muted" style="font-size: 0.65rem;"></span>
                        </div>
                        <div id="last-msg" class="text-white-50 small text-truncate">Đang tải...</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8 col-lg-9 h-100 d-flex flex-column bg-white">
            <div class="p-3 border-bottom d-flex align-items-center bg-white shadow-sm">
                <img src="assets/img/logo.jpg" class="rounded-circle me-2 border" width="40" height="40">
                <div>
                    <h6 class="mb-0 fw-bold small">Admin DocumentShare <i class="fas fa-check-circle text-primary" style="font-size: 0.7rem;"></i></h6>
                    <span class="text-success small" style="font-size: 0.65rem;">Đang trực tuyến</span>
                </div>
            </div>

            <div class="flex-grow-1 p-4 overflow-auto bg-light d-flex flex-column" id="chat-body">
            </div>

            <div class="p-3 border-top bg-white">
                <div class="input-group align-items-center bg-light rounded-pill px-3 py-1">
                    <input type="text" id="chat-input" class="form-control border-0 bg-transparent shadow-none" placeholder="Nhập tin nhắn hỗ trợ...">
                    <button class="btn btn-link text-primary p-0 ms-2" id="send-btn">
                        <i class="fas fa-paper-plane fa-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>