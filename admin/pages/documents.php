<?php
// Kiểm tra kết nối DB
if (!isset($conn) || $conn === false) {
    die('<div class="alert alert-danger">LỖI KẾT NỐI: Biến $conn không tồn tại.</div>');
}

// Khởi tạo biến
$message = "";
$data = [
    'document_id' => '',
    'title' => '',
    'description' => '',
    'thumbnail' => '',
    'file_path' => '',
    'file_type' => '',
    'subcategory_id' => '',
    'status' => 0
];

$action = $_GET['action'] ?? '';
$current_view = ($action === 'add' || $action === 'edit') ? 'form' : 'list';
$base_url = '?p=documents';

$page_title = match ($action) {
    'add' => 'Thêm tài liệu mới',
    'edit' => 'Cập nhật tài liệu',
    default => 'Danh sách tài liệu'
};

/* ============================================================
    1. XỬ LÝ LƯU (THÊM / SỬA)
============================================================ */
if (isset($_POST['save_document'])) {

    $id = (int)($_POST['document_id'] ?? 0);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $subcategory_id = (int)($_POST['subcategory_id'] ?? 0);
    $status = (int)($_POST['status']);
    $username = $_SESSION['username'] ?? 'system';

    // A. XỬ LÝ THUMBNAIL (Giống Slideshow)
    $thumbnail = $_POST['old_thumbnail'] ?? ''; // Giữ tên file cũ
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $folderThumb = '../uploads/thumbnails/';
        if (!is_dir($folderThumb)) mkdir($folderThumb, 0777, true);

        // Xóa ảnh cũ nếu có
        if (!empty($_POST['old_thumbnail']) && file_exists($folderThumb . $_POST['old_thumbnail'])) {
            @unlink($folderThumb . $_POST['old_thumbnail']);
        }

        $thumbName = time() . "_thumb_" . basename($_FILES["thumbnail"]["name"]);
        move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $folderThumb . $thumbName);
        $thumbnail = $thumbName; // Chỉ lưu tên file
    }

    // B. XỬ LÝ FILE TÀI LIỆU 
    $file_path = $_POST['old_file'] ?? '';
    $file_type = $_POST['old_file_type'] ?? '';
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $folderDoc = '../uploads/documents/';
        if (!is_dir($folderDoc)) mkdir($folderDoc, 0777, true);

        // Xóa file cũ nếu có
        if (!empty($_POST['old_file']) && file_exists($folderDoc . $_POST['old_file'])) {
            @unlink($folderDoc . $_POST['old_file']);
        }

        $fileName = time() . "_" . basename($_FILES["file"]["name"]);
        move_uploaded_file($_FILES["file"]["tmp_name"], $folderDoc . $fileName);

        $file_path = $fileName; // Chỉ lưu tên file
        $file_type = pathinfo($fileName, PATHINFO_EXTENSION);
    }

    // Kiểm tra trùng title
    $check_sql = "SELECT document_id FROM documents WHERE title=? AND document_id!=?";
    $stmt_check = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt_check, "si", $title, $id);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);
    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        $message = "Tiêu đề '$title' đã tồn tại!";
        $current_view = 'form';
    } else {
        if ($id > 0) {
            // UPDATE
            $sql = "UPDATE documents SET title=?, description=?, thumbnail=?, file_path=?, file_type=?, subcategory_id=?, status=?, username=? WHERE document_id=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssiisi", $title, $description, $thumbnail, $file_path, $file_type, $subcategory_id, $status, $username, $id);
            $success_msg = "Cập nhật tài liệu thành công!";
        } else {
            // INSERT
            $sql = "INSERT INTO documents (title, description, thumbnail, file_path, file_type, subcategory_id, status, username, views, share_link, downloads)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, 0)";
            $stmt = mysqli_prepare($conn, $sql);
            $share_link = $file_path; // link chia sẻ chỉ lưu tên file
            mysqli_stmt_bind_param($stmt, "sssssiiss", $title, $description, $thumbnail, $file_path, $file_type, $subcategory_id, $status, $username, $share_link);
            $success_msg = "Thêm tài liệu thành công!";
        }

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('$success_msg'); window.location.href='$base_url';</script>";
            exit;
        } else {
            $message = "Lỗi SQL: " . mysqli_error($conn);
        }
    }
}

