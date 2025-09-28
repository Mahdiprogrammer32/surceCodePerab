<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<?php
require_once "database.php";
global $conn;


if (isset($_GET['id'])) {
    $userId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($userId === false) {
        echo "شناسه پروژه نامعتبر است.";
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->bind_param("i", $userId);
    if ($stmt->execute()) {
        // هدایت یا نمایش پیام موفقیت
        echo '<script>
               Swal.fire({
                   icon: "success",
                   title: "حذف شد!",
                   text: "رکورد با موفقیت حذف شد."
               }).then(() => {
                   window.location.href = "projects.php"; // هدایت پس از حذف
               });
           </script>';
    } else {
        echo "خطا در حذف رکورد: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "هیچ شناسه پروژه ای ارائه نشده است.";
}
?>

