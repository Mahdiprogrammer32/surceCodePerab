<?php
session_start();

// اتصال به دیتابیس
$host = 'localhost';
$db   = 'departem_test';
$user = 'departem_kakang';
$pass = 'mahdipass.2023';
$charset = 'utf8mb4';

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset($charset);

// دریافت اطلاعات فاکتور
$invoice_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$invoice_query = "SELECT * FROM purchase_invoices WHERE id = $invoice_id";
$invoice_result = $conn->query($invoice_query);
$invoice = $invoice_result->fetch_assoc();

if (!$invoice) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'فاکتور مورد نظر یافت نشد'];
    header("Location: invoices.php");
    exit;
}

// دریافت اقلام فاکتور
$items_query = "SELECT * FROM purchase_invoice_items WHERE invoice_id = $invoice_id";
$items_result = $conn->query($items_query);
$items = $items_result->fetch_all(MYSQLI_ASSOC);

// دریافت لیست محصولات
$products_query = "SELECT id, name, brand FROM products WHERE status = 1 ORDER BY name ASC";
$products_result = $conn->query($products_query);
$products = $products_result->fetch_all(MYSQLI_ASSOC);

// ویرایش فاکتور
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();
    
    try {
        // بازگرداندن موجودی قبلی
        foreach ($items as $item) {
            $conn->query("UPDATE products SET quantity = quantity - {$item['quantity']} WHERE id = {$item['product_id']}");
        }
        
        // حذف اقلام قبلی
        $conn->query("DELETE FROM purchase_invoice_items WHERE invoice_id = $invoice_id");
        
        // افزودن اقلام جدید
        $total_amount = 0;
        foreach ($_POST['items'] as $item) {
            $product_id = (int)$item['product_id'];
            $quantity = (int)$item['quantity'];
            $purchase_price = (float)str_replace(',', '', $item['purchase_price']);
            
            // دریافت اطلاعات محصول
            $product_query = "SELECT name, brand FROM products WHERE id = $product_id";
            $product_result = $conn->query($product_query);
            $product = $product_result->fetch_assoc();
            
            $total_price = $quantity * $purchase_price;
            $total_amount += $total_price;
            
            $insert_query = "INSERT INTO purchase_invoice_items 
                            (invoice_id, product_id, product_name, product_brand, quantity, purchase_price, total_price)
                            VALUES ($invoice_id, $product_id, '{$product['name']}', '{$product['brand']}', $quantity, $purchase_price, $total_price)";
            $conn->query($insert_query);
            
            // آپدیت موجودی جدید
            $conn->query("UPDATE products SET quantity = quantity + $quantity WHERE id = $product_id");
        }
        
        // آپدیت فاکتور اصلی
        $update_query = "UPDATE purchase_invoices 
                        SET supplier_name = '{$_POST['supplier_name']}', 
                            total_amount = $total_amount 
                        WHERE id = $invoice_id";
        $conn->query($update_query);
        
        $conn->commit();
        $_SESSION['message'] = ['type' => 'success', 'text' => 'فاکتور با موفقیت ویرایش شد'];
        header("Location: view_invoice.php?id=$invoice_id");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'خطا در ویرایش فاکتور: ' . $e->getMessage()];
    }
}

