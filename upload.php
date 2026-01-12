<?php
include 'config.php';

/* ================== 1. KIỂM TRA ĐĂNG NHẬP ================== */
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$current_user = $_SESSION['username'];

/* ================== 2. XÓA TÀI LIỆU ================== */
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);

    $stmt = $conn->prepare("SELECT file_path FROM documents WHERE document_id = ? AND username = ?");
    $stmt->bind_param("is", $id, $current_user);
    $stmt->execute();
    $res = $stmt->get_result();
    $doc = $res->fetch_assoc();

    if ($doc) {
        // Đường dẫn đầy đủ đến file
        $full_path = "uploads/documents/" . $doc['file_path'];
        if (!empty($doc['file_path']) && file_exists($full_path)) {
            @unlink($full_path);
        }

        $del = $conn->prepare("DELETE FROM documents WHERE document_id = ? AND username = ?");
        $del->bind_param("is", $id, $current_user);
        $del->execute();

        echo "<script>alert('Đã xóa tài liệu thành công!'); window.location.href='upload.php';</script>";
        exit();
    }
}

/* ================== 3. PHÂN TRANG + TÌM KIẾM ================== */
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$limit = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$sql_count = "SELECT COUNT(*) AS total FROM documents WHERE username = ? AND title LIKE ?";
$stmt_count = $conn->prepare($sql_count);
$like = "%$search%";
$stmt_count->bind_param("ss", $current_user, $like);
$stmt_count->execute();
$total_docs = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_docs / $limit);

/* ================== 4. LẤY DANH SÁCH TÀI LIỆU ================== */
$sql = "
SELECT 
    d.*,
    sc.name AS subcate_name
    FROM documents d
    LEFT JOIN subcategories sc ON d.subcategory_id = sc.subcategory_id
    WHERE d.username = ?
    AND d.title LIKE ?
    ORDER BY d.document_id DESC
    LIMIT ?, ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $current_user, $like, $offset, $limit);
$stmt->execute();
$result_docs = $stmt->get_result();

function formatSizeUnits($bytes)
{
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' bytes';
}
?>

<?php include("header.php"); ?>

<div class="container" style="margin-top: 110px; margin-bottom: 60px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-0"><i class="fa fa-folder-open me-2"></i>Tài liệu của tôi</h2>
            <p class="text-muted small">Quản lý và theo dõi trạng thái các tài liệu bạn đã chia sẻ.</p>
        </div>
        <a href="upload_document.php" class="btn btn-success fw-bold shadow-sm">
            <i class="fa fa-cloud-arrow-up"></i> Đăng tài liệu mới
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light rounded">
            <form method="GET" class="row g-2">
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm tài liệu của bạn..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-dark w-100 fw-bold">Tìm kiếm</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-primary text-white">
                    <tr class="text-center">
                        <th>Ảnh</th>
                        <th>Tên tài liệu</th>
                        <th>Môn học</th>
                        <th>Kiểm duyệt</th>
                        <th>Hiển thị</th>
                        <th width="140">Thống kê</th>
                        <th width="180">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_docs->num_rows > 0): ?>
                        <?php while ($row = $result_docs->fetch_assoc()): ?>
                            <tr class="text-center">
                                <td>
                                    <?php
                                    // Kiểm tra nếu có ảnh trong DB, nếu không dùng ảnh mặc định
                                    $thumb_path = !empty($row['thumbnail']) ? "uploads/thumbnails/" . $row['thumbnail'] : "assets/img/default-document.jpg";
                                    ?>
                                    <img src="<?= $thumb_path ?>" width="50" height="70" style="object-fit: cover;" class="border rounded shadow-sm">
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row['title']) ?></strong><br>
                                    <small class="text-muted"><?= strtoupper($row['file_type']) ?> • <?= formatSizeUnits($row['file_size']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($row['subcate_name'] ?? 'Chưa phân loại') ?></td>

                                <td class="text-center">
                                    <?php if ($row['status'] == 'approved'): ?>
                                        <span class="badge bg-success">Đã duyệt</span>
                                    <?php elseif ($row['status'] == 'rejected'): ?>
                                        <span class="badge bg-danger">Từ chối</span>
                                        <button type="button"
                                            class="btn btn-link btn-sm p-0 ms-1 text-danger btn-view-reason"
                                            data-reason="<?= htmlspecialchars($row['rejection_reason'] ?? 'Không có lý do cụ thể.') ?>"
                                            title="Xem lý do từ chối">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="badge bg-info">Chờ duyệt</span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <?php if ($row['is_visible'] == 1): ?>
                                        <span class="text-success" title="Tài liệu đang hiển thị trên hệ thống" style="font-size: 14px;">
                                            <i class="fa fa-eye"></i> Hiển thị
                                        </span>
                                    <?php else: ?>
                                        <span class="text-secondary" title="Tài liệu này đang bị ẩn" style="font-size: 14px;">
                                            <i class="fa fa-eye-slash"></i> Đang ẩn
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td style="font-size: 16px;">
                                    <small style="margin-right: 10px;"><i class="far fa-eye"></i> <?= $row['views'] ?> xem</small>
                                    <small><i class="fas fa-download"></i> <?= $row['downloads'] ?> tải</small>
                                </td>

                                <td>
                                    <a href="preview_my_doc.php?id=<?= $row['document_id'] ?>" class="btn btn-sm btn-outline-success" title="Xem trước"><i class="fa fa-eye"></i></a>
                                    <a href="download_my_doc.php?id=<?= $row['document_id'] ?>" class="btn btn-sm btn-outline-secondary" title="Tải về"><i class="fa fa-download"></i></a>
                                    <?php if ($row['status'] != 'approved'): ?>
                                        <a href="edit_document.php?id=<?= $row['document_id'] ?>" class="btn btn-sm btn-outline-primary" title="Sửa"><i class="fa fa-edit"></i></a>
                                    <?php endif; ?>
                                    <a href="?delete_id=<?= $row['document_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa tài liệu này?')" title="Xóa"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">Bạn chưa có tài liệu nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php
    $queryString = '';
    if (!empty($search)) {
        $queryString = '&search=' . urlencode($search);
    }
    ?>

    <?php if ($total_pages > 1): ?>
        <nav class="mt-5">
            <ul class="pagination justify-content-center custom-pagination">

                <!-- PREV -->
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?><?= $queryString ?>">
                        <i class="fas fa-angle-left"></i>
                    </a>
                </li>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?><?= $queryString ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- NEXT -->
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?><?= $queryString ?>">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<!-- MODAL -->
<div class="modal fade" id="reasonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-circle me-2"></i>Lý do từ chối tài liệu
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 bg-light rounded border">
                    <p id="displayReason" class="mb-0 text-dark" style="line-height: 1.6;"></p>
                </div>
                <div class="mt-3 text-muted small">
                    <i class="fas fa-lightbulb me-1"></i>
                    <strong>Gợi ý:</strong> Bạn có thể chỉnh sửa lại tài liệu theo lý do trên và gửi lại yêu cầu duyệt.
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reasonButtons = document.querySelectorAll('.btn-view-reason');
    const reasonModal = new bootstrap.Modal(document.getElementById('reasonModal'));
    const displayReason = document.getElementById('displayReason');

    reasonButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Lấy lý do từ thuộc tính data-reason
            const reason = this.getAttribute('data-reason');
            
            // Gán vào modal và hiển thị
            displayReason.innerText = reason;
            reasonModal.show();
        });
    });
});
</script>

<?php include("footer.php"); ?>