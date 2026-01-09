<?php
// 1. KIỂM TRA QUYỀN VÀ TRẠNG THÁI
$isAdmin = (isset($_SESSION['role']) && (int)$_SESSION['role'] === 1);
$hasCommented = false;

if (isset($_SESSION['username'])) {
    $u_name = mysqli_real_escape_string($conn, $_SESSION['username']);
    $checkRes = mysqli_query($conn, "SELECT comment_id FROM comments WHERE username = '$u_name' AND document_id = $document_id AND parent_id = 0 LIMIT 1");
    if (mysqli_num_rows($checkRes) > 0) $hasCommented = true;
}

// 2. XỬ LÝ LƯU BÌNH LUẬN / TRẢ LỜI (Giữ nguyên logic của bạn)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['content'])) {
    if (isset($_SESSION['username'])) {
        $content = mysqli_real_escape_string($conn, $_POST['content']);
        $p_id = (int)($_POST['parent_id'] ?? 0);
        if ($p_id == 0 && $hasCommented && !$isAdmin) {
            echo "<script>alert('Bạn đã bình luận tài liệu này rồi!');</script>";
        } else {
            $sqlInsert = "INSERT INTO comments (username, content, document_id, parent_id, status) 
                          VALUES ('$u_name', '$content', $document_id, $p_id, 0)";
            if (mysqli_query($conn, $sqlInsert)) {
                echo "<script>window.location.href='document_detail.php?id=$document_id';</script>";
                exit;
            }
        }
    }
}

// 3. ĐIỀU KIỆN HIỂN THỊ: Khách chỉ xem status=0, Admin xem tất cả
$whereVisible = $isAdmin ? "" : " AND c.status = 0";
$totalRes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM comments c WHERE c.document_id = $document_id $whereVisible"));
$totalComments = $totalRes['total'];
?>

<style>
    .comment-section {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        min-height: 0;
        margin-top: 15px;
    }

    .comment-list {
        flex-grow: 1;
        overflow-y: auto;
        padding-right: 5px;
    }

    .comment-list::-webkit-scrollbar {
        width: 4px;
    }

    .comment-list::-webkit-scrollbar-thumb {
        background: #ddd;
        border-radius: 10px;
    }
</style>

