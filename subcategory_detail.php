<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include "config.php";
include "header.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='container mt-5 alert alert-danger'>Danh mục không hợp lệ</div>";
    include "footer.php";
    exit;
}

$sub_id = (int)$_GET['id'];

// Lấy thông tin danh mục con
$sub_sql = "SELECT name FROM subcategories WHERE subcategory_id = $sub_id AND status = 0";
$sub_res = mysqli_query($conn, $sub_sql);

if (!$sub_res || mysqli_num_rows($sub_res) == 0) {
    echo "<div class='container mt-5 alert alert-warning'>Danh mục không tồn tại</div>";
    include "footer.php";
    exit;
}

$subcategory = mysqli_fetch_assoc($sub_res);

// Lấy tài liệu theo danh mục con
$doc_sql = "SELECT * FROM documents 
            WHERE subcategory_id = $sub_id AND status = 0
            ORDER BY document_id DESC";
$doc_res = mysqli_query($conn, $doc_sql);
?>

<div class="container mrt">
    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
        <h4 class="fw-bold text-uppercase text-primary m-0">
            <i class="bi bi-folder2-open me-2"></i><?= htmlspecialchars($subcategory['name']) ?>
        </h4>
        <span class="text-muted small">Tổng cộng: <?= mysqli_num_rows($doc_res) ?> tài liệu</span>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php if ($doc_res && mysqli_num_rows($doc_res) > 0): ?>
            <?php while ($doc = mysqli_fetch_assoc($doc_res)): ?>
                <?php
                $thumb = !empty($doc['thumbnail'])
                    ? "uploads/thumbnails/" . $doc['thumbnail']
                    : "assets/img/default-document.jpg";
                
                // Xác định màu sắc badge dựa trên loại file
                $badge_class = 'bg-danger'; // Mặc định cho PDF
                if ($doc['file_type'] == 'docx' || $doc['file_type'] == 'doc') $badge_class = 'bg-primary';
                if ($doc['file_type'] == 'pptx') $badge_class = 'bg-warning text-dark';
                ?>
                <div class="col">
                    <div class="card h-100 doc-card shadow-sm">
                        <a href="document_detail.php?id=<?= $doc['document_id'] ?>" class="text-decoration-none">
                            <div class="doc-thumb-container">
                                <span class="badge <?= $badge_class ?> badge-file">
                                    <?= htmlspecialchars($doc['file_type']) ?>
                                </span>
                                <img src="<?= $thumb ?>" class="doc-thumb" alt="<?= htmlspecialchars($doc['title']) ?>">
                            </div>
                            
                            <div class="card-body">
                                <h6 class="doc-title fw-bold mb-2">
                                    <?= htmlspecialchars($doc['title']) ?>
                                </h6>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="text-muted small">
                                        <i class="fa fa-eye me-1"></i> <?= number_format($doc['views'] ?? 0) ?> lượt xem
                                    </div>
                                    <div class="text-muted small">
                                        <i class="bi bi-calendar3 me-1"></i> <?= date('d/m/Y', strtotime($doc['created_at'] ?? 'now')) ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <div class="card-footer bg-transparent border-0 pb-3 pt-0">
                            <a href="document_detail.php?id=<?= $doc['document_id'] ?>" 
                               class="btn btn-outline-primary btn-sm w-100 rounded-pill">
                               Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 py-5 text-center">
                <img src="assets/img/empty.png" alt="Empty" style="width: 150px; opacity: 0.5;">
                <p class="text-muted mt-3">Chưa có tài liệu nào trong danh mục này.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "footer.php"; ?>
