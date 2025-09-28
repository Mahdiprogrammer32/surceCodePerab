<?php
session_start();

// تنظیم هدرها برای پاسخ JSON
header('Content-Type: application/json; charset=utf-8');

// اعتبارسنجی دسترسی
if (!isset($_COOKIE['userID'])) {
    echo json_encode(['error' => 'کاربر وارد نشده است']);
    exit();
}

// اتصال به دیتابیس
try {
    $conn = new PDO("mysql:host=localhost;dbname=fixwbcsq_perab", "fixwbcsq_kakang", "mahdipass.2023");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    echo json_encode(['error' => 'اتصال به دیتابیس شکست خورد: ' . htmlspecialchars($e->getMessage())]);
    exit();
}

$user_id = (int)($_COOKIE['userID'] ?? 0);
$projects_per_page = 16;
$current_page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($current_page - 1) * $projects_per_page;

// تعیین شرایط مشترک
$where_conditions = "progress < 70 AND peymankar = 0 AND (contractor1 = :user_id OR contractor2 = :user_id OR contractor3 = :user_id OR contractor4 = :user_id OR contractor5 = :user_id)";

// تعداد کل پروژه‌ها با شرایط یکسان
$count_sql = "SELECT COUNT(*) FROM projects WHERE " . $where_conditions;
$stmt = $conn->prepare($count_sql);
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$total_projects = (int)$stmt->fetchColumn();
$total_pages = ceil($total_projects / $projects_per_page);

// پروژه‌های صفحه با شرایط یکسان
$sql = "SELECT 
            p.id, 
            p.phase_description, 
            p.priority, 
            p.progress, 
            p.peymankar,
            e.factory 
        FROM projects p
        JOIN employees e ON p.employer = e.id
        WHERE " . $where_conditions . "
        ORDER BY FIELD(p.priority, 'A', 'B', 'C', 'D') 
        LIMIT $projects_per_page OFFSET $offset";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// پاسخ JSON
echo json_encode([
    'projects' => $projects,
    'current_page' => $current_page,
    'total_pages' => $total_pages,
    'total_projects' => $total_projects
]);

// بستن اتصال دیتابیس
$conn = null;
?>