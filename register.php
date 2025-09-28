<?php
require_once "database.php";
global $conn;

// فعال‌سازی نمایش خطاها
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// دریافت تعداد کارمندان برای تعیین شناسه پروفایل
$sql = "SELECT COUNT(*) AS total FROM employees";
$result1 = $conn->query($sql);

if ($result1) {
    $row = $result1->fetch_assoc();
    $profile_id = $row['total'] + 1;
} else {
    echo "خطا در اجرای پرس‌وجو: " . $conn->error;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['sub'])) {
        // دریافت و ایمن‌سازی داده‌های فرم
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $password = trim($_POST['password']);
        $role = trim($_POST['role']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $date_of_birth = trim($_POST['date_of_birth']);
        $national_id = trim($_POST['national_id']);
        $marital_status = trim($_POST['marital_status']);
        $security_question = trim($_POST['security_question']);
        $security_answer = trim($_POST['security_answer']);

        // بررسی کد ملی تکراری
        $check_query = $conn->prepare("SELECT * FROM users WHERE national_id = ?");
        $check_query->bind_param("s", $national_id);
        $check_query->execute();
        $result = $check_query->get_result();

        if ($result->num_rows > 0) {
            echo "این کد ملی قبلاً ثبت شده است. لطفاً یک کد ملی دیگر وارد کنید.";
        } else {
            // مدیریت بارگذاری تصویر
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $product_image_name = basename($_FILES['image']['name']);
                $product_image_tmp = $_FILES['image']['tmp_name'];
                $image_folder = 'uploads/' . $product_image_name;

                // اعتبارسنجی نوع تصویر
                $imageFileType = strtolower(pathinfo($image_folder, PATHINFO_EXTENSION));
                if (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
                    echo "فقط فایل‌های JPG و PNG مجاز هستند.";
                    exit;
                }

                // هش کردن رمز عبور
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // استفاده از بیانیه‌های آماده برای جلوگیری از SQL Injection
                $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, password, role, phone, profile_image, address, date_of_birth, national_id, marital_status, security_question, security_answer)
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssssssss", $first_name, $last_name, $hashed_password, $role, $phone, $image_folder, $address, $date_of_birth, $national_id, $marital_status, $security_question, $security_answer);

                if ($stmt->execute()) {
                    // انتقال تصویر به مسیر مورد نظر
                    if (move_uploaded_file($product_image_tmp, $image_folder)) {
                        echo "ثبت‌نام با موفقیت انجام شد!";
                    } else {
                        echo "خطا در بارگذاری تصویر.";
                    }
                } else {
                    echo "خطا در ثبت‌نام: " . $stmt->error;
                }

                $stmt->close();
            } else {
                echo "خطا در بارگذاری تصویر: " . $_FILES['image']['error'];
            }
        }

        $check_query->close();
    }

}


