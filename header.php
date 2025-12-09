<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require("config.php");

// Kiểm tra tài khoản đăng nhập
if (isset($_SESSION['emailUser'])) {
    $email = $_SESSION['emailUser'];
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['avatar'] = $user['avatar'];
    } else {
        session_destroy();
        header("Location: login.php");
        exit();
    }
}
?>

<?php
// Lấy danh sách category đang hiển thị
$sql = "SELECT category_id, name FROM categories WHERE status = 0 ORDER BY category_id ASC";
$result = mysqli_query($conn, $sql);

$categories = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
}
?>



<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocumentShare</title>
    <link rel="icon" href="assets/img/logo.png">
    <!-- Boostrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/custom.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-white bg-white shadow-sm fixed-top" style="height: 80px;">
        <div class="container-fluid px-lg-5">

            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="assets/img/logo.png" alt="Logo" style="height: 40px; margin-right: 10px;">
                <div class="d-flex flex-column">
                    <span
                        style="font-weight: 700; font-size: 18px; line-height: 1.2; color: #0d6efd;">DocumentShare</span>
                    <span style="font-size: 12px; color: #6c757d; font-weight: 500;">Học Tập & Chia Sẻ</span>
                </div>
            </a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarMenu">
                <span class="navbar-toggler-icon"><i class="fas fa-bars" style="color:#333; font-size:24px;"></i></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMenu">

                <!-- MENU TRÁI -->
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="fa fa-home mr-1"></i> Trang chủ</a></li>
                    <li class="nav-item mega-dropdown">
                        <a class="nav-link" href="danhmuc.php">
                            <i class="fa fa-tag mr-1"></i> Danh mục tài liệu
                        </a>

                        <!-- Mega Menu -->
                        <div class="mega-menu">
                            <div class="mega-grid">

                                <?php
                                $cols_per_row = 4;
                                $count = 0;
                                $categories_count = count($categories);

                                for ($i = 0; $i < $categories_count; $i++) {
                                    // Mở row mới nếu là cột đầu tiên của hàng
                                    if ($count % $cols_per_row == 0) {
                                        echo '<div class="mega-row">';
                                    }

                                    $category = $categories[$i];
                                    echo '<div class="mega-column">';
                                    echo '<h4 class="mega-title">' . htmlspecialchars($category['name']) . '</h4>';
                                    echo '</div>';

                                    $count++;

                                    // Đóng row khi đủ 4 cột hoặc hết dữ liệu
                                    if ($count % $cols_per_row == 0 || $i == $categories_count - 1) {
                                        echo '</div>';
                                    }
                                }
                                ?>


                            </div>
                        </div>

                    </li>
                    <li class="nav-item"><a class="nav-link" href="contact.php"><i class="fa fa-envelope mr-1"></i> Liên hệ</a></li>
                </ul>

                <!-- Ô tìm kiếm -->
                <form class="form-inline search-box mx-auto" action="search.php" method="get">
                    <div class="input-group">
                        <input class="form-control" type="search" name="q" placeholder="Tìm kiếm tài liệu...">
                        <div class="input-group-append">
                            <button class="btn btn-light border" type="submit">
                                <i class="fa fa-search text-muted"></i>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- MENU PHẢI (Đăng nhập / Đăng ký hoặc User) -->
                <ul class="navbar-nav ml-auto align-items-center">

                    <?php if (isset($_SESSION["emailUser"])): ?>
                        <li class="nav-item" style="margin-right: 40px">
                            <a class="btn btn-primary btn-sm btn-rounded text-white px-4 py-2" href="upload.php">
                                <i class="fa fa-upload mr-2"></i> Đăng tải tài liệu
                            </a>
                        </li>

                        <li class="nav-item dropdown user-dropdown" style="margin-right: 1.5rem;">
                            <a class="nav-link dropdown-toggle font-weight-bold text-dark d-flex align-items-center" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                                <?php
                                // Kiểm tra avatar
                                $avatarFile = isset($_SESSION['avatar']) ? $_SESSION['avatar'] : '';
                                $avatarPath = "uploads/" . $avatarFile;

                                // Nếu có tên file và file thực sự tồn tại trên server
                                if (!empty($avatarFile) && file_exists($avatarPath)) {
                                    echo '<img src="' . $avatarPath . '" alt="Avatar" class="nav-user-avatar mr-2">';
                                } else {
                                    // Nếu không có thì dùng Icon mặc định
                                    echo '<i class="fa fa-user-circle fa-2x text-primary mr-2"></i>';
                                }
                                ?>

                                <span class="text-truncate" style="max-width: 200px;">
                                    <?php echo $_SESSION['username']; ?>
                                </span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right shadow border-0" aria-labelledby="userDropdown" style="right: -36px;">
                                <a class="dropdown-item" href="profile.php"><i class="fa fa-id-card mr-2 text-muted"></i> Thông tin tài khoản</a>
                                <a class="dropdown-item" href="saved_documents.php"><i class="fa fa-bookmark mr-2 text-muted"></i> Tài liệu đã lưu</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="logout.php"><i class="fa fa-sign-out-alt mr-2"></i> Đăng xuất</a>
                            </div>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="btn btn-primary btn-sm px-3 btn-rounded" href="login.php">Đăng nhập</a></li>
                        <li class="nav-item ml-2"><a class="nav-link font-weight-bold" href="signup.php">Đăng ký</a></li>
                    <?php endif; ?>

                </ul>
            </div>

        </div>
    </nav>

    <div style="height: 100px;"></div>

    <div class="container">
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>