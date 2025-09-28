<?php
ob_start(); // شروع بافر خروجی
session_start();
require_once "database.php";
global $conn;

// بررسی لاگین کاربر
if (!isset($_COOKIE['userID'])) {
    header("Location: login.php"); // اگر کاربر لاگین نکرده باشد، به صفحه‌ی لاگین هدایت شود
    exit();
}

$cssLinks = [
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css', // Animate.css
    'https://cdnjs.cloudflare.com/ajax/libs/hover.css/2.3.1/css/hover-min.css', // Hover.css
    'https://unpkg.com/swiper/swiper-bundle.min.css', // Swiper.js
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['files'])) {
        // بارگذاری فایل‌ها
        $directory = 'uploads/';
        foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
            // پاکسازی نام فایل
            $file_name = basename($_FILES['files']['name'][$key]);
            $file_name = preg_replace("/[^a-zA-Z0-9\.\-_]/", "", $file_name); // حذف کاراکترهای غیرمجاز
            $file_name = time() . "_" . $file_name; // اضافه کردن timestamp برای منحصر به فرد بودن نام فایل
            
            $target_file = $directory . $file_name;
            $is_private = isset($_POST['is_private'][$key]) ? 1 : 0;

            if (move_uploaded_file($tmp_name, $target_file)) {
                // ذخیره اطلاعات در دیتابیس
                $stmt = $conn->prepare("INSERT INTO images (file_name, is_private, user_id) VALUES (?, ?, ?)");
                $stmt->bind_param("sii", $file_name, $is_private, $_COOKIE['userID']);
                if ($stmt->execute()) {
                    echo "<div class='alert alert-success animate__animated animate__fadeIn' id='success-alert'>فایل $file_name با موفقیت بارگذاری شد.</div>";
                } else {
                    echo "<div class='alert alert-danger animate__animated animate__shakeX' id='error-alert'>خطا در ذخیره اطلاعات در دیتابیس!</div>";
                }
            } else {
                echo "<div class='alert alert-danger animate__animated animate__shakeX' id='error-alert'>خطا در بارگذاری فایل $file_name.</div>";
            }
        }
    }

    if (isset($_POST['toggle_private'])) {
        // تغییر وضعیت خصوصی/عمومی
        $image_id = $_POST['image_id'];
        $is_private = $_POST['is_private'];
        
        // بروزرسانی وضعیت در دیتابیس
        $stmt = $conn->prepare("UPDATE images SET is_private = ? WHERE id = ?");
        $stmt->bind_param("ii", $is_private, $image_id);
        
        if ($stmt->execute()) {
            echo "<div class='alert alert-success animate__animated animate__fadeIn' id='success-alert'>وضعیت فایل با موفقیت تغییر کرد.</div>";
        } else {
            echo "<div class='alert alert-danger animate__animated animate__shakeX' id='error-alert'>خطا در تغییر وضعیت فایل!</div>";
        }
    }

    foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
        // پاکسازی نام فایل
        $original_name = basename($_FILES['files']['name'][$key]);
        $original_name = preg_replace("/[^a-zA-Z0-9\.\-_]/", "", $original_name);
        
        // بررسی تکراری بودن نام فایل برای کاربر فعلی
        $escaped_original = str_replace('_', '\_', $original_name); // فرار از کاراکترهای زیرخط برای LIKE
        $like_pattern = '%\_' . $escaped_original;
        $check_stmt = $conn->prepare("SELECT * FROM images WHERE user_id = ? AND file_name LIKE ? ESCAPE '\\'");
        $check_stmt->bind_param("is", $_COOKIE['userID'], $like_pattern);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            echo "<div class='alert alert-danger animate__animated animate__shakeX'>فایل '$original_name' قبلاً توسط شما آپلود شده است.</div>";
            continue; // اگر تکراری باشد، این فایل پردازش نمی‌شود
        }
        
        // ادامه فرآیند آپلود
        $file_name = time() . "_" . $original_name;
        $target_file = $directory . $file_name;
        $is_private = isset($_POST['is_private'][$key]) ? 1 : 0;
    
        if (move_uploaded_file($tmp_name, $target_file)) {
            // ذخیره اطلاعات در دیتابیس
            $stmt = $conn->prepare("INSERT INTO images (file_name, is_private, user_id) VALUES (?, ?, ?)");
            $stmt->bind_param("sii", $file_name, $is_private, $_COOKIE['userID']);
            if ($stmt->execute()) {
                echo "<div class='alert alert-success animate__animated animate__fadeIn' id='success-alert'>فایل $original_name با موفقیت بارگذاری شد.</div>";
            } else {
                echo "<div class='alert alert-danger animate__animated animate__shakeX' id='error-alert'>خطا در ذخیره اطلاعات در دیتابیس!</div>";
            }
        } else {
            echo "<div class='alert alert-danger animate__animated animate__shakeX' id='error-alert'>خطا در بارگذاری فایل $original_name.</div>";
        }
    }
    if (isset($_POST['delete'])) {
        // حذف تصویر
        $image = $_POST['image'];
        $file_path = 'uploads/' . $image;
        
        if (file_exists($file_path)) {
            unlink($file_path); // حذف فایل از سرور
            
            // حذف رکورد از دیتابیس
            $stmt = $conn->prepare("DELETE FROM images WHERE file_name = ?");
            $stmt->bind_param("s", $image);
            
            if ($stmt->execute()) {
                echo "<div class='alert alert-success animate__animated animate__fadeIn' id='success-alert'>فایل $image با موفقیت حذف شد.</div>";
            } else {
                echo "<div class='alert alert-danger animate__animated animate__shakeX' id='error-alert'>خطا در حذف از دیتابیس!</div>";
            }
        } else {
            echo "<div class='alert alert-danger animate__animated animate__shakeX' id='error-alert'>فایل $image پیدا نشد.</div>";
        }
    }
}

