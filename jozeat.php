<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to the database
require_once "database.php";
global $conn;

// Function to generate nazeran link
function generate_nazeran_link($project_id) {
    return "pages_jozeat/nazeran.php?id=" . $project_id;
}

// Validate project_id
$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : null;
if (!$project_id || !filter_var($project_id, FILTER_VALIDATE_INT)) {
    die("شناسه پروژه نامعتبر است.");
}

// Variable to check if project is peymankar
$is_peymankar = false;
$projects = [];

try {
    // First check if project is peymankar
    $checkStmt = $conn->prepare("SELECT peymankar FROM projects WHERE id = ?");
    $checkStmt->bind_param("i", $project_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $row = $checkResult->fetch_assoc();
        $is_peymankar = ($row['peymankar'] == 1);
    }

    // Main query to get project details
    $sql = "
        SELECT 
            projects.id, projects.phase_description, projects.created_at, projects.updated_at, 
            projects.priority, projects.status, projects.budget, projects.progress, projects.peymankar,
            DATE(projects.created_at) AS created_date,
            TIME(projects.created_at) AS created_time,
            DATE(projects.updated_at) AS updated_date,
            TIME(projects.updated_at) AS updated_time,
            employees.manager,
            contractor1.name AS contractor1_name,
            contractor2.name AS contractor2_name,
            contractor3.name AS contractor3_name,
            contractor4.name AS contractor4_name,
            contractor5.name AS contractor5_name
        FROM projects 
        JOIN employees ON projects.employer = employees.id 
        LEFT JOIN users AS contractor1 ON projects.contractor1 = contractor1.id
        LEFT JOIN users AS contractor2 ON projects.contractor2 = contractor2.id
        LEFT JOIN users AS contractor3 ON projects.contractor3 = contractor3.id
        LEFT JOIN users AS contractor4 ON projects.contractor4 = contractor4.id
        LEFT JOIN users AS contractor5 ON projects.contractor5 = contractor5.id
        WHERE projects.id = ?
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $resultProjects = $stmt->get_result();

    if ($resultProjects->num_rows > 0) {
        while ($row = $resultProjects->fetch_assoc()) {
            $projects[] = $row;
        }
    } else {
        die("هیچ پروژه‌ای یافت نشد.");
    }

    // Validate progress value
    $progress = $projects[0]['progress'];
    if ($progress < 0 || $progress > 100) {
        die("مقدار پیشرفت نامعتبر است.");
    }

    // Determine status based on progress
    $status = 6; // Default: cancelled
    if ($progress >= 0 && $progress <= 10) {
        $status = 1; // created
    } elseif ($progress > 10 && $progress <= 70) {
        $status = 2; // in progress
    } elseif ($progress > 70 && $progress <= 95) {
        $status = 3; // completed
    } elseif ($progress > 95 && $progress <= 100) {
        $status = 4; // invoiced
    }

    // Update project status in database
    $updateStatusSQL = "UPDATE projects SET status = ?, updated_at = NOW() WHERE id = ?";
    $updateStmt = $conn->prepare($updateStatusSQL);
    $updateStmt->bind_param("ii", $status, $project_id);
    $updateStmt->execute();

    // Generate links
    $editProject = "edit_projects_page.php?id=" . $project_id;
    $deleteProject = "delete_projects.php?id=" . $project_id;
    $nazeran = generate_nazeran_link($project_id);
    $peymankaran = "pages_jozeat/peymankaran.php?id=" . $project_id;
    $pickturs = "pages_jozeat/picturs.php?project_id=" . $project_id;
    $factor_jens = "pages_jozeat/factor_jens.php?id=" . $project_id;
    $factor_ojrat = "pages_jozeat/factor_ojrat.php?id=" . $project_id;
    $amani = "pages_jozeat/amani.php?id=" . $project_id;
    $class_bandi = "pages_jozeat/class_bandi.php?id=" . $project_id;
    $rezayat_moshtari = "pages_jozeat/rezayat_moshtari.php?id=" . $project_id;

} catch (Exception $e) {
    die("خطا: " . $e->getMessage());
} finally {
    // Close database connections
    if (isset($updateStmt)) $updateStmt->close();
    if (isset($stmt)) $stmt->close();
    if (isset($checkStmt)) $checkStmt->close();
    if (isset($conn)) $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جزئیات پروژه</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.fontcdn.ir/Font/Persian/Vazir/Vazir.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>

    <style>
        /* Reset default browser styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Gulzar', serif;
            background-color: #f4f4f9;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .progress-bar {
            background: linear-gradient(90deg,
                            #00ff15 0%,
                            #aaff00 50%,
                            #ffff00 60%,
                            #ff7f00 100%,
                            #ff5500 80%,
                            #ff0000 90%);
                            color:black;
            border-radius: 5px;
            overflow: hidden;
            height: 20px;
            margin-bottom: 20px;
        }

        .progress-bar-fill {
            height: 100%;
            background-color: #28a745;
            width: 0;
            transition: width 0.4s ease;
        }

        .date_custom {
            position: absolute;
            left: -13px;
            top: -56px;
            border-radius: 10px;
            font-size: small;
        }
        
        .btn-disabled {
            opacity: 0.5;
            pointer-events: none;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <div class="card">
        <div class="card-header text-center position-relative">
            <p>
                <?php
                $priority = $projects[0]['priority'];
                if ($priority == 'A') {
                    $bg_priority = "red";
                } else if ($priority == 'B') {
                    $bg_priority = "orange";
                } else if ($priority == 'C') {
                    $bg_priority = "yellow";
                } else if ($priority == 'D') {
                    $bg_priority = "green";
                } else {
                    $bg_priority = "white";
                }
                ?>
                <?php echo htmlspecialchars($projects[0]['manager'] ?? 'نامشخص'); ?>
                <span class="text-danger">|</span>
                <?php echo htmlspecialchars($projects[0]['phase_description'] ?? 'نامشخص'); ?>
                <br>
                <span class="p-2 date_custom mx-2 mt-5 bg-danger text-white">
                    <?php echo htmlspecialchars(date('d/m/Y', strtotime($projects[0]['created_at'] ?? 'نامشخص'))); ?>
                </span>
                <span class="p-2 date_custom mx-2 mt-5" style="background-color: <?php echo $bg_priority; ?>; color:white; text-shadow: -1px 0px 7px rgba(8, 0, 0, 1); display:flex; justify-content:center; align-items:center; text-align:center; top:56px; width:60px; height:60px; padding:10px; border-radius: 50%; font-size: 40px;">
                    <?php echo htmlspecialchars($projects[0]['priority'] ?? 'نامشخص'); ?>
                </span>
            </p>
        </div>

        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 d-flex justify-content-center flex-wrap">
                    <!-- Nazeran button - only shown if peymankar=1 -->
                    <?php if ($is_peymankar): ?>
                        <a href="<?php echo htmlspecialchars($nazeran); ?>" class="btn btn-primary col-3 col-sm-3 col-md-2 mx-2 mt-3">ناظران</a>
                    <?php else: ?>
                        <button class="btn btn-secondary col-3 col-sm-3 col-md-2 mx-2 mt-3 btn-disabled" title="این گزینه فقط برای پروژه‌های پیمانکاری فعال است">ناظران</button>
                    <?php endif; ?>
                    
                    <a href="<?php echo htmlspecialchars($peymankaran); ?>" class="btn btn-primary col-3 col-sm-3 col-md-2 mx-2 mt-3">پیمانکاران</a>
                    <a href="<?php echo htmlspecialchars($pickturs); ?>" class="btn btn-primary col-3 col-sm-3 col-md-2 mx-2 mt-3">عکس‌های پروژه</a>
                    <a href="<?php echo htmlspecialchars($factor_jens); ?>" class="btn btn-primary col-3 col-sm-3 col-md-2 mx-2 mt-3">فاکتور جنس</a>
                    <a href="<?php echo htmlspecialchars($factor_ojrat); ?>" class="btn btn-primary col-3 col-sm-3 col-md-2 mx-2 mt-3">فاکتور اجرت</a>
                    <a href="<?php echo htmlspecialchars($amani); ?>" class="btn btn-primary col-3 col-sm-3 col-md-2 mx-2 mt-3">امانی</a>
                    <a href="<?php echo htmlspecialchars($class_bandi); ?>" class="btn btn-primary col-3 col-sm-3 col-md-2 mx-2 mt-3">کلاس‌بندی</a>
                    <a href="<?php echo htmlspecialchars($rezayat_moshtari); ?>" class="btn btn-primary col-3 col-sm-3 col-md-2 mx-2 mt-3">رضایت مشتری</a>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-center align-items-center gap-2">
                    <?php
                    $role = $_COOKIE['role'];
                    if ($role != 'پیمانکار'):
                    ?>
                    <a href="<?php echo htmlspecialchars($editProject); ?>" class="btn btn-primary"><i class="fa fa-edit fa-beat-fade"></i> ویرایش</a>
                    <a href="<?php echo htmlspecialchars($deleteProject); ?>" class="btn btn-danger" onclick="return confirm('آیا از حذف این پروژه اطمینان دارید؟')"><i class="fa fa-trash fa-shake"></i> حذف</a>
                    <a href="#" class="btn btn-success"><i class="fa fa-paper-plane fa-beat" aria-hidden="true"></i> ارسال</a>

                    <?php
                    endif;
                    ?>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="container" style="margin-top:-55px">
    <div id="cards-container"></div>
</div>

<?php
require_once "require_once/menu.php";
include_once "require_once/loading.php";
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const projects = <?php echo json_encode($projects, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    const cardsContainer = document.getElementById('cards-container');
    
    projects.forEach(project => {
        const card = document.createElement('div');
        card.className = 'card mt-5 p-3';
        
        const contractors = [
            { name: project.contractor1_name, label: 'پیمانکار 1' },
            { name: project.contractor2_name, label: 'پیمانکار 2' },
            { name: project.contractor3_name, label: 'پیمانکار 3' },
            { name: project.contractor4_name, label: 'پیمانکار 4' },
            { name: project.contractor5_name, label: 'پیمانکار 5' }
        ];
        
        let contractorsHTML = contractors.map(contractor => {
            return contractor.name ? `<p><strong>${contractor.label}:</strong> ${contractor.name}</p>` : '';
        }).join('');

        // Define status based on progress
        let statusText = 'نامشخص';
        if (project.progress >= 0 && project.progress <= 10) {
            statusText = 'ایجاد شده';
        } else if (project.progress > 10 && project.progress <= 70) {
            statusText = 'درحال انجام';
        } else if (project.progress > 70 && project.progress <= 95) {
            statusText = 'تمام شده';
        } else if (project.progress > 95 && project.progress <= 100) {
            statusText = 'فاکتور شده';
        }

        card.innerHTML = `
            <h2>شناسه پروژه: ${project.id ?? 'نامشخص'}</h2>
            ${contractorsHTML}
            <p><strong>وضعیت:</strong> ${statusText}</p>
            <p><strong>بودجه:</strong> ${project.budget ?? 'نامشخص'}</p>
            <p><strong>پیشرفت:</strong> ${project.progress ?? 'نامشخص'}%</p>
            <p><strong>تاریخ ایجاد:</strong> ${project.created_at ?? 'نامشخص'}</p>
            <p><strong>تاریخ به‌روزرسانی:</strong> ${project.updated_at ?? 'نامشخص'}</p>
            <div class="progress mb-2">
                <div class="progress-bar" role="progressbar" style="width: ${project.progress ?? '0'}%;" 
                     aria-valuenow="${project.progress ?? '0'}" aria-valuemin="0" aria-valuemax="100">
                    ${project.progress ?? '0'}%
                </div>
            </div>
            <p><strong>وضعیت پیمانکاری:</strong> ${project.peymankar ? 'پیمانکاری' : 'غیر پیمانکاری'}</p>
        `;
        cardsContainer.appendChild(card);
    });
});
</script>
</body>
</html>