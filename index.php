<?php
include "config.php";
include "header.php";

function countPdfPages($filePath)
{
    if (!file_exists($filePath)) return 0;

    $content = file_get_contents($filePath);
    if ($content === false) return 0;

    preg_match_all("/\/Type\s*\/Page[^s]/", $content, $matches);
    return count($matches[0]);
}

$sql = "SELECT * FROM slideshows WHERE status = 1";
$result = mysqli_query($conn, $sql);

$banners = [];
while ($row = mysqli_fetch_assoc($result)) {
    $banners[] = $row;
}

$document_id = (int)($_GET['id'] ?? 0);

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

// Lấy danh sách tài liệu đã lưu của người dùng
$savedDocs = [];

if (isset($_SESSION['username'])) {
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);

    $rs = mysqli_query($conn, "
        SELECT document_id 
        FROM saved_documents 
        WHERE username = '$username'
    ");

    while ($rowSaved = mysqli_fetch_assoc($rs)) {
        $savedDocs[] = (int)$rowSaved['document_id'];
    }
}

$sqlFeatured = "
    SELECT * FROM documents 
    WHERE status = 'approved' 
      AND is_visible = 1
      AND downloads > 0
    ORDER BY downloads DESC
    LIMIT 10
";

$rsFeatured = mysqli_query($conn, $sqlFeatured);

$featuredDocs = [];
while ($row = mysqli_fetch_assoc($rsFeatured)) {
    $featuredDocs[] = $row;
}

?>

<!-- CAROUSEL -->
<div class="bg" style="margin-top: 80px; background-color: #083f87; padding: 10px;">
    <div id="myCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2000">
        <div class="carousel-inner">

            <?php foreach ($banners as $index => $banner): ?>
                <div class="carousel-item <?= $index == 0 ? 'active' : '' ?>">
                    <img src="uploads/slideshows/<?= $banner['imageurl'] ?>" class="d-block" alt="Slide">
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Nút điều hướng -->
        <button class="carousel-control-prev" type="button" data-bs-target="#myCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>

        <button class="carousel-control-next" type="button" data-bs-target="#myCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
    <span class="d-block text-center text-white text-uppercase mt-2" style="font-size: 20px; font-weight: bold;">
        Cùng nhau chia sẻ tiếp cận nguồn tài liệu học tập chất lượng và miễn phí!
    </span>
</div>

