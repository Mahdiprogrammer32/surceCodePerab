<?php
// فعال کردن نمایش خطاها برای دیباگ (فقط در محیط توسعه)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// تنظیم هدرها برای جلوگیری از کش
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');

// اعتبارسنجی دسترسی
if (!isset($_COOKIE['userID'])) {
    header("Location: login.php");
    exit();
}

// اتصال به دیتابیس
try {
    $conn = new PDO("mysql:host=localhost;dbname=fixwbcsq_perab", "fixwbcsq_kakang", "mahdipass.2023");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    $debug_message = "<p class='message' style='color: red;'>خطا: اتصال به دیتابیس شکست خورد: " . htmlspecialchars($e->getMessage()) . "</p>";
    $projects = [];
    $total_pages = 1;
    $current_page = 1;
    $user_name = 'کاربر ناشناس';
    $total_projects = 0;
}

// اطلاعات کاربر
$user_id = (int)($_COOKIE['userID'] ?? 0);
$user_name = htmlspecialchars($_COOKIE['name'] ?? 'کاربر ناشناس');
$debug_message = '';
$projects = [];
$total_pages = 1;
$current_page = 1;
$total_projects = 0;

// دیباگ اطلاعات کاربر
$debug_message .= "<p class='message' style='color: green;'>user_id: $user_id, role: " . htmlspecialchars($_COOKIE['role'] ?? 'none') . "</p>";

