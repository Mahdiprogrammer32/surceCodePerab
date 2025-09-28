<?php
session_start(); // شروع سشن

// بررسی وجود ورودی‌ها و ذخیره‌سازی آن‌ها در متغیر
if (!isset($_SESSION['inputs'])) {
    $_SESSION['inputs'] = []; // اگر سشن خالی است، آرایه را ایجاد کنید
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['inputs'])) {
        $_SESSION['inputs'] = $_POST['inputs']; // دریافت ورودی‌ها به صورت آرایه
    }
    // بررسی وجود ورودی برای حذف
    if (isset($_POST['delete_index'])) {
        $deleteIndex = intval($_POST['delete_index']);
        if (isset($_SESSION['inputs'][$deleteIndex])) {
            unset($_SESSION['inputs'][$deleteIndex]); // حذف ورودی از آرایه
            $_SESSION['inputs'] = array_values($_SESSION['inputs']); // بازچینی آرایه
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>اضافه کردن Input</title>
</head>
<body>
<div class="container mt-5">
    <h2>اضافه کردن Input</h2>
    <form method="POST" action="">
        <div id="inputContainer" class="mb-3">
            <?php
            // نمایش ورودی‌های موجود در فرم
            foreach ($_SESSION['inputs'] as $index => $input) {
                echo '<div class="input-group mb-2">';
                echo '<input type="text" name="inputs[]" class="form-control" value="' . htmlspecialchars($input) . '">';
                echo '<button type="submit" name="delete_index" value="' . $index . '" class="btn btn-danger">حذف</button>';
                echo '</div>';
            }
            ?>
            <input type="text" name="inputs[]" class="form-control mb-2" placeholder="Input جدید">
        </div>
        <button type="button" id="addInput" class="btn btn-primary">اضافه کردن Input</button>
        <button type ="submit" class="btn btn-success">ذخیره ورودی‌ها</button>
    </form>

    <h3 class="mt-4">ورودی‌های ذخیره شده:</h3>
    <div id="savedInputs">
        <?php
        if (!empty($_SESSION['inputs'])) {
            echo "<ul class='list-group'>";
            foreach ($_SESSION['inputs'] as $input) {
                echo "<li class='list-group-item'>" . htmlspecialchars($input) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>هیچ ورودی‌ای ذخیره نشده است.</p>";
        }
        ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script>
    $(document).ready(function() {
        $('#addInput').click(function() {
            const inputField = $('<div class="input-group mb-2"><input type="text" name="inputs[]" class="form-control" placeholder="Input جدید"><button type="button" class="btn btn-danger remove-input">حذف</button></div>');
            $('#inputContainer').append(inputField);
        });

        // حذف ورودی‌های جدیدی که با جاوااسکریپت اضافه شده‌اند
        $(document).on('click', '.remove-input', function() {
            $(this).parent().remove();
        });
    });
</script>
</body>
</html>