<?php include("header.php"); ?>
<?php include("config.php"); ?>

<?php
// --- QUAN TRỌNG: KIỂM TRA ĐĂNG NHẬP ---
// Nếu header.php chưa có session_start() thì bỏ comment dòng dưới
// session_start(); 

if (!isset($_SESSION['username'])) {
    echo "<script>
        alert('Bạn cần đăng nhập để thực hiện chức năng này!');
        window.location.assign('login.php');
    </script>";
    exit(); // Dừng code ngay lập tức nếu chưa đăng nhập
}

// Khởi tạo biến thông báo
$msg = "";
$msg_type = ""; // success hoặc danger

// XỬ LÝ KHI NGƯỜI DÙNG BẤM NÚT UPLOAD
if (isset($_POST['btn_upload'])) {
    
    // 1. Lấy dữ liệu từ Form
    $title       = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category_id = intval($_POST['category_id']);
    
    // --- SỬA LỖI TẠI ĐÂY ---
    // Lấy chính xác người dùng đang đăng nhập từ Session
    $username = $_SESSION['username']; 
    // -----------------------

    // 2. Xử lý File Upload
    $target_dir = "uploads/documents/"; // Thư mục lưu file
    
    // Kiểm tra thư mục có tồn tại không, nếu không thì tạo mới
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_name     = $_FILES['file_upload']['name'];
    $file_tmp      = $_FILES['file_upload']['tmp_name'];
    $file_size     = $_FILES['file_upload']['size'];
    $file_error    = $_FILES['file_upload']['error'];

    // Lấy đuôi file (extension)
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Tạo tên file mới để tránh trùng lặp (vd: 1709123456_baitap.pdf)
    $new_file_name = time() . '_' . $file_name;
    $target_file = $target_dir . $new_file_name;

    // Các định dạng cho phép
    $allowed_ext = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'png', 'zip', 'rar'];

    // VALIDATION (Kiểm tra lỗi)
    if (empty($title)) {
        $msg = "Vui lòng nhập tiêu đề tài liệu!";
        $msg_type = "danger";
    } elseif ($file_error !== 0) {
        $msg = "Vui lòng chọn file hợp lệ!";
        $msg_type = "danger";
    } elseif (!in_array($file_ext, $allowed_ext)) {
        $msg = "Định dạng file không hỗ trợ! Chỉ cho phép: PDF, Word, Excel, Ảnh, Zip.";
        $msg_type = "danger";
    } elseif ($file_size > 52428800) { // 50MB (50 * 1024 * 1024)
        $msg = "File quá lớn! Vui lòng chọn file dưới 50MB.";
        $msg_type = "danger";
    } else {
        // 3. Tiến hành Upload và Lưu CSDL
        if (move_uploaded_file($file_tmp, $target_file)) {
            
            // Câu lệnh INSERT
            $sql = "INSERT INTO document_uploads 
                    (title, description, file_path, file_type, file_size, category_id, status, username, created_at) 
                    VALUES 
                    ('$title', '$description', '$target_file', '$file_ext', '$file_size', '$category_id', 1, '$username', NOW())";

            if (mysqli_query($conn, $sql)) {
                $msg = "Upload tài liệu thành công!";
                $msg_type = "success";
                // Reset form sau khi up thành công (nếu muốn)
                $title = ""; $description = ""; 
            } else {
                $msg = "Lỗi CSDL: " . mysqli_error($conn);
                $msg_type = "danger";
                // Xóa file nếu insert db lỗi để tránh rác
                unlink($target_file);
            }
        } else {
            $msg = "Đã xảy ra lỗi khi tải file lên Server.";
            $msg_type = "danger";
        }
    }
}
?>

<div class="container mrt">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="bi bi-cloud-arrow-up"></i> Đăng tải tài liệu mới</h4>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($msg)): ?>
                        <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show" role="alert">
                            <?= $msg ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tiêu đề tài liệu <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="Nhập tên tài liệu..." required value="<?= isset($title) ? $title : '' ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Mô tả ngắn</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Giới thiệu sơ lược về tài liệu..."><?= isset($description) ? $description : '' ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Danh mục <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">-- Chọn danh mục --</option>
                                    <option value="1">Công nghệ thông tin</option>
                                    <option value="2">Kinh tế - Tài chính</option>
                                    <option value="3">Ngoại ngữ</option>
                                    <option value="4">Khác</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Chọn File <span class="text-danger">*</span></label>
                                <input type="file" name="file_upload" class="form-control" required>
                                <div class="form-text">Hỗ trợ: PDF, Word, Excel, Ảnh, Zip (Max: 50MB)</div>
                            </div>
                        </div>

                        <div class="mb-3 text-muted small">
                            Người đăng: <strong><?= isset($_SESSION['username']) ? $_SESSION['username'] : 'Chưa đăng nhập' ?></strong>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="upload.php" class="btn btn-secondary me-2">
                                <i class="bi bi-arrow-left"></i> Quay lại
                            </a>
                            <button type="submit" name="btn_upload" class="btn btn-success">
                                <i class="bi bi-upload"></i> Đăng tải ngay
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>