/* ============================================================
    2. XỬ LÝ XÓA (Xóa cả file vật lý)
============================================================ */
if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Lấy thông tin file để xóa vật lý trước khi xóa DB
    $res = mysqli_query($conn, "SELECT thumbnail, file_path FROM documents WHERE document_id=$id");
    if ($row = mysqli_fetch_assoc($res)) {
        if (!empty($row['thumbnail'])) @unlink("../uploads/thumbnails/" . $row['thumbnail']);
        if (!empty($row['file_path'])) @unlink("../uploads/documents/" . $row['file_path']);
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM documents WHERE document_id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Xóa tài liệu thành công!'); window.location.href='$base_url';</script>";
        exit;
    }
}

// Xử lý edit để lấy dữ liệu đổ vào form
if ($action == 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $res = mysqli_query($conn, "SELECT * FROM documents WHERE document_id=$id");
    if ($row = mysqli_fetch_assoc($res)) $data = $row;
}
$is_edit = !empty($data['document_id']);

$subcategories = [];
$resSub = mysqli_query($conn, "SELECT subcategory_id, name FROM subcategories ORDER BY name ASC");
while ($rowSub = mysqli_fetch_assoc($resSub)) {
    $subcategories[] = $rowSub;
}

// =====================
// PHÂN TRANG + TÌM KIẾM
// =====================
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$keyword = trim($_GET['keyword'] ?? '');

$where = '';
if ($keyword !== '') {
    $safe_keyword = mysqli_real_escape_string($conn, $keyword);
    $where = "WHERE d.title LIKE '%$safe_keyword%' 
              OR d.description LIKE '%$safe_keyword%'";
}

// ĐẾM TỔNG
$countSql = "
    SELECT COUNT(*) AS total 
    FROM documents d
    $where
";

$countRes = mysqli_query($conn, $countSql);

// BẢO VỆ CHỐNG NULL
$totalDocs = 0;
if ($countRes) {
    $totalRow = mysqli_fetch_assoc($countRes);
    $totalDocs = (int)($totalRow['total'] ?? 0);
}

$totalPages = ceil($totalDocs / $limit);

?>

