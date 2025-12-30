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

$document_id = (int)$_GET['id'];

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

?>

<div class="container mt-4 mrt">

    <!-- CAROUSEL -->
    <div id="myCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">

            <?php foreach ($banners as $index => $banner): ?>
                <div class="carousel-item <?= $index == 0 ? 'active' : '' ?>">
                    <img src="uploads/slideshows/<?= $banner['imageurl'] ?>" class="d-block w-100" alt="Slide">
                    <div class="carousel-caption d-none d-md-block">
                        <h5><?= $banner['title'] ?></h5>
                        <p><?= $banner['description'] ?></p>
                    </div>
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


    <!-- TÀI LIỆU MỚI NHẤT -->

    <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
        <h3 class="text-uppercase fw-bold m-0" style="font-size: 1.5rem;">Tài liệu mới</h3>
        <a href="#" class="text-decoration-none text-muted">Xem thêm</a>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-5 g-4">
        <?php
        $sql = "SELECT * FROM documents WHERE status = 0 ORDER BY document_id DESC LIMIT 20";
        $result = mysqli_query($conn, $sql);

        while ($row = mysqli_fetch_assoc($result)):
            $isSaved = in_array((int)$row['document_id'], $savedDocs);

            $thumb = !empty($row['thumbnail']) ? './uploads/thumbnails/' . $row['thumbnail'] : './assets/img/default-document.jpg';

            $filePath = 'uploads/documents/' . $row['file_path'];
            $pageCount = 0;
            if ($row['file_type'] === 'pdf') {
                $pageCount = countPdfPages($filePath);
            }

            // Xác định icon dựa trên loại file
            $file_ext = strtolower($row['file_type']);
            $icon_class = ($file_ext == 'pdf') ? 'bg-danger' : 'bg-primary';
            $icon_text = ($file_ext == 'pdf') ? 'PDF' : 'W';
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
                                    <span>
                                        <i class="far fa-file-alt me-1"></i>
                                        <?= $pageCount ?> trang
                                    </span>
                                <?php endif; ?>
                            </div>
                            <!-- LƯU -->
                            <button
                                class="btn btn-light border btn-save" data-id="<?= $row['document_id'] ?>">
                                <i class="<?= $isSaved ? 'fas' : 'far' ?> fa-bookmark fs-5 d-block mb-1"></i>
                            </button>
                            <!-- LƯU -->
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include("footer.php"); ?>