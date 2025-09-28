<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once "database.php";
global $conn;
$role = isset($_COOKIE['role']) ? $_COOKIE['role'] : '';
$name = isset($_COOKIE['userID']) ? $_COOKIE['userID'] : '';


if (!isset($conn) || $conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getFilters($conn) {
    return [
        'status' => isset($_GET['status']) ? (int)$_GET['status'] : 0,
        'priority' => isset($_GET['priority']) ? $conn->real_escape_string($_GET['priority']) : '0',
        'abc' => isset($_GET['abc']) ? $conn->real_escape_string($_GET['abc']) : '0',
        'progress' => isset($_GET['progres']) ? $conn->real_escape_string($_GET['progres']) : '0',
        'start_date' => isset($_GET['start_date']) ? $conn->real_escape_string($_GET['start_date']) : '',
        'end_date' => isset($_GET['end_date']) ? $conn->real_escape_string($_GET['end_date']) : '',
        'sort' => isset($_GET['sort']) ? strtoupper($conn->real_escape_string($_GET['sort'])) : 'ASC'
    ];
}

function buildQuery($filters, $role, $name) {
    $conditions = [];
    if ($filters['status'] != 0) {
        $conditions[] = "projects.status = " . $filters['status'];
    }
    if ($filters['priority'] !== '0' && $filters['priority'] !== 'on') {
        $conditions[] = "projects.priority = '" . $filters['priority'] . "'";
    }
    if ($filters['progress'] !== '0' && $filters['progress'] !== 'on') {
        $conditions[] = "projects.progress = '" . $filters['progress'] . "'";
    }
    if (!empty($filters['start_date'])) {
        $conditions[] = "projects.created_at >= '" . $filters['start_date'] . "'";
    }
    if (!empty($filters['end_date'])) {
        $conditions[] = "projects.updated_at <= '" . $filters['end_date'] . "'";
    }
    if ($role !== "پیمانکار" && $role !== "مدیر" && $role !== "حسابدار" && $role !== "انباردار") {
        die("شما مجوز دسترسی به این صفحه را ندارید.");
    }
    // اعمال دسترسی‌ها بر اساس نقش کاربر
    if ($role === "پیمانکار") {        
        $conditions[] = "contractor1 = '$name'";
    } elseif ($role === "مدیر") {
        // مدیران می‌توانند همه پروژه‌ها را ببینند
        // نیازی به افزودن شرط خاصی نیست
    } elseif ($role === "حسابدار") {
        // حسابداران می‌توانند همه پروژه‌ها را ببینند
        // نیازی به افزودن شرط خاصی نیست
    }elseif ($role === "انباردار") {
      //انباردار می تواند همه پروژه ها رو ببیند
      //نیازی به افزودن شرط خاصی نیست
    }

    $sql = "SELECT projects.*, employees.manager 
            FROM projects 
            JOIN employees ON projects.employer = employees.id";

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }

    $orderBy = [];
    if ($filters['priority'] === 'on') {
        $orderBy[] = "projects.priority";
    }
    if ($filters['progress'] === 'on') {
        $orderBy[] = "projects.progress";
    }
    switch ($filters['abc']) {
        case '1':
            $orderBy[] = "employees.manager";
            break;
        case '2':
            $orderBy[] = "projects.phase_description";
            break;
        case '3':
            $orderBy[] = "employees.factory";
            break;
    }
    if (!empty($orderBy)) {
        $sql .= " ORDER BY " . implode(', ', $orderBy) . " " . $filters['sort'];
    } else {
        $sql .= " ORDER BY projects.created_at " . $filters['sort'];
    }

    return $sql;
}

$filters = getFilters($conn);
$sql = buildQuery($filters, $role, $name);

$resultProjects = $conn->query($sql);

if ($resultProjects === false) {
    error_log("Query error: " . $conn->error);
    die("خطا در دریافت اطلاعات. لطفاً با پشتیبانی تماس بگیرید.");
}

$cssLinks = [
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'b_icons/font/bootstrap-icons.min.css',
    'fontA/css/all.min.css',
    'checkout.css',
    'swiper-bundle.min.css',
    'assets/css/jquery.dataTables.min.css',
    'https://unpkg.com/ag-grid-community/styles/ag-grid.css',
    'https://unpkg.com/ag-grid-community/styles/ag-theme-alpine.css'
];
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects - دپارتمان برق</title>

    <?php
    foreach ($cssLinks as $Link) {
        echo '<link rel="stylesheet" href="' . $Link . '">';
    }
    ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://kalahabama.ir/webshop/assets/css/projects/style_project.css">
    <link rel="stylesheet" href="https://kalahabama.ir/webshop/assets/css/projects/style_project2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
    <style>
        body {
            direction: rtl;
            overflow-x: hidden;
        }
        .project-card {
            margin-bottom: 20px;
        }    
        .circle_custome {
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
            top: 100%;
            right: 0;
        }
    </style>

    <style>
        body {
    direction: rtl;
    font-family: 'Gulzar', serif;
    background-color: #f4f6f9;
}

