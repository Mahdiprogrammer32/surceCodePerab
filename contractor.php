<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'require_once/loading.php';
require_once "database.php";
global $conn;

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
    die("Connection failed: " . htmlspecialchars($e->getMessage()));
}

// بررسی وجود کوکی و دسترسی کاربر
if (!isset($_COOKIE['userID'])) {
    header("Location: login.php");
    exit();
}

// دریافت اطلاعات کاربر
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
$stmt->execute([$_COOKIE['userID']]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: login.php");
    exit();
}

// ذخیره اطلاعات کاربر در سشن
$_SESSION['user_data'] = [
    'userID' => $user['id'],
    'name' => $user['name'],
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

// توابع بررسی دسترسی
function checkContractorAccess() {
    return isset($_SESSION['user_data']['access_add_contractor']) && $_SESSION['user_data']['access_add_contractor'] == 1;
}
function checkContractorAccessEdit() {
    return isset($_SESSION['user_data']['access_edit_contractor']) && $_SESSION['user_data']['access_edit_contractor'] == 1;
}
function checkContractorAccessDelete() {
    return isset($_SESSION['user_data']['access_delete_contractor']) && $_SESSION['user_data']['access_delete_contractor'] == 1;
}
?>

    <!DOCTYPE html>
    <html lang="fa" dir="rtl">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>مدیریت پیمانکاران</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Vazir:wght@400;700&display=swap" rel="stylesheet">
        <link href="fontA/css/all.min.css" rel="stylesheet">
        <style>
            :root {
                --primary-color: #00ffaa;
                --secondary-color: #0066ff;
                --accent-color: #ff00cc;
                --background-dark: #0a0a14;
                --background-light: #2a2a3e;
                --text-color: #ffffff;
                --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.2);
                --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.3);
                --border-radius: 12px;
                --transition: all 0.3s ease;
            }

            a{
                text-decoration: none;
            }

            body {
                background: linear-gradient(135deg, var(--background-dark), var(--background-light));
                font-family: 'Vazir', sans-serif;
                color: var(--text-color);
                margin: 0;
                padding: 0;
                overflow-x: hidden;
            }

            .cyber-container {
                background: rgba(20, 20, 30, 0.9);
                border-radius: var(--border-radius);
                box-shadow: var(--shadow-md);
                padding: 30px;
                margin: 30px auto;
                max-width: 1200px;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(0, 255, 170, 0.2);
            }

            .cyber-title {
                font-size: 2.5rem;
                font-weight: 700;
                text-align: center;
                margin-bottom: 30px;
                color: var(--text-color);
                text-shadow: 0 0 5px var(--primary-color);
            }

            .search-container {
                display: flex;
                align-items: center;
                max-width: 500px;
                margin: 0 auto 20px;
                position: relative;
            }

            .search-input {
                width: 100%;
                padding: 12px 50px 12px 20px;
                border: 2px solid var(--primary-color);
                border-radius: 30px;
                background: rgba(30, 30, 40, 0.7);
                color: var(--text-color);
                font-size: 16px;
                outline: none;
                transition: var(--transition);
            }

            .search-input:focus {
                border-color: var(--secondary-color);
                box-shadow: 0 0 15px rgba(0, 102, 255, 0.3);
            }

            .search-button {
                position: absolute;
                left: 10px;
                top: 50%;
                transform: translateY(-50%);
                background: var(--primary-color);
                border: none;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                color: var(--text-color);
                display: flex;
                align-items: center;
                justify-content: center;
                transition: var(--transition);
            }

            .search-button:hover {
                background: var(--secondary-color);
                transform: translateY(-50%) scale(1.1);
            }

            .cyber-table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0;
                background: rgba(20, 20, 30, 0.7);
                border-radius: var(--border-radius);
                overflow: hidden;
                box-shadow: var(--shadow-sm);
            }

            .cyber-table th, .cyber-table td {
                padding: 15px;
                text-align: center;
                border-bottom: 1px solid rgba(0, 255, 170, 0.2);
                transition: var(--transition);
            }

            .cyber-table th {
                background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
                color: var(--text-color);
                font-weight: 700;
                text-shadow: 0 0 5px rgba(0, 255, 170, 0.5);
            }

            .cyber-table tr:hover td {
                background: rgba(0, 255, 170, 0.1);
            }

            .cyber-btn {
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
                color: var(--text-color);
                font-weight: 600;
                transition: var(--transition);
                margin: 0 5px;
            }

            .cyber-btn:hover {
                transform: translateY(-3px);
                box-shadow: var(--shadow-md);
            }

            .cyber-btn-danger {
                background: linear-gradient(45deg, var(--accent-color), #ff1493);
            }

            .cyber-btn-warning {
                background: linear-gradient(45deg, #ffc107, #ff9800);
            }

            .floating-button {
                position: fixed;
                bottom: 80px;
                right: 20px;
                background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
                color: var(--text-color);
                border: none;
                border-radius: 50%;
                width: 60px;
                height: 60px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: var(--shadow-md);
                transition: var(--transition);
                z-index: 1000;
            }

            .floating-button:hover {
                transform: scale(1.1);
                background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
            }

            .alert-warning {
                position: fixed;
                bottom: 80px;
                right: 20px;
                background: linear-gradient(45deg, #ffc107, #ff9800);
                color: var(--text-color);
                border-radius: 50%;
                width: 60px;
                height: 60px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: var(--shadow-md);
                transition: var(--transition);
                z-index: 1000;
            }

            /* ریسپانسیو */
            @media (max-width: 768px) {
                .cyber-container {
                    padding: 20px;
                    margin: 20px;
                }

                .cyber-title {
                    font-size: 1.8rem;
                }

                .cyber-table th, .cyber-table td {
                    padding: 10px;
                    font-size: 14px;
                }

                .cyber-btn {
                    padding: 8px 15px;
                    font-size: 14px;
                }

                .floating-button, .alert-warning {
                    width: 50px;
                    height: 50px;
                    font-size: 18px;
                }
            }

            @media (max-width: 576px) {
                .cyber-title {
                    font-size: 1.5rem;
                }

                .search-input {
                    font-size: 14px;
                    padding: 10px 40px 10px 15px;
                }

                .search-button {
                    width: 35px;
                    height: 35px;
                }
            }

            /* دسترسی‌پذیری */
            .cyber-btn:focus, .search-button:focus {
                outline: 2px solid var(--accent-color);
                outline-offset: 2px;
            }

            /* Drag and Drop */
            .sortable-ghost {
                opacity: 0.5;
                background: rgba(0, 255, 170, 0.3) !important;
            }

            .sortable-drag {
                background: rgba(20, 20, 30, 0.9) !important;
                box-shadow: var(--shadow-md) !important;
            }

            /* انیمیشن‌ها */
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }

            .pulse {
                animation: pulse 2s infinite;
            }
        </style>
    </head>
    <body>
    <div class="l_body">
        <main class="cyber-container">
            <h1 class="cyber-title">مدیریت پیمانکاران</h1>
            <div class="search-container">
                <input type="text" id="search" class="search-input" placeholder="جستجوی نام پیمانکار..." aria-label="جستجوی پیمانکار">
                <button class="search-button" aria-label="جستجو">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <table id="contractorsTable" class="cyber-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>نام</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody id="sortable-rows">
                <?php
                $sql = "SELECT * FROM users WHERE form = 'پیمانکاران' AND is_active = 1 ORDER BY name ASC";
                $result = $conn->query($sql);
                $index = 1;

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $address = $row["address"] ?? '';
                        echo "<tr data-id='{$row['id']}' role='row'>";
                        echo "<td>$index</td>";
                        echo "<td>" . htmlspecialchars($row["name"] . ' ' . ($row['family'] ?? '')) . "</td>";
                        echo "<td class='d-flex justify-content-around'>";
                        if (checkContractorAccessEdit()) {
                            echo "<a href='edit_contractor.php?user_id={$row['id']}' class='cyber-btn' role='button' aria-label='ویرایش پیمانکار'>ویرایش</a>";
                        } else {
                            echo "<button class='cyber-btn cyber-btn-warning' disabled aria-label='عدم دسترسی به ویرایش'><i class='fas fa-lock'></i></button>";
                        }
                        echo "<a href='https://kalahabama.ir/show_license.php?userId={$row['id']}' class='cyber-btn' role='button' aria-label='مشاهده لایسنس'><i class='fas fa-copy'></i></a>";
                        if (checkContractorAccessDelete()) {
                            echo "<button class='cyber-btn cyber-btn-danger' onclick='confirmDelete({$row['id']})' role='button' aria-label='حذف پیمانکار'>حذف</button>";
                        } else {
                            echo "<button class='cyber-btn cyber-btn-warning' disabled aria-label='عدم دسترسی به حذف'><i class='fas fa-lock'></i></button>";
                        }
                        echo "</td>";
                        echo "</tr>";
                        $index++;
                    }
                } else {
                    echo "<tr><td colspan='3'>پیمانکاری یافت نشد.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </main>

        <?php if (checkContractorAccess()): ?>
            <a href="add_contractor_page.php" class="floating-button pulse" aria-label="افزودن پیمانکار جدید">
                <i class="fas fa-plus"></i>
            </a>
        <?php else: ?>
            <div class="alert-warning pulse" role="alert" aria-label="عدم دسترسی به افزودن پیمانکار">
                <i class="fas fa-lock"></i>
            </div>
        <?php endif; ?>

        <?php
        require_once 'require_once/menu.php';
        menus();
        ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <script>
        $(document).ready(function() {
            // راه‌اندازی DataTable
            const table = $('#contractorsTable').DataTable({
                paging: true,
                pageLength: 10,
                scrollY: '400px',
                scrollCollapse: true,
                dom: 'rtip', // حذف فیلد جستجوی پیش‌فرض DataTable
                language: {
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


            // جستجوی پیشرفته
            $('#search').on('keyup', function() {
                const searchValue = $(this).val().trim();
                table.search(searchValue).draw();
                if (searchValue && table.rows({ search: 'applied' }).count() === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'یافت نشد!',
                        text: 'هیچ پیمانکاری با این مشخصات یافت نشد.';
                        timer: 1000,
                        timerProgressBar: true
                    });
                }
            });

            // Drag and Drop با Sortable.js
            const sortableTable = document.getElementById('sortable-rows');
            if (sortableTable) {
                new Sortable(sortableTable, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    onEnd: function(evt) {
                        const rows = sortableTable.querySelectorAll('tr');
                        const order = Array.from(rows).map(row => row.dataset.id);
                        $.ajax({
                            url: 'update_order.php',
                            method: 'POST',
                            data: { order: order },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'ترتیب به‌روزرسانی شد!',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'خطا!',
                                    text: 'خطا در به‌روزرسانی ترتیب.'
                                });
                            }
                        });
                    }
                });
            }

            // افکت‌های بصری
            $('.cyber-btn').hover(
                function() { $(this).css('box-shadow', '0 0 15px rgba(0, 255, 170, 0.5)'); },
                function() { $(this).css('box-shadow', 'var(--shadow-md)'); }
            );
        });

        // تابع تأیید حذف
        function confirmDelete(id) {
            if (!id) return;
            Swal.fire({
                title: 'آیا مطمئن هستید؟',
                text: 'این عمل غیرقابل بازگشت است!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'بله، حذف کن!',
                cancelButtonText: 'خیر، نگه‌دار',
                confirmButtonColor: '#ff00cc',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `delete_contractor.php?user_id=${id}`;
                }
            });
        }
    </script>
    </body>
    </html>

<?php
$conn->close();
ob_end_flush();
?>