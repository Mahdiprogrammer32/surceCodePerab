<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
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

// Function to fetch options for a select field
function fetchOptions($table, $idField, $displayFields) {
    global $conn;
    $options = '';
    $sql = "SELECT * FROM $table";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $displayText = '';
            foreach ($displayFields as $field) {
                $displayText .= htmlspecialchars($row[$field] ?? '') . ' ';
            }
            $options .= '<option value="' . htmlspecialchars($row[$idField] ?? '') . '">' . trim($displayText) . '</option>';
        }
    } else {
        $options .= '<option value="">هیچ کاربری یافت نشد</option>';
    }
    return $options;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role_employer = filter_input(INPUT_POST, 'role_employer', FILTER_SANITIZE_NUMBER_INT);
    $role_contractor = filter_input(INPUT_POST, 'role_contractor', FILTER_SANITIZE_NUMBER_INT);

    $subcontractors = array_map(function ($i) {
        return filter_input(INPUT_POST, 'subcontractor' . $i, FILTER_SANITIZE_NUMBER_INT) ?: null;
    }, range(1, 5));
    $subcontractors = array_filter($subcontractors);

    $phase_desc = filter_input(INPUT_POST, 'phase_desc', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $budget = filter_input(INPUT_POST, 'budget', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    // اعتبارسنجی داده‌ها
    if (empty($role_employer) || empty($phase_desc)) {
        showAlert('هشدار!', 'لطفاً همه فیلدهای اجباری را پر کنید.', 'warning');
    } else {
        // بررسی وجود پروژه تکراری
        $checkSql = "SELECT COUNT(*) FROM projects WHERE employer = ? AND contractor1 = ? AND phase_description = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("iis", $role_employer, $role_contractor, $phase_desc);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            showAlert('هشدار!', 'پروژه‌ای با این مشخصات از قبل وجود دارد.', 'warning');
        } else {
            // اگر بودجه خالی بود، به جای آن NULL قرار می‌دهیم
            $budget = empty($budget) ? null : $budget;

            // آماده‌سازی و اجرای دستور SQL برای درج داده‌ها
            $sql = "INSERT INTO projects (employer, contractor1, subcontractor1, subcontractor2, subcontractor3, subcontractor4, subcontractor5, phase_description, budget) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            if ($stmt = $conn->prepare($sql)) {
                // Prepare the subcontractor values
                $subcontractor1 = $subcontractors[0] ?? null;
                $subcontractor2 = $subcontractors[1] ?? null;
                $subcontractor3 = $subcontractors[2] ?? null;
                $subcontractor4 = $subcontractors[3] ?? null;
                $subcontractor5 = $subcontractors[4] ?? null;

                // Bind parameters
                $stmt->bind_param("iiisssssi", $role_employer, $role_contractor, $subcontractor1, $subcontractor2, $subcontractor3, $subcontractor4, $subcontractor5, $phase_desc, $budget);

                if ($stmt->execute()) {
                    echo '<script>
                            Swal.fire({
                                icon: "success",
                                title: "موفقیت آمیز!",
                                text: "پروژه جدید با موفقیت ثبت شد."
                            }).then(() => {
                                window.location.href = "projects.php?status=0&priority=on&abc=0&progres=on&start_date=&end_date=";
                            });
                          </script>';
                } else {
                    showAlert('خطا!', 'خطایی در ثبت اطلاعات رخ داده است: ' . htmlspecialchars($stmt->error), 'error');
                }

                $stmt->close();
            } else {
                showAlert('خطا!', 'خطا در آماده‌سازی دستور: ' . htmlspecialchars($conn->error), 'error');
            }
        }
    }
}
?>