<div class="comment-section mt-4 pt-3 border-top">
    <h6 class="fw-bold mb-3 text-uppercase" style="font-size: 0.85rem;">Bình luận (<?= $totalComments ?>)</h6>

    <?php if (!isset($_SESSION['username'])): ?>
        <div class="alert alert-light border small">Vui lòng <a href="login.php">đăng nhập</a> để bình luận.</div>
    <?php elseif ($hasCommented && !$isAdmin): ?>
        <div class="alert alert-info small text-center">Bạn đã để lại bình luận cho tài liệu này.</div>
    <?php else: ?>
        <form method="POST" class="mb-4">
            <input type="hidden" name="document_id" value="<?= $document_id ?>">
            <input type="hidden" name="parent_id" value="0">
            <textarea class="form-control mb-2 shadow-sm" name="content" rows="2" placeholder="Chia sẻ ý kiến của bạn..." required></textarea>
            <div class="text-end"><button type="submit" class="btn btn-primary btn-sm px-4 rounded-pill">Gửi</button></div>
        </form>
    <?php endif; ?>

    <div class="comment-list">
        <?php
        // Lấy bình luận gốc kèm Avatar
        $sqlMain = "SELECT c.*, u.avatar, 
            (SELECT COUNT(*) FROM comments r WHERE r.parent_id = c.comment_id) as reply_count
            FROM comments c 
            LEFT JOIN users u ON c.username = u.username 
            WHERE c.document_id = $document_id AND c.parent_id = 0 $whereVisible 
            ORDER BY c.created_at DESC";
        $resMain = mysqli_query($conn, $sqlMain);

        while ($c = mysqli_fetch_assoc($resMain)):
            $userAvatar = !empty($c['avatar']) ? 'uploads/users/' . $c['avatar'] : 'assets/img/default-user.jpg';
        ?>
            <div class="comment-item mb-2 pb-1 border-bottom">
                <div class="d-flex justify-content-between">
                    <div class="d-flex align-items-center">
                        <img src="<?= $userAvatar ?>" class="rounded-circle me-2 border" width="40" height="40" style="object-fit: cover;">
                        <span class="fw-bold"><?= htmlspecialchars($c['username']) ?></span>
                        <?php if ($isAdmin && $c['status'] == 1): ?>
                            <span class="badge bg-danger ms-2" style="font-size: 9px;">ĐÃ ẨN</span>
                        <?php endif; ?>
                    </div>
                    <span class="text-muted" style="font-size: 12px;"><?= date('d/m/Y', strtotime($c['created_at'])) ?></span>
                </div>

                <p class="ms-5 text-dark mb-0" style="font-size: 13px;"><?= nl2br(htmlspecialchars($c['content'])) ?></p>

                <div class="actions ms-5">
                    <?php if ($isAdmin): ?>
                        <?php if ((int)$c['reply_count'] === 0): ?>
                            <button class="btn btn-link p-0 text-decoration-none small me-3" onclick="toggleReply(<?= $c['comment_id'] ?>)" style="font-size: 12px;">
                                <i class="fa fa-reply me-1"></i>Trả lời
                            </button>
                        <?php endif; ?>
                        <a href="process_comment.php?action=toggle_status&id=<?= $c['comment_id'] ?>&doc_id=<?= $document_id ?>"
                            class="text-decoration-none small <?= $c['status'] == 0 ? 'text-danger' : 'text-success' ?>" style="font-size: 12px;">
                            <i class="fa <?= $c['status'] == 0 ? 'fa-eye-slash' : 'fa-eye' ?> me-1"></i>
                            <?= $c['status'] == 0 ? 'Ẩn' : 'Hiện' ?>
                        </a>
                    <?php endif; ?>
                </div>

                <div id="reply-form-<?= $c['comment_id'] ?>" class="d-none ms-5">
                    <form method="POST">
                        <input type="hidden" name="document_id" value="<?= $document_id ?>">
                        <input type="hidden" name="parent_id" value="<?= $c['comment_id'] ?>">
                        <textarea class="form-control mb-2" name="content" rows="1" placeholder="Phản hồi..." required></textarea>
                        <button type="submit" class="btn btn-dark btn-sm">Gửi</button>
                    </form>
                </div>

                <div class="replies ms-5 mt-3 border-start ps-3">
                    <?php
                    $parentId = $c['comment_id'];
                    $sqlRep = "SELECT c.*, u.avatar FROM comments c LEFT JOIN users u ON c.username = u.username 
                               WHERE c.parent_id = $parentId $whereVisible ORDER BY c.created_at ASC";
                    $resRep = mysqli_query($conn, $sqlRep);
                    while ($r = mysqli_fetch_assoc($resRep)):
                        $repAvatar = !empty($r['avatar']) ? 'uploads/users/' . $r['avatar'] : 'assets/img/default-user.jpg';
                    ?>
                        <div class="reply-item mb-2 p-2 bg-light rounded shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="d-flex align-items-center">
                                    <img src="<?= $repAvatar ?>" class="rounded-circle me-2 border" width="40" height="40" style="object-fit: cover;">
                                    <span class="fw-bold"><?= htmlspecialchars($r['username']) ?> <span class="badge bg-dark" style="font-size: 10px;">Admin</span></span>
                                </div>
                                <span class="text-muted" style="font-size: 12px;"><?= date('d/m/Y', strtotime($r['created_at'])) ?></span>
                            </div>
                            <p class="m-0 small text-secondary ms-4"><?= htmlspecialchars($r['content']) ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
    function toggleReply(id) {
        var form = document.getElementById('reply-form-' + id);
        form.classList.toggle('d-none');
    }
</script>