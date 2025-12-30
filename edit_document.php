<?php
session_start();
include 'config.php';

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$current_user = $_SESSION['username'];

// 2. LẤY THÔNG TIN TÀI LIỆU CŨ
if (!isset($_GET['id'])) {
    header("Location: manage_documents.php");
    exit();
}

$doc_id = intval($_GET['id']);
$stmt_get = $conn->prepare("SELECT * FROM document_uploads WHERE document_id = ? AND username = ?");
$stmt_get->bind_param("is", $doc_id, $current_user);
$stmt_get->execute();
$res_doc = $stmt_get->get_result();
$doc = $res_doc->fetch_assoc();

if (!$doc) {
    echo "<script>alert('Không tìm thấy tài liệu!'); window.location.href='manage_documents.php';</script>";
    exit();
}

// Lấy danh mục để đổ vào Select
$sql_categories = "SELECT category_id, name FROM categories WHERE status = 0 ORDER BY name ASC";
$result_categories = mysqli_query($conn, $sql_categories);

// 3. XỬ LÝ CẬP NHẬT DỮ LIỆU
$error_msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $category_id = intval($_POST['category_id']);
    $subcategory_id = intval($_POST['subcategory_id']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    // Biến giữ đường dẫn file hiện tại
    $new_file_path = $doc['file_path'];
    $new_file_type = $doc['file_type'];
    $new_file_size = $doc['file_size'];

    // KIỂM TRA XEM CÓ TẢI FILE MỚI LÊN KHÔNG
    if (!empty($_FILES['document_file']['name'])) {
        $file = $_FILES['document_file'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_types = ['pdf', 'doc', 'docx', 'zip', 'rar', 'txt', 'jpg', 'png'];

        if (!in_array($file_ext, $allowed_types)) {
            $error_msg = "Định dạng file mới không hợp lệ.";
        } elseif ($file['size'] > 20 * 1024 * 1024) {
            $error_msg = "File mới quá lớn (Max 20MB).";
        } else {
            // 1. Xóa file cũ khỏi thư mục uploads (nếu tồn tại)
            if (file_exists($doc['file_path'])) {
                unlink($doc['file_path']);
            }

            // 2. Lưu file mới
            $file_name = time() . '_' . uniqid() . '.' . $file_ext;
            $target_dir = "uploads/documents/";
            $new_file_path = $target_dir . $file_name;
            
            if (move_uploaded_file($file['tmp_name'], $new_file_path)) {
                $new_file_type = $file_ext;
                $new_file_size = $file['size'];
            } else {
                $error_msg = "Lỗi khi lưu file mới.";
            }
        }
    }

    // CẬP NHẬT DATABASE
    if ($error_msg == "") {
        $stmt_up = $conn->prepare("UPDATE document_uploads SET title = ?, description = ?, category_id = ?, subcategory_id = ?, file_path = ?, file_type = ?, file_size = ? WHERE document_id = ? AND username = ?");
        $stmt_up->bind_param("ssisssiis", $title, $description, $category_id, $subcategory_id, $new_file_path, $new_file_type, $new_file_size, $doc_id, $current_user);
        
        if ($stmt_up->execute()) {
            echo "<script>alert('Cập nhật tài liệu thành công!'); window.location.href='upload.php';</script>";
            exit();
        } else {
            $error_msg = "Lỗi database: " . $conn->error;
        }
    }
}
?>

<?php include("header.php"); ?>

<div class="edit-wrapper" style="padding-top: 130px; background-color: #f4f7f6; min-height: 100vh;">
    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0" style="border-radius: 15px;">
                    <div class="card-header bg-white py-4 border-0 text-center">
                        <h2 class="fw-bold text-primary mb-0">Chỉnh sửa tài liệu</h2>
                        <p class="text-muted">Bạn có thể thay đổi thông tin hoặc tải lên file mới thay thế</p>
                    </div>
                    
                    <div class="card-body p-4 p-md-5">
                        <?php if($error_msg): ?>
                            <div class="alert alert-danger shadow-sm border-0 mb-4"><?= $error_msg ?></div>
                        <?php endif; ?>

                        <form action="" method="POST" enctype="multipart/form-data"> <div class="mb-4">
                                <label class="form-label fw-bold">Tiêu đề tài liệu <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control custom-input" value="<?= htmlspecialchars($doc['title']) ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold">Danh mục <span class="text-danger">*</span></label>
                                    <select name="category_id" id="category_id" class="form-select custom-select-style" required>
                                        <option value="">-- Chọn danh mục --</option>
                                        <?php while($cate = mysqli_fetch_assoc($result_categories)): ?>
                                            <option value="<?= $cate['category_id'] ?>" <?= ($cate['category_id'] == $doc['category_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cate['name']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold">Danh mục con <span class="text-danger">*</span></label>
                                    <select name="subcategory_id" id="subcategory_id" class="form-select custom-select-style" required>
                                        </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Thay đổi file tài liệu (Để trống nếu không muốn đổi)</label>
                                <div class="p-3 bg-light border rounded-3 mb-2">
                                    <small class="text-muted d-block mb-2">File hiện tại: <strong><?= basename($doc['file_path']) ?></strong></small>
                                    <input type="file" name="document_file" class="form-control">
                                </div>
                                <div class="form-text">Các định dạng cho phép: PDF, DOCX, ZIP, JPG... (Max 20MB)</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Mô tả chi tiết</label>
                                <textarea name="description" class="form-control custom-input" rows="5"><?= htmlspecialchars($doc['description']) ?></textarea>
                            </div>

                            <div class="d-grid gap-2 mt-5">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm py-3 btn-update">
                                    <i class="bi bi-cloud-check-fill me-2"></i>Cập nhật ngay
                                </button>
                                <a href="upload.php" class="btn btn-link text-decoration-none text-muted mt-2">
                                    Hủy và quay lại
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-input, .custom-select-style { border-radius: 10px !important; padding: 12px 15px; }
    .btn-update { border-radius: 10px; transition: 0.3s; }
    .btn-update:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(13, 110, 253, 0.2) !important; }
</style>

<script>
// Logic AJAX lấy danh mục con khi thay đổi danh mục cha
function loadSubcategories(categoryId, selectedSubId = null) {
    var subSelect = document.getElementById('subcategory_id');
    if (categoryId !== "") {
        fetch('get_subcategories.php?category_id=' + categoryId)
            .then(response => response.text())
            .then(data => {
                subSelect.innerHTML = data;
                // Nếu có ID cũ, tự động chọn lại
                if(selectedSubId) {
                    subSelect.value = selectedSubId;
                }
            });
    } else {
        subSelect.innerHTML = '<option value="">-- Chọn danh mục cha trước --</option>';
    }
}

// Khi trang load: Tải danh mục con dựa trên ID cha hiện tại
window.onload = function() {
    var currentCateId = document.getElementById('category_id').value;
    var currentSubId = "<?= $doc['subcategory_id'] ?>";
    loadSubcategories(currentCateId, currentSubId);
};

// Khi thay đổi danh mục cha
document.getElementById('category_id').addEventListener('change', function() {
    loadSubcategories(this.value);
});
</script>

<?php include("footer.php"); ?>