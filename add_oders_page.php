<?php
require_once "database.php";
require_once "require_once/header.php";
require_once "require_once/menu.php";
global $conn;

// بهینه‌سازی لود منابع - فقط منابع ضروری
$cssLinks = [
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'b_icons/font/bootstrap-icons.min.css',
    'assets/css/style.css',
    'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css'
];

$jsLinks = [
    'https://code.jquery.com/jquery-3.6.0.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11'
];

// کوئری دیتابیس
$sql = "SELECT COUNT(*) AS total FROM employees";
$result1 = $conn->query($sql);
$row = $result1->fetch_assoc();
$totalEmployees = $row['total'] + 1;

// پردازش فرم
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $manager = $_POST['manager'] ?? '';
    $factory = $_POST['factory'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $role = "کارفرما";
    $project_id = $totalEmployees;

    if (empty($manager) || empty($factory) || empty($phone)) {
        $alertScript = "
            Swal.fire({
                icon: 'error',
                title: 'خطا!',
                text: 'لطفا تمامی فیلدها را پر کنید.',
                confirmButtonText: 'باشه',
                showClass: {popup: 'animate__animated animate__fadeIn'},
                hideClass: {popup: 'animate__animated animate__fadeOut'}
            });
        ";
    } else {
        // بررسی تکراری بودن
        $stmt = $conn->prepare("SELECT * FROM employees WHERE phone = ? OR factory = ?");
        $stmt->bind_param("ss", $phone, $factory);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $alertScript = "
                Swal.fire({
                    icon: 'error',
                    title: 'خطا!',
                    text: 'نام کارخانه یا شماره تلفن تکراری می باشد.',
                    confirmButtonText: 'باشه',
                    showClass: {popup: 'animate__animated animate__fadeIn'},
                    hideClass: {popup: 'animate__animated animate__fadeOut'}
                });
            ";
        } else {
            // درج رکورد جدید
            $stmt = $conn->prepare("INSERT INTO employees (manager, factory, phone, address, role, project_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $manager, $factory, $phone, $address, $role, $project_id);

            if ($stmt->execute()) {
                $alertScript = "
                    Swal.fire({
                        icon: 'success',
                        title: 'موفقیت آمیز!',
                        text: 'کارفرمای جدید با موفقیت ثبت شد.',
                        confirmButtonText: 'عالی',
                        showClass: {popup: 'animate__animated animate__fadeIn'},
                        hideClass: {popup: 'animate__animated animate__fadeOut'}
                    }).then(() => {
                        window.location.href = 'oders.php';
                    });
                ";
            } else {
                $alertScript = "
                    Swal.fire({
                        icon: 'error',
                        title: 'خطا!',
                        text: 'خطایی در ثبت اطلاعات رخ داده است: " . $stmt->error . "',
                        confirmButtonText: 'باشه',
                        showClass: {popup: 'animate__animated animate__fadeIn'},
                        hideClass: {popup: 'animate__animated animate__fadeOut'}
                    });
                ";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ثبت کارفرمای جدید</title>
    <?php foreach ($cssLinks as $link) echo '<link rel="stylesheet" href="'.$link.'">'; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400..700&display=swap" rel="stylesheet">

    <style>
/* استایل گرافیکی و انیمیشنی فوق‌العاده مدرن */
:root {
  /* پالت رنگ نئون و گرادیانت */
  --primary: #00c6ff;
  --primary-light: #0072ff;
  --primary-dark: #00429b;
  --secondary: #ff00c8;
  --secondary-light: #ff5bac;
  --accent: #00ff9d;
  --accent-dark: #00b377;
  --text-dark: #1a1a2e;
  --text-light: #ffffff;
  --background: #0f0f1a;
  --card-bg: #16213e;
  --card-bg-hover: #1e2a4a;
  --success: #00ffbb;
  --warning: #ffbb00;
  --danger: #ff2e63;
  --info: #00d2ff;
  
  /* سایه‌های نئون */
  --shadow-sm: 0 2px 10px rgba(0, 198, 255, 0.15);
  --shadow-md: 0 5px 20px rgba(0, 198, 255, 0.2);
  --shadow-lg: 0 10px 30px rgba(0, 198, 255, 0.25);
  --shadow-hover: 0 15px 40px rgba(0, 198, 255, 0.3);
  --shadow-neon: 0 0 10px rgba(0, 198, 255, 0.5), 0 0 20px rgba(0, 198, 255, 0.3), 0 0 30px rgba(0, 198, 255, 0.1);
  --shadow-neon-purple: 0 0 10px rgba(255, 0, 200, 0.5), 0 0 20px rgba(255, 0, 200, 0.3), 0 0 30px rgba(255, 0, 200, 0.1);
  --shadow-neon-green: 0 0 10px rgba(0, 255, 157, 0.5), 0 0 20px rgba(0, 255, 157, 0.3), 0 0 30px rgba(0, 255, 157, 0.1);
  
  /* ویژگی‌های طراحی */
  --border-radius: 16px;
  --border-radius-lg: 24px;
  --border-radius-sm: 8px;
  --border-radius-circle: 50%;
  --border-width: 2px;
  --transition-fast: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  --transition-medium: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  --transition-slow: all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  --transition-bounce: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

/* انیمیشن‌های پیشرفته */
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(40px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInDown {
  from { opacity: 0; transform: translateY(-40px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes slideInRight {
  from { opacity: 0; transform: translateX(80px); }
  to { opacity: 1; transform: translateX(0); }
}

@keyframes slideInLeft {
  from { opacity: 0; transform: translateX(-80px); }
  to { opacity: 1; transform: translateX(0); }
}

@keyframes zoomInRotate {
  from { opacity: 0; transform: scale(0.5) rotate(-10deg); }
  to { opacity: 1; transform: scale(1) rotate(0); }
}

@keyframes bounceIn {
  0% { opacity: 0; transform: scale(0.3); }
  40% { opacity: 1; transform: scale(1.1); }
  60% { transform: scale(0.9); }
  80% { transform: scale(1.03); }
  100% { transform: scale(1); }
}

@keyframes floatY {
  0% { transform: translateY(0); }
  50% { transform: translateY(-15px); }
  100% { transform: translateY(0); }
}

@keyframes floatX {
  0% { transform: translateX(0); }
  50% { transform: translateX(15px); }
  100% { transform: translateX(0); }
}

@keyframes pulse {
  0% { transform: scale(1); box-shadow: var(--shadow-md); }
  50% { transform: scale(1.05); box-shadow: var(--shadow-lg), var(--shadow-neon); }
  100% { transform: scale(1); box-shadow: var(--shadow-md); }
}

@keyframes neonPulse {
  0% { box-shadow: var(--shadow-md); }
  50% { box-shadow: var(--shadow-neon); }
  100% { box-shadow: var(--shadow-md); }
}

@keyframes shimmer {
  0% { background-position: -1000px 0; }
  100% { background-position: 1000px 0; }
}

@keyframes gradientFlow {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

@keyframes rotateGradient {
  0% { background-position: 0% 0%; }
  100% { background-position: 100% 100%; }
}

@keyframes borderGlow {
  0% { border-color: var(--primary); box-shadow: 0 0 10px var(--primary); }
  33% { border-color: var(--secondary); box-shadow: 0 0 10px var(--secondary); }
  66% { border-color: var(--accent); box-shadow: 0 0 10px var(--accent); }
  100% { border-color: var(--primary); box-shadow: 0 0 10px var(--primary); }
}

@keyframes textGlow {
  0% { text-shadow: 0 0 10px var(--primary); }
  33% { text-shadow: 0 0 10px var(--secondary); }
  66% { text-shadow: 0 0 10px var(--accent); }
  100% { text-shadow: 0 0 10px var(--primary); }
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
  20%, 40%, 60%, 80% { transform: translateX(5px); }
}

/* استایل‌های عمومی */
body {
  background: var(--background);
  background-image: 
    radial-gradient(circle at 10% 20%, rgba(0, 198, 255, 0.05) 0%, transparent 20%),
    radial-gradient(circle at 90% 80%, rgba(255, 0, 200, 0.05) 0%, transparent 20%),
    radial-gradient(circle at 50% 50%, rgba(0, 255, 157, 0.05) 0%, transparent 30%);
  color: var(--text-light);
  font-family: "Noto Nastaliq Urdu", serif;
  line-height: 1.8;
  overflow-x: hidden;
  min-height: 100vh;
}

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 30px;
}

/* کارت‌ها و باکس‌ها */
.card {
  background: var(--card-bg);
  background-image: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, transparent 100%);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-md);
  overflow: hidden;
  transition: var(--transition-medium);
  margin-bottom: 30px;
  border: var(--border-width) solid rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  animation: fadeInUp 0.8s ease forwards;
  position: relative;
  z-index: 1;
}

.card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, var(--primary-light) 0%, var(--secondary) 100%);
  opacity: 0;
  z-index: -1;
  transition: var(--transition-medium);
  border-radius: var(--border-radius);
}

.card:hover {
  transform: translateY(-10px) scale(1.02);
  box-shadow: var(--shadow-hover), var(--shadow-neon);
  border-color: rgba(255, 255, 255, 0.1);
}

.card:hover::before {
  opacity: 0.1;
}

.card-header {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
  background-size: 200% 200%;
  animation: gradientFlow 5s ease infinite;
  color: var(--text-light);
  padding: 25px;
  font-weight: bold;
  border-bottom: none;
  position: relative;
  overflow: hidden;
}

.card-header::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transform: translateX(-100%);
  animation: shimmer 3s infinite;
}

.card-body {
  padding: 30px;
  position: relative;
}

/* فرم‌ها و ورودی‌ها */
.form-control, .form-select {
  background: rgba(255, 255, 255, 0.05);
  border-radius: var(--border-radius-sm);
  padding: 15px 20px;
  border: var(--border-width) solid rgba(255, 255, 255, 0.1);
  color: var(--text-light);
  transition: var(--transition-bounce);
  backdrop-filter: blur(5px);
  box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.1);
}

.form-control:focus, .form-select:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(0, 198, 255, 0.25), var(--shadow-neon);
  transform: translateY(-5px);
  background: rgba(255, 255, 255, 0.1);
}

.form-control::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.form-label {
  font-weight: 600;
  margin-bottom: 12px;
  color: var(--text-light);
  display: block;
  transition: var(--transition-fast);
  text-transform: uppercase;
  letter-spacing: 1px;
  font-size: 0.9rem;
  position: relative;
}

.form-label::after {
  content: '';
  position: absolute;
  bottom: -5px;
  left: 0;
  width: 30px;
  height: 2px;
  background: var(--primary);
  transition: var(--transition-fast);
}

.form-group:hover .form-label::after {
  width: 50px;
  background: var(--accent);
}

.form-group {
  margin-bottom: 30px;
  position: relative;
}

.form-group:nth-child(odd) {
  animation: slideInLeft 0.7s ease forwards;
}

.form-group:nth-child(even) {
  animation: slideInRight 0.7s ease forwards;
}

/* دکمه‌ها */
.btn {
  border-radius: var(--border-radius-sm);
  padding: 14px 30px;
  font-weight: 600;
  letter-spacing: 1px;
  transition: var(--transition-bounce);
  position: relative;
  overflow: hidden;
  text-transform: uppercase;
  font-size: 14px;
  box-shadow: var(--shadow-md);
  border: none;
  z-index: 1;
}

.btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  transition: var(--transition-fast);
  z-index: -1;
}

.btn::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
  z-index: -2;
  transition: var(--transition-medium);
  border-radius: var(--border-radius-sm);
}

