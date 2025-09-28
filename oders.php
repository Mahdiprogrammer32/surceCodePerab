<?php
// شروع session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'require_once/loading.php';
require_once "database.php";
global $conn;

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

if ($row) {
    // ایجاد آرایه داده‌های کاربر
    $_SESSION['user_data'] = [
        'userID' => $row['id'],
        'name' => $row['name'],
        'phone' => $row['phone'],
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
} else {
    header("Location: login.php");
    exit();
}

// توابع بررسی دسترسی‌ها
function checkEmployerAccess() {
    return $_SESSION['user_data']['access_add_employer'] ?? false;
}

function checkEmployerAccessEdit() {
    return $_SESSION['user_data']['access_edit_employer'] ?? false;
}

function checkEmployerAccessDelete() {
    return $_SESSION['user_data']['access_delete_employer'] ?? false;
}

$a = 1; // شمارنده برای ردیف‌های جدول
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت کارفرمایان</title>
    
    <!-- لینک‌های CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="fontA/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.2/css/all.css">
    
    <!-- اسکریپت‌های JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400..700&display=swap" rel="stylesheet">
    <style>
        /* استایل‌های اصلی */
        :root {
            --primary-color: #007bff;
            --primary-hover: #0056b3;
            --success-color: #28a745;
            --success-hover: #218838;
            --danger-color: #dc3545;
            --danger-hover: #c82333;
            --warning-color: #ffc107;
            --light-bg: #f0f2f5;
            --white: #ffffff;
            --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 20px rgba(0, 0, 0, 0.2), 0 6px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 10px;
            --transition-normal: all 0.3s ease;
            --transition-fast: all 0.2s ease;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: "Noto Nastaliq Urdu", serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        /* استایل‌های کانتینر اصلی */
        .container {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            padding: 20px;
            margin-top: 20px;
            margin-bottom: 20px;
            transition: var(--transition-normal);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            max-width: 1200px;
        }
        
        .container:hover {
            transform: scale(1.01);
        }
        
        /* استایل‌های جدول */
        table {
            margin-top: 20px;
            border-collapse: collapse;
            width: 100%;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }
        
        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #dee2e6;
            transition: var(--transition-fast);
        }
        
        th {
            background-color: var(--primary-color);
            color: var(--white);
            font-weight: bold;
        }
        
        th:hover {
            background-color: var(--primary-hover);
            cursor: pointer;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tr:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }
        
        /* استایل‌های دکمه‌ها */
        .btn {
            transition: var(--transition-normal);
            border-radius: 5px;
            padding: 8px 15px;
            font-weight: 500;
            margin: 0 3px;
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-success:hover {
            background-color: var(--success-hover);
            border-color: var(--success-hover);
            transform: scale(1.05);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .btn-danger:hover {
            background-color: var(--danger-hover);
            border-color: var(--danger-hover);
            transform: scale(1.05);
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
        }
        
        /* استایل‌های باکس جستجو */
        .search-container {
            display: flex;
            align-items: center;
            justify-content: center;
            max-width: 500px;
            margin: 0 auto 20px;
            position: relative;
        }
        
        .search-input {
            width: 100%;
            padding: 12px 20px;
            padding-right: 50px;
            border: 2px solid var(--primary-color);
            border-radius: 30px;
            font-size: 16px;
            font-family: 'Vazir', sans-serif;
            outline: none;
            transition: var(--transition-normal);
            box-shadow: var(--shadow-sm);
        }
        
        .search-input:focus {
            border-color: var(--primary-hover);
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }
        
        .search-button {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            background-color: var(--primary-color);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            color: var(--white);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-normal);
            box-shadow: var(--shadow-sm);
        }
        
        .search-button:hover {
            background-color: var(--primary-hover);
            transform: translateY(-50%) scale(1.1);
        }
        
        .search-button i {
            font-size: 18px;
        }
        
        /* استایل‌های دکمه شناور */
        .floating-button {
            position: fixed;
            bottom: 10vh;
            right: 20px;
            background-color: #FF6F61;
            color: var(--white);
            border: none;
            padding: 0;
            font-size: 24px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            transition: var(--transition-normal);
            z-index: 1000;
        }
        
        .floating-button:hover {
            transform: scale(1.1);
            background-color: #ff5a4c;
        }
        
        .fa-bounce {
            animation: bounce 1s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
        
        /* استایل‌های واکنش‌گرا */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            th, td {
                padding: 8px;
                font-size: 14px;
            }
            
            .btn {
                padding: 6px 12px;
                font-size: 13px;
            }
            
            .search-input {
                padding: 10px 15px;
                padding-right: 40px;
                font-size: 14px;
            }
            
            .search-button {
                width: 35px;
                height: 35px;
            }
            
            .floating-button {
                width: 60px;
                height: 60px;
                font-size: 20px;
            }
        }
        
        /* استایل‌های مربوط به drag & drop */
        .sortable-ghost {
            opacity: 0.4;
            background-color: #c8e6c9 !important;
        }
        
        .sortable-drag {
            background-color: var(--white) !important;
            box-shadow: var(--shadow-md) !important;
        }
        
        .sortable-chosen {
            background-color: #e3f2fd !important;
        }
        
        #sortable-rows tr {
            touch-action: none;
            cursor: move;
            transition: var(--transition-fast);
        }
        
        #sortable-rows tr:active {
            background-color: #e3f2fd;
        }
        
        /* استایل هشدار */
        .alert-warning {
            position: fixed;
            bottom: 10vh;
            right: 20px;
            background-color: rgb(253, 249, 29);
            color: orange;
            border-radius: 50%;
                        width: 70px;
            height: 70px;
            text-align: center;
            line-height: 60px;
            box-shadow: var(--shadow-md);
            transition: var(--transition-normal);
            z-index: 1000;
            display: flex;
            text-decoration: none;
            justify-content: center;
            align-items: center;
            font-size: 200%;
            cursor: pointer;
        }
        
        .fa-shake {
            animation: shake 1s infinite;
        }
        
        @keyframes shake {
            0% { transform: translateX(0); }
            25% { transform: translateX(5px); }
            50% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
            100% { transform: translateX(0); }
        }
    </style>
