<?php
session_start();

// اتصال به دیتابیس
$host = 'localhost';
$db   = 'fixwbcsq_perab';
$user = 'fixwbcsq_kakang';
$pass = 'mahdipass.2023';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// بررسی وجود کوکی‌های ضروری
if (!isset($_COOKIE['userID'])) {
    header("Location: login.php");
    exit();
}

// دریافت داده‌های کاربر از دیتابیس
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_COOKIE['userID']]);
$row = $stmt->fetch();

if ($row && !empty($row['id']) && !empty($row['name']) && !empty($row['role'])) {
    // داده‌ها درست هستند، آرایه داده‌های کاربر را ایجاد کنید
    $user_data = [
        'userID' => $row['id'],
        'name' => $row['name'],
        'phone' => $row['phone'],
        'role' => $row['role'],
        'access_add_contractor' => $row['access_add_contractor'],
        'access_edit_contractor' => $row['access_edit_contractor'],
        'access_delete_contractor' => $row['access_delete_contractor'],
        'access_add_employee' => $row['access_add_employee'],
        'access_edit_employee' => $row['access_edit_employee'],
        'access_delete_employee' => $row['access_delete_employee'],
        'access_add_employer' => $row['access_add_employer'],
        'access_edit_employer' => $row['access_edit_employer'],
        'access_delete_employer' => $row['access_delete_employer'],
        'access_view_settings' => $row['access_view_settings'],
        'access_enter_settings' => $row['access_enter_settings'],
        'access_edit_theme'  => $row['access_edit_theme'],
        'access_view_image' => $row['access_view_image'],
        'access_add_image' => $row['access_add_image'],
        'access_edit_image' => $row['access_edit_image'],
        'access_delete_image' => $row['access_delete_image']
    ];

    // ذخیره داده‌ها در session
    $_SESSION['user_data'] = $user_data;

    // هدایت به dashboard.php
    header("Location: dashboard.php");
    exit();
} else {
    // اگر داده‌ها درست نبودند یا کاربر یافت نشد، به صفحه لاگین هدایت شود
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>در حال بررسی...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS -->
    <style>
        .refresh-loader {
            position: fixed;
            top: -50px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            opacity: 0;
            transition: opacity 0.3s, top 0.3s;
        }

        @keyframes spin {
            0% { transform: translateX(-50%) rotate(0deg); }
            100% { transform: translateX(-50%) rotate(360deg); }
        }

        .refreshing .refresh-loader {
            top: 20px;
            opacity: 1;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <h1 class="text-center mb-4">در حال بررسی اطلاعات...</h1>
    <div class="refresh-loader"></div>
</div>
</body>
</html>