<!doctype html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ثبت پروژه</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
            direction:rtl;
        }
        .container {
            margin-top: 50px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            padding: 30px;
        }
        .form-label {
            font-weight: bold;
            color: #495057;
        }
        .form-select {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .form-select:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .btn-success, .btn-danger {
            width: 100px;
        }
        .btn-add {
            width: auto;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>
<div class="container">
    <h2 class="text-center mb-4">فرم ثبت اطلاعات</h2>
    <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>

        <div class="mb-3">
            <label for="priority" class="form-label">اولویت پروژه:</label>
            <select class="select2-single form-select" id="priority" name="priority" required>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
            </select>
            <div class="invalid-feedback">لطفاً اولویت پروژه را انتخاب کنید.</div>
        </div>

        <div class="mb-3">
            <label for="role_employer" class="form-label">کارفرما:</label>
            <select class="select2-single form-select" id="role_employer" name="role_employer" required>
                <option value="">انتخاب کنید...</option>
                <?php echo fetchOptions('employees', 'id', ['manager', 'factory']); ?>
            </select>
            <div class="invalid-feedback">لطفاً کارفرما خود را انتخاب کنید.</div>
            <a href="add_project_page.php" class="text-white btn btn-success mt-2">جدید</a>
        </div>

        <div class="mb-3">
            <label for="role_contractor" class="form-label">پیمانکار:</label>
            <select class="select2-single form-select" id="role_contractor" name="role_contractor" required>
                <option value="">انتخاب کنید...</option>
                <?php echo fetchOptions('contractors', 'id', ['name', 'family']); ?>
            </select>
            <div class="invalid-feedback">لطفاً پیمانکار خود را انتخاب کنید.</div>
        </div>

        <div id="subcontractorList" class="mt-2">
            <!-- ورودی‌های وردست به اینجا اضافه می‌شوند -->
        </div>
        <button type="button" onclick="addSelect('subcontractor', 'وردست')" class="btn btn-add btn-success mb-3">افزودن وردست</button>

        <div class="mb-3">
            <label for="phase_desc" class="form-label">شرح فاز:</label>
            <textarea class="form-control" id="phase_desc" name="phase_desc" rows="3" required></textarea>
            <div class="invalid-feedback">لطفاً شرح فاز را وارد کنید.</div>
        </div>

        <div class="mb-3">
            <label for="budget" class="form-label">بودجه:</label>
            <input type="number" step="0.01" class="form-control" id="budget" name="budget">
            <div class="invalid-feedback">لطفاً بودجه را وارد کنید.</div>
        </div>

        <button type="submit" class="btn btn-primary">ثبت پروژه</button>
    </form>
</div>

<script>
    $(document).ready(function () {
        $('.select2-single').select2({
            width: '100%', // تنظیم عرض select2
            placeholder: 'انتخاب کنید...', // متن پیش‌فرض
            allowClear: true // اجازه پاک کردن انتخاب
        });

        // تابع افزودن ورودی
        window.addSelect = function(type, label) {
            const maxCount = 5;
            const currentCount = document.querySelectorAll(`#${type}List select`).length;

            if (currentCount >= maxCount) {
                Swal.fire({
                    icon: "error",
                    title: "خطا!",
                    text: `${label} نمی‌تواند بیشتر از ${maxCount} باشد.`
                });
                return;
            }

            const container = document.getElementById(type + 'List');
            const newSelect = document.createElement('div');
            newSelect.classList.add('mb-3');
            const uniqueId = `${type}${currentCount + 1}`;
            newSelect.innerHTML = `
            <label class="form-label">${label} ${currentCount + 1}:</label>
            <div class="d-flex">
                <select class="select2-single form-select" id="${uniqueId}" name="${uniqueId}" required>
                    <option value="">انتخاب کنید...</option>
                    <?php echo addslashes(fetchOptions('contractors', 'id', ['name', 'family'])); ?>
                </select>
                <button type="button" class="btn btn-danger ms-2" onclick="removeSelect(this)">حذف</button>
            </div>
        `;
            container.appendChild(newSelect);
            $(newSelect).find('.select2-single').select2({
                width: '100%', // تنظیم عرض select2
                placeholder: 'انتخاب کنید...', // متن پیش‌فرض
                allowClear: true // اجازه پاک کردن انتخاب
            });
            scrollToField(newSelect);
        }

        // تابع حذف ورودی
        window.removeSelect = function(button) {
            const container = button.closest('.mb-3');
            container.remove();
        }

        // تابع اسکرول به فیلد جدید
        function scrollToField(field) {
            field.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
</script>

</body>
</html>