<?php
require_once "database.php"; // فایل اتصال به دیتابیس
require_once "require_once/header.php"; // فایل هدر
require_once "require_once/swiper.php"; // فایل اسلایدر
require_once "require_once/menu.php"; // فایل منو

global $conn;

// فعال‌سازی نمایش خطاها برای اشکال‌زدایی (فقط در محیط توسعه)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// تعریف مسیر فایل تنظیمات برای کلید مخفی
require_once "config.php"; // باید شامل define('SECRET_KEY', 'your_secure_random_key_here') باشد

$cssLinks = [
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'b_icons/font/bootstrap-icons.min.css',
    'fontA/css/all.min.css',
    'checkout.css',
    'assets/css/style.css',
    'swiper-bundle.min.css',
    'assets/css/style_project.css',
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
    'https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js'
];

// شمارش تعداد کاربران برای تعیین project_id
$sql = "SELECT COUNT(*) AS total FROM users";
$result1 = $conn->query($sql);

if (!$result1) {
    die('Error executing query: ' . $conn->error);
}

$row = $result1->fetch_assoc();
$totalstaff = $row['total'] + 1;

// تابع تولید لایسنس منحصربه‌فرد
function generateLicense($phone, $secretKey) {
    return hash_hmac('sha256', $phone, $secretKey);
}

