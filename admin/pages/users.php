<?php
// ------------------------------------------------------------------
// 1. CẤU HÌNH & KẾT NỐI
// ------------------------------------------------------------------

// Kiểm tra kết nối
if (!isset($conn) || $conn === false) {
    die('<div class="alert alert-danger m-3">LỖI KẾT NỐI: Biến $conn không tồn tại.</div>');
}
mysqli_report(MYSQLI_REPORT_OFF);

// --- CẤU HÌNH TÊN TRANG (SLUG) ---
$base_url = '?p=users';

// Khởi tạo biến
$page_title = "Danh sách tài khoản";
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
    // --- THÊM MỚI (INSERT) ---
    else {
        $username   = $_POST['username'];
        $fullname   = $_POST['fullname'];
        $email      = $_POST['email'];
        $google_id  = $_POST['google_id'];
        $password   = $_POST['password'];

        $avatar_path = $default_avatar;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $target_dir = "../uploads/users/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
            $filename = time() . "_" . basename($_FILES["avatar"]["name"]);
            move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_dir . $filename);
            $avatar_path = $filename;
        }

        $check = mysqli_query($conn, "SELECT username FROM users WHERE username = '$username'");
        if (mysqli_num_rows($check) > 0) {
            $message = "Lỗi: Tên đăng nhập '<strong>$username</strong>' đã tồn tại!";
            $current_view = 'form';
            $data = $_POST;
            $data['avatar'] = $avatar_path;
        } else {
            // --- SỬA TẠI ĐÂY: Dùng MD5 thay vì password_hash ---
            $md5_password = md5($password);

            $sql = "INSERT INTO users (username, fullname, password, email, avatar, role, status, google_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            // Biến truyền vào là $md5_password
            mysqli_stmt_bind_param($stmt, "ssssssis", $username, $fullname, $md5_password, $email, $avatar_path, $role, $status, $google_id);

            if (mysqli_stmt_execute($stmt)) {
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
            echo "<script>alert('Đã xóa tài khoản thành công!'); window.location.href='$base_url';</script>";
            exit;
        } else {
            $message = "Lỗi xóa: " . mysqli_error($conn);
        }
    }

    // --> CHUYỂN FORM
    if ($action == 'add' || $action == 'edit') {
        $current_view = 'form';
        $page_title = ($action == 'add') ? "Thêm tài khoản mới" : "Cập nhật tài khoản";

        if ($action == 'edit' && isset($_GET['username'])) {
            $u_edit = $_GET['username'];
            $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "s", $u_edit);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($res)) {
                $data = $row;
            } else {
                $message = "Không tìm thấy tài khoản này!";
                $current_view = 'list';
            }
        }
    }
}

$is_edit_mode = ($current_view == 'form' && !empty($data['username']));

// --- CẤU HÌNH TÌM KIẾM & PHÂN TRANG ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$limit = 5; // Số dòng trên mỗi trang
$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Xây dựng điều kiện WHERE cho tìm kiếm
$where_clause = "";
if (!empty($search)) {
    $where_clause = " WHERE username LIKE '%$search%' OR email LIKE '%$search%' OR fullname LIKE '%$search%' ";
}

// Tính tổng số dòng để phân trang
$total_rows_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM users" . $where_clause);
$total_rows = mysqli_fetch_assoc($total_rows_query)['total'];
$total_pages = ceil($total_rows / $limit);
?>

<div class="card shadow">
    <div class="card-header bg-gradient-<?php echo ($current_view == 'list') ? 'dark' : 'primary'; ?> text-white d-flex align-items-center">

        <h4 class="mb-0"><?php echo $page_title; ?></h4>

        <?php if ($current_view == 'list'): ?>
            <form method="GET" class="ms-auto d-flex me-2">
                <input type="hidden" name="p" value="users">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Tìm tên, email..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-light" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>

            <a href="<?php echo $base_url; ?>&action=add" class="btn btn-warning ms-auto px-3">
                <i class="fas fa-plus-circle me-1"></i> Thêm mới
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
                    Đang xem thông tin tài khoản: <strong><?php echo htmlspecialchars($data['username']); ?></strong>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="old_username" value="<?php echo htmlspecialchars($data['username']); ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tên đăng nhập <span class="text-danger">*</span></label>
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
                            <label class="form-label fw-bold">Ảnh đại diện</label>
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
                        <label class="form-label fw-bold text-primary me-2">Trạng thái: </label>
                        <select name="status" class="form-select border-primary">
                            <option value="0" <?php echo ($data['status'] == 0) ? 'selected' : ''; ?>>Hoạt động</option>
                            <option value="1" <?php echo ($data['status'] == 1) ? 'selected' : ''; ?>>Bị chặn</option>
                        </select>
                    </div>
                </div>

                <div class="text-end pt-3">
                    <button type="submit" name="save_user" class="btn btn-success px-4">
                        <i class="fas fa-save"></i> <?php echo $is_edit_mode ? 'Cập nhật' : 'Thêm mới'; ?>
                    </button>
                    <a href="<?php echo $base_url; ?>" class="btn btn-secondary me-2">Quay lại</a>
                </div>
            </form>

        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="text-center">Avatar</th>
                            <th>Tên đăng nhập</th>
                            <th>Email</th>
                            <th>Họ tên</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th class="text-center" width="140">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Thay đổi câu SQL để hỗ trợ tìm kiếm và phân trang
                        $sql = "SELECT * FROM users $where_clause ORDER BY username ASC LIMIT $limit OFFSET $offset";
                        $result = mysqli_query($conn, $sql);

                        if (!$result):
                            echo '<tr><td colspan="7" class="text-danger p-4">Lỗi truy vấn: ' . mysqli_error($conn) . '</td></tr>';
                        elseif (mysqli_num_rows($result) > 0):
                            while ($row = mysqli_fetch_assoc($result)):
                                $avatarSrc = $default_avatar;

                                if (!empty($row['avatar'])) {
                                    if (filter_var($row['avatar'], FILTER_VALIDATE_URL)) {
                                        $avatarSrc = $row['avatar'];
                                    } else {
                                        $avatarSrc = "../uploads/users/" . $row['avatar'];
                                    }
                                }
                        ?>
                                <tr>
                                    <td class="text-center">
                                        <img src="<?php echo $avatarSrc; ?>" class="rounded-circle border" width="40" height="40" style="object-fit: cover;">
                                    </td>
                                    <td><strong><?php echo $row['username']; ?></strong></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><?php echo $row['fullname']; ?></td>
                                    <td><?php echo ($row['role'] == 1) ? 'Quản trị viên' : 'Người dùng'; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row['status'] == 1): ?>
                                            <span class="badge bg-danger"><i class="fas fa-ban"></i> Bị chặn</span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> Hoạt động</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row['role'] !== '1'): ?>
                                            <a href="<?php echo $base_url; ?>&action=edit&username=<?php echo urlencode($row['username']); ?>"
                                                class="btn btn-info btn-sm" title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo $base_url; ?>&action=delete&username=<?php echo urlencode($row['username']); ?>"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Bạn chắc chắn muốn xóa tài khoản: <?php echo $row['username']; ?>?');"
                                                title="Xóa">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Không thể thao tác</span>
                                        <?php endif; ?>
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
            <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo $base_url; ?>&search=<?php echo $search; ?>&page=<?php echo $page - 1; ?>"><span aria-hidden="true">&laquo;</span></a>
                        </li>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo $base_url; ?>&search=<?php echo $search; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo $base_url; ?>&search=<?php echo $search; ?>&page=<?php echo $page + 1; ?>"><span aria-hidden="true">&raquo;</span></a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>