<?php
// 1. KHỞI TẠO SESSION & CONFIG
session_start();
include 'config.php';

// Kiểm tra user
if (isset($_SESSION['username'])) {
    $current_user = $_SESSION['username'];}
// } else {
//     $current_user = 'Admin'; // User mặc định nếu chưa login
// }

// 2. XỬ LÝ LOGIC

// --- Xử lý xóa ---
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    
    // Kiểm tra quyền sở hữu trước khi xóa
    $sql_check = "SELECT file_path FROM document_uploads WHERE document_id = $id AND username = '$current_user'";
    $query_check = mysqli_query($conn, $sql_check);
    $doc = mysqli_fetch_assoc($query_check);

    if ($doc) {
        if (!empty($doc['file_path']) && file_exists($doc['file_path'])) {
            unlink($doc['file_path']);
        }

        $sql_del = "DELETE FROM document_uploads WHERE document_id = $id";
        if (mysqli_query($conn, $sql_del)) {
            echo "<script>alert('Đã xóa tài liệu thành công!'); window.location.href='manage_documents.php';</script>";
            exit(); 
        } else {
            echo "<script>alert('Lỗi MySQL: " . mysqli_error($conn) . "');</script>";
        }
    } else {
        echo "<script>alert('Không thể xóa! Có thể tài liệu không tồn tại hoặc không phải của bạn.'); window.location.href='manage_documents.php';</script>";
        exit();
    }
}

// --- Xử lý duyệt/ẩn ---
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $new_status = intval($_GET['toggle_status']); 
    
    $sql_update = "UPDATE document_uploads SET status = $new_status WHERE document_id = $id AND username = '$current_user'";
    mysqli_query($conn, $sql_update);
    header("Location: manage_documents.php");
    exit();
}

// 3. QUERY LẤY DỮ LIỆU (Đã đổi tên biến để tránh lỗi)
$sql = "SELECT * FROM document_uploads WHERE username = '$current_user'";
$search = "";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $sql .= " AND title LIKE '%$search%'";
}

$sql .= " ORDER BY document_id DESC";

// *** ĐỔI TÊN BIẾN TỪ $result THÀNH $result_docs ***
$result_docs = mysqli_query($conn, $sql);

// Hàm format size
function formatSizeUnits($bytes) {
    if ($bytes >= 1073741824) { $bytes = number_format($bytes / 1073741824, 2) . ' GB'; }
    elseif ($bytes >= 1048576) { $bytes = number_format($bytes / 1048576, 2) . ' MB'; }
    elseif ($bytes >= 1024) { $bytes = number_format($bytes / 1024, 2) . ' KB'; }
    elseif ($bytes > 1) { $bytes = $bytes . ' bytes'; }
    elseif ($bytes == 1) { $bytes = $bytes . ' byte'; }
    else { $bytes = '0 bytes'; }
    return $bytes;
}
?>

<?php include("header.php"); ?>

<div class="container mrt">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="text-primary"><i class="bi bi-folder2-open"></i> Quản lý tài liệu cá nhân</h2>
        </div>
        <a href="upload_document.php" class="btn btn-success shadow-sm">
            <i class="bi bi-cloud-upload"></i> Đăng tài liệu mới
        </a>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body bg-light rounded">
            <form action="" method="GET" class="row g-2">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Nhập tên tài liệu..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-small w-100" style="height: 38px; padding: 0;">Tìm kiếm</button>
                </div>
                <?php if(!empty($search)): ?>
                    <div class="col-12 mt-2">
                        <small>Đang tìm kiếm: <strong><?= htmlspecialchars($search) ?></strong> (<a href="manage_documents.php">Xóa lọc</a>)</small>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th scope="col" class="py-3 ps-3">STT</th>
                            <th scope="col" class="py-3" style="width: 30%;">Tài liệu</th>
                            <th scope="col" class="py-3">Danh mục</th>
                            <th scope="col" class="py-3">Thống kê</th>
                            <th scope="col" class="py-3">Trạng thái</th>
                            <th scope="col" class="py-3">Ngày đăng</th>
                            <th scope="col" class="py-3 text-end pe-3">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result_docs) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result_docs)): ?>
                                <tr>
                                    <td class="ps-3"><strong>#<?= $row['document_id'] ?></strong></td>
                                    
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-2 fs-4 text-secondary">
                                                <i class="bi bi-file-earmark-text"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($row['title']) ?>">
                                                    <?= htmlspecialchars($row['title']) ?>
                                                </div>
                                                <small class="text-muted" style="font-size: 0.85em;">
                                                    <?= strtoupper($row['file_type']) ?> &bull; <?= formatSizeUnits($row['file_size']) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="badge bg-light text-dark border">ID: <?= $row['category_id'] ?></span>
                                    </td>

                                    <td>
                                        <small class="d-block text-muted"><i class="bi bi-eye"></i> <?= $row['views'] ?></small>
                                        <small class="d-block text-muted"><i class="bi bi-share"></i> <?= $row['shares'] ?></small>
                                    </td>

                                    <td>
                                        <?php if ($row['status'] == 1): ?>
                                            <a href="?toggle_status=0&id=<?= $row['document_id'] ?>" 
                                               class="badge bg-success text-decoration-none" 
                                               onclick="return confirm('Ẩn tài liệu này?');">
                                               <i class="bi bi-check-circle"></i> Hiển thị
                                            </a>
                                        <?php else: ?>
                                            <a href="?toggle_status=1&id=<?= $row['document_id'] ?>" 
                                               class="badge bg-warning text-dark text-decoration-none" 
                                               onclick="return confirm('Hiển thị tài liệu này?');">
                                               <i class="bi bi-eye-slash"></i> Đang ẩn
                                            </a>
                                        <?php endif; ?>
                                    </td>

                                    <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>

                                    <td class="text-end pe-3">
                                        <div class="btn-group" role="group">
                                            <a href="<?= $row['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Tải về">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <a href="edit_document.php?id=<?= $row['document_id'] ?>" class="btn btn-sm btn-outline-primary" title="Sửa">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="?delete_id=<?= $row['document_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa vĩnh viễn?');" title="Xóa">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Bạn chưa có tài liệu nào.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="mt-3 text-end mb-5">
        <span class="badge bg-secondary">Tổng số: <?= mysqli_num_rows($result_docs) ?> tài liệu</span>
    </div>
</div>

<?php include("footer.php"); ?>