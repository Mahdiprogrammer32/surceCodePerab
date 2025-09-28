<?php
// تنظیمات دیتابیس  
$host = 'localhost';
$db   = 'fixwbcsq_perab';
$pass = 'mahdipass.2023';
$user = 'fixwbcsq_kakang';
$charset = 'utf8mb4';

// اتصال به دیتابیس  
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    error_log("Database Connection Error: " . $conn->connect_error);
    die("خطا در اتصال به دیتابیس.");
}

// تنظیم charset  
if (!$conn->set_charset($charset)) {
    error_log("Charset Setting Error: " . $conn->error);
}

// تابع تبدیل تاریخ میلادی به شمسی - تابع دقیق‌تر
function gregorian_to_jalali($gy, $gm, $gd)
{
    $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    $jy = ($gy <= 1600) ? 0 : 979;
    $gy -= ($gy <= 1600) ? 621 : 1600;
    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
    $days = (365 * $gy) + ((int)(($gy2 + 3) / 4)) - ((int)(($gy2 + 99) / 100))
        + ((int)(($gy2 + 399) / 400)) - 80 + $gd + $g_d_m[$gm - 1];
    $jy += 33 * ((int)($days / 12053));
    $days %= 12053;
    $jy += 4 * ((int)($days / 1461));
    $days %= 1461;
    $jy += (int)(($days - 1) / 365);
    if ($days > 365) $days = ($days - 1) % 365;
    $jm = ($days < 186) ? 1 + (int)($days / 31) : 7 + (int)(($days - 186) / 30);
    $jd = 1 + (($days < 186) ? ($days % 31) : (($days - 186) % 30));
    return [$jy, $jm, $jd];
}

// بررسی درخواست افزودن درصد
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_percent'])) {
    header('Content-Type: application/json');

    $name = trim($_POST['name']);
    $value = floatval($_POST['value']);
    $p_ojrat = isset($_POST['p_ojrat']) ? intval($_POST['p_ojrat']) : 0;
    $p_jens = isset($_POST['p_jens']) ? intval($_POST['p_jens']) : 0;

    if (empty($name) || $value < 0 || $value > 100) {
        echo json_encode(['status' => 'error', 'message' => 'مقدار نامعتبر است']);
        exit;
    }

    // تبدیل تاریخ میلادی به شمسی با ساعت دقیق
    $date = new DateTime('now', new DateTimeZone('Asia/Tehran'));

    // تبدیل تاریخ به شمسی
    $shamsi_date = gregorian_to_jalali(
        (int)$date->format('Y'),
        (int)$date->format('m'),
        (int)$date->format('d')
    );

    // افزودن ساعت، دقیقه و ثانیه به تاریخ شمسی
    $shamsi_formatted = $shamsi_date[0] . '/' . sprintf('%02d', $shamsi_date[1]) . '/' . sprintf('%02d', $shamsi_date[2]);
    $shamsi_formatted .= ' ' . $date->format('H:i:s');

    $sql = "INSERT INTO percentages (name, value, p_ojrat, p_jens, created_at_shamsi) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("SQL Prepare Error: " . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'خطای سرور']);
        exit;
    }

    $stmt->bind_param("sdiss", $name, $value, $p_ojrat, $p_jens, $shamsi_formatted);

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'درصد با موفقیت اضافه شد',
            'id' => $conn->insert_id,
            'shamsi_date' => $shamsi_formatted
        ]);
    } else {
        error_log("SQL Execution Error: " . $stmt->error);
        echo json_encode(['status' => 'error', 'message' => 'خطا در ثبت درصد']);
    }

    $stmt->close();
    exit;
}

// بررسی درخواست حذف درصد
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_percent'])) {
    header('Content-Type: application/json');

    $id = intval($_POST['id']);

    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'شناسه نامعتبر']);
        exit;
    }

    $sql = "UPDATE percentages SET status = 0 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("SQL Prepare Error: " . $conn->connect_error);
        echo json_encode(['status' => 'error', 'message' => 'خطای سرور']);
        exit;
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'درصد با موفقیت حذف شد']);
    } else {
        error_log("SQL Execution Error: " . $stmt->error);
        echo json_encode(['status' => 'error', 'message' => 'خطا در حذف درصد']);
    }

    $stmt->close();
    exit;
}

