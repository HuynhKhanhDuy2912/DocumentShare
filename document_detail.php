<?php
include "config.php";
include "header.php";

// Đếm số trang PDF
function countPdfPages($filePath)
{
    if (!file_exists($filePath)) return 0;

    $content = file_get_contents($filePath);
    if ($content === false) return 0;

    preg_match_all("/\/Type\s*\/Page[^s]/", $content, $matches);
    return count($matches[0]);
}


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Tài liệu không tồn tại</div></div>";
    include "footer.php";
    exit;
}

$document_id = (int)$_GET['id'];

$sql = "
    SELECT *
    FROM documents
    WHERE document_id = $document_id AND status = 0
    LIMIT 1
";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    echo "<div class='container mt-5'><div class='alert alert-warning'>Không tìm thấy tài liệu</div></div>";
    include "footer.php";
    exit;
}

$doc = mysqli_fetch_assoc($result);

// Kiểm tra tài liệu đã được lưu chưa
$isSaved = false;

if (isset($_SESSION['username'])) {
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);

    $checkSave = mysqli_query($conn, "
        SELECT id FROM saved_documents 
        WHERE username = '$username' AND document_id = $document_id
        LIMIT 1
    ");

    if ($checkSave && mysqli_num_rows($checkSave) > 0) {
        $isSaved = true;
    }
}


// Tăng lượt xem
mysqli_query($conn, "UPDATE documents SET views = views + 1 WHERE document_id = $document_id");

$filePath = "uploads/documents/" . $doc['file_path'];
// Đếm số trang nếu là PDF
$pageCount = 0;
if ($doc['file_type'] === 'pdf') {
    $pageCount = countPdfPages($filePath);
}

$thumbnail = !empty($doc['thumbnail'])
    ? "uploads/thumbnails/" . $doc['thumbnail']
    : "assets/img/default-document.jpg";
?>

<div class="container-fluid mrt">
    <div class="row g-4">

        <!-- TRÁI -->
        <div class="col-lg-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <!-- THỐNG KÊ -->
                    <div class="d-flex align-items-center text-muted small mb-3 gap-3">
                        <?php if ($pageCount > 0): ?>
                            <span>
                                <i class="far fa-file-alt me-1"></i>
                                <?= $pageCount ?> trang
                            </span>
                        <?php endif; ?>
                        <span>
                            <i class="far fa-eye me-1"></i>
                            <?= (int)$doc['views'] + 1 ?> lượt xem
                        </span>
                        <span>
                            <i class="fas fa-download me-1"></i>
                            <?= (int)$doc['downloads'] ?> lượt tải
                        </span>
                    </div>

                    <!-- TITLE -->
                    <h5 class="fw-bold mb-2">
                        <?= htmlspecialchars($doc['title']) ?>
                    </h5>

                    <!-- MÔ TẢ -->
                    <p class="text-muted mb-3">
                        <?= nl2br(htmlspecialchars($doc['description'])) ?>
                    </p>

                    <!-- NÚT CHỨC NĂNG -->
                    <div class="d-flex justify-content-between text-center mb-3">

                        <!-- LƯU -->
                        <button
                            class="btn btn-light border flex-fill mx-1 py-3 btn-save" data-id="<?= $doc['document_id'] ?>">
                            <i class="<?= $isSaved ? 'fas' : 'far' ?> fa-bookmark fs-5 d-block mb-1"></i>

                            <span class="small">
                                <?= $isSaved ? 'Đã lưu' : 'Lưu' ?>
                            </span>
                        </button>
                        <!-- DEBUG -->
                        <!-- <div style="font-size:12px;color:red;">
                            isSaved =
                            <?= var_export($isSaved, true) ?>
                        </div> -->

                        <!-- TẢI -->
                        <a href="download.php?id=<?= $document_id ?>"
                            class="btn btn-light border flex-fill mx-1 py-3">
                            <i class="fas fa-download fs-5 d-block mb-1"></i>
                            <span class="small">Tải</span>
                        </a>

                        <!-- CHIA SẺ -->
                        <button class="btn btn-light border flex-fill mx-1 py-3"
                            data-bs-toggle="modal" data-bs-target="#shareModal">
                            <i class="fas fa-share-alt fs-5 d-block mb-1"></i>
                            <span class="small">Chia sẻ</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>


        <!-- GIỮA -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-body p-2">
                    <?php if ($doc['file_type'] === 'pdf'): ?>
                        <iframe src="<?= $filePath ?>" width="100%" height="850" style="border:none;"></iframe>
                    <?php else: ?>
                        <div class="alert alert-info">
                            File không hỗ trợ xem trực tiếp.
                            <a href="<?= $filePath ?>" download>Tải về</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- PHẢI -->
        <div class="col-lg-2">
            <h6 class="fw-bold mb-3">Tài liệu liên quan</h6>

            <?php
            $docId = (int)$document_id;
            $subId = (int)$doc['subcategory_id'];

            // Lấy tài liệu cùng DANH MỤC LỚN (category)
            $sqlRelated = " SELECT d.document_id, d.title, d.thumbnail, d.file_type FROM documents d
                    INNER JOIN subcategories s ON d.subcategory_id = s.subcategory_id
                    WHERE s.category_id = (
                        SELECT category_id
                        FROM subcategories
                        WHERE subcategory_id = $subId
                        LIMIT 1
                    )
                AND d.document_id != $docId
                AND d.status = 0
                ORDER BY RAND()
                LIMIT 7";

            $related = mysqli_query($conn, $sqlRelated);

            if ($related && mysqli_num_rows($related) > 0):
                while ($r = mysqli_fetch_assoc($related)):
                    $thumbRel = !empty($r['thumbnail'])
                        ? "uploads/thumbnails/" . $r['thumbnail']
                        : "assets/img/default-document.jpg";
            ?>
                    <a href="document_detail.php?id=<?= $r['document_id'] ?>" class="text-decoration-none text-dark">
                        <div class="d-flex mb-3">
                            <img src="<?= $thumbRel ?>" width="80" height="100"
                                class="border me-2" style="object-fit:cover;">
                            <div>
                                <div class="small fw-semibold">
                                    <?= htmlspecialchars($r['title']) ?>
                                </div>
                                <span class="badge bg-secondary">
                                    <?= strtoupper($r['file_type']) ?>
                                </span>
                            </div>
                        </div>
                    </a>
            <?php
                endwhile;
            else:
                echo '<div class="text-muted small">Chưa có tài liệu liên quan</div>';
            endif;
            ?>
        </div>
    </div>
</div>

<div class="modal fade" id="shareModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3">

            <div class="modal-header">
                <h6 class="modal-title fw-bold">Chia sẻ tài liệu</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">

                <div class="d-flex justify-content-center gap-3 mb-3">

                    <!-- Facebook -->
                    <a target="_blank"
                        href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>"
                        class="btn">
                         <img src="assets/img/facebook.png" width="45" height="45">
                    </a>

                    <!-- Zalo -->
                    <a target="_blank"
                        href="https://zalo.me/share?url=<?= urlencode($url) ?>"
                        class="btn d-flex align-items-center justify-content-center">
                        <img src="assets/img/zalo.png" width="40" height="40">
                    </a>

                    <!-- Copy link -->
                    <button class="btn btn-secondary" onclick="copyShareLink()" style="width: 50px; height: 50px; margin-top: 5px;">
                        <i class="fas fa-link"></i>
                    </button>

                </div>

                <input type="text" class="form-control text-center" id="shareLink"
                    value="<?= 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>"
                    readonly>
            </div>
        </div>
    </div>
</div>


<?php include "footer.php"; ?>