.btn:hover::before {
  left: 100%;
}

.btn:hover {
  transform: translateY(-5px) scale(1.05);
  box-shadow: var(--shadow-lg), var(--shadow-neon);
  color: white;
}

.btn:active {
  transform: translateY(2px) scale(0.95);
}

.btn-primary {
  background: transparent;
  color: white;
}

.btn-primary::after {
  background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
}

.btn-danger {
  background: transparent;
  color: white;
}

.btn-danger::after {
  background: linear-gradient(135deg, #d32f2f 0%, var(--danger) 100%);
}

.btn-danger:hover {
  box-shadow: var(--shadow-lg), 0 0 15px rgba(255, 46, 99, 0.5);
}

.btn-success {
  background: transparent;
  color: white;
}

.btn-success::after {
  background: linear-gradient(135deg, var(--accent-dark) 0%, var(--success) 100%);
}

.btn-success:hover {
  box-shadow: var(--shadow-lg), 0 0 15px rgba(0, 255, 187, 0.5);
}

.btn-info {
  background: transparent;
  color: white;
}

.btn-info::after {
  background: linear-gradient(135deg, #0288d1 0%, var(--info) 100%);
}

.btn-info:hover {
  box-shadow: var(--shadow-lg), 0 0 15px rgba(0, 210, 255, 0.5);
}

.btn-warning {
  background: transparent;
  color: white;
}

.btn-warning::after {
  background: linear-gradient(135deg, #f57c00 0%, var(--warning) 100%);
}

.btn-warning:hover {
  box-shadow: var(--shadow-lg), 0 0 15px rgba(255, 187, 0, 0.5);
}

/* تصویر پروفایل */
.profile-img-container {
  position: relative;
  width: 180px;
  height: 180px;
  margin: 0 auto 40px;
  border-radius: var(--border-radius-circle);
  overflow: hidden;
  box-shadow: var(--shadow-lg);
  border: 4px solid transparent;
  animation: borderGlow 5s infinite, floatY 6s ease-in-out infinite;
  background: linear-gradient(var(--card-bg), var(--card-bg)) padding-box,
              linear-gradient(135deg, var(--primary) 0%, var(--secondary) 50%, var(--accent) 100%) border-box;
}

.profile-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: var(--transition-medium);
  filter: brightness(0.9) contrast(1.1);
}

.profile-img-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
  color: white;
  text-align: center;
  padding: 15px 0;
  font-size: 14px;
  transform: translateY(100%);
  transition: var(--transition-fast);
}

.profile-img-container:hover .profile-img-overlay {
  transform: translateY(0);
}

.profile-img-container:hover .profile-img {
  transform: scale(1.1);
  filter: brightness(1.1) contrast(1.1);
}

/* افکت‌های متنی */
.text-gradient {
  background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 50%, var(--accent) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-size: 200% 200%;
  animation: gradientFlow 5s ease infinite;
  font-weight: bold;
}

.text-glow {
  text-shadow: 0 0 5px var(--primary), 0 0 10px rgba(0, 198, 255, 0.5);
  animation: textGlow 5s infinite;
}

.text-shadow {
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

/* افکت‌های لودینگ */
.loading-shimmer {
  background: linear-gradient(90deg, var(--card-bg) 25%, rgba(255, 255, 255, 0.1) 50%, var(--card-bg) 75%);
  background-size: 1000px 100%;
  animation: shimmer 2s infinite linear;
  border-radius: var(--border-radius);
  min-height: 100px;
}

/* افکت‌های هاور */
.hover-float {
  transition: var(--transition-bounce);
}

.hover-float:hover {
  transform: translateY(-10px);
  box-shadow: var(--shadow-hover);
}

.hover-scale {
  transition: var(--transition-bounce);
}

.hover-scale:hover {
  transform: scale(1.1);
}

.hover-rotate {
  transition: var(--transition-bounce);
}

.hover-rotate:hover {
  transform: rotate(5deg) scale(1.05);
}

.hover-neon {
  transition: var(--transition-medium);
}

.hover-neon:hover {
  box-shadow: var(--shadow-neon);
}

.hover-neon-purple:hover {
  box-shadow: var(--shadow-neon-purple);
}

.hover-neon-green:hover {
  box-shadow: var(--shadow-neon-green);
}

/* کلاس‌های انیمیشن */
.animate-fade-in-up {
  animation: fadeInUp 0.8s ease forwards;
}

.animate-fade-in-down {
  animation: fadeInDown 0.8s ease forwards;
}

.animate-slide-right {
  animation: slideInRight 0.8s ease forwards;
}

.animate-slide-left {
  animation: slideInLeft 0.8s ease forwards;
}

.animate-zoom-in {
  animation: zoomInRotate 0.8s ease forwards;
}

.animate-bounce-in {
  animation: bounceIn 1s ease forwards;
}

.animate-float-y {
  animation: floatY 6s ease-in-out infinite;
}

.animate-float-x {
  animation: floatX 6s ease-in-out infinite;
}

.animate-pulse {
  animation: pulse 3s infinite;
}

.animate-neon-pulse {
  animation: neonPulse 3s infinite;
}

.animate-border-glow {
  animation: borderGlow 5s infinite;
}

.animate-shake {
  animation: shake 0.8s ease-in-out;
}

/* تاخیر در انیمیشن */
.delay-100 { animation-delay: 0.1s; }
.delay-200 { animation-delay: 0.2s; }
.delay-300 { animation-delay: 0.3s; }
.delay-400 { animation-delay: 0.4s; }
.delay-500 { animation-delay: 0.5s; }
.delay-600 { animation-delay: 0.6s; }
.delay-700 { animation-delay: 0.7s; }
.delay-800 { animation-delay: 0.8s; }
.delay-900 { animation-delay: 0.9s; }
.delay-1000 { animation-delay: 1s; }

/* استایل‌های جدول */
.table-custom {
  border-radius: var(--border-radius);
  overflow: hidden;
  box-shadow: var(--shadow-md);
  background: var(--card-bg);
  border: var(--border-width) solid rgba(255, 255, 255, 0.05);
}

.table-custom thead {
  background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
  color: var(--text-light);
}

.table-custom th {
  font-weight: 600;
  padding: 18px;
  border: none;
  text-transform: uppercase;
  letter-spacing: 1px;
  font-size: 0.9rem;
}

.table-custom td {
  padding: 18px;
  border-color: rgba(255, 255, 255, 0.05);
  transition: var(--transition-fast);
  color: var(--text-light);
}

.table-custom tbody tr {
  transition: var(--transition-fast);
  background: rgba(255, 255, 255, 0.02);
}

.table-custom tbody tr:nth-child(odd) {
  background: rgba(255, 255, 255, 0.01);
}

.table-custom tbody tr:hover {
  background: rgba(0, 198, 255, 0.1);
  transform: scale(1.01);
}

/* استایل‌های آلرت */
.alert {
  border-radius: var(--border-radius);
  padding: 20px 25px;
  border: none;
  box-shadow: var(--shadow-md);
  animation: fadeInUp 0.5s ease forwards;
  background: var(--card-bg);
  border-left: 5px solid transparent;
  position: relative;
  overflow: hidden;
}

.alert::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, transparent 100%);
  z-index: -1;
}