</head>

<body>
    <div class="l_body">
        <main class="container hidden-scrollbar">
            <div class="container">
                <div class="filter-container">
                    <div class="search-container">
                        <input type="text" id="search" class="search-input" placeholder="جستجو کنید..." />
                        <button class="search-button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table id="contractorsTable" class="table table-striped table-bordered ">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>نام مدیر</th>
                                <th>کارخانه</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-rows" >
                            <?php
                            $sql = "SELECT * FROM employees ORDER BY manager ASC";
                            $result = $conn->query($sql);

                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $a++ . "</td>";
                                    echo "<td>" . htmlspecialchars($row["manager"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["factory"]) . "</td>";
                                    $userID = $row["id"];
                                    $link = "edit.php?user_id=" . htmlspecialchars($userID);
                                    echo "<td class='d-flex justify-content-around'>";
                                    
                                    if(checkEmployerAccessEdit()) {
                                        echo "<a href='{$link}' class='btn btn-success'>ویرایش</a>";
                                    } else {
                                        echo "<i class='fa fa-warning btn btn-warning'></i>";
                                    }
                                    
                                    if(checkEmployerAccessDelete()) {
                                        echo "<button type='button' class='btn btn-danger' onclick='confirmDelete(" . htmlspecialchars($userID) . ")'>حذف</button>";
                                    } else {
                                        echo "<i class='fa fa-warning btn btn-warning'></i>";
                                    }
                                    
                                    echo '</td>';
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center'>کارفرمایی یافت نشد.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <?php if (checkEmployerAccess()) : ?>
            <a href="add_oders_page.php" class="floating-button">
                <i class="fa fa-plus fa-bounce"></i>
            </a>
        <?php else: ?>
            <div class="alert alert-warning text-center">
                <i class="fa fa-lock fa-shake"></i>
            </div>
        <?php endif; ?>

        <?php
        require_once 'require_once/menu.php';
        menus();
        ?>
    </div>

    <!-- اسکریپت‌های جاوااسکریپت -->
    <script>
        // تابع تأیید حذف
        function confirmDelete(id) {
            if (!id) return;
            
            Swal.fire({
                title: 'آیا مطمئن هستید؟',
                text: "این عمل غیرقابل بازگشت است!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'بله، حذف کن!',
                cancelButtonText: 'خیر، نگه‌دار',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "delete.php?user_id=" + encodeURIComponent(id);
                }
            });
        }

        // راه‌اندازی DataTable
        $(document).ready(function() {
            // بررسی وجود جدول قبل از ایجاد DataTable
            if ($.fn.DataTable.isDataTable('#contractorsTable')) {
                $('#contractorsTable').DataTable().destroy();
            }
            
            if ($('#contractorsTable').length > 0) {
                const table = $('#contractorsTable').DataTable({
                    paging: false,
                    scrollY: '444px',
                    scrollCollapse: true,
                    dom: 't',
                    language: {
                        search: "جستجو:",
                        zeroRecords: "هیچ رکوردی یافت نشد.",
                        info: "نمایش _START_ تا _END_ از _TOTAL_ رکورد",
                        infoEmpty: "هیچ رکوردی موجود نیست",
                        infoFiltered: "(فیلتر شده از _MAX_ رکورد)",
                        paginate: {
                            first: "اولین",
                            last: "آخرین",
                            next: "بعدی",
                            previous: "قبلی"
                        }
                    }
                });

                // جستجو در جدول با استفاده از باکس جستجوی سفارشی
                $('#search').on('keyup', function() {
                    const searchValue = $(this).val().trim();
                    table.search(searchValue).draw();

                    // نمایش پیام اگر نتیجه‌ای یافت نشد و مقدار جستجو خالی نباشد
                    if (searchValue !== '' && table.rows({ search: 'applied' }).count() === 0) {
                        Swal.fire({
                            icon: 'info',
                            title: 'یافت نشد!',
                            text: 'هیچ رکوردی با این مشخصات یافت نشد.',
                            confirmButtonText: 'باشه',
                            timer: 2000,
                            timerProgressBar: true
                        });
                    }
                });

                // اضافه کردن قابلیت مرتب‌سازی با کشیدن و رها کردن
                // if ($('#sortable-rows').length > 0) {
                //     new Sortable(document.getElementById('sortable-rows'), {
                //         animation: 150,
                //         ghostClass: 'sortable-ghost',
                //         chosenClass: 'sortable-chosen',
                //         dragClass: 'sortable-drag',
                //         onEnd: function(evt) {
                //             // می‌توانید اینجا کد مربوط به ذخیره ترتیب جدید را اضافه کنید
                //             console.log('Row reordered:', evt.oldIndex, evt.newIndex);
                //         }
                //     });
                // }
            }

            // اضافه کردن افکت‌های بصری به دکمه‌ها
            $('.btn').hover(
                function() { $(this).addClass('shadow-sm'); },
                function() { $(this).removeClass('shadow-sm'); }
            );

            // نمایش پیام خوش‌آمدگویی
            // setTimeout(function() {
            //     const userName = "<?php echo isset($_SESSION['user_data']['name']) ? $_SESSION['user_data']['name'] : 'کاربر'; ?>";
            //     Swal.fire({
            //         title: `${userName} عزیز، خوش آمدید!`,
            //         text: 'به سیستم مدیریت کارفرمایان خوش آمدید.',
            //         icon: 'success',
            //         timer: 2000,
            //         timerProgressBar: true,
            //         showConfirmButton: false
            //     });
            // }, 1000);
        });

        // تابع نمایش تصویر پیش‌نمایش
        function showMyImage(fileInput) {
            const thumbnil = document.getElementById('thumbnil');
            if (!thumbnil) return;
            
            const files = fileInput.files;
            if (files && files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    thumbnil.src = e.target.result;
                    thumbnil.style.display = 'block';
                }
                reader.readAsDataURL(files[0]);
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>

