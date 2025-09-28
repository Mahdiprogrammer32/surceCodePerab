<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
require_once "database.php";
global $conn;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// بررسی وجود جدول users
$tableName = "users";
$checkTableQuery = "SHOW TABLES LIKE '$tableName'";
$result = $conn->query($checkTableQuery);

// Function to generate a unique license
function generateLicense($phone, $secretKey) {
    return hash_hmac('sha256', $phone, $secretKey);
}

// Function to check if the license is unique
function isLicenseUnique($license, $conn) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE license_key = ?");
    $stmt->bind_param("s", $license);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows === 0;
}

if ($result->num_rows == 0) {
    echo '<script>
        Swal.fire({
            icon: "error",
            title: "خطا!",
            text: "جدول users وجود ندارد."
        });
    </script>';
    die();
} else {
    $sql = "SELECT COUNT(*) AS total FROM $tableName";
    $result1 = $conn->query($sql);
    if ($result1) {
        $row = $result1->fetch_assoc();
        $totalEmployees = $row['total'] + 1;
    } else {
        echo '<script>
            Swal.fire({
                icon: "error",
                title: "خطا!",
                text: "خطایی در اجرای کوئری رخ داده است: ' . $conn->error . '"
            });
        </script>';
        die();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // اعتبارسنجی اولیه داده‌ها
    $name = htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8');
    $family = htmlspecialchars($_POST['family'] ?? '', ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8');
    $address = htmlspecialchars($_POST['address'] ?? '', ENT_QUOTES, 'UTF-8');
    $level = htmlspecialchars($_POST['level'] ?? '', ENT_QUOTES, 'UTF-8');
    
    $role = "پیمانکار";
    $project_id = $totalEmployees;
    $form = "پیمانکاران";

    if (empty($name) || empty($family) || empty($phone) || empty($address)) {
        echo '<script>
            Swal.fire({
                icon: "error",
                title: "خطا!",
                text: "لطفا تمامی فیلدها را پر کنید."
            });
        </script>';
    } else {
        $secretKey = 'kalahabama'; // کلید مخفی (این را تغییر دهید)
        $license = generateLicense($phone, $secretKey);

        // بررسی تکراری نبودن لایسنس
        while (!isLicenseUnique($license, $conn)) {
            // اگر لایسنس تکراری باشد، لایسنس جدید تولید می‌کنیم
            $license = generateLicense($phone . rand(1, 1000), $secretKey); // اضافه کردن یک عدد تصادفی به تلفن
        }

        // بررسی وجود کارمند با تلفن مشابه
        $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "خطا!",
                    text: "شماره تلفن تکراری می‌باشد."
                });
            </script>';
        } else {
            // درج رکورد جدید
            $stmt = $conn->prepare("INSERT INTO users (name, family, phone, address, role, project_id, form, license_key, level) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssissssss", $name, $family, $phone, $address, $role, $project_id, $form, $license, $level);

            if ($stmt->execute()) {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "موفقیت آمیز!",
                        text: "پیمانکار جدید با موفقیت ثبت شد."
                    }).then(() => {
                        setTimeout(() => {
                            window.location.href = "contractor.php";
                        }, 1000);
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "خطا!",
                        text: "خطایی در ثبت اطلاعات رخ داده است: ' . $stmt->error . '"
                    });
                </script>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فرم ثبت‌نام پیمانکار</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="b_icons/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="fontA/css/all.min.css">
    <link rel="stylesheet" href="assets/css/checkout.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/style_project.css">
    <link rel="stylesheet" href="assets/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://unpkg.com/ag-grid-community/styles/ag-grid.css">
    <link rel="stylesheet" href="https://unpkg.com/ag-grid-community/styles/ag-theme-alpine.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Vazir', Tahoma, Arial, sans-serif;
        }
        
        .container {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        
        h2 {
            color: #343a40;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
            margin-bottom: 25px;
            position: relative;
        }
        
        h2:after {
            content: '';
            position: absolute;
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, #4e73df, #36b9cc);
            bottom: -2px;
            right: 0;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ced4da;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
        
        .btn {
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-success {
            background-color: #1cc88a;
            border-color: #1cc88a;
        }
        
        .btn-success:hover {
            background-color: #169a6e;
            border-color: #169a6e;
        }
        
        .btn-danger {
            background-color: #e74a3b;
            border-color: #e74a3b;
        }
        
        .btn-danger:hover {
            background-color: #c23321;
            border-color: #c23321;
        }
        
        .uploader-image {
            object-fit: cover;
            border: 3px solid #4e73df;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s;
        }
        
        .uploader-image:hover {
            transform: scale(1.05);
        }
        
        .input-group .btn {
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .input-group .btn-outline-secondary {
            border-color: #ced4da;
            color: #6c757d;
        }
        
        .input-group .btn-outline-secondary:hover {
            background-color: #4e73df;
            border-color: #4e73df;
            color: white;
        }
        .select2-container--bootstrap-5 .select2-selection--single {
    height: 48px; /* ارتفاع انتخاب */
    border-radius: 8px; /* گوشه‌های گرد */
    border: 1px solid #ced4da; /* رنگ مرز */
}

.select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
    line-height: 46px; /* تراز عمودی متن */
}

.select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
    height: 46px; /* ارتفاع فلش */
}
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center">فرم ثبت‌نام پیمانکار</h2>
    <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
        <div class="mb-4 text-center">
            <img id="thumbnil" class="uploader-image rounded-circle" style="width: 150px; height: 150px;" src="images/departeman_black.jpg" alt="Thumbnail">
            <div class="mt-3">
                <label for="imageUpload" class="btn btn-outline-primary">
                    <i class="bi bi-upload me-2"></i>انتخاب تصویر
                </label>
                <input type="file" class="form-control d-none" id="imageUpload" name="image" accept="image/jpeg, image/png" onchange="showMyImage(this)">
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="role" class="form-label"><i class="bi bi-person-badge me-2"></i>نقش:</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="پیمانکار">پیمانکار</option>
                </select>
                <div class="invalid-feedback">لطفاً نقش خود را انتخاب کنید.</div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="project_id" class="form-label"><i class="bi bi-key me-2"></i>شناسه پروژه:</label>
                <input type="text" class="form-control" id="project_id" name="project_id" value="<?php echo $totalEmployees; ?>" disabled>
                <div class="invalid-feedback">لطفاً شناسه پروژه را تغییر ندهید.</div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label"><i class="bi bi-person me-2"></i>نام پیمانکار:</label>
                <input type="text" class="form-control" id="name" name="name" required>
                <div class="invalid-feedback">لطفاً نام پیمانکار را وارد کنید.</div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="family" class="form-label"><i class="bi bi-person-vcard me-2"></i>نام خانوادگی:</label>
                <input type="text" class="form-control" id="family" name="family" required>
                <div class="invalid-feedback">لطفاً نام خانوادگی را وارد کنید.</div>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="phone" class="form-label"><i class="bi bi-telephone me-2"></i>شماره تلفن:</label>
            <div class="input-group">
                <input type="text" class="form-control" id="phone" name="phone" required>
                <button class="btn btn-outline-secondary" type="button" id="selectContact">
                    <i class="fas fa-address-book"></i>
                </button>
            </div>
            <div class="invalid-feedback">لطفاً شماره تلفن را وارد کنید.</div>
        </div>
        
        <div class="mb-4">
            <label for="address" class="form-label"><i class="bi bi-geo-alt me-2"></i>آدرس:</label>
            <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
            <div class="invalid-feedback">لطفاً آدرس را وارد کنید.</div>
        </div>
        
        <div class="mb-4">
    <label for="level" class="form-label"><i class="fa fa-level-up me-2" aria-hidden="true"></i>لول:</label>
    <select id="level" name="level" class="form-select select2-level" required>
        <option value="C"  selected>پیش فرض</option>
        <option value="A">A</option>
        <option value="B">B</option>
        <option value="C">C</option>
    </select>
    <div class="invalid-feedback">لطفا لول را انتخاب کنید</div>
</div>
        
        <div class="d-flex justify-content-center mt-4">
            <button type="submit" class="btn btn-success mx-2">
                <i class="bi bi-check-circle me-2"></i>ثبت‌نام
            </button>
            <a href="index.php" class="btn btn-danger mx-2">
                <i class="bi bi-arrow-left me-2"></i>بازگشت
            </a>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-3d.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // فعال‌سازی Select2
        $('.select2-level').select2({
            theme: 'bootstrap-5'
        });

        // اعتبارسنجی فرم
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()

        // نمایش تصویر انتخاب شده
        function showMyImage(fileInput) {
            var files = fileInput.files;
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                var imageType = /image.*/;
                if (!file.type.match(imageType)) {
                    continue;
                }
                var img = document.getElementById("thumbnil");
                img.file = file;
                var reader = new FileReader();
                reader.onload = (function (aImg) {
                    return function (e) {
                        aImg.src = e.target.result;
                    };
                })(img);
                reader.readAsDataURL(file);
            }
        }

        // انتخاب مخاطب از دفترچه تلفن
        document.getElementById('selectContact').addEventListener('click', function() {
            // بررسی پشتیبانی از Contact Picker API
            if ('contacts' in navigator && 'ContactsManager' in window) {
                navigator.contacts.select(['tel'], { multiple: false })
                    .then(contacts => {
                        if (contacts.length > 0) {
                            const contact = contacts[0];
                            const phoneNumbers = contact.tel || [];

                            if (phoneNumbers.length > 0) {
                                // دریافت اولین شماره تلفن معتبر
                                let validPhoneNumber = null;
                                for (let phone of phoneNumbers) {
                                    if (phone && typeof phone === 'string' && phone.trim() !== '') {
                                        validPhoneNumber = phone;
                                        break;
                                    } else if (phone && phone.value && phone.value.trim() !== '') {
                                        validPhoneNumber = phone.value;
                                        break;
                                    }
                                }

                                if (validPhoneNumber) {
                                    // پاکسازی شماره تلفن (حذف فاصله‌ها، خط تیره و غیره)
                                    validPhoneNumber = validPhoneNumber.replace(/[^\d+]/g, '');
                                    
                                    // قرار دادن شماره تلفن در فیلد ورودی
                                    document.getElementById('phone').value = validPhoneNumber;
                                    
                                    // نمایش پیام موفقیت
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'انتخاب شد!',
                                        text: 'شماره تلفن با موفقیت انتخاب شد.',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'خطا!',
                                        text: 'شماره تلفن معتبری پیدا نشد.'
                                    });
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'خطا!',
                                    text: 'این مخاطب هیچ شماره تلفنی ندارد.'
                                });
                            }
                        } else {
                            Swal.fire({
                                icon: 'info',
                                title: 'توجه!',
                                text: 'هیچ مخاطبی انتخاب نشد.'
                            });
                        }
                    })
                    .catch(err => {
                        console.error('Contact Picker Error:', err);
                        Swal.fire({
                            icon: 'error',
                            title: 'خطا!',
                            text: 'خطا در انتخاب مخاطب: ' + (err.message || 'دسترسی به مخاطبین امکان‌پذیر نیست.')
                        });
                    });
            } else {
                // پیام برای مرورگرهایی که Contact Picker API را پشتیبانی نمی‌کنند
                Swal.fire({
                    icon: 'warning',
                    title: 'عدم پشتیبانی',
                    text: 'دفترچه تلفن در مرورگر شما پشتیبانی نمی‌شود. لطفا شماره را به صورت دستی وارد کنید.'
                });
            }
        });
    });
</script>
</body>
</html>