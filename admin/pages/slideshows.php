<?php
ob_start();
include("../config.php");

if (!isset($conn) || $conn === false) {
    die('<div class="alert alert-danger m-3">LỖI KẾT NỐI: Không thể kết nối DB.</div>');
}

$base_url = '?p=slideshows';

$page_title = "Quản lý slideshow";
$message = "";
$current_view = "list";

// DATA FORM MẶC ĐỊNH
$data = [
    'slideshow_id' => '',
    'title' => '',
    'description' => '',
    'imageurl' => '',
    'status' => 1
];

/* ============================================================
    1. LƯU (ADD / UPDATE)
============================================================ */
if (isset($_POST['save_slide'])) {

    $slideshow_id = $_POST['slideshow_id'] ?? '';

    // Nếu CẬP NHẬT
    if (!empty($slideshow_id)) {

        $title = $_POST['title'];
        $description = $_POST['description'];
        $status = (int)$_POST['status'];
        $imageurl = $_POST['old_image']; // mặc định giữ ảnh cũ

        // Xử lý upload ảnh mới
        if (isset($_FILES['imageurl']) && $_FILES['imageurl']['error'] == 0) {

            $folder = "../uploads/slideshows/";
            if (!file_exists($folder)) mkdir($folder, 0777, true);

            // Xóa ảnh cũ nếu tồn tại
            if (!empty($_POST['old_image']) && file_exists($folder . $_POST['old_image'])) {
                unlink($folder . $_POST['old_image']);
            }

            // Tạo tên file mới
            $filename = time() . "_" . basename($_FILES["imageurl"]["name"]);

            // Lưu file vào thư mục
            move_uploaded_file($_FILES["imageurl"]["tmp_name"], $folder . $filename);

            // Gán tên mới vào database
            $imageurl = $filename;
        }

        $sql = "UPDATE slideshows SET title=?, description=?, imageurl=?, status=? WHERE slideshow_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssii", $title, $description, $imageurl, $status, $slideshow_id);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Cập nhật Slideshow thành công!'); window.location.href='$base_url';</script>";
            exit;
        } else {
            $message = "Lỗi sửa: " . mysqli_error($conn);
        }
    } else {

        // THÊM mới

        $title = $_POST['title'];
        $description = $_POST['description'];
        $status = (int)$_POST['status'];
        $imageurl = '';

        // Upload ảnh
        if (isset($_FILES['imageurl']) && $_FILES['imageurl']['error'] == 0) {
            $folder = "../uploads/slideshows/";
            if (!file_exists($folder)) mkdir($folder, 0777, true);

            $filename = time() . "_" . basename($_FILES["imageurl"]["name"]);
            move_uploaded_file($_FILES["imageurl"]["tmp_name"], $folder . $filename);
            $imageurl = $filename;
        }

        $sql = "INSERT INTO slideshows (title, description, imageurl, status) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", $title, $description, $imageurl, $status);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Thêm slideshow thành công!'); window.location.href='$base_url';</script>";
            exit;
        } else {
            $message = "Lỗi thêm mới: " . mysqli_error($conn);
        }
    }
}

/* ============================================================
    2. ACTION GET (DELETE / EDIT)
============================================================ */
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // XÓA
    if ($action == 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);

        // Lấy ảnh
        $row = mysqli_fetch_assoc(mysqli_query(
            $conn,
            "SELECT imageurl FROM slideshows WHERE slideshow_id=$id"
        ));

        // Xóa file
        if ($row && !empty($row['imageurl'])) {
            @unlink("uploads/slideshows/" . $row['imageurl']);
        }

        // Xóa DB
        mysqli_query($conn, "DELETE FROM slideshows WHERE slideshow_id=$id");

        echo "<script>alert('Đã xóa slideshow!'); window.location.href='$base_url';</script>";
        exit;
    }


    // MỞ FORM EDIT / ADD
    if ($action == 'add' || $action == 'edit') {

        $current_view = "form";
        $page_title = ($action == 'add') ? "Thêm slideshow mới" : "Cập nhật slideshow";

        if ($action == 'edit' && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $res = mysqli_query($conn, "SELECT * FROM slideshows WHERE slideshow_id=$id");
            if ($row = mysqli_fetch_assoc($res)) {
                $data = $row;
            } else {
                $message = "Không tìm thấy slideshow!";
                $current_view = 'list';
            }
        }
    }
}

