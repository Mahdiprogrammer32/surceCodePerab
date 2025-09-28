<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>انتخاب شماره تلفن</title>
</head>
<body>
<h2>فرم ثبت اطلاعات</h2>
<form id="myForm" action="submit.php" method="POST">
    <!-- فیلد نام -->
    <label for="name">نام:</label><br>
    <input type="text" id="name" name="name" required><br><br>

    <!-- فیلد شماره تلفن -->
    <label for="phone">شماره تلفن:</label><br>
    <input type="tel" id="phone" name="phone" placeholder="شماره تلفن خود را وارد کنید" required readonly>
    <button type="button" id="selectPhone">انتخاب از دفترچه تلفن</button><br><br>

    <!-- دکمه ارسال فرم -->
    <button type="submit">ارسال</button>
</form>

<script>
    // دکمه انتخاب شماره تلفن
    document.getElementById('selectPhone').addEventListener('click', function () {
        if ('contacts' in navigator) {
            // انتخاب تماس از دفترچه تلفن
            navigator.contacts.select(['tel'], { multiple: false })
                .then(contacts => {
                    if (contacts.length > 0) {
                        const contact = contacts[0];
                        const phoneNumbers = contact.tel || []; // لیست شماره‌های تلفن

                        if (phoneNumbers.length > 0) {
                            let validPhoneNumber = null;

                            // بررسی تمام شماره‌های تلفن
                            for (let phone of phoneNumbers) {
                                if (phone.value && phone.value.trim() !== '') {
                                    validPhoneNumber = phone.value;
                                    break; // اولین شماره معتبر را انتخاب کنید
                                }
                            }

                            if (validPhoneNumber) {
                                document.getElementById('phone').value = validPhoneNumber;
                            } else {
                                alert('شماره تلفن معتبری پیدا نشد.');
                            }
                        } else {
                            alert('این تماس هیچ شماره تلفنی ندارد.');
                        }
                    } else {
                        alert('هیچ تماسی انتخاب نشد.');
                    }
                })
                .catch(err => {
                    alert('خطا: ' + err.message);
                });
        } else {
            alert('Contact Picker API در مرورگر شما پشتیبانی نمی‌شود.');
        }
    });
</script>
</body>
</html>