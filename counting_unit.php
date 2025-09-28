<?php
require_once 'database.php';

// افزودن واحد جدید
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_unit'])) {
    $unitName = $conn->real_escape_string($_POST['unitName']);
    $sql = "INSERT INTO units (name) VALUES ('$unitName')";
    if ($conn->query($sql) === TRUE) {
        $success_message = "واحد جدید با موفقیت اضافه شد!";
    } else {
        $error_message = "خطا در افزودن واحد: " . $conn->error;
    }
}

// ویرایش واحد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_unit'])) {
    $unitId = $_POST['unitId'];
    $unitName = $conn->real_escape_string($_POST['unitName']);
    $sql = "UPDATE units SET name='$unitName' WHERE id='$unitId'";
    if ($conn->query($sql) === TRUE) {
        $success_message = "واحد با موفقیت ویرایش شد!";
    } else {
        $error_message = "خطا در ویرایش واحد: " . $conn->error;
    }
}

// حذف واحد
if (isset($_GET['delete'])) {
    $unitId = $_GET['delete'];
    $sql = "DELETE FROM units WHERE id='$unitId'";
    if ($conn->query($sql) === TRUE) {
        $success_message = "واحد با موفقیت حذف شد!";
    } else {
        $error_message = "خطا در حذف واحد: " . $conn->error;
    }
}

// دریافت لیست واحدها از دیتابیس
$units = [];
$sql = "SELECT * FROM units ORDER BY name DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $units[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت واحدهای شمارش</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            border: none;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="text-center mb-4">📊 مدیریت واحدهای شمارش</h1>
                
                <!-- نمایش پیام‌ها -->
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $success_message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $error_message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <!-- فرم اضافه کردن واحد جدید -->
                <div class="form-container mb-4">
                    <h3 class="mb-4"><i class ="fas fa-plus-circle me-2"></i>افزودن واحد شمارش جدید</h3>
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="unitName" class="form-label">نام واحد</label>
                                <input type="text" class="form-control" id="unitName" name="unitName" placeholder="مثال: کیلوگرم" required>
                            </div>
                        </div>
                        <button type="submit" name="add_unit" class="btn btn-submit btn-lg">
                            <i class="fas fa-save me-2"></i>ذخیره واحد
                        </button>
                    </form>
                </div>
                
                <!-- لیست واحدهای اضافه شده -->
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="mb-0"><i class="fas fa-list me-2"></i>واحدهای ثبت شده</h3>
                        <span class="badge bg-primary rounded-pill"><?= count($units) ?></span>
                    </div>
                    
                    <div id="unitsList">
                        <?php if (count($units) === 0): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">هنوز هیچ واحدی اضافه نشده است</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($units as $unit): ?>
                                <div class="unit-item d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <h5 class="mb-1"><?= htmlspecialchars($unit['name']) ?></h5>
                                    </div>
                                    <div>
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $unit['id'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=<?= $unit['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('آیا مطمئن هستید که می‌خواهید این واحد را حذف کنید؟');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>

                                <!-- Modal ویرایش -->
                                <div class="modal fade" id="editModal<?= $unit['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editModalLabel">ویرایش واحد</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="POST" action="">
                                                    <input type="hidden" name="unitId" value="<?= $unit['id'] ?>">
                                                    <div class="mb-3">
                                                        <label for="unitName" class="form-label">نام واحد</label>
                                                        <input type="text" class="form-control" id="unitName" name="unitName" value="<?= htmlspecialchars($unit['name']) ?>" required>
                                                    </div>
                                                    <button type="submit" name="edit_unit" class="btn btn-primary">ذخیره تغییرات</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>