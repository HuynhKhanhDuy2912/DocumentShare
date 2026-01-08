<?php
session_start();
include "config.php";
include "header.php";

/* ===============================
   LẤY TỪ KHÓA
================================ */
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '') {
    echo "<div class='container mrt h-300'><div class='alert alert-warning'>Vui lòng nhập từ khóa tìm kiếm</div></div>";
    include "footer.php";
    exit;
}

$q_safe = mysqli_real_escape_string($conn, $q);

/* ===============================
   PHÂN TRANG
================================ */
$limit  = 10;
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

/* ===============================
   LẤY DANH SÁCH DOCUMENT ĐÃ LƯU
================================ */
$savedDocs = [];

if (isset($_SESSION['username'])) {
    $u = mysqli_real_escape_string($conn, $_SESSION['username']);
    $rs = mysqli_query($conn, "
        SELECT document_id 
        FROM saved_documents 
        WHERE username = '$u'
    ");
    while ($row = mysqli_fetch_assoc($rs)) {
        $savedDocs[] = (int)$row['document_id'];
    }
}

/* ===============================
   QUERY TÌM KIẾM
================================ */
$sql = "
SELECT d.*
FROM documents d
LEFT JOIN subcategories s ON d.subcategory_id = s.subcategory_id
LEFT JOIN categories c ON s.category_id = c.category_id
WHERE d.status = 'approved' AND d.is_visible = 1
AND (
    d.title LIKE '%$q_safe%' OR
    d.description LIKE '%$q_safe%' OR
    s.name LIKE '%$q_safe%' OR
    c.name LIKE '%$q_safe%'
)
ORDER BY d.document_id DESC
LIMIT $limit OFFSET $offset
";

$result = mysqli_query($conn, $sql);

/* ===============================
   ĐẾM TỔNG KẾT QUẢ
================================ */
$sqlCount = "
SELECT COUNT(*) AS total
FROM documents d
LEFT JOIN subcategories s ON d.subcategory_id = s.subcategory_id
LEFT JOIN categories c ON s.category_id = c.category_id
WHERE d.status = 'approved' AND d.is_visible = 1
AND (
    d.title LIKE '%$q_safe%' OR
    d.description LIKE '%$q_safe%' OR
    s.name LIKE '%$q_safe%' OR
    c.name LIKE '%$q_safe%'
)
";
$totalRow   = mysqli_fetch_assoc(mysqli_query($conn, $sqlCount));
$total      = (int)$totalRow['total'];
$totalPages = ceil($total / $limit);

function countPdfPages($filePath)
{
    if (!file_exists($filePath)) return 0;

    $pdftext = file_get_contents($filePath);
    if ($pdftext === false) return 0;

    // Đếm số /Type /Page trong file PDF
    preg_match_all("/\/Type\s*\/Page[^s]/", $pdftext, $matches);
    return count($matches[0]);
}
?>

<div class="container mt-4 mrt">
    <h4 class="mb-4">
        Kết quả tìm kiếm cho:
        <span class="text-primary">"<?= htmlspecialchars($q) ?>"</span>
        (<?= $total ?> tài liệu)
    </h4>

    <?php if ($total === 0): ?>
        <div class="alert alert-info">Không tìm thấy tài liệu phù hợp.</div>
    <?php else: ?>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-5 g-4">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <?php
                $thumb = !empty($row['thumbnail'])
                    ? "uploads/thumbnails/" . $row['thumbnail']
                    : "assets/img/default-document.jpg";

                $file_ext   = strtolower($row['file_type']);
                $icon_class = ($file_ext === 'pdf') ? 'bg-danger' : 'bg-primary';
                $icon_text  = ($file_ext === 'pdf') ? 'PDF' : 'W';

                $filePath  = "uploads/documents/" . $row['file_path'];
                $pageCount = ($row['file_type'] === 'pdf' && file_exists($filePath))
                    ? countPdfPages($filePath)
                    : 0;

                $isSaved = in_array((int)$row['document_id'], $savedDocs);
                ?>

                <div class="col">
                    <div class="card h-100 border-0 shadow-sm doc-card">

                        <!-- ẢNH -->
                        <div class="position-relative p-2">
                            <span class="badge <?= $icon_class ?> position-absolute top-0 start-0 m-2 shadow-sm"
                                style="font-size: 0.6rem;">
                                <?= $icon_text ?>
                            </span>

                            <a href="document_detail.php?id=<?= $row['document_id'] ?>" class="text-decoration-none">
                                <div class="doc-thumb rounded-2 border d-flex justify-content-center align-items-center">
                                    <img src="<?= $thumb ?>" alt="Document Cover">
                                </div>
                            </a>
                        </div>

                        <!-- NỘI DUNG -->
                        <div class="card-body p-2 pt-0 d-flex flex-column">
                            <h6 class="card-title fw-semibold text-truncate-2 mb-2"
                                style="font-size: 0.85rem; line-height: 1.3;">
                                <a href="document_detail.php?id=<?= $row['document_id'] ?>"
                                    class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($row['title']) ?>
                                </a>
                            </h6>

                            <div class="mt-auto d-flex justify-content-between align-items-center text-muted"
                                style="font-size: 13px;">

                                <div>
                                    <?php if ($pageCount > 0): ?>
                                        <i class="far fa-file-alt me-1"></i><?= $pageCount ?> trang
                                    <?php endif; ?>
                                </div>

                                <!-- LƯU -->
                                <button
                                    class="btn btn-light border btn-save p-1 px-2" data-id="<?= $row['document_id'] ?>">
                                    <i class="<?= $isSaved ? 'fas' : 'far' ?> fa-bookmark fs-5"></i>
                                </button>
                                <!-- /LƯU -->
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- PHÂN TRANG -->
        <?php if ($totalPages > 1): ?>
            <nav class="mt-5">
                <ul class="pagination justify-content-center custom-pagination">

                    <!-- PREV -->
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link"
                            href="?q=<?= urlencode($q) ?>&page=<?= $page - 1 ?>">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </li>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link"
                                href="?q=<?= urlencode($q) ?>&page=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- NEXT -->
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link"
                            href="?q=<?= urlencode($q) ?>&page=<?= $page + 1 ?>">
                            <i class="fas fa-angle-right"></i>
                        </a>
                    </li>

                </ul>
            </nav>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php include "footer.php"; ?>