?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فرم ثبت‌نام</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            direction: rtl;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .uploader {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .uploader-image {
            width: 200px;
            height: 200px;
            position: relative;
            box-shadow: 0 0 10px black;
        }
        .file-uploader {
            width: 200px;
            height: 200px;
            opacity: 0;
            position: absolute;
            border-radius: 50%;
            cursor: pointer;
        }

        body {
            background-color: #f8f9fa;
            direction: rtl;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
        }
        .uploader-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
    </style>


 <!-- Vazir Font -->
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@latest/dist/font-face.css" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0-alpha1/css/bootstrap.min.css" />
    
    <!-- Kamadatepicker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/niklasvh/kamadatepicker/dist/kamadatepicker.min.css" />
</head>
<body>



<div class="container">
    <h2 class="text-center">فرم ثبت‌نام</h2>
    <form action="register.php" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
        <div class="mb-3 uploader">
            <img id="thumbnil" class="uploader-image" style="margin-top:10px; border-radius: 50%;" src="images/departeman_black.jpg" alt="Thumbnail">
            <input type="file" class="file-uploader" name="image" accept="image/jpeg, image/png" onchange="showMyImage(this)" required>
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">نقش:</label>
            <select class="form-select" id="role" name="role" required>
                <option value="">انتخاب کنید...</option>
                <option value="پیمانکار">پیمانکار</option>
                <option value="مدیر">مدیر</option>
                <option value="مدیر ارشد">مدیر ارشد</option>
                <option value="انباردار">انباردار</option>
                <option value="ناظر">ناظر</option>
                <option value="حسابدار">حسابدار</option>
            </select>
            <div class="invalid-feedback">لطفاً نقش خود را انتخاب کنید.</div>
        </div>

        <div class="default" id="commonFields">
            <div class="mb-3">
                <label for="first_name" class="form-label">نام:</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
                <div class="invalid-feedback">لطفاً نام خود را وارد کنید.</div>
            </div>

            <div class="mb-3">
                <label for="last_name" class="form-label">نام خانوادگی:</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
                <div class="invalid-feedback">لطفاً نام خانوادگی خود را وارد کنید.</div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">رمز عبور:</label>
                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                <div class="invalid-feedback">رمز عبور باید حداقل ۶ کاراکتر باشد.</div>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">شماره تلفن:</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
                <div class="invalid-feedback">لطفاً شماره تلفن خود را وارد کنید.</div>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">آدرس:</label>
                <textarea class="form-control" id="address" name="address"></textarea>
            </div>
            <div class="mb-3">
            <label for="date_of_birth" class="form-label">تاریخ تولد:</label>
            <input type="text" class="form-control" id="date_of_birth" name="date_of_birth" required>
            <div class="invalid-feedback">لطفاً تاریخ تولد خود را وارد کنید.</div>
        </div>
            <div class="mb-3">
                <label for="national_id" class="form-label">کد ملی:</label>
                <input type="text" class="form-control" id="national_id" name="national_id" required>
                <div class="invalid-feedback">لطفاً کد ملی خود را وارد کنید.</div>
            </div>

            <div class="mb-3">
                <label for="marital_status" class="form-label">وضعیت تأهل:</label>
                <select class="form-select" id="marital_status" name="marital_status" required>
                    <option value="single">مجرد</option>
                    <option value="married">متأهل</option>
                </select>
                <div class="invalid-feedback">لطفاً وضعیت تأهل خود را انتخاب کنید.</div>
            </div>

            <div class="mb-3">
                <label for="security_question" class="form-label">سوال امنیتی:</label>
                <select class="form-select" id="security_question" name="security_question" required>
                    <option value="">انتخاب کنید...</option>
                    <option value="چه رنگی دوست دارید؟">چه رنگی را دوست دارید؟</option>
                    <option value="نام پدربزرگتان چیست؟">نام پدر بزرگتان چیست؟</option>
                    <option value="آخرین محلی که در آن تحصیل کردید؟">آخرین محلی که در آن تحصیل کردید؟</option>
                    <option value="به چه ابزاری علاقه خاص دارید؟">به چه ابزاری علاقه خاص دارید؟</option>
                    <option value="غذای مورد علاقه؟">غذای مورد علاقه؟</option>
                </select>
                <div class="invalid-feedback">لطفاً سوال امنیتی خود را انتخاب کنید.</div>
            </div>

            <div class="mb-3">
                <label for="security_answer" class="form-label">پاسخ امنیتی:</label>
                <input type="text" class="form-control" id="security_answer" name="security_answer">
            </div>
        </div>

       
        <div class="d-flex justify-content-center align-items-center">
            <button type="submit" class="btn btn-success" name="sub" style="margin-inline: 20px;">ثبت‌نام</button>
            <a href="index.php" class="btn btn-danger mr-5">بازگشت</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('role').addEventListener('change', function() {
        const employerFields = document.getElementById('employerFields');
        const commonFields = document.getElementById('commonFields');

        if (this.value === "کارفرما") {
            employerFields.style.display = 'block';
            commonFields.style.display = 'block';
        } else {
            employerFields.style.display = 'none';
        }

        // اگر کاربر پیمانکار است، فرم کارفرما را پنهان کنید و فقط فیلدهای مشترک را نمایش دهید
        if (this.value === "پیمانکار") {
            commonFields.style.display = 'block';
        } else {
            commonFields.style.display = 'block'; // در غیر این صورت، فیلدهای مشترک را نمایش دهید
        }
    });


</script>

</body>
</html>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>


    <!-- Your content here -->

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0-alpha1/js/bootstrap.bundle.min.js"></script>
    
    <!-- Kamadatepicker JS -->
    <script src="https://cdn.jsdelivr.net/gh/niklasvh/kamadatepicker/dist/kamadatepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/niklasvh/kamadatepicker/dist/kamadatepicker.holidays.js"></script>
<script>
        kamaDatepicker('date_of_birth', { buttonsColor: "red" });

var customOptions = {
    placeholder: "روز / ماه / سال"
    , twodigit: false
    , closeAfterSelect: false
    , nextButtonIcon: "fa fa-arrow-circle-right"
    , previousButtonIcon: "fa fa-arrow-circle-left"
    , buttonsColor: "blue"
    , forceFarsiDigits: true
    , markToday: true
    , markHolidays: true
    , highlightSelectedDay: true
    , sync: true
    , gotoToday: true
}
</script>
<script>


    function showMyImage(fileInput) {
        var files = fileInput.files;
        if (files && files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('thumbnil').src = e.target.result;
            }
            reader.readAsDataURL(files[0]);
        }
    }

    (function () {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>



</body>
</html>




<?php
$conn->close();
?>