<?php
$pageName = 'saved';
include "config.php";
include "header.php";

if (!isset($_SESSION['username'])) {
    echo "<script>alert('Vui lòng đăng nhập'); window.location='login.php';</script>";
    exit;
}

$username = $_SESSION['username'];

/* ===============================
   PHÂN TRANG
================================ */
$limit  = 10;
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Tính tổng tài liệu đã lưu
$countSql = "
    SELECT COUNT(*) AS total
    FROM saved_documents s
    INNER JOIN documents d ON s.document_id = d.document_id
    WHERE s.username = '$username'
      AND d.status = 'approved'
      AND d.is_visible = 1
";

$countResult = mysqli_query($conn, $countSql);
$totalRow    = mysqli_fetch_assoc($countResult);
$total       = (int)$totalRow['total'];
$totalPages  = ceil($total / $limit);


// Lấy tài liệu đã lưu
$sql = "
    SELECT d.*
    FROM saved_documents s
    INNER JOIN documents d ON s.document_id = d.document_id
    WHERE s.username = '$username'
      AND d.status = 'approved'
      AND d.is_visible = 1
    ORDER BY s.created_at DESC
    LIMIT $limit OFFSET $offset
";

$result = mysqli_query($conn, $sql);
?>

<div class="container mt-4 mrt page-saved">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Tài liệu đã lưu</h3>
    </div>

    <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-5 g-4">

            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <?php
                $thumb = !empty($row['thumbnail'])
                    ? "uploads/thumbnails/" . $row['thumbnail']
                    : "assets/img/default-document.jpg";

                $file_ext = strtolower($row['file_type']);
                $icon_class = ($file_ext == 'pdf') ? 'bg-danger' : 'bg-primary';
                $icon_text = strtoupper($file_ext);
                ?>

                <div class="col">
                    <div class="card h-100 border-0 shadow-sm doc-card">

                        <div class="position-relative p-2">
                            <span class="badge <?= $icon_class ?> position-absolute top-0 start-0 m-2"
                                style="font-size: 0.65rem;">
                                <?= $icon_text ?>
                            </span>

                            <a href="document_detail.php?id=<?= $row['document_id'] ?>">
                                <div class="doc-thumb border rounded-2 d-flex justify-content-center align-items-center">
                                    <img src="<?= $thumb ?>" alt="">
                                </div>
                            </a>
                        </div>

                        <div class="card-body p-2 pt-0 d-flex flex-column">
                            <h6 class="fw-semibold text-truncate-2 mb-2"
                                style="font-size: 0.85rem;">
                                <a href="document_detail.php?id=<?= $row['document_id'] ?>"
                                    class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($row['title']) ?>
                                </a>
                            </h6>

                            <div class="mt-auto d-flex justify-content-between align-items-center text-muted">
                                <span style="font-size: 13px;">
                                    <i class="far fa-eye me-1"></i><?= $row['views'] ?> lượt xem
                                </span>

                                <button class="btn btn-light border btn-save p-1 px-2"
                                    data-id="<?= $row['document_id'] ?>">
                                    <i class="fas fa-bookmark fs-5"></i>
                                </button>

                            </div>
                        </div>

                    </div>
                </div>
            <?php endwhile; ?>

        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Bạn chưa lưu tài liệu nào.
        </div>
    <?php endif; ?>

    <!-- PHÂN TRANG -->
    <?php if ($totalPages > 1): ?>
        <nav class="mt-5">
            <ul class="pagination justify-content-center custom-pagination">
                <!-- PREV -->
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>">
                        <i class="fas fa-angle-left"></i>
                    </a>
                </li>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- NEXT -->
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>

</div>

<?php include "footer.php"; ?>