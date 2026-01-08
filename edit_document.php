<?php
session_start();
include 'config.php';

/* ================== 1. KIỂM TRA ĐĂNG NHẬP ================== */
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$current_user = $_SESSION['username'];

/* ================== 2. LẤY THÔNG TIN TÀI LIỆU CŨ ================== */
if (!isset($_GET['id'])) {
    header("Location: upload.php");
    exit();
}

$doc_id = intval($_GET['id']);

// Lấy thông tin tài liệu kèm theo category_id (thông qua JOIN với subcategories)
$sql_get = "SELECT d.*, sc.category_id 
            FROM documents d 
            LEFT JOIN subcategories sc ON d.subcategory_id = sc.subcategory_id 
            WHERE d.document_id = ? AND d.username = ?";
$stmt_get = $conn->prepare($sql_get);
$stmt_get->bind_param("is", $doc_id, $current_user);
$stmt_get->execute();
$doc = $stmt_get->get_result()->fetch_assoc();

if (!$doc) {
    echo "<script>alert('Không tìm thấy tài liệu hoặc bạn không có quyền sửa!'); window.location.href='upload.php';</script>";
    exit();
}

// Lấy danh sách danh mục cha
$result_categories = mysqli_query($conn, "SELECT category_id, name FROM categories WHERE status = 0 ORDER BY name ASC");

/* ================== 3. XỬ LÝ CẬP NHẬT ================== */
$error_msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $subcategory_id = intval($_POST['subcategory_id']);
    $description = trim($_POST['description']);

    // Giữ lại giá trị cũ
    $new_file_path = $doc['file_path'];
    $new_file_type = $doc['file_type'];
    $new_file_size = $doc['file_size'];
    $new_thumbnail = $doc['thumbnail'];

    // --- A. XỬ LÝ ẢNH ĐẠI DIỆN MỚI ---
    if (!empty($_FILES['thumbnail']['name'])) {
        $t_file = $_FILES['thumbnail'];
        $t_ext = strtolower(pathinfo($t_file['name'], PATHINFO_EXTENSION));
        if (in_array($t_ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            // Xóa ảnh cũ
            if (!empty($doc['thumbnail']) && file_exists("uploads/thumbnails/" . $doc['thumbnail'])) {
                @unlink("uploads/thumbnails/" . $doc['thumbnail']);
            }
            $new_thumbnail = time() . "_thumb_" . uniqid() . "." . $t_ext;
            move_uploaded_file($t_file['tmp_name'], "uploads/thumbnails/" . $new_thumbnail);
        }
    }

    // --- B. XỬ LÝ FILE TÀI LIỆU MỚI ---
    if (!empty($_FILES['document_file']['name'])) {
        $f_file = $_FILES['document_file'];
        $f_ext = strtolower(pathinfo($f_file['name'], PATHINFO_EXTENSION));
        if ($f_file['size'] <= 20 * 1024 * 1024) {
            // Xóa file cũ
            if (!empty($doc['file_path']) && file_exists("uploads/documents/" . $doc['file_path'])) {
                @unlink("uploads/documents/" . $doc['file_path']);
            }
            $file_name = time() . "_" . uniqid() . "." . $f_ext;
            if (move_uploaded_file($f_file['tmp_name'], "uploads/documents/" . $file_name)) {
                $new_file_path = $file_name;
                $new_file_type = $f_ext;
                $new_file_size = $f_file['size'];
            }
        } else {
            $error_msg = "File mới quá lớn (Tối đa 20MB).";
        }
    }

    // --- C. CẬP NHẬT DATABASE ---
    if (empty($error_msg)) {
        // Khi sửa, đưa trạng thái về 'pending' để Admin duyệt lại
        $status = 'pending';
        $is_visible = 0;

        $sql_up = "UPDATE documents SET 
                   title = ?, description = ?, thumbnail = ?, file_path = ?, 
                   file_type = ?, file_size = ?, subcategory_id = ?, 
                   status = ?, is_visible = ? 
                   WHERE document_id = ? AND username = ?";

        $stmt_up = $conn->prepare($sql_up);
        $stmt_up->bind_param(
            "sssssiisiss",
            $title,
            $description,
            $new_thumbnail,
            $new_file_path,
            $new_file_type,
            $new_file_size,
            $subcategory_id,
            $status,
            $is_visible,
            $doc_id,
            $current_user
        );

        if ($stmt_up->execute()) {
            echo "<script>alert('Cập nhật tài liệu thành công và đang chờ duyệt lại!'); window.location.href='upload.php';</script>";
            exit();
        } else {
            $error_msg = "Lỗi hệ thống: " . $conn->error;
        }
    }
}
?>

