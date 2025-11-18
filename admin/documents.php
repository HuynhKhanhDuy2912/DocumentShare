<?php include("includes/header.php"); ?>
<?php require("../config.php"); ?>

<h3 class="mb-3">📚 Danh sách tài liệu</h3>

<a href="document_add.php" class="btn btn-primary mb-3">➕ Thêm tài liệu</a>

<table class="table table-bordered table-hover bg-white">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Tiêu đề</th>
            <th>Người upload</th>
            <th>Lượt xem</th>
            <th>Trạng thái</th>
            <th width="150">Hành động</th>
        </tr>
    </thead>
    <tbody>

<?php
$sql = "SELECT * FROM documents ORDER BY document_id DESC";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
?>
        <tr>
            <td><?= $row['document_id'] ?></td>
            <td><?= $row['title'] ?></td>
            <td><?= $row['username'] ?></td>
            <td><?= $row['views'] ?></td>
            <td><?= $row['status'] == 1 ? 'Hiển thị' : 'Ẩn' ?></td>
            <td>
                <a href="document_edit.php?id=<?= $row['document_id'] ?>" class="btn btn-sm btn-warning">Sửa</a>
                <a onclick="return confirm('Xóa tài liệu này?')" 
                   href="document_delete.php?id=<?= $row['document_id'] ?>" 
                   class="btn btn-sm btn-danger">Xóa</a>
            </td>
        </tr>
<?php } ?>

    </tbody>
</table>

<?php include("includes/footer.php"); ?>
