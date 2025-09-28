<?php
// خطاها فقط در محیط توسعه فعال باشد
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// هدرهای JSON و جلوگیری از کش
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// اعتبارسنجی کاربر
if (!isset($_COOKIE['userID']) || !is_numeric($_COOKIE['userID'])) {
    http_response_code(401);
    echo json_encode(['error' => 'کاربر وارد نشده یا نامعتبر است']);
    exit;
}

$user_id = (int)$_COOKIE['userID'];

// تنظیمات دیتابیس
$host = 'localhost';
$db   = 'fixwbcsq_perab';
$user = 'fixwbcsq_kakang';
$pass = 'mahdipass.2023';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// صفحه بندی
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$projects_per_page = 16;
$offset = ($page - 1) * $projects_per_page;

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // تعداد کل پروژه‌ها
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) FROM projects
        WHERE progress < 70 
          AND (contractor1 = ? OR contractor2 = ? OR contractor3 = ? OR contractor4 = ? OR contractor5 = ?)
    ");
    $countStmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
    $total_projects = (int)$countStmt->fetchColumn();

    $total_pages = max(1, ceil($total_projects / $projects_per_page));

    // دریافت پروژه‌ها
    $stmt = $pdo->prepare("
        SELECT 
            p.id, 
            p.phase_description, 
            p.priority, 
            p.progress, 
            e.factory 
        FROM projects p
        JOIN employees e ON p.employer = e.id
        WHERE p.progress < 70 
          AND (p.contractor1 = :user_id OR p.contractor2 = :user_id OR p.contractor3 = :user_id OR p.contractor4 = :user_id OR p.contractor5 = :user_id)
        ORDER BY FIELD(p.priority, 'A', 'B', 'C', 'D')
        LIMIT :limit OFFSET :offset
    ");

    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $projects_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $projects = $stmt->fetchAll();

    echo json_encode([
        'page' => $page,
        'totalPages' => $total_pages,
        'totalProjects' => $total_projects,
        'projects' => $projects,
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'خطا در ارتباط با دیتابیس',
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
