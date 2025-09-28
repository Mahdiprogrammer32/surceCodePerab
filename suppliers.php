<?php
// اتصال به دیتابیس
require_once "database.php";
global $conn;

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if (isset($_POST['add'])) {
        $stmt = $conn->prepare("INSERT INTO suppliers (name, phone, address) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $phone, $address);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["success" => true, "message" => "تامین‌کننده با موفقیت اضافه شد."]); exit;
    } elseif (isset($_POST['edit'])) {
        $id = (int) $_POST['id'];
        $stmt = $conn->prepare("UPDATE suppliers SET name=?, phone=?, address=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $phone, $address, $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["success" => true, "message" => "تامین‌کننده با موفقیت ویرایش شد."]); exit;
    } elseif (isset($_POST['delete'])) {
        $id = (int) $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM suppliers WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["success" => true, "message" => "تامین‌کننده با موفقیت حذف شد."]); exit;
    }
    exit;
}

$sql = "SELECT * FROM suppliers";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت تامین‌کنندگان</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #e3f2fd, #ffffff);
            font-family: 'IRANSans', sans-serif;
        }
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border-radius: 5px;
            padding: 5px;
        }
        .form-label {
            font-weight: bold;
        }
        .modal-header {
            background-color: #0d6efd;
            color: white;
        }
        .modal-title {
            margin: 0;
        }
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 15px;
        }
        .btn {
            border-radius: 10px;
        }
        .table thead {
            background-color: #0d6efd;
            color: white;
        }
        .toast-container {
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1055;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-truck"></i> مدیریت تامین‌کنندگان</h2>
        <button class="btn btn-success shadow" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus"></i> افزودن تامین‌کننده</button>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="suppliersTable" class="display nowrap table table-bordered" style="width:100%">
                <thead>
                <tr>
                    <th>نام و نام خانوادگی</th>
                    <th>شماره تلفن</th>
                    <th>آدرس</th>
                    <th style="width: 140px;">عملیات</th>
                </tr>
                </thead>
                <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editSupplier(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>', '<?php echo addslashes($row['phone']); ?>', '<?php echo addslashes($row['address']); ?>')"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger" onclick="deleteSupplier(<?php echo $row['id']; ?>)"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="toast-container">
    <div id="toast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Modal HTML برای افزودن و ویرایش -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">افزودن تامین‌کننده</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="supplierForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="supplierId">
                    <div class="mb-3">
                        <label for="name" class="form-label">نام و نام خانوادگی</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">شماره تلفن</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">آدرس</label>
                        <input type="text" class="form-control" id="address" name="address" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                    <button type="submit" class="btn btn-primary">ذخیره</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function () {
        $('#suppliersTable').DataTable({
            scrollX: true,
            responsive: true,
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'print', 'colvis'],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fa.json'
            }
        });

        $('#supplierForm').on('submit', function (e) {
            e.preventDefault();
            const formData = new URLSearchParams(new FormData(this));
            let isEdit = document.getElementById('supplierId').value !== "";
            formData.append(isEdit ? 'edit' : 'add', true);

            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                        setTimeout(() => location.reload(), 1000);
                    }
                });
        });
    });

    function showToast(message) {
        document.getElementById('toastMessage').textContent = message;
        const toast = new bootstrap.Toast(document.getElementById('toast'));
        toast.show();
    }

    function deleteSupplier(id) {
        if (!confirm('آیا از حذف این تامین‌کننده مطمئن هستید؟')) return;
        fetch('', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({delete: true, id: id})
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message);
                    setTimeout(() => location.reload(), 1000);
                }
            });
    }

    function editSupplier(id, name, phone, address) {
        document.getElementById('addModalLabel').textContent = 'ویرایش تامین‌کننده';
        document.getElementById('supplierId').value = id;
        document.getElementById('name').value = name;
        document.getElementById('phone').value = phone;
        document.getElementById('address').value = address;
        const addModal = new bootstrap.Modal(document.getElementById('addModal'));
        addModal.show();
    }
</script>
</body>
</html>