<div class="container mt-4">

    <?php include 'featured_document.php' ?>

    <!-- TÀI LIỆU NỔI BẬT -->
    <?php if (!empty($featuredDocs)): ?>
        <section class="featured-section mt-5">
            <div class="featured-header">
                <h3 class="text-uppercase fw-bold m-0" style="font-size: 1.5rem;">Tài liệu nổi bật</h3>
            </div>

            <div class="slider-container">
                <button class="nav-btn prev" onclick="slideFeatured(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="nav-btn next" onclick="slideFeatured(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>

                <div class="featured-wrapper">
                    <div class="featured-track" id="featuredTrack">
                        <?php foreach ($featuredDocs as $doc):

                            $thumb = !empty($doc['thumbnail']) ? './uploads/thumbnails/' . $doc['thumbnail'] : './assets/img/default-document.jpg';

                            $file_ext = strtolower($doc['file_type']);
                            $icon_class = ($file_ext == 'pdf') ? 'bg-danger' : 'bg-primary';
                            $icon_text = ($file_ext == 'pdf') ? 'PDF' : 'W';

                            $filePath = 'uploads/documents/' . $doc['file_path'];
                            $pageCount = 0;
                            if ($file_ext === 'pdf') {
                                $pageCount = countPdfPages($filePath);
                            }

                            $isSavedFeatured = in_array((int)$doc['document_id'], $savedDocs);
                        ?>
                            <div class="featured-item">
                                <div class="card h-100 border-0 shadow-sm doc-card">

                                    <div class="position-relative p-2">
                                        <span class="badge <?= $icon_class ?> position-absolute top-0 start-0 m-2 shadow-sm" style="font-size: 0.6rem; z-index: 2;">
                                            <?= $icon_text ?>
                                        </span>

                                        <a href="document_detail.php?id=<?= $doc['document_id'] ?>" class="text-decoration-none">
                                            <div class="doc-thumb rounded-2 border d-flex justify-content-center align-items-center">
                                                <img src="<?= $thumb ?>" alt="Document Cover">
                                            </div>
                                        </a>
                                    </div>

                                    <div class="card-body p-2 pt-0 d-flex flex-column">
                                        <h6 class="card-title fw-semibold text-truncate-2 mb-2" style="font-size: 0.85rem; line-height: 1.3; height: 34px;">
                                            <a href="document_detail.php?id=<?= $doc['document_id'] ?>" class="text-decoration-none text-dark">
                                                <?= htmlspecialchars($doc['title']) ?>
                                            </a>
                                        </h6>

                                        <div class="mt-auto d-flex justify-content-between align-items-center text-muted" style="font-size: 13px;">
                                            <div class="d-flex align-items-center gap-2">
                                                <?php if ($pageCount > 0): ?>
                                                    <span>
                                                        <i class="fas fa-download me-1"></i><?= (int)$doc['downloads'] ?> lượt tải xuống
                                                    </span>
                                                <?php else: ?>
                                                    <span><i class="fas fa-download me-1"></i><?= (int)$doc['downloads'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <button class="btn btn-light border btn-save p-1 px-2" data-id="<?= $doc['document_id'] ?>">
                                                <i class="<?= $isSavedFeatured ? 'fas' : 'far' ?> fa-bookmark"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- TÀI LIỆU MỚI NHẤT -->

    <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
        <h3 class="text-uppercase fw-bold m-0" style="font-size: 1.5rem;">Tài liệu mới</h3>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-5 g-4">
        <?php
        $sqlLatest = "SELECT * FROM documents WHERE status = 'approved' AND is_visible = 1 ORDER BY document_id DESC LIMIT 30";
        $resultLatest = mysqli_query($conn, $sqlLatest);

        while ($row = mysqli_fetch_assoc($resultLatest)):
            $isSaved = in_array((int)$row['document_id'], $savedDocs);
            $thumb = !empty($row['thumbnail']) ? './uploads/thumbnails/' . $row['thumbnail'] : './assets/img/default-document.jpg';
            $file_ext = strtolower($row['file_type']);

            $pageCount = 0;
            if ($file_ext === 'pdf') {
                $pageCount = countPdfPages('uploads/documents/' . $row['file_path']);
            }
        ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm doc-card">

                    <!-- Ảnh -->
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

                    <!-- Nội dung -->
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
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($pageCount > 0): ?>
                                    <span class="border p-2 btn-light" style="border-radius: 10px;">
                                        <?= $pageCount ?> trang
                                        <i class="fas fa-ellipsis-h ms-1 p-1 page-more"
                                            style="border-radius: 50%; border: solid 1px; cursor: pointer;"
                                            data-id="<?= $row['document_id'] ?>"
                                            data-views="<?= $row['views'] ?>"
                                            data-downloads="<?= $row['downloads'] ?>"
                                            data-pages="<?= $pageCount ?>"
                                            data-title="<?= htmlspecialchars($row['title']) ?>"
                                            data-thumb="<?= $thumb ?>"
                                            data-desc="<?= htmlspecialchars($row['description'] ?? 'Không có mô tả') ?>"
                                            data-saved="<?= $isSaved ? 1 : 0 ?>">
                                        </i>

                                    </span>
                                <?php endif; ?>
                            </div>
                            <!-- Lưu -->
                            <button
                                class="btn btn-light border btn-save p-1 px-2" data-id="<?= $row['document_id'] ?>">
                                <i class="<?= $isSaved ? 'fas' : 'far' ?> fa-bookmark fs-5"></i>
                            </button>

                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- OVERLAY -->
<div id="docOverlay" class="doc-overlay"></div>

<!-- MODAL -->
<div id="docModal" class="doc-modal">
    <button class="close-modal"><i class="fas fa-times"></i></button>
    <div class="modal-body d-flex gap-3">
        <img id="modalThumb" src="" class="modal-thumb">
        <div>
            <div class="d-flex gap-4 text-muted mt-2 mb-2" style="font-size:14px;">
                <span><b id="modalPageCount">0</b> trang</span>
                <span class="dot">•</span>
                <span><b id="modalViewCount">0</b> lượt xem</span>
                <span class="dot">•</span>
                <span><b id="modalDownloadCount">0</b> lượt tải</span>
            </div>
            <h5 id="modalTitle"></h5>
            <p id="modalDesc" class="text-muted"></p>
            <div class="modal-actions mt-3">
                <a id="modalView" class="btn btn-success btn-sm"><i class="far fa-eye"></i> Xem tài liệu</a>
                <a id="modalDownload" class="btn btn-outline-dark btn-sm"><i class="fas fa-download"></i> Tải xuống</a>
                <button id="modalSave" class="btn btn-outline-dark btn-sm btn-save"><i class="far fa-bookmark"></i> <span class="save-text">Lưu</span></button>
            </div>
        </div>
    </div>
</div>


<?php include("footer.php"); ?>