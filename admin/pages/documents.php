<?php

// Kiểm tra kết nối DB
if (!isset($conn) || $conn === false) {
    die('<div class="alert alert-danger">LỖI KẾT NỐI: Biến $conn không tồn tại.</div>');
}

// Khởi tạo biến
$message = "";
$data = [
    'document_id' => null,
    'title' => '',
    'description' => '',
    'file_path' => '',
    'file_type' => '',
    'subcategory_id' => null,
    'status' => 0
];

$action = $_GET['action'] ?? '';
$current_view = ($action === 'add' || $action === 'edit') ? 'form' : 'list';

$page_title = match ($action) {
    'add' => 'Thêm tài liệu mới',
    'edit' => 'Cập nhật tài liệu',
    default => 'Danh sách tài liệu'
};

// Xử lý form submit
if (isset($_POST['save_document'])) {

    $id = (int)($_POST['document_id'] ?? 0);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $subcategory_id = (int)($_POST['subcategory_id'] ?? 0);
    $status = (int)($_POST['status']);
    $username = $_SESSION['username'] ?? 'system';

    // Xử lý upload file
    $file_path = $data['file_path'];
    $file_type = $data['file_type'];
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/documents/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $filename = time().'_'.basename($_FILES['file']['name']);
        $target = $uploadDir.$filename;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
            $file_path = $target;
            $file_type = pathinfo($filename, PATHINFO_EXTENSION);
        } else {
            $message = "Upload file thất bại!";
            $current_view = 'form';
            goto end_form; // dừng các bước tiếp theo
        }
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
        mysqli_stmt_close($stmt_check);
        goto end_form; // dừng các bước tiếp theo
    }
    mysqli_stmt_close($stmt_check);

    // Thêm / Sửa
    if ($id > 0) {
        // UPDATE
        $sql = "UPDATE documents SET title=?, description=?, file_path=?, file_type=?, subcategory_id=?, status=?, username=? WHERE document_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssiisi", $title, $description, $file_path, $file_type, $subcategory_id, $status, $username, $id);
        $success_msg = "Cập nhật tài liệu thành công!";
    } else {
        // INSERT
        $sql = "INSERT INTO documents (title, description, file_path, file_type, subcategory_id, status, username, views, shares, share_link) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        $share_link = $file_path;
        mysqli_stmt_bind_param($stmt, "sssssiis", $title, $description, $file_path, $file_type, $subcategory_id, $status, $username, $share_link);
        $success_msg = "Thêm tài liệu thành công!";
    }

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('$success_msg'); window.location.href='?p=documents';</script>";
        exit;
    } else {
        $message = "Lỗi SQL: ".mysqli_stmt_error($stmt);
    }
    mysqli_stmt_close($stmt);

    end_form:
    ;
}

// Xử lý delete
if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($conn, "DELETE FROM documents WHERE document_id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Xóa tài liệu thành công!'); window.location.href='?p=documents';</script>";
        exit;
    } else {
        $message = "Lỗi xóa: ".mysqli_stmt_error($stmt);
    }
    mysqli_stmt_close($stmt);
}

// Xử lý edit
if ($action == 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM documents WHERE document_id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $rs = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($rs)) {
        $data = $row;
    } else {
        $message = "Không tìm thấy tài liệu!";
        $current_view = 'list';
    }
    mysqli_stmt_close($stmt);
}
$is_edit = !empty($data['document_id']);
?>

<!-- ================= HTML ================= -->
<div class="card shadow">
    <div class="card-header bg-gradient-<?php echo ($current_view=='list')?'dark':'primary'; ?> text-white d-flex align-items-center">
        <h4 class="mb-0"><?= $page_title ?></h4>
        <?php if ($current_view=='list'): ?>
            <a href="?p=documents&action=add" class="btn btn-warning ms-auto px-3"><i class="fas fa-plus-circle me-1"></i> Thêm mới</a>
        <?php endif; ?>
    </div>
    <div class="card-body">

        <?php if ($message): ?>
            <div class="alert alert-danger"><?= $message ?></div>
        <?php endif; ?>

        <?php if ($current_view=='form'): ?>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="document_id" value="<?= htmlspecialchars($data['document_id']) ?>">

            <div class="mb-3">
                <label class="form-label fw-bold">Tiêu đề *</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($data['title']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Mô tả</label>
                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($data['description']) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Danh mục</label>
                <select name="subcategory_id" class="form-select">
                    <option value="">-- Chọn danh mục --</option>
                    <?php
                    $cats = mysqli_query($conn, "SELECT * FROM subcategories ORDER BY name ASC");
                    while ($c=mysqli_fetch_assoc($cats)):
                    ?>
                    <option value="<?= $c['subcategory_id'] ?>" <?= $c['subcategory_id']==$data['subcategory_id']?'selected':'' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">File</label>
                <input type="file" name="file" class="form-control">
                <?php if(!empty($data['file_path'])): ?>
                    <small>Hiện tại: <a href="<?= $data['file_path'] ?>" target="_blank"><?= basename($data['file_path']) ?></a></small>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Trạng thái</label>
                <select name="status" class="form-select" style="width:130px;">
                    <option value="0" <?= $data['status']==0?'selected':'' ?>>Hiển thị</option>
                    <option value="1" <?= $data['status']==1?'selected':'' ?>>Ẩn</option>
                </select>
            </div>

            <div class="text-end pt-3">
                <button class="btn btn-success px-4" name="save_document"><i class="fas fa-save"></i> <?= $is_edit?'Cập nhật':'Thêm mới' ?></button>
                <a href="?p=documents" class="btn btn-secondary px-3">Quay lại</a>
            </div>
        </form>

        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Tiêu đề</th>
                        <th>Mô tả</th>
                        <th>Danh mục</th>
                        <th>File</th>
                        <th>Trạng thái</th>
                        <th>Người tạo</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = mysqli_query($conn, "SELECT d.*, s.name as category_name FROM documents d LEFT JOIN subcategories s ON d.subcategory_id=s.subcategory_id ORDER BY d.document_id DESC");
                    if(!$res || mysqli_num_rows($res)==0):
                    ?>
                    <tr><td colspan="7" class="text-center py-4">Chưa có tài liệu</td></tr>
                    <?php else: ?>
                    <?php while($r=mysqli_fetch_assoc($res)): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['title']) ?></td>
                            <td><?= htmlspecialchars($r['description']) ?></td>
                            <td><?= htmlspecialchars($r['category_name']) ?></td>
                            <td><?php if($r['file_path']): ?><a href="<?= $r['file_path'] ?>" target="_blank">Xem file</a><?php endif; ?></td>
                            <td>
                                <?= $r['status']==1?'<span class="badge bg-secondary">Ẩn</span>':'<span class="badge bg-success">Hiển thị</span>' ?>
                            </td>
                            <td>Admin</td>
                            <td class="text-center">
                                <a href="?p=documents&action=edit&id=<?= $r['document_id'] ?>" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
                                <a href="?p=documents&action=delete&id=<?= $r['document_id'] ?>" onclick="return confirm('Xóa tài liệu này?');" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
