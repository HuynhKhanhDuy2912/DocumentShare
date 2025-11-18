<?php require('header.php') ?>

<?php
if (isset($_SESSION['emailUser'])) {
    echo "<script> alert(' Login');";
    echo "window.location.assign('index.php');";
    echo "</script>";
}

// xử lý đăng nhập tại đây
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
        header("Location: index.php");
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
            <input type="text"
                class="form-control"
                name="txtUsername"
                placeholder="Nhập tên đăng nhập..."
                required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Mật khẩu</label>
            <input type="password"
                class="form-control"
                name="txtPassword"
                placeholder="Nhập mật khẩu..."
                required>
        </div>

        <button type="submit" name="sbSubmit" class="btn btn-primary w-100 mt-2">
            Đăng nhập
        </button>

    </form>

    <p class="text-center mt-3 mb-0">
        Chưa có tài khoản? <a href="signup.php">Đăng ký ngay</a>
    </p>
</div>

<?php require('footer.php') ?>