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
        $options .= '<option value="">هيچ کاربري يافت نشد</option>';
    }
    return $options;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role_employer = filter_input(INPUT_POST, 'role_employer', FILTER_SANITIZE_NUMBER_INT);
    $role_contractor = filter_input(INPUT_POST, 'role_contractor', FILTER_SANITIZE_NUMBER_INT);
    $priority = $_POST['priority'];

    // دریافت درصد‌ها از فرم
    $c1p = filter_input(INPUT_POST, 'c1p', FILTER_VALIDATE_FLOAT) ?? 0;
    $c2p = filter_input(INPUT_POST, 'c2p', FILTER_VALIDATE_FLOAT) ?? 0;
    $c3p = filter_input(INPUT_POST, 'c3p', FILTER_VALIDATE_FLOAT) ?? 0;
    $c4p = filter_input(INPUT_POST, 'c4p', FILTER_VALIDATE_FLOAT) ?? 0;
    $c5p = filter_input(INPUT_POST, 'c5p', FILTER_VALIDATE_FLOAT) ?? 0;

    // محاسبه مجموع درصدها
    $totalPercentage = $c1p + $c2p + $c3p + $c4p + $c5p;

    // بررسی محدودیت مجموع درصدها
    if ($totalPercentage > 60) {
        showAlert('خطا', 'مجموع درصدها نباید بیشتر از 60 باشد. مجموع فعلی: ' . $totalPercentage, 'error');
        exit;
    }
    if ($totalPercentage < 60) {
        showAlert('خطا', 'مجموع درصد ها نباید کمتر از 60 باشد. مجموع فعلی: ' . $totalPercentage, 'error');
        exit;
    }

    // دریافت پیمانکاران فرعی
    $subcontractor1 = filter_input(INPUT_POST, 'subcontractor1', FILTER_SANITIZE_NUMBER_INT) ?: null;
    $subcontractor2 = filter_input(INPUT_POST, 'subcontractor2', FILTER_SANITIZE_NUMBER_INT) ?: null;
    $subcontractor3 = filter_input(INPUT_POST, 'subcontractor3', FILTER_SANITIZE_NUMBER_INT) ?: null;
    $subcontractor4 = filter_input(INPUT_POST, 'subcontractor4', FILTER_SANITIZE_NUMBER_INT) ?: null;

    $phase_desc = filter_input(INPUT_POST, 'phase_desc', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $budget = filter_input(INPUT_POST, 'budget', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    // اعتبارسنجي داده‌ها
    if (empty($role_employer) || empty($phase_desc)) {
        showAlert('هشدار!', 'لطفاً همه فيلدهاي اجباري را پر کنيد.', 'warning');
    } else {
        // بررسي وجود پروژه تکراري
        $checkSql = "SELECT COUNT(*) FROM projects WHERE employer = ? AND contractor1 = ? AND phase_description = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("iis", $role_employer, $role_contractor, $phase_desc);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            showAlert('هشدار!', 'پروژه‌اي با اين مشخصات از قبل وجود دارد.', 'warning');
        } else {
            // اگر بودجه خالي بود، به جاي آن NULL قرار مي‌دهيم
            $budget = empty($budget) ? null : $budget;

            // محاسبه درصد پیشرفت اولیه بر اساس تعریف کارفرما و پیمانکار
            $initialProgress = 0;

            if (!empty($role_employer)) {
                $initialProgress += 5; // اگر کارفرما تعریف شده باشد، 5% اضافه می‌شود
            }

            if (!empty($role_contractor)) {
                $initialProgress += 5; // اگر پیمانکار تعریف شده باشد، 5% اضافه می‌شود
            }

            $sql = "INSERT INTO projects (employer, contractor1, contractor2, contractor3, contractor4, contractor5, c1p, c2p, c3p, c4p, c5p, phase_description, budget, priority, progress) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            if ($stmt = $conn->prepare($sql)) {
                // Bind parameters
                $stmt->bind_param("iiiiidddddssssi",
                    $role_employer,
                    $role_contractor,
                    $subcontractor1,
                    $subcontractor2,
                    $subcontractor3,
                    $subcontractor4,
                    $c1p,
                    $c2p,
                    $c3p,
                    $c4p,
                    $c5p,
                    $phase_desc,
                    $budget,
                    $priority,
                    $initialProgress
                );

                if ($stmt->execute()) {
                    echo '<script>
                            Swal.fire({
                                icon: "success",
                                title: "موفقيت آميز!",
                                text: "پروژه جديد با موفقيت ثبت شد."
                            }).then(() => {
                                window.location.href = "projects.php?status=0&priority=on&abc=0&progres=on&start_date=&end_date=";
                            });
                          </script>';
                } else {
                    showAlert('خطا!', 'خطايي در ثبت اطلاعات رخ داده است: ' . htmlspecialchars($stmt->error), 'error');
                }

                $stmt->close();
            } else {
                showAlert('خطا!', 'خطا در آماده‌سازي دستور: ' . htmlspecialchars($conn->error), 'error');
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
        .percentage-input {
            width: 100px;
            margin-left: 10px;
        }
        .total-percentage {
            margin-top: 10px;
            font-weight: bold;
            color: #495057;
        }
        .percentage-warning {
            color: red;
            display: none;
        }
        .select2-container--default .select2-selection--single {
            height: calc(1.5em + 0.75rem + 2px);
            padding: 0.375rem 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: calc(1.5em + 0.75rem);
            direction: rtl;
            text-align: right;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            left: 10px;
            right: auto;
        }
        .select2-container--default .select2-search--inline .select2-search__field {
            direction: rtl;
            text-align: right;
            width: 100%;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="container">
    <h2 class="text-center mb-4">فرم ثبت اطلاعات</h2>
    <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>

        <div class="mb-3">
            <label for="priority" class="form-label">اولويت پروژه:</label>
            <select class="select2-single form-select" id="priority" name="priority" required>
                <option value="D">پيش‌فرض </option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
            </select>
            <div class="invalid-feedback">لطفاً اولويت پروژه را انتخاب کنيد.</div>
        </div>

        <div class="mb-3">
            <label for="role_employer" class="form-label">کارفرما:</label>
            <select class="select2-single form-select" id="role_employer" name="role_employer" required>
                <option value="">انتخاب کنيد...</option>
                <?php echo fetchOptions('employees', 'id', ['manager', 'factory']); ?>
            </select>
            <div class="invalid-feedback">لطفاً کارفرما خود را انتخاب کنيد.</div>
            <a href="add_oders_page.php" class="text-white btn btn-success mt-2">جديد</a>
        </div>

        <!-- پیمانکار اصلی با درصد -->
        <div class="mb-3">
            <label for="role_contractor" class="form-label">پيمانکار اصلی:</label>
            <div class="d-flex align-items-center">
                <select class="select2-single form-select" id="role_contractor" name="role_contractor" required>
                    <option value="">انتخاب کنيد...</option>
                    <?php echo fetchOptions('users', 'id', ['name', 'family']); ?>
                </select>
                <input type="number" class="form-control percentage-input" name="c1p" id="c1p" placeholder="درصد" min="0" max="60" required onchange="calculateTotalPercentage()">
            </div>
            <div class="invalid-feedback">لطفاً پيمانکار خود را انتخاب کنيد.</div>
        </div>

        <div id="subcontractorList" class="mt-2">
            <!-- ورودي‌هاي وردست به اينجا اضافه مي‌شوند -->
        </div>

        <div class="total-percentage mb-3">
            مجموع درصدها: <span id="totalPercentage">0</span>%
            <div class="percentage-warning" id="percentageWarning">مجموع درصدها باید دقیقاً 60% باشد</div>
        </div>

        <button type="button" onclick="addSubcontractor()" class="btn btn-add btn-success mb-3">افزودن وردست</button>

        <div class="mb-3">
            <label for="phase_desc" class="form-label">شرح فاز:</label>
            <textarea class="form-control" id="phase_desc" name="phase_desc" rows="3" required></textarea>
            <div class="invalid-feedback">لطفاً شرح فاز را وارد کنيد.</div>
        </div>

        <div class="mb-3" style="display:none;">
            <label for="budget" class="form-label">بودجه:</label>
            <input type="number" step="0.01" class="form-control" id="budget" name="budget">
            <div class="invalid-feedback">لطفاً بودجه را وارد کنيد.</div>
        </div>

        <button type="submit" class="btn btn-primary">ثبت پروژه</button>
    </form>
</div>

<script>
    $(document).ready(function () {
        // تنظیم Select2 برای همه فیلدهای select با قابلیت تایپ مستقیم
        $('.select2-single').select2({
            width: '100%',
            placeholder: 'انتخاب کنید...',
            allowClear: true,
            tags: false, // غیرفعال کردن افزودن گزینه‌های جدید
            minimumResultsForSearch: 0, // نمایش فوری باکس جستجو
            selectOnClose: true, // انتخاب خودکار گزینه هنگام بستن
            language: {
                noResults: function () {
                    return 'نتیجه‌ای یافت نشد';
                },
                searching: function () {
                    return 'در حال جستجو...';
                },
                inputTooShort: function () {
                    return 'لطفاً حداقل یک کاراکتر وارد کنید';
                }
            },
            dir: 'rtl', // تنظیم جهت به راست برای پشتیبانی از فارسی
            dropdownAutoWidth: true,
            matcher: function(params, data) {
                // تابع جستجوی سفارشی برای پشتیبانی از فارسی
                if ($.trim(params.term) === '') {
                    return data;
                }
                if (typeof data.text === 'undefined') {
                    return null;
                }
                // جستجوی حساس به فارسی
                if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
                    return data;
                }
                return null;
            }
        });

        // تنظیم درصد پیمانکار اصلی به 60% به صورت پیش‌فرض
        $('#c1p').val(60);
        calculateTotalPercentage();

        // وقتی پیمانکار اصلی انتخاب می‌شود، درصد را تنظیم کن
        $('#role_contractor').on('change', function() {
            if ($(this).val()) {
                distributePercentages();
            }
        });

        // اطمینان از اینکه باکس جستجو در خود select ظاهر می‌شود
        $('.select2-single').on('select2:open', function() {
            $('.select2-search__field').focus();
        });
    });

    // متغیر برای نگهداری تعداد وردست‌ها
    let subcontractorCount = 0;

    // تابع برای تقسیم خودکار درصدها
    function distributePercentages() {
        // تعداد کل پیمانکاران (پیمانکار اصلی + وردست‌ها)
        const totalContractors = 1 + subcontractorCount;

        // اگر پیمانکاری وجود ندارد، کاری انجام نده
        if (totalContractors === 0) return;

        // درصد برای هر پیمانکار (مجموع 60 درصد تقسیم بر تعداد پیمانکاران)
        const percentagePerContractor = (60 / totalContractors).toFixed(1);

        // تنظیم درصد برای پیمانکار اصلی
        $('#c1p').val(percentagePerContractor);

        // تنظیم درصد برای وردست‌ها
        for (let i = 1; i <= subcontractorCount; i++) {
            $(`#c${i+1}p`).val(percentagePerContractor);
        }

        // بروزرسانی نمایش مجموع درصدها
        calculateTotalPercentage();
    }

    // تابع برای افزودن وردست جدید
    function addSubcontractor() {
        if (subcontractorCount >= 4) {
            Swal.fire({
                icon: "error",
                title: "خطا!",
                text: "حداکثر ۴ وردست مجاز است"
            });
            return;
        }

        subcontractorCount++;
        const newId = subcontractorCount;

        const html = `
            <div class="mb-3 subcontractor-item" id="subcontractor${newId}">
                <label class="form-label">وردست ${newId}:</label>
                <div class="d-flex align-items-center">
                    <select class="form-select select2-single" id="subcontractor${newId}" name="subcontractor${newId}">
                        <option value="">انتخاب کنید...</option>
                        <?php echo addslashes(fetchOptions('users', 'id', ['name', 'family'])); ?>
                    </select>
                    <input type="number" class="form-control percentage-input" name="c${newId+1}p" id="c${newId+1}p" placeholder="درصد" min="0" max="60" step="0.1" required onchange="calculateTotalPercentage()">
                    <button type="button" class="btn btn-danger ms-2" onclick="removeSubcontractor(${newId})">حذف</button>
                </div>
            </div>
        `;

        $('#subcontractorList').append(html);
        $(`#subcontractor${newId} .select2-single`).select2({
            width: '100%',
            placeholder: 'انتخاب کنید...',
            allowClear: true,
            tags: false,
            minimumResultsForSearch: 0,
            selectOnClose: true,
            language: {
                noResults: function () {
                    return 'نتیجه‌ای یافت نشد';
                },
                searching: function () {
                    return 'در حال جستجو...';
                },
                inputTooShort: function () {
                    return 'لطفاً حداقل یک کاراکتر وارد کنید';
                }
            },
            dir: 'rtl',
            dropdownAutoWidth: true,
            matcher: function(params, data) {
                if ($.trim(params.term) === '') {
                    return data;
                }
                if (typeof data.text === 'undefined') {
                    return null;
                }
                if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
                    return data;
                }
                return null;
            }
        });

        // توزیع خودکار درصدها بعد از اضافه کردن وردست
        distributePercentages();
    }

    // تابع برای حذف وردست
    function removeSubcontractor(id) {
        $(`#subcontractor${id}`).remove();

        // بازنشانی شماره گذاری
        let newCount = 0;
        $('.subcontractor-item').each(function(index) {
            newCount++;
            const newId = newCount;
            $(this).attr('id', 'subcontractor' + newId);
            $(this).find('label').text('وردست ' + newId + ':');
            $(this).find('select').attr({
                'id': 'subcontractor' + newId,
                'name': 'subcontractor' + newId
            });
            $(this).find('input').attr({
                'id': 'c' + (newId + 1) + 'p',
                'name': 'c' + (newId + 1) + 'p'
            });
            $(this).find('button').attr('onclick', 'removeSubcontractor(' + newId + ')');
        });

        subcontractorCount = newCount;

        // توزیع خودکار درصدها بعد از حذف وردست
        distributePercentages();
    }

    // تابع برای محاسبه مجموع درصدها
    function calculateTotalPercentage() {
        let total = 0;

        // پیمانکار اصلی
        const c1p = parseFloat($('#c1p').val()) || 0;
        total += c1p;

        // وردست‌ها
        for (let i = 1; i <= 4; i++) {
            const value = parseFloat($(`#c${i + 1}p`).val()) || 0;
            total += value;
        }

        $('#totalPercentage').text(total.toFixed(1));

        // نمایش هشدار اگر مجموع درصدها 60 نباشد
        if (Math.abs(total - 60) > 0.1) {
            $('#percentageWarning').show();
            $('#percentageWarning').text(`مجموع درصدها باید دقیقاً 60% باشد (مجموع فعلی: ${total.toFixed(1)}%)`);
        } else {
            $('#percentageWarning').hide();
        }
    }

    // اعتبارسنجی فرم قبل از ارسال
    document.querySelector('form').addEventListener('submit', function(event) {
        calculateTotalPercentage();
        const total = parseFloat($('#totalPercentage').text());

        if (Math.abs(total - 60) > 0.1) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'خطا!',
                text: `مجموع درصدها باید دقیقاً 60% باشد (مجموع فعلی: ${total.toFixed(1)}%)`
            });
        }
    });
</script>

</body>
</html>