<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "database.php";
global $conn;

// Function to display SweetAlert messages
function showAlert($title, $text, $icon) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: '$title',
                text: '$text',
                icon: '$icon',
                confirmButtonText: 'باشه'
            });
        });
    </script>";
}

session_start();
require_once "database.php";
global $conn;

// تابع showAlert
function showAlert($title, $text, $icon) { ... }

// تابع getSubcontractorOptions
function getSubcontractorOptions($selectedId = null) { ... }

// پاک‌سازی ID
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$id) {
    showAlert('خطا', 'شناسه پروژه معتبر نیست.', 'error');
    exit;
}

// گرفتن ID کاربر
$peymankar = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$peymankar) {
    showAlert('خطا', 'لطفاً ابتدا وارد سیستم شوید.', 'error');
    exit;
}

// بررسی پروژه
$stmt = $conn->prepare("SELECT * FROM projects WHERE id = ? AND peymankar = ?");
$stmt->bind_param("ii", $id, $peymankar);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();
$stmt->close();

if (!$project) {
    showAlert('خطا', 'پروژه نظارت خورده است و امکان تغییر مشخصات وجود ندارد.', 'error');
    exit;
}


// Function to generate subcontractor options
function getSubcontractorOptions($selectedId = null) {
    global $conn;
    $options = '';
    $result = $conn->query("SELECT * FROM users");
    while ($row = $result->fetch_assoc()) {
        $selected = ($row['id'] == $selectedId) ? 'selected' : '';
        $options .= "<option value='{$row['id']}' $selected>{$row['name']} {$row['family']}</option>";
    }
    return $options;
}