/* استایل کارت‌های پروژه */
.project-card {
    margin-bottom: 20px;
    position: relative;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
}

.project-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.project-card .card {
    background-color: #ffffff;
    border-radius: 15px;
    border: none;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease;
}

/* انیمیشن کارت‌ها */
.project-card {
    animation: fadeInUp 0.5s ease forwards;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.project-card .card-body {
    padding: 20px;
    text-align: right;
}

.project-card .icons {
    margin-top: 10px;
    display: flex;
    justify-content: space-around;
    color: #6c757d;
    font-size: 1.5em;
}

/* انیمیشن برای آیکون‌ها */
.project-card .icons i {
    transition: color 0.3s ease;
}

.project-card .icons i:hover {
    color: #007bff;
}

/* دایره نمایش اولویت */
.priority {
    position: absolute;
    top: 10px;
    left: 10px;
    font-size: 1.2em;
    font-weight: bold;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
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

/* دکمه مرتب‌سازی */
#sortToggle {
    position: relative;
    overflow: hidden;
    transition: background-color 0.3s ease;
}

#sortToggle:hover {
    background-color: #495057;
}

/* افکت جستجو */
#searchInput {
    position: fixed;
    top: 10px;
    right: 0px;
    border-radius: 30px;
    z-index: 999;
    transition: box-shadow 0.3s ease;
}

#searchInput:focus {
    box-shadow: 0 0 10px rgba(0, 123, 255, 0.4);
}

    </style>
</head>
<body>
    <?php
    require_once "require_once/header.php";
    require_once "require_once/swiper.php";
    require_once "require_once/menu.php";

    // headers();
    menus();
    ?>
    <div class="row search-bar" style="margin-top:100px;">
        <div class="col-md-6 offset-md-3">
            <input type="text" class="form-control" placeholder="جستجوی پروژه..." id="searchInput">
        </div>
    </div>
    <div class="container mt-4">
        <!-- <h1 class="header">لیست پروژه‌ها - دپارتمان برق</h1> -->
        <a href="filter.php" class="btn btn-primary mb-3"><i class="fa fa-filter" aria-hidden="true"></i></a>
        <a href="add_project_page.php" class="bg-danger btn btn-danger  fa-bounce"  style="   position: fixed;
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
    z-index: 1000;"><i class="fa fa-plus" aria-hidden="true"></i>
        </a>
        <button id="sortToggle" class="btn btn-secondary mb-3">
            <i id="sortIcon" class="fas fa-sort-amount-down-alt"></i>
        </button>
        <div class="row project-grid" id="projectList">
          <?php