<?php include("header.php"); ?>

<div class="edit-wrapper mrt" style="background-color: #f4f7f6; min-height: 100vh; padding-bottom: 50px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0" style="border-radius: 15px;">
                    <div class="card-header bg-white py-4 border-0 text-center">
                        <h2 class="fw-bold text-primary mb-0">Chỉnh sửa tài liệu</h2>
                        <p class="text-muted">Cập nhật thông tin hoặc thay thế file mới</p>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <?php if ($error_msg): ?>
                            <div class="alert alert-danger shadow-sm border-0 mb-4"><?= $error_msg ?></div>
                        <?php endif; ?>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Tiêu đề tài liệu <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control custom-input" value="<?= htmlspecialchars($doc['title']) ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold">Chủ đề <span class="text-danger">*</span></label>
                                    <select name="category_id" id="category_id" class="form-select custom-select-style" required>
                                        <?php while ($cate = mysqli_fetch_assoc($result_categories)): ?>
                                            <option value="<?= $cate['category_id'] ?>" <?= ($cate['category_id'] == $doc['category_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cate['name']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold">Môn học <span class="text-danger">*</span></label>
                                    <select name="subcategory_id" id="subcategory_id" class="form-select custom-select-style" required>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Ảnh đại diện</label>
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <?php if ($doc['thumbnail']): ?>
                                        <img src="uploads/thumbnails/<?= $doc['thumbnail'] ?>" width="80" class="rounded border">
                                    <?php endif; ?>
                                    <input type="file" name="thumbnail" class="form-control" accept="image/*">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Thay đổi file tài liệu (Để trống nếu giữ nguyên)</label>
                                <div class="p-3 bg-light border rounded-3 mb-2">
                                    <small class="text-muted d-block mb-2">File hiện tại: <strong><?= $doc['file_path'] ?></strong></small>
                                    <input type="file" name="document_file" class="form-control">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Mô tả chi tiết</label>
                                <textarea name="description" class="form-control custom-input" rows="5"><?= htmlspecialchars($doc['description']) ?></textarea>
                            </div>

                            <div class="d-grid gap-2 mt-5 d-flex">  
                                <button type="submit" class="btn btn-primary btn-small fw-bold shadow-sm py-3 btn-update">
                                    <i class="fa fa-save me-2"></i>Lưu thay đổi
                                </button>                             
                                <a href="upload.php" class="btn btn-secondary btn-small text-decoration-none text-white py-3 fw-bold">Hủy bỏ</a>                                
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function loadSubcategories(categoryId, selectedSubId = null) {
        var subSelect = document.getElementById('subcategory_id');
        if (categoryId !== "") {
            fetch('get_subcategories.php?category_id=' + categoryId)
                .then(response => response.text())
                .then(data => {
                    subSelect.innerHTML = data;
                    if (selectedSubId) {
                        subSelect.value = selectedSubId;
                    }
                });
        }
    }

    window.onload = function() {
        var currentCateId = document.getElementById('category_id').value;
        var currentSubId = "<?= $doc['subcategory_id'] ?>";
        loadSubcategories(currentCateId, currentSubId);
    };

    document.getElementById('category_id').addEventListener('change', function() {
        loadSubcategories(this.value);
    });
</script>

<?php include("footer.php"); ?>