if (isset($_GET['id'])) {
    $projectID = intval($_GET['id']);

    // Fetch project details
    $stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->bind_param("i", $projectID);
    $stmt->execute();
    $result = $stmt->get_result();
    $project = $result->fetch_assoc();

    if (!$project) {
        showAlert('خطا', 'پروژه ای با این شناسه پیدا نشد.', 'error');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize inputs
        $employer = filter_input(INPUT_POST, 'role_employer', FILTER_SANITIZE_NUMBER_INT);
        $contractor = filter_input(INPUT_POST, 'role_contractor', FILTER_SANITIZE_NUMBER_INT);
        $phase_desc = filter_input(INPUT_POST, 'phase_desc', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $priority = filter_input(INPUT_POST, 'priority', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        // Handle subcontractors
        $contractor2 = filter_input(INPUT_POST, 'subcontractor1', FILTER_SANITIZE_NUMBER_INT);
        $contractor3 = filter_input(INPUT_POST, 'subcontractor2', FILTER_SANITIZE_NUMBER_INT);
        $contractor4 = filter_input(INPUT_POST, 'subcontractor3', FILTER_SANITIZE_NUMBER_INT);
        $contractor5 = filter_input(INPUT_POST, 'subcontractor4', FILTER_SANITIZE_NUMBER_INT);

        // Convert empty values to NULL
        $contractor2 = $contractor2 ?: NULL;
        $contractor3 = $contractor3 ?: NULL;
        $contractor4 = $contractor4 ?: NULL;
        $contractor5 = $contractor5 ?: NULL;

        // دریافت درصد‌ها از فرم با تبدیل صریح به float
        $c1p = (float)filter_input(INPUT_POST, 'c1p', FILTER_VALIDATE_FLOAT) ?? 0;
        $c2p = (float)filter_input(INPUT_POST, 'c2p', FILTER_VALIDATE_FLOAT) ?? 0;
        $c3p = (float)filter_input(INPUT_POST, 'c3p', FILTER_VALIDATE_FLOAT) ?? 0;
        $c4p = (float)filter_input(INPUT_POST, 'c4p', FILTER_VALIDATE_FLOAT) ?? 0;
        $c5p = (float)filter_input(INPUT_POST, 'c5p', FILTER_VALIDATE_FLOAT) ?? 0;

        // محاسبه مجموع درصدها
        $totalPercentage = $c1p + $c2p + $c3p + $c4p + $c5p;

        // بررسی محدودیت مجموع درصدها
        if ($totalPercentage > 60) {
            showAlert('خطا', 'مجموع درصدها نباید بیشتر از 60 باشد. مجموع فعلی: ' . $totalPercentage, 'error');
            exit;
        }
        if ($totalPercentage < 60) {
            showAlert('خطا', 'مجموع درصدها نباید کمتر از 60 باشد. مجموع فعلی: ' . $totalPercentage, 'error');
            exit;
        }

        // محاسبه درصد پیشرفت
        $initialProgress = 0;
        if (!empty($employer)) {
            $initialProgress += 5; // اگر کارفرما تعریف شده باشد، 5% اضافه می‌شود
        }
        if (!empty($contractor)) {
            $initialProgress += 5; // اگر پیمانکار تعریف شده باشد، 5% اضافه می‌شود
        }

        // Update query with all contractor fields
        $updateStmt = $conn->prepare("UPDATE projects SET 
            employer = ?, 
            contractor1 = ?, 
            contractor2 = ?, 
            contractor3 = ?, 
            contractor4 = ?, 
            contractor5 = ?, 
            c1p = ?, 
            c2p = ?, 
            c3p = ?, 
            c4p = ?, 
            c5p = ?, 
            phase_description = ?, 
            priority = ?, 
            progress = ? 
            WHERE id = ?");

        // تصحیح نوع داده‌ها - باید با تعداد پارامترها مطابقت داشته باشد
        $updateStmt->bind_param(
            "iiiiiidddddssii", // 15 پارامتر (6 integer, 5 double, 2 string, 1 integer)
            $employer,
            $contractor,
            $contractor2,
            $contractor3,
            $contractor4,
            $contractor5,
            $c1p,
            $c2p,
            $c3p,
            $c4p,
            $c5p,
            $phase_desc,
            $priority,
            $initialProgress,
            $projectID
        );

        if ($updateStmt->execute()) {
            echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'موفقیت!',
                    text: 'اطلاعات پروژه با موفقیت به‌روزرسانی شد.',
                    icon: 'success'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'projects.php?status=0&priority=on&abc=0&progres=on&start_date=&end_date=';
                    }
                });
            });
            </script>";
        } else {
            showAlert('خطا', 'خطایی در به‌روزرسانی اطلاعات پروژه رخ داد: ' . $conn->error, 'error');
        }
        $updateStmt->close();
    }
} else {
    showAlert('خطا', 'پارامتر ID وجود ندارد.', 'error');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ویرایش پروژه</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body { direction: rtl; }
        .container {
            margin-top: 50px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
        }
        .select2-container--default .select2-selection--single {
            height: 38px;
            padding: 5px;
        }
        .percentage-input {
            width: 100px;
        }
        #totalPercentageDisplay {
            font-size: 1.1rem;
            font-weight: bold;
        }
        #percentageWarning {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center mb-4">ویرایش اطلاعات پروژه</h2>
    <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
        
        <!-- کارفرما -->
        <div class="mb-3">
            <label for="role_employer" class="form-label">کارفرما:</label>
            <select class="form-select select2" id="role_employer" name="role_employer" required>
                <option value="">انتخاب کنید...</option>
                <?php
                $result = $conn->query("SELECT * FROM employees");
                while ($row = $result->fetch_assoc()) {
                    $selected = ($row['id'] == $project['employer']) ? 'selected' : '';
                    echo '<option value="' . $row['id'] . '" ' . $selected . '>' . 
                         htmlspecialchars($row['manager']) . '</option>';
                }
                ?>
            </select>
        </div>

        <!-- پیمانکار اصلی -->
        <div class="mb-3">
            <label for="role_contractor" class="form-label">پیمانکار اصلی:</label>
            <div class="d-flex gap-2 align-items-center">
                <select class="form-select select2 flex-grow-1" id="role_contractor" name="role_contractor" required>
                    <option value="">انتخاب کنید...</option>
                    <?php
                    $result = $conn->query("SELECT * FROM users");
                    while ($row = $result->fetch_assoc()) {
                        $selected = ($row['id'] == $project['contractor1']) ? 'selected' : '';
                        echo '<option value="' . $row['id'] . '" ' . $selected . '>' . 
                             htmlspecialchars($row['name'] . ' ' . $row['family']) . '</option>';
                    }
                    ?>
                </select>
                <input type="number" class="form-control percentage-input" name="c1p" 
                       value="<?= htmlspecialchars($project['c1p'] ?? '') ?>" 
                       placeholder="درصد" min="0" max="60" step="0.1" required>
                <span class="ms-1">%</span>
            </div>
        </div>

        <!-- وردست‌ها -->
        <div class="mb-3">
            <label class="form-label">وردست‌ها:</label>
            <div id="subcontractorList">
                <?php for ($i = 1; $i <= 4; $i++): 
                    $field = 'contractor' . ($i + 1);
                    $percentageField = 'c' . ($i + 1) . 'p';
                    if (!empty($project[$field])): ?>
                        <div class="subcontractor-item mb-3" id="subcontractor<?= $i ?>">
                            <div class="d-flex gap-2 align-items-center">
                                <select class="form-select select2 flex-grow-1" name="subcontractor<?= $i ?>">
                                    <option value="">انتخاب کنید...</option>
                                    <?= getSubcontractorOptions($project[$field]) ?>
                                </select>
                                <input type="number" class="form-control percentage-input" name="c<?= $i + 1 ?>p" 
                                       value="<?= htmlspecialchars($project[$percentageField] ?? '') ?>" 
                                       placeholder="درصد" min="0" max="60" step="0.1" required>
                                <span class="ms-1">%</span>
                                <button type="button" class="btn btn-danger" onclick="removeSubcontractor(<?= $i ?>)">حذف</button>
                            </div>
                        </div>
                    <?php endif; 
                endfor; ?>
            </div>
            <button type="button" class="btn btn-success mt-2" id="addSubcontractorBtn" onclick="addSubcontractor()">+ افزودن وردست</button>
        </div>

        <!-- بقیه فیلدها -->
        <div class="mb-3">
            <label for="phase_desc" class="form-label">شرح فازبندی:</label>
            <input type="text" class="form-control" name="phase_desc" 
                   value="<?= htmlspecialchars($project['phase_description']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="priority" class="form-label">اولویت:</label>
            <select class="form-select" name="priority" required>
                <option value="A" <?= $project['priority'] == 'A' ? 'selected' : '' ?>>A</option>
                <option value="B" <?= $project['priority'] == 'B' ? 'selected' : '' ?>>B</option>
                <option value="C" <?= $project['priority'] == 'C' ? 'selected' : '' ?>>C</option>
                <option value="D" <?= $project['priority'] == 'D' ? 'selected' : '' ?>>D</option>
            </select>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">ذخیره تغییرات</button>
            <a href="projects.php" class="btn btn-outline-secondary btn-lg">بازگشت</a>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2();
    
    // Initialize subcontractor count
    let subcontractorCount = <?= count(array_filter([$project['contractor2'], $project['contractor3'], $project['contractor4'], $project['contractor5']])) ?>;

    // Add subcontractor
    window.addSubcontractor = function() {
        if (subcontractorCount >= 4) {
            Swal.fire('خطا!', 'حداکثر ۴ وردست مجاز است');
            return;
        }
        subcontractorCount++;
        const newItem = `
        <div class="subcontractor-item mb-3" id="subcontractor${subcontractorCount}">
            <div class="d-flex gap-2 align-items-center">
                <select class="form-select select2 flex-grow-1" name="subcontractor${subcontractorCount}" required>
                    <option value="">انتخاب کنید...</option>
                    <?= getSubcontractorOptions() ?>
                </select>
                <input type="number" class="form-control percentage-input" name="c${subcontractorCount + 1}p" 
                       placeholder="درصد" min="0" max="60" step="0.1" required>
                <span class="ms-1">%</span>
                <button type="button" class="btn btn-danger" onclick="removeSubcontractor(${subcontractorCount})">حذف</button>
            </div>
        </div>`;
        $('#subcontractorList').append(newItem);
        $(`#subcontractor${subcontractorCount} .select2`).select2();
        
        // توزیع درصدها به طور خودکار
        distributePercentages();
    };

    // Remove subcontractor
    window.removeSubcontractor = function(id) {
        $(`#subcontractor${id}`).remove();
        subcontractorCount--;
        distributePercentages(); // Update percentages after removal
    };

    // Distribute percentages automatically
    $('#role_contractor').on('change', function() {
        if ($(this).val()) {
            distributePercentages();
        }
    });

    function distributePercentages() {
        const totalContractors = subcontractorCount + 1; // Including the main contractor
        const percentagePerContractor = (60 / totalContractors).toFixed(1);

        // Set percentage for main contractor
        $('input[name="c1p"]').val(percentagePerContractor);

        // Set percentage for all subcontractors
        for (let i = 1; i <= subcontractorCount; i++) {
            $(`input[name="c${i + 1}p"]`).val(percentagePerContractor);
        }

        // Update total percentage display
        calculateTotalPercentage();
    }

    function calculateTotalPercentage() {
        let total = 0;

        // Main contractor
        const c1p = parseFloat($('input[name="c1p"]').val()) || 0;
        total += c1p;

        // Subcontractors
        for (let i = 1; i <= subcontractorCount; i++) {
            const value = parseFloat($(`input[name="c${i + 1}p"]`).val()) || 0;
            total += value;
        }

        // Display total percentage
        $('#totalPercentage').text(total.toFixed(1));

        // Show warning if total percentage is not 60
        if (Math.abs(total - 60) > 0.1) {
            $('#percentageWarning').show();
            $('#percentageWarning').text(`مجموع درصدها باید دقیقاً 60% باشد (مجموع فعلی: ${total.toFixed(1)}%)`);
            $('#totalPercentageDisplay').removeClass('alert-info alert-success').addClass('alert-danger');
        } else {
            $('#percentageWarning').hide();
            $('#totalPercentageDisplay').removeClass('alert-info alert-danger').addClass('alert-success');
        }

        return total;
    }
});
</script>
</body>
</html>


