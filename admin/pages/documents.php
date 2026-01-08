<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($conn)) {
    die('Lỗi kết nối CSDL');
}

$message = "";
// $base_url = '?p=documents';
$redirect_page = $_POST['redirect'] ?? 'documents';
$base_url = '?p=' . $redirect_page;
$action = $_GET['action'] ?? '';
$current_view = in_array($action, ['add', 'edit']) ? 'form' : 'list';
$is_edit = ($action === 'edit');

$page_title = match ($action) {
    'add' => 'Thêm tài liệu',
    'edit' => 'Cập nhật tài liệu',
    default => 'Danh sách tài liệu'
};

$data = [
    'document_id' => '',
    'title' => '',
    'description' => '',
    'thumbnail' => '',
    'file_path' => '',
    'file_type' => '',
    'subcategory_id' => '',
    'status' => 'approved',
    'is_visible' => 1
];

$subcategories = [];
$resSub = mysqli_query($conn, "SELECT subcategory_id, name FROM subcategories ORDER BY name ASC");
while ($row = mysqli_fetch_assoc($resSub)) {
    $subcategories[] = $row;
}

/* =====================================================
                    SAVE (ADD / EDIT) 
===================================================== */
if (isset($_POST['save_document'])) {
    $id = (int)($_POST['document_id'] ?? 0);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $subcategory_id = (int)$_POST['subcategory_id'];
    $status = 'approved';
    $is_visible = (int)($_POST['is_visible'] ?? 1);

    $username = $_SESSION['username'] ?? 'system';
    $uploader_role = $_SESSION['role'] ?? 'admin';

    // ===== XỬ LÝ THUMBNAIL =====
    $thumbnail = $_POST['old_thumbnail'] ?? '';
    if (!empty($_FILES['thumbnail']['name'])) {
        $dir = '../uploads/thumbnails/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        if ($thumbnail && file_exists($dir . $thumbnail)) @unlink($dir . $thumbnail);
        $thumbnail = time() . '_' . $_FILES['thumbnail']['name'];
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], $dir . $thumbnail);
    }

    // ===== XỬ LÝ FILE TÀI LIỆU =====
    $file_path = $_POST['old_file'] ?? '';
    $file_type = $_POST['old_file_type'] ?? '';
    if (!empty($_FILES['file']['name'])) {
        $dir = '../uploads/documents/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        if ($file_path && file_exists($dir . $file_path)) @unlink($dir . $file_path);
        $file_path = time() . '_' . $_FILES['file']['name'];
        move_uploaded_file($_FILES['file']['tmp_name'], $dir . $file_path);
        $file_type = pathinfo($file_path, PATHINFO_EXTENSION);
    }

    $approved_at = date('Y-m-d H:i:s');
    $approved_by = $username;

    if ($id > 0) {

        $sql = "UPDATE documents SET
            title=?, description=?, thumbnail=?, file_path=?, file_type=?,
            subcategory_id=?, status=?, is_visible=?, approved_at=?, approved_by=?
            WHERE document_id=?";


        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "sssssisissi",
            $title,
            $description,
            $thumbnail,
            $file_path,
            $file_type,
            $subcategory_id,
            $status,
            $is_visible,
            $approved_at,
            $approved_by,
            $id
        );
        $msg = 'Cập nhật tài liệu thành công!';
    } else {

        $sql = "INSERT INTO documents (
            title, description, thumbnail, file_path, file_type,
            subcategory_id, status, is_visible, username, uploader_role,
            approved_at, approved_by, views, downloads
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,0,0)";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "sssssisissss",
            $title,
            $description,
            $thumbnail,
            $file_path,
            $file_type,
            $subcategory_id,
            $status,
            $is_visible,
            $username,
            $uploader_role,
            $approved_at,
            $approved_by
        );
        $msg = 'Thêm tài liệu thành công!';
    }

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('$msg');location.href='$base_url'</script>";
        exit;
    } else {
        die("Lỗi thực thi SQL: " . mysqli_error($conn));
    }
}

