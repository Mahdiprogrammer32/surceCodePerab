<?php
session_start();
$role = $_COOKIE["role"];

if ($role == "پیمانکار" ) {
    echo '
    <style>
    #custom-alert {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #f44336;
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        font-family: sans-serif;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        z-index: 1000;
    }
    </style>
    <div id="custom-alert">شما دسترسی به این بخش را ندارید!</div>
    <script>
    setTimeout(function() {
        window.location.href = "https://kalahabama.ir/index.php";
    }, 1000); // 1000 میلی‌ثانیه = 1 ثانیه
    </script>
    ';
    exit;
}




?>




<?php
require_once "database.php";
global $conn;

// لیست فایل‌های CSS
$cssLinks = [
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'assets/css/checkout.css'
    // 'assets/css/style_project.css'
];
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="پنل مدیریت پیشرفته">
    <title>پنل مدیریت | تنظیمات سیستم</title>
    
    <!-- Preload منابع -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" as="style">
    
    <!-- CSS Links -->
    <?php foreach ($cssLinks as $link): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($link) ?>" media="all">
    <?php endforeach; ?>
    
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Gulzar&family=Noto+Nastaliq+Urdu:wght@400..700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #00c8ff;
            --secondary: #ff6bff;
            --success: #2ecc71;
            --info: #3498db;
            --dark: #0f0f2d;
            --light: #f5f7fa;
            --card-bg: rgba(255, 255, 255, 0.08);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --neon-shadow: 0 0 20px rgba(0, 200, 255, 0.3);
            --border-radius: 12px;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            background: linear-gradient(135deg, var(--dark), #1a1a3a);
            color: var(--light);
           font-family: "Gulzar", serif;
            min-height: 100vh;
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        .dashboard {
            max-width: 1400px;
            width: 95%;
            margin: 2rem auto;
            padding: 2.5rem;
            background: rgba(15, 15, 45, 0.7);
            border-radius: var(--border-radius);
            backdrop-filter: blur(15px);
            box-shadow: var(--neon-shadow);
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: fadeIn 0.8s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .header {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }
        
        .header::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 200px;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
            border-radius: 3px;
        }
        
        .title {
            color: var(--primary);
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            text-shadow: 0 0 10px rgba(0, 200, 255, 0.5);
        }
        
        .subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
        }
        
        .grid {
            display: grid;
            gap: 2.8rem;
            margin-top: 2.5rem;
        }
        
        /* سیستم گرید واکنش‌گرا */
        .grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        @media (min-width: 576px) {
            .grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (min-width: 992px) {
            .grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (min-width: 1200px) {
            .grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        .card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2rem;
            text-align: center;
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 1;
            /*max-width: 40vw;*/
            /*max-height: 20vw;*/
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            overflow: hidden;
            margin-top:20px;

        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.2);
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            /* width: 100%;
            height: 100%; */
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.05), transparent);
            z-index: -1;
        }
        
        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 1.2rem;
            color: var(--primary);
            transition: var(--transition);
        }
        
        .card:hover .card-icon {
            transform: scale(1.1);
            text-shadow: 0 0 15px var(--primary);
        }
        
        /*.card-title {*/
        /*    font-size: 1.3rem;*/
        /*    font-weight: 600;*/
        /*    margin-bottom: 0.5rem;*/
        /*    color: white;*/
        /*}*/
        
        .card-desc {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95rem;
            /*margin-bottom: 1.5rem;*/
        }
        
        .btn {
            padding: 0.8rem 1.8rem;
            border-radius: 50px;
            font-weight: 500;
            font-size: 15px;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            z-index: 1;
            width: 100%;
            max-width: 400px;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
            z-index: -1;
        }
        
        .btn:hover::before {
            transform: translateX(100%);
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--info), #5d9cec);
            color: white;
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success), #48cfad);
            color: white;
        }
        
        .btn-electric {
            background: linear-gradient(135deg, var(--secondary), #ff6b9e);
            color: white;
        }
        
        .copy-section {
            margin-top: 3rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
        }
        
        #copyStatus {
            opacity: 0;
            height: 0;
            transition: all 0.3s ease;
            color: var(--primary);
            font-weight: 600;
            text-align: center;
        }
        
        #copyStatus.show {
            opacity: 1;
            height: auto;
        }
        
        /* رسپانسیو */
        @media (max-width: 768px) {
            .dashboard {
                padding: 1.5rem;
                width: 98%;
            }
            
            .title {
                font-size: 2rem;
            }
            
            .card {
                padding: 1.5rem;
                /*min-height: 200px;*/
            }
            
            .card-icon {
                font-size: 2rem;
            }
        }
        
        /* افکت‌های ویژه */
        /*.pulse {*/
        /*    animation: pulse 2s infinite;*/
        /*}*/
        
        /*@keyframes pulse {*/
        /*    0% { transform: scale(1); }*/
        /*    50% { transform: scale(1.05); }*/
        /*    100% { transform: scale(1); }*/
        /*}*/
    </style>
</head>
<body>

