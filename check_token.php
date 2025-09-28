<?php
header('Content-Type: application/json');
session_start();

$host = 'localhost';
$db = 'fixwbcsq_perab';
$user = 'fixwbcsq_kakang';
$pass = 'mahdipass.2023';
$charset = 'utf8mb4';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['valid' => false, 'error' => 'خطا در اتصال به سرور']);
    exit();
}
$conn->set_charset($charset);

$input = json_decode(file_get_contents('php://input'), true);
$token = $input['token'] ?? '';

if (empty($token)) {
    echo json_encode(['valid' => false, 'error' => 'توکن ارائه نشده است']);
    exit();
}

$stmt = $conn->prepare("SELECT id FROM users WHERE license_key = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['valid' => true]);
} else {
    echo json_encode(['valid' => false, 'error' => 'توکن نامعتبر است']);
}

$stmt->close();
$conn->close();
?>