if ($resultProjects->num_rows > 0) {
    $a = 1;
    while ($row = $resultProjects->fetch_assoc()) {
        $priority = $row['priority'];
        if ($priority == 'A') {
            $bg_priority = "red";
        } else if ($priority == 'B') {
            $bg_priority = "orange";
        } else if ($priority == 'C') {
            $bg_priority = "yellow";
        } else if ($priority == 'D') {
            $bg_priority = "green";
        } else {
            $bg_priority = "white";
        }

        $addP = "";
        $anbar = "";
        $nazer = "";
        $hesab = "";
        
        if ($row['progress'] <= 10) {
            $addP = "red";
        } elseif ($row['progress'] >= 100) {
            $addP = "red";
            $anbar = "brown";
            $nazer = "orange";
            $hesab = "green";
        } elseif ($row['progress'] >= 80) {
            $addP = "red";
            $anbar = "brown";
            $nazer = "orange";
        } elseif ($row['progress'] >= 70) {
            $addP = "red";
            $anbar = "brown";
        }
        
        // echo '<span class="fa fa-pen-to-square addP" style="color:' . $addP . '; "> </span> 
        //       <span class="fas fa-box anbar" style="color:' . $anbar . '; "> </span> 
        //       <span class="fas fa-wrench nazer" style="color:' . $nazer . '; "> </span> 
        //       <span class="fas fa-file-invoice hesab" style="color:' . $hesab . '; "></span>';
        
        
        $userId = $row['id'];
        echo '<div class="col-12 col-md-6 col-lg-4 project-card">';
        echo '<a href="jozeat.php?project_id=' . $userId . '" class="card shadow-lg border-primary">';
        echo '<div class="card-body text-center">';
        echo '<h6 class="card-subtitle mb-2 mt-3 text-muted">' . htmlspecialchars($row['manager'] ?? '') . '<span class="text-danger"> | </span>' . '<span class="text-primary">' . htmlspecialchars($row['phase_description'] ?? '') . '</span>' . '</h6>';
        echo '<h5 class="mb-2 text-success" style="width:100%; text-align:right;">' . htmlspecialchars($row['price'] ?? '') . ' تومان</h5>';    
        echo '<div class="icons">';
        echo '<span class="fa fa-pen-to-square addP" style="color:' . $addP . '; "> </span> 
              <span class="fas fa-box anbar" style="color:' . $anbar . '; "> </span> 
              <span class="fas fa-wrench nazer" style="color:' . $nazer . '; "> </span> 
              <span class="fas fa-file-invoice hesab" style="color:' . $hesab . '; "></span>';        echo '</div>';
        echo '<div class="progress mb-2">';
        echo '<div class="progress-bar" role="progressbar" style="width: ' . htmlspecialchars($row['progress'] ?? '0') . '%;" aria-valuenow="' . htmlspecialchars($row['progress'] ?? '0') . '" aria-valuemin="0" aria-valuemax="100"><span class="text-dark" style="" >'. htmlspecialchars($row['progress'] ?? '0') . '% </span></div>';
        echo '</div>';
        echo '</div>';
        echo '<div class="circle text-white" style="top: 0px; right:0px ">' . $a++ . '</div>';
        echo '<div class="priority text-white p-4 d-flex justify-content-center align-items-center" style="width:50px; height:50px; text-shadow: -1px 0px 7px rgba(8, 0, 0, 1); background:' . htmlspecialchars($bg_priority) . '; border-radius:50%;">' . htmlspecialchars($row['priority'] ?? '') . '</div>';                   
        echo '</a>';
        echo '</div>';
    }
} else {
    echo "هیچ پروژه‌ای پیدا نشد.";
}
?>

        </div>
    </div>

    <?php 
require_once "require_once/loading.php";
?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2-single').select2({
                dir: "rtl",
                language: {
                    noResults: function() {
                        return "نتیجه‌ای یافت نشد";
                    },
                    searching: function() {
                        return "در حال جستجو...";
                    }
                },
                placeholder: "جستجو کنید...",
                allowClear: true,
                templateResult: formatOption,
                templateSelection: formatOption
            });

            $('.select2-multiple').select2({
                dir: "rtl",
                language: {
                    noResults: function() {
                        return "نتیجه‌ای یافت نشد";
                    },
                    searching: function() {
                        return "در حال جستجو...";
                    }
                },
                placeholder: "چند گزینه انتخاب کنید...",
                allowClear: true,
                templateResult: formatOption,
                templateSelection: formatOption,
                tags: true
            });

            function formatOption(option) {
                if (!option.id) return option.text;
                var icon = $(option.element).data('icon');
                if (!icon) return option.text;
                return $(`<span><i class="bi ${icon} me-2"></i>${option.text}</span>`);
            }

            function updateSelectedTags() {
                var selectedItems = $('.select2-multiple').select2('data');
                var tagsHtml = '';
                selectedItems.forEach(function(item) {
                    var icon = $(item.element).data('icon') || 'bi-tag';
                    tagsHtml += `
                        <div class="custom-tag">
                            <i class="bi ${icon}"></i>
                            ${item.text}
                        </div>
                    `;
                });
                $('#selected-tags').html(tagsHtml);
                $('.selected-items').toggleClass('show', selectedItems.length > 0);
            }

            $('.select2-multiple').on('change', updateSelectedTags);

            $('.btn-clear').click(function() {
                $('.select2-multiple').val(null).trigger('change');
            });

            $('.select2-selection').hover(
                function() { $(this).addClass('hover'); },
                function() { $(this).removeClass('hover'); }
            );

            $('#searchInput').on('input', function() {
                const searchValue = $(this).val().toLowerCase();
                $('.project-card').each(function() {
                    const cardText = $(this).text().toLowerCase();
                    $(this).toggle(cardText.includes(searchValue));
                });
            });

            // Toggle sort order
            $('#sortToggle').on('click', function() {
                var currentSort = '<?php echo $filters['sort']; ?>';
                var newSort = currentSort === 'ASC' ? 'DESC' : 'ASC';
                var newIconClass = newSort === 'ASC' ? 'fas fa-sort-amount-down-alt' : 'fas fa-sort-amount-up-alt';
                $('#sortIcon').removeClass().addClass(newIconClass);
                var urlParams = new URLSearchParams(window.location.search);
                urlParams.set('sort', newSort);
                window.location.search = urlParams.toString();
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>