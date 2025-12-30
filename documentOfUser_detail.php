<?php
session_start();
include "config.php";
include "header.php";

// 1. HÀM ĐẾM SỐ TRANG PDF
function countPdfPages($filePath) {
    if (!file_exists($filePath)) return 0;
    $content = @file_get_contents($filePath);
    if ($content === false) return 0;
    preg_match_all("/\/Type\s*\/Page[^s]/", $content, $matches);
    return count($matches[0]);
}

$document_id = (int)$_GET['id'];

// 2. TRUY VẤN DỮ LIỆU TÀI LIỆU HIỆN TẠI
$sql = "SELECT d.*, c.name as cate_name, sc.name as subcate_name 
        FROM document_uploads d 
        LEFT JOIN categories c ON d.category_id = c.category_id 
        LEFT JOIN subcategories sc ON d.subcategory_id = sc.subcategory_id 
        WHERE d.document_id = $document_id AND d.status = 1 LIMIT 1";
$result = mysqli_query($conn, $sql);
$doc = mysqli_fetch_assoc($result);

if (!$doc) {
    echo "<div class='container' style='margin-top:150px;'><div class='alert alert-warning'>Tài liệu không tồn tại</div></div>";
    include "footer.php"; exit;
}

// 3. TĂNG LƯỢT XEM
mysqli_query($conn, "UPDATE document_uploads SET views = views + 1 WHERE document_id = $document_id");

$filePath = $doc['file_path'];
$pageCount = ($doc['file_type'] === 'pdf') ? countPdfPages($filePath) : 0;

// 4. KIỂM TRA TRẠNG THÁI LƯU (Nếu cần)
$isSaved = false;
if (isset($_SESSION['username'])) {
    $user_check = mysqli_real_escape_string($conn, $_SESSION['username']);
    $check_saved = mysqli_query($conn, "SELECT id FROM saved_documents WHERE username = '$user_check' AND document_id = $document_id");
    if (mysqli_num_rows($check_saved) > 0) $isSaved = true;
}
?>

