<?php
session_start();
$role = isset($_COOKIE["role"]) ? $_COOKIE["role"] : null;

$restricted_roles = ["پیمانکار", "انباردار", "ناظر فنی", "ناظر کیفی", "ناظر ارشد"];
if (in_array($role, $restricted_roles)) {
    echo '
    <style>
    #custom-alert {
        position: fixed;
        top: 30px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(45deg, #dc3545, #e4606d);
        color: white;
        padding: 20px 30px;
        border-radius: 12px;
        font-family: "Vazirmatn", sans-serif;
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        z-index: 1000;
        animation: slideIn 0.5s ease-in-out;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    #custom-alert i {
        font-size: 1.2rem;
    }
    @keyframes slideIn {
        from { opacity: 0; transform: translate(-50%, -30px); }
        to { opacity: 1; transform: translate(-50%, 0); }
    }
    </style>
    <div id="custom-alert"><i class="fas fa-exclamation-circle"></i> شما دسترسی به این بخش را ندارید!</div>
    <script>
    setTimeout(() => {
        window.location.href = "https://kalahabama.ir/index.php";
    }, 1500);
    </script>
    ';
    exit;
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="سیستم حسابداری پیشرفته با رابط کاربری مدرن و قابلیت‌های حرفه‌ای">
    <meta name="keywords" content="حسابداری, مدیریت مالی, فاکتور, گزارشات مالی">
    <title>سیستم حسابداری پیشرفته</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --info-color: #0dcaf0;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --sidebar-width: 320px;
            --sidebar-bg: linear-gradient(180deg, #1a1d29 0%, #2c323f 100%);
            --sidebar-text: #e9ecef;
            --content-bg: #f4f6f9;
            --transition-speed: 0.4s;
            --shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            --border-radius: 12px;
        }

        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: var(--content-bg);
            margin: 0;
            overflow-x: hidden;
        }

        /* سایدبار */
        .main-sidebar {
            position: fixed;
            top: 0;
            right: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            box-shadow: -3px 0 12px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            transition: transform var(--transition-speed) ease;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) rgba(0, 0, 0, 0.2);
        }

        .main-sidebar::-webkit-scrollbar {
            width: 8px;
        }

        .main-sidebar::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            text-align: center;
            background: rgba(0, 0, 0, 0.3);
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        .sidebar-header h3 {
            color: var(--sidebar-text);
            font-weight: 700;
            margin: 0;
            font-size: 1.7rem;
            letter-spacing: 0.5px;
        }

        .sidebar-header .company-logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid rgba(255, 255, 255, 0.25);
            margin-bottom: 1rem;
            transition: transform var(--transition-speed), box-shadow var(--transition-speed);
        }

        .sidebar-header .company-logo:hover {
            transform: scale(1.08);
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.3);
        }

        .sidebar-menu {
            padding: 1.5rem 0;
        }

        .menu-section-title {
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.95rem;
            padding: 0.8rem 1.8rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 0.9rem 1.8rem;
            color: var(--sidebar-text);
            text-decoration: none;
            transition: all var(--transition-speed) ease;
            position: relative;
            border-radius: 0 8px 8px 0;
        }

        .menu-item:hover,
        .menu-item.active {
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.1), transparent);
            color: #fff;
        }

        .menu-item.active::before {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 6px;
            background: var(--primary-color);
            border-radius: 0 4px 4px 0;
        }

        .menu-icon {
            margin-left: 1.2rem;
            font-size: 1.4rem;
            width: 26px;
            text-align: center;
        }

        .menu-text {
            flex-grow: 1;
            font-size: 1.15rem;
            font-weight: 500;
        }

        .menu-badge {
            background: var(--primary-color);
            color: #fff;
            padding: 0.3rem 0.7rem;
            border-radius: 14px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .menu-arrow {
            transition: transform var(--transition-speed);
        }

        .menu-arrow.rotated {
            transform: rotate(-180deg);
        }

        .submenu {
            padding-right: 2rem;
            background: rgba(0, 0, 0, 0.2);
        }

        .submenu-item {
            padding: 0.7rem 1.2rem 0.7rem 3rem;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 1rem;
            transition: all var(--transition-speed);
            border-radius: 8px;
            display: block;
        }

        .submenu-item:hover,
        .submenu-item.active {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            transform: translateX(-5px);
        }

        .main-content {
            margin-right: var(--sidebar-width);
            min-height: 100vh;
            transition: all var(--transition-speed);
            padding: 2rem;
        }

        .topbar {
            height: 80px;
            background: #fff;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .topbar-search {
            flex-grow: 1;
            max-width: 600px;
            position: relative;
        }

        .search-input {
            border: 1px solid var(--secondary-color);
            background: #f8f9fa;
            padding: 0.7rem 1.2rem 0.7rem 2.5rem;
            border-radius: 30px;
            width: 100%;
            transition: all var(--transition-speed);
            font-family: 'Vazirmatn', sans-serif;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15);
            background: #fff;
        }

        .search-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
        }

        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            transition: all var(--transition-speed);
            background: #fff;
        }

        .card:hover {
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
        }

        .card-header {
            background: linear-gradient(45deg, var(--primary-color), #4e73df);
            color: #fff;
            font-weight: 600;
            padding: 1.2rem 1.8rem;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-body {
            padding: 2rem;
        }

        .table {
            font-size: 1rem;
            border-collapse: separate;
            border-spacing: 0 0.5rem;
        }

        .table th {
            font-weight: 600;
            color: var(--primary-color);
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.6px;
        }

        .table td {
            vertical-align: middle;
            padding: 1rem;
        }

        .table-hover tbody tr {
            background: #fff;
            border-radius: 8px;
            transition: all var(--transition-speed);
        }

        .table-hover tbody tr:hover {
            background: rgba(13, 110, 253, 0.05);
            transform: translateY(-2px);
        }

        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 0.7rem 1.5rem;
            font-size: 1rem;
            transition: all var(--transition-speed);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .status-paid, .status-unpaid, .status-pending {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-block;
            transition: all var(--transition-speed);
        }

        .status-paid {
            color: var(--success-color);
            background: rgba(25, 135, 84, 0.15);
        }

        .status-unpaid {
            color: var(--danger-color);
            background: rgba(220, 53, 69, 0.15);
        }

        .status-pending {
            color: var(--warning-color);
            background: rgba(255, 193, 7, 0.15);
        }

        .dropdown-menu {
            border-radius: 10px;
            box-shadow: var(--shadow);
            border: none;
            padding: 0.5rem 0;
        }

        .dropdown-item {
            padding: 0.6rem 1.5rem;
            font-size: 1rem;
            transition: all var(--transition-speed);
        }

        .dropdown-item:hover {
            background: rgba(13, 110, 253, 0.1);
            color: var(--primary-color);
        }

        @media (max-width: 992px) {
            .main-sidebar {
                transform: translateX(100%);
            }

            .main-sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-right: 0;
            }

            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.6);
                z-index: 999;
                opacity: 0;
                visibility: hidden;
                transition: all var(--transition-speed);
            }

            .sidebar-overlay.show {
                opacity: 1;
                visibility: visible;
            }

            .topbar {
                height: 60px;
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .badge-counter {
            animation: pulse 2s infinite;
        }
    </style>
</head>

<body>
<div class="main-sidebar">
    <div class="sidebar-header">
        <img src="https://cdn.imgurl.ir/uploads/p125400_logo_2025-03-30_13-13-47.png" alt="لوگو" class="company-logo">
        <h3>سیستم حسابداری پیشرفته</h3>
    </div>
    <div class="sidebar-menu">
        <div class="menu-section">
            <div class="menu-section-title">حسابداری</div>
            <a href="#" class="menu-item active">
                <div class="menu-icon"><i class="fas fa-tachometer-alt"></i></div>
                <div class="menu-text">داشبورد</div>
            </a>
            <a href="#factors" class="menu-item" data-bs-toggle="collapse" aria-expanded="false">
                <div class="menu-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <div class="menu-text">فاکتورها</div>
                <div class="menu-badge">5</div>
                <div class="menu-arrow"><i class="fas fa-angle-down"></i></div>
            </a>
            <div class="submenu collapse" id="factors" data-bs-parent=".sidebar-menu">
                <a href="accounting/F_kh/index.php" class="submenu-item">فاکتور خرید</a>
                <a href="accounting/F_fo/index.php" class="submenu-item">فاکتور فروش</a>
                <a href="accounting/F_khadamat/index.php" class="submenu-item">فاکتور خدمات</a>
                <a href="#" class="submenu-item">برگشت از خرید</a>
                <a href="#" class="submenu-item">برگشت از فروش</a>
                <a href="#" class="submenu-item">پیش فاکتور</a>
            </div>
            <a href="#taarif" class="menu-item" data-bs-toggle="collapse" aria-expanded="false">
                <div class="menu-icon"><i class="fas fa-receipt"></i></div>
                <div class="menu-text">تعاریف</div>
                <div class="menu-arrow"><i class="fas fa-angle-down"></i></div>
            </a>
            <div class="submenu collapse" id="taarif" data-bs-parent=".sidebar-menu">
                <a href="https://kalahabama.ir/warehouse/add.php" class="submenu-item">ثبت کالا</a>
                <a href="https://kalahabama.ir/warehouse/add_khadamat.php" class="submenu-item">ثبت خدمات</a>
                <a href="https://kalahabama.ir/staff.php" class="submenu-item">کارمند</a>
                <a href="https://kalahabama.ir/contractor.php" class="submenu-item">پیمانکار</a>
                <a href="https://kalahabama.ir/oders.php" class="submenu-item">کارفرما</a>
                <a href="https://kalahabama.ir/suppliers.php" class="submenu-item">تامین کننده</a>
                <a href="https://kalahabama.ir/counting_unit.php" class="submenu-item">واحد ها</a>
                <a href="https://kalahabama.ir/accounting/banks/bank.php" class="submenu-item">بانک</a>
            </div>
            <a href="#submenuAccounting" class="menu-item" data-bs-toggle="collapse" aria-expanded="false">
                <div class="menu-icon"><i class="fas fa-book"></i></div>
                <div class="menu-text">دفاتر حسابداری</div>
                <div class="menu-arrow"><i class="fas fa-angle-down"></i></div>
            </a>
            <div class="submenu collapse" id="submenuAccounting" data-bs-parent=".sidebar-menu">
                <a href="https://kalahabama.ir/accounting/moeen/" class="submenu-item">دفتر معین</a>
            </div>
        </div>
        <div class="menu-section">
            <div class="menu-section-title">مدیریت مالی</div>
            <a href="#" class="menu-item">
                <div class="menu-icon"><i class="fas fa-wallet"></i></div>
                <div class="menu-text">صندوق و بانک</div>
            </a>
            <a href="#" class="menu-item">
                <div class="menu-icon"><i class="fas fa-exchange-alt"></i></div>
                <div class="menu-text">عملیات بانکی</div>
            </a>
            <a href="#amaliat_mali" class="menu-item" data-bs-toggle="collapse" aria-expanded="false">
                <div class="menu-icon"><i class="fas fa-exchange-alt"></i></div>
                <div class="menu-text">عملیات مالی</div>
                <div class="menu-arrow"><i class="fas fa-angle-down"></i></div>
            </a>
            <div class="submenu collapse" id="amaliat_mali" data-bs-parent=".sidebar-menu">
                <a href="#" class="submenu-item">بدهکار</a>
                <a href="#" class="submenu-item">بستانکار</a>
                <a href="#" class="submenu-item">دریافت</a>
                <a href="#" class="submenu-item">پرداخت</a>
                <a href="#" class="submenu-item">چک دریافتی</a>
                <a href="#" class="submenu-item">چک پرداختی</a>
            </div>
        </div>
        <div class="menu-section">
            <div class="menu-section-title">گزارشات مالی</div>
            <a href="#" class="menu-item">
                <div class="menu-icon"><i class="fas fa-chart-pie"></i></div>
                <div class="menu-text">گزارشات مالی</div>
            </a>
            <a href="#" class="menu-item">
                <div class="menu-icon"><i class="fas fa-sign-out-alt"></i></div>
                <div class="menu-text">خروج از سیستم</div>
            </a>
        </div>
    </div>
</div>

<div class="sidebar-overlay"></div>

<div class="main-content">
    <div class="topbar">
        <button class="btn btn-link text-dark d-lg-none me-2" id="sidebarToggle">
            <i class="fas fa-bars fa-lg"></i>
        </button>
        <div class="topbar-search">
            <input type="text" class="search-input" placeholder="جستجو در سیستم...">
            <i class="fas fa-search search-icon"></i>
        </div>
        <ul class="navbar-nav ms-auto d-flex align-items-center gap-3" style="flex-direction: row;">
            <li class="nav-item dropdown">
                <a class="nav-link" href="#" id="alertsDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-bell fa-lg"></i>
                    <span class="badge bg-danger badge-counter">3+</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in">
                    <h6 class="dropdown-header">مرکز اعلان‌ها</h6>
                    <a class="dropdown-item d-flex align-items-center" href="#">
                        <div class="me-3">
                            <div class="icon-circle bg-primary">
                                <i class="fas fa-file-alt text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500">1402/05/15</div>
                            <span class="font-weight-bold">فاکتور جدیدی برای تایید شما ارسال شد</span>
                        </div>
                    </a>
                    <a class="dropdown-item d-flex align-items-center" href="#">
                        <div class="me-3">
                            <div class="icon-circle bg-success">
                                <i class="fas fa-donate text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500">1402/05/14</div>
                            پرداخت فاکتور #2900 با موفقیت انجام شد
                        </div>
                    </a>
                    <a class="dropdown-item d-flex align-items-center" href="#">
                        <div class="me-3">
                            <div class="icon-circle bg-warning">
                                <i class="fas fa-exclamation-triangle text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500">1402/05/13</div>
                            موجودی حساب بانکی در حال اتمام است
                        </div>
                    </a>
                    <a class="dropdown-item text-center small text-gray-500" href="#">نمایش همه اعلان‌ها</a>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link" href="#" id="messagesDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-envelope fa-lg"></i>
                    <span class="badge bg-success badge-counter">7</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in">
                    <h6 class="dropdown-header">صندوق پیام</h6>
                    <a class="dropdown-item d-flex align-items-center" href="#">
                        <div class="dropdown-list-image me-3">
                            <img class="rounded-circle" src="https://cdn.imgurl.ir/uploads/p125400_photo_2025-03-30_13-13-47.jpg" width="50px" height="50px" alt="...">
                            <div class="status-indicator bg-success"></div>
                        </div>
                        <div>
                            <div class="text-truncate">لطفا فاکتور شماره 1234 را بررسی نمایید</div>
                            <div class="small text-gray-500">علی محمدی · 58 دقیقه پیش</div>
                        </div>
                    </a>
                    <a class="dropdown-item text-center small text-gray-500" href="#">خواندن همه پیام‌ها</a>
                </div>
            </li>
            <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                    <img class="img-profile rounded-circle" src="https://cdn.imgurl.ir/uploads/p125400_photo_2025-03-30_13-13-47.jpg" width="50px" height="50px" alt="پروفایل">
                </a>
                <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in">
                    <a class="dropdown-item" href="#"><i class="fas fa-user dropdown-item-icon"></i> پروفایل</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-cogs dropdown-item-icon"></i> تنظیمات</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-list dropdown-item-icon"></i> فعالیت‌ها</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#"><i class="fas fa-sign-out-alt dropdown-item-icon"></i> خروج</a>
                </div>
            </li>
        </ul>
    </div>

    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                <div class="card">
                    <div class="card-header"><i class="fas fa-wallet"></i> موجودی کل</div>
                    <div class="card-body">
                        <h4 class="text-primary mb-0">۱۲,۵۰۰,۰۰۰,۰۰۰ ریال</h4>
                        <small class="text-muted">تا تاریخ 1402/05/15</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                <div class="card">
                    <div class="card-header"><i class="fas fa-file-invoice-dollar"></i> فاکتورهای باز</div>
                    <div class="card-body">
                        <h4 class="text-warning mb-0">۸ فاکتور</h4>
                        <small class="text-muted">در انتظار پرداخت</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                <div class="card">
                    <div class="card-header"><i class="fas fa-chart-line"></i> درآمد ماه</div>
                    <div class="card-body">
                        <h4 class="text-success mb-0">۳,۲۰۰,۰۰۰,۰۰۰ ریال</h4>
                        <small class="text-muted">افزایش ۱۲٪</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                <div class="card">
                    <div class="card-header"><i class="fas fa-exclamation-triangle"></i> هشدارها</div>
                    <div class="card-body">
                        <h4 class="text-danger mb-0">۳ هشدار</h4>
                        <small class="text-muted">نیاز به بررسی</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header"><i class="fas fa-chart-line"></i> گردش مالی ماه جاری</div>
                    <div class="card-body">
                        <canvas id="monthlyRevenueChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header"><i class="fas fa-chart-pie"></i> توزیع هزینه‌ها</div>
                    <div class="card-body">
                        <canvas id="expenseDistributionChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header"><i class="fas fa-table"></i> آخرین تراکنش‌ها</div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>شماره تراکنش</th>
                                <th>تاریخ</th>
                                <th>مبلغ</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>#TX1234</td>
                                <td>1402/05/15</td>
                                <td>۵,۰۰۰,۰۰۰ ریال</td>
                                <td><span class="status-paid">پرداخت شده</span></td>
                                <td><a href="#" class="btn btn-sm btn-primary">جزئیات</a></td>
                            </tr>
                            <tr>
                                <td>#TX1235</td>
                                <td>1402/05/14</td>
                                <td>۳,۲۰۰,۰۰۰ ریال</td>
                                <td><span class="status-pending">در انتظار</span></td>
                                <td><a href="#" class="btn btn-sm btn-primary">جزئیات</a></td>
                            </tr>
                            <tr>
                                <td>#TX1236</td>
                                <td>1402/05/13</td>
                                <td>۷,۸۰۰,۰۰۰ ریال</td>
                                <td><span class="status-unpaid">پرداخت نشده</span></td>
                                <td><a href="#" class="btn btn-sm btn-primary">جزئیات</a></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    $(document).ready(function() {
        $('#sidebarToggle').click(function() {
            $('.main-sidebar').toggleClass('show');
            $('.sidebar-overlay').toggleClass('show');
        });

        $('.sidebar-overlay').click(function() {
            $('.main-sidebar').removeClass('show');
            $(this).removeClass('show');
        });

        $(document).click(function(e) {
            if (!$(e.target).closest('.dropdown-menu, .nav-link').length) {
                $('.dropdown-menu').removeClass('show');
            }
        });

        $('.menu-item[data-bs-toggle="collapse"]').on('show.bs.collapse', function() {
            $(this).find('.menu-arrow').addClass('rotated');
        }).on('hide.bs.collapse', function() {
            $(this).find('.menu-arrow').removeClass('rotated');
        });

        const ctx1 = document.getElementById('monthlyRevenueChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: ['هفته ۱', 'هفته ۲', 'هفته ۳', 'هفته ۴'],
                datasets: [{
                    label: 'گردش مالی (ریال)',
                    data: [12000000, 15000000, 13000000, 17000000],
                    backgroundColor: 'rgba(13, 110, 253, 0.2)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: 'rgba(13, 110, 253, 1)',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { font: { family: 'Vazirmatn', size: 12 } },
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    },
                    x: {
                        ticks: { font: { family: 'Vazirmatn', size: 12 } },
                        grid: { display: false }
                    }
                },
                plugins: {
                    legend: {
                        labels: { font: { family: 'Vazirmatn', size: 12 } }
                    },
                    tooltip: {
                        bodyFont: { family: 'Vazirmatn' },
                        titleFont: { family: 'Vazirmatn' }
                    }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                }
            }
        });

        const ctx2 = document.getElementById('expenseDistributionChart').getContext('2d');
        new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: ['حقوق و دستمزد', 'مواد اولیه', 'سایر هزینه‌ها', 'خسارت‌ها'],
                datasets: [{
                    label: 'توزیع هزینه‌ها',
                    data: [40, 30, 20, 10],
                    backgroundColor: [
                        'rgba(25, 135, 84, 0.7)',
                        'rgba(13, 110, 253, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(220, 53, 69, 0.7)'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { family: 'Vazirmatn', size: 12 } }
                    },
                    tooltip: {
                        bodyFont: { family: 'Vazirmatn' },
                        titleFont: { family: 'Vazirmatn' }
                    }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                }
            }
        });
    });
</script>
</body>
</html>