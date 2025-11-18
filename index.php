<?php include("header.php"); ?>

<div class="container mt-4">

    <!-- CAROUSEL SLIDE -->
    <div id="carouselDocs" class="carousel slide mb-4" data-ride="carousel">
        <ol class="carousel-indicators">
            <li data-target="#carouselDocs" data-slide-to="0" class="active"></li>
            <li data-target="#carouselDocs" data-slide-to="1"></li>
            <li data-target="#carouselDocs" data-slide-to="2"></li>
        </ol>

        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="assets/img/bg.jpg" class="d-block w-100" alt="Slide 2">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Bài giảng lập trình PHP</h5>
                    <p>Học từ cơ bản đến nâng cao.</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="assets/img/bg1.jpg" class="d-block w-100" alt="Slide 3">
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

    <!-- DANH SÁCH TÀI LIỆU MỚI NHẤT -->
    <h3 class="mb-3">Tài liệu mới nhất</h3>
    <div class="row">

        <!-- Tài liệu 1 -->
        <!-- <div class="col-md-3 mb-3">
            <div class="card h-100">
                <img src="assets/img/bg.jpg" class="card-img-top" alt="Doc 1">
                <div class="card-body">
                    <h5 class="card-title">Toán Cao Cấp</h5>
                    <p class="card-text">Tài liệu tổng hợp các chương Toán Cao Cấp dành cho sinh viên.</p>
                    <a href="#" class="btn btn-primary btn-sm">Xem chi tiết</a>
                </div>
            </div>
        </div> -->
        <?php
        // Lấy dữ liệu từ bảng document
        $sql = "SELECT document_id, title, description, file_path FROM documents";
        $result = mysqli_query($conn, $sql);
        ?>

        <div class="row">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>

                <div class="col-md-3 mb-3">
                    <div class="card h-100">

                        <!-- Ảnh tài liệu -->
                        <?php
                        // Nếu file_path NULL hoặc không phải ảnh → dùng ảnh mặc định
                        $image = (!empty($row['file_path']) && preg_match('/\.(jpg|jpeg|png|gif)$/i', $row['file_path']))
                            ? $row['file_path']
                            : "assets/img/bg.jpg";
                        ?>

                        <img src="<?= $image ?>" class="card-img-top" alt="Document Image">

                        <div class="card-body">
                            <!-- Tiêu đề -->
                            <h5 class="card-title">
                                <?= htmlspecialchars($row['title']) ?>
                            </h5>

                            <!-- Mô tả -->
                            <p class="card-text">
                                <?= htmlspecialchars($row['description']) ?>
                            </p>

                            <!-- Link chi tiết -->
                            <a href="document_detail.php?id=<?= $row['document_id'] ?>" class="btn btn-primary btn-sm">
                                Xem chi tiết
                            </a>
                        </div>

                    </div>
                </div>

            <?php endwhile; ?>
        </div>



    </div>

</div>

<?php include("footer.php"); ?>