// دریافت عکس‌ها از دیتابیس
$user_id = $_COOKIE['userID'];
$stmt = $conn->prepare("SELECT * FROM images WHERE is_private = 0 OR user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$images = $result->fetch_all(MYSQLI_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>مدیریت عکس ها</title>

    <?php
    foreach ($cssLinks as $cssLink) {
        echo "<link rel='stylesheet' href='" . $cssLink . "'>\n";
    }
    ?>
 <style>
        body {
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            font-family: 'Arial', sans-serif;
        }
        .image-container {
            position: relative;
            margin: 10px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
            height: 250px; /* ارتفاع ثابت برای همه عکس‌ها */
        }
        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* حفظ نسبت تصویر */
            transition: transform 0.3s ease;
        }
        .image-container:hover img {
            transform: scale(1.1);
        }
        .image-container:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
        .image-actions {
            display: none;
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 10;
        }
        .image-container:hover .image-actions {
            display: flex;
            gap: 10px;
        }
        .btn-custom {
            margin-top: 20px;
            border-radius: 25px;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: white;
            border: none;
            transition: background 0.3s ease;
        }
        .btn-custom:hover {
            background: linear-gradient(135deg, #2575fc, #6a11cb);
        }
        .preview img {
            border-radius: 10px;
            margin: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100px; /* سایز ثابت برای پیش‌نمایش */
            height: 100px;
            object-fit: cover;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); /* شبکه منظم */
            gap: 15px;
            padding: 20px;
        }
        .card {
            border: none;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
        .delete-animation {
    animation: deleteEffect 0.5s ease forwards;
}

@keyframes deleteEffect {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    100% {
        transform: scale(0);
        opacity: 0;
    }
}
    </style>
</head>
<body>
<h1 class="text-center animate__animated animate__fadeInDown">بارگذاری فایل</h1>
<form id="uploadForm" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="fileInput">انتخاب فایل‌ها:</label>
        <input type="file" id="fileInput" name="files[]" class="form-control-file hvr-grow" multiple accept="image/*">
    </div>
    <div class="form-check">
        <input type="checkbox" class="form-check-input" name="is_private[]" value="1">
        <label class="form-check-label">خصوصی</label>
    </div>
    <div class="preview" id="preview"></div>
    <button type="submit" class="btn btn-custom hvr-float">بارگذاری فایل‌ها</button>
</form>

<div id="uploadMessage"></div>

<h2 class="text-center animate__animated animate__fadeInDown">تصاویر بارگذاری شده</h2>
<div class="grid-container">
    <?php foreach ($images as $image): ?>
        <div class="card image-container animate__animated animate__fadeInUp">
            <img src="uploads/<?php echo $image['file_name']; ?>" class="card-img-top hvr-grow" alt="Image">
            <div class="image-actions">
                <form method="post" class="d-inline">
                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                    <input type="hidden" name="is_private" value="<?php echo $image['is_private'] ? 0 : 1; ?>">
                    <button type="submit" name="toggle_private" class="btn <?php echo $image['is_private'] ? 'btn-warning' : 'btn-success'; ?> hvr-shrink">
                        <?php echo $image['is_private'] ? 'عمومی' : 'خصوصی'; ?>
                    </button>
                </form>
                <form method="post" class="d-inline">
                    <input type="hidden" name="image" value="<?php echo $image['file_name']; ?>">
                    <button type="submit" name="delete" class="btn btn-danger hvr-shrink">حذف</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
   $(document).ready(function () {
    // پیش‌نمایش فایل‌ها
    $('#fileInput').on('change', function () {
        var files = $(this)[0].files;
        $('#preview').empty();
        for (var i = 0; i < files.length; i++) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#preview').append('<img src="' + e.target.result + '" class="img-thumbnail hvr-grow" style="max-width: 100%; margin-top: 10px;">');
            }
            reader.readAsDataURL(files[i]);
        }
    });

    // مدیریت حذف با انیمیشن
    $('.delete-form').on('submit', function (e) {
        e.preventDefault(); // جلوگیری از ارسال فرم بلافاصله
        var form = $(this);
        var imageContainer = form.closest('.image-container');

        // اعمال افکت حذف
        imageContainer.addClass('delete-animation');

        // حذف آیتم پس از پایان انیمیشن
        setTimeout(function () {
            form.off('submit').submit(); // ارسال فرم پس از انیمیشن
        }, 500); // مدت زمان انیمیشن (0.5 ثانیه)
    });

    // حذف پیام‌ها بعد از 3 ثانیه
    setTimeout(function () {
        $('#success-alert').fadeOut('slow', function () {
            $(this).remove();
        });
    }, 3000);

    setTimeout(function () {
        $('#error-alert').fadeOut('slow', function () {
            $(this).remove();
        });
    }, 3000);
});
</script>

</body>
</html>