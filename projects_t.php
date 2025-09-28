<?php

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
$jsLinks = [
    'https://code.jquery.com/jquery-3.6.0.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
    'https://unpkg.com/swiper/swiper-bundle.min.js',
    'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',
    'https://code.highcharts.com/highcharts.js',
    'https://code.highcharts.com/highcharts-3d.js',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11',
];



error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once "database.php";

// دریافت فیلترها از URL
$status_filter = $_GET['status'] ?? '0';
$priority_filter = $_GET['priority'] ?? '0';
$abc_filter = $_GET['abc'] ?? '0';
$progress_filter = $_GET['progres'] ?? '0'; // باید 'progres' باشد نه 'progress'
$start_date_filter = $_GET['start_date'] ?? '';
$end_date_filter = $_GET['end_date'] ?? '';

// ساخت کوئری پایه
$sql = "SELECT projects.*, employees.manager FROM projects 
        JOIN employees ON projects.employer = employees.id";

// بررسی و افزودن شرط‌ها به کوئری
$conditions = [];
if ($status_filter !== '0') {
    $conditions[] = "projects.status = '" . $conn->real_escape_string($status_filter) . "'";
}
if ($priority_filter !== '0') {
    $conditions[] = "projects.priority = '" . $conn->real_escape_string($priority_filter) . "'";
}
if ($progress_filter !== '0') { 
    $conditions[] = "projects.progress = '" . $conn->real_escape_string($progress_filter) . "'";
}
if (!empty($start_date_filter)) {
    $conditions[] = "projects.created_at >= '" . $conn->real_escape_string($start_date_filter) . "'";
}
if (!empty($end_date_filter)) {
    $conditions[] = "projects.updated_at <= '" . $conn->real_escape_string($end_date_filter) . "'";
}

// افزودن شرط‌ها به کوئری نهایی
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

// افزودن مرتب‌سازی بر اساس فیلتر انتخاب شده
switch ($abc_filter) {
    case '1':
        $sql .= " ORDER BY employees.manager";
        break;
    case '2':
        $sql .= " ORDER BY projects.phase_description";
        break;
    case '3':
        $sql .= " ORDER BY employees.factory";
        break;
}

// نمایش کوئری نهایی برای اشکال‌زدایی
// echo "SQL Query: " . $sql . "<br>";

// آماده‌سازی و اجرای کوئری
$resultProjects = $conn->query($sql);

if ($resultProjects === false) {
    die("خطا در اجرای کوئری: " . $conn->error);
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
    ?>
    <title>فرم با Select2</title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://kalahabama.ir/webshop/assets/css/projects/style_project.css">
    <link rel="stylesheet" href="https://kalahabama.ir/webshop/assets/css/projects/style_project2.css">
    <style>
        body {
            direction: rtl;
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
</head>
<body>
    <?php
    require_once "require_once/header.php";
    require_once "require_once/swiper.php";
    require_once "require_once/menu.php";

    headers();
    menus();
    ?>
    <div class="row search-bar" style="margin-top:100px;">
        <div class="col-md-6 offset-md-3">
            <input type="text" class="form-control" placeholder="جستجوی پروژه..." id="searchInput">
        </div>
    </div>
    <div class="container mt-4">
        <h1 class="header">لیست پروژه‌ها - دپارتمان برق</h1>
        <a href="filter.php" class="btn btn-primary mb-3">فیلتر پروژه‌ها</a>
        <div class="row project-grid" id="projectList">
            <?php
            if ($resultProjects->num_rows > 0) {
                $a = 1;
                while ($row = $resultProjects->fetch_assoc()) {
                    $userId = $row['id'];
                    echo '<div class="col-12 col-md-6 col-lg-4">';
                    echo '<a href="jozeat.php?project_id=' . $userId . '" class="card shadow-lg border-primary project-card">';
                    echo '<div class="card-body text-center">';
                    echo '<h6 class="card-subtitle mb-2 mt-3 text-muted">' . htmlspecialchars($row['manager'] ?? '') . '<span class="text-danger"> | </span>' . '<span class="text-primary">' . htmlspecialchars($row['phase_description'] ?? '') . '</span>' . '</h6>';
                    echo '<h5 class="mb-2 text-success" style="width:100%; text-align:right;">' . htmlspecialchars($row['price'] ?? '') . ' تومان</h5>';    
                    echo '<div class="icons">';
                    echo '<span class="fa fa-pen-to-square addP"> </span> <span class="fas fa-box anbar"> </span> <span class="fas fa-wrench nazer"> </span> <span class="fas fa-file-invoice hesab"></span>';
                    echo '</div>';
                    echo '<div class="progress mb-2">';
                    echo '<div class="progress-bar" role="progressbar" style="width: ' . htmlspecialchars($row['progress'] ?? '0') . '%;" aria-valuenow="' . htmlspecialchars($row['progress'] ?? '0') . '" aria-valuemin="0" aria-valuemax="100">' . htmlspecialchars($row['progress'] ?? '0') . '%</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="circle text-whtie" style="top: 0px; right:0px ">' . $a++ . '</div>';
                    echo '</a>';
                    echo '</div>';
                }
            } else {
                echo "هیچ پروژه‌ای پیدا نشد.";
            }
            $conn->close();
            ?>
        </div>
    </div>
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
        });

        document.getElementById('searchInput').addEventListener('input', function() {
            const searchValue = this.value.toLowerCase();
            const projectCards = document.querySelectorAll('.card');
            projectCards.forEach(card => {
                const cardText = card.textContent.toLowerCase();
                card.parentElement.style.display = cardText.includes(searchValue) ? '' : 'none';
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script>
        document.getElementById('statusFilter').addEventListener('change', function() {
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

        document.getElementById('contractorFilter').addEventListener('change', function() {
            const filterValue = this.value;
            const projectCards = document.querySelectorAll('.card');
            projectCards.forEach(card => {
                const contractorId = card.getAttribute('data-contractor-id');
                if (filterValue === '' || contractorId == filterValue) {
                    card.parentElement.style.display = '';
                } else {
                    card.parentElement.style.display = 'none';
                }
            });
        });

        document.getElementById('sortFilter').addEventListener('change', function() {
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
    <script type="text/javascript">
        let selectCounter = 1;

        function addSelect() {
            const container = document.querySelector('.mb-3');
            const currentSelects = container.querySelectorAll('div');

            if (currentSelects.length >= 5) {
                alert("حداکثر 5 سلکت می‌توانید اضافه کنید.");
                return;
            }

            const newSelect = document.createElement('div');

            newSelect.innerHTML = `
                <select class="form-select" id="role_contractor_${selectCounter}" name="contractor${selectCounter}" required>
                    <option value="">انتخاب کنید...</option>
                    <?php
                    $sql = "SELECT * FROM contractor";
                    $result = $conn->query($sql);
                    if ($result) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row['id'] ?? '') . '">' . htmlspecialchars($row['name'] . ' ' . $row['family'] ?? '') . '</option>';
                        }
                    } else {
                        echo '<option value="">هیچ کاربری یافت نشد</option>';
                    }
                    ?>
                </select>
                <button type="button" onclick="addSelect()" class="text-white btn btn-success">+</button>
                <button type="button" onclick="deleteSelect(this)" class="btn btn-danger text-white">-</button>
                <div class="invalid-feedback">لطفاً پیمانکار خود را انتخاب کنید.</div>
            `;
            container.appendChild(newSelect);
            selectCounter++;
        }

        function deleteSelect(button) {
            const selectDiv = button.parentElement;
            selectDiv.remove();
        }
    </script>
</body>
</html>