<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
require_once "database.php"; // Include your database connection
global $conn;

// Function to display SweetAlert messages
function showAlert($title, $text, $icon) {
    echo "<script>
        Swal.fire({
            title: '$title',
            text: '$text',
            icon: '$icon',
            confirmButtonText: 'باشه'
        });
    </script>";
}

// Check if user_id is set in the URL
if (isset($_GET['user_id'])) {
    $userID = filter_var($_GET['user_id'], FILTER_VALIDATE_INT);
    if ($userID === false) {
        showAlert('خطا', 'شناسه کاربری نامعتبر است.', 'error');
        exit;
    }

    // Prepare and execute the SQL statement to fetch the employer details
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Check if the employer record exists
    if (!$row) {
        showAlert('خطا', 'کارفرمایی با این شناسه پیدا نشد.', 'error');
        exit;
    }

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize and validate form inputs
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $role = $_POST['role'];

        // Prepare and execute the update statement
        $updateStmt = $conn->prepare("UPDATE users SET name = ?, role = ?, phone = ?, address = ? WHERE id = ?");
        $updateStmt->bind_param("ssssi", $name, $role, $phone, $address, $userID);
        $updateStmt->execute();

        // Check if the update was successful
        if ($updateStmt->affected_rows > 0) {
            echo "<script>
            Swal.fire({
                title: 'موفقیت!',
                text: 'اطلاعات کارمند با موفقیت به‌روزرسانی شد.',
                icon: 'success'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'staff.php'; // Redirect to your desired page
                }
            });
            </script>";
        } else {
            showAlert('اطلاعات', 'هیچ تغییری اعمال نشد.', 'info');
        }
        $updateStmt->close();
    }
} else {
    showAlert('خطا', 'خطا: پارامتر user_id وجود ندارد.', 'error');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ویرایش کارمندان</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <!-- jQuery باید قبل از Select2 لود شود -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        body {
            direction: rtl;
            background-color: #f8f9fa;
            font-family: 'Vazir', Tahoma, Arial, sans-serif;
        }
       
        .container {
            margin-top: 50px;
            background-color: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
            max-width: 800px;
        }
       
        .form-header {
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 25px;
            padding-bottom: 15px;
            position: relative;
        }
       
        .form-header:after {
            content: '';
            position: absolute;
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, #4e73df, #36b9cc);
            bottom: -2px;
            right: 0;
        }
       
        .form-label {
            font-weight: 600;
            color: #495057;
        }
       
        .form-control, .form-select {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ced4da;
            transition: all 0.3s;
        }
       
        .form-control:focus, .form-select:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
       
        .btn {
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
       
        .btn-success {
            background-color: #1cc88a;
            border-color: #1cc88a;
        }
       
        .btn-success:hover {
            background-color: #169a6e;
            border-color: #169a6e;
        }
       
        .btn-danger {
            background-color: #e74a3b;
            border-color: #e74a3b;
        }
       
        .btn-danger:hover {
            background-color: #c23321;
            border-color: #c23321;
        }
       
        .select2-container--default .select2-selection--single {
            height: 46px;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #ced4da;
        }
       
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px;
        }
       
        .form-icon {
            margin-left: 10px;
            color: #4e73df;
        }
       
        .form-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }
       
        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin-top: 20px;
            }
        }
        
        .phone-input-group {
            display: flex;
            align-items: center;
        }
        
        .phone-input-group .form-control {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        
        .phone-input-group .btn {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="form-header">
        <h2 class="text-center">ویرایش اطلاعات کارمند</h2>
    </div>
    <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>

        <div class="mb-3">
            <label for="role" class="form-label">نقش:</label>
            <select class="form-select" id="role" name="role" required>
                <option value="<?php echo htmlspecialchars($row['role']); ?>"><?php echo htmlspecialchars($row['role']); ?></option>
                <option value="مدیر">مدیر</option>
                <option value="ناظر فنی">ناظر فنی</option>
                <option value="ناظر کیفی">ناظر کیفی</option>
                <option value="ناظر ارشد">ناظر ارشد</option>
                <option value="انبار دار">انبار دار</option>
                <option value="حسابدار">حسابدار</option>
            </select>
            <div class="invalid-feedback">لطفاً نقش خود را انتخاب کنید.</div>
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">کارمند :</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>
            <div class="invalid-feedback">لطفاً کارمند را وارد کنید.</div>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">شماره تلفن:</label>
            <div class="input-group phone-input-group">
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($row['phone']); ?>" required>
                <button type="button" id="selectPhone" class="btn btn-primary">
                    <i class="bi bi-person-rolodex"></i>
                </button>
            </div>
            <div class="invalid-feedback">لطفاً شماره تلفن را وارد کنید.</div>
        </div>
        
        <div class="mb-3">
            <label for="address" class="form-label">آدرس:</label>
            <textarea class="form-control" id="address" name="address" required><?php echo htmlspecialchars($row['address']); ?></textarea>
        </div>
        
        <div class="d-flex justify-content-center align-items-center">
            <button type="submit" class="btn btn-success" name="sub" style="margin-inline: 20px;">به‌روزرسانی</button>
            <a href="staff.php" class="btn btn-danger">بازگشت</a>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('.form-select').select2({
        dir: "rtl",
        width: '100%',
        placeholder: "انتخاب کنید"
    });
    
    // Form validation
    (function() {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
});

// انتخاب شماره تلفن
document.getElementById('selectPhone').addEventListener('click', function() {
    // بررسی پشتیبانی از Contact Picker API
    if ('contacts' in navigator && 'ContactsManager' in window) {
        navigator.contacts.select(['tel'], { multiple: false })
            .then(contacts => {
                if (contacts.length > 0) {
                    const contact = contacts[0];
                    const phoneNumbers = contact.tel || [];

                    if (phoneNumbers.length > 0) {
                        // دریافت اولین شماره تلفن معتبر
                        let validPhoneNumber = null;
                        for (let phone of phoneNumbers) {
                            if (phone && typeof phone === 'string' && phone.trim() !== '') {
                                validPhoneNumber = phone;
                                break;
                            } else if (phone && phone.value && phone.value.trim() !== '') {
                                validPhoneNumber = phone.value;
                                break;
                            }
                        }

                        if (validPhoneNumber) {
                            // پاکسازی شماره تلفن (حذف فاصله‌ها، خط تیره و غیره)
                            validPhoneNumber = validPhoneNumber.replace(/[^\d+]/g, '');
                            
                            // قرار دادن شماره تلفن در فیلد ورودی
                            document.getElementById('phone').value = validPhoneNumber;
                            
                            // نمایش پیام موفقیت
                            Swal.fire({
                                icon: 'success',
                                title: 'انتخاب شد!',
                                text: 'شماره تلفن با موفقیت انتخاب شد.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'خطا!',
                                text: 'شماره تلفن معتبری پیدا نشد.'
                            });
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطا!',
                            text: 'این مخاطب هیچ شماره تلفنی ندارد.'
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'توجه!',
                        text: 'هیچ مخاطبی انتخاب نشد.'
                    });
                }
            })
            .catch(err => {
                console.error('Contact Picker Error:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'خطا!',
                    text: 'خطا در انتخاب مخاطب: ' + (err.message || 'دسترسی به مخاطبین امکان‌پذیر نیست.')
                });
            });
    } else {
        // پیام برای مرورگرهایی که Contact Picker API را پشتیبانی نمی‌کنند
        Swal.fire({
            icon: 'warning',
            title: 'عدم پشتیبانی',
            text: 'دفترچه تلفن در مرورگر شما پشتیبانی نمی‌شود. لطفا شماره را به صورت دستی وارد کنید.'
        });
    }
});
</script>
</body>
</html>
