<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فیلتر ها</title>
    <!-- MDBootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/css/mdb.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/persian-datepicker@latest/dist/css/persian-datepicker.min.css">
    <style>
        body {
            direction: rtl;
            text-align: right;
            font-family: 'Vazirmatn', sans-serif;
        }
        .filter-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background-color: #f9f9f9;
        }
        .custom-checkbox .form-check-input {
            margin-right: 10px;
        }
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">فیلتر پروژه‌ها</h1>
    <div class="row justify-content-center">
        <div class="col-md-8 filter-card">
            <form action="projects.php" method="get" id="filterForm">
                <!-- وضعیت -->
                <div class="form-group">
                    <label for="status">وضعیت</label>
                    <select class="form-control choices-select" id="status" name="status">
                        <option value="0">همه</option>
						       <option value="1">ایجاد شده</option>
                        <option value="2">در حال انجام</option>
                        <option value="3">انجام شده</option>
                        <option value="4">فاکتور شده</option>    
						<option value="5">لغو شده</option>
                   
                    </select>
                </div>

                <!-- اولویت -->
                <div class="form-group custom-checkbox">
                    <input type="checkbox" class="form-check-input" id="priority" name="priority">
                    <label class="form-check-label" for="priority">اولویت</label>
                </div>

                <!-- حروف الفبا -->
                <div class="form-group">
                    <label for="abc">حروف الفبا</label>
                    <select class="form-control choices-select" id="abc" name="abc">
                        <option value="0">همه</option>
                        <option value="1">کارفرما</option>
                        <option value="2">فازبندی</option>
                        <option value="3">نام کارخانه</option>
                    </select>
                </div>

                <!-- پیشرفت پروژه (چک‌باکس) -->
                <div class="form-group custom-checkbox">
                    <input type="checkbox" class="form-check-input" id="progres" name="progres">
                    <label class="form-check-label" for="progres">پیشرفت پروژه</label>
                </div>

                <!-- تاریخ شروع و پایان -->
                <div class="form-row">
                    <div class="col-md-6 form-group">
                        <label for="start_date">تاریخ شروع</label>
                        <input type="text" class="form-control persian-date" id="start_date" name="start_date">
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="end_date">تاریخ پایان</label>
                        <input type="text" class="form-control persian-date" id="end_date" name="end_date">
                    </div>
                </div>

                <!-- دکمه فیلتر -->
                <button type="submit" class="btn btn-primary btn-block">فیلتر</button>
            </form>
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div class="toast-container">
    <div id="errorToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" style="display: none;">
        <div class="toast-header bg-danger text-white">
            <strong class="mr-auto">خطا</strong>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.4/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/js/mdb.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="https://unpkg.com/persian-date@latest/dist/persian-date.min.js"></script>
<script src="https://unpkg.com/persian-datepicker@latest/dist/js/persian-datepicker.min.js"></script>

<script>
    function setCookie(name, value, days) {
        const d = new Date();
        d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = "expires=" + d.toUTCString();
        document.cookie = name + "=" + value + ";" + expires + ";path=/";
    }

    function getCookie(name) {
        const cname = name + "=";
        const decodedCookie = decodeURIComponent(document.cookie);
        const ca = decodedCookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(cname) == 0) {
                return c.substring(cname.length, c.length);
            }
        }
        return "";
    }

    $(document).ready(function() {
        // Initialize Choices.js
        const status = new Choices('#status', { searchEnabled: true });
        const abc = new Choices('#abc', { searchEnabled: true });

        // Initialize Persian Date Picker
        $(".persian-date").persianDatepicker({
            format: 'YYYY/MM/DD',
            initialValue: false,
            autoClose: true,
            calendar: {
                persian: {
                    locale: 'fa'
                }
            },
            minDate: new persianDate().subtract(10, 'year'),
            maxDate: new persianDate()
        });

        // Load saved filters from cookies
        loadFilters();

        // Form Submission Validation
        $('#filterForm').on('submit', function(e) {
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();

            if (startDate && endDate && new persianDate(startDate).isAfter(endDate)) {
                showErrorMessage('تاریخ شروع نمی‌تواند بعد از تاریخ پایان باشد.');
                e.preventDefault();
                return;
            }

            saveFilters();
        });

        // Save Filters to Cookies
        function saveFilters() {
            const filters = {
                status: $('#status').val(),
                priority: $('#priority').prop('checked'),
                abc: $('#abc').val(),
                progres: $('#progres').prop('checked'),
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val()
            };
            setCookie('filters', JSON.stringify(filters), 7);
        }

        // Load Filters from Cookies
        function loadFilters() {
            const filters = JSON.parse(getCookie('filters'));
            if (filters) {
                $('#status').val(filters.status).trigger('change');
                $('#priority').prop('checked', filters.priority);
                $('#abc').val(filters.abc).trigger('change');
                $('#progres').prop('checked', filters.progres);
                $('#start_date').val(filters.start_date);
                $('#end_date').val(filters.end_date);
            }
        }

        // Show Error Message with Toast
        function showErrorMessage(message) {
            $('.toast-body').text(message);
            $('#errorToast').fadeIn().toast('show');
        }
    });
</script>
</body>
</html>