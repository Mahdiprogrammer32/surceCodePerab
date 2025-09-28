<?php
session_start();
// تنظیم هدر برای پشتیبانی از UTF-8
header('Content-Type: text/html; charset=utf-8');

// فعال کردن نمایش خطاها برای دیباگ
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// اتصال به دیتابیس
$servername = "localhost";
$username = "departem_kakang";
$password = "mahdipass.2023";
$dbname = "departem_test";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8mb4");
} catch(PDOException $e) {
    echo "<p style='color: red; text-align: center;'>خطا: اتصال به دیتابیس شکست خورد: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// گرفتن نام و ID کاربر
$user_name = isset($_COOKIE['name']) ? htmlspecialchars($_COOKIE['name']) : 'کاربر ناشناس';
$user_id = isset($_COOKIE['userID']) ? (int)$_COOKIE['userID'] : 0;
$debug_message = '';
$projects = [];
$total_pages = 1;
$current_page = 1;
$total_projects = 0;

// تنظیم صفحه‌بندی
$projects_per_page = 5;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}
$offset = ($current_page - 1) * $projects_per_page;

// دیباگ: نمایش user_id و role
$debug_message .= "<p style='text-align: center; color: green;'>user_id: $user_id, role: " . (isset($_COOKIE['role']) ? htmlspecialchars($_COOKIE['role']) : 'none') . "</p>";

if ($user_id > 0) {
    // گرفتن تعداد کل پروژه‌ها
    $stmt = $conn->prepare("SELECT COUNT(*) FROM projects WHERE progress < 70 AND (contractor1 = ? OR contractor2 = ? OR contractor3 = ? OR contractor4 = ? OR contractor5 = ?)");
    $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
    $total_projects = $stmt->fetchColumn();
    $total_pages = ceil($total_projects / $projects_per_page);

    // دیباگ: نمایش تعداد پروژه‌ها
    $debug_message .= "<p style='text-align: center; color: blue;'>تعداد کل پروژه‌های یافت‌شده: $total_projects</p>";

    // گرفتن پروژه‌های مرتبط برای صفحه فعلی
    $stmt = $conn->prepare("SELECT id, phase_description, priority FROM projects WHERE progress < 70 AND (contractor1 = :user_id OR contractor2 = :user_id OR contractor3 = :user_id OR contractor4 = :user_id OR contractor5 = :user_id) LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $projects_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // دیباگ: نمایش پروژه‌های یافت‌شده
    if (empty($projects)) {
        $debug_message .= "<p style='text-align: center; color: red;'>هیچ پروژه‌ای با شرایط داده‌شده یافت نشد. (progress < 70, user_id = $user_id)</p>";
    } else {
        $debug_message .= "<p style='text-align: center; color: green;'>تعداد پروژه‌های این صفحه: " . count($projects) . "</p>";
    }
} else {
    $debug_message .= "<p style='color: red; text-align: center;'>خطا: userID معتبر نیست. لطفاً دوباره لاگین کنید.</p>";
}

// شروع بافر خروجی برای گرفتن گرید
ob_start();
?>
    <div class="user-info">
        خوش آمدید، <?php echo htmlspecialchars($user_name); ?> (نقش: <?php echo isset($_COOKIE['role']) ? htmlspecialchars($_COOKIE['role']) : 'نامشخص'; ?>)
    </div>
<?php echo $debug_message; ?>
    <div class="dashboard">
        <?php if (empty($projects)): ?>
            <p class="message">هیچ پروژه‌ای یافت نشد.</p>
        <?php else: ?>
            <?php foreach ($projects as $project): ?>
                <a href="projects/jozeat.php?project_id=<?php echo (int)$project['id']; ?>" class="project-tile">
                    <div>
                        <h3><?php echo htmlspecialchars($project['phase_description']); ?> (اولویت: <?php echo htmlspecialchars($project['priority']); ?>)</h3>
                        <p>شناسه پروژه: <?php echo (int)$project['id']; ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($current_page > 1): ?>
            <a href="dashboard.php?page=<?php echo $current_page - 1; ?>">صفحه قبلی</a>
        <?php else: ?>
            <a class="disabled">صفحه قبلی</a>
        <?php endif; ?>
        <span>صفحه <?php echo $current_page; ?> از <?php echo $total_pages; ?></span>
        <?php if ($current_page < $total_pages): ?>
            <a href="dashboard.php?page=<?php echo $current_page + 1; ?>">صفحه بعدی</a>
        <?php else: ?>
            <a class="disabled">صفحه بعدی</a>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php
// گرفتن خروجی گرید و پاک کردن بافر
$grid_output = ob_get_clean();

// اگه فایل مستقیم لود شده، کل صفحه رو نشون بده
if (basename($_SERVER['PHP_SELF']) === 'tes.php') {
    ?>
    <!DOCTYPE html>
    <html lang="fa" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>داشبورد پروژه‌ها</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css">
        <style>
            body {
                font-family: 'Vazir', Arial, sans-serif;
                background-color: #f4f4f4;
                margin: 0;
                padding: 20px;
            }
            * {
                text-decoration: none;
            }
            .dashboard {
                display: grid;
                grid-template-columns: repeat(5, 1fr);
                gap: 20px;
                max-width: 1200px;
                margin: 0 auto;
            }
            .project-tile {
                background-color: #ffffff;
                border: 1px solid #ddd;
                border-radius: 10px;
                padding: 15px;
                text-align: center;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                transition: transform 0.2s;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                min-height: 120px;
            }
            .project-tile:hover {
                transform: scale(1.05);
            }
            .project-tile h3 {
                font-size: 16px;
                margin: 0;
                color: #333;
            }
            .project-tile p {
                font-size: 14px;
                color: #666;
                margin: 5px 0 0;
            }
            .user-info {
                text-align: center;
                margin-bottom: 20px;
                font-size: 18px;
                color: #333;
            }
            .message {
                text-align: center;
                color: #d33;
                font-size: 16px;
                margin: 20px 0;
            }
            .pagination {
                text-align: center;
                margin-top: 20px;
            }
            .pagination a {
                display: inline-block;
                padding: 10px 15px;
                margin: 0 5px;
                background-color: #28a745;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                font-size: 14px;
            }
            .pagination a:hover {
                background-color: #218838;
            }
            .pagination a.disabled {
                background-color: #ccc;
                cursor: not-allowed;
            }
            @media (max-width: 1024px) {
                .dashboard {
                    grid-template-columns: repeat(3, 1fr);
                }
                .project-tile h3 {
                    font-size: 14px;
                }
                .project-tile p {
                    font-size: 12px;
                }
            }
            @media (max-width: 768px) {
                .dashboard {
                    grid-template-columns: 1fr;
                }
                .project-tile {
                    min-height: 120px;
                }
                .project-tile h3 {
                    font-size: 14px;
                }
                .project-tile p {
                    font-size: 12px;
                }
                .pagination a {
                    padding: 8px 12px;
                    font-size: 12px;
                }
            }
        </style>
    </head>
    <body>
    <?php echo $grid_output; ?>
    </body>
    </html>
    <?php
}
?>