.alert-success {
  border-color: var(--success);
  color: var(--success);
}

.alert-success::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 5px;
  height: 100%;
  background: var(--success);
  box-shadow: 0 0 15px var(--success);
}

.alert-danger {
  border-color: var(--danger);
  color: var(--danger);
}

.alert-danger::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 5px;
  height: 100%;
  background: var(--danger);
  box-shadow: 0 0 15px var(--danger);
}

.alert-warning {
  border-color: var(--warning);
  color: var(--warning);
}

.alert-warning::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 5px;
  height: 100%;
  background: var(--warning);
  box-shadow: 0 0 15px var(--warning);
}

.alert-info {
  border-color: var(--info);
  color: var(--info);
}

.alert-info::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 5px;
  height: 100%;
  background: var(--info);
  box-shadow: 0 0 15px var(--info);
}

/* استایل‌های بج */
.badge {
  padding: 8px 15px;
  border-radius: 30px;
  font-weight: 600;
  letter-spacing: 1px;
  box-shadow: var(--shadow-sm);
  position: relative;
  overflow: hidden;
  text-transform: uppercase;
  font-size: 0.75rem;
}

.badge::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, transparent 100%);
  z-index: -1;
}

.badge-primary {
  background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
  color: var(--text-light);
}

.badge-success {
  background: linear-gradient(135deg, var(--accent-dark) 0%, var(--success) 100%);
  color: var(--text-light);
}