$message = isset($_SESSION['message']) ? $_SESSION['message'] : null;
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ویرایش فاکتور #<?= $invoice['id'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body { font-family: 'Vazirmatn', sans-serif; background-color: #f8f9fa; }
        .invoice-container { max-width: 1000px; margin: 30px auto; background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .invoice-header { border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 30px; }
        .invoice-title { color: #333; font-weight: 700; }
        .invoice-details { margin-top: 20px; }
        .table th { background-color: #f8f9fa; font-weight: 600; }
        .total-section { background-color: #f8f9fa; padding: 20px; border-radius: 10px; margin-top: 30px; }
        .select2-container--bootstrap-5 .select2-selection { height: 38px; padding: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="invoice-container">
            <?php if ($message): ?>
            <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show">
                <?= $message['text'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <div class="invoice-header">
                <div class="row">
                    <div class="col-md-6">
                        <h2 class="invoice-title">ویرایش فاکتور خرید #<?= $invoice['id'] ?></h2>
                    </div>
                    <div class="col-md-6 text-start">
                        <a href="view_invoice.php?id=<?= $invoice['id'] ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-right me-2"></i>بازگشت به فاکتور
                        </a>
                    </div>
                </div>
            </div>
            
            <form method="post">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="supplier_name" class="form-label">تامین کننده</label>
                            <input type="text" id="supplier_name" name="supplier_name" class="form-control" 
                                   value="<?= htmlspecialchars($invoice['supplier_name']) ?>" required>
                        </div>
                    </div>
                </div>
                
                <h4 class="mb-3">اقلام فاکتور</h4>
                <div id="invoice-items">
                    <?php foreach ($items as $index => $item): ?>
                    <div class="item-row mb-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">محصول</label>
                                    <select name="items[<?= $index ?>][product_id]" class="form-select select-product" required>
                                        <option value="">انتخاب کنید</option>
                                        <?php foreach ($products as $product): ?>
                                        <option value="<?= $product['id'] ?>"
                                            <?= $item['product_id'] == $product['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($product['name'] . ' - ' . $product['brand']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">تعداد</label>
                                    <input type="number" name="items[<?= $index ?>][quantity]" class="form-control" 
                                           value="<?= $item['quantity'] ?>" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">قیمت واحد (ریال)</label>
                                    <input type="text" name="items[<?= $index ?>][purchase_price]" class="form-control price-input" 
                                           value="<?= number_format($item['purchase_price']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">قیمت کل</label>
                                    <input type="text" class="form-control total-price" 
                                           value="<?= number_format($item['total_price']) ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-danger remove-item">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="row mb-4">
                    <div class="col-12">
                        <button type="button" id="add-item" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>افزودن آیتم جدید
                        </button>
                    </div>
                </div>
                
                <div class="total-section">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>تعداد اقلام:</strong> <span id="items-count"><?= count($items) ?></span></p>
                        </div>
                        <div class="col-md-6 text-start">
                            <p><strong>جمع کل فاکتور:</strong> <span id="invoice-total"><?= number_format($invoice['total_amount']) ?></span> ریال</p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>ذخیره تغییرات
                    </button>
                    <a href="view_invoice.php?id=<?= $invoice['id'] ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>انصراف
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select-product').select2({
                theme: 'bootstrap-5',
                width: '100%',
                language: {
                    noResults: function() {
                        return "محصولی یافت نشد";
                    }
                }
            });
            
            // Format price inputs
            $('.price-input').on('input', function() {
                let value = $(this).val().replace(/[^\d]/g, '');
                if (value.length > 0) {
                    value = parseInt(value).toLocaleString('fa-IR');
                }
                $(this).val(value);
                calculateRowTotal($(this).closest('.item-row'));
                calculateInvoiceTotal();
            });
            
            // Calculate row total
            function calculateRowTotal(row) {
                const quantity = row.find('input[name*="quantity"]').val();
                const price = row.find('.price-input').val().replace(/[^\d]/g, '');
                
                if (quantity && price) {
                    const total = quantity * price;
                    row.find('.total-price').val(total.toLocaleString('fa-IR'));
                }
            }
            
            // Calculate invoice total
            function calculateInvoiceTotal() {
                let total = 0;
                $('.total-price').each(function() {
                    const value = $(this).val().replace(/[^\d]/g, '');
                    if (value) total += parseInt(value);
                });
                
                $('#invoice-total').text(total.toLocaleString('fa-IR'));
                $('#items-count').text($('.item-row').length);
            }
            
            // Remove item
            $(document).on('click', '.remove-item', function() {
                $(this).closest('.item-row').remove();
                calculateInvoiceTotal();
            });
            
            // Add new item
            $('#add-item').click(function() {
                const index = $('.item-row').length;
                const html = `
                <div class="item-row mb-3">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">محصول</label>
                                <select name="items[${index}][product_id]" class="form-select select-product" required>
                                    <option value="">انتخاب کنید</option>
                                    <?php foreach ($products as $product): ?>
                                    <option value="<?= $product['id'] ?>">
                                        <?= htmlspecialchars($product['name'] . ' - ' . $product['brand']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">تعداد</label>
                                <input type="number" name="items[${index}][quantity]" class="form-control" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">قیمت واحد (ریال)</label>
                                <input type="text" name="items[${index}][purchase_price]" class="form-control price-input" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">قیمت کل</label>
                                <input type="text" class="form-control total-price" readonly>
                            </div>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-danger remove-item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>`;
                
                $('#invoice-items').append(html);
                $('.select-product').select2();
                
                // Re-index items
                $('.item-row').each(function(i) {
                    $(this).find('select, input').each(function() {
                        const name = $(this).attr('name').replace(/\[\d+\]/, `[${i}]`);
                        $(this).attr('name', name);
                    });
                });
            });
            
            // Calculate on quantity change
            $(document).on('change', 'input[name*="quantity"]', function() {
                calculateRowTotal($(this).closest('.item-row'));
                calculateInvoiceTotal();
            });
        });
    </script>
</body>
</html>