<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فرم با Select2</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <style>
        .invalid-feedback {
            display: none;
        }
        .was-validated .form-select:invalid ~ .invalid-feedback {
            display: block;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>فرم انتخاب کارفرما</h2>
    <form method="post" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="role_employer" class="form-label">کارفرما:</label>
            <select class="form-select" id="role_employer" name="role_employer" required>
                <option value="">انتخاب کنید...</option>
                <?php
             require_once "database.php";
             global $conn;
                // بارگذاری داده‌ها از پایگاه داده
                $sqlk = "SELECT * FROM employees";
                $result = $conn->query($sqlk);
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($row['id'] ?? '') . '">' . htmlspecialchars($row['manager'] ?? '') . '</option>';
                    }
                } else {
                    echo '<option value="">هیچ کاربری یافت نشد</option>';
                }

                // بستن اتصال
                $conn->close();
                ?>
            </select>
            <div class="invalid-feedback">لطفاً کارفرما خود را انتخاب کنید.</div>
        </div>
        <button type="submit" class="btn btn-primary">ارسال</button>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('#role_employer').select2({
            placeholder: "انتخاب کنید...",
            allowClear: true,
            minimumResultsForSearch: 0 // این خط قابلیت جستجو را بدون توجه به تعداد گزینه‌ها فعال می‌کند
        });
    });
</script>

</body>
</html>