<?php
include "config.php";

// 1. Cập nhật SQL: Lấy trực tiếp c.image và xóa Subquery lấy ảnh từ tài liệu
$sql_featured_sets = "
    SELECT 
        c.category_id, 
        c.name AS category_name,
        c.image AS category_image,
        SUM(d.downloads) AS total_downloads,
        COUNT(d.document_id) AS total_docs
    FROM categories c
    JOIN subcategories sc ON c.category_id = sc.category_id
    JOIN documents d ON sc.subcategory_id = d.subcategory_id
    WHERE d.status = 'approved' AND d.is_visible = 1
    GROUP BY c.category_id
    ORDER BY total_downloads DESC
    LIMIT 4";

$result_sets = mysqli_query($conn, $sql_featured_sets);

function formatK($num)
{
    if ($num >= 1000) return round($num / 1000, 1) . 'k';
    return $num;
}
?>

<style>
    /* Giữ nguyên các Style cũ của bạn */
    .section-title {
        font-weight: 700;
        color: #333;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .set-card {
        transition: 0.3s;
        border: none;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
    }

    .set-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .set-thumb-container {
        height: 200px;
        overflow: hidden;
        position: relative;
    }

    .set-thumb-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .set-title {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        height: 2.8em;
        line-height: 1.4;
        margin-bottom: 15px;
    }

    .set-stats {
        font-size: 0.85rem;
        color: #777;
    }

    .bookmark-icon {
        color: #333;
        font-size: 1.2rem;
        cursor: pointer;
        transition: 0.2s;
    }

    .bookmark-icon:hover {
        color: #0d6efd;
    }

    .btn-xem-them {
        font-size: 0.9rem;
        color: #666;
        text-decoration: none;
        font-weight: 600;
    }

    .btn-xem-them:hover {
        color: #0d6efd;
    }
</style>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="section-title mb-0">BỘ TÀI LIỆU NỔI BẬT</h4>
    </div>

    <div class="row g-4">
        <?php while ($set = mysqli_fetch_assoc($result_sets)):
            // 2. Cập nhật đường dẫn: Lấy từ thư mục uploads/categories/
            $cover = !empty($set['category_image'])
                ? "uploads/categories/" . $set['category_image']
                : "assets/img/default_set.jpg";
        ?>
            <div class="col-md-6 col-lg-3">
                <div class="card set-card shadow-sm h-100">
                    <div class="set-thumb-container">
                        <a href="category_detail.php?id=<?= $set['category_id'] ?>">
                            <img src="<?= $cover ?>" alt="<?= htmlspecialchars($set['category_name']) ?>">
                        </a>
                    </div>

                    <div class="card-body p-2">
                        <a href="category_detail.php?id=<?= $set['category_id'] ?>" class="text-decoration-none">
                            <h5 class="set-title">Bộ tài liệu <?= htmlspecialchars($set['category_name']) ?> cực hay cho sinh viên</h5>
                        </a>

                        <div class="d-flex justify-content-between set-stats">
                            <div class="mb-1"><i class="fas fa-book me-1"></i>
                                <?= number_format($set['total_docs']) ?> tài liệu</div>
                            <div class="ms-auto"><i class="fa-solid fa-download me-1"></i>
                            <?= formatK($set['total_downloads']) ?> lượt tải</div>
                        </div>

                        <div class="card-footer bg-transparent border-0 pt-2">
                            <a href="category_detail.php?id=<?= $set['category_id'] ?>"
                                class="btn btn-outline-primary btn-sm w-100 rounded-pill">
                                Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>