.badge-danger {
  background: linear-gradient(135deg, #d32f2f 0%, var(--danger) 100%);
  color: var(--text-light);
}

.badge-warning {
  background: linear-gradient(135deg, #f57c00 0%, var(--warning) 100%);
  color: var(--text-dark);
}

.badge-info {
  background: linear-gradient(135deg, #0288d1 0%, var(--info) 100%);
  color: var(--text-light);
}

/* کارت‌های ویژه */
.glass-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-radius: var(--border-radius);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: var(--shadow-md);
  padding: 30px;
  transition: var(--transition-medium);
}

.glass-card:hover {
  box-shadow: var(--shadow-hover), var(--shadow-neon);
  transform: translateY(-10px);
}

.neon-card {
  background: var(--card-bg);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-md);
  padding: 30px;
  position: relative;
  border: 2px solid var(--primary);
  transition: var(--transition-medium);
}

.neon-card::before {
  content: '';
  position: absolute;
  top: -2px;
  left: -2px;
  right: -2px;
  bottom: -2px;
  background: linear-gradient(45deg, var(--primary), var(--secondary), var(--accent), var(--primary));
  background-size: 400% 400%;
  z-index: -1;
  border-radius: calc(var(--border-radius) + 2px);
  animation: gradientFlow 10s ease infinite;
  opacity: 0.5;
  filter: blur(10px);
  transition: var(--transition-medium);
}

.neon-card:hover {
  box-shadow: var(--shadow-hover);
  transform: translateY(-10px) scale(1.02);
}

.neon-card:hover::before {
  opacity: 1;
  filter: blur(15px);
}

/* استایل‌های واکنش‌گرا */
@media (max-width: 992px) {
  .card {
    margin-bottom: 25px;
  }
  
  .card-body {
    padding: 25px;
  }
  
  .btn {
    padding: 12px 25px;
  }
  
  .profile-img-container {
    width: 150px;
    height: 150px;
  }
}

@media (max-width: 768px) {
  .card {
    margin-bottom: 20px;
  }
  
  .card-body {
    padding: 20px;
  }
  
  .btn {
    padding: 10px 20px;
    font-size: 13px;
  }
  
  .profile-img-container {
    width: 120px;
    height: 120px;
  }
  
  .form-control, .form-select {
    padding: 12px 15px;
  }
  
  .table
  .table-custom th,
  .table-custom td {
    padding: 15px;
    font-size: 0.9rem;
  }
  
  .alert {
    padding: 15px 20px;
  }
  
  .badge {
    padding: 6px 12px;
    font-size: 0.7rem;
  }
}

@media (max-width: 576px) {
  .container {
    padding: 15px;
  }
  
  .card-header {
    padding: 20px;
  }
  
  .card-body {
    padding: 15px;
  }
  
  .btn {
    padding: 8px 15px;
    font-size: 12px;
  }
  
  .profile-img-container {
    width: 100px;
    height: 100px;
  }
  
  .form-label {
    font-size: 0.8rem;
  }
  
  .form-control, .form-select {
    padding: 10px;
    font-size: 0.9rem;
  }
  
  .table-custom th,
  .table-custom td {
    padding: 12px;
    font-size: 0.8rem;
  }
}

/* استایل‌های اضافی و پیشرفته */
.divider {
  height: 2px;
  background: linear-gradient(90deg, transparent, var(--primary), transparent);
  margin: 30px 0;
  position: relative;
}

.divider::before {
  content: '';
  position: absolute;
  width: 10px;
  height: 10px;
  background: var(--primary);
  border-radius: 50%;
  top: -4px;
  left: 50%;
  transform: translateX(-50%);
  box-shadow: 0 0 10px var(--primary);
}

.icon-circle {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
  color: white;
  font-size: 1.5rem;
  margin: 0 auto 20px;
  box-shadow: var(--shadow-md);
  transition: var(--transition-bounce);
}

.icon-circle:hover {
  transform: scale(1.1) rotate(10deg);
  box-shadow: var(--shadow-lg), var(--shadow-neon);
}

.progress {
  height: 10px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  overflow: hidden;
  margin: 15px 0;
}

.progress-bar {
  height: 100%;
  border-radius: 10px;
  background: linear-gradient(90deg, var(--primary-dark), var(--primary));
  position: relative;
  overflow: hidden;
  transition: width 1s ease;
}

.progress-bar::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  animation: shimmer 2s infinite;
}

