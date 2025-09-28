<?php
$servername = "localhost";
$username = "fixwbcsq_kakang"; // نام کاربری دیتابیس
$password = "mahdipass.2023";  // رمز عبور دیتابیس
$dbname = "fixwbcsq_perab"; // نام دیتابیس

// ایجاد اتصال به دیتابیس با استفاده از mysqli
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

// بررسی اتصال
if ($conn->connect_error) {
    echo '<div class="alert alert-danger" role="alert">اتصال به پایگاه داده ناموفق بود: ' . $conn->connect_error . '</div>';
}
?>
