<?php
include 'config.php';
include 'header.php';

/* =========================
1. KIỂM TRA ĐĂNG NHẬP
========================= */
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$current_user = $_SESSION['username'];
$user_role    = (isset($_SESSION['role']) && !empty($_SESSION['role'])) ? $_SESSION['role'] : 'user';

/* =========================
2. LẤY CHỦ ĐỀ
========================= */
$sql_categories = "SELECT category_id, name FROM categories WHERE status = 0 ORDER BY name ASC";
$result_categories = mysqli_query($conn, $sql_categories);

/* =========================
3. XỬ LÝ UPLOAD
========================= */
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document_file'])) {

    $title          = trim($_POST['title']);
    $description    = trim($_POST['description']);
    $subcategory_id = intval($_POST['subcategory_id']);

    if ($subcategory_id <= 0) {
        $error_msg = "Vui lòng chọn đầy đủ chủ đề và môn học.";
    } else {
        $file      = $_FILES['document_file'];
        $file_ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $file_size = $file['size'];

        $allowed_types = ['pdf', 'doc', 'docx', 'zip', 'rar', 'txt', 'jpg', 'png'];
        $max_size = 20 * 1024 * 1024;

        if (!in_array($file_ext, $allowed_types)) {
            $error_msg = "Định dạng file tài liệu .$file_ext không được hỗ trợ.";
        } elseif ($file_size > $max_size) {
            $error_msg = "Dung lượng file tài liệu tối đa 20MB.";
        } else {

            // --- XỬ LÝ ẢNH ĐẠI DIỆN ---
            $thumbnail_name = "";
            if (!empty($_FILES['thumbnail']['name'])) {
                $thumb = $_FILES['thumbnail'];
                $thumbnail_name = time() . "_thumb_" . uniqid() . "." . strtolower(pathinfo($thumb['name'], PATHINFO_EXTENSION));
                move_uploaded_file($thumb['tmp_name'], "uploads/thumbnails/" . $thumbnail_name);
            }

            // --- XỬ LÝ FILE TÀI LIỆU ---
            $original_name = basename($file['name']); 
            $original_name = preg_replace('/\s+/', '_', $original_name);
            $original_name = preg_replace('/[^A-Za-z0-9._-]/', '', $original_name);

            $file_name_db = time() . "_" . $original_name;
            $file_path_full = "uploads/documents/" . $file_name_db;


            if (move_uploaded_file($file['tmp_name'], $file_path_full)) {

                $status = 'pending'; // Trạng thái mặc định
                $is_visible = 0;     // Ẩn mặc định
                $share_link = $file_name_db; 

                $sql = "INSERT INTO documents
                        (title, description, thumbnail, file_path, file_type, file_size, share_link, 
                         subcategory_id, status, is_visible, username, uploader_role, views, downloads, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, NOW())";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param(
                    "sssssisisiss",
                    $title,
                    $description,
                    $thumbnail_name,
                    $file_name_db,
                    $file_ext,
                    $file_size,
                    $share_link,
                    $subcategory_id,
                    $status,
                    $is_visible,
                    $current_user,
                    $user_role
                );

                if ($stmt->execute()) {
                    echo "<script>alert('Đăng tài liệu thành công!');location.href='upload.php';</script>";
                    exit();
                } else {
                    $error_msg = "Lỗi CSDL: " . $stmt->error;
                }
            }
        }
    }
}

?>

<div class="upload-page-wrapper mrt">
    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 main-upload-card">
                    <div class="card-header text-center bg-white py-4  text-primary">
                        <h3 class="fw-bold"><i class="fa fa-cloud-upload-alt"></i> Đăng tài liệu</h3>
                        <p class="text-muted">Chia sẻ kiến thức của bạn với cộng đồng</p>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <?php if ($error_msg): ?>
                            <div class="alert alert-danger"><?= $error_msg ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label class="fw-bold">Tiêu đề tài liệu *</label>
                                <input type="text" name="title" class="form-control custom-input" placeholder="Ví dụ: Đề thi mẫu môn Lập trình PHP" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="fw-bold">Chủ đề *</label>
                                    <select name="category_id" id="category_id" class="form-select custom-select-style" required>
                                        <option value="" disabled selected>-- Chọn chủ đề --</option>
                                        <?php while ($row = mysqli_fetch_assoc($result_categories)): ?>
                                            <option value="<?= $row['category_id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label class="fw-bold">Môn học *</label>
                                    <select name="subcategory_id" id="subcategory_id" class="form-select custom-select-style" required disabled>
                                        <option value="" disabled selected>-- Chọn môn học --</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="fw-bold">Ảnh đại diện tài liệu</label>
                                <input type="file" name="thumbnail" class="form-control custom-input" accept="image/*">
                            </div>

                            <div class="mb-4">
                                <label class="fw-bold">Tệp tài liệu *</label>
                                <input type="file" name="document_file" class="form-control custom-input" required>
                                <div class="form-text small">Hỗ trợ: PDF, DOCX,... tối đa 20MB.</div>
                            </div>

                            <div class="mb-4">
                                <label class="fw-bold">Mô tả</label>
                                <textarea name="description" class="form-control custom-input" rows="4" placeholder="Viết mô tả ngắn gọn giúp mọi người dễ tìm kiếm hơn..."></textarea>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="upload.php" class="btn btn-secondary">Quay lại</a>
                                <button class="btn btn-success px-4">
                                    <i class="fa fa-upload"></i> Tải lên ngay
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('category_id').addEventListener('change', function() {
        const id = this.value;
        const sub = document.getElementById('subcategory_id');
        if (!id) {
            sub.innerHTML = '<option value="">-- Chọn môn học --</option>';
            sub.disabled = true;
            return;
        }
        sub.disabled = false;
        sub.innerHTML = '<option>Đang tải dữ liệu...</option>';
        fetch('get_subcategories.php?category_id=' + id)
            .then(res => res.text())
            .then(html => {
                sub.innerHTML = html;
            })
            .catch(() => {
                sub.innerHTML = '<option>Lỗi tải dữ liệu</option>';
            });
    });
</script>

<?php include "footer.php"; ?>