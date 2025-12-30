<?php
// get_subcategories.php
include 'config.php';

if (isset($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']);
    
    // Truy vấn danh mục con (Sử dụng status = 0 theo logic database của bạn)
    $stmt = $conn->prepare("SELECT subcategory_id, name FROM subcategories WHERE category_id = ? AND status = 0 ORDER BY name ASC");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<option value="">-- Chọn danh mục con --</option>';
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['subcategory_id'] . '">' . htmlspecialchars($row['name']) . '</option>';
        }
    } else {
        echo '<option value="">Không có danh mục con</option>';
    }
}
?>