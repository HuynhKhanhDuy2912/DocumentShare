<?php
include 'header.php';

if (!isset($_SESSION['emailUser'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['emailUser'];

$sql = "SELECT * FROM users WHERE email = '$email'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();


// Cập nhật profile
if (isset($_POST['btnUpdate'])) {

    $fullname = $_POST['fullname'];
    $avatarName = $user['avatar'];

    // Upload avatar mới
    if (!empty($_FILES['avatar']['name'])) {

        $uploadDir = "uploads/users/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allow = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $allow)) {

            $avatarName = time() . "_" . uniqid() . "." . $ext;
            move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $avatarName);

            // Xóa avatar cũ (nếu có)
            if (!empty($user['avatar']) && file_exists($uploadDir . $user['avatar'])) {
                unlink($uploadDir . $user['avatar']);
            }

            $_SESSION['avatar'] = $avatarName;
        }
    }

    $username = $user['username'];
    $sqlUpdate = "UPDATE users SET fullname='$fullname', avatar='$avatarName' WHERE username = '$username'";
    if ($conn->query($sqlUpdate)) {
        echo "<script>alert('Cập nhật thành công'); window.location='profile.php';</script>";
    }
}

// ================= ĐỔI MẬT KHẨU (HỖ TRỢ MD5) =================
if (isset($_POST['btnChangePassword']) && empty($user['google_id'])) {

    $oldPass = $_POST['old_password'];
    $newPass = $_POST['new_password'];
    $rePass  = $_POST['re_password'];

    // Kiểm tra mật khẩu cũ
    if (md5($oldPass) !== $user['password']) {
        echo "<script>alert('Mật khẩu cũ không đúng');</script>";
        return;
    }

    // Kiểm tra mật khẩu mới
    if ($newPass !== $rePass) {
        echo "<script>alert('Mật khẩu nhập lại không khớp');</script>";
        return;
    }

    if (strlen($newPass) < 6) {
        echo "<script>alert('Mật khẩu phải ít nhất 6 ký tự');</script>";
        return;
    }

    // 👉 MD5 mật khẩu mới
    $newHash = md5($newPass);

    $username = $user['username'];
    $sqlPass = "UPDATE users SET password='$newHash' WHERE username='$username'";

    if ($conn->query($sqlPass)) {
        echo "<script>
            alert('Đổi mật khẩu thành công!');
            window.location='profile.php';
        </script>";
    } else {
        echo "<script>alert('Lỗi đổi mật khẩu');</script>";
    }
}


?>

<div class="container mrt">

    <!-- WRAPPER -->
    <div id="profileWrapper" class="profile-wrapper justify-center">

        <!-- FORM CẬP NHẬT THÔNG TIN -->
        <div class="profile-box">
            <div class="card shadow" style="max-width:700px;">
                <div class="card-header bg-primary text-white fw-bold">
                    Thông tin tài khoản
                </div>

                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">

                        <div class="text-center mb-4">
                            <?php if (!empty($user['avatar'])): ?>
                                <img src="uploads/users/<?= $user['avatar'] ?>" width="120" height="120"
                                    style="object-fit:cover; border-radius:50%;">
                            <?php else: ?>
                                <i class="fa fa-user-circle fa-5x text-secondary"></i>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Đổi ảnh đại diện</label>
                            <input type="file" name="avatar" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tên đăng nhập</label>
                            <input type="text" class="form-control" value="<?= $user['username'] ?>" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= $user['email'] ?>" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Họ và tên</label>
                            <input type="text" name="fullname" class="form-control" value="<?= $user['fullname'] ?>">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="btnUpdate" class="btn btn-primary w-50">
                                <i class="fa fa-save me-2"></i> Cập nhật
                            </button>

                            <?php if (empty($user['google_id'])): ?>
                                <button type="button" class="btn btn-warning w-50"
                                    onclick="togglePasswordForm()">
                                    <i class="fa fa-key me-2"></i> Đổi mật khẩu
                                </button>
                            <?php endif; ?>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- FORM ĐỔI MẬT KHẨU -->
        <?php if (empty($user['google_id'])): ?>
            <div class="password-box">
                <div class="card shadow" style="max-width:700px;">
                    <div class="card-header bg-warning fw-bold">
                        <i class="fa fa-key me-2"></i> Đổi mật khẩu
                    </div>

                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Mật khẩu cũ</label>
                                <input type="password" name="old_password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mật khẩu mới</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nhập lại mật khẩu mới</label>
                                <input type="password" name="re_password" class="form-control" required>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" name="btnChangePassword" class="btn btn-warning w-50">
                                    <i class="fa fa-key me-2"></i> Xác nhận
                                </button>
                                <button type="button" class="btn btn-secondary w-50"
                                    onclick="togglePasswordForm()">
                                    Hủy
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>


<?php require("footer.php"); ?>