<?php
session_start();

// تنظیمات اولیه
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// حالت توسعه (فقط برای محیط توسعه)
const DEBUG = false;

if (DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// ثابت‌های تنظیمات
const COOKIE_LIFETIME_DAYS = 1000;
const SESSION_COOKIE_PARAMS = [
    'lifetime' => 0,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
];

// تابع تنظیم کوکی امن
function setSecureCookie(string $name, string $value, int $days = COOKIE_LIFETIME_DAYS): bool {
    return setcookie(
        $name,
        $value,
        [
            'expires' => time() + ($days * 24 * 60 * 60),
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );
}

// تنظیمات سشن
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(SESSION_COOKIE_PARAMS);
    session_start();
    session_regenerate_id(true); // جلوگیری از Session Fixation
}

// تنظیمات اتصال به دیتابیس
const DB_CONFIG = [
    'host' => 'localhost',
    'dbname' => 'fixwbcsq_perab',
    'user' => 'fixwbcsq_kakang',
    'pass' => 'mahdipass.2023',
    'charset' => 'utf8mb4'
];

const PDO_OPTIONS = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO(
        "mysql:host=" . DB_CONFIG['host'] . ";dbname=" . DB_CONFIG['dbname'] . ";charset=" . DB_CONFIG['charset'],
        DB_CONFIG['user'],
        DB_CONFIG['pass'],
        PDO_OPTIONS
    );
} catch (\PDOException $e) {
    $_SESSION['error'] = 'خطا در اتصال به پایگاه داده. لطفاً بعداً تلاش کنید.';
    if (DEBUG) {
        error_log("Database connection error: " . $e->getMessage());
    }
    header("Location: login.php");
    exit();
}

// بررسی ورود کاربر
if (isset($_COOKIE['token'])) {
    header("Location: home.php");
    exit();
}

// مدیریت درخواست POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $license_key = trim($_POST['license_key'] ?? '');
    $license_key = preg_replace('/\s+/', '', $license_key);

    if (DEBUG) {
        error_log("License Key Entered: $license_key");
    }

    // اعتبارسنجی کلید لایسنس
    if (!preg_match('/^[0-9a-fA-F]{64}$/', $license_key)) {
        $_SESSION['error'] = 'فرمت کلید لایسنس نامعتبر است. باید 64 کاراکتر هگزادسیمال باشد.';
        header("Location: login.php");
        exit();
    }

    try {
        // جستجوی کاربر
        $stmt = $pdo->prepare("SELECT * FROM users WHERE license_key = ?");
        $stmt->execute([$license_key]);
        $user = $stmt->fetch();

        if ($user) {
            // دریافت نقش‌های کاربر
            $stmt_roles = $pdo->prepare("
                SELECT r.title 
                FROM roles r
                INNER JOIN user_roles ur ON r.id = ur.role_id
                WHERE ur.user_id = ?
            ");
            $stmt_roles->execute([$user['id']]);
            $roles = $stmt_roles->fetchAll(PDO::FETCH_COLUMN);

            // اطلاعات کاربر
            $user_data = [
                'userID' => $user['id'],
                'name' => $user['name'],
                'roles' => implode(',', $roles),
                'role' => $user['role'],
                'token' => $user['license_key'],
                'phone' => $user['phone'],
                'access_add_contractor' => $user['access_add_contractor'],
                'access_edit_contractor' => $user['access_edit_contractor'],
                'access_delete_contractor' => $user['access_delete_contractor'],
                'access_add_employee' => $user['access_add_employee'],
                'access_edit_employee' => $user['access_edit_employee'],
                'access_delete_employee' => $user['access_delete_employee'],
                'access_add_employer' => $user['access_add_employer'],
                'access_edit_employer' => $user['access_edit_employer'],
                'access_delete_employer' => $user['access_delete_employer'],
                'access_view_settings' => $user['access_view_settings'],
                'access_enter_settings' => $user['access_enter_settings'],
                'access_edit_theme' => $user['access_edit_theme'],
                'access_view_image' => $user['access_view_image'],
                'access_add_image' => $user['access_add_image'],
                'access_edit_image' => $user['access_edit_image'],
                'access_delete_image' => $user['access_delete_image']
            ];

            // ذخیره در سشن و کوکی‌ها
            $_SESSION['user'] = $user_data;
            foreach ($user_data as $key => $value) {
                setSecureCookie($key, $value);
            }

            header("Location: home.php");
            exit();
        } else {
            $_SESSION['error'] = 'کلید لایسنس در سیستم ثبت نشده است.';
            header("Location: login.php");
            exit();
        }
    } catch (\PDOException $e) {
        $_SESSION['error'] = 'خطا در پردازش درخواست. لطفاً بعداً تلاش کنید.';
        if (DEBUG) {
            error_log("Database error: " . $e->getMessage());
        }
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به سیستم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.fontcdn.ir/Font/Persian/Vazir/Vazir.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6a11cb, #2575fc);
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Vazir', sans-serif;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 30px;
            background: #fff;
            border-radius: 15px;
            box-shadow: var(--shadow);
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 5px rgba(106, 17, 203, 0.3);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-size: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .alert {
            border-radius: 10px;
            font-size: 0.9rem;
        }

        h1 {
            font-size: 1.5rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h1 class="text-center mb-4">ورود به سیستم <i class="fas fa-sign-in-alt ms-2"></i></h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="post" id="login-form">
        <div class="mb-3">
            <label for="license_key" class="form-label">کلید لایسنس:</label>
            <input
                    type="text"
                    class="form-control"
                    id="license_key"
                    name="license_key"
                    placeholder="45ae4515fd97fe9dbd4c297f300216c585eaca74388258b204db0a54c21ba38a"
                    required
                    maxlength="64"
            >
        </div>
        <button type="submit" class="btn btn-primary w-100">
            ورود <i class="fas fa-arrow-left ms-2"></i>
        </button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('login-form').addEventListener('submit', function(e) {
        const licenseKey = document.getElementById('license_key').value;
        if (!/^[0-9a-fA-F]{64}$/.test(licenseKey)) {
            e.preventDefault();
            alert('کلید لایسنس باید 64 کاراکتر هگزادسیمال باشد.');
        }
    });
</script>
</body>
</html>