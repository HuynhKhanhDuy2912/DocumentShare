<?php
// Lấy số lượng tổng quát cho thanh ngang phía trên
$totalCats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM categories"))['total'];
$totalSubs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM subcategories"))['total'];
$totalDocs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM documents"))['total'];
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role != '1'"))['total'];

// Lấy dữ liệu cho biểu đồ: Thống kê số môn học theo từng chủ đề
$sqlChart = "SELECT c.name as cat_name, COUNT(sc.subcategory_id) as sub_count 
             FROM categories c 
             LEFT JOIN subcategories sc ON c.category_id = sc.category_id 
             GROUP BY c.category_id";
$resChart = mysqli_query($conn, $sqlChart);

$catNames = [];
$subCounts = [];

while ($row = mysqli_fetch_assoc($resChart)) {
    $catNames[] = $row['cat_name'];
    $subCounts[] = $row['sub_count'];
}

// Lấy danh sách tài liệu được tải xuống nhiều nhất
$sqlTopDocs = "SELECT d.*, sc.name AS sub_name 
               FROM documents d 
               LEFT JOIN subcategories sc ON d.subcategory_id = sc.subcategory_id 
               ORDER BY d.downloads DESC 
               LIMIT 5";
$resTopDocs = mysqli_query($conn, $sqlTopDocs);

// Lấy danh sách người dùng mới
$sqlNewUsers = "SELECT * FROM users WHERE role != '1' ORDER BY created_at DESC LIMIT 5";
$resNewUsers = mysqli_query($conn, $sqlNewUsers);
?>

<!-- Số lượng tổng quát -->
<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 border-start border-primary border-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="small text-muted text-uppercase fw-bold">Chủ đề</div>
                        <h3 class="mb-0 fw-bold text-primary"><?= $totalCats ?></h3>
                    </div>
                    <i class="fas fa-th-large fa-2x"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 border-start border-success border-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="small text-muted text-uppercase fw-bold">Môn học</div>
                        <h3 class="mb-0 fw-bold text-success"><?= $totalSubs ?></h3>
                    </div>
                    <i class="fas fa-book fa-2x"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 border-start border-info border-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="small text-muted text-uppercase fw-bold">Tài liệu</div>
                        <h3 class="mb-0 fw-bold text-info"><?= $totalDocs ?></h3>
                    </div>
                    <i class="fas fa-file-alt fa-2x"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 border-start border-warning border-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="small text-muted text-uppercase fw-bold">Người dùng</div>
                        <h3 class="mb-0 fw-bold text-warning"><?= $totalUsers ?></h3>
                    </div>
                    <i class="fas fa-users fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ và người dùng mới -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-pie me-2"></i>Số lượng môn học theo chủ đề</h5>
                </div>
                <div class="d-flex">
                    <div class="px-5 py-2">
                        <div style="width: 276px; height: 276px; position: relative;">
                            <canvas id="categoryPieChart"></canvas>
                        </div>
                    </div>

                    <div class="px-3 py-3">
                        <ul class="list-unstyled mb-0" id="custom-legend">
                            <?php
                            $colors = ['#0d00ff', '#0ef544', '#108799', '#ffea07', '#ff0019', '#6607ff', '#f26e10'];
                            foreach ($catNames as $index => $name):
                                $color = $colors[$index] ?? '#ccc';
                            ?>
                                <li class="d-flex align-items-center mb-2">
                                    <span style="display: inline-block; width: 13px; height: 13px; background-color: <?= $color ?>; border-radius: 2px; margin-right: 10px; flex-shrink: 0;"></span>
                                    <span class="text-dark small fw-bold text-truncate" style="font-size: 15px;"><?= htmlspecialchars($name) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-users me-2 text-success"></i>Người dùng mới</h5>
                    <a href="?p=users" class="small text-decoration-none">Xem tất cả</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="small text-uppercase text-center">
                                    <th width="50">#</th>
                                    <th>Tên người dùng</th>
                                    <th>Ngày tham gia</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stt_u = 1;
                                if (mysqli_num_rows($resNewUsers) > 0):
                                    while ($user = mysqli_fetch_assoc($resNewUsers)):
                                ?>
                                        <tr class="text-center">
                                            <td class="text-muted small"><?= $stt_u++ ?></td>
                                            <td class="text-start">
                                                <div class="fw-bold ps-3"><?= htmlspecialchars($user['username']) ?></div>
                                            </td>
                                            <td class=""><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                            <td>
                                                <?php if ($user['status'] == 0): ?>
                                                    <span class="badge bg-success-light text-success border border-success px-2 py-1" style="font-size: 10px;">
                                                        <i class="fas fa-check-circle me-1"></i> Hoạt động
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-light text-danger border border-danger px-2 py-1" style="font-size: 10px;">
                                                        <i class="fas fa-ban me-1"></i> Bị chặn
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php
                                    endwhile;
                                else:
                                    ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Chưa có người dùng mới</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách tài liệu được tải xuống nhiều nhất -->
    <div class="col-lg-12">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-fire me-2 text-danger"></i>Tài liệu được tải nhiều nhất</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th class="ps-3" width="50">#</th>
                                <th width="80">Ảnh</th>
                                <th>Tên tài liệu</th>
                                <th>Môn học</th>
                                <th>Lượt xem</th>
                                <th>Lượt tải</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stt = 1;
                            while ($top = mysqli_fetch_assoc($resTopDocs)):
                                // Xử lý đường dẫn ảnh thumbnail
                                $thumbnail = !empty($top['thumbnail'])
                                    ? "../uploads/thumbnails/" . $top['thumbnail']
                                    : "../assets/img/default-document.jpg";
                            ?>
                                <tr class="text-center">
                                    <td class="ps-3 text-muted"><?= $stt++ ?></td>

                                    <td>
                                        <img src="<?= $thumbnail ?>" width="50" height="65  ">
                                    </td>

                                    <td>
                                        <div class="fw-bold text-wrap" style="max-width: 250px;">
                                            <?= htmlspecialchars($top['title']) ?>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="text-dark fw-normal">
                                            <?= htmlspecialchars($top['sub_name'] ?? 'N/A') ?>
                                        </span>
                                    </td>

                                    <td>
                                        <span class="rounded-pill"><?= number_format($top['views']) ?></span>
                                    </td>

                                    <td>
                                        <span class="rounded-pill"><?= number_format($top['downloads']) ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Truyền dữ liệu từ PHP sang biến JavaScript toàn cục
    const catNamesData = <?php echo json_encode($catNames); ?>;
    const subCountsData = <?php echo json_encode($subCounts); ?>;
</script>