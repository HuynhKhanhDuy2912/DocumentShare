<?php
if (!isset($conn) || $conn === false) {
    die('<div class="alert alert-danger m-3">LỖI KẾT NỐI: Không thể kết nối DB.</div>');
}

$base_url = '?p=comments';
$page_title = "Danh sách bình luận";
$message = "";

// ------------------------------------------------------
// 1. CẤU HÌNH PHÂN TRANG
// ------------------------------------------------------
$limit = 10; // Số lượng bản ghi trên mỗi trang
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $limit;

// Tính tổng số bình luận gốc để phân trang
$sql_count = "SELECT COUNT(*) as total FROM comments WHERE parent_id = 0";
$res_count = mysqli_query($conn, $sql_count);
$total_records = mysqli_fetch_assoc($res_count)['total'];
$total_pages = ceil($total_records / $limit);

?>

<style>
    .unread-dot {
        width: 10px;
        height: 10px;
        background-color: #ff4d4d;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 5px rgba(255, 77, 77, 0.8);
    }

    .comment-text {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        font-size: 0.9rem;
    }

    /* Màu nền nhẹ cho hàng chưa phản hồi */
    .table-warning-light {
        background-color: #fffdf5 !important;
    }
</style>

<div class="card shadow">
    <div class="card-header bg-gradient-dark text-white d-flex align-items-center">
        <h4 class="mb-0"></i><?php echo $page_title; ?></h4>
    </div>

    <div class="card-body">
        <?php if ($message): ?>
            <div class="alert alert-danger"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr class="text-center">
                        <th width="50">STT</th>
                        <th>Thông tin người dùng</th>
                        <th>Nội dung bình luận</th>
                        <th>Tài liệu</th>
                        <th width="140">Phản hồi</th>
                        <th width="130">Trạng thái</th>
                        <th width="140">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT c.*, d.title as doc_title, u.avatar,
                            (SELECT COUNT(*) FROM comments r WHERE r.parent_id = c.comment_id) as reply_count
                            FROM comments c
                            JOIN documents d ON c.document_id = d.document_id
                            LEFT JOIN users u ON c.username = u.username
                            WHERE c.parent_id = 0
                            ORDER BY reply_count ASC, c.created_at DESC
                            LIMIT $limit OFFSET $offset";

                    $res = mysqli_query($conn, $sql);
                    $stt = $offset + 1;

                    if (mysqli_num_rows($res) > 0):
                        while ($row = mysqli_fetch_assoc($res)):
                            $is_unreplied = ($row['reply_count'] == 0);
                            $avatar = !empty($row['avatar']) ? '../uploads/users/' . $row['avatar'] : '../assets/img/default-user.png';
                    ?>
                            <tr class="<?php echo $is_unreplied ? 'table-warning-light' : ''; ?>">
                                <td class="text-center"><?php echo $stt++; ?></td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $avatar; ?>" width="50" height="50" class="rounded-circle border me-2" style="object-fit: cover;">
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($row['username']); ?></div>
                                            <div class="text-muted" style="font-size: 12px;">
                                                <i class="far fa-clock me-1"></i><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="comment-text" title="<?php echo htmlspecialchars($row['content']); ?>">
                                        <?php echo htmlspecialchars($row['content']); ?>
                                    </div>
                                </td>

                                <td>
                                    <a href="../document_detail.php?id=<?php echo $row['document_id']; ?>#comment-<?php echo $row['comment_id']; ?>"
                                        target="_blank" class="text-decoration-none small fw-bold text-truncate d-block" style="max-width: 150px;">
                                        <i class="fas fa-link me-1"></i><?php echo htmlspecialchars($row['doc_title']); ?>
                                    </a>
                                </td>

                                <td class="text-center">
                                    <?php if ($is_unreplied): ?>
                                        <span class="badge badge-dot border border-danger text-danger bg-white p-2" style="font-size: 11px;">
                                            <span class="unread-dot me-1"></span> Chưa phản hồi
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-success border border-success p-2" style="font-size: 11px;">
                                            <i class="fas fa-check me-1 text-success"></i> Đã phản hồi
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <?php if ($row['status'] == 0): ?>
                                        <span class="text-success small fw-bold"><i class="fas fa-eye"></i> Hiển thị</span>
                                    <?php else: ?>
                                        <span class="text-muted small fw-bold"><i class="fas fa-eye-slash"></i> Đang ẩn</span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <?php if ($is_unreplied): ?>
                                    <a href="../document_detail.php?id=<?php echo $row['document_id']; ?>#comment-<?php echo $row['comment_id']; ?>"
                                        class="btn btn-primary btn-sm rounded-pill px-3" title="Đi đến trả lời">
                                        <i class="fas fa-reply me-1"></i> Trả lời
                                    </a>
                                    <?php else: ?>
                                        <a href="#" style="cursor: not-allowed; opacity: 50%"
                                        class="btn btn-primary btn-sm rounded-pill px-3">
                                        <i class="fas fa-reply me-1"></i> Đã trả lời
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endwhile;
                    else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Không có bình luận nào</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo $base_url . '&page=' . ($current_page - 1); ?>">&laquo;</a>
                    </li>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo $base_url . '&page=' . $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo $base_url . '&page=' . ($current_page + 1); ?>">&raquo;</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

    </div>
</div>