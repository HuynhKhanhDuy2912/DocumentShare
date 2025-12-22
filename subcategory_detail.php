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
    <h4 class="fw-bold mb-4">
        <?= htmlspecialchars($subcategory['name']) ?>
    </h4>

    <div class="row">
        <?php if ($doc_res && mysqli_num_rows($doc_res) > 0): ?>
            <?php while ($doc = mysqli_fetch_assoc($doc_res)): ?>
                <?php
                $thumb = !empty($doc['thumbnail'])
                    ? "uploads/thumbnails/" . $doc['thumbnail']
                    : "assets/img/default-document.jpg";
                ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <a href="document_detail.php?id=<?= $doc['document_id'] ?>"
                       class="text-decoration-none text-dark">
                        <div class="card h-100 shadow-sm">
                            <img src="<?= $thumb ?>" class="card-img-top"
                                 style="height:180px;object-fit:cover">
                            <div class="card-body">
                                <h6 class="card-title small fw-bold">
                                    <?= htmlspecialchars($doc['title']) ?>
                                </h6>
                                <span class="badge bg-secondary">
                                    <?= strtoupper($doc['file_type']) ?>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-muted">
                Chưa có tài liệu trong danh mục này
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "footer.php"; ?>
