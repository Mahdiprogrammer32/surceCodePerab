<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<?php
// اتصال به پایگاه داده
require_once "database.php";
require_once "require_once/header.php";
require_once "require_once/swiper.php";
require_once "require_once/menu.php";
global $conn;
error_reporting(E_ALL);
ini_set('display_errors', 1);
$a = 1; // شمارش پروژه‌ها

$progress = 50;

$cssLinks = [
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'b_icons/font/bootstrap-icons.min.css',
    'fontA/css/all.min.css',
    'assets/css/checkout.css',
    'assets/css/style_project.css',
    'assets/css/jquery.dataTables.min.css',
    'https://unpkg.com/swiper/swiper-bundle.min.css',
    'https://unpkg.com/ag-grid-community/styles/ag-grid.css',
    'https://unpkg.com/ag-grid-community/styles/ag-theme-alpine.css'
];
$jsLinks = [
    'fontA/js/all.min.js',
    'https://code.jquery.com/jquery-3.6.0.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
    'https://unpkg.com/swiper/swiper-bundle.min.js',
    'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',
    'https://code.highcharts.com/highcharts.js',
    'https://code.highcharts.com/highcharts-3d.js',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11'
];


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['subAdd'])) {
    // دریافت اطلاعات از فرم
    $role_employer = $_POST['role_employer'];
    $role_contractor = $_POST['role_contractor'];
    $subcontractor1 = $_POST['subcontractor1'];
    $subcontractor2 = $_POST['subcontractor2'];
    $subcontractor3 = $_POST['subcontractor3'];
    $subcontractor4 = $_POST['subcontractor4'];
    $subcontractor5 = $_POST['subcontractor5'];
    $phase_desc = $_POST['phase_desc'];
    $budget = $_POST['budget'];

    // اعتبارسنجی داده‌ها
    if (empty($role_employer) || empty($role_contractor) || empty($phase_desc) || empty($budget)) {
        echo '<script>
                Swal.fire({
                    icon: "warning",
                    title: "هشدار!",
                    text: "لطفاً همه فیلدها را پر کنید."
                });
              </script>';
    } else {
        // آماده‌سازی و اجرای دستور SQL برای درج داده‌ها
        $sql = "INSERT INTO projects (employer, contractor, subcontractor1, subcontractor2, subcontractor3, subcontractor4, subcontractor5, phase_description, budget) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iissssisi", $role_employer, $role_contractor, $subcontractor1, $subcontractor2, $subcontractor3, $subcontractor4, $subcontractor5, $phase_desc, $budget);

            if ($stmt->execute()) {
                echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "موفقیت آمیز!",
                            text: "پروژه جدید با موفقیت ثبت شد."
                        }).then(() => {
                            setTimeout(() => {
                                window.location.href = "projects.php";
                            }, 1000);
                        });
                      </script>';
            } else {
                echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "خطا!",
                            text: "خطایی در ثبت اطلاعات رخ داده است: ' . htmlspecialchars($stmt->error) . '"
                        });
                      </script>';
            }

            $stmt->close();
        } else {
            echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "خطا!",
                        text: "خطا در آماده‌سازی دستور: ' . htmlspecialchars($conn->error) . '"
                    });
                  </script>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects - دپارتمان برق</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <?php
    foreach ($cssLinks as $Link) {
        echo '<link rel="stylesheet" href="' . $Link . '">';
    }


    //    styles();
    ?>
    <style>
        body {
            background-color: #f0f4f8;
            font-family: 'Arial', sans-serif;
            overflow-x: hidden;
            /* جلوگیری از اسکرول افقی */
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .card {
            border-radius: 15px;
            margin-bottom: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            background-color: white;
            /* اضافه کردن پس‌زمینه سفید */
            flex: 100 0 calc(100% - 10px);
            /* تنظیم اندازه کادرها برای دستگاه‌های بزرگ */
            max-width: 100%;
            /* حداکثر عرض کادرها برای دستگاه‌های بزرگ */
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 123, 255, 0.2);
        }

        .circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            position: absolute;
            top: 15px;
            right: 15px;
        }

        .price {
            font-size: 1.2em;
            font-weight: bold;
            color: #28a745;
        }

        .modal-header {
            background-color: #007bff;
            color: white;
        }

        .search-bar {
            margin-bottom: 20px;
        }

        .filter-container {
            margin-bottom: 20px;
        }

        .card-title {
            color: #007bff;
        }

        .progress {
            height: 20px;
        }

        .progress-bar {
            background: linear-gradient(90deg,
            #00ff15 0%,
            #aaff00 50%,
            #ffff00 60%,
            #ff7f00 100%,
            #ff5500 80%,
            #ff0000 90%);
        }

        .floating-button {
            position: fixed;
            bottom: 60px;
            right: 1px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            font-size: 24px;
            transition: background-color 0.3s;
        }

        .floating-button:hover {
            background-color: #0056b3;
        }

        / /* Grid layout for project cards */
        .project-grid {
            display: flex;
            /* Keep flex layout */
            flex-wrap: wrap;
            /* Allow cards to wrap */
            justify-content: space-between;
            /* Distribute cards evenly */
            gap: 20px;
            /* Space between cards */
            padding-bottom: 50px;
        }


        /* Style for the filter select */
        .form-select {
            border-radius: 20px;
            border: 1px solid #007bff;
        }

        .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        .icons .addP {
            color: turquoise;
        }

        .icons .anbar_A {
            color: firebrick;
        }

        .icons .nazer_A {
            color: red;
        }


        .icons .hesab_A {
            color: #28a745;
        }


        /* Ensure cards stack vertically on mobile */
        @media (max-width: 768px) {
            .card {

                max-width: 100%;
                /* حداکثر عرض کادرها */
            }

            .project-grid {
                justify-content: center;
                /* کادرها را در وسط بچینید */
                padding-bottom: 40px;
            }
        }
    </style>
</head>

<body dir="rtl">
<?php
headers();
menus();
?>
<div class="container mt-4">
    <h1 class="header">لیست پروژه‌ها - دپارتمان برق</h1>

    <!-- نوار جستجو -->
    <div class="row search-bar">
        <div class="col-md-6 offset-md-3">
            <input type="text" class="form-control" placeholder="جستجوی پروژه..." id="searchInput">
        </div>
    </div>

    <!-- فیلترها -->
    <div class="row filter-container">
        <div class="col-md-4 col-sm-4">
            <select class="form-select" id="statusFilter">
                <option value="">وضعیت پروژه</option>
                <option value="completed">پروژه‌های کامل شده</option>
                <option value="in-progress">پروژه‌های در حال انجام</option>
            </select>
        </div>
        <div class="col-md-4 col-sm-4">
            <select class="form-select" id="contractorFilter">
                <option value="">پیمانکار</option>
                <?php
                // Fetch contractors for filter
                $contractors = $conn->query("SELECT id, name, family FROM contractor");
                while ($contractor = $contractors->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($contractor['id']) . '">' . htmlspecialchars($contractor['name'] . ' ' . $contractor['family']) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="col-md-4 col-sm-4">
            <select class="form-select" id="sortFilter">
                <option value="">مرتب‌سازی بر اساس</option>
                <option value="name">نام پروژه</option>
                <option value="budget">بودجه</option>
                <option value="progress">پیشرفت</option>
            </select>
        </div>
    </div>

    <div class="row project-grid" id="projectList">
        <!-- Dynamic Card Generation -->
        <?php
        $sql = "SELECT * FROM projects";
        $resultProjects = $conn->query($sql);

        if ($resultProjects->num_rows > 0) {
            while ($row = $resultProjects->fetch_assoc()) {
                $contractorId = $row['contractor'];
                $employerId = $row['employer'];

                $tabel_contractor = "SELECT name, family FROM contractor WHERE id = ?";
                $stmt = $conn->prepare($tabel_contractor);
                $stmt->bind_param("i", $contractorId);
                $stmt->execute();
                $resultContractor = $stmt->get_result()->fetch_assoc();

                // Fetch employer information
                $tabel_employer = "SELECT manager FROM employees WHERE id = ?";
                $stmtEmployer = $conn->prepare($tabel_employer);
                $stmtEmployer->bind_param("i", $employerId);
                $stmtEmployer->execute();
                $resultEmployer = $stmtEmployer->get_result()->fetch_assoc();


                echo '<div class="col-12 col-md-6 col-lg-4 mb-4">';
                echo '<div class="card shadow-lg border-primary" style="height: 100%;" data-contractor-id="' . htmlspecialchars($contractorId) . '" data-status="' . htmlspecialchars($row['status']) . '" data-budget="' . htmlspecialchars($row['budget']) . '" data-progress="' . htmlspecialchars($row['progress']) . '">';
                echo '<div class="card-body">';
                echo '<h6 class="card-subtitle mb-2 text-muted">کارفرما: ' . htmlspecialchars($resultEmployer['manager']) . '<span class="text-danger"> | </span>' . '<span class="text-primary">' . htmlspecialchars($row['phase_description']) . '</span>' . '</h6>';
                echo '<h6 class="card-subtitle mb-2 text-muted">پیمانکار: ' . htmlspecialchars($resultContractor['name']) . ' ' . htmlspecialchars($resultContractor['family']) . '</h6>';
                echo '<button class="btn btn-warning text-danger font-weight-bold text-center m-1 show-details" 
    data-bs-toggle="modal" 
    data-bs-target="#jozeat" 
    data-employer="' . htmlspecialchars($resultEmployer['manager'] ?? '') . '" 
    data-contractor="' . htmlspecialchars($resultContractor['name'] . ' ' . $resultContractor['family'] ?? '') . '" 
    data-phase="' . htmlspecialchars($row['phase_description'] ?? '') . '" 
    data-budget="' . htmlspecialchars($row['budget'] ?? '') . '" 
    data-progress="' . htmlspecialchars($row['progress'] ?? '') . '" 
    data-subcontractor1="' . htmlspecialchars($row['subcontractor1'] ?? '') . '" 
    data-subcontractor2="' . htmlspecialchars($row['subcontractor2'] ?? '') . '" 
    data-subcontractor3="' . htmlspecialchars($row['subcontractor3'] ?? '') . '" 
    data-subcontractor4="' . htmlspecialchars($row['subcontractor4'] ?? '') . '" 
    data-subcontractor5="' . htmlspecialchars($row['subcontractor5'] ?? '') . '">';
                echo '<span class="fa fa-circle-question"></span> جزئیات';
                echo '</button>';

                echo '<div class="circle">' . $a++ . '</div>';
                echo '<div class="progress mb-2"> ';
                echo '<div class="progress-bar" role="progressbar" style="width: ' . htmlspecialchars($row['progress']) . '%;" aria-valuenow="' . htmlspecialchars($row['progress']) . '" aria-valuemin="0" aria-valuemax="100">' . htmlspecialchars($row['progress']) . '%</div>';
                echo '</div>';
                echo '<p class="price">بودجه: ' . htmlspecialchars($row['budget']) . ' تومان</p>';
                echo '<div class="icons">';
                echo '<span class="fa fa-pen-to-square addP"> </span> <span class="fas fa-box anbar" > </span> <span class="fas fa-wrench nazer"> </span> <span class="fas fa-file-invoice hesab"></span>';
                echo '</div>';
                echo '</div></div></div>';

                ?>


                <?php

            }
            ?>


            <!-- Modal for jozeat beshtart -->
            <div class="modal fade" id="jozeat" tabindex="-1" aria-labelledby="projectLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" dir="ltr">
                            <h5 class="modal-title" id="projectLabel"> جزئیات پروژه </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- اطلاعات به صورت داینامیک در اینجا نمایش داده می‌شود -->
                            <h6 class="card-subtitle mb-2 text-muted">کارفرما: <span id="modal-employer"></span><span
                                        class="text-danger"> | </span><span class="text-primary"
                                                                      id="modal-phase"></span></h6>
                            <h6 class="card-subtitle mb-2 text-muted">پیمانکار: <span id="modal-contractor"></span></h6>
                            <h6 class="card-subtitle mb-2 text-muted">وردست 1: <span id="modal-subcontractor1"></span></h6>
                            <h6 class="card-subtitle mb-2 text-muted">وردست 2: <span id="modal-subcontractor2"></span></h6>
                            <h6 class="card-subtitle mb-2 text-muted">وردست 3: <span id="modal-subcontractor3"></span></h6>
                            <h6 class="card-subtitle mb-2 text-muted">وردست 4: <span id="modal-subcontractor4"></span></h6>
                            <h6 class="card-subtitle mb-2 text-muted">وردست 5: <span id="modal-subcontractor5"></span></h6>
                            <div class="progress mb-2">
                                <div class="progress-bar" role="progressbar" style="width: 0%;" id="modal-progress"
                                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%
                                </div>
                            </div>
                            <p class="price">بودجه: <span id="modal-budget"></span> تومان</p>
                            <button class="col-12 btn btn-success col-md-12" data-bs-toggle="modal"
                                    data-bs-target="#nazerin">
                                <span class="fa fa-check-double"></span> تأیید
                            </button>
                        </div>

                        <div class="modal-footer">
                        </div>
                    </div>
                </div>
            </div>

            <?php
        }
        ?>
    </div>

    <script>
        document.querySelectorAll('.show-details').forEach(button => {
            button.addEventListener('click', function () {
                const employer = this.getAttribute('data-employer');
                const contractor = this.getAttribute('data-contractor');
                const phase = this.getAttribute('data-phase');
                const budget = this.getAttribute('data-budget');
                const progress = this.getAttribute('data-progress');
                const subcontractor1 = this.getAttribute('data-subcontractor1');
                const subcontractor2 = this.getAttribute('data-subcontractor2');
                const subcontractor3 = this.getAttribute('data-subcontractor3');
                const subcontractor4 = this.getAttribute('data-subcontractor4');
                const subcontractor5 = this.getAttribute('data-subcontractor5');

                // Display information in the modal
                document.getElementById('modal-employer').textContent = employer;
                document.getElementById('modal-contractor').textContent = contractor;
                document.getElementById('modal-phase').textContent = phase;
                document.getElementById('modal-budget').textContent = budget;
                document.getElementById('modal-progress').style.width = progress + '%';
                document.getElementById('modal-progress').setAttribute('aria-valuenow', progress);
                document.getElementById('modal-progress').textContent = progress + '%';

                // Display subcontractor information
                document.getElementById('modal-subcontractor1').textContent = subcontractor1;
                document.getElementById('modal-subcontractor2').textContent = subcontractor2;
                document.getElementById('modal-subcontractor3').textContent = subcontractor3;
                document.getElementById('modal-subcontractor4').textContent = subcontractor4;
                document.getElementById('modal-subcontractor5').textContent = subcontractor5;

                // Log the data to the console for debugging
                console.log('Employer:', employer);
                console.log('Contractor:', contractor);
                console.log('Phase:', phase);
                console.log('Budget:', budget);
                console.log('Progress:', progress);
                console.log('Subcontractor 1:', subcontractor1);
                console.log('Subcontractor 2:', subcontractor2);
                console.log('Subcontractor 3:', subcontractor3);
                console.log('Subcontractor 4:', subcontractor4);
                console.log('Subcontractor 5:', subcontractor5);
            });
        });
    </script>
    <button class="floating-button" data-bs-toggle="modal" data-bs-target="#project">
        <i class="fa fa-plus"></i>
    </button>


    <!-- Modal for project confirmation -->
    <div class="modal fade" dir="ltr" id="nazerin" tabindex="-1" aria-labelledby="nazerinLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nazerinLabel">تأیید پروژه</h5>
                    <button type="button" class="btn-close text-danger" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    آیا مطمئن هستید که می‌خواهید این پروژه را تأیید کنید؟
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                    <button type="button" class="btn btn-primary">تأیید</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for adding a new project -->
    <div class="modal fade" id="project" tabindex="-1" aria-labelledby="projectLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="projectLabel">اضافه کردن پروژه جدید</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="role_employer" class="form-label">کارفرما:</label>
                            <select class="form-select" id="role_employer" name="role_employer" required>
                                <option value="">انتخاب کنید...</option>
                                <?php
                                $sqlk = "SELECT * FROM employees";
                                $result = $conn->query($sqlk);
                                if ($result) {
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['manager']) . '</option>';
                                        }
                                    } else {
                                        echo '<option value="">هیچ کاربری یافت نشد</option>';
                                    }
                                } else {
                                    echo "Error executing query: " . $conn->error;
                                }
                                ?>
                            </select>
                            <div class="invalid-feedback">لطفاً کارفرما خود را انتخاب کنید.</div>
                        </div>

                        <div class="mb-3">
                            <label for="role_contractor" class="form-label">پیمانکار:</label>
                            <select class="form-select" id="role_contractor" name="role_contractor" required>
                                <option value="">انتخاب کنید...</option>
                                <?php
                                $sql = "SELECT * FROM contractor";
                                $result = $conn->query($sql);
                                if ($result) {
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['name'] . ' ' . $row['family']) . '</option>';
                                        }
                                    } else {
                                        echo '<option value="">هیچ کاربری یافت نشد</option>';
                                    }
                                } else {
                                    echo "Error executing query: " . $conn->error;
                                }
                                ?>
                            </select>
                            <div class="invalid-feedback">لطفاً پیمانکار خود را انتخاب کنید.</div>
                        </div>

                        <button id="addSubcontractor" type="button" class="btn btn-primary mt-2">اضافه کردن وردست
                        </button>

                        <div id="subcontractorList" class="mt-2">
                            <!-- ورودی‌های وردست به اینجا اضافه می‌شوند -->
                        </div>

                        <script>
                            // آرایه‌ای برای نام ورودی‌ها
                            const subcontractorNames = ['subcontractor1', 'subcontractor2', 'subcontractor3', 'subcontractor4', 'subcontractor5'];
                            let subcontractorCount = 0; // شمارنده برای ورودی‌های وردست

                            document.getElementById('addSubcontractor').addEventListener('click', function () {
                                const subcontractorList = document.getElementById('subcontractorList');
                                const currentInputs = subcontractorList.getElementsByTagName('input').length;

                                if (currentInputs < subcontractorNames.length) {
                                    const inputDiv = document.createElement('div');
                                    inputDiv.className = 'mb-2';

                                    const input = document.createElement('input');
                                    input.type = 'text';
                                    input.name = subcontractorNames[subcontractorCount]; // استفاده از نام‌های تعریف شده در آرایه
                                    input.placeholder = 'نام وردست ' + (subcontractorCount + 1); // مشخص کردن نام وردست
                                    input.className = 'form-control';

                                    inputDiv.appendChild(input);
                                    subcontractorList.appendChild(inputDiv);

                                    subcontractorCount++; // افزایش شمارنده
                                } else {
                                    alert('شما نمی‌توانید بیشتر از 5 وردست اضافه کنید.');
                                }
                            });
                        </script>


                        <div class="mb-3">
                            <label for="phase_desc" class="form-label">شرح فازبندی:</label>
                            <input type="text" class="form-control" name="phase_desc" required>
                            <div class="invalid-feedback">لطفاً شرح فازبندی را وارد کنید.</div>
                        </div>

                        <div>
                            <div class="mb-3">
                                <label for="budget" class="form-label">هزینه:</label>
                                <input type="number" name="budget" id="budget" class="form-control" required>
                                <div class="invalid-feedback">لطفاً هزینه را وارد کنید.</div>
                            </div>

                            <div class="btn d-flex justify-content-center align-items-center">
                                <input type="submit" value="ارسال" name="subAdd" class="btn btn-success"
                                       style="width:100px;">
                                <input type="reset" value="لغو" class="btn btn-danger">
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
<script>
    // جستجو و فیلتر پروژه‌ها
    document.getElementById('searchInput').addEventListener('input', function () {
        const searchValue = this.value.toLowerCase();
        const projectCards = document.querySelectorAll('.card');
        projectCards.forEach(card => {
            const phaseDescription = card.getAttribute('data-phase').toLowerCase();
            if (phaseDescription.includes(searchValue)) {
                card.parentElement.style.display = '';
            } else {
                card.parentElement.style.display = 'none';
            }
        });
        console.log('Search value:', searchValue); // Log search value
        projectCards.forEach(card => {
            console.log('Card phase:', card.getAttribute('data-phase'), 'Visibility:', card.parentElement.style.display);
        });
        console.log('Total project cards:', projectCards.length); // Log total number of project cards
    });

    document.getElementById('statusFilter').addEventListener('change', function () {
        const filterValue = this.value;
        const projectCards = document.querySelectorAll('.card');
        projectCards.forEach(card => {
            const projectStatus = card.getAttribute('data-status');
            if (filterValue === '' || projectStatus === filterValue) {
                card.parentElement.style.display = '';
            } else {
                card.parentElement.style.display = 'none';
            }
        });
    });

    document.getElementById('contractorFilter').addEventListener('change', function () {
        const filterValue = this.value;
        const projectCards = document.querySelectorAll('.card');
        projectCards.forEach(card => {
            const contractorId = card.getAttribute('data-contractor-id'); // اکنون contractor-id به درستی تنظیم شده است
            if (filterValue === '' || contractorId == filterValue) {
                card.parentElement.style.display = '';
            } else {
                card.parentElement.style.display = 'none';
            }
        });
    });

    document.getElementById('sortFilter').addEventListener('change', function () {
        const sortValue = this.value;
        const projectCards = Array.from(document.querySelectorAll('.card'));
        projectCards.sort((a, b) => {
            let aValue, bValue;
            if (sortValue === 'name') {
                aValue = a.querySelector('.card-title').textContent;
                bValue = b.querySelector('.card-title').textContent;
                return aValue.localeCompare(bValue);
            } else if (sortValue === 'budget') {
                aValue = parseInt(a.getAttribute('data-budget'));
                bValue = parseInt(b.getAttribute('data-budget'));
                return aValue - bValue;
            } else if (sortValue === 'progress') {
                aValue = parseInt(a.getAttribute('data-progress'));
                bValue = parseInt(b.getAttribute('data-progress'));
                return aValue - bValue;
            }
        });
        const projectList = document.getElementById('projectList');
        projectList.innerHTML = '';
        projectCards.forEach(card => {
            projectList.appendChild(card.parentElement);
        });
    });
