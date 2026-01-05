<?php
session_start();
include 'config.php';

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$current_user = $_SESSION['username'];

// 2. XỬ LÝ LOGIC XÓA & ẨN/HIỆN
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("SELECT file_path FROM document_uploads WHERE document_id = ? AND username = ?");
    $stmt->bind_param("is", $id, $current_user);
    $stmt->execute();
    $res = $stmt->get_result();
    $doc = $res->fetch_assoc();

    if ($doc) {
        if (!empty($doc['file_path']) && file_exists($doc['file_path'])) {
            unlink($doc['file_path']); 
        }
        $del = $conn->prepare("DELETE FROM document_uploads WHERE document_id = ?");
        $del->bind_param("i", $id);
        if ($del->execute()) {
            echo "<script>alert('Đã xóa tài liệu thành công!'); window.location.href='manage_documents.php';</script>";
            exit(); 
        }
    }
}

if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $new_status = intval($_GET['toggle_status']); 
    $up = $conn->prepare("UPDATE document_uploads SET status = ? WHERE document_id = ? AND username = ?");
    $up->bind_param("iis", $new_status, $id, $current_user);
    $up->execute();
    header("Location: manage_documents.php");
    exit();
}

// 3. LOGIC PHÂN TRANG & TÌM KIẾM
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$limit = 10; 
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$sql_count = "SELECT COUNT(*) as total FROM document_uploads WHERE username = '$current_user' AND title LIKE '%$search%'";
$count_res = mysqli_query($conn, $sql_count);
$total_docs = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_docs / $limit);

// 4. TRUY VẤN DỮ LIỆU (JOIN lấy tên từ 2 bảng danh mục)
$sql = "SELECT d.*, c.name as cate_name, sc.name as subcate_name 
        FROM document_uploads d 
        LEFT JOIN categories c ON d.category_id = c.category_id 
        LEFT JOIN subcategories sc ON d.subcategory_id = sc.subcategory_id 
        WHERE d.username = '$current_user' AND d.title LIKE '%$search%'
        ORDER BY d.document_id DESC 
        LIMIT $offset, $limit";
$result_docs = mysqli_query($conn, $sql);

function formatSizeUnits($bytes) {
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' bytes';
}
?>

<?php include("header.php"); ?>

<div class="container" style="margin-top: 110px; margin-bottom: 60px;">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-0"><i class="fa fa-folder-open me-2"></i>Quản lý tài liệu</h2>
            <p class="text-muted small">Tối ưu hóa quy trình lưu trữ và chia sẻ kiến thức của bạn.</p>
        </div>
        <a href="upload_document.php" class="btn btn-success px-2 fw-bold shadow-sm">
            <i class="fa fa-cloud-arrow-up"></i> Đăng tài liệu mới
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light rounded">
            <form action="" method="GET" class="row g-2">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Nhập tên tài liệu cần tìm..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-dark w-100 fw-bold">Tìm kiếm</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="ps-3 py-3">ID</th>
                        <th style="width: 25%;">Tên tài liệu</th>
                        <th>Danh mục</th>
                        <th>Danh mục con</th>
                        <th class="text-center">Trạng thái</th>
                        <th>Thống kê</th>
                        <th class="text-end pe-3">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result_docs) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result_docs)): ?>
                            <tr>
                                <td class="ps-3 text-muted"><?= $row['document_id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-2 fs-3 text-danger"><i class="bi bi-file-earmark-pdf-fill"></i></div>
                                        <div class="text-truncate" style="max-width: 200px;">
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($row['title']) ?></div>
                                            <small class="text-muted"><?= strtoupper($row['file_type']) ?> • <?= formatSizeUnits($row['file_size']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                                        <i class="bi bi-tag-fill me-1"></i><?= htmlspecialchars($row['cate_name'] ?? 'Chưa rõ') ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill">
                                        <i class="bi bi-diagram-2 me-1"></i><?= htmlspecialchars($row['subcate_name'] ?? 'Không có') ?>
                                    </span>
                                </td>

                                <td class="text-center">
                                    <?php if ($row['status'] == 1): ?>
                                        <a href="?toggle_status=0&id=<?= $row['document_id'] ?>" class="badge bg-success text-decoration-none shadow-sm" onclick="return confirm('Ẩn tài liệu này?');">
                                            <i class="bi bi-check-circle"></i> Hiển thị
                                        </a>
                                    <?php else: ?>
                                        <a href="?toggle_status=1&id=<?= $row['document_id'] ?>" class="badge bg-warning text-dark text-decoration-none shadow-sm" onclick="return confirm('Công khai tài liệu này?');">
                                            <i class="bi bi-eye-slash"></i> Đang ẩn
                                        </a>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <small class="d-block text-muted text-nowrap"><i class="bi bi-eye"></i> <?= $row['views'] ?> xem</small>
                                    <small class="d-block text-muted text-nowrap"><i class="bi bi-download"></i> <?= $row['shares'] ?> tải</small>
                                </td>

                                <td class="text-end pe-3">
                                    <div class="btn-group shadow-sm">
                                        <a href="<?= $row['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Xem/Tải">
                                            <i class="bi bi-eye"></i> Tải về
                                        </a>
                                        <a href="edit_document.php?id=<?= $row['document_id'] ?>" class="btn btn-sm btn-outline-primary" title="Sửa">
                                            <i class="bi bi-pencil"></i> Sửa
                                        </a>
                                        <a href="?delete_id=<?= $row['document_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa vĩnh viễn?');" title="Xóa">
                                            <i class="bi bi-trash"></i> Xóa
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">Không tìm thấy tài liệu nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($total_pages > 1): ?>
    <nav class="mt-4"><ul class="pagination justify-content-center">
        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Trước</a></li>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a></li>
        <?php endfor; ?>
        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Sau</a></li>
    </ul></nav>
    <?php endif; ?>
</div>

<style>
    .bg-primary-subtle { background-color: #cfe2ff !important; }
    .table-hover tbody tr:hover { background-color: rgba(13, 110, 253, 0.02); transition: 0.2s; }
    .badge { font-weight: 500; padding: 0.5em 0.8em; }
    .btn-group .btn { font-size: 0.85rem; padding: 0.4rem 0.7rem; }
</style>

<?php include("footer.php"); ?>