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
$data = ['subcategory_id' => null, 'name' => '', 'status' => 0, 'category_id' => null];

// Xác định chế độ view
$action = $_GET['action'] ?? '';
$current_view = ($action === 'add' || $action === 'edit') ? 'form' : 'list';

$page_title = match ($action) {
    'add' => 'Thêm danh môn học mới',
    'edit' => 'Cập nhật môn học',
    default => 'Danh sách môn học'
};

// ------------------------------------------------------
// A. XỬ LÝ FORM SUBMIT (THÊM / SỬA)
// ------------------------------------------------------
if (isset($_POST['save_subcategory'])) {

    $id = $_POST['subcategory_id'] ?? 0;
    $id = (int)$id;
    $name = trim($_POST['name']);
    $status = (int)($_POST['status'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);

    // KIỂM TRA TRÙNG TÊN
    $check_sql = "SELECT subcategory_id FROM subcategories WHERE name=? AND category_id=? AND subcategory_id!=?";
    $stmt_check = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt_check, "sii", $name, $category_id, $id);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);

    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        $message = "Môn học <strong>$name</strong> đã tồn tại trong chủ đề này!";
        $data = ['subcategory_id' => $id, 'name' => $name, 'status' => $status, 'category_id' => $category_id];
        $current_view = 'form';
    }
    mysqli_stmt_close($stmt_check);

    if (empty($message)) {
        if ($id > 0) {
            // Cập nhật
            $sql = "UPDATE subcategories SET name=?, status=?, category_id=? WHERE subcategory_id=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "siii", $name, $status, $category_id, $id);
            $success_msg = "Cập nhật môn học thành công!";
        } else {
            // Thêm mới
            $sql = "INSERT INTO subcategories (name, status, category_id) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sii", $name, $status, $category_id);
            $success_msg = "Thêm môn học thành công!";
        }

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('$success_msg'); window.location.href='?p=subcategories';</script>";
            exit;
        } else {
            $message = "Lỗi SQL: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    }
}

// ------------------------------------------------------
// B. XỬ LÝ DELETE
// ------------------------------------------------------
if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($conn, "DELETE FROM subcategories WHERE subcategory_id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Xóa môn học thành công!'); window.location.href='?p=subcategories';</script>";
        exit;
    } else {
        $message = "Lỗi xóa: " . mysqli_stmt_error($stmt);
    }
    mysqli_stmt_close($stmt);
}

// ------------------------------------------------------
// C. XỬ LÝ EDIT
// ------------------------------------------------------
if ($action == 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM subcategories WHERE subcategory_id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($rs)) {
        $data = $row;
    } else {
        $message = "Không tìm thấy môn học!";
        $current_view = 'list';
    }
    mysqli_stmt_close($stmt);
}

$is_edit = !empty($data['subcategory_id']);

// ------------------------------------------------------
// SEARCH
// ------------------------------------------------------
$keyword = trim($_GET['keyword'] ?? '');
$where = '';

if ($keyword !== '') {
    $safe = mysqli_real_escape_string($conn, $keyword);
    $where = "WHERE s.name LIKE '%$safe%' OR c.name LIKE '%$safe%'";
}

// ------------------------------------------------------
// D. PHÂN TRANG
// ------------------------------------------------------
$limit = 10; // số dòng mỗi trang
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Tổng số môn học
$total_rs = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM subcategories s
    LEFT JOIN categories c ON s.category_id = c.category_id
    $where
");
$total_row = mysqli_fetch_assoc($total_rs);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

?>

