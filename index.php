<?php include("header.php"); ?>

<div class="container mt-4">

    <!-- CAROUSEL -->
    <div id="carouselDocs" class="carousel slide mb-4" data-ride="carousel">
        <ol class="carousel-indicators">
            <li data-target="#carouselDocs" data-slide-to="0" class="active"></li>
            <li data-target="#carouselDocs" data-slide-to="1"></li>
        </ol>

        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="assets/img/bg.jpg" class="d-block w-100" alt="Slide 1">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Bài giảng lập trình PHP</h5>
                    <p>Học từ cơ bản đến nâng cao.</p>
                </div>
            </div>

            <div class="carousel-item">
                <img src="assets/img/bg1.jpg" class="d-block w-100" alt="Slide 2">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Thư viện tài liệu miễn phí</h5>
                    <p>Tìm kiếm tài liệu nhanh chóng, dễ dàng.</p>
                </div>
            </div>
        </div>

        <a class="carousel-control-prev" href="#carouselDocs" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </a>
        <a class="carousel-control-next" href="#carouselDocs" role="button" data-slide="next">
            <span class="carousel-control-next-icon"></span>
        </a>
    </div>

    <!-- TÀI LIỆU MỚI NHẤT -->
    <h3 class="mb-3">Tài liệu mới nhất</h3>

    <div class="row">
        <?php
        $sql = "SELECT document_id, title, description, file_path 
                FROM documents ORDER BY document_id DESC LIMIT 8";
        $result = mysqli_query($conn, $sql);

        while ($row = mysqli_fetch_assoc($result)):
            $image = (!empty($row['file_path']) && preg_match('/\.(jpg|jpeg|png|gif)$/i', $row['file_path']))
                ? $row['file_path']
                : "assets/img/bg.jpg";
        ?>

            <div class="col-md-3 mb-3">
                <div class="card h-100 shadow-sm">

                    <img src="<?= $image ?>" class="card-img-top" alt="Document">

                    <div class="card-body">
                        <h5 class="card-title">
                            <?= htmlspecialchars($row['title']) ?>
                        </h5>

                        <p class="card-text text-truncate">
                            <?= htmlspecialchars($row['description']) ?>
                        </p>

                        <a href="document_detail.php?id=<?= $row['document_id'] ?>"
                            class="btn btn-primary btn-sm">
                            Xem chi tiết
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include("footer.php"); ?>