<?php
require_once "database.php";
global $conn;
$targetDir = "uploads/"; // دایرکتوری برای ذخیره فایل‌ها

// بررسی وجود دایرکتوری و ایجاد آن در صورت عدم وجود
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$uploadStatus = 1; // وضعیت بارگذاری
$fileNames = []; // آرایه برای ذخیره نام فایل‌های بارگذاری شده

// بررسی اینکه آیا فایلی انتخاب شده است
if (isset($_FILES['files'])) {
    foreach ($_FILES['files']['name'] as $key => $name) {
        $targetFile = $targetDir . basename($_FILES['files']['name'][$key]);
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // بررسی اینکه آیا فایل واقعی است یا خیر
        if (isset($_FILES['files']['tmp_name'][$key]) && is_uploaded_file($_FILES['files']['tmp_name'][$key])) {
            // بررسی نوع فایل
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
            if (!in_array($fileType, $allowedTypes)) {
                echo "نوع فایل غیرمجاز: " . htmlspecialchars($name) . "<br>";
                $uploadStatus = 0;
                continue;
            }

            // تلاش برای بارگذاری فایل
            if ( move_uploaded_file($_FILES['files']['tmp_name'][$key], $targetFile)) {
                echo "فایل " . htmlspecialchars($name) . " با موفقیت بارگذاری شد.<br>";
                $fileNames[] = $name;

                // ذخیره اطلاعات فایل در دیتابیس
                $stmt = $conn->prepare("INSERT INTO uploads (file_name, file_path) VALUES (?, ?)");
                $stmt->bind_param("ss", $name, $targetFile);
                if (!$stmt->execute()) {
                    echo "خطا در ذخیره اطلاعات فایل " . htmlspecialchars($name) . " در دیتابیس.<br>";
                }
                $stmt->close();
            } else {
                echo "خطا در بارگذاری فایل " . htmlspecialchars($name) . "<br>";
                $uploadStatus = 0;
            }
        }
    }
} else {
    echo "هیچ فایلی انتخاب نشده است.";
}

if ($uploadStatus) {
    echo "تمام فایل‌ها با موفقیت بارگذاری و ذخیره شدند.";
} else {
    echo "برخی از فایل‌ها با موفقیت بارگذاری نشدند.";
}

$conn->close();
?>