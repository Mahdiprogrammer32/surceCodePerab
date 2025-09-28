
<?php
require_once "database.php";

function getUserLicenseKey($userId, $conn) {
    if (!is_numeric($userId)) {
        return ["status" => "error", "message" => "Invalid user ID"];
    }

    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return ["status" => "error", "message" => "Database error"];
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
    $stmt->close();

    if ($userData) {
        return ["status" => "success", "user_data" => $userData];
    } else {
        return ["status" => "error", "message" => "No users found"];
    }
}

global $conn;
$userId = $_GET['userId'] ?? null;
$response = [];

if ($userId) {
    $response = getUserLicenseKey($userId, $conn);
} else {
    $response = ["status" => "error", "message" => "User ID is required"];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نمایش لایسنس</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
       <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            transition: background-color 0.3s, color 0.3s;
        }
        body.dark-mode {
            background-color: #1a1a1a;
            color: #f4f4f9;
        }
        .license-container {
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
            transition: background-color 0.3s, color 0.3s;
        }
        .dark-mode .license-container {
            background: #2d2d2d;
            color: #f4f4f9;
        }
        .license-key {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            margin: 1rem 0;
            padding: 0.5rem;
            border: 2px dashed #ccc;
            border-radius: 5px;
            background: #f9f9f9;
            cursor: pointer;
            position: relative;
            transition: background-color 0.3s, color 0.3s;
        }
        .dark-mode .license-key {
            background: #3d3d3d;
            color: #f4f4f9;
            border-color: #555;
        }
        .copy-message {
            font-size: 0.9rem;
            color: #28a745;
            margin-top: 0.5rem;
            display: none;
        }
        .btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 1rem;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .dark-mode .btn {
            background-color: #555;
        }
        .dark-mode .btn:hover {
            background-color: #777;
        }
        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
        }
        .dark-mode .theme-toggle {
            background: #555;
        }
    </style>
</head>
<body>
    <button class="theme-toggle" onclick="toggleDarkMode()">تغییر تم</button>
    <div class="license-container">
        <h1>لایسنس کاربر</h1>
        <?php if ($response['status'] === 'success'): ?>   
		
		<p dir="rtl">نام کاربر: <?php echo htmlspecialchars($response['user_data']['name'] ?? 'نام نامشخص'); ?></p>
            <p dir="rtl">نقش : <?php echo htmlspecialchars($response['user_data']['role'] ?? 'نام نامشخص'); ?></p>
			<p>دسته بندی : <?php echo htmlspecialchars($response['user_data']['form'] ?? 'نام نامشخص'); ?></p>
			<div class="license-key" style="font-size:60%; text-align:center; display: flex; justify-content: center; align-items: center;" id="licenseKey" onclick="copyLicense()">
                <?php echo htmlspecialchars($response['user_data']['license_key'] ?? ''); ?>
            </div>
         
            <div class="copy-message" id="copyMessage">کپی شد!</div>
            <button class="btn" onclick="copyLicense()">کپی لایسنس</button>
            <button class="btn" onclick="downloadLicense()">دانلود لایسنس</button>
        <?php else: ?>
            <p style="color: red;"><?php echo htmlspecialchars($response['message']); ?></p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function copyLicense() {
            const licenseKey = document.getElementById("licenseKey").innerText;
            navigator.clipboard.writeText(licenseKey).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'کپی شد!',
                    text: 'لایسنس با موفقیت کپی شد.',
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        }

        function downloadLicense() {
            const licenseKey = document.getElementById("licenseKey").innerText;
            const blob = new Blob([licenseKey], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'license_key.txt';
            a.click();
            URL.revokeObjectURL(url);
        }

        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>