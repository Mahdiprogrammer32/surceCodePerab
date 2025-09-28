<?php
require_once "database.php";

?>


<!DOCTYPE html>
<html lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>صفحه اصلی</title>
    <!-- بوت‌استرپ 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity=""
          crossorigin="anonymous">
    <!-- آیکون‌ها -->
    <link rel="stylesheet" href="b_icons/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="fontA/css/all.min.css" />
    <!-- استایل‌ها -->
    <link rel="stylesheet" href="checkout.css" />
    <link rel="stylesheet" href="style.css" />
    <!-- Swiper.js -->
    <link rel="stylesheet" href="swiper-bundle.min.css" />

    <!-- CSS برای DataTables -->
    <link rel="stylesheet" href="jquery.dataTables.min.css" />

    <link rel="stylesheet" href="https://unpkg.com/ag-grid-community/styles/ag-grid.css">
    <link rel="stylesheet" href="https://unpkg.com/ag-grid-community/styles/ag-theme-alpine.css">
    <script src="https://unpkg.com/ag-grid-community/dist/ag-grid-community.min.noStyle.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-jalaali/0.9.3/moment-jalaali.min.js"></script>

    <style>
        body {
            overflow: hidden;
        }

        header {
            height: 70px;
            /* ارتفاع ثابت برای هدر */
        }

        .fixed-bottom {
            height: 70px;
            /* ارتفاع ثابت برای منوی پایین */
        }

        /* بخش اصلی با قابلیت اسکرول */
        main {
            height: calc(100vh - 140px);
            /* محاسبه فضای بین هدر و منو */
            overflow-y: auto;
            /* فعال کردن اسکرول */
            padding: 20px;
            margin-top: 70px;
            /* فاصله برای هدر */
            margin-bottom: 70px;
            /* فاصله برای منو */
        }

        .table-responsive {
            overflow-x: auto;
            /* فعال کردن اسکرول افقی */
        }

        table {
            border-radius: 10px;
            overflow: hidden;
        }

        .table th {
            background-color: #007bff;
            color: white;
        }

        .table td {
            vertical-align: middle;
        }
    </style>
</head>