</script>

<script>
    document.querySelectorAll('.show-details').forEach(button => {
        button.addEventListener('click', function () {
            const employer = this.getAttribute('data-employer');
            const contractor = this.getAttribute('data-contractor');
            const phase = this.getAttribute('data-phase');
            const budget = this.getAttribute('data-budget');
            const progress = this.getAttribute('data-progress');

            // نمایش اطلاعات در مودال
            document.querySelector('#jozeat .modal-body').innerHTML = `
                <h6 class="card-subtitle mb-2 text-muted">کارفرما: ${employer}<span class="text-danger"> | </span><span class="text-primary">${phase}</span></h6>
                <h6 class="card-subtitle mb-2 text-muted">پیمانکار: ${contractor}</h6>
                <div class="progress mb-2">
                    <div class="progress-bar" role="progressbar" style="width: ${progress}%;"
                         aria-valuenow="${progress}" aria-valuemin="0" aria-valuemax="100">${progress}%</div>
                </div>
                <p class="price">بودجه: ${budget} تومان</p>
            `;
        });
    });
</script>


<script>
    function getPersianDay(date) {
        const daysOfWeek = ["شنبه", "یکشنبه", "دوشنبه", "سه‌شنبه", "چهارشنبه", "پنجشنبه", "جمعه"];
        return daysOfWeek[date.getDay()];
    }

    const today = new Date();
    const persianDay = getPersianDay(today);
    document.getElementById("timer").innerText = persianDay + " " + today.toLocaleDateString("fa-IR");
