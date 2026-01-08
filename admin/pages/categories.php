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

$page_title = match ($action) {
    'add' => 'Thêm chủ đề mới',
    'edit' => 'Cập nhật chủ đề',
    default => 'Danh sách chủ đề'
};

// ------------------------------------------------------
// A. XỬ LÝ FORM SUBMIT (THÊM / SỬA)
// ------------------------------------------------------
if (isset($_POST['save_category'])) {

    $id = $_POST['category_id'] ?? '';
    $id = ($id === '' || $id === null) ? 0 : (int)$id;

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $status = (int)$_POST['status'];

    // KIỂM TRA TRÙNG TÊN
    $check_sql = "SELECT category_id FROM categories WHERE name = ? AND category_id != ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);

    mysqli_stmt_bind_param($check_stmt, "si", $name, $id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        $message = "Tên chủ đề <strong>$name</strong> đã tồn tại!";
        $data = ['category_id' => $id, 'name' => $name, 'description' => $description, 'status' => $status];
        $current_view = "form";
    }

    mysqli_stmt_close($check_stmt);

    // NẾU KHÔNG LỖI THÌ THÊM / SỬA
    if (empty($message)) {

        if ($id > 0) {
            // SỬA
            $sql = "UPDATE categories SET name=?, description=?, status=? WHERE category_id=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssii", $name, $description, $status, $id);
            $success_msg = "Cập nhật chủ đề thành công!";
        } else {
            // THÊM
            $sql = "INSERT INTO categories (name, description, status) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssi", $name, $description, $status);
            $success_msg = "Thêm chủ đề thành công!";
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
        echo "<script>alert('Xóa chủ đề thành công!'); window.location.href='?p=categories';</script>";
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
        $message = "Không tìm thấy chủ đề!";
        $current_view = "list";
    }
    mysqli_stmt_close($stmt);
}

$is_edit = !empty($data['category_id']);

// ------------------------------------------------------
// SEARCH
// ------------------------------------------------------
$keyword = trim($_GET['keyword'] ?? '');
$where = '';

if ($keyword !== '') {
    $safe = mysqli_real_escape_string($conn, $keyword);
    $where = "WHERE name LIKE '%$safe%' OR description LIKE '%$safe%'";
}

// ------------------------------------------------------
// PHÂN TRANG
// ------------------------------------------------------
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Tổng số bản ghi
$total_rs = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM categories
    $where
");

$total_row = mysqli_fetch_assoc($total_rs);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

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

            <form method="POST" action="">

                <input type="hidden" name="category_id" value="<?= htmlspecialchars($data['category_id']) ?>">

                <div class="mb-3">
                    <label class="form-label fw-bold">Tên chủ đề *</label>
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
                        <option value="0" <?= $data['status'] == 0 ? 'selected' : '' ?>>Hiển thị</option>
                        <option value="1" <?= $data['status'] == 1 ? 'selected' : '' ?>>Ẩn</option>
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

            <form method="get" class="row g-2 mb-3">
                <input type="hidden" name="p" value="categories">

                <div class="col-md-4 ms-auto">
                    <input type="text"
                        name="keyword"
                        class="form-control"
                        placeholder="Tìm chủ đề..."
                        value="<?= htmlspecialchars($keyword) ?>">
                </div>

                <div class="col-md-auto">
                    <button class="btn btn-primary px-4">
                        <i class="fas fa-search"></i> Tìm
                    </button>
                    <a href="?p=categories" class="btn btn-secondary">
                        <i class="fas fa-sync"></i>
                    </a>
                </div>
            </form>


            <!-- ============= DANH SÁCH ============= -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Tên chủ đề</th>
                            <th>Mô tả</th>
                            <th width="110">Trạng thái</th>
                            <th width="140" class="text-center">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $result = mysqli_query($conn, "
                            SELECT *
                            FROM categories
                            $where
                            ORDER BY category_id DESC
                            LIMIT $limit OFFSET $offset
                        ");
                        ?>

                        <?php if (!$result || mysqli_num_rows($result) == 0): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">Chưa có chủ đề</td>
                            </tr>

                        <?php else: ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['description']) ?></td>

                                    <td>
                                        <?= $row['status'] == 1
                                            ? '<span class="badge bg-secondary"><i class="fas fa-eye-slash"></i> Ẩn</span>'
                                            : '<span class="badge bg-success"><i class="fas fa-check-circle"></i> Hiển thị</span>' ?>
                                    </td>

                                    <td class="text-center">
                                        <a href="?p=categories&action=edit&id=<?= $row['category_id'] ?>"
                                            class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>

                                        <a href="?p=categories&action=delete&id=<?= $row['category_id'] ?>"
                                            onclick="return confirm('Xóa chủ đề này?');"
                                            class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php if ($total_pages > 1): ?>
                    <nav class="pagination-wrapper mt-4">
                        <ul class="pagination justify-content-center">

                            <!-- Trang trước -->
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="?p=categories&page=<?= max(1, $page - 1) ?>&keyword=<?= urlencode($keyword) ?>">
                                    &laquo;
                                </a>
                            </li>

                            <?php
                            $start = max(1, $page - 2);
                            $end   = min($total_pages, $page + 2);
                            ?>

                            <?php if ($start > 1): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="?p=categories&page=1&keyword=<?= urlencode($keyword) ?>">1</a>
                                </li>
                                <?php if ($start > 2): ?>
                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link"
                                        href="?p=categories&page=<?= $i ?>&keyword=<?= urlencode($keyword) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end < $total_pages): ?>
                                <?php if ($end < $total_pages - 1): ?>
                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="?p=categories&page=<?= $total_pages ?>&keyword=<?= urlencode($keyword) ?>">
                                        <?= $total_pages ?>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <!-- Trang sau -->
                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="?p=categories&page=<?= min($total_pages, $page + 1) ?>&keyword=<?= urlencode($keyword) ?>">
                                    &raquo;
                                </a>
                            </li>

                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>