.tooltip-custom {
  position: relative;
  display: inline-block;
}

.tooltip-custom .tooltip-text {
  visibility: hidden;
  width: 200px;
  background: var(--card-bg);
  color: var(--text-light);
  text-align: center;
  border-radius: var(--border-radius-sm);
  padding: 10px;
  position: absolute;
  z-index: 1;
  bottom: 125%;
  left: 50%;
  transform: translateX(-50%);
  opacity: 0;
  transition: var(--transition-fast);
  box-shadow: var(--shadow-md);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.tooltip-custom .tooltip-text::after {
  content: "";
  position: absolute;
  top: 100%;
  left: 50%;
  margin-left: -5px;
  border-width: 5px;
  border-style: solid;
  border-color: var(--card-bg) transparent transparent transparent;
}

.tooltip-custom:hover .tooltip-text {
  visibility: visible;
  opacity: 1;
  transform: translateX(-50%) translateY(-10px);
}

/* استایل‌های اسکرول‌بار */
::-webkit-scrollbar {
  width: 10px;
  background: var(--background);
}

::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(var(--primary), var(--primary-light));
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(var(--primary-light), var(--primary));
}

/* استایل‌های انتخاب متن */
::selection {
  background: var(--primary);
  color: white;
  text-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
}

/* استایل‌های فوکوس */
*:focus {
  outline: none !important;
  color: white !important;
  box-shadow: 0 0 0 3px rgba(0, 198, 255, 0.25) !important;
}

/* استایل‌های سوییت آلرت */
.swal2-popup {
  background: var(--card-bg) !important;
  border-radius: var(--border-radius) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  box-shadow: var(--shadow-lg) !important;
  color: var(--text-light) !important;
}

.swal2-title {
  color: var(--text-light) !important;
}

.swal2-content {
  color: rgba(255, 255, 255, 0.8) !important;
}

.swal2-confirm {
  background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%) !important;
  border-radius: var(--border-radius-sm) !important;
  box-shadow: var(--shadow-md) !important;
  transition: var(--transition-bounce) !important;
}

.swal2-confirm:hover {
  transform: translateY(-3px) !important;
  box-shadow: var(--shadow-lg), var(--shadow-neon) !important;
}

