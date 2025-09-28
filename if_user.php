<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


    // بررسی وجود سشن و معتبر بودن آن
    if (!isset($_SESSION['user_login'])!== true) {

        // پاکسازی سشن
        session_unset();
        session_destroy();

        // ریدایرکت به صفحه لاگین
        header("Location: login.php");
        exit();
    }










?>