<?php
include 'config.php';
include 'header.php';

$isLoggedIn = isset($_SESSION['username']);
$conv_id = 0; // Mặc định

if ($isLoggedIn) {
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
}
?>

<div class="container-fluid chat-wrapper bg-white shadow-sm rounded-4 overflow-hidden" style="height: auto; margin-top: 95px; max-width: 1200px;">

    <div class="container-fluid mb-4" style="max-width: 1200px; margin-top: 20px;">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="d-flex align-items-center bg-white p-3 shadow-sm rounded-4 h-100 border border-light">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 50px; height: 50px; background-color: #ff3366;">
                        <i class="fas fa-map-marker-alt text-white fs-5"></i>
                    </div>
                    <div class="overflow-hidden">
                        <p class="mb-0 fw-bold small">Trụ sở chính</p>
                        <p class="mb-0 text-muted small text-truncate">126 Nguyễn Thiện Thành, Trà Vinh</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="d-flex align-items-center bg-white p-3 shadow-sm rounded-4 h-100 border border-light">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 50px; height: 50px; background-color: #ff9900;">
                        <i class="fas fa-phone-alt text-white fs-5"></i>
                    </div>
                    <div class="overflow-hidden">
                        <p class="mb-0 fw-bold small">Hotline hỗ trợ</p>
                        <p class="mb-0 text-muted small text-truncate">0999 999 999</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="d-flex align-items-center bg-white p-3 shadow-sm rounded-4 h-100 border border-light">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 50px; height: 50px; background-color: #007bff;">
                        <i class="fas fa-envelope text-white fs-5"></i>
                    </div>
                    <div class="overflow-hidden">
                        <p class="mb-0 fw-bold small">Email liên hệ</p>
                        <p class="mb-0 text-muted small text-truncate">contact@documentshare.vn</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-0" style="height: 450px;">
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
                    <input type="text" id="chat-input"
                        class="form-control border-0 bg-transparent shadow-none"
                        placeholder="<?php echo $isLoggedIn ? 'Nhập tin nhắn hỗ trợ...' : 'Vui lòng đăng nhập để gửi tin nhắn...'; ?>"
                        <?php echo !$isLoggedIn ? 'disabled' : ''; ?>>

                    <button class="btn btn-link text-primary p-0 ms-2"
                        id="send-btn"
                        <?php echo !$isLoggedIn ? 'disabled style="color: #ccc !important;"' : ''; ?>>
                        <i class="fas fa-paper-plane fa-lg"></i>
                    </button>
                </div>

                <?php if (!$isLoggedIn): ?>
                    <div class="text-center mt-2">
                        <small class="text-muted">Bạn cần <a href="login.php" class="text-primary fw-bold">đăng nhập</a> để bắt đầu trò chuyện.</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>