.swal2-cancel {
  background: linear-gradient(135deg, #424242 0%, #616161 100%) !important;
  border-radius: var(--border-radius-sm) !important;
  box-shadow: var(--shadow-md) !important;
  transition: var(--transition-bounce) !important;
}

.swal2-cancel:hover {
  transform: translateY(-3px) !important;
  box-shadow: var(--shadow-lg) !important;
}

/* استایل‌های دیتاتیبل */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_processing,
.dataTables_wrapper .dataTables_paginate {
  color: var(--text-light) !important;
  margin-bottom: 15px;
}

.dataTables_wrapper .dataTables_length select,
.dataTables_wrapper .dataTables_filter input {
  background: rgba(255, 255, 255, 0.05) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  color: var(--text-light) !important;
  border-radius: var(--border-radius-sm) !important;
  padding: 8px 12px !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
  background: rgba(255, 255, 255, 0.05) !important;
  color: var(--text-light) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  border-radius: var(--border-radius-sm) !important;
  transition: var(--transition-fast) !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
  background: rgba(0, 198, 255, 0.1) !important;
  color: white !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
  background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%) !important;
  color: white !important;
  border: none !important;
}

/* استایل‌های چارت */
.highcharts-background {
  fill: var(--card-bg) !important;
}

.highcharts-title,
.highcharts-axis-title,
.highcharts-axis-labels text {
  fill: var(--text-light) !important;
}

.highcharts-grid-line {
  stroke: rgba(255, 255, 255, 0.1) !important;
}

.highcharts-point {
  stroke: var(--card-bg) !important;
  stroke-width: 2px !important;
}

/* استایل‌های سوایپر */
.swiper-container {
  padding: 30px 0 !important;
}

.swiper-slide {
  transition: var(--transition-medium) !important;
  transform: scale(0.85) !important;
  opacity: 0.5 !important;
}

.swiper-slide-active {
  transform: scale(1) !important;
  opacity: 1 !important;
}

.swiper-pagination-bullet {
  background: rgba(255, 255, 255, 0.5) !important;
  opacity: 1 !important;
}

.swiper-pagination-bullet-active {
  background: var(--primary) !important;
  box-shadow: 0 0 10px var(--primary) !important;
}

.swiper-button-next,
.swiper-button-prev {
  color: var(--primary) !important;
  background: rgba(0, 0, 0, 0.3) !important;
  width: 40px !important;
  height: 40px !important;
  border-radius: 50% !important;
  transition: var(--transition-fast) !important;
}

.swiper-button-next:hover,
.swiper-button-prev:hover {
  background: rgba(0, 0, 0, 0.5) !important;
  transform: scale(1.1) !important;
}

.swiper-button-next:after,
.swiper-button-prev:after {
  font-size: 18px !important;
}

.table-custom th,
  .table-custom td {
    padding: 15px;
    font-size: 0.9rem;
  }
  
  .alert {
    padding: 15px 20px;
  }
  
  .badge {
    padding: 6px 12px;
    font-size: 0.7rem;
  }


@media (max-width: 576px) {
  .container {
    padding: 15px;
  }
  
  .card-header {
    padding: 20px;
  }
  
  .card-body {
    padding: 15px;
  }
  
  .btn {
    padding: 8px 15px;
    font-size: 12px;
  }
  
  .profile-img-container {
    width: 100px;
    height: 100px;
  }
  
  .form-label {
    font-size: 0.8rem;
  }
  
  .form-control, .form-select {
    padding: 10px;
    font-size: 0.9rem;
  }
  
  .table-custom th,
  .table-custom td {
    padding: 12px;
    font-size: 0.8rem;
  }
}

/* استایل‌های اضافی و پیشرفته */
.divider {
  height: 2px;
  background: linear-gradient(90deg, transparent, var(--primary), transparent);
  margin: 30px 0;
  position: relative;
}

.divider::before {
  content: '';
  position: absolute;
  width: 10px;
  height: 10px;
  background: var(--primary);
  border-radius: 50%;
  top: -4px;
  left: 50%;
  transform: translateX(-50%);
  box-shadow: 0 0 10px var(--primary);
}

.icon-circle {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
  color: white;
  font-size: 1.5rem;
  margin: 0 auto 20px;
  box-shadow: var(--shadow-md);
  transition: var(--transition-bounce);
}

.icon-circle:hover {
  transform: scale(1.1) rotate(10deg);
  box-shadow: var(--shadow-lg), var(--shadow-neon);
}

.progress {
  height: 10px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  overflow: hidden;
  margin: 15px 0;
}

.progress-bar {
  height: 100%;
  border-radius: 10px;
  background: linear-gradient(90deg, var(--primary-dark), var(--primary));
  position: relative;
  overflow: hidden;
  transition: width 1s ease;
}

.progress-bar::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  animation: shimmer 2s infinite;
}

.tooltip-custom {
  position: relative;
  display: inline-block;
}

