<?php
// ------------------------------------------------------------------
// 1. CẤU HÌNH & KẾT NỐI
// ------------------------------------------------------------------
ob_start(); // Ngăn lỗi Header
include("../config.php");

// Kiểm tra kết nối
if (!isset($conn) || $conn === false) {
    die('<div class="alert alert-danger m-3">LỖI KẾT NỐI: Biến $conn không tồn tại.</div>');
}
mysqli_report(MYSQLI_REPORT_OFF);

// --- CẤU HÌNH TÊN TRANG (SLUG) ---
// Thay 'users' bằng tên tham số trên URL của bạn (ví dụ: index.php?p=users)
$base_url = '?p=users';

// Khởi tạo biến
$page_title = "Quản lý Người Dùng";
$message = "";
$current_view = 'list';

// Dữ liệu mặc định
$data = [
    'username' => '',
    'fullname' => '',
    'email' => '',
    'role' => 'user',
    'google_id' => '',
    'avatar' => '',
    'status' => 0
];
$default_avatar = "https://cdn-icons-png.flaticon.com/512/1077/1077114.png";

// ------------------------------------------------------------------
// 2. XỬ LÝ LOGIC
// ------------------------------------------------------------------

// A. XỬ LÝ LƯU (THÊM / SỬA)
if (isset($_POST['save_user'])) {
    $old_username = $_POST['old_username'] ?? '';
    $role         = $_POST['role'];
    $status       = (int)$_POST['status'];

    // --- SỬA (UPDATE) ---
    if (!empty($old_username)) {
        $sql = "UPDATE users SET role=?, status=? WHERE username=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sis", $role, $status, $old_username);

        if (mysqli_stmt_execute($stmt)) {
            // SỬA: Thêm $base_url vào đường dẫn chuyển trang
            echo "<script>alert('Cập nhật thành công!'); window.location.href='$base_url';</script>";
            exit;
        } else {
            $message = "Lỗi cập nhật: " . mysqli_error($conn);
        }
    }
    // --- THÊM MỚI (INSERT) ---
    else {
        $username   = $_POST['username'];
        $fullname   = $_POST['fullname'];
        $email      = $_POST['email'];
        $google_id  = $_POST['google_id'];
        $password   = $_POST['password'];

        $avatar_path = $default_avatar;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
            $avatar_path = $target_dir . time() . "_" . basename($_FILES["avatar"]["name"]);
            move_uploaded_file($_FILES["avatar"]["tmp_name"], $avatar_path);
        }

        $check = mysqli_query($conn, "SELECT username FROM users WHERE username = '$username'");
        if (mysqli_num_rows($check) > 0) {
            $message = "Lỗi: Username '<strong>$username</strong>' đã tồn tại!";
            $current_view = 'form';
            $data = $_POST;
            $data['avatar'] = $avatar_path;
        } else {
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, fullname, password, email, avatar, role, status, google_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssssis", $username, $fullname, $hashed_pass, $email, $avatar_path, $role, $status, $google_id);

            if (mysqli_stmt_execute($stmt)) {
                // SỬA: Thêm $base_url
                echo "<script>alert('Thêm mới thành công!'); window.location.href='$base_url';</script>";
                exit;
            } else {
                $message = "Lỗi thêm mới: " . mysqli_error($conn);
            }
        }
    }
}

// B. XỬ LÝ GET ACTION
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // --> XÓA
    if ($action == 'delete' && isset($_GET['username'])) {
        $u_del = $_GET['username'];
        $sql = "DELETE FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $u_del);

        if ($stmt && mysqli_stmt_execute($stmt)) {
            // SỬA: Thêm $base_url
            echo "<script>alert('Đã xóa user thành công!'); window.location.href='$base_url';</script>";
            exit;
        } else {
            $message = "Lỗi xóa: " . mysqli_error($conn);
        }
    }

    // --> CHUYỂN FORM
    if ($action == 'add' || $action == 'edit') {
        $current_view = 'form';
        $page_title = ($action == 'add') ? "Thêm thành viên mới" : "Cập nhật thành viên";

        if ($action == 'edit' && isset($_GET['username'])) {
            $u_edit = $_GET['username'];
            $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "s", $u_edit);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($res)) {
                $data = $row;
            } else {
                $message = "Không tìm thấy username này!";
                $current_view = 'list';
            }
        }
    }
}

$is_edit_mode = ($current_view == 'form' && !empty($data['username']));
?>

