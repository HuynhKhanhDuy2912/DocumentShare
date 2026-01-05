<?php
session_start();
include 'config.php';

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$current_user = $_SESSION['username'];

// 2. LẤY DANH SÁCH DANH MỤC CHA (status = 0)
$sql_categories = "SELECT category_id, name FROM categories WHERE status = 0 ORDER BY name ASC";
$result_categories = mysqli_query($conn, $sql_categories);

// 3. XỬ LÝ LOGIC KHI NHẤN "TẢI LÊN NGAY"
$error_msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['document_file'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $category_id = intval($_POST['category_id']);
    $subcategory_id = intval($_POST['subcategory_id']); 
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    $file = $_FILES['document_file'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_name = time() . '_' . uniqid() . '.' . $file_ext;
    $target_dir = "uploads/documents/";
    $target_file = $target_dir . $file_name;
    $file_size = $file['size'];

    $upload_ok = true;
    $allowed_types = ['pdf', 'doc', 'docx', 'zip', 'rar', 'txt', 'jpg', 'png'];

    if (!in_array($file_ext, $allowed_types)) {
        $upload_ok = false;
        $error_msg = "Định dạng file .$file_ext không được hỗ trợ.";
    }

    if ($file_size > 20 * 1024 * 1024) {
        $upload_ok = false;
        $error_msg = "Dung lượng tối đa cho phép là 20MB.";
    }

    if ($upload_ok) {
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            // Cập nhật câu lệnh SQL lưu cả subcategory_id
            $stmt = $conn->prepare("INSERT INTO document_uploads (title, description, category_id, subcategory_id, username, file_path, file_type, file_size, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())");
            $stmt->bind_param("ssiisssi", $title, $description, $category_id, $subcategory_id, $current_user, $target_file, $file_ext, $file_size);
            
            if ($stmt->execute()) {
                echo "<script>alert('Tải lên thành công!'); window.location.href='upload.php';</script>";
                exit();
            } else {
                $error_msg = "Lỗi database: " . $conn->error;
            }
        } else {
            $error_msg = "Không thể di chuyển file vào thư mục lưu trữ.";
        }
    }
}
?>

<?php include("header.php"); ?>

<div class="upload-page-wrapper">
    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 main-upload-card">
                    <div class="card-header bg-white py-4 border-bottom text-center">
                        <h2 class="fw-bold mb-0">
                            <i class="fa fa-cloud-upload-alt me-2"></i>Đăng tài liệu mới
                        </h2>
                        <p class="text-muted mt-2">Chia sẻ kiến thức của bạn với cộng đồng DocumentShare</p>
                    </div>
                    
                    <div class="card-body p-4 p-md-5">
                        <?php if(!empty($error_msg)): ?>
                            <div class="alert alert-danger d-flex align-items-center mb-4 shadow-sm" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div><?= $error_msg ?></div>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Tiêu đề tài liệu <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control custom-input" placeholder="Ví dụ: Đồ án tốt nghiệp Công nghệ thông tin" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold">Danh mục <span class="text-danger">*</span></label>
                                    <select name="category_id" id="category_id" class="form-select custom-select-style" required>
                                        <option value="">-- Chọn danh mục --</option>
                                        <?php if ($result_categories): ?>
                                            <?php while($cate = mysqli_fetch_assoc($result_categories)): ?>
                                                <option value="<?= $cate['category_id'] ?>">
                                                    <?= htmlspecialchars($cate['name']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold">Danh mục con <span class="text-danger">*</span></label>
                                    <select name="subcategory_id" id="subcategory_id" class="form-select custom-select-style" required>
                                        <option value="">-- Chọn danh mục cha trước --</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <label class="form-label fw-bold">Chọn tệp tin <span class="text-danger">*</span></label>
                                    <input type="file" name="document_file" class="form-control custom-input" required>
                                    <div class="form-text mt-2 text-muted">Hỗ trợ: PDF, DOCX, ZIP, JPG (Tối đa 20MB)</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Mô tả tài liệu</label>
                                <textarea name="description" class="form-control custom-input" rows="5" placeholder="Ghi chú ngắn gọn về nội dung chính của tài liệu..."></textarea>
                            </div>

                            <div class="d-flex gap-2 mt-5">                                
                                <a href="upload.php" class="btn btn-small btn-secondary    fw-bold text-decoration-none">
                                    <i class="fa fa-arrow-left"></i> Quay lại
                                </a>
                                <button type="submit" class="btn btn-success btn-small fw-bold shadow-sm py-2 btn-submit-upload ms-auto">
                                    <i class="fa fa-upload me-2"></i>Tải lên và Chia sẻ ngay
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Tổng thể trang */
    body { background-color: #f0f2f5; }
    
    /* Wrapper để fix lỗi Header Fixed đè nội dung */
    .upload-page-wrapper {
        padding-top: 130px; /* Khoảng cách an toàn dưới Header */
        min-height: 100vh;
    }

    /* Tùy chỉnh Card */
    .main-upload-card {
        border-radius: 20px;
        overflow: hidden;
    }

    /* FIX LỖI Ô CHỌN BỊ MẤT NỬA & STYLE ĐỒNG BỘ */
    .custom-input, .custom-select-style {
        border: 1px solid #ced4da !important;
        border-radius: 12px !important;
        padding: 12px 18px !important;
        font-size: 15px !important;
        height: auto !important; /* Quan trọng: để ô không bị bóp nghẹt */
        line-height: 1.6 !important;
        transition: all 0.2s ease;
    }

    .custom-input:focus, .custom-select-style:focus {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.08) !important;
        outline: none;
    }

    /* Nút bấm xanh lá cây phong cách hiện đại */
    .btn-submit-upload {
        background-color: #198754;
        border: none;
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    .btn-submit-upload:hover {
        background-color: #157347;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(25, 135, 84, 0.3) !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');

    categorySelect.addEventListener('change', function() {
        const categoryId = this.value;

        // Reset ô danh mục con và hiện trạng thái tải
        subcategorySelect.innerHTML = '<option value="">Đang tải dữ liệu...</option>';

        if (categoryId !== "") {
            // Fetch API để gọi file xử lý ngầm
            fetch('get_subcategories.php?category_id=' + categoryId)
                .then(response => response.text())
                .then(data => {
                    subcategorySelect.innerHTML = data;
                })
                .catch(error => {
                    console.error('Error:', error);
                    subcategorySelect.innerHTML = '<option value="">Lỗi tải danh mục!</option>';
                });
        } else {
            subcategorySelect.innerHTML = '<option value="">-- Chọn danh mục cha trước --</option>';
        }
    });
});
</script>

<?php include("footer.php"); ?>