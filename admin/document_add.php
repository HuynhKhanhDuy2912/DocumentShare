<?php include("includes/header.php"); ?>
<?php require("../config.php"); ?>

<?php
if (isset($_POST['add'])) {

    $title = $_POST['title'];
    $desc = $_POST['description'];
    $content = $_POST['content'];
    $status = $_POST['status'];
    $category = $_POST['category_id'];
    $username = $_SESSION['username'];

    // Upload file
    $file = $_FILES['file'];
    $file_path = "uploads/" . time() . "_" . $file['name'];
    move_uploaded_file($file['tmp_name'], "../" . $file_path);

    $file_type = pathinfo($file_path, PATHINFO_EXTENSION);

    $sql = "INSERT INTO document (title, description, content, file_path, file_type, status, category_id, username)
            VALUES ('$title', '$desc', '$content', '$file_path', '$file_type', '$status', '$category', '$username')";

    mysqli_query($conn, $sql);

    header("Location: documents.php");
    exit();
}
?>

<h3>➕ Thêm tài liệu</h3>

<form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label>Tiêu đề</label>
        <input type="text" name="title" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Mô tả ngắn</label>
        <textarea name="description" class="form-control"></textarea>
    </div>

    <div class="mb-3">
        <label>Nội dung</label>
        <textarea name="content" class="form-control" rows="5"></textarea>
    </div>

    <div class="mb-3">
        <label>File tài liệu</label>
        <input type="file" name="file" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Danh mục</label>
        <input type="number" name="category_id" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Trạng thái</label>
        <select name="status" class="form-control">
            <option value="1">Hiển thị</option>
            <option value="0">Ẩn</option>
        </select>
    </div>

    <button name="add" class="btn btn-success">Thêm</button>
</form>

<?php include("includes/footer.php"); ?>