<body dir="rtl">
<div class="l_body">

    <header class="col-12 bg-danger d-flex justify-content-between align-items-center p-1 fixed-top">
        <section class="d-flex justify-content-center align-items-center">
            <img src="images/departeman_black.png" width="100px" height="100px" alt=""
                 class="avatar_departeman p-1" />
            <div class="d-flex flex-column">
                <p class="text-light mt-3 mb-0 fw-bold">دپارتمان برق شکوهیه</p>
                <p class="version text-warning">نسخه تیمی پرو</p>
            </div>
        </section>
        <div class="d-flex justify-content-center align-items-center flex-column">

            <button class="" style="border:none; background: none;" data-bs-toggle="modal" data-bs-target="#time">
                <section id="timer" class="text-white ">
                    timer
                    <script>
                        function getPersianDay(date) {
                            const daysOfWeek = ["شنبه", "یکشنبه", "دوشنبه", "سه‌شنبه", "چهارشنبه", "پنجشنبه", "جمعه"];
                            return daysOfWeek[date.getDay()];
                        }

                        const today = new Date();
                        const persianDay = getPersianDay(today);
                        document.getElementById("timer").innerText = persianDay + " " + today.toLocaleDateString("fa-IR");
                    </script>
                </section>
            </button>
            <button style="background: none; border:none; margin-top:-20px;" data-bs-toggle="modal" data-bs-target="#religious-times" ">

            <div class="clock text-warning" style="font-size:30px"></div>

            <script>var rocket_beacon_data = { "ajax_url": "https:\/\/itsir.ir\/wp-admin\/admin-ajax.php", "nonce": "9dce2b20ce", "url": "https:\/\/itsir.ir\/%D8%B7%D8%B1%D8%A7%D8%AD%DB%8C-%D9%88-%D8%A7%DB%8C%D8%AC%D8%A7%D8%AF-%D8%B3%D8%A7%D8%B9%D8%AA-%D8%AF%DB%8C%D8%AC%DB%8C%D8%AA%D8%A7%D9%84-%D8%A8%D8%A7-%D8%AC%D8%A7%D9%88%D8%A7-%D8%A7%D8%B3%DA%A9%D8%B1", "is_mobile": true, "width_threshold": 1600, "height_threshold": 700, "delay": 500, "debug": true, "status": { "atf": true, "lrc": true }, "elements": "img, video, picture, p, main, div, li, svg, section, header, span", "lrc_threshold": 1800 }
            </script>
            <script data-name="wpr-wpr-beacon"
                    src='https://itsir.ir/wp-content/plugins/wp-rocket/assets/js/wpr-beacon.min.js'></script>
            </button>

        </div>
    </header>



    <div class="col-12 bg-danger fixed-bottom d-flex justify-content-center align-items-center p-0 m-0"
         style="border-radius: 20px 20px 0px 0px;" dir="ltr">
        <ul class="col-12 d-flex align-items-center justify-content-between flex-row" style="line-height: 10px;">
            <li class="d-flex justify-content-center align-items-center flex-column text-center"

                style="line-height: 10px;">
                <a href="index.php"
                   class="d-flex justify-content-center align-items-center flex-column text-white-custom">
                    <span class="bi-columns-gap" style="font-size: medium;"></span>
                    <span style="font-size: medium;">داشبورد</span>
                </a>
            </li>
            <li class="d-flex justify-content-center align-items-center flex-column text-center p-2">
                <a href="product.php"
                   class="d-flex justify-content-center align-items-center flex-column text-white-custom">
                    <span class="fa fa-users-gear" style="font-size: medium;"></span>
                    <span style="font-size: medium;">پیمانکاران</span>
                </a>
            </li>
            <li class="d-flex justify-content-center align-items-center flex-column text-center p-2">
                <a href="oders.php"
                   class="d-flex justify-content-center align-items-center flex-column text-white-custom">
                    <span class="fa fa-user-tie" style="font-size: medium;"></span>
                    <span style="font-size: medium;">کارفرمایان</span>
                </a>
            </li>
            <li class="d-flex justify-content-center align-items-center flex-column text-center text-white p-2">
                <a href="webshop.php"
                   class="d-flex justify-content-center align-items-center flex-column text-white-custom">
                    <span class="fa fa-people-group" style="font-size: medium;"></span>
                    <span style="font-size: medium;">کارمندان</span>
                </a>
            </li>
            <li class="d-flex justify-content-center align-items-center flex-column text-center text-white p-2">
                <a href="projects.php"
                   class="d-flex justify-content-center align-items-center flex-column text-white-custom">
                    <span class="fa fa-diagram-project" style="font-size: medium;"></span>
                    <span style="font-size: medium;">پروژه</span>
                </a>
            </li>
            <li
                    class="nav-item dropdown d-flex justify-content-center align-items-center flex-column text-center text-white p-2">
                <a href="#"
                   class="dropdown-toggle d-flex justify-content-center align-items-center flex-column text-white-custom"
                   id="moreMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="bi-three-dots" style="font-size: medium;"></span>
                    <span style="font-size: medium;">بیشتر</span>
                </a>
                <ul class="dropdown-menu text-center" aria-labelledby="moreMenu" style="f">
                    <li>
                        <a class="dropdown-item" href="register.php" >
                            اضافه کردن
                            <span class="fa fa-warehouse"></span>
                        </a></li>
                    <li><a class="dropdown-item" href="#">
                            انبار
                            <span class="fa fa-warehouse"></span>
                        </a></li>
                    <li><a class="dropdown-item" href="#">
                            درباره ما
                            <span class="fa fa-info-circle"></span>
                        </a></li>
                    <li><a class="dropdown-item" href="#">
                            تماس باما
                            <span class="fa fa-phone"></span>
                        </a></li>
                    <li><a class="dropdown-item" href="#">
                            تنظیمات
                            <span class="fa fa-cog"></span>
                        </a></li>

                </ul>
            </li>
        </ul>
    </div>

    <main class="container hidden-scrollbar">

    </main>


    <footer style="width: 100%; height: fit-content; ">

    </footer>


    <!-- دکمه آبی ثابت با آیکون -->
    <!--        <div class="floating-button">-->
    <!--            <i class="fa fa-plus"></i>-->
    <!--        </div>-->






    <!--MODALS-->


    <div class="modal fade" id="religious-times" tabindex="-1" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">اوقات شرعی</h5>
                    <!-- <button type="button" class="btn-close text-danger" data-bs-dismiss="modal" aria-label="Close"></button> -->
                </div>
                <div class="modal-body custom-scroll d-flex justify-content-center align-items-center"
                     style="overflow-y: auto;">

                    <!-- OghatSharee by www.1abzar.com --->
                    <script type="text/javascript" src="https://1abzar.ir/abzar/tools/azan/v2/?mod=mod3&shahr=18-2"
                            style=""></script>
                    <div style="display:none">
                        <h2><a href="https://www.1abzar.com/abzar/azan2.php">اوقات شرعی</a></h2>
                    </div><!-- OghatSharee by www.1abzar.com --->

                </div>

                <div class="modal-footer" style="height: fit-content;">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">بستن</button>
                    <a href="edit-profile.html" class="btn btn-success text-white text-decoration-none">ویرایش</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addUser" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">عنوان مودال</h5>
                    <button type="button" class="btn btn-close btn-danger" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body custom-scroll" style="overflow: auto;" >
                    محتوای مورد نظر
                </div>
                <div class="modal-footer" style="height: fit-content;">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">بستن</button>
                </div>
            </div>
        </div>
    </div>










    <div class="modal fade" id="time" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">پروفایل</h5>
                    <!-- <button type="button" class="btn-close text-danger" data-bs-dismiss="modal" aria-label="Close"></button> -->
                </div>
                <div class="modal-body custom-scroll d-flex justify-content-center align-items-center"
                     style="overflow-y: auto;">

                    <!-- Calendar by www.1abzar.com --->
                    <!-- <script type="text/javascript" src="https://1abzar.ir/abzar/tools/ruznama/?mod=9"></script><div style="display:none"><h3><a href="https://www.1abzar.com/abzar/ruznama.php">&#1578;&#1602;&#1608;&#1740;&#1605; &#1588;&#1605;&#1587;&#1740;</a></h3></div> -->
                    <!-- Calendar by www.1abzar.com --->
                    <div class="container" style="margin-top:800px ;">


                        <div class="calendar-wrapper">
                            <div class="calendar-base">

                                <div class="year-wrapper">
                                </div>

                                <div class="months">
                                        <span class="month-hover month-letter month-letter-1"
                                              data-num="1">فروردین</span>
                                    <span class="month-hover month-letter month-letter-2"
                                          data-num="2">اردیبهشت</span>
                                    <span class="month-hover month-letter month-letter-3" data-num="3">خرداد</span>
                                    <span class="month-hover month-letter month-letter-4" data-num="4">تیر</span>
                                    <span class="month-hover month-letter month-letter-5" data-num="5">مرداد</span>
                                    <span class="month-hover month-letter month-letter-6" data-num="6">شهریور</span>
                                    <span class="month-hover month-letter month-letter-7" data-num="7">مهر</span>
                                    <span class="month-hover month-letter month-letter-8" data-num="8">آبان</span>
                                    <span class="month-hover month-letter month-letter-9" data-num="9">آذر</span>
                                    <span class="month-hover month-letter month-letter-10" data-num="10">دی</span>
                                    <span class="month-hover month-letter month-letter-11" data-num="11">بهمن</span>
                                    <span class="month-hover month-letter month-letter-12"
                                          data-num="12">اسفند</span>
                                </div>
                                <hr class="month-line" />

                                <div class="days">
                                    <ul class="weeks">
                                        <li>شنبه</li>
                                        <li>یکشنبه</li>
                                        <li>دوشنبه</li>
                                        <li>سه شنبه</li>
                                        <li>چهارشنبه</li>
                                        <li>پنجشنبه</li>
                                        <li>جمعه</li>
                                        <div class="clearfix"></div>
                                    </ul>
                                </div>

                                <div class="num-dates"></div>

                            </div>

                            <div class="calendar-left active-season">

                                <div class="num-date">X</div>
                                <div class="day">X</div>



                            </div>
                        </div>
                        <div class="clearfix"></div>


                    </div>


                </div>

                <div class="modal-footer" style="height: fit-content;">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">بستن</button>
                    <a href="edit-profile.html" class="btn btn-success text-white text-decoration-none">ویرایش</a>
                </div>
            </div>
        </div>
    </div>











    <!-- اسکریپت های DataTable -->
    <script>
        $(document).ready(function () {
            // ایجاد جدول با DataTables
            const table = $('#contractorsTable').DataTable();

            // جستجو در جدول
            $('#search').on('keyup', function () {
                table.search(this.value).draw();
            });

            // تغییر چیدمان
            $('#toggleLayout').on('click', function () {
                const currentLayout = table.order()[0][1]; // چیدمان فعلی
                const newOrder = currentLayout === 'asc' ? 'desc' : 'asc'; // چیدمان جدید
                table.order([0, newOrder]).draw(); // تغییر چیدمان بر اساس نام پروژه
            });
        });
    </script>
