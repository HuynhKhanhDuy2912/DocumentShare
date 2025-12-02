<?php
// Kiểm tra biến $conn đã được thiết lập từ file index.php
if (!isset($conn) || $conn === false) {
    die('<div class="alert alert-danger">LỖI KẾT NỐI: Biến $conn không tồn tại.</div>');
}

// Khởi tạo biến
$message = "";
$current_view = 'list'; // Mặc định là danh sách
$data = ['category_id' => null, 'name' => '', 'description' => '', 'status' => 0];

// ------------------------------------------------------------------
// A. XỬ LÝ FORM SUBMIT (THÊM/SỬA)
// ------------------------------------------------------------------
if (isset($_POST['save_category'])) {
    $id = $_POST['category_id'] ?? null;
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $status = (int)$_POST['status'];
    $executed = false;
    $stmt = null;

    // 1. Kiểm tra Tên Danh mục Tồn tại (cho cả Thêm mới và Sửa)
    $check_sql = "SELECT category_id FROM categories WHERE name = ? AND category_id != ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);

    if ($check_stmt) {
        // Gán 0 nếu id là null (cho chế độ Thêm mới)
        $id_check = $id ?? 0;
        mysqli_stmt_bind_param($check_stmt, "si", $name, $id_check);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $message = "Lỗi: Tên danh mục '<strong>$name</strong>' đã tồn tại.";
            // Gán lại dữ liệu cũ để giữ trên form nếu lỗi
            $data = [
                'category_id' => $id,
                'name' => $name,
                'description' => $description,
                'status' => $status
            ];
            $current_view = 'form';
        }
        mysqli_stmt_close($check_stmt);
    } else {
        $message = "Lỗi chuẩn bị SQL (CHECK): " . mysqli_error($conn);
    }

    // 2. Thực hiện Thêm mới hoặc Cập nhật nếu không có lỗi kiểm tra
    if (empty($message)) {
        if ($id) {
            // --- SỬA (UPDATE) ---
            $sql = "UPDATE categories SET name=?, description=?, status=? WHERE category_id=?";
            $stmt = mysqli_prepare($conn, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssii", $name, $description, $status, $id);
                $msg_text = "Cập nhật danh mục thành công!";
            }
        } else {
            // --- THÊM MỚI (INSERT) ---
            $sql = "INSERT INTO categories (name, description, status) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssi", $name, $description, $status);
                $msg_text = "Thêm mới danh mục thành công!";
            }
        }

        if ($stmt) {
            if (mysqli_stmt_execute($stmt)) {
                $executed = true;
            } else {
                $message = "Lỗi thực thi SQL: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt); // Đóng statement
        } else {
            $message = "Lỗi chuẩn bị SQL (CRUD): " . mysqli_error($conn);
        }
    }

    if ($executed) {
        // Sử dụng JS để hiện thông báo và chuyển hướng
        echo "<script>alert('$msg_text'); window.location.href='?p=categories';</script>";
        exit;
    }
}

// ------------------------------------------------------------------
// B. XỬ LÝ GET ACTION (XEM, SỬA, XÓA)
// ------------------------------------------------------------------
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = $_GET['id'] ?? null;

    if ($action == 'delete' && $id) {
        // --- XÓA (DELETE) ---
        $sql = "DELETE FROM categories WHERE category_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                echo "<script>alert('Xóa danh mục thành công!'); window.location.href='?p=categories';</script>";
                exit;
            } else {
                $message = "Lỗi xóa danh mục: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $message = "Lỗi chuẩn bị xóa: " . mysqli_error($conn);
        }
    }

    if ($action == 'add' || $action == 'edit') {
        $current_view = 'form';
        $page_title = ($action == 'add') ? "Thêm danh mục mới" : "Cập nhật danh manh mục";

        if ($action == 'edit' && $id) {
            // --- LẤY DATA ĐỂ SỬA (READ single) ---
            $stmt = mysqli_prepare($conn, "SELECT category_id, name, description, status FROM categories WHERE category_id = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($res)) {
                    $data = $row;
                } else {
                    $message = "Không tìm thấy ID danh mục này!";
                    $current_view = 'list';
                }
                mysqli_stmt_close($stmt); // Đóng statement
            } else {
                $message = "Lỗi chuẩn bị truy vấn sửa: " . mysqli_error($conn);
                $current_view = 'list';
            }
        }
    }
}

