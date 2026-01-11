<?php
include "config.php";
include "header.php";

// 1. Lấy ID chủ đề lớn từ URL
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. Lấy thông tin chủ đề lớn
$sql_cate = "SELECT * FROM categories WHERE category_id = $category_id LIMIT 1";
$res_cate = mysqli_query($conn, $sql_cate);
$category = mysqli_fetch_assoc($res_cate);

if (!$category) {
    echo "<div class='container mt-5 pt-5'><div class='alert alert-danger'>Chủ đề không tồn tại!</div></div>";
    include "footer.php";
    exit;
}

// 3. Lấy danh sách tài liệu đã lưu (để hiển thị icon bookmark đúng trạng thái)
$savedDocs = [];
if (isset($_SESSION['username'])) {
    $current_user = $_SESSION['username'];
    $sqlSaved = "SELECT document_id FROM saved_documents WHERE username = '$current_user'";
    $resSaved = mysqli_query($conn, $sqlSaved);
    while ($s = mysqli_fetch_assoc($resSaved)) {
        $savedDocs[] = (int)$s['document_id'];
    }
}

// 4. Lấy tất cả chủ đề nhỏ thuộc chủ đề lớn này
$sql_sub = "SELECT * FROM subcategories WHERE category_id = $category_id AND status = 0 ORDER BY name ASC";
$res_sub = mysqli_query($conn, $sql_sub);
?>

<style>
    /* CSS đồng bộ với Index */
    .doc-card {
        transition: 0.3s;
        border-radius: 12px;
        overflow: hidden;
    }

    .doc-card:hover {
        transform: translateY(-5px);
    }

    .doc-thumb img {
        width: 100%;
        height: 180px;
        border-radius: 8px;
    }

    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        height: 2.6em;
    }

    .sub-section-title {
        position: relative;
        padding-left: 15px;
        font-size: 24px;
    }

    .sub-section-title::before {
        content: "";
        position: absolute;
        left: 0;
        top: 5px;
        bottom: 5px;
        width: 4px;
        background: #0d6efd;
        border-radius: 2px;
    }


    .sub-link-item {
        padding: 8px;
        border-radius: 6px;
        transition: 0.2s;
    }

    .sub-link-item:hover {
        background-color: #f0f7ff;
        color: #0d6efd !important;
        padding-left: 12px;
    }

    .category-info-section {
        background: linear-gradient(to right, #ffffff, #fcfcfc);
    }
</style>

<div class="container" style="margin-top: 110px; margin-bottom: 50px;">
    <div class="mb-5 text-center">
        <h2 class="fw-bold text-uppercase text-primary m-0"><?= htmlspecialchars($category['name']) ?></h2>
        <p class="text-muted small mt-2">Khám phá kho tài liệu chuyên sâu thuộc chủ đề này</p>
        <hr class="mx-auto" style="width: 60px; border-top: 3px solid #0d6efd; opacity: 1;">
    </div>

    <div class="category-info-section bg-white p-4 rounded-3 shadow-sm mb-5 border-start border-primary border-4">
        <div class="category-description mb-4">
            <p class="text-secondary" style="text-align: justify; line-height: 1.6;">
                <?= !empty($category['description']) ? nl2br(htmlspecialchars($category['description'])) : "Chào mừng bạn đến với kho tài liệu chuyên sâu thuộc chủ đề " . htmlspecialchars($category['name']) . ". Tại đây, chúng tôi cung cấp hàng ngàn tài liệu chọn lọc giúp bạn học tập và nghiên cứu hiệu quả." ?>
            </p>
        </div>

        <div class="subcategory-quick-list">
            <h5 class="fw-bold mb-3" style="font-size: 1.1rem;">
                Các chủ đề nổi bật trong <?= htmlspecialchars($category['name']) ?>:
            </h5>
            <div class="row row-cols-2 row-cols-md-4 g-3">
                <?php
                // Query lại danh sách sub để làm menu nhanh
                $res_sub_list = mysqli_query($conn, "SELECT subcategory_id, name FROM subcategories WHERE category_id = $category_id AND status = 0");
                while ($s_item = mysqli_fetch_assoc($res_sub_list)):
                ?>
                    <div class="col">
                        <a href="subcategory_detail.php?id=<?= $s_item['subcategory_id'] ?>" class="text-decoration-none text-dark d-flex align-items-center sub-link-item">
                            <i class="fa fa-chevron-right me-2 text-primary" style="font-size: 10px;"></i>
                            <span class="small fw-semibold"><?= htmlspecialchars($s_item['name']) ?></span>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <?php
    while ($sub = mysqli_fetch_assoc($res_sub)):
        $sub_id = $sub['subcategory_id'];

        // Lấy danh sách tài liệu thuộc chủ đề nhỏ này (Giới hạn hiển thị mẫu)
        $sql_docs = "SELECT * FROM documents WHERE subcategory_id = $sub_id 
                     AND status = 'approved' AND is_visible = 1 
                     ORDER BY document_id DESC LIMIT 10";
        $res_docs = mysqli_query($conn, $sql_docs);

        if (mysqli_num_rows($res_docs) > 0):
    ?>
            <div class="subcategory-group mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                    <h4 class="sub-section-title fw-bold m-0"><?= htmlspecialchars($sub['name']) ?></h4>
                </div>

                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-5 g-4">
                    <?php while ($row = mysqli_fetch_assoc($res_docs)):
                        $isSaved = in_array((int)$row['document_id'], $savedDocs);
                        $thumb = !empty($row['thumbnail']) ? './uploads/thumbnails/' . $row['thumbnail'] : './assets/img/default-document.jpg';
                        $file_ext = strtolower($row['file_type']);
                        $icon_class = ($file_ext === 'pdf') ? 'bg-danger' : 'bg-primary';
                    ?>
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm doc-card">
                                <div class="position-relative p-2">
                                    <span class="badge <?= $icon_class ?> position-absolute top-0 start-0 m-2 shadow-sm" style="font-size: 0.6rem;">
                                        <?= strtoupper($file_ext) ?>
                                    </span>

                                    <a href="document_detail.php?id=<?= $row['document_id'] ?>" class="text-decoration-none">
                                        <div class="doc-thumb rounded-2 border d-flex justify-content-center align-items-center bg-light">
                                            <img src="<?= $thumb ?>" alt="Document Cover">
                                        </div>
                                    </a>
                                </div>

                                <div class="card-body p-2 pt-0 d-flex flex-column">
                                    <h6 class="card-title fw-semibold text-truncate-2 mb-2" style="font-size: 0.85rem; line-height: 1.3;">
                                        <a href="document_detail.php?id=<?= $row['document_id'] ?>" class="text-decoration-none text-dark">
                                            <?= htmlspecialchars($row['title']) ?>
                                        </a>
                                    </h6>

                                    <div class="mt-auto d-flex justify-content-between align-items-center text-muted" style="font-size: 16px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="small"><i class="far fa-eye me-1"></i><?= number_format($row['views']) ?> lượt xem</span>
                                        </div>

                                        <button class="btn btn-light border btn-save p-1 px-2" data-id="<?= $row['document_id'] ?>" style="border-radius: 6px;">
                                            <i class="<?= $isSaved ? 'fas' : 'far' ?> fa-bookmark"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
    <?php
        endif;
    endwhile;
    ?>
</div>

<?php include "footer.php"; ?>