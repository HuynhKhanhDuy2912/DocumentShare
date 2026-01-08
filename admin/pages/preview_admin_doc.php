<?php
require("../../config.php");

if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
    die('Không có quyền');
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die('ID không hợp lệ');

$sql = "SELECT title, file_path, file_type 
        FROM documents 
        WHERE document_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$doc = mysqli_fetch_assoc($res);

if (!$doc) die('Không tồn tại');

$file = "../uploads/documents/" . $doc['file_path'];
$ext  = strtolower($doc['file_type']);
?>

<h5 class="mb-3"><?= htmlspecialchars($doc['title']) ?></h5>

<?php if ($ext === 'pdf'): ?>
    <iframe src="<?= $file ?> #toolbar=0&navpanes=0&scrollbar=0" style="width:100%;height:600px;border:none"></iframe>

<?php elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
    <img src="<?= $file ?>" class="img-fluid rounded border">

<?php else: ?>
    <div class="alert alert-warning">Không hỗ trợ xem trước</div>
    <a href="<?= $file ?>" class="btn btn-primary" download>
        <i class="fas fa-download"></i> Tải file
    </a>
<?php endif; ?>
