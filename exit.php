<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>برگشت به تعداد صفحات بازشده</title>
</head>
<body>
    <h1>این صفحه فعلی است</h1>
    <button onclick="goBackAll()">برگشت به تعداد صفحات بازشده</button>

    <script>
        // تابع برای برگشت به تعداد صفحات بازشده
        function goBackAll() {
            const historyLength = window.history.length; // تعداد صفحات در تاریخچه
            if (historyLength > 1) {
                for (let i = 1; i < historyLength; i++) {
                    window.history.back(); // برگشت به صفحه قبل
                }
            } else {
                alert("صفحه قبلی وجود ندارد!");
            }
        }
    </script>
</body>
</html>