<?php
require_once "database.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    $sql = "DELETE FROM percents WHERE id = ?";
    $stmt = executeQuery($sql, [$id]);
    
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'درصد با موفقیت حذف شد'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'خطا در حذف درصد یا درصد یافت نشد'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'درخواست نامعتبر'
    ]);
}
?>