<div class="main-content-wrapper" style="background-color: #f0f2f5; min-height: 100vh; padding-top: 100px; padding-bottom: 50px;">
    <div class="container-fluid px-lg-5">
        <div class="row g-4">
            
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm sticky-top" style="top: 100px; border-radius: 16px;">
                    <div class="card-body p-4">
                        <nav class="mb-3">
                            <ol class="breadcrumb small mb-0">
                                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                                <li class="breadcrumb-item active"><?= htmlspecialchars($doc['cate_name']) ?></li>
                            </ol>
                        </nav>

                        <h4 class="fw-bold mb-3" style="line-height: 1.4; color: #1a1a1a;">
                            <?= htmlspecialchars($doc['title']) ?>
                        </h4>

                        <div class="d-flex flex-wrap align-items-center gap-3 mb-4 text-muted small border-bottom pb-3">
                            <span title="Lượt xem"><i class="bi bi-eye-fill me-1"></i> <?= $doc['views'] + 1 ?> lượt xem</span>
                            <span title="Lượt tải"><i class="bi bi-cloud-download-fill me-1"></i> <?= $doc['shares'] ?> lượt tải</span>
                            <?php if($pageCount > 0): ?>
                                <span title="Số trang"><i class="bi bi-file-earmark-text-fill me-1"></i> <?= $pageCount ?> trang</span>
                            <?php endif; ?>
                        </div>

                        <p class="text-muted small mb-4">
                            <?= nl2br(htmlspecialchars($doc['description'] ?: 'Tài liệu chia sẻ bởi cộng đồng DocumentShare.')) ?>
                        </p>

                        <div class="d-grid gap-2 mb-3">
                            <a href="download.php?id=<?= $document_id ?>" class="btn btn-success btn-lg fw-bold py-3 shadow-sm border-0" style="background-color: #198754; border-radius: 12px;">
                                <i class="bi bi-cloud-arrow-down-fill me-2"></i> TẢI XUỐNG NGAY
                            </a>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <button class="btn btn-outline-primary w-100 py-2 small fw-bold border-2" style="border-radius: 10px;">
                                    <i class="bi bi-bookmark<?= $isSaved ? '-fill' : '' ?> me-1"></i> <?= $isSaved ? 'Đã lưu' : 'Lưu lại' ?>
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-outline-secondary w-100 py-2 small fw-bold border-2" onclick="copyUrl()" style="border-radius: 10px;">
                                    <i class="bi bi-share me-1"></i> Chia sẻ
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top small">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2 fw-bold" style="width: 32px; height: 32px;">
                                    <?= strtoupper(substr($doc['username'], 0, 1)) ?>
                                </div>
                                <span class="text-dark fw-bold"><?= htmlspecialchars($doc['username']) ?></span>
                            </div>
                            <div class="text-muted">Đăng vào: <?= date('d/m/Y', strtotime($doc['created_at'])) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card border-0 shadow-lg overflow-hidden" style="border-radius: 16px; background-color: #525659;">
                    <div class="d-flex justify-content-between align-items-center px-4 py-2 text-white-50" style="background-color: #323639;">
                        <div class="small fw-bold text-truncate" style="max-width: 80%;"><i class="bi bi-file-pdf-fill text-danger me-2"></i><?= htmlspecialchars($doc['title']) ?></div>
                        <i class="bi bi-fullscreen pointer" onclick="toggleFullScreen()"></i>
                    </div>
                    
                    <div class="card-body p-0">
                        <?php if ($doc['file_type'] === 'pdf' && file_exists($filePath)): ?>
                            <iframe id="pdf-frame" src="<?= $filePath ?>#view=FitH&pagemode=thumbs" width="100%" height="900" style="border: none; display: block;"></iframe>
                        <?php else: ?>
                            <div class="text-center py-5 text-white my-5">
                                <i class="bi bi-file-earmark-lock fs-1 mb-3"></i>
                                <h5>Định dạng không hỗ trợ xem trực tiếp</h5>
                                <a href="download.php?id=<?= $document_id ?>" class="btn btn-light mt-3 px-4">Tải về</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-2">
                <h6 class="fw-bold mb-4 text-dark"><i class="bi bi-person-badge text-primary me-2"></i>Từ <?= htmlspecialchars($doc['username']) ?></h6>
                <?php
                $uploader = $doc['username'];
                // Lấy các tài liệu khác của người đăng tài liệu hiện tại
                $sqlUserDocs = "SELECT document_id, title, file_type FROM document_uploads 
                                WHERE username = '$uploader' AND document_id != $document_id AND status = 1 
                                ORDER BY created_at DESC LIMIT 6";
                $resUserDocs = mysqli_query($conn, $sqlUserDocs);
                
                if(mysqli_num_rows($resUserDocs) > 0):
                    while($r = mysqli_fetch_assoc($resUserDocs)):
                ?>
                <a href="document_detail.php?id=<?= $r['document_id'] ?>" class="text-decoration-none d-block mb-3">
                    <div class="card border-0 shadow-sm related-card">
                        <div class="card-body p-2">
                            <div class="fw-bold text-dark small text-truncate mb-1" title="<?= htmlspecialchars($r['title']) ?>">
                                <?= htmlspecialchars($r['title']) ?>
                            </div>
                            <span class="badge bg-light text-muted border" style="font-size: 9px;"><?= strtoupper($r['file_type']) ?></span>
                        </div>
                    </div>
                </a>
                <?php endwhile; else: ?>
                    <p class="small text-muted italic">Người đăng chưa có tài liệu khác.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
function copyUrl() {
    navigator.clipboard.writeText(window.location.href);
    alert('Đã sao chép liên kết tài liệu!');
}
function toggleFullScreen() {
    let elem = document.getElementById("pdf-frame");
    if (elem.requestFullscreen) { elem.requestFullscreen(); }
}
</script>

<style>
    .related-card { transition: all 0.2s; border-radius: 10px; }
    .related-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important; border-left: 3px solid #0d6efd; }
    /* Icon style */
    .bi-eye-fill { color: #17a2b8; }
    .bi-cloud-download-fill { color: #28a745; }
    .bi-file-earmark-text-fill { color: #6c757d; }
</style>

<?php include "footer.php"; ?>