</script>

<script>

    document.addEventListener('DOMContentLoaded', function () {
        const showDetailsButtons = document.querySelectorAll('.show-details');

        showDetailsButtons.forEach(button => {
            button.addEventListener('click', function () {
                const employer = this.getAttribute('data-employer');
                const contractor = this.getAttribute('data-contractor');
                const phase = this.getAttribute('data-phase');
                const budget = this.getAttribute('data-budget');
                const progress = this.getAttribute('data-progress');

// نمایش اطلاعات در مودال
                document.getElementById('modal-employer').textContent = employer;
                document.getElementById('modal-contractor').textContent = contractor;
                document.getElementById('modal-phase').textContent = phase;
                document.getElementById('modal-budget').textContent = budget;
                document.getElementById('modal-progress').style.width = progress + '%';
                document.getElementById('modal-progress').setAttribute('aria-valuenow', progress);
                document.getElementById('modal-progress').textContent = progress + '%';
            });
        });

// جستجو و فیلتر پروژه‌ها
        document.getElementById('searchInput').addEventListener('input', function () {
            const searchValue = this.value.toLowerCase();
            const projectCards = document.querySelectorAll('.card');
            projectCards.forEach(card => {
                const phaseDescription = card.getAttribute('data-phase').toLowerCase();
                card.parentElement.style.display = phaseDescription.includes(searchValue) ? '' : 'none';
            });
        });

        document.getElementById('statusFilter').addEventListener('change', function () {
            const filterValue = this.value;
            const projectCards = document.querySelectorAll('.card');
            projectCards.forEach(card => {
                const projectStatus = card.getAttribute('data-status');
                card.parentElement.style.display = (filterValue === '' || projectStatus === filterValue) ? '' : 'none';
            });
        });

        document.getElementById('contractorFilter').addEventListener('change', function () {
            const filterValue = this.value;
            const projectCards = document.querySelectorAll('.card');
            projectCards.forEach(card => {
                const contractorId = card.getAttribute('data-contractor-id');
                card.parentElement.style.display = (filterValue === '' || contractorId == filterValue) ? '' : 'none';
            });
        });

        document.getElementById('sortFilter').addEventListener('change', function () {
            const sortValue = this.value;
            const projectCards = Array.from(document.querySelectorAll('.card'));
            projectCards.sort((a, b) => {
                let aValue, bValue;
                if (sortValue === 'name') {
                    aValue = a.querySelector('.card-title').textContent;
                    bValue = b.querySelector('.card-title').textContent;
                    return aValue.localeCompare(bValue);
                } else if (sortValue === 'budget') {
                    aValue = parseInt(a.getAttribute('data-budget'));
                    bValue = parseInt(b.getAttribute('data-budget'));
                    return aValue - bValue;
                } else if (sortValue === 'progress') {
                    aValue = parseInt(a.getAttribute('data-progress'));
                    bValue = parseInt(b.getAttribute('data-progress'));
                    return aValue - bValue;
                }
            });
            const projectList = document.getElementById('projectList');
            projectList.innerHTML = '';
            projectCards.forEach(card => {
                projectList.appendChild(card.parentElement);
            });
        });
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
</script>

</body>

</html>