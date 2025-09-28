<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صفحه انبار</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            margin-bottom: 20px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>مدیریت انبار</h1>
    <p>به صفحه مدیریت انبار خوش آمدید</p>
</div>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>محصولات موجود</h5>
                    <input type="text" id="searchInput" class="form-control" placeholder="جستجو کنید..." onkeyup="filterProducts()">
                </div>
                <div class="card-body">
                    <table class="table table-striped" id="productTable">
                        <thead>
                            <tr>
                                <th>نام محصول</th>
                                <th>تعداد موجود</th>
                                <th>تاریخ انقضا</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody id="productList">
                            <!-- محصولات از Local Storage بارگذاری می‌شود -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>ورود محصولات</h5>
                </div>
                <div class="card-body">
                    <form id="addProductForm">
                        <div class="form-group">
                            <label for="productName">نام محصول</label>
                            <input type="text" class="form-control" id="productName" placeholder="نام محصول را وارد کنید" required>
                        </div>
                        <div class="form-group">
                            <label for="productQuantity">تعداد</label>
                            <input type="number" class="form-control" id="productQuantity" placeholder="تعداد را وارد کنید" required>
                        </div>
                        <div class="form-group">
                            <label for="productExpiryDate">تاریخ انقضا</label>
                            <input type="text" class="form-control" id="productExpiryDate" required>
                        </div>
                        <button type="submit" class="btn btn-success">ثبت محصول</button>
                    </form>
                    <div id="successMessage" class="alert alert-success mt-3" style="display:none;">محصول با موفقیت ثبت شد!</div>
                    <div id="errorMessage" class="alert alert-danger mt-3" style="display:none;">خطا در ثبت محصول. لطفا دوباره تلاش کنید.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src ```html
="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    let products = JSON.parse(localStorage.getItem('products')) || [];

    function renderProducts() {
        const productList = document.getElementById('productList');
        productList.innerHTML = '';
        products.forEach((product, index) => {
            const row = `<tr>
                            <td>${product.name}</td>
                            <td>${product.quantity}</td>
                            <td>${product.expiryDate}</td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editProduct(${index})">ویرایش</button>
                                <button class="btn btn-danger btn-sm" onclick="deleteProduct(${index})">حذف</button>
                            </td>
                        </tr>`;
            productList.innerHTML += row;
        });
    }

    function filterProducts() {
        const searchInput = document.getElementById('searchInput').value.toLowerCase();
        const filteredProducts = products.filter(product => product.name.toLowerCase().includes(searchInput));
        const productList = document.getElementById('productList');
        productList.innerHTML = '';
        filteredProducts.forEach((product, index) => {
            const row = `<tr>
                            <td>${product.name}</td>
                            <td>${product.quantity}</td>
                            <td>${product.expiryDate}</td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editProduct(${index})">ویرایش</button>
                                <button class="btn btn-danger btn-sm" onclick="deleteProduct(${index})">حذف</button>
                            </td>
                        </tr>`;
            productList.innerHTML += row;
        });
    }

    function editProduct(index) {
        const product = products[index];
        document.getElementById('productName').value = product.name;
        document.getElementById('productQuantity').value = product.quantity;
        document.getElementById('productExpiryDate').value = product.expiryDate;
        deleteProduct(index);
    }

    function deleteProduct(index) {
        products.splice(index, 1);
        localStorage.setItem('products', JSON.stringify(products));
        renderProducts();
    }

    $(document).ready(function() {
        renderProducts();
        $('#productExpiryDate').datepicker({
            dateFormat: 'yy-mm-dd',
            // تنظیمات مربوط به زبان فارسی
            regional: 'fa'
        });
        $('#addProductForm').on('submit', function(e) {
            e.preventDefault();
            const productName = $('#productName').val();
            const productQuantity = $('#productQuantity').val();
            const productExpiryDate = $('#productExpiryDate').val();
            products.push({ name: productName, quantity: productQuantity, expiryDate: productExpiryDate });
            localStorage.setItem('products', JSON.stringify(products));
            $('#successMessage').show();
            $('#errorMessage').hide();
            $(this).trigger("reset");
            renderProducts();
        });
    });
</script>
</body>
</html>