/* =====================================================
             DELETE / LOAD EDIT / PHÂN TRANG
===================================================== */
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $res = mysqli_query($conn, "SELECT thumbnail, file_path FROM documents WHERE document_id=$id");
    if ($r = mysqli_fetch_assoc($res)) {
        @unlink('../uploads/thumbnails/' . $r['thumbnail']);
        @unlink('../uploads/documents/' . $r['file_path']);
    }
    mysqli_query($conn, "DELETE FROM documents WHERE document_id=$id");
    echo "<script>alert('Đã xóa thành công!');location.href='$base_url'</script>";
    exit;
}

if ($is_edit && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $res = mysqli_query($conn, "SELECT * FROM documents WHERE document_id=$id");
    if ($res) $data = mysqli_fetch_assoc($res);
}

$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$keyword = trim($_GET['keyword'] ?? '');
// $where = $keyword ? "WHERE d.title LIKE '%" . mysqli_real_escape_string($conn, $keyword) . "%'" : ""; //Lấy tất cả (kể cả user upload)
$where = "WHERE d.uploader_role = 'admin'";

if ($keyword !== '') {
    $safe = mysqli_real_escape_string($conn, $keyword);
    $where .= " AND d.title LIKE '%$safe%'";
}


// $countRes = mysqli_query($conn, "SELECT COUNT(*) AS total FROM documents d $where"); //Lấy tất cả (kể cả user upload)
$countRes = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM documents d
    $where
");
$totalDocs = mysqli_fetch_assoc($countRes)['total'] ?? 0;
$totalPages = ceil($totalDocs / $limit);
?>

