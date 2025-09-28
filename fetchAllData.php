<?php
header('Content-Type: application/json; charset=utf-8');

// اتصال به دیتابیس
$conn = new mysqli('localhost', 'fixwbcsq_kakang', 'mahdipass.2023', 'fixwbcsq_perab');
$conn->set_charset("utf8mb4");

// لیست جداولی که می‌خوای کش کنی
$tables = ['ap_accounts', 'users']; // اینو تنظیم کن

$data = [];

foreach ($tables as $table) {
    $result = $conn->query("SELECT * FROM `$table`");
    $rows = [];

    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $data[$table] = $rows;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
