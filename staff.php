<?php
// فعال کردن نمایش خطاها برای دیباگ
error_reporting(E_ALL);
ini_set('display_errors', 1);

// شروع session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// اتصال به دیتابیس با PDO
$host = 'localhost';
$db = 'fixwbcsq_perab';
$user = 'fixwbcsq_kakang';
$pass = 'mahdipass.2023';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die(json_encode(['error' => 'خطا در اتصال به دیتابیس: ' . $e->getMessage()]));
}

// بررسی وجود کوکی userID
if (!isset($_COOKIE['userID'])) {
    header("Location: login.php");
    exit();
}

// دریافت اطلاعات کاربر فعلی
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_COOKIE['userID']]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: login.php");
    exit();
}

// ذخیره اطلاعات کاربر در session
$_SESSION['user_data'] = $user;

// توابع بررسی دسترسی
function checkEmployeeAccess() {
    return isset($_SESSION['user_data']['access_add_employee']) && $_SESSION['user_data']['access_add_employee'] == 1;
}

function checkEmployeeAccessEdit() {
    return isset($_SESSION['user_data']['access_edit_employee']) && $_SESSION['user_data']['access_edit_employee'] == 1;
}

function checkEmployeeAccessDelete() {
    return isset($_SESSION['user_data']['access_delete_employee']) && $_SESSION['user_data']['access_delete_employee'] == 1;
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="سیستم مدیریت کارمندان با رابط کاربری شیک و ریسپانسیو">
    <meta name="keywords" content="مدیریت کارمندان, سیستم حسابداری, داشبورد حرفه‌ای">
    <meta name="author" content="xAI">
    <title>مدیریت کارمندان</title>

    <!-- لینک‌های CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <style>
        :root {
            --primary-color: #1e3a8a;
            --primary-hover: #1e40af;
            --success-color: #16a34a;
            --success-hover: #15803d;
            --danger-color: #dc2626;
            --danger-hover: #b91c1c;
            --warning-color: #d97706;
            --warning-hover: #b45309;
            --info-color: #0ea5e9;
            --info-hover: #0284c7;
            --light-bg: #f3f4f6;
            --white: #ffffff;
            --shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
            --transition-speed: 0.3s;
        }



        a{
            text-decoration:none;
        }

        body {
            background: linear-gradient(135deg, var(--light-bg), #e5e7eb);
            font-family: 'Vazirmatn', sans-serif;
            margin: 0;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .container {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin: 2rem auto;
            padding: 1.5rem;
            max-width: 1200px;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-hover));
            border-radius: 2px;
        }

        .search-container {
            margin-bottom: 1.5rem;
            position: relative;
            max-width: 350px;
            margin-right: auto;
            margin-left: auto;
        }

        .search-input {
            border-radius: 20px;
            padding: 0.7rem 1rem 0.7rem 2.5rem;
            border: 1px solid #d1d5db;
            width: 100%;
            font-family: 'Vazirmatn', sans-serif;
            font-size: 0.9rem;
            transition: all var(--transition-speed);
            background: #f9fafb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
            background: var(--white);
        }

        .search-icon {
            position: absolute;
            right: 0.8rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 1rem;
        }

        .table-responsive {
            border-radius: var(--border-radius);
            overflow-x: auto;
            background: var(--white);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .table {
            border-collapse: separate;
            border-spacing: 0 0.5rem;
            margin-bottom: 0;
            width: 100%;
        }

        .table th {
            background: linear-gradient(45deg, var(--primary-color));
            color: var(--white);
            font-weight: 600;
            padding: 0.8rem;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: none;
            text-align: center;
        }

        .table td {
            background: var(--white);
            padding: 0.8rem;
            vertical-align: middle;
            font-size: 0.9rem;
            border-radius: 6px;
            transition: all var(--transition-speed);
            text-align: center;
        }

        .table tbody tr {
            transition: all var(--transition-speed);
            animation: fadeInUp 0.5s ease-in-out;
        }

        .table tbody tr:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            background: rgba(30, 58, 138, 0.05);
        }


        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .btn {
            border-radius: 6px;
            font-weight: 600;
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
            transition: all var(--transition-speed);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .btn-success {
            background: var(--success-color);
            border-color: var(--success-color);
        }

        .btn-success:hover {
            background: var(--success-hover);
            border-color: var(--success-hover);
        }

        .btn-danger {
            background: var(--danger-color);
            border-color: var(--danger-color);
        }

        .btn-danger:hover {
            background: var(--danger-hover);
            border-color: var(--danger-hover);
        }

        .btn-info {
            background: var(--info-color);
            border-color: var(--info-color);
        }

        .btn-info:hover {
            background: var(--info-hover);
            border-color: var(--info-hover);
        }

        .btn-warning {
            background: var(--warning-color);
            border-color: var(--warning-color);
            cursor: not-allowed;
            opacity: 0.7;
        }

        .floating-button {
            position: fixed;
            bottom: 10vh;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            z-index: 1000;
            box-shadow: var(--shadow);
            transition: all var(--transition-speed);
            background: linear-gradient(45deg, var(--primary-color));
            color: var(--white);
            animation: pulse 2s infinite;
        }

        .floating-button:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .floating-button.bg-warning {
            background: linear-gradient(45deg, var(--warning-color), var(--warning-hover));
            color: #1f2937;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.08); }
            100% { transform: scale(1); }
        }

        .status-active, .status-inactive {
            padding: 0.3rem 0.8rem;
            border-radius: 14px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-active {
            color: var(--success-color);
            background: rgba(22, 163, 74, 0.15);
        }

        .status-inactive {
            color: var(--danger-color);
            background: rgba(220, 38, 38, 0.15);
        }

        .notification {
            position: fixed;
            top: 15px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(45deg, var(--danger-color), #f87171);
            color: var(--white);
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            box-shadow: var(--shadow);
            z-index: 1000;
            animation: slideIn 0.5s ease-in-out;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translate(-50%, -15px); }
            to { opacity: 1; transform: translate(-50%, 0); }
        }

        /* ریسپانسیو */
        @media (max-width: 992px) {
            .container {
                padding: 1rem;
                margin: 1rem;
            }

            .page-title {
                font-size: 1.6rem;
            }

            .search-container {
                max-width: 300px;
            }

            .floating-button {
                bottom: 8vh;
                right: 15px;
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }
        }

        @media (max-width: 768px) {
            .table {
                font-size: 0.85rem;
            }

            .table th, .table td {
                padding: 0.6rem;
                font-size: 0.8rem;
            }

            .btn {
                padding: 0.3rem 0.6rem;
                font-size: 0.75rem;
            }

            .search-input {
                padding: 0.6rem 0.8rem 0.6rem 2rem;
                font-size: 0.85rem;
            }

            .search-icon {
                font-size: 0.9rem;
                right: 0.6rem;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 0.8rem;
                margin: 0.8rem;
            }

            .page-title {
                font-size: 1.4rem;
            }

            .search-container {
                max-width: 100%;
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .table th, .table td {
                padding: 0.5rem;
                font-size: 0.75rem;
            }

            .btn {
                padding: 0.3rem 0.5rem;
                font-size: 0.7rem;
            }

            .btn i {
                font-size: 0.8rem;
            }

            .floating-button {
                bottom: 6vh;
                right: 10px;
                width: 45px;
                height: 45px;
                font-size: 1rem;
            }
        }


        table>th,td{
            text-align: center;
        }
    </style>
</head>

<body>
<div class="container py-4">
    <h1 class="page-title">مدیریت کارمندان</h1>

    <div class="search-container">
        <input type="text" id="employeeSearch" class="search-input" placeholder="جستجو بر اساس نام یا سمت...">
        <i class="fas fa-search search-icon"></i>
    </div>

    <div class="table-responsive">
        <table id="employeesTable" class="table">
            <thead>
            <tr>
                <th>#</th>
                <th>سمت</th>
                <th>نام</th>
                <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $stmt = $pdo->prepare("SELECT * FROM users WHERE form = 'کارمندان' ORDER BY name ASC");
            $stmt->execute();
            $employees = $stmt->fetchAll();

            if ($employees) {
                $counter = 1;
                foreach ($employees as $employee) {
                    echo "<tr data-id='" . $employee['id'] . "'>";
                    echo "<td>$counter</td>";
                    echo "<td>" . htmlspecialchars($employee["role"]) . "</td>";
                    echo "<td>" . htmlspecialchars($employee["name"]) . "</td>";
                    echo "<td><span class='status-" . ($employee["status"] ?? 'active') . "'>" . ($employee["status"] ?? 'فعال') . "</span></td>";
                    echo "<td class='d-flex gap-2 justify-content-center'>";

                    if (checkEmployeeAccessEdit()) {
                        echo '<a href="edit_staff.php?user_id=' . $employee['id'] . '" class="btn btn-success btn-sm"><i class="fas fa-edit"></i> ویرایش</a>';
                    } else {
                        echo '<button class="btn btn-warning btn-sm" disabled><i class="fas fa-lock"></i></button>';
                    }

                    echo '<a href="show_license.php?userId=' . $employee['id'] . '" class="btn btn-info btn-sm"><i class="fas fa-copy"></i> مجوز</a>';

                    if (checkEmployeeAccessDelete()) {
                        echo '<button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(' . $employee['id'] . ')"><i class="fas fa-trash"></i> حذف</button>';
                    } else {
                        echo '<button class="btn btn-warning btn-sm" disabled><i class="fas fa-lock"></i></button>';
                    }

                    echo "</td></tr>";
                    $counter++;
                }
            } else {
                echo "<tr><td colspan='5' class='text-center'>کارمندی یافت نشد.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'require_once/menu.php'; menus(); ?>

<?php if (checkEmployeeAccess()): ?>
    <a href="add_staff_page.php" class="floating-button" title="افزودن کارمند جدید">
        <i class="fas fa-plus"></i>
    </a>
<?php else: ?>
    <div class="floating-button bg-warning" title="عدم دسترسی">
        <i class="fas fa-lock"></i>
    </div>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        const table = $('#employeesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fa.json'
            },
            dom: 't<"row align-items-center mt-3"<"col-md-6"i><"col-md-6"p>>',
            searchDelay: 300,
            responsive: true,
            order: [[2, 'asc']],
            columnDefs: [
                { targets: 0, width: '5%' },
                { targets: 4, orderable: false, width: '30%' }
            ],
            drawCallback: function() {
                $('.dataTables_paginate .paginate_button').addClass('btn btn-sm btn-light').css({
                    'border-radius': '6px',
                    'margin': '0 0.2rem',
                    'transition': 'all 0.3s'
                });
                $('.dataTables_paginate .paginate_button:hover').css({
                    'background': 'var(--primary-color)',
                    'color': '#fff',
                    'border-color': 'var(--primary-color)'
                });
            }
        });

        $('#employeeSearch').on('keyup', function() {
            table.search(this.value).draw();
        });

        window.confirmDelete = function(id) {
            Swal.fire({
                title: 'حذف کارمند',
                text: 'آیا مطمئن هستید که می‌خواهید این کارمند را حذف کنید؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--danger-color)',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-trash"></i> حذف',
                cancelButtonText: '<i class="fas fa-times"></i> انصراف',
                reverseButtons: true,
                customClass: {
                    popup: 'swal2-styled',
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary'
                },
                backdrop: 'rgba(0,0,0,0.7)',
                showClass: {
                    popup: 'animate__animated animate__fadeIn'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOut'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'در حال حذف...',
                        text: 'لطفاً صبر کنید',
                        icon: 'info',
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });
                    window.location.href = `delete_staff.php?user_id=${id}`;
                }
            });
        };
    });
</script>
</body>
</html>