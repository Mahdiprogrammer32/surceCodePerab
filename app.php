<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<?php
require_once "database.php";
global $conn;
// دریافت تعداد کارمندان برای تعیین شناسه پروفایل (یکبار اجرا)
$sql = "SELECT COUNT(*) AS total FROM employees";
$result1 = $conn->query($sql);
$row = $result1->fetch_assoc();
$totalEmployees = $row['total'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // اعتبارسنجی اولیه داده‌ها
    $manager = filter_var($_POST['manager'], FILTER_SANITIZE_STRING);
    $factory = filter_var($_POST['factory'], FILTER_SANITIZE_STRING);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_NUMBER_INT);
   $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
    $role = "کارفرما";
    $project_id = $_POST['project_id'];


    // بررسی وجود کارمند با تلفن مشابه
    $stmt = $conn->prepare("SELECT * FROM employees WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<script>
            Swal.fire({
                icon: "error",
                title: "خطا!",
                text: "کارمندی با این شماره تلفن قبلاً ثبت شده است."
            });
        </script>';
    } else {
        // درج رکورد جدید
        $stmt = $conn->prepare("INSERT INTO employees (manager, factory, phone, address, role, project_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $manager, $factory, $phone, $address, $role, $project_id);


        if ($stmt->execute()) {
            echo '<script>
                Swal.fire({
                    icon: "success",
                    title: "موفقیت آمیز!",
                    text: "کارمند جدید با موفقیت ثبت شد."
                });
            </script>';
        } else {
            echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "خطا!",
                    text: "خطایی در ثبت اطلاعات رخ داده است: " . $stmt->error
                });
            </script>';
        }
    }

    $stmt->close();
}
$conn->close();
?>



<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود اطلاعات کارمندان</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }
        form {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        input, button {
            display: block;
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            font-size: 16px;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<h2>ورود اطلاعات کارمند</h2>
<form id="employeeForm" action="app.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm();">
    <input type="text" name="manager" placeholder="مدیر" required>
    <input type="text" name="factory" placeholder="کارخانه" required>
    <input type="text" name="phone" placeholder="تلفن" required>
    <textarea name="address" id="address" cols="30" rows="10"></textarea>
    <input type="text" name="project_id" placeholder="شناسه پروژه" value="<?php echo $totalEmployees;  ?>" required>
    <button type="submit">ارسال</button>
</form>

<script>
    function validateForm() {
        // انجام اعتبارسنجی ابتدایی
        const form = document.getElementById('employeeForm');
        const inputs = form.getElementsByTagName('input');

        for (let i = 0; i < inputs.length; i++) {
            if (inputs[i].value === '') {
                Swal.fire({
                    icon: 'error',
                    title: 'خطا!',
                    text: 'لطفا تمامی فیلدها را پر کنید!',
                });
                return false;
            }
        }

        // نمایش SweetAlert برای تایید ارسال فرم
        function showS() {
            Swal.fire({
                icon: 'success',
                title: 'موفقیت آمیز!',
                text: 'اطلاعات با موفقیت ثبت شد.',
            });
        }
         function showW(){
            Swal.fire({
                icon: "error",
                title: "خطا!",
                text: "خطایی در ثبت اطلاعات رخ داده است. لطفا مجددا تلاش کنید. " ,
                 showConfirmButton: true,
                 confirmButtonText: "تلاش مجدد"
         });

        }




        return true;
    }
</script>

</body>
</html>