<div class="card shadow">
    <div class="card-header bg-gradient-<?= ($current_view == 'list') ? 'dark' : 'primary'; ?> text-white d-flex align-items-center">
        <h4 class="mb-0"><?= $page_title ?></h4>
        <?php if ($current_view == 'list'): ?>
            <a href="<?= $base_url ?>&action=add" class="btn btn-warning ms-auto px-3"><i class="fas fa-plus-circle me-1"></i> Thêm mới</a>
        <?php endif; ?>
    </div>

    <div class="card-body">
        <?php if ($current_view == 'form'): ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="document_id" value="<?= $data['document_id'] ?>">
                <input type="hidden" name="old_thumbnail" value="<?= $data['thumbnail'] ?>">
                <input type="hidden" name="old_file" value="<?= $data['file_path'] ?>">
                <input type="hidden" name="old_file_type" value="<?= $data['file_type'] ?>">
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect'] ?? 'documents') ?>">

                <div class="mb-3">
                    <label class="form-label fw-bold">Tiêu đề *</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($data['title'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Mô tả</label>
                    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Môn học *</label>
                    <select name="subcategory_id" class="form-select" required>
                        <option value="">-- Chọn môn học --</option>
                        <?php foreach ($subcategories as $sub): ?>
                            <option value="<?= $sub['subcategory_id'] ?>" <?= ($sub['subcategory_id'] == ($data['subcategory_id'] ?? '')) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sub['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Ảnh đại diện</label>
                    <input type="file" name="thumbnail" class="form-control mb-2" accept="image/*">
                    <?php if (!empty($data['thumbnail'])): ?>
                        <img src="../uploads/thumbnails/<?= $data['thumbnail'] ?>" width="100" class="img-thumbnail">
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">File tài liệu</label>
                    <input type="file" name="file" class="form-control mb-2">
                    <?php if (!empty($data['file_path'])): ?>
                        <small class="text-muted d-block">File hiện tại: <strong><?= $data['file_path'] ?></strong></small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Hiển thị trên web</label>
                    <select name="is_visible" class="form-select">
                        <option value="1" <?= ($data['is_visible'] ?? 1) == 1 ? 'selected' : '' ?>>Hiển thị</option>
                        <option value="0" <?= ($data['is_visible'] ?? 1) == 0 ? 'selected' : '' ?>>Ẩn</option>
                    </select>
                </div>

                <div class="text-end pt-3">
                    <button class="btn btn-success px-4" name="save_document">
                        <i class="fas fa-save me-1"></i> <?= $is_edit ? 'Cập nhật' : 'Thêm mới' ?>
                    </button>
                    <a href="javascript:history.back()" class="btn btn-secondary px-3">Quay lại</a>
                </div>
            </form>

        <?php else: ?>
            <form method="get" class="row g-2 mb-3 justify-content-end">
                <input type="hidden" name="p" value="documents">
                <div class="col-md-4">
                    <input type="text" name="keyword" class="form-control" placeholder="Tìm tài liệu..." value="<?= htmlspecialchars($keyword) ?>">
                </div>
                <div class="col-md-auto">
                    <button class="btn btn-primary px-4"><i class="fas fa-search"></i> Tìm</button>
                    <a href="<?= $base_url ?>" class="btn btn-secondary"><i class="fas fa-sync"></i></a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th width="80">Ảnh</th>
                            <th>Tiêu đề</th>
                            <th>Mô tả</th>
                            <th>Môn học</th>
                            <th>File</th>
                            <th width="90">Hiển thị</th>
                            <th width="100">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $resList = mysqli_query($conn, "
                            SELECT d.*, s.name AS subcategory_name
                            FROM documents d
                            LEFT JOIN subcategories s ON d.subcategory_id = s.subcategory_id
                            $where
                            ORDER BY d.document_id DESC
                            LIMIT $limit OFFSET $offset
                        ");

                        while ($r = mysqli_fetch_assoc($resList)): ?>
                            <tr class="text-center">
                                <td>
                                    <?php if ($r['thumbnail']): ?>
                                        <img src="../uploads/thumbnails/<?= $r['thumbnail'] ?>" width="55" height="75" style="object-fit: cover;">
                                    <?php else: ?>
                                        <span class="text-muted small">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($r['title']) ?></strong></td>
                                <td><?= htmlspecialchars($r['description']) ?></td>
                                <td><?= htmlspecialchars($r['subcategory_name'] ?? 'N/A') ?></td>
                                <td>
                                    <a href="#"
                                        class="btn btn-sm btn-info btn-preview"
                                        data-id="<?= $r['document_id'] ?>">
                                        <i class="fas fa-eye"></i> Xem
                                    </a>
                                </td>
                                <td>
                                    <?= $r['is_visible'] ? '<span class="badge bg-success">Hiển thị</span>' : '<span class="badge bg-secondary">Đã ẩn</span>' ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= $base_url ?>&action=edit&id=<?= $r['document_id'] ?>&redirect=documents"
                                        class="btn btn-info btn-sm" title="Sửa"><i class="fas fa-edit"></i></a>

                                    <a href="<?= $base_url ?>&action=delete&id=<?= $r['document_id'] ?>"
                                        class="btn btn-danger btn-sm" onclick="return confirm('Xóa tài liệu này?')"
                                        title="Xóa"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">

                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $base_url ?>&page=<?= $page - 1 ?>&keyword=<?= urlencode($keyword) ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $base_url ?>&page=<?= $i ?>&keyword=<?= urlencode($keyword) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $base_url ?>&page=<?= $page + 1 ?>&keyword=<?= urlencode($keyword) ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL PREVIEW -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> Xem trước tài liệu
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body" id="previewContent">
                <div class="text-center text-muted">
                    <i class="fas fa-spinner fa-spin"></i> Đang tải...
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.btn-preview').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.id;

            document.getElementById('previewContent').innerHTML =
                '<div class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>';

            $('#previewModal').modal('show');

            fetch('pages/preview_admin_doc.php?id=' + id)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('previewContent').innerHTML = html;
                });
        });
    });
</script>