<div class="dashboard text-center pulse">
    <header class="header">
        <h1 class="title">
            <i class="fas fa-cogs"></i> پنل مدیریت
        </h1>
        <p class="subtitle">مدیریت سیستم و تنظیمات پیشرفته</p>
    </header>
    
    <div class="grid">
        <div class="card">
            <!--<i class="fas fa-images card-icon"></i>-->
            <!--<h3 class="card-title">گالری تصاویر</h3>-->
            <!-- <p class="card-desc">مدیریت و سازماندهی عکس‌ها و آلبوم‌ها</p> -->
            <a href="picture_page_managment.php" class="btn btn-success">
                 گالری
            </a>
        </div>
        
        <div class="card">
            <!--<i class="fas fa-user-cog card-icon"></i>-->
            <!--<h3 class="card-title">پنل ادمین</h3>-->
            <!-- <p class="card-desc">تنظیمات پیشرفته و مدیریت کاربران</p> -->
            <a href="admin/" class="btn btn-electric">
                پنل مدیریت </a>
        </div>
        
        <div class="card">
            <!--<i class="fas fa-link card-icon"></i>-->
            <!--<h3 class="card-title">لینک دانلود</h3>-->
            <!-- <p class="card-desc">کپی لینک مستقیم برای دانلود</p> -->
            <button id="copyButton" class="btn btn-primary">
               کپی لینک  
            </button>
            <input type="text" id="linkToCopy" value="https://kalahabama.ir/download.php" hidden readonly>
        </div>
        
        <div class="card">
            <!--<i class="fas fa-calculator card-icon"></i>-->
            <!--<h3 class="card-title">واحد شمارش</h3>-->
            <!-- <p class="card-desc">مدیریت واحدهای اندازه‌گیری سیستم</p> -->
            <a href="counting_unit.php " class="btn btn-primary">
                <!--<i class="fas fa-arrow-left"></i>-->
            واحدشمارش</a>
        </div>
        <div class="card">
            <!--<i class="fas fa-calculator card-icon"></i>-->
            <!--<h3 class="card-title">واحد شمارش</h3>-->
            <!-- <p class="card-desc">مدیریت واحدهای اندازه‌گیری سیستم</p> -->
            <a href="percent.php " class="btn btn-primary">
                <!--<i class="fas fa-arrow-left"></i>-->
                درصد ها</a>
            </div>
            <div class="card">
                <!--<i class="fas fa-calculator card-icon"></i>-->
                <!--<h3 class="card-title">واحد شمارش</h3>-->
                <!-- <p class="card-desc">مدیریت واحدهای اندازه‌گیری سیستم</p> -->
                <a href="https://kalahabama.ir/admin/manage_users.php " class="btn btn-primary">
                    <!--<i class="fas fa-arrow-left"></i>-->
                    دسترسی ها</a>
                </div>
                <div class="card">
                    <!--<i class="fas fa-calculator card-icon"></i>-->
                    <!--<h3 class="card-title">واحد شمارش</h3>-->
                    <!-- <p class="card-desc">مدیریت واحدهای اندازه‌گیری سیستم</p> -->
                    <a href="attendance-system/index.php" class="btn btn-primary">
                        <!--<i class="fas fa-arrow-left"></i>-->
                        حضور و غیاب</a>
                    </div>
                    <div class="card">
        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#beautifulModal">
            طرف حساب ها
        </a>
    </div>
                </div>
            </div>
    
    <div class="copy-section">
        <p id="copyStatus"></p>
    </div>
</div>


<!-- مودال زیبا -->
<div class="modal fade custom-modal" id="beautifulModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close-btn" data-bs-dismiss="modal" aria-label="Close">×</button>
            </div>
            <div class="modal-body text-center">
                <h4 class="mb-4">انتخاب عملیات</h4>
                
                <a href="https://kalahabama.ir/staff.php" class="btn btn-primary modal-btn">
                    <!-- <i class="bi bi-person-plus"></i>  -->
                    کارمند
                </a>
                
                <a href="https://kalahabama.ir/contractor.php" class="btn btn-success modal-btn">
                    <!-- <i class="bi bi-search"></i>  -->
                    پیمانکار
                </a>
                
                <a href="https://kalahabama.ir/oders.php" class="btn btn-info modal-btn">
                    <!-- <i class="bi bi-list-ul"></i>  -->
                    کارفرما
                </a>
                
                <a href="https://kalahabama.ir/suppliers.php" class="btn btn-warning modal-btn">
                    <!-- <i class="bi bi-gear"></i>  -->
                    تامین کننده
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<script>
    // اسکریپت برای نمایش انیمیشن هنگام نمایش مودال
    document.getElementById('beautifulModal').addEventListener('show.bs.modal', function () {
        // اینجا میتوانید انیمیشن های سفارشی اضافه کنید
    });
</script>
<!-- اسکریپت‌ها -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // مدیریت کپی لینک
    const copyButton = document.getElementById('copyButton');
    const copyStatus = document.getElementById('copyStatus');
    
    if (copyButton) {
        copyButton.addEventListener('click', async () => {
            try {
                const linkToCopy = document.getElementById('linkToCopy');
                linkToCopy.hidden = false;
                linkToCopy.select();
                
                await navigator.clipboard.writeText(linkToCopy.value);
                
                // نمایش پیام موفقیت
                copyStatus.textContent = '✅ لینک با موفقیت کپی شد!';
                copyStatus.classList.add('show');
                
                // تغییر موقت ظاهر دکمه
                copyButton.innerHTML = '<i class="fas fa-check"></i> کپی شد!';
                copyButton.style.background = 'linear-gradient(135deg, #2ecc71, #48cfad)';
                
                // بازگشت به حالت اولیه بعد از 3 ثانیه
                setTimeout(() => {
                    copyStatus.classList.remove('show');
                    copyButton.innerHTML = '<i class="fas fa-copy"></i> کپی لینک';
                    copyButton.style.background = 'linear-gradient(135deg, #3498db, #5d9cec)';
                }, 3000);
                
                linkToCopy.hidden = true;
            } catch (err) {
                console.error('خطا در کپی:', err);
                copyStatus.textContent = '❌ خطا در کپی لینک!';
                copyStatus.classList.add('show');
            }
        });
    }
});
</script>
</body>
</html>