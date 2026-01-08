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

$sql = "SELECT d.*, 
               sc.name AS sub_name, 
               c.name AS cate_name, 
               c.category_id AS parent_cate_id
        FROM documents d
        LEFT JOIN subcategories sc ON d.subcategory_id = sc.subcategory_id
        LEFT JOIN categories c ON sc.category_id = c.category_id
        WHERE d.document_id = $document_id 
        AND d.status = 'approved' 
        AND d.is_visible = 1 
        LIMIT 1";
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

//Lấy danh sách document
$savedDocs = [];

if (isset($_SESSION['username'])) {
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);

    $savedQuery = mysqli_query($conn, "
        SELECT document_id FROM saved_documents
        WHERE username = '$username'
    ");

    while ($row = mysqli_fetch_assoc($savedQuery)) {
        $savedDocs[] = $row['document_id'];
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
    <div class="row g-4" style="padding-left: 0px !important;">

        <!-- TRÁI -->
        <div class="col-lg-3" style="padding-left: 0px !important;">
            <div class="card">
                <div class="card-body">
                    <div>
                        <ol class="breadcrumb mb-0 small">
                            <li><a href="index.php" class="text-decoration-none">Trang chủ</a></li>
                            <li>
                                <i class="fa fa-angle-double-right"></i> <?= htmlspecialchars($doc['cate_name'] ?? 'Chủ đề') ?>
                            </li>
                            <li>
                                <i class="fa fa-angle-double-right"></i>
                                <a href="subcategory_detail.php?id=<?= (int)$doc['subcategory_id'] ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($doc['sub_name'] ?? 'Môn học') ?>
                                </a>
                            </li>
                        </ol>
                    </div>

                    <!-- THỐNG KÊ -->
                    <div class="d-flex align-items-center text-muted small mb-3 gap-3">
                        <?php if ($pageCount > 0): ?>
                            <span>
                                <i class="far fa-file-alt me-1"></i>
                                <?= $pageCount ?> trang
                            </span>
                        <?php endif; ?>
                        <span class="dot">•</span>
                        <span>
                            <i class="far fa-eye me-1"></i>
                            <?= (int)$doc['views'] + 1 ?> lượt xem
                        </span>
                        <span class="dot">•</span>
                        <span>
                            <i class="fas fa-download me-1"></i>
                            <?= (int)$doc['downloads'] ?> lượt tải
                        </span>
                    </div>

                    <!-- TITLE -->
                    <h5 class="fw-bold mb-2" style="font-size: 1.5rem;">
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
        <div class="col-lg-7" style="padding-left: 0px !important;">
            <div class="card shadow-sm border-0">
                <div class="card-body p-2">
                    <?php
                    $isLogin = isset($_SESSION['username']);
                    ?>

                    <?php if ($doc['file_type'] === 'pdf'): ?>

                        <?php if ($isLogin): ?>
                            <iframe src="<?= $filePath ?>" width="100%" height="850" style="border:none;"></iframe>
                        <?php else: ?>
                            <iframe src="<?= $filePath ?>#toolbar=0&navpanes=0&scrollbar=0" width="100%" height="850" style="border:none;"></iframe>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="alert alert-info">
                            File không hỗ trợ xem trực tiếp.
                            <?php if ($isLogin): ?>
                                <a href="<?= $filePath ?>" download>Tải về</a>
                            <?php else: ?>
                                <span class="text-danger">Vui lòng đăng nhập để tải file</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- PHẢI -->
        <div class="col-lg-2">
            <h4 class="fw-bold mb-3">Tài liệu liên quan</h4>

            <?php
            $docId = (int)$document_id;
            $subId = (int)$doc['subcategory_id'];

            // Lấy tài liệu cùng chủ đề
            $sqlRelated = " SELECT d.document_id, d.title, d.thumbnail, d.file_type FROM documents d
                    INNER JOIN subcategories s ON d.subcategory_id = s.subcategory_id
                    WHERE s.category_id = (
                        SELECT category_id
                        FROM subcategories
                        WHERE subcategory_id = $subId
                        LIMIT 1
                    )
                AND d.document_id != $docId
                AND d.status = 'approved' AND d.is_visible = 1
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
                                <div class="d-flex mt-1">
                                    <div class="bg-danger text-white small" style="padding: 4px 4px; border-radius: 4px; font-size: 10px; font-weight: bold; margin-right: 5px;">
                                        <?= strtoupper($r['file_type']) ?>
                                    </div>
                                    <?php if ($pageCount > 0): ?>
                                        <span class="text-muted small">
                                            <?= $pageCount ?> trang
                                        </span>
                                    <?php endif; ?>

                                    <?php
                                        $isRelatedSaved = in_array($r['document_id'], $savedDocs);
                                    ?>

                                    <button
                                        class="btn btn-small btn-light btn-save ms-auto" data-id="<?= $r['document_id'] ?>" style="margin-right: 20px;">
                                        <i class="<?= $isRelatedSaved ? 'fas' : 'far' ?> fa-bookmark d-block mb-1" style="font-size: 17px;"></i>
                                    </button>
                                </div>
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

            <div class="modal-body text-center" style="display: block;">
                <div class="d-flex justify-content-center gap-3 mb-3">
                    <!-- Facebook -->
                    <a target="_blank"
                        href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>"
                        class="btn" title="Facebook">
                        <img src="assets/img/facebook.png" width="45" height="45">
                    </a>

                    <!-- Zalo -->
                    <a target="_blank"
                        href="https://zalo.me/share?url=<?= urlencode($url) ?>"
                        class="btn d-flex align-items-center justify-content-center" title="Zalo">
                        <img src="assets/img/zalo.png" width="40" height="40">
                    </a>

                    <!-- Copy link -->
                    <button class="btn btn-secondary" onclick="copyShareLink()"
                        style="width: 50px; height: 50px; margin-top: 5px;" title="Copy Link">
                        <i class="fas fa-link"></i>
                    </button>
                </div>
                <input type="text" class="form-control text-center mx-auto"
                    id="shareLink" value="<?= 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>"
                    readonly style="max-width: 440px;">
            </div>
        </div>
    </div>
</div>


<?php include "footer.php"; ?>