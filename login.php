<?php require('header.php') ?>

<?php
// --- 1. CẤU HÌNH GOOGLE API ---
require_once 'vendor/autoload.php'; // Gọi thư viện bạn vừa cài

// Cấu hình Client ID và Secret
$clientID = '483642035448-o4gcrpg5vd9knnu8av8j70mip7sklbul.apps.googleusercontent.com';
$clientSecret = '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';
$redirectUri = 'http://localhost/chiasetailieu/login.php';

$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);  
$client->addScope("email");
$client->addScope("profile");

// --- 2. XỬ LÝ KHI NGƯỜI DÙNG BẤM ĐĂNG NHẬP GOOGLE ---
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();

        // Lấy thông tin
        $email = $google_account_info->email;
        $name = $google_account_info->name;
        $google_id = $google_account_info->id;

        // Kiểm tra email đã tồn tại trong database chưa
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // --> Trường hợp A: Đã có tài khoản -> Đăng nhập luôn
            $row = $result->fetch_assoc();
            $_SESSION['username'] = $row['username'];
            $_SESSION['emailUser'] = $row['email'];
            $_SESSION['role'] = $row['role'];

            // Cập nhật google_id nếu chưa có
            if (empty($row['google_id'])) {
                $conn->query("UPDATE users SET google_id = '$google_id' WHERE email = '$email'");
            }

            if ($row['role'] == 1) {
                // Nếu là Admin -> Chuyển vào trang quản trị
                echo "<script>window.location.assign('admin/index.php');</script>";
            } else {
                // Nếu là User -> Chuyển vào trang chủ
                echo "<script>window.location.assign('index.php');</script>";
            }
            exit();
        } else {
            // --> Trường hợp B: Chưa có tài khoản -> Tự động Đăng ký
            $new_username = explode('@', $email)[0]; // Tạo username lấy phần trước @ của email
            $default_role = 0;            
            $random_pass = md5(uniqid(rand(), true)); 

            // Lưu vào DB
            $insert_sql = "INSERT INTO users (username, password, email, role, google_id, status) 
                           VALUES ('$new_username', '$random_pass', '$email', '$default_role', '$google_id', 0)";

            if ($conn->query($insert_sql) === TRUE) {
                $_SESSION['username'] = $new_username;
                $_SESSION['emailUser'] = $email;
                $_SESSION['role'] = $default_role;
                echo "<script>window.location.assign('index.php');</script>";
                exit();
            } else {
                echo "<script>alert('Lỗi tạo tài khoản: " . $conn->error . "');</script>";
            }
        }
    }
}

// --- 3. LOGIC CŨ CỦA BẠN (KIỂM TRA SESSION) ---
if (isset($_SESSION['emailUser'])) {
    echo "<script> alert('Bạn đã đăng nhập rồi!');";
    echo "window.location.assign('index.php');";
    echo "</script>";
}

// --- 4. LOGIC CŨ CỦA BẠN (XỬ LÝ FORM THƯỜNG) ---
if (isset($_REQUEST['sbSubmit'])) {
    $tendangnhap = $_REQUEST['txtTendangnhap'];
    $matkhau = md5($_REQUEST['txtMatkhau']);
    $sql = "select * from users where username='$tendangnhap' and password='$matkhau'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $tendangnhap;
        $_SESSION['emailUser'] = $row['email'];
        $_SESSION['role'] = $row['role'];
        echo "<script>
            alert('Bạn đã đăng nhập thành công!');
        </script>";
        if ($row['role'] == 1) {
            // Nếu là Admin -> Chuyển vào trang admin
            echo "<script>window.location.assign('admin/index.php');</script>";
        } else {
            // Nếu là User -> Chuyển vào trang chủ
            echo "<script>window.location.assign('index.php');</script>";
        }
    } else {
        echo "<script> alert('Tên đăng nhập hoặc mật khẩu không đúng!');";
        echo "</script>";
    }
}
?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="login-card">
        <div class="text-center mb-4 login-header">
            <div class="mb-3 text-primary logo-login">
                <a href="index.php">
                    <img src="assets/img/logo.png" style="width: 64px; height: 64px; object-fit: contain;">
                </a>
            </div>
            <h3>Đăng Nhập</h3>
            <p class="text-muted small">Chào mừng bạn quay đến với DocumentShare</p>
        </div> 

        <form action="" method="post" name="f1">
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="txtTendangnhap" name="txtTendangnhap" placeholder="Tên đăng nhập" required>
                <label for="txtTendangnhap"><i class="fa fa-user me-2"></i> Tên đăng nhập</label>
            </div>

            <div class="mb-3 password-wrapper">
                <div class="form-floating">
                    <input type="password" class="form-control" id="txtMatkhau" name="txtMatkhau" placeholder="Mật khẩu" required>
                    <label for="txtMatkhau"><i class="fa fa-lock me-2"></i> Mật khẩu</label>
                </div>
                <i class="fa fa-eye toggle-password" onclick="togglePassword('txtMatkhau',this)"></i>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="rememberMe">
                    <label class="form-check-label small text-muted" for="rememberMe">Ghi nhớ</label>
                </div>
                <a href="#" class="text-decoration-none small">Quên mật khẩu?</a>
            </div>

            <button type="submit" name="sbSubmit" class="btn btn-primary w-100 mb-3">
                Đăng nhập
            </button>
        </form>

        <div class="divider small">HOẶC</div>

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
            Đăng nhập bằng Google
        </a>

        <p class="text-center mt-4 mb-0 small text-muted">
            Bạn chưa có tài khoản? <a href="signup.php" class="fw-bold text-decoration-none">Đăng ký ngay</a>
        </p>
    </div>

<?php require('footer.php') ?>