<div class="card shadow">
    <div class="card-header bg-gradient-<?php echo ($current_view == 'list') ? 'dark' : 'primary'; ?> text-white d-flex align-items-center">
        <h4 class="mb-0"><?= $page_title ?></h4>
        <?php if ($current_view == 'list'): ?>
            <a href="<?= $base_url ?>&action=add" class="btn btn-warning ms-auto px-3"><i class="fas fa-plus-circle me-1"></i> Thêm mới</a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($message): ?>
            <div class="alert alert-danger"><?= $message ?></div>
        <?php endif; ?>

        <?php if ($current_view == 'form'): ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="document_id" value="<?= $data['document_id'] ?>">
                <input type="hidden" name="old_thumbnail" value="<?= $data['thumbnail'] ?>">
                <input type="hidden" name="old_file" value="<?= $data['file_path'] ?>">
                <input type="hidden" name="old_file_type" value="<?= $data['file_type'] ?>">

                <!-- TIÊU ĐỀ -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Tiêu đề *</label>
                    <input type="text" name="title" class="form-control"
                        value="<?= htmlspecialchars($data['title']) ?>" required>
                </div>

                <!-- MÔ TẢ -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Mô tả</label>
                    <textarea name="description" class="form-control" rows="4"
                        placeholder="Nhập mô tả tài liệu..."><?= htmlspecialchars($data['description']) ?></textarea>
                </div>

                <!-- DANH MỤC CON -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Danh mục *</label>
                    <select name="subcategory_id" class="form-select" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($subcategories as $sub): ?>
                            <option value="<?= $sub['subcategory_id'] ?>"
                                <?= ($sub['subcategory_id'] == $data['subcategory_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sub['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>


                <!-- ẢNH ĐẠI DIỆN -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Ảnh đại diện</label>
                    <input type="file" name="thumbnail" class="form-control mb-2" accept="image/*">
                    <?php if (!empty($data['thumbnail'])): ?>
                        <img src="../uploads/thumbnails/<?= $data['thumbnail'] ?>"
                            width="100" class="img-thumbnail border">
                    <?php endif; ?>
                </div>

                <!-- FILE TÀI LIỆU -->
                <div class="mb-3">
                    <label class="form-label fw-bold">File tài liệu</label>
                    <input type="file" name="file" class="form-control mb-2">
                    <?php if (!empty($data['file_path'])): ?>
                        <small class="text-muted">
                            File hiện tại: <strong><?= $data['file_path'] ?></strong>
                        </small>
                    <?php endif; ?>
                </div>

                <!-- TRẠNG THÁI -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="0" <?= $data['status'] == 0 ? 'selected' : '' ?>>Hiển thị</option>
                        <option value="1" <?= $data['status'] == 1 ? 'selected' : '' ?>>Ẩn</option>
                    </select>
                </div>

                <!-- BUTTON -->
                <div class="text-end pt-3">
                    <button class="btn btn-success px-4" name="save_document">
                        <i class="fas fa-save me-1"></i> <?= $is_edit ? 'Cập nhật' : 'Thêm mới' ?>
                    </button>
                    <a href="<?= $base_url ?>" class="btn btn-secondary px-3">Quay lại</a>
                </div>
            </form>
        <?php else: ?>
            <form method="get" class="row g-2 mb-3 justify-content-end">
                <input type="hidden" name="p" value="documents">

                <div class="col-md-4">
                    <input type="text"
                        name="keyword"
                        class="form-control"
                        placeholder="Tìm tài liệu..."
                        value="<?= htmlspecialchars($keyword) ?>">
                </div>

                <div class="col-md-auto">
                    <button class="btn btn-primary px-4">
                        <i class="fas fa-search"></i> Tìm
                    </button>
                    <a href="<?= $base_url ?>" class="btn btn-secondary">
                        <i class="fas fa-sync"></i>
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="80">Ảnh</th>
                            <th>Tiêu đề</th>
                            <th>Mô tả</th>
                            <th>Danh mục</th>
                            <th>File</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $res = mysqli_query($conn, "
                            SELECT d.*, s.name as category_name 
                            FROM documents d 
                            LEFT JOIN subcategories s 
                                ON d.subcategory_id = s.subcategory_id
                            $where
                            ORDER BY d.document_id DESC
                            LIMIT $limit OFFSET $offset
                        ");

                        while ($r = mysqli_fetch_assoc($res)):
                        ?>
                            <tr>
                                <td>
                                    <?php if ($r['thumbnail']): ?>
                                        <img src="../uploads/thumbnails/<?= $r['thumbnail'] ?>" width="55" height="75" style="object-fit: cover;" class="border">
                                    <?php else: ?>
                                        <span class="text-muted small">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($r['title']) ?></strong></td>
                                <td><?= nl2br(htmlspecialchars($r['description'])) ?></td>
                                <td>
                                    <?= $r['category_name']
                                        ? htmlspecialchars($r['category_name'])
                                        : '<span class="text-muted small">Chưa phân loại</span>' ?>
                                </td>
                                <td>
                                    <?php if ($r['file_path']): ?>
                                        <a href="../uploads/documents/<?= $r['file_path'] ?>" target="_blank" class="badge bg-info text-decoration-none">Xem file</a>
                                    <?php endif; ?>
                                </td>
                                <td><?= $r['status'] == 0 ? '<span class="badge bg-success">Hiển thị</span>' : '<span class="badge bg-secondary">Ẩn</span>' ?></td>
                                <td class="text-center">
                                    <a href="<?= $base_url ?>&action=edit&id=<?= $r['document_id'] ?>" class="btn btn-info btn-sm" title="Sửa" style="margin-bottom: 10px;"><i class="fas fa-edit"></i></a>
                                    <a href="<?= $base_url ?>&action=delete&id=<?= $r['document_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa tài liệu này?')" title="Xóa"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <!-- PHÂN TRANG -->
            <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">

                        <!-- PREV -->
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link"
                                href="<?= $base_url ?>&page=<?= $page - 1 ?>&keyword=<?= urlencode($keyword) ?>">
                                &laquo;
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link"
                                    href="<?= $base_url ?>&page=<?= $i ?>&keyword=<?= urlencode($keyword) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <!-- NEXT -->
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link"
                                href="<?= $base_url ?>&page=<?= $page + 1 ?>&keyword=<?= urlencode($keyword) ?>">
                                &raquo;
                            </a>
                        </li>

                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>