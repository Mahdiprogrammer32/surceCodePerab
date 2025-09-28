<?php
// وارد کردن فایل Database
require_once 'database.php';

// ایجاد نمونه‌ای از کلاس دیتابیس
$db = new Database();

// اتصال به دیتابیس
$connection = $db->connect();

if ($connection) {
    echo "اتصال به دیتابیس با موفقیت انجام شد.";
} else {
    echo "خطا در اتصال به دیتابیس.";
}

// بعد از اتمام کار، می‌توانید اتصال را ببندید (در صورت نیاز)
$db->disconnect();
?>
