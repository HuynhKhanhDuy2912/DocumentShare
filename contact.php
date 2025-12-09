<?php include("header.php"); ?>

<?php
include 'config.php'; // file chứa $conn

$sql = "SELECT * FROM slideshows WHERE status = 1";
$result = mysqli_query($conn, $sql);

$banners = [];
while ($row = mysqli_fetch_assoc($result)) {
    $banners[] = $row;
}
?>

<div class="container mt-4">

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
    <h3 class="mb-3">Liên hệ mới nhất</h3>

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