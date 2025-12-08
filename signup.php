<?php require("header.php"); ?>

<?php
if (isset($_SESSION['emailUser'])) {
    echo "<script>
        alert('Bạn đã đăng nhập rồi!');
        window.location.assign('index.php');
    </script>";
    exit();
}

if (isset($_POST['sbDangky'])) {

    // LẤY ĐÚNG TÊN FIELD TRONG HTML
    $tendangnhap = $_POST['txtTendangnhap'];
    $matkhau     = md5($_POST['txtMatkhau']);
    $tendaydu    = $_POST['txtTendaydu'];
    $email       = $_POST['txtEmail'];

    $tm = "uploads/";
    $fileName = basename($_FILES["fileAnh"]["name"]);
    $targetFilePath = $tm . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    // Kiểm tra trùng username
    $sqlcheck1 = "SELECT * FROM users WHERE username = '$tendangnhap'";
    $result1 = $conn->query($sqlcheck1);

    // Kiểm tra trùng email
    $sqlcheck2 = "SELECT * FROM users WHERE email = '$email'";
    $result2 = $conn->query($sqlcheck2);

    if ($result1->num_rows > 0) {
        echo "<script>alert('Tên đăng nhập đã tồn tại');</script>";
    } elseif ($result2->num_rows > 0) {
        echo "<script>alert('Email đã tồn tại');</script>";
    } else {

        // Nếu có upload avatar
        if (!empty($_FILES["fileAnh"]["name"])) {
            $allowTypes = ['jpg', 'png', 'jpeg', 'gif'];

            if (in_array($fileType, $allowTypes)) {

                if (move_uploaded_file($_FILES["fileAnh"]["tmp_name"], $targetFilePath)) {

                    $sql = "INSERT INTO users(username, password, fullname, email, avatar, role, status)
                            VALUES('$tendangnhap', '$matkhau', '$tendaydu', '$email', '$fileName', 0, 0)";

                    if ($conn->query($sql)) {
                        echo "<script>
                            alert('Đăng ký tài khoản thành công!');
                            window.location.assign('login.php');
                        </script>";
                    }

                } else {
                    echo "<script>alert('Lỗi upload ảnh! Kiểm tra quyền thư mục');</script>";
                }

            } else {
                echo "<script>alert('Chỉ chấp nhận file ảnh JPG, PNG, JPEG, GIF');</script>";
            }

        } else {

            // Không upload avatar
            $sql = "INSERT INTO users(username, password, fullname, email, role, status)
                    VALUES('$tendangnhap', '$matkhau', '$tendaydu', '$email', 0, 0)";

            if ($conn->query($sql)) {
                echo "<script>
                    alert('Đăng ký tài khoản thành công!');
                    window.location.assign('login.php');
                </script>";
            }
        }
    }
}
?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 90vh;">
    <div class="register-box">

        <div class="text-center mb-4">
            <div class="mb-3 text-primary">
                <a href="index.php">
                    <img src="assets/img/logo.png" style="width: 64px; height: 64px; object-fit: contain;">
                </a>
            </div>
            <h3 class="fw-bold text-dark">Đăng ký tài khoản</h3>
            <p class="text-muted small">Tham gia ngay cộng đồng DocumentShare</p>
        </div>

        <form action="" method="post" name="f1" onsubmit="return validateRegisterForm();" enctype="multipart/form-data">

            <div class="form-floating mb-3">
                <input type="text" class="form-control" name="txtTendangnhap" id="txtTendangnhap" placeholder="Tên đăng nhập" required>
                <label for="txtTendangnhap"><i class="fa fa-user me-2"></i> Tên đăng nhập</label>
            </div>

            <div class="form-floating mb-3">
                <input type="text" class="form-control" name="txtTendaydu" id="txtTendaydu" placeholder="Họ và tên">
                <label for="txtTendaydu"><i class="fa fa-id-card me-2"></i> Tên đầy đủ</label>
            </div>

            <div class="form-floating mb-3">
                <input type="email" class="form-control" name="txtEmail" id="txtEmail" placeholder="Email" required>
                <label for="txtEmail"><i class="fa fa-envelope me-2"></i> Email</label>
            </div>

            <div class="mb-3 password-wrapper">
                <div class="form-floating">
                    <input type="password" class="form-control" name="txtMatkhau" id="txtMatkhau" placeholder="Mật khẩu" required>
                    <label for="txtMatkhau"><i class="fa fa-lock me-2"></i> Mật khẩu</label>
                </div>
                <i class="fa fa-eye toggle-password" onclick="togglePassword('txtMatkhau',this)"></i>
            </div>

            <div class="mb-3 password-wrapper">
                <div class="form-floating">
                    <input type="password" class="form-control" name="txtNLMatkhau" id="txtNLMatkhau" placeholder="Nhập lại mật khẩu" required>
                    <label for="txtNLMatkhau"><i class="fa fa-lock me-2"></i> Xác nhận mật khẩu</label>
                </div>
                <i class="fa fa-eye toggle-password" onclick="togglePassword('txtNLMatkhau', this)"></i>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold small text-muted mb-2">Ảnh đại diện</label>
                <div class="upload-container">
                    <input type="file" id="fileAnh" name="fileAnh" class="d-none" onchange="updateFileName(this)">

                    <label for="fileAnh" class="btn-upload">
                        <i class="fa fa-cloud-upload-alt mr-2"></i> Chọn ảnh...
                    </label>

                    <span id="fileName" class="file-name text-muted">Chưa chọn ảnh nào</span>
                </div>
            </div>

            <button type="submit" name="sbDangky" class="btn btn-primary w-100 mb-3">
                <i class="fa fa-user-plus me-2"></i> Đăng ký
            </button>

        </form>

        <div class="divider">HOẶC</div>

        <?php if (isset($client)): ?>
            <a href="<?php echo $client->createAuthUrl(); ?>" class="btn w-100 btn-google">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" viewBox="0 0 48 48">
                    <g>
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.13 5.51C44.38 38.37 46.98 32.07 46.98 24.55z"></path>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.13-5.51c-2.18 1.45-5.04 2.3-8.76 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path>
                        <path fill="none" d="M0 0h48v48H0z"></path>
                    </g>
                </svg>
                Đăng ký với Google
            </a>
        <?php else: ?>
            <a href="login.php" class="btn w-100 btn-google">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" viewBox="0 0 48 48">
                    <g>
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.13 5.51C44.38 38.37 46.98 32.07 46.98 24.55z"></path>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.13-5.51c-2.18 1.45-5.04 2.3-8.76 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path>
                        <path fill="none" d="M0 0h48v48H0z"></path>
                    </g>
                </svg>
                Đăng ký với Google
            </a>
        <?php endif; ?>

        <p class="text-center mt-4 mb-0 small text-muted">
            Bạn đã có tài khoản? <a href="login.php" class="fw-bold text-decoration-none">Đăng nhập ngay</a>
        </p>
    </div>

    <?php require("footer.php"); ?>