// بررسی درخواست ویرایش درصد
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_percent'])) {
    header('Content-Type: application/json');

    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $value = floatval($_POST['value']);
    $p_ojrat = isset($_POST['p_ojrat']) ? intval($_POST['p_ojrat']) : 0;
    $p_jens = isset($_POST['p_jens']) ? intval($_POST['p_jens']) : 0;

    if ($id <= 0 || empty($name) || $value < 0 || $value > 1000) {
        echo json_encode(['status' => 'error', 'message' => 'مقدار نامعتبر است']);
        exit;
    }

    $sql = "UPDATE percentages SET name = ?, value = ?, p_ojrat = ?, p_jens = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("SQL Prepare Error: " . $conn->error);
        echo json_encode(['status' => 'error', 'message' => 'خطای سرور']);
        exit;
    }

    $stmt->bind_param("sdiii", $name, $value, $p_ojrat, $p_jens, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'درصد با موفقیت ویرایش شد']);
    } else {
        error_log("SQL Execution Error: " . $stmt->error);
        echo json_encode(['status' => 'error', 'message' => 'خطا در ویرایش درصد']);
    }

    $stmt->close();
    exit;
}

// دریافت لیست درصدها
$sql = "SELECT id, name, value, p_ojrat, p_jens, created_at_shamsi FROM percentages WHERE status = 1 ORDER BY id DESC";
$result = $conn->query($sql);
$percentages = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت درصدها</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100;200;300;400;500;600;700;800;900&display=swap');

        body {
            background-color: #f8f9fa;
            font-family: 'Vazirmatn', sans-serif;
        }

        .dashboard {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .percent-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transform: translateX(100%);
            opacity: 0;
            animation: slideIn 0.5s forwards;
            transition: all 0.3s ease;
        }

        .percent-item:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            background: #f8f9fa;
        }

        @keyframes slideIn {
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .delete-animation {
            animation: fadeOut 0.5s forwards;
        }

        @keyframes fadeOut {
            to {
                transform: translateX(-100%);
                opacity: 0;
            }
        }

        .btn {
            transition: all 0.3s ease;
            border-radius: 8px;
            padding: 0.5rem 1rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: #4CAF50;
            border: none;
        }

        .btn-primary:hover {
            background: #45a049;
        }

        .btn-danger {
            background: #ff5252;
            border: none;
        }

        .btn-danger:hover {
            background: #ff3939;
        }

        .btn-warning {
            background: #ff9800;
            border: none;
        }

        .btn-warning:hover {
            background: #f57c00;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.7rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }

        .percent-value {
            font-weight: bold;
            color: #4CAF50;
        }

        .loading-spinner {
            animation: spin 1s infinite linear;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .section-title {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: #4CAF50;
            border-radius: 2px;
        }

        .date-info {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        .timestamp {
            margin-left: 5px;
            font-family: 'Vazirmatn', sans-serif;
            display: inline-block;
        }

        .additional-fields {
            font-size: 0.85rem;
            color: #495057;
            margin-top: 0.5rem;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .field-label {
            font-weight: 600;
            color: #007bff;
        }

        .row.g-3 {
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
<div class="dashboard animate__animated animate__fadeIn">
    <h2 class="text-center section-title">مدیریت درصدها</h2>

    <form id="percentForm" class="mb-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="percent_name" class="form-label">نام درصد</label>
                <select id="percent_name" class="form-control" required>
                    <option value="">انتخاب کنید...</option>
                    <option value="PA">پیمانکار-A</option>
                    <option value="PB">پیمانکار-B</option>
                    <option value="PC">پیمانکار-C</option>
                    <option value="AJ">انبار جنس</option>
                    <option value="khadamat">خدمات</option>
                    <option value="abzar">ابزار</option>
                    <option value="kalahabama">کالاهاباما</option>
                    <option value="generator">ژنراتور</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                    <option value="E">E</option>
                    <option value="F">F</option>
                    <option value="Anbardar">انباردار</option>
                    <option value="NazerFani">ناظر فنی</option>
                    <option value="NazerKefi">ناظر کیفی</option>
                    <option value="NazerArshad">ناظر ارشد</option>
                    <option value="Hesabdar">حسابدار</option>


                </select>
            </div>
            <div class="col-md-6">
                <label for="percent_value" class="form-label">مقدار درصد</label>
                <input type="number" id="percent_value" class="form-control" placeholder="مقدار درصد را وارد کنید" min="0" max="100" step="0.1" required>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="p_ojrat" class="form-label">درصد اجرت</label>
                <input type="number" id="p_ojrat" class="form-control" placeholder="درصد اجرت" min="0" max="100" step="1" value="0">
            </div>
            <div class="col-md-6">
                <label for="p_jens" class="form-label">درصد جنس</label>
                <input type="number" id="p_jens" class="form-control" placeholder="درصد جنس" min="0" max="100" step="1" value="0">
            </div>
        </div>
        <button type="button" id="addPercentBtn" class="btn btn-primary w-100 mt-3">
            افزودن درصد <i class="fas fa-plus ms-2"></i>
        </button>
    </form>

    <div id="percentList">
        <?php foreach ($percentages as $percent): ?>
            <div class="percent-item" data-id="<?php echo $percent['id']; ?>">
                    <span>
                        <?php echo htmlspecialchars($percent['name']); ?> -
                        <span class="percent-value"><?php echo $percent['value']; ?>%</span>
                        <div class="additional-fields">
                            <span class="field-label">اجرت:</span> <?php echo $percent['p_ojrat']; ?>% |
                            <span class="field-label">جنس:</span> <?php echo $percent['p_jens']; ?>%
                        </div>
                        <div class="date-info">
                            <i class="fas fa-calendar-alt me-1"></i> تاریخ ثبت:
                            <?php
                            // جداسازی تاریخ و ساعت
                            $datetime_parts = explode(' ', $percent['created_at_shamsi']);
                            $date_part = $datetime_parts[0];
                            $time_part = isset($datetime_parts[1]) ? $datetime_parts[1] : '';
                            ?>
                            <span><?php echo $date_part; ?></span>
                            <?php if (!empty($time_part)): ?>
                                <i class="fas fa-clock ms-2 me-1"></i>
                                <span class="timestamp"><?php echo $time_part; ?></span>
                            <?php endif; ?>
                        </div>
                    </span>
                <div>
                    <button class="btn btn-warning btn-sm edit-btn me-2" data-bs-toggle="modal" data-bs-target="#editModal"
                            data-id="<?php echo $percent['id']; ?>"
                            data-name="<?php echo htmlspecialchars($percent['name']); ?>"
                            data-value="<?php echo $percent['value']; ?>"
                            data-p-ojrat="<?php echo $percent['p_ojrat']; ?>"
                            data-p-jens="<?php echo $percent['p_jens']; ?>">
                        ویرایش <i class="fas fa-edit me-1"></i>
                    </button>
                    <button class="btn btn-danger btn-sm delete-btn">
                        حذف <i class="fas fa-trash-alt me-1"></i>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal for Editing Percent -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">ویرایش درصد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editPercentForm">
                    <input type="hidden" id="edit_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_percent_name" class="form-label">نام درصد</label>
                            <select id="edit_percent_name" class="form-control" required>
                                <option value="PA">پیمانکار-A</option>
                                <option value="PB">پیمانکار-B</option>
                                <option value="PC">پیمانکار-C</option>
                                <option value="AJ">انبار جنس</option>
                                <option value="khadamat">خدمات</option>
                                <option value="abzar">ابزار</option>
                                <option value="kalahabama">کالاهاباما</option>
                                <option value="generator">ژنراتور</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                                <option value="E">E</option>
                                <option value="F">F</option>
                                <option value="Anbardar">انباردار</option>
                                <option value="NazerFani">ناظر فنی</option>
                                <option value="NazerKefi">ناظر کیفی</option>
                                <option value="NazerArshad">ناظر ارشد</option>
                                <option value="Hesabdar">حسابدار</option>

                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_percent_value" class="form-label">مقدار درصد</label>
                            <input type="number" id="edit_percent_value" class="form-control" placeholder="مقدار درصد را وارد کنید" min="0" max="1000" step="0.1" required>
                        </div>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label for="edit_p_ojrat" class="form-label">درصد اجرت</label>
                            <input type="number" id="edit_p_ojrat" class="form-control" placeholder="درصد اجرت" min="0" max="100" step="1">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_p_jens" class="form-label">درصد جنس</label>
                            <input type="number" id="edit_p_jens" class="form-control" placeholder="درصد جنس" min="0" max="100" step="1">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                <button type="button" id="saveEditBtn" class="btn btn-primary">ذخیره تغییرات</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // تنظیمات پیش‌فرض برای SweetAlert2
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // اعمال select2 به عنصر select
        $('#percent_name').select2({
            dir: 'rtl', // جهت راست به چپ
            placeholder: 'لطفا نام درصد را انتخاب کنید',
            allowClear: true
        });

        $("#addPercentBtn").click(function() {
            let btn = $(this);
            let name = $("#percent_name").val();
            let value = $("#percent_value").val();
            let p_ojrat = $("#p_ojrat").val() || 0;
            let p_jens = $("#p_jens").val() || 0;

            if (!name || !value) {
                Toast.fire({
                    icon: 'error',
                    title: 'لطفا تمام فیلدهای اجباری را پر کنید'
                });
                return;
            }

            btn.prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin"></i> در حال افزودن...');

            $.post(window.location.href, {
                add_percent: true,
                name: name,
                value: value,
                p_ojrat: p_ojrat,
                p_jens: p_jens
            }, function(response) {
                if (response.status === 'success') {
                    // جداسازی تاریخ و ساعت دریافتی
                    let datetime_parts = response.shamsi_date.split(' ');
                    let date_part = datetime_parts[0];
                    let time_part = datetime_parts[1] || '';

                    let timeDisplay = '';
                    if (time_part) {
                        timeDisplay = `<i class="fas fa-clock ms-2 me-1"></i><span class="timestamp">${time_part}</span>`;
                    }

                    let newItem = $(`
                            <div class="percent-item animate__animated animate__fadeInRight" data-id="${response.id}">
                                <span>
                                    ${name} -
                                    <span class="percent-value">${value}%</span>
                                    <div class="additional-fields">
                                        <span class="field-label">اجرت:</span> ${p_ojrat}% |
                                        <span class="field-label">جنس:</span> ${p_jens}%
                                    </div>
                                    <div class="date-info">
                                        <i class="fas fa-calendar-alt me-1"></i> تاریخ ثبت:
                                        <span>${date_part}</span>
                                        ${timeDisplay}
                                    </div>
                                </span>
                                <div>
                                    <button class="btn btn-warning btn-sm edit-btn me-2" data-bs-toggle="modal" data-bs-target="#editModal"
                                            data-id="${response.id}" data-name="${name}" data-value="${value}"
                                            data-p-ojrat="${p_ojrat}" data-p-jens="${p_jens}">
                                        ویرایش <i class="fas fa-edit me-1"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm delete-btn">
                                        حذف <i class="fas fa-trash-alt me-1"></i>
                                    </button>
                                </div>
                            </div>
                        `);

                    $("#percentList").prepend(newItem);
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });

                    $("#percent_name, #percent_value, #p_ojrat, #p_jens").val('');
                    $("#percent_name").trigger('change');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطا!',
                        text: response.message,
                        confirmButtonText: 'باشه'
                    });
                }
            }, "json")
                .always(function() {
                    btn.prop('disabled', false)
                        .html('افزودن درصد <i class="fas fa-plus ms-2"></i>');
                });
        });

        $(document).on("click", ".delete-btn", function() {
            let item = $(this).closest('.percent-item');
            let id = item.data('id');

            Swal.fire({
                title: 'آیا مطمئن هستید؟',
                text: "این عملیات قابل بازگشت نیست!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'انصراف',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(window.location.href, {
                        delete_percent: true,
                        id: id
                    }, function(response) {
                        if (response.status === 'success') {
                            item.addClass('delete-animation');
                            setTimeout(() => item.remove(), 500);
                            Toast.fire({
                                icon: 'success',
                                title: response.message
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'خطا!',
                                text: response.message,
                                confirmButtonText: 'باشه'
                            });
                        }
                    }, "json");
                }
            });
        });

        // پر کردن فرم ویرایش در مودال
        $(document).on("click", ".edit-btn", function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            let value = $(this).data('value');
            let p_ojrat = $(this).data('p-ojrat');
            let p_jens = $(this).data('p-jens');

            $("#edit_id").val(id);
            $("#edit_percent_name").val(name).trigger('change');
            $("#edit_percent_value").val(value);
            $("#edit_p_ojrat").val(p_ojrat);
            $("#edit_p_jens").val(p_jens);
        });

        // ذخیره تغییرات ویرایش
        $("#saveEditBtn").click(function() {
            let id = $("#edit_id").val();
            let name = $("#edit_percent_name").val();
            let value = $("#edit_percent_value").val();
            let p_ojrat = $("#edit_p_ojrat").val() || 0;
            let p_jens = $("#edit_p_jens").val() || 0;

            if (!name || !value) {
                Toast.fire({
                    icon: 'error',
                    title: 'لطفا تمام فیلدهای اجباری را پر کنید'
                });
                return;
            }

            $.post(window.location.href, {
                edit_percent: true,
                id: id,
                name: name,
                value: value,
                p_ojrat: p_ojrat,
                p_jens: p_jens
            }, function(response) {
                if (response.status === 'success') {
                    let item = $(`.percent-item[data-id='${id}']`);

                    // به‌روزرسانی محتوای آیتم
                    let itemContent = `
                            ${name} -
                            <span class="percent-value">${value}%</span>
                            <div class="additional-fields">
                                <span class="field-label">اجرت:</span> ${p_ojrat}% |
                                <span class="field-label">جنس:</span> ${p_jens}%
                            </div>
                        `;

                    // حفظ بخش تاریخ
                    let dateInfo = item.find('.date-info').prop('outerHTML');
                    item.find('span:first-child').html(itemContent + dateInfo);

                    // به‌روزرسانی data attributes دکمه ویرایش
                    item.find('.edit-btn').attr({
                        'data-name': name,
                        'data-value': value,
                        'data-p-ojrat': p_ojrat,
                        'data-p-jens': p_jens
                    });

                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                    $('#editModal').modal('hide');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطا!',
                        text: response.message,
                        confirmButtonText: 'باشه'
                    });
                }
            }, "json");
        });

        // اضافه کردن انیمیشن به فرم ورودی
        $("#percentForm input, #percentForm select").focus(function() {
            $(this).addClass('animate__animated animate__pulse');
        }).blur(function() {
            $(this).removeClass('animate__animated animate__pulse');
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>