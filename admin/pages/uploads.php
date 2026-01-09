<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($conn)) {
    die('Lỗi kết nối CSDL');
}

$page_title = "Danh sách tài liệu người dùng đăng tải";

// =====================================================
// DUYỆT TÀI LIỆU
// =====================================================
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $user = $_SESSION['username'] ?? 'admin';

    mysqli_query($conn, "
        UPDATE documents SET
            status='approved',
            is_visible=1,
            approved_at=NOW(),
            approved_by='$user'
        WHERE document_id=$id
        AND uploader_role='user'
    ");

    echo "<script>alert('Đã duyệt tài liệu!');location.href='?p=uploads'</script>";
    exit;
}

// =====================================================
// TỪ CHỐI TÀI LIỆU
// =====================================================
if (isset($_GET['reject'])) {
    $id = (int)$_GET['reject'];

    mysqli_query($conn, "
        UPDATE documents SET
            status='rejected',
            is_visible=0
        WHERE document_id=$id
          AND uploader_role='user'
    ");

    echo "<script>alert('Đã từ chối tài liệu!');location.href='?p=uploads'</script>";
    exit;
}


// =====================================================
// SEARCH + PHÂN TRANG + LỌC USER
// =====================================================
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$keyword = trim($_GET['keyword'] ?? '');
$where = "WHERE d.uploader_role = 'user'";

if ($keyword !== '') {
    $safe = mysqli_real_escape_string($conn, $keyword);
    $where .= " AND d.title LIKE '%$safe%'";
}

// Tổng số bản ghi
$countSql = "
    SELECT COUNT(*) AS total
    FROM documents d
    $where
";
$countRes = mysqli_query($conn, $countSql);
$totalDocs = mysqli_fetch_assoc($countRes)['total'] ?? 0;
$totalPages = ceil($totalDocs / $limit);

// Lấy dữ liệu
$sql = "
    SELECT d.*, s.name AS subcategory_name
    FROM documents d
    LEFT JOIN subcategories s ON d.subcategory_id = s.subcategory_id
    $where
    ORDER BY d.document_id DESC
    LIMIT $limit OFFSET $offset
";
$result = mysqli_query($conn, $sql);
?>

<div class="card shadow">

    <div class="card-header bg-dark text-white d-flex align-items-center">
        <h4 class="mb-0"><?= $page_title ?></h4>
    </div>

    <div class="card-body">

        <!-- SEARCH -->
        <form method="get" class="row g-2 mb-3">
            <input type="hidden" name="p" value="uploads">

            <div class="col-md-4 ms-auto">
                <input type="text"
                    name="keyword"
                    class="form-control"
                    placeholder="Tìm theo tiêu đề..."
                    value="<?= htmlspecialchars($keyword) ?>">
            </div>

            <div class="col-md-auto">
                <button class="btn btn-primary px-4">
                    <i class="fas fa-search"></i>
                </button>
                <a href="?p=uploads" class="btn btn-secondary">
                    <i class="fas fa-sync"></i>
                </a>
            </div>
        </form>

        <!-- TABLE -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped">
                <thead class="table-light">
                    <tr class="text-center">
                        <th width="80">Ảnh</th>
                        <th>Tiêu đề</th>
                        <th>Mô tả</th>
                        <th>Môn học</th>
                        <th width="150">Người đăng tải</th>
                        <th width="100">Trạng thái</th>
                        <th>Hiển thị</th>
                        <th width="160" class="text-center">Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!$result || mysqli_num_rows($result) == 0): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                Không có tài liệu nào
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="text-center">
                                <td class="text-center">
                                    <?php if (!empty($row['thumbnail']) && file_exists('../uploads/thumbnails/' . $row['thumbnail'])): ?>
                                        <img src="../uploads/thumbnails/<?= $row['thumbnail'] ?>"
                                            style="width:70px;height:70px;object-fit:cover"
                                            class="rounded border">
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Không ảnh</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row['title']) ?></strong>
                                </td>

                                <td><?= htmlspecialchars($row['description']) ?></td>

                                <td><?= htmlspecialchars($row['subcategory_name'] ?? '-') ?></td>

                                <td>
                                    <?= htmlspecialchars($row['username']) ?>
                                </td>

                                <td>
                                    <?php
                                    if ($row['status'] === 'approved') {
                                        echo '<span class="badge bg-success">Đã duyệt</span>';
                                    } elseif ($row['status'] === 'rejected') {
                                        echo '<span class="badge bg-danger">Từ chối</span>';
                                    } else {
                                        echo '<span class="badge bg-warning">Chờ duyệt</span>';
                                    }
                                    ?>
                                </td>

                                <td>
                                    <?= $row['is_visible'] ? '<span class="badge bg-success">Hiển thị</span>' : '<span class="badge bg-secondary">Đã ẩn</span>' ?>
                                </td>

                                <td class="text-center">
                                    <a href="#"
                                        class="btn btn-sm btn-info btn-preview"
                                        data-id="<?= $row['document_id'] ?>">
                                        <i class="fas fa-eye"></i>  
                                    </a>

                                    <?php if ($row['status'] !== 'rejected'): ?>
                                        <a href="?p=documents&action=edit&id=<?= $row['document_id'] ?>&redirect=uploads"
                                            class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($row['status'] === 'pending'): ?>

                                        <a href="?p=uploads&approve=<?= $row['document_id'] ?>"
                                            class="btn btn-sm btn-success"
                                            onclick="return confirm('Bạn có muốn duyệt tài liệu này?')">
                                            <i class="fas fa-check"></i>
                                        </a>

                                        <a href="?p=uploads&reject=<?= $row['document_id'] ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Bạn muốn từ chối tài liệu này?')">
                                            <i class="fas fa-times"></i>
                                        </a>

                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">

                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link"
                            href="?p=uploads&page=<?= $page - 1 ?>&keyword=<?= urlencode($keyword) ?>">
                            &laquo;
                        </a>
                    </li>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    ?>

                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link"
                                href="?p=uploads&page=<?= $i ?>&keyword=<?= urlencode($keyword) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link"
                            href="?p=uploads&page=<?= $page + 1 ?>&keyword=<?= urlencode($keyword) ?>">
                            &raquo;
                        </a>
                    </li>

                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL PREVIEW -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> Xem trước tài liệu
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body" id="previewContent">
                <div class="text-center text-muted">
                    <i class="fas fa-spinner fa-spin"></i> Đang tải...
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.btn-preview').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.id;

            document.getElementById('previewContent').innerHTML =
                '<div class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>';

            $('#previewModal').modal('show');

            fetch('pages/preview_admin_doc.php?id=' + id)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('previewContent').innerHTML = html;
                });
        });
    });
</script>