<?php
include '../include/cn.php';

if (isset($_POST['btnSaveEmployee'])) {
    $userID = $_SESSION['user_id'] ?? 0;
    $employeeID = isset($_POST['EmployeeID']) ? (int)$_POST['EmployeeID'] : 0;
    $fullName = mysqli_real_escape_string($myCon, $_POST['FullName']);
    $gender = mysqli_real_escape_string($myCon, $_POST['Gender']);
    $phone = mysqli_real_escape_string($myCon, $_POST['Phone']);
    $email = mysqli_real_escape_string($myCon, $_POST['Email']);
    $address = mysqli_real_escape_string($myCon, $_POST['Address']);
    $username = mysqli_real_escape_string($myCon, $_POST['Username']);
    $salary = !empty($_POST['Salary']) ? (float)$_POST['Salary'] : 0;
    $roleID = !empty($_POST['RoleID']) ? (int)$_POST['RoleID'] : 'NULL';
    $hireDate = mysqli_real_escape_string($myCon, $_POST['HireDate']);
    $status = mysqli_real_escape_string($myCon, $_POST['Status']);

    // Check duplicate username (Exclude current user if updating)
    $checkUserSql = "SELECT EmployeeID FROM Employees WHERE Username='$username'";
    if ($employeeID > 0) {
        $checkUserSql .= " AND EmployeeID != $employeeID";
    }
    $checkUser = mysqli_query($myCon, $checkUserSql);

    if (mysqli_num_rows($checkUser) > 0) {
        echo "<script>alert('Username already exists!'); window.location='employee.php';</script>";
        exit();
    }

    // Handle Upload Image
    // NOTE: uploads/ folder lives ONE LEVEL ABOVE administrator/, i.e.
    //   project-root/
    //   ├── administrator/
    //   │   └── employee.php   <-- this file
    //   └── uploads/
    //       └── employees/
    $photoPath = "";
    if ($employeeID > 0) {
        // Retain existing image if no new image is provided
        $imgQuery = mysqli_query($myCon, "SELECT Photo FROM Employees WHERE EmployeeID = $employeeID");
        if ($imgRow = mysqli_fetch_assoc($imgQuery)) {
            $photoPath = $imgRow['Photo'];
        }
    }

    if (isset($_FILES['Photo']) && $_FILES['Photo']['error'] == 0) {
        // Validate it's really an image before touching the filesystem
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['Photo']['tmp_name']);
        finfo_close($finfo);

        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['Photo']['name'], PATHINFO_EXTENSION));

        if (!in_array($mimeType, $allowedMimes, true) || !in_array($ext, $allowedExt, true)) {
            die("Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.");
        }

        // uploads/ is one directory ABOVE administrator/ (this file's folder)
        $uploadDir = dirname(__DIR__) . "/uploads/employees/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate a safe, non-user-controlled filename
        $photoName = time() . "_" . uniqid() . "." . $ext;
        $tmpName = $_FILES['Photo']['tmp_name'];

        if (move_uploaded_file($tmpName, $uploadDir . $photoName)) {
            // Store the web-relative path from the SITE ROOT (not from this file)
            $photoPath = "uploads/employees/" . $photoName;
        } else {
            die("Image Upload Failed");
        }
    }

    if ($employeeID > 0) {
        // UPDATE Existing Employee
        $sql = "UPDATE Employees SET 
                    FullName = '$fullName', Gender = '$gender', Phone = '$phone', 
                    Email = '$email', Address = '$address', Username = '$username', ";

        // Only update password if a new one is provided
        if (!empty($_POST['Password'])) {
            $password = password_hash($_POST['Password'], PASSWORD_DEFAULT);
            $sql .= "PasswordHash = '$password', ";
        }

        $sql .= "Photo = '$photoPath', Salary = $salary, RoleID = $roleID, 
                 HireDate = '$hireDate', Status = '$status' 
                 WHERE EmployeeID = $employeeID";

        $success_message = "Employee Updated Successfully!";
    } else {
        // INSERT New Employee
        $password = password_hash($_POST['Password'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO Employees (FullName, Gender, Phone, Email, Address, Username, PasswordHash, Photo, Salary, RoleID, HireDate, Status)
                VALUES ('$fullName', '$gender', '$phone', '$email', '$address', '$username', '$password', '$photoPath', $salary, $roleID, '$hireDate', '$status')";
        $success_message = "Employee Added Successfully!";
    }

    if (mysqli_query($myCon, $sql)) {
        echo "<script>alert('$success_message');</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_real_escape_string($myCon, mysqli_error($myCon)) . "');</script>";
    }
}

// Delete Logic
if (isset($_GET['delete_id'])) {
    $delID = (int)$_GET['delete_id'];
    $sqlDelete = "DELETE FROM Employees WHERE EmployeeID = $delID";

    if ($myCon->query($sqlDelete) === TRUE) {
        $success_message = "Employee Deleted Successfully!";
    } else {
        $error_message = "Employee Deleted Failed!";
    }
}

// Get Search Term Safely
$searchTerm = isset($_GET['search']) ? $myCon->real_escape_string(trim($_GET['search'])) : '';

// --- PAGINATION & SEARCH CALCULATION LOGIC ---

// Page size: 10 / 25 / 50 / all — default 10
$allowedLimits = ['10', '25', '50', 'all'];
$limitParam = isset($_GET['limit']) ? (string)$_GET['limit'] : '10';
if (!in_array($limitParam, $allowedLimits, true)) {
    $limitParam = '10';
}
$showAll = ($limitParam === 'all');
$limit = $showAll ? 0 : (int)$limitParam;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$offset = $showAll ? 0 : ($page - 1) * $limit;

// Adjust condition based on whether a search query exists
$searchQueryCondition = "";
if (!empty($searchTerm)) {
    $searchQueryCondition = " AND ItemName LIKE '%$searchTerm%'";
}

// Fetch total row count for calculating structural links with search criteria
$countResult = $myCon->query("SELECT COUNT(*) AS total FROM Employees e WHERE 1=1 $searchQueryCondition");
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = $showAll ? 1 : ceil($totalRows / $limit);

// Function to maintain search query + page-size parameters across pagination links
function buildPaginationUrl($pageNum, $search, $limitParam)
{
    $url = "?page=" . $pageNum . "&limit=" . urlencode($limitParam);
    if (!empty($search)) {
        $url .= "&search=" . urlencode($search);
    }
    return $url;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee</title>
    <link rel="icon" type="image/png" href="../../img/logo-ang.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Bayon&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="../assets/dist/css/employee.css">
    <style>
        /* Reserve scrollbar space always, so the page/modal doesn't shift width when a modal opens or closes */
        html { overflow-y: scroll; scrollbar-gutter: stable; }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #dfe6ef; border-radius: 4px; }

        /* Keep modal fixed in place; only the body content scrolls internally */
        #createModal .modal-dialog,
        #editModal .modal-dialog {
            max-height: 90vh;
        }
        #createModal .modal-body,
        #editModal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        .rank-badge {
            display: inline-block;
            width: 22px;
            height: 22px;
            line-height: 22px;
            border-radius: 50%;
            background: #eef2ff;
            color: #2563eb;
            font-weight: 700;
            font-size: 11px;
        }
        .status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .status::before {
            content: '';
            width: 5px;
            height: 5px;
            border-radius: 50%;
        }
        .st-green {
            background: rgba(34, 201, 122, 0.12);
            color: var(--green);
        }
        .st-green::before {
            background: var(--green);
        }
        .st-amber {
            background: rgba(245, 166, 35, 0.12);
            color: var(--amber);
        }
        .st-amber::before {
            background: var(--amber);
        }
        .st-red {
            background: rgba(242, 92, 92, 0.12);
            color: var(--red);
        }
        .st-red::before {
            background: var(--red);
        }
        .st-blue {
            background: rgba(79, 142, 247, 0.12);
            color: var(--accent);
        }
        .st-blue::before {
            background: var(--accent);
        }
        /* ── Table ── */
        .table-card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            margin-bottom: 22px;
        }
        .metric-card, .chart-card, .table-card, .customer-card,.settings-panel,.settings-nav {
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05);
        }
        .table-card-header { padding: 18px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        table { width: 100%; border-collapse: collapse; }
        thead th { padding: 15px 22px; text-align: left; font-size: 12px; font-weight: 600; color: var(--text3); letter-spacing: 0.5px; text-transform: uppercase; background: var(--bg3); border-bottom: 1px solid var(--border); }
        tbody tr { border-bottom: 1px solid var(--border); transition: background .15s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--bg3); }
        tbody td { padding: 15px 15px; font-size: 13px; color: #000; }
        .td-main { color: var(--text); font-weight: 500; }
        .td-mono { font-family: var(--mono); font-size: 12px; }
    </style>
</head>
<body>

    <?php include '../include/administratorbd.php'; ?>
    <?php include '../include/administratorhd.php'; ?>
    <?php include '../include/smooth_refresh.php'; ?>

    <div class="container py-3">
        <div class="page-title font-bayon">បញ្ជីឈ្មោះរបស់បុគ្គលិក</div>        

        <div class="table-card">
            <div class="table-card-header font-kantumruy">
                <div class="d-flex flex-column flex-md-row align-items-center gap-3">
                    <div class="align-items-center gap-2 font-kantumruy">
                        <label for="pageSizeSelect" class="mb-0 text-nowrap">Show</label>
                        <div style="max-width:260px; position: relative;">                            
                            <select id="pageSizeSelect" class="form-select" onchange="changePageSize(this.value)" style="padding-left: 0 35px;">
                                <option value="10" <?php echo ($limitParam === '10') ? 'selected' : ''; ?>>10</option>
                                <option value="25" <?php echo ($limitParam === '25') ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo ($limitParam === '50') ? 'selected' : ''; ?>>50</option>
                                <option value="all" <?php echo ($limitParam === 'all') ? 'selected' : ''; ?>>All</option>
                            </select>
                        </div>
                    </div>

                    <div class="align-items-center gap-2 font-kantumruy">
                        <label for="searchInput" class="mb-0 text-nowrap">Search</label>
                        <div style="max-width:260px; position: relative;">
                            <i class="fas fa-search" style="position:absolute; left:15px; top:50%; transform:translateY(-50%); color:#888;"></i>
                            <input type="text" id="searchInput" class="form-control" placeholder="ស្វែងរកតាមឈ្មោះ..." oninput="applyFilters()" style="padding-left:35px;">
                        </div>
                    </div>                                
                </div>
            </div>
            <table>
                <thead class="font-bayon g-2">
                    <tr>
                        <th class="text-center">លេខសម្គាល់</th>
                        <th class="text-center">រូបភាព</th>
                        <th class="text-center">ឈ្មោះបុគ្គលិក</th>
                        <th class="text-center">ភេទ</th>
                        <th class="text-center">លេខទូរស័ព្ទ</th>
                        <th class="text-center">អ៊ីមែល</th>
                        <th class="text-center">ឈ្មោះគណនី</th>
                        <th class="text-center">ប្រាក់ខែ</th>
                        <th class="text-center">តួនាទី</th>
                        <th class="text-center">ថ្ងៃចូលធ្វើការ</th>
                        <th class="text-center">ស្ថានភាព</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php
                    $limitClause = $showAll ? "" : "LIMIT $limit OFFSET $offset";
                    $sql = "SELECT e.*, r.RoleName FROM Employees e 
                            LEFT JOIN Roles r ON e.RoleID = r.RoleID 
                            WHERE 1=1 $searchQueryCondition 
                            ORDER BY e.EmployeeID ASC 
                            $limitClause";
                    $result = mysqli_query($myCon, $sql);

                    $i = 1;
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Photo is stored web-relative from SITE ROOT (e.g. "uploads/employees/xxx.jpg").
                        // This page lives in administrator/, so we need to go up one level ("../")
                        // to reach it. Fall back to a placeholder when no photo is set.
                        if (!empty($row['Photo'])) {
                            $photoSrc = htmlspecialchars('../' . $row['Photo']);
                        } else {
                            $photoSrc = 'https://placehold.co/45';
                        }
                    ?>
                        <tr class="employee-row text-center font-kantumruy">
                            <td><span class="rank-badge"><?= $i++; ?></span></td>
                            <td>
                                <img src="<?= $photoSrc; ?>" class="rounded-circle" width="45" height="45" style="object-fit: cover;" onerror="this.src='https://placehold.co/45';">
                            </td>
                            <td class="emp-name"><?= htmlspecialchars($row['FullName']); ?></td>
                            <td><?= htmlspecialchars($row['Gender']); ?></td>
                            <td class="emp-phone"><?= htmlspecialchars($row['Phone']); ?></td>
                            <td><?= htmlspecialchars($row['Email']); ?></td>
                            <td><?= htmlspecialchars($row['Username']); ?></td>
                            <td>$<?= number_format($row['Salary'], 2); ?></td>
                            <td><?= htmlspecialchars($row['RoleName']); ?></td>
                            <td><?= date('d-m-Y', strtotime($row['HireDate'])); ?></td>
                            <td>
                                <span class="badge <?= ($row['Status'] == 'Active') ? 'status st-green' : 'status st-red'; ?>">
                                    <?= htmlspecialchars($row['Status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="align-items-center gap-3" style="margin-left: 10px;">
                <?php if ($totalPages > 0): ?>
                    <div class="d-flex justify-content-start mt-4" id="paginationWrapper">
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-md align-items-center gap-1">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link rounded-3 border-0 text-secondary bg-light" href="<?php echo ($page <= 1) ? '#' : buildPaginationUrl($page - 1, $searchTerm, $limitParam); ?>">
                                        <i class="fas fa-chevron-left small"></i>
                                    </a>
                                </li>

                                <?php
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $page + 2);

                                if ($startPage > 1) {
                                    echo '<li class="page-item"><a class="page-link rounded-3 border-0 text-dark bg-light" href="' . buildPaginationUrl(1, $searchTerm, $limitParam) . '">1</a></li>';
                                    if ($startPage > 2) echo '<li class="page-item disabled"><span class="page-link border-0 bg-transparent text-muted">...</span></li>';
                                }

                                for ($p = $startPage; $p <= $endPage; $p++):
                                    $activeClass = ($p == $page) ? 'border border-primary bg-white text-primary fw-bold' : 'border-0 bg-light text-dark';
                                ?>
                                    <li class="page-item">
                                        <a class="page-link rounded-3 <?php echo $activeClass; ?>" href="<?php echo buildPaginationUrl($p, $searchTerm, $limitParam); ?>">
                                            <?php echo $p; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php
                                if ($endPage < $totalPages) {
                                    if ($endPage < $totalPages - 1) {
                                        echo '<li class="page-item disabled"><span class="page-link border-0 bg-transparent text-muted">...</span></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link rounded-3 border-0 text-dark bg-light" href="' . buildPaginationUrl($totalPages, $searchTerm, $limitParam) . '">' . $totalPages . '</a></li>';
                                }
                                ?>

                                <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                    <a class="page-link rounded-3 border-0 text-secondary bg-light" href="<?php echo ($page >= $totalPages) ? '#' : buildPaginationUrl($page + 1, $searchTerm, $limitParam); ?>">
                                        <i class="fas fa-chevron-right small"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>        
    </div>

    <div class="toast-wrap">
        <?php if (isset($success_message)): ?>
            <div class="cool-alert success smooth-alert" role="alert">
                <div class="icon-badge"><i class="fas fa-check"></i></div>
                <div class="msg"><?php echo htmlspecialchars($success_message); ?></div>
                <button type="button" class="close-btn" onclick="this.closest('.cool-alert').remove()">
                    <i class="fas fa-times"></i>
                </button>
                <div class="progress-bar"></div>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="cool-alert error smooth-alert" role="alert">
                <div class="icon-badge"><i class="fas fa-exclamation"></i></div>
                <div class="msg"><?php echo htmlspecialchars($error_message); ?></div>
                <button type="button" class="close-btn" onclick="this.closest('.cool-alert').remove()">
                    <i class="fas fa-times"></i>
                </button>
                <div class="progress-bar"></div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Change page size -> reload with new limit, reset back to page 1
        function changePageSize(value) {
            var params = new URLSearchParams(window.location.search);
            params.set('limit', value);
            params.set('page', '1');
            window.location.search = params.toString();
        }

        // POPULATE EDIT MODAL WITH ROW DATA
        $('.btn-edit').on('click', function() {
            $('#e_id').val($(this).data('id'));
            $('#e_name').val($(this).data('fullname'));
            $('#e_gender').val($(this).data('gender'));
            $('#e_phone').val($(this).data('phone'));
            $('#e_email').val($(this).data('email'));
            $('#e_address').val($(this).data('address'));
            $('#e_username').val($(this).data('username'));
            $('#e_salary').val($(this).data('salary'));
            $('#e_role').val($(this).data('roleid'));
            $('#e_hiredate').val($(this).data('hiredate'));
            $('#e_status').val($(this).data('status'));

            $('#e_password').removeAttr('required');
        });

        // RESET CREATE MODAL EACH TIME IT OPENS
        $('#createModal').on('show.bs.modal', function() {
            this.reset();
        });

        // Automatic Toast dismissals
        setTimeout(() => {
            document.querySelectorAll('.smooth-alert').forEach((alert, index) => {
                setTimeout(() => {
                    alert.classList.add('hide');
                    setTimeout(() => alert.remove(), 400);
                }, index * 150);
            });
        }, 1500);

        function applyFilters() {
            var search = document.getElementById('searchInput').value.toLowerCase();

            document.querySelectorAll('#tableBody tr').forEach(function(row) {
                var name = row.querySelector('.emp-name')?.textContent.toLowerCase() || '';
                var phone = row.querySelector('.emp-phone')?.textContent.toLowerCase() || '';

                var matchesSearch = !search || name.includes(search) || phone.includes(search);

                row.style.display = matchesSearch ? '' : 'none';
            });
        }
    </script>
</body>

</html>