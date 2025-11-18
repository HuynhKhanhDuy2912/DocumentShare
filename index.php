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
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <img src="assets/img/bg.jpg" class="card-img-top" alt="Doc 1">
                <div class="card-body">
                    <h5 class="card-title">Toán Cao Cấp</h5>
                    <p class="card-text">Tài liệu tổng hợp các chương Toán Cao Cấp dành cho sinh viên.</p>
                    <a href="#" class="btn btn-primary btn-sm">Xem chi tiết</a>
                </div>
            </div>
        </div>

        <!-- Tài liệu 2 -->
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <img src="assets/img/bg.jpg" class="card-img-top card-img-small" alt="Doc 2">
                <div class="card-body">
                    <h5 class="card-title">Lập trình C++</h5>
                    <p class="card-text">Bài giảng C++ cơ bản và nâng cao, kèm bài tập thực hành.</p>
                    <a href="#" class="btn btn-primary btn-sm">Xem chi tiết</a>
                </div>
            </div>
        </div>

        <!-- Tài liệu 3 -->
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <img src="assets/img/bg.jpg" class="card-img-top card-img-small" alt="Doc 3">
                <div class="card-body">
                    <h5 class="card-title">Hóa học đại cương</h5>
                    <p class="card-text">Tài liệu đầy đủ các chương Hóa học đại cương cho sinh viên mới nhập học.</p>
                    <a href="#" class="btn btn-primary btn-sm">Xem chi tiết</a>
                </div>
            </div>
        </div>

    </div>

</div>

<?php include("footer.php"); ?>