.tooltip-custom .tooltip-text {
  visibility: hidden;
  width: 200px;
  background: var(--card-bg);
  color: var(--text-light);
  text-align: center;
  border-radius: var(--border-radius-sm);
  padding: 10px;
  position: absolute;
  z-index: 1;
  bottom: 125%;
  left: 50%;
  transform: translateX(-50%);
  opacity: 0;
  transition: var(--transition-fast);
  box-shadow: var(--shadow-md);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.tooltip-custom .tooltip-text::after {
  content: "";
  position: absolute;
  top: 100%;
  left: 50%;
  margin-left: -5px;
  border-width: 5px;
  border-style: solid;
  border-color: var(--card-bg) transparent transparent transparent;
}

.tooltip-custom:hover .tooltip-text {
  visibility: visible;
  opacity: 1;
  transform: translateX(-50%) translateY(-10px);
}

/* استایل‌های اسکرول‌بار */
::-webkit-scrollbar {
  width: 10px;
  background: var(--background);
}

::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(var(--primary), var(--primary-light));
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(var(--primary-light), var(--primary));
}

/* استایل‌های انتخاب متن */
::selection {
  background: var(--primary);
  color: white;
  text-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
}

/* استایل‌های فوکوس */
*:focus {
  outline: none !important;
  box-shadow: 0 0 0 3px rgba(0, 198, 255, 0.25) !important;
}

/* استایل‌های سوییت آلرت */
.swal2-popup {
  background: var(--card-bg) !important;
  border-radius: var(--border-radius) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  box-shadow: var(--shadow-lg) !important;
  color: var(--text-light) !important;
}

.swal2-title {
  color: var(--text-light) !important;
}

.swal2-content {
  color: rgba(255, 255, 255, 0.8) !important;
}

.swal2-confirm {
  background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%) !important;
  border-radius: var(--border-radius-sm) !important;
  box-shadow: var(--shadow-md) !important;
  transition: var(--transition-bounce) !important;
}

.swal2-confirm:hover {
  transform: translateY(-3px) !important;
  box-shadow: var(--shadow-lg), var(--shadow-neon) !important;
}

.swal2-cancel {
  background: linear-gradient(135deg, #424242 0%, #616161 100%) !important;
  border-radius: var(--border-radius-sm) !important;
  box-shadow: var(--shadow-md) !important;
  transition: var(--transition-bounce) !important;
}

.swal2-cancel:hover {
  transform: translateY(-3px) !important;
  box-shadow: var(--shadow-lg) !important;
}

/* استایل‌های دیتاتیبل */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_processing,
.dataTables_wrapper .dataTables_paginate {
  color: var(--text-light) !important;
  margin-bottom: 15px;
}

.dataTables_wrapper .dataTables_length select,
.dataTables_wrapper .dataTables_filter input {
  background: rgba(255, 255, 255, 0.05) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  color: var(--text-light) !important;
  border-radius: var(--border-radius-sm) !important;
  padding: 8px 12px !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
  background: rgba(255, 255, 255, 0.05) !important;
  color: var(--text-light) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  border-radius: var(--border-radius-sm) !important;
  transition: var(--transition-fast) !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
  background: rgba(0, 198, 255, 0.1) !important;
  color: white !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
  background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%) !important;
  color: white !important;
  border: none !important;
}

/* استایل‌های چارت */
.highcharts-background {
  fill: var(--card-bg) !important;
}

.highcharts-title,
.highcharts-axis-title,
.highcharts-axis-labels text {
  fill: var(--text-light) !important;
}

.highcharts-grid-line {
  stroke: rgba(255, 255, 255, 0.1) !important;
}

.highcharts-point {
  stroke: var(--card-bg) !important;
  stroke-width: 2px !important;
}

/* استایل‌های سوایپر */
.swiper-container {
  padding: 30px 0 !important;
}

.swiper-slide {
  transition: var(--transition-medium) !important;
  transform: scale(0.85) !important;
  opacity: 0.5 !important;
}

.swiper-slide-active {
  transform: scale(1) !important;
  opacity: 1 !important;
}

.swiper-pagination-bullet {
  background: rgba(255, 255, 255, 0.5) !important;
  opacity: 1 !important;
}

.swiper-pagination-bullet-active {
  background: var(--primary) !important;
  box-shadow: 0 0 10px var(--primary) !important;
}

.swiper-button-next,
.swiper-button-prev {
  color: var(--primary) !important;
  background: rgba(0, 0, 0, 0.3) !important;
  width: 40px !important;
  height: 40px !important;
  border-radius: 50% !important;
  transition: var(--transition-fast) !important;
}

.swiper-button-next:hover,
.swiper-button-prev:hover {
  background: rgba(0, 0, 0, 0.5) !important;
  transform: scale(1.1) !important;
}

.swiper-button-next:after,
.swiper-button-prev:after {
  font-size: 18px !important;
}


    </style>