// تابع بررسی منحصربه‌فرد بودن لایسنس
function isLicenseUnique($license, $conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE license_key = ?");
    $stmt->bind_param("s", $license);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count == 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // دریافت و پاکسازی داده‌ها
    $name = htmlspecialchars(trim($_POST['name']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $address = htmlspecialchars(trim($_POST['address']));
    $role = htmlspecialchars(trim($_POST['role']));
    $project_id = $totalstaff;
    $form = "کارمندان";

    // اعتبارسنجی اولیه
    if (empty($name) || empty($phone) || empty($address) || empty($role)) {
        echo '<script>
            Swal.fire({
                icon: "error",
                title: "خطا!",
                text: "لطفاً تمامی فیلدها را پر کنید."
            });
        </script>';
    } elseif (!preg_match('/^\+?\d{10,12}$/', $phone)) {
        echo '<script>
            Swal.fire({
                icon: "error",
                title: "خطا!",
                text: "شماره تلفن نامعتبر است. لطفاً یک شماره تلفن معتبر وارد کنید."
            });
        </script>';
    } else {
        // اعتبارسنجی فایل آپلود شده
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png'];
            if (!in_array($_FILES['image']['type'], $allowedTypes)) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "خطا!",
                        text: "فقط فایل‌های JPEG یا PNG مجاز هستند."
                    });
                </script>';
                exit;
            }
            $imagePath = 'uploads/' . uniqid() . '_' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
        }

        // تولید لایسنس
        $secretKey = SECRET_KEY;
        $license = generateLicense($phone, $secretKey);

        // بررسی منحصربه‌فرد بودن لایسنس
        while (!isLicenseUnique($license, $conn)) {
            $license = generateLicense($phone . rand(1, 1000), $secretKey);
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
                    text: "شماره تلفن قبلاً ثبت شده است."
                });
            </script>';
        } else {
            // درج رکورد جدید
            $stmt = $conn->prepare("INSERT INTO users (name, phone, address, role, form, project_id, license_key, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssiss", $name, $phone, $address, $role, $form, $project_id, $license, $imagePath);

            if ($stmt->execute()) {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "موفقیت آمیز!",
                        text: "کارمند جدید با موفقیت ثبت شد.",
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = "staff.php";
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
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>فرم ثبت‌نام کارمند</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <?php foreach ($cssLinks as $link): ?>
        <link rel="stylesheet" href="<?php echo $link; ?>" type="text/css">
    <?php endforeach; ?>

    <style>
        :root {
            --primary-color: #00ffaa;
            --secondary-color: #0066ff;
            --accent-color: #ff00cc;
            --neon-pink: #ff1493;
            --electric-blue: #00bfff;
            --electric-purple: #9900ff;
            --background-dark: #0a0a14;
            --background-light: #2a2a3e; /* روشن‌تر برای کنتراست بهتر */
            --text-color: #ffffff;
        }

        body {
            background: linear-gradient(135deg, var(--background-dark), var(--background-light));
            color: var(--text-color);
            font-family: 'Vazir', Arial, sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .cyber-container {
            background: rgba(10, 10, 20, 0.8);
            border-radius: 20px;
            padding: 40px;
            margin: 30px auto;
            max-width: 1000px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 255, 170, 0.2);
        }

        .cyber-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 40px;
            color: var(--text-color);
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 0 5px var(--primary-color);
        }

        .form-field {
            position: relative;
            margin-bottom: 35px;
            transition: transform 0.3s ease;
        }

        .form-field:hover {
            transform: translateY(-3px);
        }

        .form-control, .input-group-text {
            background-color: rgba(30, 30, 40, 0.7);
            border: 1px solid rgba(0, 255, 170, 0.3);
            color: #fff;
            border-radius: 10px;
            padding: 15px;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .form-control:focus {
            background-color: rgba(40, 40, 50, 0.8);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(0, 255, 170, 0.2), 0 0 15px rgba(0, 255, 170, 0.5);
            outline: none;
        }

        .form-label {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 12px;
            display: block;
            text-shadow: 0 0 5px rgba(0, 255, 170, 0.5);
            font-size: 1.1rem;
        }

        .cyber-uploader {
            position: relative;
            width: 100%;
            max-width: 200px;
            height: auto;
            aspect-ratio: 1/1;
            margin: 0 auto 30px;
        }

        .uploader-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid var(--primary-color);
            transition: transform 0.3s ease;
        }

        .uploader-image:hover {
            transform: scale(1.05);
        }

        .file-uploader {
            cursor: pointer;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
        }

        .cyber-btn {
            padding: 15px 35px;
            border: none;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .cyber-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
        }

        .cyber-btn-danger {
            background: linear-gradient(45deg, var(--accent-color), var(--neon-pink));
        }

        .invalid-feedback {
            color: var(--accent-color);
            font-size: 0.85rem;
            margin-top: 5px;
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

            .cyber-uploader {
                max-width: 150px;
            }
        }

        @media (max-width: 576px) {
            .cyber-container {
                padding: 15px;
            }

            .cyber-title {
                font-size: 1.5rem;
            }

            .cyber-btn {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
        }

        /* دسترسی‌پذیری */
        .form-control[aria-invalid="true"] {
            border-color: var(--accent-color);
        }

        /* افکت‌های بهینه‌شده */
        @media (prefers-reduced-motion: reduce) {
            .cyber-btn, .uploader-image, .form-field {
                transition: none;
            }
        }
    </style>
</head>
<body>
<div class="cyber-container">
    <h2 class="cyber-title">فرم ثبت‌نام کارمند</h2>
    <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
        <div class="cyber-uploader">
            <img id="thumbnil" class="uploader-image" src="images/departeman_black.jpg" alt="تصویر پیش‌نمایش کارمند">
            <input type="file" class="file-uploader" name="image" accept="image/jpeg,image/png" onchange="showMyImage(this)" aria-label="آپلود تصویر کارمند">
            <div class="cyber-progress">
                <div class="cyber-progress-bar" id="upload-progress" style="width: 0%"></div>
            </div>
        </div>

        <div class="form-field">
            <label for="role" class="form-label">نقش:</label>
            <select class="form-control" id="role" name="role" required aria-describedby="role-error">
                <option value="" disabled selected>لطفاً نقش خود را انتخاب کنید</option>
                <option value="مدیر">مدیر</option>
                <option value="ناظر ارشد">ناظر ارشد</option>
                <option value="ناظر فنی">ناظر فنی</option>
                <option value="ناظر کیفی">ناظر کیفی</option>
                <option value="حسابدار">حسابدار</option>
                <option value="انباردار">انباردار</option>
                <option value="منشی">منشی</option>
            </select>
            <div id="role-error" class="invalid-feedback">لطفاً نقش خود را انتخاب کنید.</div>
        </div>

        <div class="form-field">
            <label for="name" class="form-label">نام کارمند:</label>
            <input type="text" class="form-control" id="name" name="name" required aria-describedby="name-error">
            <div id="name-error" class="invalid-feedback">لطفاً نام کارمند را وارد کنید.</div>
        </div>

        <div class="form-field">
            <label for="phone" class="form-label">شماره تلفن:</label>
            <div class="input-group">
                <input type="text" class="form-control" id="phone" name="phone" required aria-describedby="phone-error">
                <button class="btn btn-outline-secondary cyber-tooltip" type="button" id="selectContact">
                    <i class="fas fa-address-book"></i>
                    <span class="cyber-tooltip-text">انتخاب از دفترچه تلفن</span>
                </button>
            </div>
            <div id="phone-error" class="invalid-feedback">لطفاً شماره تلفن معتبر وارد کنید.</div>
        </div>

        <div class="form-field">
            <label for="address" class="form-label">آدرس:</label>
            <textarea class="form-control" id="address" name="address" required aria-describedby="address-error"></textarea>
            <div id="address-error" class="invalid-feedback">لطفاً آدرس را وارد کنید.</div>
        </div>

        <div class="form-field">
            <label for="project_id" class="form-label">شناسه پروژه:</label>
            <input type="text" class="form-control" name="project_id" value="<?php echo $totalstaff; ?>" disabled aria-describedby="project_id-error">
            <div id="project_id-error" class="invalid-feedback">شناسه پروژه نباید تغییر کند.</div>
        </div>

        <div class="d-flex justify-content-center align-items-center gap-3 flex-wrap">
            <button type="submit" class="cyber-btn">ثبت‌نام</button>
            <a href="staff.php" class="cyber-btn cyber-btn-danger">بازگشت</a>
        </div>
    </form>
</div>

<?php foreach ($jsLinks as $js): ?>
    <script src="<?php echo $js; ?>"></script>
<?php endforeach; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // پیش‌نمایش تصویر
    function showMyImage(fileInput) {
        const files = fileInput.files;
        const progressBar = document.getElementById('upload-progress');
        if (files && files[0]) {
            if (!files[0].type.match('image/(jpeg|png)')) {
                Swal.fire({
                    icon: 'error',
                    title: 'خطا!',
                    text: 'لطفاً فقط فایل‌های JPEG یا PNG آپلود کنید.'
                });
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('thumbnil').src = e.target.result;
                let progress = 0;
                const interval = setInterval(() => {
                    progress += 10;
                    progressBar.style.width = `${progress}%`;
                    if (progress >= 100) clearInterval(interval);
                }, 100);
            };
            reader.readAsDataURL(files[0]);
        }
    }

    // انتخاب مخاطب از دفترچه تلفن
    document.getElementById('selectContact').addEventListener('click', function() {
        if ('contacts' in navigator && 'ContactsManager' in window) {
            navigator.contacts.select(['tel'], { multiple: false })
                .then(contacts => {
                    if (contacts.length > 0) {
                        const contact = contacts[0];
                        const phoneNumbers = contact.tel || [];
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
                            validPhoneNumber = validPhoneNumber.replace(/[^\d+]/g, '');
                            document.getElementById('phone').value = validPhoneNumber;
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
            Swal.fire({
                icon: 'warning',
                title: 'عدم پشتیبانی',
                text: 'دفترچه تلفن در مرورگر شما پشتیبانی نمی‌شود.'
            });
        }
    });

    // اعتبارسنجی فرم
    document.querySelector('form').addEventListener('submit', function(e) {
        const form = this;
        if (!form.checkValidity()) {
            e.preventDefault();
            form.classList.add('was-validated');
        } else {
            e.preventDefault();
            const name = document.getElementById('name').value;
            const phone = document.getElementById('phone').value;
            const role = document.getElementById('role').value;
            Swal.fire({
                title: 'تأیید اطلاعات',
                html: `نام: ${name}<br>تلفن: ${phone}<br>نقش: ${role}<br>آیا مطمئن هستید؟`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'بله، ثبت شود',
                cancelButtonText: 'خیر'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    });
</script>
</body>
</html>