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
$db = 'departem_test';
$user = 'departem_kakang';
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
        'access_edit_theme' => $row['access_edit_theme'],
        'access_view_image' => $row['access_view_image'],
        'access_add_image' => $row['access_add_image'],
        'access_edit_image' => $row['access_edit_image'],
        'access_delete_image' => $row['access_delete_image']
    ];
} else {
    header("Location: login.php");
    exit();
}

// بررسی دسترسی کاربر برای اضافه کردن پیمانکار
function checkContractorAccess()
{
    return isset($_SESSION['user_data']['access_add_contractor']) && $_SESSION['user_data']['access_add_contractor'] == 1;
}

function checkContractorAccessEdit()
{
    return isset($_SESSION['user_data']['access_edit_contractor']) && $_SESSION['user_data']['access_edit_contractor'] == 1;
}

function checkContractorAccessDelete()
{
    return isset($_SESSION['user_data']['access_delete_contractor']) && $_SESSION['user_data']['access_delete_contractor'] == 1;
}

$a = 1;
?>


<!DOCTYPE html>
<html lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>پیمانکاران </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <link href="fontA/css/all.min.css" rel="stylesheet">


<style>
        .floating-button {
        position: fixed;
        bottom: 9vh;
        right: 20px;
            background-color: #007bff;
            color: white;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            text-align: center;
            line-height: 60px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s, transform 0.3s;
            z-index: 1000;
            display: flex;
            text-decoration: none;
            justify-content: center;
            align-items: center;
            font-size: 200%;
        }
        .floating-button:hover {
            background-color: #0056b3;
            transform: scale(1.1);
        }
        .btn-bounce {
            animation: bounce 1s infinite;
        }
    
        th {
            text-align: center;
        }
        body {
            background-color: #f0f2f5;
            font-family: 'Gulzar', serif;
            margin: 0;
            padding: 0;
        }
        h1 {
            color: #007bff;
            margin-bottom: 30px;
            text-align: center;
            font-size: 2.5em;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.1);
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .container:hover {
            transform: scale(1.02);
        }
        table {
            margin-top: 20px;
            border-collapse: collapse;
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #dee2e6;
            transition: background-color 0.3s;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        th:hover {
            background-color: #0056b3;
            cursor: pointer;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }
        .btn {
            transition: background-color 0.3s, transform 0.3s;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
            transform: scale(1.05);
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            transform: scale(1.05);
        }
        .modal-header {
            background-color: #007bff;
            color: white;
            border-bottom: none;
        }
        .modal-body {
            padding: 20px;
        }
        .form-label {
            font-weight: bold;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .alert {
            position: fixed;
            bottom: 1px;
            right: 20px;
            background-color: rgb(253, 249, 29);
            color: orange;
            border-radius: 50%;
            width: 70px;
            height: 70px;
            text-align: center;
            line-height: 60px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s, transform 0.3s;
            z-index: 1000;
            display: flex;
            text-decoration: none;
            justify-content: center;
            align-items: center;
            font-size: 200%;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            th, td {
                /* کاهش اندازه فونت در صفحه‌های کوچک */
            }
        }
    </style>
	
	<style>
				 /* استایل‌های کلی باکس جستجو */
.search-container {
    display: flex;
    align-items: center;
    justify-content: center;
    max-width: 500px;
    margin: 20px auto;
    position: relative;
}

/* استایل‌های فیلد جستجو */
.search-input {
    width: 100%;
    padding: 12px 20px;
    padding-right: 50px; /* فاصله برای آیکون جستجو */
    border: 2px solid #007bff;
    border-radius: 30px;
    font-size: 16px;
    font-family: 'Vazir', sans-serif;
    outline: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.search-input:focus {
    border-color: #0056b3;
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
}

/* استایل‌های دکمه جستجو */
.search-button {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    background-color: #007bff;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.search-button:hover {
    background-color: #0056b3;
    transform: translateY(-50%) scale(1.1);
}

.search-button i {
    font-size: 18px;
}

/* استایل‌های نمایش تعداد نتایج */
#search-results-count {
    font-size: 14px;
    color: #007bff;
    font-weight: bold;
    margin-top: 10px;
    text-align: center;
    transition: color 0.3s ease;
}

#search-results-count.found {
    color: #28a745; /* رنگ سبز برای حالت یافت‌شده */
}

#search-results-count.not-found {
    color: #dc3545; /* رنگ قرمز برای حالت یافت‌نشده */
}

/* استایل‌های ردیف‌های یافت‌شده */
tr.found {
    background-color: #e3f2fd !important;
    transition: background-color 0.3s ease;
}

