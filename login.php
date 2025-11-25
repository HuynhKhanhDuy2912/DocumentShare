<?php require('header.php') ?>

<?php
// --- 1. CẤU HÌNH GOOGLE API ---
require_once 'vendor/autoload.php'; // Gọi thư viện bạn vừa cài

// Cấu hình Client ID và Secret (Lấy từ Google Console điền vào đây)
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

            echo "<script>window.location.assign('index.php');</script>";
            exit();
        } else {
            // --> Trường hợp B: Chưa có tài khoản -> Tự động Đăng ký
            // Tạo username lấy phần trước @ của email
            $new_username = explode('@', $email)[0];
            $default_role = 'user';
            // Tạo mật khẩu ngẫu nhiên (vì login Google ko cần pass)
            $random_pass = md5(uniqid(rand(), true));

            // Lưu vào DB
            $insert_sql = "INSERT INTO users (username, password, email, role, google_id) 
                           VALUES ('$new_username', '$random_pass', '$email', '$default_role', '$google_id')";

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
    $tendangnhap = $_REQUEST['txtUsername'];
    $matkhau = md5($_REQUEST['txtPassword']);
    $sql = "select * from users where username='$tendangnhap' and password='$matkhau'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $tendangnhap;
        $_SESSION['emailUser'] = $row['email'];
        $_SESSION['role'] = $row['role']; 
        echo "<script>
            alert('Bạn đã đăng nhập thành công!');
            window.location.assign('index.php');
        </script>";
    } else {
        echo "<script> alert('Tên đăng nhập hoặc email không đúng!');";
        echo "</script>";
    }
}
?>

<div class="login-box">
    <h3 class="text-center mb-4"><i class="fa fa-sign-in-alt"></i> Đăng nhập</h3>

    <form action="" method="post" name="f1">

        <div class="mb-3">
            <label class="form-label fw-bold">Tên đăng nhập</label>
            <input type="text" class="form-control" name="txtUsername" placeholder="Nhập tên đăng nhập..." required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Mật khẩu</label>
            <input type="password" class="form-control" name="txtPassword" placeholder="Nhập mật khẩu..." required>
        </div>

        <button type="submit" name="sbSubmit" class="btn btn-primary w-100 mt-2">
            Đăng nhập
        </button>

    </form>

    <div class="text-center mt-3">
        <p class="text-muted small">- Hoặc -</p>
        <a href="<?php echo $client->createAuthUrl(); ?>" class="btn btn-danger w-100">
            <i class="fab fa-google me-2"></i> Đăng nhập bằng Google
        </a>
    </div>
    <p class="text-center mt-3 mb-0">
        Chưa có tài khoản? <a href="signup.php">Đăng ký ngay</a>
    </p>
</div>

<?php require('footer.php') ?>