<?php
include 'config.php';
include 'header.php';


/* ==== 1. KIỂM TRA ĐĂNG NHẬP ==== */
if (!isset($_SESSION['username'])) {
    die("Bạn chưa đăng nhập.");
}

$current_user = $_SESSION['username'];
$current_role = $_SESSION['role'] ?? 'user';

/* ==== 2. LẤY ID TÀI LIỆU ==== */
$doc_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($doc_id <= 0) {
    die("Tài liệu không hợp lệ.");
}

/* ==== 3. LẤY TÀI LIỆU (ĐÚNG THEO BẢNG) ==== */
$sql = "
    SELECT 
        d.*,
        c.name  AS category_name,
        sc.name AS subcategory_name
    FROM documents d
    LEFT JOIN subcategories sc ON d.subcategory_id = sc.subcategory_id
    LEFT JOIN categories c ON sc.category_id = c.category_id
    WHERE d.document_id = ?
      AND (d.username = ? OR ? = 'admin')
    LIMIT 1
";


$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $doc_id, $current_user, $current_role);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Bạn không có quyền xem tài liệu này.");
}

$doc = $result->fetch_assoc();

/* ==== 4. KIỂM TRA FILE ==== */
$file_path = "uploads/documents/" . $doc['file_path'];
$file_ext  = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

if (!file_exists($file_path)) {
    die("File không tồn tại trên hệ thống.");
}
?>

<div class="container mrt">
    <div class="card shadow">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-uppercase">Xem trước tài liệu</h5>
            <a href="javascript:history.back()" class="btn btn-sm btn-secondary fw-bold p-2"> Quay lại</a>
        </div>

        <div class="card-body">
            <p><strong>Tiêu đề:</strong> <?= htmlspecialchars($doc['title']) ?></p>

            <p><strong>Chủ đề:</strong>
                <?= htmlspecialchars($doc['category_name'] ?? 'Chưa phân loại') ?>
            </p>

            <p><strong>Môn học:</strong>
                <?= htmlspecialchars($doc['subcategory_name'] ?? 'Chưa phân loại') ?>
            </p>

            <p><strong>Trạng thái:</strong>
                <?php
                if ($doc['status'] === 'approved')
                    echo '<span class="badge bg-success">Đã duyệt</span>';
                elseif ($doc['status'] === 'pending')
                    echo '<span class="badge bg-warning">Chờ duyệt</span>';
                else
                    echo '<span class="badge bg-danger">Từ chối</span>';
                ?>
            </p>


            <hr>

            <?php if ($file_ext === 'pdf'): ?>
                <iframe src="<?= $file_path ?>" width="100%" height="800" style="border:1px solid #ccc;"></iframe>

            <?php elseif (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                <img src="<?= $file_path ?>" class="img-fluid rounded border">

            <?php else: ?>
                <div class="alert alert-info">
                    File <strong>.<?= $file_ext ?></strong> không hỗ trợ xem trước.
                </div>
                <div class="d-flex justify-content-end">
                    <a href="download_my_doc.php?document_id=<?= $doc['document_id'] ?>"
                        class="btn btn-primary">
                        <i class="fas fa-download"></i> Tải tài liệu
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>