/* استایل‌های واکنش‌گرا */
@media (max-width: 768px) {
    .search-input {
        padding: 10px 15px;
        padding-right: 40px;
        font-size: 14px;
    }

    .search-button {
        width: 35px;
        height: 35px;
    }

    .search-button i {
        font-size: 16px;
    }

    #search-results-count {
        font-size: 12px;
    }
}







/* دکمه شناور برای افزودن پروژه */
.add_project_btn {
    position: fixed;
    bottom: 15vh;
    right: 1vw;
    background-color: #28a745;
    border-radius: 50%;
    width: 70px;
    height: 70px;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
    font-size: 2em;
    transition: background-color 0.3s ease;
	
}

.add_project_btn:hover {
    background-color: #218838;
}

.add_project_btn i {
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
	</style>
</head>

<body dir="rtl">
    <div class="l_body">

        <?php


        ?>



        <main class="container hidden-scrollbar">

            <div class="container">
                <!-- <h1 class="text-center">جدول پيمانکاران </h1> -->
                <div class="filter-container">
                    <div class="input-group">
                        <div class="input-group-append">
																<!-- <div class="search-container">
    <input type="text" id="search" class="search-input" placeholder="جستجو کنید..." />
    <button class="search-button">
        <i class="fas fa-search"></i>
    </button>
</div> -->
                        </div>
                    </div>
                </div>
                <table id="contractorsTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                  <th>نام</th>
                      

                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody id="sortable-rows">
                        <?php

                        // پرس و جو برای دریافت همه کارمندان
                        $sql = "SELECT * FROM users WHERE  form = 'پیمانکاران' ORDER BY name ASC";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $address = $row["address"] ?? ''; // اگر $row["address"] NULL بود، مقدار پیش‌فرض خالی اختصاص دهید.
                            

                                echo "<tr data-id='" . $row['id'] . "'>";
                                echo "<td>" . $a++ . "</td>";
                                echo "<td>" . $row["name"].$row['family'] . "</td>";
                                $userkID = $row["id"];
                                $link = "edit_contractor.php?user_id=$userkID";
                                $delete = "delete_contractor.php?user_id='$userkID'";
                                echo "<td class='d-flex justify-content-around'>";
                        ?>
                                <?php if (checkContractorAccessEdit()): ?>
                                    <a href='<?php echo  $link ?>' class='btn btn-success'>ویرایش</a>
                                <?php else: ?>
                                    <i class="fa fa-warning btn btn-warning"></i>

                                <?php endif; ?>

                                <?php
                                global $userID;
echo '<a href="https://kalahabama.ir/webshop/show_license.php?userId=' . $row['id'] . '" class="btn btn-secondary"><i class="fa fa-copy bg-secondary text-white"> </i></a>';
?>

<?php if (checkContractorAccessDelete()): ?> 
     <button type=" button" class="btn btn-danger" onclick="confirmDelete(' <?php echo $userkID ?>  ')">حذف</button>
                                <?php else: ?>
                                    <i class="fa fa-warning btn btn-warning"></i>

                                <?php endif; ?>

                        <?php
                                echo '</td>';
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>کارمندی یافت نشد.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>


            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                async function confirmDelete(id) {
                    const result = await Swal.fire({
                        title: 'آیا مطمئن هستید؟',
                        text: "این عمل غیرقابل بازگشت است!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'بله، حذف کن!',
                        cancelButtonText: 'خیر، نگه‌دار'
                    });

                    if (result.isConfirmed) {
                        // هدایت به delete.php با پارامتر user_id
                        window.location.href = "delete_contractor.php?user_id=" + id;
                    }
                }
            </script>


    </div>





    </main>


    <footer style="width: 100%; height: fit-content; ">

    </footer>
    <?php if (checkContractorAccess()) : ?>
        <!-- دکمه آبی ثابت با آیکون -->
        <a href="add_contractor_page.php" class="bg-danger btn btn-danger  fa-bounce" style=" position: fixed;
    bottom: 10vh;
    right: 20px;
    background:#FF6F61;
    color: white;
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
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2),
                0 6px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: all 0.5s ease;
    z-index: 1000; " >
            <i class="fa fa-plus"></i>
        </a>

    <?php else: ?>
        <div class="alert alert-warning text-center"><i class="fa fa-lock"></i></div>

    <?php endif; ?>



    </div>




    <!--MODALS-->



<?php
require_once 'require_once/menu.php';
menus();

?>


















    <!-- اسکریپت های DataTable -->
    <script>