if ($user_id > 0 && isset($conn)) {
    // تنظیم صفحه‌بندی
    $projects_per_page = 16;
    $current_page = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($current_page - 1) * $projects_per_page;

    // تعداد کل پروژه‌ها
    $stmt = $conn->prepare("SELECT COUNT(*) FROM projects WHERE progress < 70 AND (contractor1 = ? OR contractor2 = ? OR contractor3 = ? OR contractor4 = ? OR contractor5 = ?)");
    $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
    $total_projects = (int)$stmt->fetchColumn();
    $total_pages = ceil($total_projects / $projects_per_page);

    $debug_message .= "<p class='message' style='color: blue;'>تعداد کل پروژه‌ها: $total_projects</p>";

    // پروژه‌های صفحه
    $sql = "SELECT 
                p.id, 
                p.phase_description, 
                p.priority, 
                p.progress, 
                e.factory 
            FROM projects p
            JOIN employees e ON p.employer = e.id
            WHERE p.progress < 70 
            AND (
                p.contractor1 = :user_id OR 
                p.contractor2 = :user_id OR 
                p.contractor3 = :user_id OR 
                p.contractor4 = :user_id OR 
                p.contractor5 = :user_id
            )
            ORDER BY FIELD(p.priority, 'A', 'B', 'C', 'D') 
            LIMIT $projects_per_page OFFSET $offset";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Include header and menu files
require_once 'require_once/menu.php';
require_once 'require_once/header.php';
?>

    <!DOCTYPE html>
    <html lang="fa" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
        <title>صفحه اصلی</title>
        <link rel="manifest" href="/manifest.json">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Gulzar&family=Reem+Kufi+Fun:wght@400..700&display=swap" rel="stylesheet">
        <link href="https://cdn.fontcdn.ir/Font/Persian/Vazir/Vazir.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="/fontA/css/all.min.css">
        <link rel="stylesheet" href="/assets/css/checkout.css">
        <link rel="stylesheet" href="/assets/css/style.css">
        <link rel="stylesheet" href="/assets/css/style_project.css">
        <link rel="stylesheet" href="/assets/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
        <link rel="stylesheet" href="https://unpkg.com/ag-grid-community/styles/ag-grid.css">
        <link rel="stylesheet" href="https://unpkg.com/ag-grid-community/styles/ag-theme-alpine.css">
        <link rel="stylesheet" href="/assets/css/calculator.css">
        <style>
            body {
                font-family: 'Vazir', 'Reem Kufi Fun', Arial, sans-serif;
                background-color: #f8f9fa;
                margin: 0;
                padding: 15px;
                min-height: 100vh;
            }
            * { text-decoration: none; }
            .container { max-width: 1400px; margin: 0 auto; }
            .header {
                background-color: #343a40;
                color: white;
                padding: 15px;
                text-align: center;
                margin-bottom: 15px;
                border-radius: 12px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            .sidebar {
                background-color: #e9ecef;
                padding: 15px;
                margin-bottom: 15px;
                border-radius: 12px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            .dashboard {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 10px;
                max-width: 100%;
                margin: 0 auto;
            }
            .project-tile {
                background-color: #ffffff;
                border: 1px solid #e0e0e0;
                border-radius: 12px;
                padding: 15px;
                text-align: center;
                box-shadow: 0 4px 12px rgba(0,0,0,0.08);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                position: relative;
                min-height: 120px;
                cursor: pointer;
            }
            .project-tile:hover {
                transform: translateY(-5px);
                box-shadow: 0 6px 16px rgba(0,0,0,0.12);
            }
            .project-tile h3 {
                font-size: 14px;
                margin: 0 0 8px 0;
                color: #343a40;
                font-weight: 600;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .project-tile p {
                font-size: 12px;
                color: #6c757d;
                margin: 4px 0;
                line-height: 1.4;
            }
            .message {
                text-align: center;
                color: #dc3545;
                font-size: 14px;
                margin: 15px 0;
            }
            .pagination {
                text-align: center;
                margin-top: 20px;
                margin-bottom: 20px;
            }
            .pagination a {
                display: inline-block;
                padding: 8px 12px;
                margin: 0 4px;
                background-color: #28a745;
                color: white;
                border-radius: 8px;
                font-size: 12px;
                transition: background-color 0.3s ease;
            }
            .pagination a:hover {
                background-color: #218838;
            }
            .pagination a.disabled {
                background-color: #ccc;
                cursor: not-allowed;
            }
            .priority {
                width: 30px;
                height: 30px;
                border-radius: 50%;
                position: absolute;
                left: -10px;
                top: -10px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
                font-size: 14px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                border: 2px solid #ffffff;
            }
            .calculator-modal .modal-content {
                border-radius: 15px;
                box-shadow: 0 8px 30px rgba(0,0,0,0.2);
                background: #fff;
            }
            .calculator-modal .modal-dialog {
                max-width: 90%;
                margin: 1.5rem auto;
            }
            .calculator-modal .calculator {
                padding: 15px;
                background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
                border-radius: 10px;
                margin: 15px;
                direction: rtl;
            }
            .calculator-modal #display {
                width: 100%;
                height: 50px;
                font-size: 1.8rem;
                text-align: right;
                padding: 8px;
                border: none;
                background: #e0e0e0;
                border-radius: 8px;
                margin-bottom: 15px;
                box-shadow: inset 0 2px 5px rgba(0,0,0,0.1);
            }
            .calculator-modal .buttons {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 8px;
            }
            .calculator-modal button {
                padding: 12px;
                font-size: 1.2rem;
                border: none;
                border-radius: 8px;
                background: #ffffff;
                box-shadow: 0 3px 8px rgba(0,0,0,0.1);
                cursor: pointer;
                transition: all 0.2s ease;
            }
            .calculator-modal button:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 12px rgba(0,0,0,0.2);
            }
            .calculator-modal button:active {
                transform: translateY(0);
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            .calculator-modal .number { color: #333; }
            .calculator-modal .operator { background: #ff9500; color: white; }
            .calculator-modal .clear { background: #ff3b30; color: white; }
            .calculator-modal .equal { background: #28a745; color: white; }
            .calculator-modal .zero { grid-column: span 2; }
            .calculator-modal .history {
                max-height: 80px;
                overflow-y: auto;
                margin: 8px 0;
                padding: 8px;
                background: #f8f9fa;
                border-radius: 8px;
                font-size: 0.8rem;
            }
            /* Responsive adjustments */
            @media (max-width: 768px) {
                body { padding: 10px; }
                .dashboard {
                    grid-template-columns: repeat(4, 1fr);
                    gap: 8px;
                }
                .project-tile {
                    padding: 12px;
                    min-height: 100px;
                }
                .project-tile h3 {
                    font-size: 12px;
                    margin-bottom: 6px;
                }
                .project-tile p {
                    font-size: 10px;
                    margin: 3px 0;
                }
                .priority {
                    width: 25px;
                    height: 25px;
                    font-size: 12px;
                    left: -8px;
                    top: -8px;
                }
                .pagination a {
                    padding: 6px 10px;
                    font-size: 11px;
                }
                .header, .sidebar {
                    padding: 10px;
                    margin-bottom: 10px;
                }
            }
            @media (max-width: 576px) {
                .dashboard {
                    grid-template-columns: repeat(4, 1fr);
                    gap: 6px;
                }
                .project-tile {
                    padding: 10px;
                    min-height: 90px;
                }
                .project-tile h3 {
                    font-size: 11px;
                    margin-bottom: 5px;
                }
                .project-tile p {
                    font-size: 9px;
                    margin: 2px 0;
                }
                .priority {
                    width: 22px;
                    height: 22px;
                    font-size: 11px;
                    left: -7px;
                    top: -7px;
                }
                .pagination a {
                    padding: 5px 8px;
                    font-size: 10px;
                }
                .calculator-modal .modal-dialog {
                    max-width: 95%;
                    margin: 1rem auto;
                }
                .calculator-modal .calculator {
                    margin: 10px;
                    padding: 10px;
                }
                .calculator-modal button {
                    padding: 10px;
                    font-size: 1rem;
                }
                .calculator-modal #display {
                    font-size: 1.5rem;
                    height: 45px;
                }
                .calculator-modal .history {
                    font-size: 0.7rem;
                    max-height: 70px;
                }
            }
            /* نادیده گرفتن فونت‌های نادرست در style.css */
            @font-face {
                font-family: 'Vazir';
                src: url('https://cdn.fontcdn.ir/Font/Persian/Vazir/Vazir.woff2') format('woff2');
                font-weight: normal;
                font-style: normal;
            }
        </style>
    </head>
    <body>
    <?php
    // Call header and menu functions
    headers();
    menus();
    ?>
    <div class="l_body">
        <div class="content">
            <?php echo $debug_message; ?>
            <div class="dashboard">
                <?php if (empty($projects)): ?>
                    <p class="message">هیچ پروژه‌ای یافت نشد.</p>
                <?php else: ?>
                    <?php foreach ($projects as $project): ?>
                        <?php
                        // Determine priority background color
                        $priority = $project['priority'];
                        $bg_priority = match ($priority) {
                            'A' => 'red',
                            'B' => 'orange',
                            'C' => 'yellow',
                            'D' => 'green',
                            default => 'white',
                        };

                        // Determine progress-based styling
                        $addP = '';
                        $anbar = '';
                        $nazer = '';
                        $hesab = '';
                        $progress = $project['progress'] ?? 0;

                        if ($progress <= 10) {
                            $addP = 'red';
                        } elseif ($progress >= 95) {
                            $addP = 'red';
                            $anbar = 'brown';
                            $nazer = 'orange';
                            $hesab = 'green';
                        } elseif ($progress >= 80) {
                            $addP = 'red';
                            $anbar = 'brown';
                            $nazer = 'orange';
                        } elseif ($progress >= 70) {
                            $addP = 'red';
                            $anbar = 'brown';
                        }
                        ?>
                        <a href="projects/jozeat.php?project_id=<?php echo (int)$project['id']; ?>" class="project-tile">
                            <div style="position: relative;">
                                <h3><?php echo htmlspecialchars($project['factory']); ?></h3>
                                <span class="priority" style="background-color: <?php echo $bg_priority; ?>;">
                                <?php echo htmlspecialchars($project['priority']); ?>
                            </span>
                                <p style="color: <?php echo $addP; ?>;">
                                    <?php echo htmlspecialchars($project['phase_description']); ?>
                                </p>
                                <p>شناسه پروژه: <?php echo (int)$project['id']; ?></p>
                                <?php if ($anbar): ?>
                                    <p style="color: <?php echo $anbar; ?>;">وضعیت انبار: فعال</p>
                                <?php endif; ?>
                                <?php if ($nazer): ?>
                                    <p style="color: <?php echo $nazer; ?>;">وضعیت ناظر: فعال</p>
                                <?php endif; ?>
                                <?php if ($hesab): ?>
                                    <p style="color: <?php echo $hesab; ?>;">وضعیت حساب: فعال</p>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <a href="index.php?page=<?php echo $current_page - 1; ?>" class="<?php echo $current_page <= 1 ? 'disabled' : ''; ?>">صفحه قبلی</a>
                    <span>صفحه <?php echo $current_page; ?> از <?php echo $total_pages; ?></span>
                    <a href="index.php?page=<?php echo $current_page + 1; ?>" class="<?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">صفحه بعدی</a>
                </div>
            <?php endif; ?>
        </div>
        <!-- اضافه کردن استایل‌ها و اسکریپت Swiper از CDN -->
        <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
        <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

        <div class="carousel-container">
            <div class="swiper" id="mySwiper">
                <!-- wrapper برای اسلایدها -->
                <div class="swiper-wrapper">
                    <!-- اسلاید اول (دکمه ماشین‌حساب) -->
                    <div class="swiper-slide">
                        <div class="slide-content">
                            <button class="btn btn-success calculator-btn" data-bs-toggle="modal" data-bs-target="#calculator">
                                <span class="fa fa-calculator"></span> ماشین‌حساب
                            </button>
                        </div>
                    </div>
                    <!-- اسلایدهای تصاویر -->
                    <?php
                    $folder = __DIR__ . '/uploads';
                    $base_url = '/uploads/';
                    $images = glob($folder . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
                    if (empty($images)) {
                        echo '<p class="message">هیچ تصویری در پوشه uploads یافت نشد.</p>';
                    }
                    foreach ($images as $index => $image):
                        $imageName = basename($image);
                        $imagePath = $base_url . htmlspecialchars($imageName);
                        ?>
                        <div class="swiper-slide">
                            <div class="slide-content">
                                <button class="image-btn" data-bs-toggle="modal" data-bs-target="#imageModal" data-image="<?php echo $imagePath; ?>">
                                    <img src="<?php echo $imagePath; ?>" class="slide-image" alt="تصویر پروژه <?php echo $index + 1; ?>">
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- دکمه‌های ناوبری (در صورت وجود تصاویر) -->
                <?php if (count($images) > 0): ?>
                    <div class="swiper-button-prev custom-nav"></div>
                    <div class="swiper-button-next custom-nav"></div>
                <?php endif; ?>
                <!-- اضافه کردن صفحه‌بندی (Pagination) -->
                <div class="swiper-pagination"></div>
            </div>
        </div>

        <!-- مقداردهی Swiper -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const swiper = new Swiper('#mySwiper', {
                    slidesPerView: 'auto',
                    spaceBetween: 15,
                    loop: false,
                    freeMode: true, // اسکرول روان
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    },
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                    breakpoints: {
                        320: {
                            spaceBetween: 10,
                        },
                        640: {
                            spaceBetween: 15,
                        },
                        1024: {
                            spaceBetween: 20,
                        },
                    },
                    // افکت انیمیشن برای اسلایدها
                    speed: 600,
                    grabCursor: true, // نمایش اشاره‌گر دست هنگام کشیدن
                });

                // تنظیم تصویر مودال
                document.querySelectorAll('.image-btn').forEach(button => {
                    button.addEventListener('click', function () {
                        const imageSrc = this.getAttribute('data-image');
                        document.getElementById('modalImage').setAttribute('src', imageSrc);
                    });
                });
            });
        </script>

        <!-- استایل‌های حرفه‌ای -->
        <style>
            .carousel-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
                position: relative;
            }

            .swiper {
                padding: 10px 0;
            }

            .swiper-slide {
                width: auto;
                flex-shrink: 0;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .slide-content {
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 10px;
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .slide-content:hover {
                transform: translateY(-5px);
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            }

            .calculator-btn {
                width: 150px;
                height: 80px;
                border-radius: 15px;
                font-size: 16px;
                font-weight: 600;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                background: linear-gradient(45deg, #28a745, #34c759);
                border: none;
                color: #fff;
                transition: transform 0.2s ease;
            }

            .calculator-btn:hover {
                transform: scale(1.05);
            }

            .slide-image {
                height: 100px;
                width: 150px;
                object-fit: cover;
                border-radius: 10px;
                transition: transform 0.3s ease;
            }

            .image-btn {
                background: none;
                border: none;
                padding: 0;
                cursor: pointer;
            }

            .image-btn:hover .slide-image {
                transform: scale(1.1);
            }

            .custom-nav {
                color: #333;
                background: rgba(255, 255, 255, 0.8);
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
                transition: background 0.3s ease;
            }

            .custom-nav:hover {
                background: #fff;
            }

            .swiper-button-prev:after,
            .swiper-button-next:after {
                font-size: 18px;
            }

            .swiper-pagination-bullet {
                background: #333;
                opacity: 0.6;
            }

            .swiper-pagination-bullet-active {
                background: #28a745;
                opacity: 1;
            }

            /* استایل‌های مودال */
            .modal-content {
                background: #fff;
                border-radius: 15px;
                overflow: hidden;
            }

            .modal-body {
                padding: 0;
            }

            #modalImage {
                max-height: 90vh;
                max-width: 90vw;
                object-fit: contain;
                border-radius: 10px;
            }

            .btn-close {
                background: #fff;
                border-radius: 50%;
                padding: 10px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            }
        </style>

        <!-- مودال نمایش تصویر -->
        <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
                    </div>
                    <div class="modal-body d-flex justify-content-center align-items-center">
                        <img id="modalImage" src="" class="img-fluid" alt="تصویر پروژه">
                    </div>
                </div>
            </div>
        </div>

    <!-- مودال ماشین‌حساب -->
    <div class="modal fade calculator-modal" id="calculator" tabindex="-1" aria-labelledby="calculatorLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="calculatorLabel">ماشین‌حساب</h5>
                </div>
                <div class="calculator">
                    <input type="text" id="display" value="0" disabled>
                    <div class="history" id="history"></div>
                    <div class="buttons">
                        <button class="clear" onclick="clearDisplay()">C</button>
                        <button class="number" onclick="appendToDisplay('7')">7</button>
                        <button class="number" onclick="appendToDisplay('8')">8</button>
                        <button class="number" onclick="appendToDisplay('9')">9</button>
                        <button class="operator" onclick="appendToDisplay('/')">/</button>
                        <button class="number" onclick="appendToDisplay('4')">4</button>
                        <button class="number" onclick="appendToDisplay('5')">5</button>
                        <button class="number" onclick="appendToDisplay('6')">6</button>
                        <button class="operator" onclick="appendToDisplay('*')">*</button>
                        <button class="number" onclick="appendToDisplay('1')">1</button>
                        <button class="number" onclick="appendToDisplay('2')">2</button>
                        <button class="number" onclick="appendToDisplay('3')">3</button>
                        <button class="operator" onclick="appendToDisplay('-')">-</button>
                        <button class="number" onclick="appendToDisplay('.')">.</button>
                        <button class="number zero" onclick="appendToDisplay('0')">0</button>
                        <button class="operator" onclick="appendToDisplay('+')">+</button>
                        <button class="equal" onclick="calculateResult()">=</button>
                        <button class="number" onclick="backspace()">⌫</button>
                        <button class="operator" onclick="appendToDisplay('%')">%</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">بستن</button>
                </div>
            </div>
        </div>
    </div>

    <!-- اسکریپت‌ها -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/highcharts-3d.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script src="/assets/js/calculator.js"></script>
    <script>
        // PWA نصب
        let deferredPrompt = null;
        const installBtn = document.getElementById('installBtn');
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            if (installBtn) {
                installBtn.style.display = 'block';
            }
        });

        if (installBtn) {
            installBtn.addEventListener('click', () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.then((choiceResult) => {
                        console.log(choiceResult.outcome === 'accepted' ? 'نصب اپلیکیشن تأیید شد!' : 'نصب رد شد.');
                        deferredPrompt = null;
                        installBtn.style.display = 'none';
                    });
                }
            });
        }

        // ثبت Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => console.log('Service Worker registered with scope:', registration.scope))
                    .catch(error => console.error('Error registering Service Worker:', error));
                navigator.serviceWorker.ready.then(() => console.log('Service Worker activated!'));
            });
        }

        // مدیریت تصاویر در مودال
        document.addEventListener('DOMContentLoaded', function () {
            const modalImage = document.getElementById('modalImage');
            if (!modalImage) {
                console.error('عنصر modalImage یافت نشد.');
                return;
            }

            document.querySelectorAll('.image-btn').forEach(button => {
                button.addEventListener('click', () => {
                    const imgSrc = button.getAttribute('data-image');
                    if (imgSrc) {
                        console.log("در حال نمایش:", imgSrc);
                        modalImage.src = imgSrc;
                    } else {
                        console.error('مقدار data-image یافت نشد.');
                    }
                });
            });
        });

        // ماشین‌حساب
        const display = document.getElementById('display');
        const history = document.getElementById('history');
        function appendToDisplay(value) {
            display.value = display.value === '0' && value !== '.' ? value : display.value + value;
        }
        function clearDisplay() {
            display.value = '0';
        }
        function backspace() {
            display.value = display.value.slice(0, -1) || '0';
        }
        function calculateResult() {
            try {
                const result = Function('"use strict";return (' + display.value + ')')();
                if (!isFinite(result)) throw new Error('عملیات نامعتبر');
                history.innerHTML += `<p>${display.value} = ${result}</p>`;
                display.value = result;
            } catch (error) {
                display.value = 'خطا';
                setTimeout(clearDisplay, 1000);
            }
        }
        document.addEventListener('keydown', (e) => {
            const key = e.key;
            if (/\d/.test(key)) appendToDisplay(key);
            else if (['+', '-', '*', '/'].includes(key)) appendToDisplay(key);
            else if (key === 'Enter') calculateResult();
            else if (key === 'Escape') clearDisplay();
            else if (key === 'Backspace') backspace();
            else if (key === '.') appendToDisplay('.');
            else if (key === '%') appendToDisplay('%');
        });
    </script>
    </body>
    </html>
<?php
// Close database connection
if (isset($conn)) {
    $conn = null;
}
?>