</div>

<div class="modal-footer" style="height: fit-content;">
    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">بستن</button>
    <a href="edit-profile.html" class="btn btn-success text-white text-decoration-none">ویرایش</a>
</div>
</div>
</div>
</div>



<!-- اسکریپت‌های مورد نیاز -->
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Swiper.js -->
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<!-- Highcharts -->
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-3d.js"></script>


</div>
<script>
    $(document).ready(function () {
        $('#example').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/fa.json" // پشتیبانی از زبان فارسی
            },
            "paging": true,         // صفحه‌بندی
            "searching": true,      // قابلیت جستجو
            "ordering": true,       // قابلیت مرتب‌سازی
            "order": [[0, "asc"]] // مرتب‌سازی پیش‌فرض بر اساس ستون اول
        });
    });
</script>

<script>
    $(document).ready(function () {
        var x = $("#timer");
        var d = new Date();

        // آرایه‌ای از نام‌های روزهای هفته به فارسی
        var daysOfWeek = ["شنبه", "یکشنبه", "دوشنبه", "سه‌شنبه", "چهارشنبه", "پنجشنبه", "جمعه"];

        // به‌دست آوردن شماره روز هفته (0 = شنبه، 1 = یکشنبه، ...)
        var dayIndex = (d.getDay() + 1) % 7;  // یک واحد اضافه می‌کنیم و اگر به 7 برسد، دوباره به 0 برمی‌گردد.

        // دریافت نام روز هفته به فارسی
        var currentDay = daysOfWeek[dayIndex];

        // تنظیم محتوای HTML برای نمایش روز هفته به فارسی به همراه تاریخ
        x.html('<p class="date text-light text-center">' + currentDay + ' - ' + d.toLocaleDateString('fa-IR') + '</p>');

        console.log(d);
    });



</script>




<script>

    const clock = document.querySelector('.clock');

    const tik = () => {
        const now = new Date();
        const h = now.getHours();
        const m = now.getMinutes();
        const s = now.getSeconds();

        const html = `
                <span>${s}</span>:
                <span>${m}</span> :
                <span>${h}</span>
  `;

        clock.innerHTML = html;

    };

    setInterval(tik, 1000);

</script>



</body>



</html>