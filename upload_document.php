<?php
session_start();
include 'config.php';

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
            $file_name_db = time() . "_" . uniqid() . "." . $file_ext;
            $file_path_full = "uploads/documents/" . $file_name_db;

            if (move_uploaded_file($file['tmp_name'], $file_path_full)) {

                $status = 'pending'; // Trạng thái mặc định
                $is_visible = 0;     // Ẩn mặc định
                $share_link = $file_name_db; // share_link bằng tên file theo yêu cầu

                // Câu lệnh SQL 12 tham số (?)
                $sql = "INSERT INTO documents
                        (title, description, thumbnail, file_path, file_type, file_size, share_link, 
                         subcategory_id, status, is_visible, username, uploader_role, views, downloads, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, NOW())";

                $stmt = $conn->prepare($sql);

                /* --- SỬA LỖI TẠI ĐÂY: Chuỗi định dạng sssssisisiss (12 ký tự) --- */
                // 1.title(s), 2.desc(s), 3.thumb(s), 4.path(s), 5.type(s), 6.size(i)
                // 7.link(s), 8.sub_id(i), 9.status(s), 10.visible(i), 11.user(s), 12.role(s)
                $stmt->bind_param(
                    "sssssisisiss",
                    $title,          // 1
                    $description,    // 2
                    $thumbnail_name, // 3
                    $file_name_db,   // 4
                    $file_ext,       // 5
                    $file_size,      // 6 (i)
                    $share_link,     // 7
                    $subcategory_id, // 8 (i)
                    $status,         // 9
                    $is_visible,     // 10 (i)
                    $current_user,   // 11
                    $user_role       // 12
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

// Sửa lỗi hiển thị danh sách bên dưới
$sql_list = "SELECT d.*, sc.name as subcate_name FROM documents d 
             LEFT JOIN subcategories sc ON d.subcategory_id = sc.subcategory_id 
             WHERE d.username = '$current_user' ORDER BY d.document_id DESC";
$result_docs = mysqli_query($conn, $sql_list);

function formatSizeUnits($bytes)
{
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' bytes';
}
?>

<?php include "header.php"; ?>

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

        <div class="card mt-5 border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-primary text-white">
                        <tr class="text-center">
                            <th>Ảnh</th>
                            <th>Tên tài liệu</th>
                            <th>Chuyên mục</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result_docs)): ?>
                            <tr class="text-center">
                                <td><img src="<?= !empty($row['thumbnail']) ? 'uploads/thumbnails/' . $row['thumbnail'] : 'assets/img/default.png' ?>" width="40"></td>
                                <td class="text-start">
                                    <strong><?= htmlspecialchars($row['title']) ?></strong><br>
                                    <small class="text-muted"><?= strtoupper($row['file_type']) ?> • <?= isset($row['file_size']) ? formatSizeUnits($row['file_size']) : '0 bytes' ?></small>
                                </td>
                                <td><?= htmlspecialchars($row['subcate_name'] ?? 'Chưa rõ') ?></td>
                                <td><span class="badge bg-warning"><?= $row['status'] ?></span></td>
                                <td><a href="edit_document.php?id=<?= $row['document_id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-edit"></i></a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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