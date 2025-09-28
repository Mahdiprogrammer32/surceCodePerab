<?php
// اتصال به دیتابیس
$servername = "localhost";
$username = "root"; // نام کاربری دیتابیس
$password = ""; // رمز عبور دیتابیس
$dbname = "webshop"; // نام دیتابیس

// اتصال به دیتابیس
$conn = new mysqli($servername, $username, $password, $dbname);

// بررسی اتصال
if ($conn->connect_error) {
    die("اتصال به دیتابیس برقرار نشد: " . $conn->connect_error);
}

// مسیر آپلود تصاویر
$uploadDir = 'uploads/';

// بررسی و ساخت پوشه آپلود در صورت نیاز
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// متغیر برای پیام‌ها
$uploadStatus = [];
$deleteStatus = [];

// حذف تصویر
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $imageToDelete = $_GET['delete'];
    $imagePath = $uploadDir . basename($imageToDelete);

    if (file_exists($imagePath)) {
        if (unlink($imagePath)) {
            // حذف از دیتابیس
            $deleteQuery = "DELETE FROM images WHERE image_name = '$imageToDelete'";
            if ($conn->query($deleteQuery) === TRUE) {
                $deleteStatus[] = "تصویر '$imageToDelete' با موفقیت حذف شد.";
            } else {
                $deleteStatus[] = "خطا در حذف تصویر از دیتابیس.";
            }
        } else {
            $deleteStatus[] = "خطا در حذف تصویر '$imageToDelete'.";
        }
    } else {
        $deleteStatus[] = "تصویر '$imageToDelete' یافت نشد.";
    }
}

// بررسی آپلود تصاویر جدید
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['images'])) {
    // انواع فایل‌های مجاز
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    foreach ($_FILES['images']['name'] as $key => $fileName) {
        $uploadFile = $uploadDir . basename($fileName);
        $fileTmpName = $_FILES['images']['tmp_name'][$key];
        $fileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));

        // بررسی نوع فایل
        if (in_array($fileType, $allowedTypes)) {
            // بررسی اندازه فایل (حداکثر 5MB)
            if ($_FILES['images']['size'][$key] <= 5 * 1024 * 1024) {
                // انتقال فایل به پوشه uploads
                if (move_uploaded_file($fileTmpName, $uploadFile)) {
                    // ذخیره اطلاعات تصویر در دیتابیس
                    $imagePath = $uploadDir . basename($fileName);
                    $stmt = $conn->prepare("INSERT INTO images (image_name, image_path) VALUES (?, ?)");
                    $stmt->bind_param("ss", $fileName, $imagePath);

                    if ($stmt->execute()) {
                        $uploadStatus[] = "فایل '$fileName' با موفقیت بارگذاری شد!";
                    } else {
                        $uploadStatus[] = "مشکلی در ذخیره اطلاعات '$fileName' در دیتابیس به وجود آمد.";
                    }
                } else {
                    $uploadStatus[] = "مشکلی در آپلود '$fileName' به وجود آمد.";
                }
            } else {
                $uploadStatus[] = "فایل '$fileName' بیش از حد مجاز است (حداکثر 5MB).";
            }
        } else {
            $uploadStatus[] = "فایل '$fileName' فرمت مجاز ندارد. فقط فرمت‌های jpg، jpeg، png و gif مجاز هستند.";
        }
    }
}

// خواندن فایل‌های موجود در پوشه uploads
$uploadedImages = array_diff(scandir($uploadDir), ['.', '..']);
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت تصاویر</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* استایل‌ها */
        body {
            font-family: 'Tahoma', sans-serif;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }
        .image-preview {
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .image-preview img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .status-message {
            margin: 20px 0;
            font-size: 16px;
        }
        .status-message.success {
            color: green;
        }
        .status-message.error {
            color: red;
        }
        .custom-file-input {
            border-radius: 50px;
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
        }
        .custom-file-input:hover {
            background-color: #0056b3;
        }
        .file-input-label {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            cursor: pointer;
            margin-top: 10px;
        }
        .file-input-label:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center mb-4 text-3xl font-semibold">مدیریت تصاویر</h2>

    <!-- نمایش وضعیت آپلود -->
    <?php if (isset($uploadStatus)) { ?>
        <div class="status-message">
            <?php foreach ($uploadStatus as $message) {
                $messageType = strpos($message, 'با موفقیت') !== false ? 'success' : 'error';
                echo "<p class='$messageType'>$message</p>";
            } ?>
        </div>
    <?php } ?>

    <!-- نمایش وضعیت حذف -->
    <?php if (isset($deleteStatus)) { ?>
        <div class="status-message">
            <?php foreach ($deleteStatus as $message) {
                $messageType = strpos($message, 'با موفقیت') !== false ? 'success' : 'error';
                echo "<p class='$messageType'>$message</p>";
            } ?>
        </div>
    <?php } ?>

    <!-- فرم بارگذاری تصاویر -->
    <form action="" method="POST" enctype="multipart/form-data" id="uploadForm">
        <div class="mb-3">
            <label for="images" class="file-input-label">انتخاب تصاویر</label>
            <input type="file" name="images[]" id="images" accept="image/*" multiple class="custom-file-input" required onchange="previewImages()">
        </div>
        <button type="submit" class="btn btn-primary w-full py-3 bg-blue-500 hover:bg-blue-700 text-white rounded-lg">بارگذاری تصاویر</button>
    </form>

    <!-- پیش‌نمایش تصاویر -->
    <div class="image-preview" id="imagePreview"></div>

    <!-- نمایش تصاویر موجود -->
    <h3 class="text-2xl font-semibold mt-8 mb-4">تصاویر آپلود شده</h3>
    <div class="grid grid-cols-3 gap-4">
        <?php foreach ($uploadedImages as $image) { ?>
            <div class="relative">
                <img src="<?php echo $uploadDir . $image; ?>" alt="Uploaded Image" class="w-full h-32 object-cover rounded-lg">
                <a href="?delete=<?php echo $image; ?>" class="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-full hover:bg-red-700">
                    حذف
                </a>
            </div>
        <?php } ?>
    </div>
</div>

<script>
    function previewImages() {
        const files = document.getElementById('images').files;
        const previewContainer = document.getElementById('imagePreview');
        previewContainer.innerHTML = ''; // پاک کردن پیش‌نمایش‌های قبلی

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = "Image preview";
                previewContainer.appendChild(img);
            }
            reader.readAsDataURL(file);
        }
    }
</script>

</body>
</html>