$is_edit_mode = ($current_view == 'form' && !empty($data['slideshow_id']));

?>


<!-- ============================================================
          GIAO DIỆN HIỂN THỊ
============================================================ -->

<div class="card shadow">
    <div class="card-header bg-gradient-<?php echo ($current_view == 'list') ? 'dark' : 'primary'; ?> text-white d-flex align-items-center">

        <h4 class="mb-0"><?php echo $page_title; ?></h4>

        <?php if ($current_view == 'list'): ?>
            <a href="<?php echo $base_url; ?>&action=add" class="btn btn-warning ms-auto px-3">
                <i class="fas fa-plus-circle me-1"></i> Thêm mới
            </a>
        <?php endif; ?>

    </div>

    <div class="card-body">

        <?php if ($message): ?>
            <div class="alert alert-danger"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- ====================== FORM ========================== -->
        <?php if ($current_view == 'form'): ?>

            <form method="POST" enctype="multipart/form-data">

                <input type="hidden" name="slideshow_id" value="<?php echo $data['slideshow_id']; ?>">
                <input type="hidden" name="old_image" value="<?php echo $data['imageurl']; ?>">

                <div class="mb-3">
                    <label class="form-label fw-bold">Tiêu đề</label>
                    <input type="text" name="title" class="form-control"
                        value="<?php echo htmlspecialchars($data['title']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Mô tả</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($data['description']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Ảnh slideshow</label>
                    <input type="file" name="imageurl" class="form-control">

                    <?php if (!empty($data['imageurl'])): ?>
                        <div class="mt-2">
                            <img src="../uploads/slideshows/<?php echo $data['imageurl']; ?>" height="70" class="border">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="1" <?php echo ($data['status'] == 1 ? 'selected' : ''); ?>>Hiển thị</option>
                        <option value="0" <?php echo ($data['status'] == 0 ? 'selected' : ''); ?>>Ẩn</option>
                    </select>
                </div>

                <div class="text-end pt-3">
                    <button type="submit" name="save_slide" class="btn btn-success px-4">
                        <i class="fas fa-save me-1"></i>
                        <?php echo ($is_edit_mode ? 'Cập nhật' : 'Thêm mới'); ?>
                    </button>

                    <a href="<?php echo $base_url; ?>" class="btn btn-secondary me-2">Quay lại</a>
                </div>


            </form>

        <?php else: ?>

            <!-- ====================== DANH SÁCH ====================== -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="80">Ảnh</th>
                            <th>Tiêu đề</th>
                            <th>Mô tả</th>
                            <th width="120">Trạng thái</th>
                            <th width="150" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $res = mysqli_query($conn, "SELECT * FROM slideshows ORDER BY slideshow_id DESC");
                        if (mysqli_num_rows($res) > 0):
                            while ($row = mysqli_fetch_assoc($res)):
                        ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($row['imageurl'])): ?>
                                            <img src="../uploads/slideshows/<?php echo $row['imageurl']; ?>" height="55" class="border">
                                        <?php endif; ?>
                                    </td>

                                    <td><strong><?php echo $row['title']; ?></strong></td>
                                    <td><?php echo $row['description']; ?></td>

                                    <td>
                                        <?php if ($row['status'] == 1): ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> Hiển thị</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><i class="fas fa-eye-slash"></i> Ẩn</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <a href="<?php echo $base_url; ?>&action=edit&id=<?php echo $row['slideshow_id']; ?>"
                                            class="btn btn-info btn-sm" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>

                                        <a href="<?php echo $base_url; ?>&action=delete&id=<?php echo $row['slideshow_id']; ?>"
                                            class="btn btn-danger btn-sm" title="Xóa"
                                            onclick="return confirm('Bạn chắc chắn muốn xóa slideshow này?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>

                            <?php endwhile;
                        else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Chưa có slideshow nào</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>