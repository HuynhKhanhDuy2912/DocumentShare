<?php
include "config.php";
include "header.php";

// Chưa đăng nhập thì đá về login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Vui lòng đăng nhập'); window.location='login.php';</script>";
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Lấy tài liệu đã lưu
$sql = "
    SELECT d.*
    FROM saved_documents s
    INNER JOIN documents d ON s.document_id = d.document_id
    WHERE s.user_id = $user_id AND d.status = 0
    ORDER BY s.created_at DESC
";

$result = mysqli_query($conn, $sql);
?>

<div class="container mt-4 mrt">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">📌 Tài liệu đã lưu</h3>
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
                                <div class="doc-thumb border rounded-2">
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

                            <div class="mt-auto d-flex justify-content-between align-items-center text-muted"
                                style="font-size: 0.75rem;">
                                <span>
                                    <i class="far fa-eye me-1"></i><?= $row['views'] ?>
                                </span>

                                <a href="unsave_document.php?id=<?= $row['document_id'] ?>"
                                    class="text-danger"
                                    onclick="return confirm('Bỏ lưu tài liệu này?')">
                                    <i class="fas fa-trash"></i>
                                </a>
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

</div>

<?php include "footer.php"; ?>