// Biến kiểm tra chế độ sửa
$is_edit_mode = !empty($data['category_id']);
?>

<?php
if ($current_view == 'list') {
    $header_title = "Danh mục";
} elseif ($current_view == 'form') {
    if (!empty($data['category_id'])) {
        $header_title = "Sửa danh mục";
    } else {
        $header_title = "Thêm danh mục";
    }
} else {
    $header_title = "Danh mục";
}
?>

<div class="card shadow">
    <div class="card-header bg-gradient-<?php echo ($current_view == 'list') ? 'dark' : 'primary'; ?> 
    text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><?php echo $header_title; ?></h4>
        <?php if ($current_view == 'list'): ?>
            <a href="?p=categories&action=add" class="btn btn-warning btn-sm">
                <i class="fas fa-plus-circle"></i> Thêm mới
            </a>
        <?php endif; ?>
    </div>

    <div class="card-body">
        <?php if ($message): ?>
            <div class="alert alert-danger shadow-sm"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- VIEW: FORM (THÊM/SỬA) -->
        <?php if ($current_view == 'form'): ?>
            <form method="POST">
                <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($data['category_id'] ?? ''); ?>">

                <div class="mb-3">
                    <label class="form-label fw-bold">Tên Danh mục <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control"
                        value="<?php echo htmlspecialchars($data['name']); ?>"
                        required maxlength="255" placeholder="Ví dụ: Lập trình Web">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Mô tả</label>
                    <textarea name="description" class="form-control" rows="3"
                        placeholder="Mô tả ngắn gọn về nội dung danh mục này..."><?php echo htmlspecialchars($data['description']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Trạng thái</label>
                    <select name="status" class="form-select pt-2 pb-2 pl-2 ml-2" style="width: 110px;">
                        <option value="0" <?php echo ($data['status'] == 0) ? 'selected' : ''; ?>>Hiển thị</option>
                        <option value="1" <?php echo ($data['status'] == 1) ? 'selected' : ''; ?>>Ẩn</option>
                    </select>
                </div>

                <div class="text-end pt-3">
                    <button type="submit" name="save_category" class="btn btn-success px-4">
                        <i class="fas fa-save"></i> <?php echo $is_edit_mode ? 'Cập nhật' : 'Thêm mới'; ?>
                    </button>
                    <a href="?p=categories" class="btn btn-secondary me-2">Quay lại</a>
                </div>
            </form>

            <!-- VIEW: DANH SÁCH -->
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tên danh mục</th>
                            <th>Mô tả</th>
                            <th width="120">Trạng thái</th>
                            <th class="text-center" width="140">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT category_id, name, description, status FROM categories ORDER BY category_id DESC";
                        $result = mysqli_query($conn, $sql);
                        ?>

                        <?php if (!$result): ?>
                            <tr>
                                <td colspan="5" class="text-danger p-4">
                                    Lỗi truy vấn: <?= mysqli_error($conn) ?>
                                </td>
                            </tr>

                        <?php elseif (mysqli_num_rows($result) > 0): ?>

                            <?php while ($row = mysqli_fetch_assoc($result)):
                                $id_val   = htmlspecialchars($row['category_id']);
                                $name_val = htmlspecialchars($row['name']);
                            ?>
                                <tr>
                                    <td><strong><?= $name_val ?></strong></td>
                                    <td><?= htmlspecialchars($row['description']) ?></td>

                                    <td>
                                        <?php if ($row['status'] == 1): ?>
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-eye-slash"></i> Ẩn
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle"></i> Hoạt động
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <a href="?p=categories&action=edit&id=<?= urlencode($id_val) ?>"
                                            class="btn btn-info btn-sm" title="Sửa danh mục">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <a href="?p=categories&action=delete&id=<?= urlencode($id_val) ?>"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Bạn có chắc muốn xóa danh mục: <?= $name_val ?> này không?');"
                                            title="Xóa">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php mysqli_free_result($result); ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Chưa có danh mục nào được tạo.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>