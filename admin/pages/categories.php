<?php
// ------------------------------------------------------
// KIỂM TRA KẾT NỐI DB
// ------------------------------------------------------
if (!isset($conn) || $conn === false) {
    die('<div class="alert alert-danger">LỖI KẾT NỐI: Biến $conn không tồn tại.</div>');
}

// ------------------------------------------------------
// BIẾN CẦN THIẾT
// ------------------------------------------------------
$message = "";
$data = ['category_id' => null, 'name' => '', 'description' => '', 'status' => 0];

// Xác định chế độ view
$action = $_GET['action'] ?? '';
$current_view = ($action === 'add' || $action === 'edit') ? 'form' : 'list';

$page_title = match($action) {
    'add' => 'Thêm danh mục mới',
    'edit' => 'Cập nhật danh mục',
    default => 'Danh mục'
};

// ------------------------------------------------------
// A. XỬ LÝ FORM SUBMIT (THÊM / SỬA)
// ------------------------------------------------------
if (isset($_POST['save_category'])) {
    $id = $_POST['category_id'] ?? null;
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $status = (int)$_POST['status'];

    // KIỂM TRA TRÙNG TÊN
    $check_sql = "SELECT category_id FROM categories WHERE name = ? AND category_id != ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "si", $name, $id ?? 0);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        $message = "Tên danh mục <strong>$name</strong> đã tồn tại!";
        $data = ['category_id' => $id, 'name' => $name, 'description' => $description, 'status' => $status];
        $current_view = 'form';
    }
    mysqli_stmt_close($check_stmt);

    // NẾU KHÔNG LỖI THÌ THÊM / SỬA
    if (empty($message)) {

        if ($id) {
            // SỬA
            $sql = "UPDATE categories SET name=?, description=?, status=? WHERE category_id=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssii", $name, $description, $status, $id);
            $success_msg = "Cập nhật danh mục thành công!";
        } else {
            // THÊM
            $sql = "INSERT INTO categories (name, description, status) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssi", $name, $description, $status);
            $success_msg = "Thêm danh mục thành công!";
        }

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('$success_msg'); window.location.href='?p=categories';</script>";
            exit;
        } else {
            $message = "Lỗi SQL: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    }
}

// ------------------------------------------------------
// B. XỬ LÝ GET ACTION (SỬA / XÓA)
// ------------------------------------------------------
if ($action == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM categories WHERE category_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Xóa danh mục thành công!'); window.location.href='?p=categories';</script>";
        exit;
    } else {
        $message = "Lỗi xóa: " . mysqli_stmt_error($stmt);
    }
    mysqli_stmt_close($stmt);
}

if ($action == 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = mysqli_prepare($conn, "SELECT * FROM categories WHERE category_id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($rs)) {
        $data = $row;
    } else {
        $message = "Không tìm thấy danh mục!";
        $current_view = "list";
    }
    mysqli_stmt_close($stmt);
}

$is_edit = !empty($data['category_id']);
?>

<!-- ============================== HTML =============================== -->

<div class="card shadow">

    <div class="card-header bg-gradient-<?php echo ($current_view == 'list') ? 'dark' : 'primary'; ?> text-white d-flex align-items-center">
        <h4 class="mb-0"><?php echo $page_title; ?></h4>

        <?php if ($current_view == 'list'): ?>
            <a href="?p=categories&action=add" class="btn btn-warning ms-auto px-3">
                <i class="fas fa-plus-circle me-1"></i> Thêm mới
            </a>
        <?php endif; ?>
    </div>

    <div class="card-body">

        <?php if ($message): ?>
            <div class="alert alert-danger"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- ================= FORM ================= -->
        <?php if ($current_view == 'form'): ?>

            <form method="POST">

                <input type="hidden" name="category_id" value="<?= htmlspecialchars($data['category_id']) ?>">

                <div class="mb-3">
                    <label class="form-label fw-bold">Tên danh mục *</label>
                    <input type="text" name="name" class="form-control"
                        value="<?= htmlspecialchars($data['name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Mô tả</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($data['description']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Trạng thái</label>
                    <select name="status" class="form-select" style="width:130px;">
                        <option value="0" <?= $data['status']==0 ? 'selected':'' ?>>Hiển thị</option>
                        <option value="1" <?= $data['status']==1 ? 'selected':'' ?>>Ẩn</option>
                    </select>
                </div>

                <div class="text-end pt-3">
                    <button class="btn btn-success px-4" name="save_category">
                        <i class="fas fa-save"></i> <?= $is_edit ? "Cập nhật" : "Thêm mới" ?>
                    </button>

                    <a href="?p=categories" class="btn btn-secondary px-3">Quay lại</a>
                </div>
            </form>

        <?php else: ?>

            <!-- ============= DANH SÁCH ============= -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Tên danh mục</th>
                            <th>Mô tả</th>
                            <th width="110">Trạng thái</th>
                            <th width="140" class="text-center">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $result = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_id DESC");
                        ?>

                        <?php if (!$result || mysqli_num_rows($result) == 0): ?>
                            <tr><td colspan="4" class="text-center py-4">Chưa có danh mục</td></tr>

                        <?php else: ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['description']) ?></td>

                                    <td>
                                        <?= $row['status']==1
                                            ? '<span class="badge bg-secondary"><i class="fas fa-eye-slash"></i> Ẩn</span>'
                                            : '<span class="badge bg-success"><i class="fas fa-check-circle"></i> Hiển thị</span>' ?>
                                    </td>

                                    <td class="text-center">
                                        <a href="?p=categories&action=edit&id=<?= $row['category_id'] ?>"
                                            class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>

                                        <a href="?p=categories&action=delete&id=<?= $row['category_id'] ?>"
                                            onclick="return confirm('Xóa danh mục này?');"
                                            class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>

    </div>
</div>
