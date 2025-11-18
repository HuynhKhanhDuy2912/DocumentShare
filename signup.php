<?php require("header.php"); ?>

<?php
if (isset($_SESSION['emailUser'])) {
    echo "<script> alert('Bạn đã đăng nhập rồi!');";
    echo "window.location.assign('index.php');";
    echo "</script>";
}

if (isset($_REQUEST['sbDangky'])) {
    $tendangnhap = $_REQUEST['txtTendangnhap'];
    $matkhau = md5($_REQUEST['txtMatkhau']);
    $tendaydu = $_REQUEST['txtTendaydu'];
    $email = $_REQUEST['txtEmail'];
    $tm = "uploads/";
    $fileName = basename($_FILES["fileAnh"]["name"]);
    $targetFilePath = $tm . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    // Kiểm tra trùng lặp
    $sqlcheck1 = "select * from users where username='$tendangnhap'";
    $result1 = $conn->query($sqlcheck1);
    $sqlcheck2 = "select * from users where email='$email'";
    $result2 = $conn->query($sqlcheck2);

    if ($result1->num_rows > 0) {
        echo "<script> alert('Tên đăng nhập đã tồn tại'); </script>";
    } else if ($result2->num_rows > 0) {
        echo "<script> alert('Email đã tồn tại'); </script>";
    } else {
        // Xử lý upload ảnh
        if (!empty($_FILES["fileAnh"]["name"])) {
            $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
            if (in_array($fileType, $allowTypes)) {
                if (move_uploaded_file($_FILES["fileAnh"]["tmp_name"], $targetFilePath)) {

                    $sql = "insert into users(username, password, fullname, email, avatar, role) 
                            values('$tendangnhap', '$matkhau', '$tendaydu', '$email', '$fileName', 0)";

                    if ($conn->query($sql)) {
                        echo "<script> alert('Đăng ký tài khoản thành công!'); window.location.assign('login.php'); </script>";
                    }
                } else {
                    echo "<script> alert('Upload tập tin avatar bị lỗi (Kiểm tra quyền thư mục)'); </script>";
                }
            } else {
                echo "<script> alert('Chỉ chấp nhận file ảnh (JPG, PNG, JPEG, GIF)'); </script>";
            }
        } else {
            $sql = "insert into users(username, password, fullname, email, role) 
                    values('$tendangnhap', '$matkhau', '$tendaydu', '$email', 0)";
            if ($conn->query($sql)) {
                echo "<script> alert('Đăng ký tài khoản thành công!'); window.location.assign('login.php'); </script>";
            }
        }
    }
}
?>

<div class="register-box">
    <h3 class="text-center mb-4"><i class="fa fa-user-plus"></i> Đăng ký tài khoản</h3>

    <form action="" method="post" name="f1" onsubmit="return validateRegisterForm();" enctype="multipart/form-data">

        <div class="mb-3">
            <label class="form-label">Tên đăng nhập</label>
            <input type="text" class="form-control" name="txtTendangnhap" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Mật khẩu</label>
            <input type="password" class="form-control" name="txtMatkhau" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Nhập lại mật khẩu</label>
            <input type="password" class="form-control" name="txtreMatkhau" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Tên đầy đủ</label>
            <input type="text" class="form-control" name="txtTendaydu">
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="txtEmail" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Ảnh đại diện</label>
            <div class="upload-container">
                <input type="file" id="fileAnh" name="fileAnh" class="d-none"
                    onchange="document.getElementById('fileName').innerText = this.files[0] ? this.files[0].name : 'Chưa chọn ảnh nào'">

                <label for="fileAnh" class="btn-upload">
                    <i class="fa fa-cloud-upload-alt mr-2"></i> Chọn ảnh...
                </label>

                <span id="fileName" class="file-name text-muted ml-2">Chưa chọn ảnh nào</span>
            </div>
        </div>

        <button type="submit" name="sbDangky" class="btn btn-primary w-100">Đăng ký
        </button>
    </form>

    <p class="text-center mt-3">
        Đã có tài khoản? <a href="login.php">Đăng nhập</a>
    </p>
</div>

<?php require("footer.php"); ?>