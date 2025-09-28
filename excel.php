<?php
header('Content-Type: application/json; charset=utf-8');

// دریافت داده‌ها از درخواست POST
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// اتصال به دیتابیس (مثال برای MySQL)
$servername = "localhost";
$username = "departem_kakang"; // نام کاربری دیتابیس
$password = "mahdipass.2023";  // رمز عبور دیتابیس
$dbname = "departem_test"; // نام دیتابیس

$conn = new mysqli($servername, $username, $password, $dbname);

// بررسی اتصال
if ($conn->connect_error) {
    die("اتصال به دیتابیس ناموفق بود: " . $conn->connect_error);
}

// وارد کردن داده‌ها به دیتابیس
foreach ($data as $row) {
    // فرض کنید جدول شما دارای ستون‌های `name` و `age` است
    $name = $conn->real_escape_string($row['name']); // جایگزین کنید با نام ستون‌های Excel
    $age = $conn->real_escape_string($row['age']);   // جایگزین کنید با نام ستون‌های Excel

    $sql = "INSERT INTO your_table (name, age) VALUES ('$name', '$age')";
    if (!$conn->query($sql)) {
        echo "خطا در درج داده: " . $conn->error;
        $conn->close();
        exit;
    }
}

$conn->close();
echo "داده‌ها با موفقیت به دیتابیس اضافه شدند.";
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>بارگذاری فایل Excel</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body>
    <input type="file" id="excelFile" accept=".xlsx, .xls" />
    <button onclick="uploadFile()">بارگذاری و ارسال به سرور</button>

    <script>
        function uploadFile() {
            const fileInput = document.getElementById('excelFile');
            const file = fileInput.files[0];

            if (!file) {
                alert("لطفاً یک فایل انتخاب کنید.");
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });

                // خواندن اولین برگه
                const sheetName = workbook.SheetNames[0];
                const sheet = workbook.Sheets[sheetName];
                const jsonData = XLSX.utils.sheet_to_json(sheet);

                // ارسال داده‌ها به سرور
                sendDataToServer(jsonData);
            };
            reader.readAsArrayBuffer(file);
        }

        function sendDataToServer(data) {
            fetch('upload.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.text())
            .then(result => {
                alert(result); // نمایش نتیجه از سرور
            })
            .catch(error => {
                console.error('خطا:', error);
            });
        }
    </script>
</body>
</html>