<!-- ================= HTML ================= -->
<div class="card shadow">
    <div class="card-header bg-gradient-<?php echo ($current_view == 'list') ? 'dark' : 'primary'; ?> text-white d-flex align-items-center">
        <h4 class="mb-0"><?= $page_title ?></h4>
        <?php if ($current_view == 'list'): ?>
            <a href="?p=subcategories&action=add" class="btn btn-warning ms-auto px-3"><i class="fas fa-plus-circle me-1"></i> Thêm mới</a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($message): ?>
            <div class="alert alert-danger"><?= $message ?></div>
        <?php endif; ?>

        <?php if ($current_view == 'form'): ?>
            <form method="POST" action="">
                <input type="hidden" name="subcategory_id" value="<?= htmlspecialchars($data['subcategory_id']) ?>">

                <div class="mb-3">
                    <label class="form-label fw-bold">Tên môn học *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($data['name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Chủ đề *</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Chọn chủ đề --</option>
                        <?php
                        $cats = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
                        while ($c = mysqli_fetch_assoc($cats)):
                        ?>
                            <option value="<?= $c['category_id'] ?>" <?= $c['category_id'] == $data['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Trạng thái</label>
                    <select name="status" class="form-select" style="width:130px;">
                        <option value="0" <?= $data['status'] == 0 ? 'selected' : '' ?>>Hiển thị</option>
                        <option value="1" <?= $data['status'] == 1 ? 'selected' : '' ?>>Ẩn</option>
                    </select>
                </div>

                <div class="text-end pt-3">
                    <button class="btn btn-success px-4" name="save_subcategory"><i class="fas fa-save"></i> <?= $is_edit ? 'Cập nhật' : 'Thêm mới' ?></button>
                    <a href="?p=subcategories" class="btn btn-secondary px-3">Quay lại</a>
                </div>
            </form>
        <?php else: ?>
            <form method="get" class="row g-2 mb-3" >
                <input type="hidden" name="p" value="subcategories">

                <div class="col-md-4 ms-auto">
                    <input type="text"
                        name="keyword"
                        class="form-control"
                        placeholder="Tìm môn học..."
                        value="<?= htmlspecialchars($keyword) ?>">
                </div>

                <div class="col-md-auto">
                    <button class="btn btn-primary px-4">
                        <i class="fas fa-search"></i> Tìm
                    </button>
                    <a href="?p=subcategories" class="btn btn-secondary">
                        <i class="fas fa-sync"></i>
                    </a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Tên môn học</th>
                            <th>Chủ đề</th>
                            <th width="110">Trạng thái</th>
                            <th width="140" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $res = mysqli_query($conn, "
                            SELECT s.*, c.name AS category_name
                            FROM subcategories s
                            LEFT JOIN categories c ON s.category_id = c.category_id
                            $where
                            ORDER BY s.subcategory_id DESC
                            LIMIT $limit OFFSET $offset
                        ");


                        if (!$res || mysqli_num_rows($res) == 0):
                        ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">Chưa có môn học</td>
                            </tr>
                        <?php else: ?>
                            <?php while ($r = mysqli_fetch_assoc($res)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['name']) ?></td>
                                    <td><?= htmlspecialchars($r['category_name']) ?></td>
                                    <td><?= $r['status'] == 1 ? '<span class="badge bg-secondary">Ẩn</span>' : '<span class="badge bg-success">Hiển thị</span>' ?></td>
                                    <td class="text-center">
                                        <a href="?p=subcategories&action=edit&id=<?= $r['subcategory_id'] ?>" class="btn btn-info btn-sm"><i class="fas fa-edit" title="Sửa"></i></a>
                                        <a href="?p=subcategories&action=delete&id=<?= $r['subcategory_id'] ?>" onclick="return confirm('Xóa môn học này?');" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt" title="Xóa"></i></a>
                                    </td>
                                </tr>
                        <?php endwhile;
                        endif; ?>
                    </tbody>
                </table>
                <?php if ($total_pages > 1): ?>
                    <nav class="pagination-wrapper mt-4">
                        <ul class="pagination justify-content-center document-pagination">

                            <!-- Trang trước -->
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="?p=subcategories&page=<?= max(1, $page - 1) ?>&keyword=<?= urlencode($keyword) ?>">
                                    &laquo;
                                </a>
                            </li>

                            <?php
                            // Hiển thị tối đa 5 trang cho gọn
                            $start = max(1, $page - 2);
                            $end   = min($total_pages, $page + 2);
                            ?>

                            <?php if ($start > 1): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="?p=subcategories&page=1&keyword=<?= urlencode($keyword) ?>">1</a>
                                </li>
                                <?php if ($start > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">…</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link"
                                        href="?p=subcategories&page=<?= $i ?>&keyword=<?= urlencode($keyword) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end < $total_pages): ?>
                                <?php if ($end < $total_pages - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">…</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="?p=subcategories&page=<?= $total_pages ?>&keyword=<?= urlencode($keyword) ?>">
                                        <?= $total_pages ?>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <!-- Trang sau -->
                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="?p=subcategories&page=<?= min($total_pages, $page + 1) ?>&keyword=<?= urlencode($keyword) ?>">
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