</head>
<body>
    <div class="container py-5 animate-in">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header text-center">
                        <h3 class="mb-0">ثبت کارفرمای جدید</h3>
                    </div>
                    <div class="card-body p-4">
                        <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="profile-img-container">
                                <img id="thumbnil" class="profile-img" src="images/departeman_black.jpg" alt="تصویر پروفایل">
                                <div class="profile-img-overlay">تغییر تصویر</div>
                                <input type="file" class="position-absolute" style="width: 100%; height: 100%; opacity: 0; cursor: pointer; top: 0; left: 0;" name="image" accept="image/jpeg, image/png" onchange="showMyImage(this)">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="role" class="form-label">نقش:</label>
                                        <select class="form-select" id="role" name="role" required>
                                            <option value="کارفرما">کارفرما</option>
                                        </select>
                                        <div class="invalid-feedback">لطفاً نقش خود را انتخاب کنید.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="manager" class="form-label">نام مدیر:</label>
                                        <input type="text" class="form-control" id="manager" name="manager" required>
                                        <div class="invalid-feedback">لطفاً نام مدیر را وارد کنید.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="factory" class="form-label">نام کارخانه:</label>
                                        <input type="text" class="form-control" id="factory" name="factory" required>
                                        <div class="invalid-feedback">لطفاً نام کارخانه را وارد کنید.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone" class="form-label">شماره تلفن:</label>
                                        <div class="input-group">
                                            <input type="tel" class="form-control" id="phone" name="phone" required>
                                            <button type="button" id="selectPhone" class="btn btn-primary">
                                                <i class="bi bi-person-rolodex"></i>
                                            </button>
                                        </div>
                                        <div class="invalid-feedback">لطفاً شماره تلفن را وارد کنید.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="address" class="form-label">آدرس:</label>
                                <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                            </div>

                            <div class="form-group" style="display: none;">
                                
                                <label for="project_id" class="form-label">شناسه پروژه:</label>
                                <input type="text" class="form-control" name="project_id" placeholder="شناسه پروژه" disabled value="<?php echo $totalEmployees; ?>">
                                <div class="invalid-feedback">لطفاً شناسه را تغییر ندهید!</div>
                            </div>

                            <div class="d-flex justify-content-center gap-3 mt-4">
                                <button type="submit" class="btn btn-primary" name="sub">
                                    <i class="bi bi-check-circle-fill me-2"></i> ثبت کارفرما
                                </button>
                                <?php
                                $backUrl = isset($_GET['back']) ? urldecode($_GET['back']) : 'default_page.php';
                                ?>

                                <a href="<?= htmlspecialchars($backUrl) ?>" class="btn btn-secondary">⬅ برگشت</a>
<!--                                <button onclick="history.back()" class="btn btn-secondary">⬅ برگشت</button>-->

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php foreach ($jsLinks as $link) echo '<script src="'.$link.'"></script>'; ?>
    
    <?php if (isset($alertScript)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php echo $alertScript; ?>
        });
    </script>
    <?php endif; ?>

    <script>
        // نمایش تصویر آپلود شده
        function showMyImage(fileInput) {
            var files = fileInput.files;
            if (files && files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('thumbnil').src = e.target.result;
                }
                reader.readAsDataURL(files[0]);
            }
        }

        // انتخاب شماره تل
        // انتخاب شماره تلفن
// Replace the existing selectPhone event listener with this improved version
document.getElementById('selectPhone').addEventListener('click', function () {
    if ('contacts' in navigator && 'ContactsManager' in window) {
        navigator.contacts.select(['tel'], { multiple: false })
            .then(contacts => {
                if (contacts.length > 0) {
                    const contact = contacts[0];
                    const phoneNumbers = contact.tel || [];

                    if (phoneNumbers.length > 0) {
                        // Get the first valid phone number
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
                            // Clean the phone number (remove spaces, dashes, etc.)
                            validPhoneNumber = validPhoneNumber.replace(/[^\d+]/g, '');
                            
                            // Set the phone number in the input field
                            document.getElementById('phone').value = validPhoneNumber;
                            
                            // Show success message
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
        // Fallback for browsers that don't support Contact Picker API
        Swal.fire({
            icon: 'warning',
            title: 'عدم پشتیبانی',
            text: 'دفترچه تلفن در مرورگر شما پشتیبانی نمی‌شود. لطفا شماره را به صورت دستی وارد کنید.'
        });
    }
});


        // اعتبارسنجی فرم
        (function() {
            'use strict';
            
            // اعتبارسنجی بوت‌استرپ
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
            
            // انیمیشن‌های ورودی
            const animateElements = () => {
                const elements = document.querySelectorAll('.form-group, .profile-img-container, .btn');
                elements.forEach((el, index) => {
                    setTimeout(() => {
                        el.style.opacity = '0';
                        el.style.transform = 'translateY(20px)';
                        el.style.transition = 'all 0.3s ease';
                        
                        setTimeout(() => {
                            el.style.opacity = '1';
                            el.style.transform = 'translateY(0)';
                        }, 50);
                    }, index * 100);
                });
            };
            
            // اجرای انیمیشن‌ها پس از بارگذاری صفحه
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', animateElements);
            } else {
                animateElements();
            }
        })();
    </script>
    
    <script>
        // بهینه‌سازی عملکرد با لود تنبل تصاویر
        document.addEventListener("DOMContentLoaded", function() {
            var lazyImages = [].slice.call(document.querySelectorAll("img.lazy"));
            
            if ("IntersectionObserver" in window) {
                let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            let lazyImage = entry.target;
                            lazyImage.src = lazyImage.dataset.src;
                            lazyImage.classList.remove("lazy");
                            lazyImageObserver.unobserve(lazyImage);
                        }
                    });
                });
                
                lazyImages.forEach(function(lazyImage) {
                    lazyImageObserver.observe(lazyImage);
                });
            }
        });
        
        // بهینه‌سازی سرعت با پیش‌بارگذاری منابع مهم
        const preloadResources = () => {
            // پیش‌بارگذاری تصاویر مهم
            const preloadImages = ['images/departeman_black.jpg'];
            preloadImages.forEach(src => {
                const img = new Image();
                img.src = src;
            });
        };
        
        // اجرای پیش‌بارگذاری با اولویت پایین
        window.addEventListener('load', () => {
            setTimeout(preloadResources, 1000);
        });
    </script>
<?php $conn->close(); ?>
</body>
</html>