$(document).ready(function() {
    const table = $('#contractorsTable').DataTable({
        paging: false, // غیرفعال کردن Pagination
        scrollY: '400px', // ایجاد اسکرول عمودی با ارتفاع 400px
        scrollCollapse: true, // جمع‌شدن اسکرول در صورت کم بودن ردیف‌ها
        dom: 't', // فقط جدول را نمایش بده (جستجو و دیگر عناصر حذف می‌شوند)
        language: {
            // ترجمه پیام‌های DataTables به فارسی
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
        const searchValue = this.value.trim(); // حذف فاصله‌های اضافی
        table.search(searchValue).draw();

        // نمایش پیام اگر نتیجه‌ای یافت نشد
        if (table.rows({ search: 'applied' }).count() === 0) {
            Swal.fire({
                icon: 'info',
                title: 'یافت نشد!',
                text: 'هیچ رکوردی با این مشخصات یافت نشد.',
                confirmButtonText: 'باشه',
                timer: 2000 // بسته شدن خودکار پس از 2 ثانیه
            });
        }
    });
});
    </script>
    </div>


   
    <script>

        
        document.addEventListener('DOMContentLoaded', function() {
            var el = document.getElementById('sortable-rows');

            var sortable = new Sortable(el, {
                animation: 150,
                delay: 30, // تاخیر 300 میلی‌ثانیه برای شروع drag
                delayOnTouchOnly: true, // فقط در دستگاه‌های لمسی تاخیر داشته باشد
                direction: 'vertical',
                touchStartThreshold: 5,

                // استایل‌های مربوط به drag
                dragClass: "sortable-drag",
                ghostClass: "sortable-ghost",
                chosenClass: "sortable-chosen",

                // غیرفعال کردن scroll خودکار
                forceFallback: true,
                fallbackTolerance: 1,

                onStart: function(evt) {
                    const row = evt.item;
                    row.style.backgroundColor = '#e3f2fd';
                    // ویبره در صورت پشتیبانی
                    if (window.navigator && window.navigator.vibrate) {
                        navigator.vibrate(50);
                    }
                },

                onEnd: function(evt) {
                    const row = evt.item;
                    row.style.backgroundColor = '';
                    updateRowNumbers();

                    // ویبره در صورت پشتیبانی
                    if (window.navigator && window.navigator.vibrate) {
                        navigator.vibrate([50, 50]);
                    }

                    let rows = Array.from(el.getElementsByTagName('tr'));
                    let newOrder = rows.map(row => row.getAttribute('data-id'));
                    console.log('New order:', newOrder);
                }
            });

            function updateRowNumbers() {
                let rows = document.querySelectorAll('#sortable-rows tr');
                rows.forEach((row, index) => {
                    let firstCell = row.cells[0];
                    firstCell.textContent = index + 1;
                });
            }
        });
    </script>

    <style>
        /* استایل‌های مربوط به drag & drop */
        .sortable-ghost {
            opacity: 0.4;
            background-color: #c8e6c9 !important;
        }

        .sortable-drag {
            background-color: #fff !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2) !important;
        }

        .sortable-chosen {
            background-color: #e3f2fd !important;
        }

        #sortable-rows tr {
            touch-action: none;
            /* مهم برای عملکرد در موبایل */
            cursor: move;
            transition: all 0.2s ease;
        }

        #sortable-rows tr:active {
            background-color: #e3f2fd;
        }

        @media (max-width: 768px) {
            #sortable-rows tr {
                user-select: none;
                -webkit-user-select: none;
            }
        }
    </style>

<script type="text/javascript">
        $(document).ready(function() {
            var x = $("#timer");
            var d = new Date();

            // آرایه‌ای از نام‌های روزهای هفته به فارسی
            var daysOfWeek = ["شنبه", "یکشنبه", "دوشنبه", "سه‌شنبه", "چهارشنبه", "پنجشنبه", "جمعه"];

            // به‌دست آوردن شماره روز هفته (0 = شنبه، 1 = یکشنبه، ...)
            var dayIndex = (d.getDay() + 1) % 7; // یک واحد اضافه می‌کنیم و اگر به 7 برسد، دوباره به 0 برمی‌گردد.

            // دریافت نام روز هفته به فارسی
            var currentDay = daysOfWeek[dayIndex];

            // تنظیم محتوای HTML برای نمایش روز هفته به فارسی به همراه تاریخ
            x.html('<p class="date text-light text-center">' + currentDay + ' - ' + d.toLocaleDateString('fa-IR') + '</p>');

            console.log(d);
        });
    </script>




    <script>
        const clock = document.querySelector('.clock');

        const tik = () => {
            const now = new Date();
            const h = now.getHours();
            const m = now.getMinutes();
            const s = now.getSeconds();

            const html = `
                <span>${s}</span>:
                <span>${m}</span> :
                <span>${h}</span>
  `;

            clock.innerHTML = html;

        };

        setInterval(tik, 1000);




        function showMyImage(fileInput) {
            var files = fileInput.files;
            if (files && files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('thumbnil').src = e.target.result;
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