<div class="card shadow">
    <div class="card-header bg-gradient-<?php echo ($current_view == 'list') ? 'dark' : 'primary'; ?> text-white d-flex align-items-center">

        <h4 class="mb-0"><?php echo $page_title; ?></h4>

        <?php if ($current_view == 'list'): ?>
            <a href="<?php echo $base_url; ?>&action=add" class="btn btn-warning btn-sm ms-auto">
                <i class="fas fa-plus-circle"></i> Thêm mới
            </a>
        <?php else: ?>
            <a href="<?php echo $base_url; ?>" class="btn btn-light btn-sm ms-auto">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        <?php endif; ?>
    </div>

    <div class="card-body">

        <?php if ($message): ?>
            <div class="alert alert-danger shadow-sm mb-3">
                <i class="fas fa-exclamation-circle"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($current_view == 'form'): ?>

            <?php if ($is_edit_mode): ?>
                <div class="alert alert-warning border-0 bg-warning bg-opacity-10 mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    Đang xem thông tin user: <strong><?php echo htmlspecialchars($data['username']); ?></strong>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="old_username" value="<?php echo htmlspecialchars($data['username']); ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control"
                            value="<?php echo htmlspecialchars($data['username']); ?>"
                            required <?php echo $is_edit_mode ? 'readonly' : ''; ?>>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Họ tên <span class="text-danger">*</span></label>
                        <input type="text" name="fullname" class="form-control"
                            value="<?php echo htmlspecialchars($data['fullname']); ?>"
                            required <?php echo $is_edit_mode ? 'readonly' : ''; ?>>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control"
                            value="<?php echo htmlspecialchars($data['email']); ?>"
                            required <?php echo $is_edit_mode ? 'readonly' : ''; ?>>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Google ID</label>
                        <input type="text" name="google_id" class="form-control"
                            value="<?php echo htmlspecialchars($data['google_id']); ?>"
                            <?php echo $is_edit_mode ? 'readonly' : ''; ?>>
                    </div>
                </div>

                <?php if (!$is_edit_mode): ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required placeholder="Nhập mật khẩu...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Avatar</label>
                            <input type="file" name="avatar" class="form-control">
                        </div>
                    </div>
                <?php endif; ?>

                <div class="row bg-light p-3 rounded border mb-3 mx-0">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="form-label fw-bold text-primary">Quyền (Role)</label>
                        <select name="role" class="form-select border-primary">
                            <option value="user" <?php echo ($data['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                            <option value="admin" <?php echo ($data['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-primary">Trạng thái</label>
                        <select name="status" class="form-select border-primary">
                            <option value="0" <?php echo ($data['status'] == 0) ? 'selected' : ''; ?>>Hoạt động</option>
                            <option value="1" <?php echo ($data['status'] == 1) ? 'selected' : ''; ?>>Bị chặn</option>
                        </select>
                    </div>
                </div>

                <div class="text-end pt-3">
                    <a href="<?php echo $base_url; ?>" class="btn btn-secondary me-2">Hủy</a>
                    <button type="submit" name="save_user" class="btn btn-success px-4">
                        <i class="fas fa-save"></i> <?php echo $is_edit_mode ? 'Cập nhật' : 'Thêm mới'; ?>
                    </button>
                </div>
            </form>

        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="text-center">Avatar</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Họ tên</th>
                            <th>Role</th>
                            <th>Trạng thái</th>
                            <th class="text-center" width="140">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM users ORDER BY username ASC";
                        $result = mysqli_query($conn, $sql);

                        if (!$result):
                            echo '<tr><td colspan="7" class="text-danger p-4">Lỗi truy vấn: ' . mysqli_error($conn) . '</td></tr>';

                        elseif (mysqli_num_rows($result) > 0):
                            while ($row = mysqli_fetch_assoc($result)):
                                $avatar = !empty($row['avatar']) ? $row['avatar'] : $default_avatar;
                        ?>
                                <tr>
                                    <td class="text-center">
                                        <img src="<?php echo $avatar; ?>" class="rounded-circle border" width="40" height="40" style="object-fit: cover;">
                                    </td>
                                    <td><strong><?php echo $row['username']; ?></strong></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><?php echo $row['fullname']; ?></td>
                                    <td>
                                        <span class="badge <?php echo ($row['role'] == 'admin') ? 'bg-danger' : 'bg-primary'; ?>">
                                            <?php echo ucfirst($row['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] == 1): ?>
                                            <span class="badge bg-secondary"><i class="fas fa-ban"></i> Bị chặn</span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> Hoạt động</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?php echo $base_url; ?>&action=edit&username=<?php echo urlencode($row['username']); ?>"
                                            class="btn btn-info btn-sm" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo $base_url; ?>&action=delete&username=<?php echo urlencode($row['username']); ?>"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Bạn chắc chắn muốn xóa: <?php echo $row['username']; ?>?');"
                                            title="Xóa">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Chưa có thành viên nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</div>