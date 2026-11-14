<?php include 'include/cn.php';
include '../include/walkin.php';

$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host    = $_SERVER['HTTP_HOST'];
$baseUrl = $scheme . '://' . $host . dirname(dirname($_SERVER['PHP_SELF']));
$baseUrl = rtrim($baseUrl, '/');


// ADD NEW TABLE  (prepared statement to avoid SQL injection)
if (isset($_POST['btnSavetable'])) {
    $tableName = $_POST['TableName'];
    $capacity  = $_POST['Capacity'];
    $location  = $_POST['Location'];
    $status    = $_POST['Status'];

    $sql  = "INSERT INTO RestaurantTables (TableName, Capacity, Location, Status)
             VALUES (?, ?, ?, ?)";
    $stmt = $myCon->prepare($sql);
    $stmt->bind_param("siss", $tableName, $capacity, $location, $status);

    if ($stmt->execute()) {
        $success_message = "Added Table Success!";
    } else {
        $error_message = "Error! Cannot insert data: " . $myCon->error;
    }
    $stmt->close();
}

// Update drink via POST to prevent data sync mismatch
if (isset($_POST['btnUpdate']) && isset($_POST['tableID'])) {
    $tableID = (int)$_POST['tableID'];
    $TableName = $myCon->real_escape_string($_POST['TableName']);
    $Capacity = $myCon->real_escape_string($_POST['Capacity']);
    $Location = $myCon->real_escape_string($_POST['Location']);
    $Status = $myCon->real_escape_string($_POST['Status']);

    $sqlUpdate = "UPDATE restauranttables SET TableName='$TableName', Capacity='$Capacity', Location='$Location' , Status='$Status' WHERE TableID=$tableID";

    if ($myCon->query($sqlUpdate) === TRUE) {
        $success_message = "Update Table Success!";
    } else {
        $error_message = "Error! Cannot update data: " . $myCon->error;
    }
}

// DELETE TABLE
if (isset($_REQUEST['delID'])) {
    $delID = (int) $_REQUEST['delID'];

    $stmt = $myCon->prepare("DELETE FROM RestaurantTables WHERE TableID = ?");
    $stmt->bind_param("i", $delID);

    if ($stmt->execute()) {
        $success_message = "Deleted Table Success!";
    } else {
        $error_message = "Error! Cannot delete data: " . $myCon->error;
    }
    $stmt->close();
}

// PROCESS TABLE
if (isset($_POST['btnProcessBooking'])) {
    $tableID       = (int) $_POST['TableID'];
    $bookingStatus = $_POST['Status'];

    $confirmPhone  = isset($_POST['ConfirmPhone']) ? trim($_POST['ConfirmPhone']) : '';

    if ($bookingStatus === 'Check In') {
        $newTableStatus   = 'Occupied';
        $newBookingStatus = 'CheckedIn';
    } elseif ($bookingStatus === 'Check Out') {
        $newTableStatus   = 'Available';
        $newBookingStatus = 'CheckedOut';
    } else {
        $newTableStatus   = null;
        $newBookingStatus = null;
    }

    if ($tableID && $newTableStatus) {

        if ($bookingStatus === 'Check In') {
            $lookupStatus = 'Reserved';
            $stmtFind = $myCon->prepare("
                SELECT b.BookingID, c.CustomerName, c.Phone AS ContactPhone
                FROM tablebookings b
                LEFT JOIN Customers c ON b.CustomerID = c.CustomerID
                WHERE b.TableID = ? AND b.BookingStatus = ? AND b.BookingDate >= CURDATE()
                ORDER BY b.BookingDate ASC, b.BookingTime ASC
                LIMIT 1
            ");
            $stmtFind->bind_param("is", $tableID, $lookupStatus);
        } else {
            $lookupStatus = 'CheckedIn';
            $stmtFind = $myCon->prepare("
                SELECT b.BookingID, c.CustomerName, c.Phone AS ContactPhone
                FROM tablebookings b
                LEFT JOIN Customers c ON b.CustomerID = c.CustomerID
                WHERE b.TableID = ? AND b.BookingStatus = ?
                ORDER BY b.BookingDate DESC, b.BookingTime DESC
                LIMIT 1
            ");
            $stmtFind->bind_param("is", $tableID, $lookupStatus);
        }

        $stmtFind->execute();
        $bookingRow = $stmtFind->get_result()->fetch_assoc();
        $stmtFind->close();

        $identityOk = true;

        if ($bookingStatus === 'Check In' && $bookingRow && $confirmPhone !== '') {
            $last4Stored = substr(preg_replace('/\D/', '', $bookingRow['ContactPhone']), -4);
            $last4Given  = substr(preg_replace('/\D/', '', $confirmPhone), -4);
            $identityOk  = ($last4Stored === $last4Given);
        }

        if (!$identityOk) {
            $error_message = "Phone number does not match. You entered a number ending in \""
                . htmlspecialchars($last4Given) . "\", but the booking for "
                . htmlspecialchars($bookingRow['CustomerName'])
                . " is on file ending in \"" . htmlspecialchars($last4Stored) . "\". Check-in blocked.";
        } else {
            // Update the table
            $stmt = $myCon->prepare("UPDATE RestaurantTables SET Status = ? WHERE TableID = ?");
            $stmt->bind_param("si", $newTableStatus, $tableID);
            $stmt->execute();
            $stmt->close();

            // Update the matching booking record, if one exists
            if ($bookingRow) {
                $stmt2 = $myCon->prepare("UPDATE tablebookings SET BookingStatus = ? WHERE BookingID = ?");
                $stmt2->bind_param("si", $newBookingStatus, $bookingRow['BookingID']);
                $stmt2->execute();
                $stmt2->close();
            }

            $who = $bookingRow ? " for " . htmlspecialchars($bookingRow['CustomerName']) : "";
            $success_message = "Table has confirm check in!";
        }
    } else {
        $error_message = "Please select a table and a valid status.";
    }
}

// PROCESS WALKIN
if (isset($_POST['btnWalkIn'])) {
    $tableID    = (int) $_POST['WalkInTableID'];
    $partySize  = (int) ($_POST['PartySize'] ?? 1);
    if ($partySize < 1) {
        $partySize = 1;
    }

    if ($tableID) {
        $stmtCheck = $myCon->prepare("SELECT TableName, Status FROM RestaurantTables WHERE TableID = ?");
        $stmtCheck->bind_param("i", $tableID);
        $stmtCheck->execute();
        $tblRow = $stmtCheck->get_result()->fetch_assoc();
        $stmtCheck->close();

        if (!$tblRow) {
            $error_message = "Table not found.";
        } elseif ($tblRow['Status'] !== 'Available') {
            $error_message = "That table is no longer available. Please pick another table.";
        } else {
            $bookingStatusVal = 'CheckedIn';
            $stmtBooking = $myCon->prepare("
                INSERT INTO tablebookings (TableID, CustomerID, BookingDate, BookingTime, NumberOfGuests, BookingStatus)
                VALUES (?, NULL, CURDATE(), CURTIME(), ?, ?)
            ");
            $stmtBooking->bind_param("iis", $tableID, $partySize, $bookingStatusVal);
            $stmtBooking->execute();
            $stmtBooking->close();

            $stmtTbl = $myCon->prepare("UPDATE RestaurantTables SET Status = 'Occupied' WHERE TableID = ?");
            $stmtTbl->bind_param("i", $tableID);
            $stmtTbl->execute();
            $stmtTbl->close();

            $success_message = "Guest seated successfully!";
        }
    } else {
        $error_message = "Please select an available table.";
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
    $searchQueryCondition = " AND TableName LIKE '%$searchTerm%'";
}

// Fetch total row count for calculating structural links with search criteria
$countResult = $myCon->query("SELECT COUNT(*) AS total FROM restauranttables WHERE 1=1 $searchQueryCondition");
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
    <title>Table Booking</title>
    <link rel="icon" type="image/png" href="../img/logo-ang.png">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <link href="https://fonts.googleapis.com/css2?family=Bayon&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="assets/dist/css/tblbooking.css">
    <style>
        /* ── Scrollbar ── */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #dfe6ef;
            border-radius: 4px;
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

        .process-modal-header {
            background: #10b981;
        }

        .btn-checkin {
            background: #10b981;   
            color: #fff;     
            transition: .3s;
        }

        .btn-checkin:hover {
            background: #16a34a;
            color: #fff;
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
        .table-card-header { padding: 18px 22px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        table { width: 100%; border-collapse: collapse; }
        thead th { padding: 12px 22px; text-align: left; font-size: 13px; font-weight: 600; color: var(--text3); letter-spacing: 0.5px; text-transform: uppercase; background: var(--bg3); border-bottom: 1px solid var(--border); }
        tbody tr { border-bottom: 1px solid var(--border); transition: background .15s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--bg3); }
        tbody td { padding: 14px 22px; font-size: 13px; color: #000; }
        .td-main { color: var(--text); font-weight: 500; }
        .td-mono { font-family: var(--mono); font-size: 12px; }
    </style>
</head>

<body>

    <?php include 'include/adminsbd.php'; ?>
    <?php include 'include/adminhd.php'; ?>
    <?php include 'include/smooth_refresh.php'; ?>

    <!-- My PAGE CONTENT -->
    <div class="container py-3">

        <!-- Title -->
        <div class="page-title font-bayon">
            បញ្ជីតុក្នុងហាង
        </div>

        <!-- Add Button -->
        <div class="d-flex justify-content-end mb-4 gap-2">
            <button class="font-bayon btn-success process-btn" data-bs-toggle="modal" data-bs-target="#bookingModal">
                <i class="fa-solid fa-bag-shopping"></i>
                បញ្ជាក់ការកក់តុ
            </button>

            <button class="font-bayon create-btn" data-bs-toggle="modal" data-bs-target="#walkinModal">
                <i class="fa-solid fa-person-walking"></i>
                ភ្ញៀវមកដល់ទីតាំង
            </button>

            <button class="font-bayon create-btn" data-bs-toggle="modal" data-bs-target="#tableModal">
                បង្កើតតុថ្មី
            </button>
        </div>

        <!-- Table Card -->
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
                <thead>
                    <tr class="font-bayon">
                        <th class="text-center">លេខសម្គាល់</th>                        
                        <th class="text-center">ឈ្មោះតុ</th>
                        <th class="text-center">ចំនួនកៅអី</th>
                        <th class="text-center">ទីតាំង</th>
                        <th class="text-center">ស្ថានភាព</th>
                        <th class="text-center">ភ្ញៀវកក់</th>
                        <th class="text-center">អនុកម្ម</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $limitClause = $showAll ? "" : "LIMIT $limit OFFSET $offset";
                    $sql = "
                        SELECT t.*,
                                COALESCE(c.CustomerName, CONCAT('Walk-in ', t.TableName)) AS CustomerName,
                                COALESCE(c.Phone, b.ContactPhone) AS ContactPhone
                        FROM RestaurantTables t
                        LEFT JOIN tablebookings b ON b.BookingID = (
                            SELECT b2.BookingID
                            FROM tablebookings b2
                            WHERE b2.TableID = t.TableID
                            AND (
                                    b2.BookingStatus = 'CheckedIn'
                                    OR (b2.BookingStatus = 'Reserved' AND b2.BookingDate >= CURDATE())
                                )
                            ORDER BY (b2.BookingStatus = 'CheckedIn') DESC, b2.BookingDate ASC, b2.BookingTime ASC
                            LIMIT 1
                        )
                        LEFT JOIN Customers c ON b.CustomerID = c.CustomerID
                        ORDER BY t.TableID ASC
                    ";
                    $result = $myCon->query($sql);
                    $i = 1;

                    while ($row = mysqli_fetch_assoc($result)) {
                        $escapedName = addslashes(htmlspecialchars($row['TableName'], ENT_QUOTES, 'UTF-8'));
                        $escapedLocation = addslashes(htmlspecialchars($row['Location'], ENT_QUOTES, 'UTF-8'));
                        $escapedStatus = addslashes(htmlspecialchars($row['Status'], ENT_QUOTES, 'UTF-8'));
                    ?>
                        <tr class="text-center font-kantumruy">
                            <td><span class="rank-badge"><?= $i; $i++; ?></span></td>
                            <td class="tbl-name"><?= htmlspecialchars($row['TableName']); ?></td>
                            <td class="tbl-capacity"><?= htmlspecialchars($row['Capacity']); ?></td>
                            <td><?= htmlspecialchars($row['Location']); ?></td>
                            <td>
                                <?php if ($row['Status'] == 'Available') { ?>
                                    <span class="status st-green" style="font-size: 0.9rem;">តុទំនេរ</span>
                                <?php } elseif ($row['Status'] == 'Reserved') { ?>
                                    <span class="status st-amber" style="font-size: 0.9rem;">តុកក់</span>
                                <?php } elseif ($row['Status'] == 'Occupied') { ?>
                                    <span class="status st-red" style="font-size: 0.9rem;">តុមានភ្ញៀវ</span>
                                <?php } ?>
                            </td>
                            <!-- WHO HAS THIS TABLE -->
                            <td>
                                <?php if (!empty($row['CustomerName']) && in_array($row['Status'], ['Reserved', 'Occupied'])) { ?>
                                    <div><?= htmlspecialchars($row['CustomerName']); ?></div>
                                    <small class="text-muted">
                                        <?= !empty($row['ContactPhone']) ? htmlspecialchars($row['ContactPhone']) : 'Walk-in'; ?>
                                    </small>
                                <?php } else { ?>
                                    <span class="text-muted">-</span>
                                <?php } ?>
                            </td>

                            <!-- ACTION BUTTONS -->
                            <td>
                                <button type="button" class="btn btn-outline-primary btn-sm me-2"
                                    data-bs-toggle="modal" data-bs-target="#qrModal"
                                    onclick="showTableQR(<?php echo (int)$row['TableID']; ?>, '<?php echo $escapedName; ?>')"
                                    title="Show / print table QR code">
                                    <i class="fas fa-qrcode"></i>
                                </button>

                                <button type="button" class="btn text-white btn-warning btn-sm me-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editTableModal"
                                    onclick="fillEditForm(<?php echo (int)$row['TableID']; ?>, '<?php echo $escapedName; ?>', '<?php echo (int)$row['Capacity']; ?>', '<?php echo $escapedLocation; ?>', '<?php echo $escapedStatus; ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <a href="tblbooking.php?delID=<?php echo (int)$row['TableID']; ?>"
                                    class="btn btn-danger btn-sm font-kantumruy"
                                    onclick="return confirmKantumruy('តើអ្នកពិតជាចង់លុបតុនេះមែនទេ?', () => { window.location.href = this.href; })">
                                    <i class="fas fa-trash"></i>
                                </a>
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

    <form method="POST" class="modal fade" id="editTableModal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="font-kantumruy mb-0">Edit Table</h5>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="TableID" id="editTableID">
                    <div class="mb-3">
                        <label class="form-label font-bayon">ឈ្មោះតុ</label>
                        <input type="text" name="TableName" id="editTableName" class="form-control font-kantumruy" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-bayon">ចំនួនកៅអី</label>
                        <input type="number" name="Capacity" id="editCapacity" class="form-control font-kantumruy" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-bayon">ទីតាំង</label>
                        <select name="Location" id="editLocation" class="form-select font-kantumruy">
                            <option value="ក្រៅវ">ក្រៅវ</option>
                            <option value="ក្នុង">ក្នុង</option>
                            <option value="VIP">VIP</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-bayon">ស្ថានភាព</label>
                        <select name="Status" id="editStatus" class="form-select font-kantumruy">
                            <option value="Available">តុទំនេរ</option>
                            <option value="Reserved">តុកក់</option>
                            <option value="Occupied">តុមានភ្ញៀវ</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer font-kantumruy">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning" name="btnUpdate">Update</button>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal Add Table -->
    <form action="" method="POST" class="modal fade" id="tableModal" tabindex="-1" novalidate>
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title font-bayon">
                        បង្កើតតុថ្មី
                    </h5>

                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">

                    <div class="row g-3">

                        <!-- Table Name -->
                        <div class="col-md-6">
                            <label class="form-label font-bayon">
                                ឈ្មោះតុ
                            </label>
                            <input type="text" name="TableName" class="form-control font-kantumruy" placeholder="Table Name" required>
                        </div>

                        <!-- Capacity -->
                        <div class="col-md-6">
                            <label class="form-label font-bayon">ចំនួនកៅអី</label>
                            <input type="number" name="Capacity" class="form-control font-kantumruy" placeholder="Capacity" required>
                        </div>

                        <!-- Location -->
                        <div class="col-md-6">
                            <label class="form-label font-bayon">ទីតាំង</label>
                            <select class="form-select font-kantumruy" name="Location">
                                <option selected disabled>
                                    ប្រភេទទីតាំង
                                </option>
                                <option>ក្រៅវ</option>
                                <option>ក្នុង</option>
                                <option>VIP</option>
                            </select>
                        </div>

                        <!-- status -->
                        <div class="col-md-6">
                            <label class="form-label font-bayon">ស្ថានភាព</label>
                            <select class="form-select font-kantumruy" name="Status">
                                <option selected disabled>
                                    Status
                                </option>
                                <option>Not Available</option>
                                <option>Available</option>
                                <option>Reserved</option>
                                <option>Occupied</option>
                                <option>Cleaning</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer font-kantumruy">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="btnSavetable">Save</button>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal Process booking (Check In for EXISTING reservations) -->
    <form action="" method="POST" class="modal fade" id="bookingModal" tabindex="-1" novalidate>
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header process-modal-header">
                    <h5 class="modal-title font-bayon">
                        បញ្ជាក់ការកក់តុ
                    </h5>

                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label font-bayon">
                                ឈ្មោះតុ
                            </label>
                            <?php
                            $sqlOrder = "
                                    SELECT b.BookingID, t.TableID, t.TableName, t.Location,
                                        c.CustomerName, c.Phone AS ContactPhone, b.NumberOfGuests
                                    FROM tablebookings b
                                    JOIN RestaurantTables t ON b.TableID = t.TableID
                                    LEFT JOIN Customers c ON b.CustomerID = c.CustomerID
                                    WHERE b.BookingStatus = 'Reserved' AND b.BookingDate >= CURDATE()
                                    ORDER BY b.BookingDate ASC, b.BookingTime ASC
                                ";

                            $resultOrder = mysqli_query($myCon, $sqlOrder);
                            ?>
                            <select class="form-select font-kantumruy" name="TableID" id="TableID">

                                <option value="">
                                    ជ្រើសរើសឈ្មោះតុកក់
                                </option>

                                <?php while ($row = mysqli_fetch_assoc($resultOrder)) { ?>
                                    <option value="<?= $row['TableID']; ?>">
                                        #TBL - <?= htmlspecialchars($row['TableName']); ?> / <?= htmlspecialchars($row['Location']); ?>
                                        — <?= htmlspecialchars($row['CustomerName']); ?> (<?= htmlspecialchars($row['ContactPhone']); ?>, party of <?= (int) $row['NumberOfGuests']; ?>)
                                    </option>
                                <?php } ?>

                            </select>
                        </div>

                        <!-- Identity check: guest gives their phone number at the door -->
                        <div class="col-md-12">
                            <label class="form-label font-bayon">បញ្ជាក់លេខទូរស័ព្ទ</label>
                            <input type="text" name="ConfirmPhone" class="form-control font-kantumruy"
                                placeholder="លេខទូរស័ព្ទអតិថិជន">
                        </div>

                        <!-- This modal only performs Check In, so Status is fixed -->
                        <input type="hidden" name="Status" value="Check In">
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer font-kantumruy">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-checkin" name="btnProcessBooking">Check In</button>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal Walk-in Guest (NO prior reservation — seat at any Available table) -->
    <form action="" method="POST" class="modal fade" id="walkinModal" tabindex="-1" novalidate>
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header bg-primary">
                    <h5 class="modal-title font-bayon">
                        ភ្ញៀវមកដល់ទីតាំង
                    </h5>

                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Only tables that are currently Available show up here -->
                        <div class="col-md-12">
                            <label class="form-label font-bayon">
                                ជ្រើសរើសតុទំនេរ
                            </label>
                            <?php
                            $sqlAvail = "
                                SELECT TableID, TableName, Capacity, Location
                                FROM RestaurantTables
                                WHERE Status = 'Available'
                                ORDER BY TableName ASC
                            ";
                            $resultAvail = mysqli_query($myCon, $sqlAvail);
                            $availCount  = $resultAvail ? mysqli_num_rows($resultAvail) : 0;
                            ?>
                            <select class="form-select font-kantumruy" name="WalkInTableID" required>
                                <option value="" selected disabled>
                                    ជ្រើសរើសតុ
                                </option>
                                <?php while ($row = mysqli_fetch_assoc($resultAvail)) { ?>
                                    <option value="<?= $row['TableID']; ?>">
                                        #TBL - <?= htmlspecialchars($row['TableName']); ?> / <?= htmlspecialchars($row['Location']); ?>
                                        (<?= (int) $row['Capacity']; ?> seats)
                                    </option>
                                <?php } ?>
                            </select>
                            <?php if ($availCount === 0) { ?>
                                <div class="form-text text-danger">
                                    មិនមានតុទំនេរនាពេលនេះទេ
                                </div>
                            <?php } ?>
                        </div>

                        <!-- Party size -->
                        <div class="col-md-12">
                            <label class="form-label font-bayon">ចំនួនភ្ញៀវ</label>
                            <input type="number" name="PartySize" class="form-control font-kantumruy" min="1" value="1" required>
                        </div>

                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="modal-footer font-kantumruy">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="btnWalkIn" <?= $availCount === 0 ? 'disabled' : '' ?>>Save</button>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal: Table QR code (view / print) -->
    <div class="modal fade" id="qrModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content text-center">
                <div class="modal-header" style="background:#2563eb;color:#fff">
                    <h5 class="font-kantumruy" id="qrTableName">Table QR</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <img id="qrImage" src="" alt="Table QR code" class="mx-auto mb-2" style="width:220px; height:220px;">
                    <a id="qrUrl" href="#" target="_blank" rel="noopener"
                        class="small d-block mb-3" style="word-break:break-all;"></a>
                    <div class="d-flex gap-2 justify-content-center font-kantumruy">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-outline-primary" onclick="window.open(document.getElementById('qrUrl').href, '_blank')">
                            <i class="fas fa-arrow-up-right-from-square me-1"></i>Open Link
                        </button>
                        <button type="button" class="btn btn-primary" onclick="printQR()">
                            <i class="fas fa-print me-1"></i>Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Order Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteModalLabel">❌ Reject Order</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="location.reload()"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="order_id" id="reject_order_id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="reject_order" class="btn btn-danger" onclick="return confirmReject()">
                            <i class="fas fa-times me-1"></i>Confirm Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmKantumruy(message, onYes) {
            Swal.fire({
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'បាទ/ចាស',
                cancelButtonText: 'ទេ',
                confirmButtonColor: '#10b981',
                customClass: {
                    popup: 'font-kantumruy',
                    confirmButton: 'font-kantumruy',
                    cancelButton: 'font-kantumruy'
                }
            }).then((result) => {
                if (result.isConfirmed && typeof onYes === 'function') {
                    onYes();
                }
            });
            return false;
        }

        function notifyKantumruy(message, icon = 'warning') {
            Swal.fire({
                text: message,
                icon: icon,
                confirmButtonText: 'យល់ព្រម',
                confirmButtonColor: '#10b981',
                customClass: {
                    popup: 'font-kantumruy',
                    confirmButton: 'font-kantumruy'
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {

            // ── Add Table ──────────────────────────────
            let bypassTableValidation = false;
            document.getElementById('tableModal').addEventListener('submit', function (e) {
                if (bypassTableValidation) { bypassTableValidation = false; return; } // let it actually submit
                e.preventDefault();
                const form = this;
                const name = form.TableName.value.trim();
                const capacity = form.Capacity.value.trim();
                const location = form.Location.value;
                const status = form.Status.value;

                if (!name) return notifyKantumruy('សូមបញ្ចូលឈ្មោះតុ!');
                if (!capacity || parseInt(capacity) < 1) return notifyKantumruy('សូមបញ្ចូលចំនួនកៅអីឱ្យត្រឹមត្រូវ!');
                if (!location || location === 'ប្រភេទទីតាំង') return notifyKantumruy('សូមជ្រើសរើសទីតាំង!');
                if (!status || status === 'Status') return notifyKantumruy('សូមជ្រើសរើសស្ថានភាព!');

                confirmKantumruy('តើអ្នកពិតជាចង់បង្កើតតុនេះមែនទេ?', () => {
                    bypassTableValidation = true;
                    form.requestSubmit(document.querySelector('[name="btnSavetable"]'));
                });
            });

            // ── Check In (existing reservation) ─────────
            let bypassBookingValidation = false;
            document.getElementById('bookingModal').addEventListener('submit', function (e) {
                if (bypassBookingValidation) { bypassBookingValidation = false; return; }
                e.preventDefault();
                const form = this;
                const tableID = form.TableID.value;
                const phone = form.ConfirmPhone.value.trim();

                if (!tableID) return notifyKantumruy('សូមជ្រើសរើសតុកក់!');
                if (!phone) return notifyKantumruy('សូមបញ្ជូលលេខទូរស័ព្ទដើម្បីបញ្ជាក់!');

                confirmKantumruy('តើអ្នកពិតជាចង់បញ្ជាក់ការឆែកអ៊ីនមែនទេ?', () => {
                    bypassBookingValidation = true;
                    form.requestSubmit(document.querySelector('[name="btnProcessBooking"]'));
                });
            });

            // ── Walk-in Guest ────────────────────────────
            let bypassWalkinValidation = false;
            document.getElementById('walkinModal').addEventListener('submit', function (e) {
                if (bypassWalkinValidation) { bypassWalkinValidation = false; return; }
                e.preventDefault();
                const form = this;
                const tableID = form.WalkInTableID.value;
                const party = form.PartySize.value.trim();

                if (!tableID) return notifyKantumruy('សូមជ្រើសរើសតុទំនេរ!');
                if (!party || parseInt(party) < 1) return notifyKantumruy('សូមបញ្ចូលចំនួនភ្ញៀវឱ្យត្រឹមត្រូវ!');

                confirmKantumruy('តើអ្នកពិតជាចង់រក្សាទុកភ្ញៀវនេះមែនទេ?', () => {
                    bypassWalkinValidation = true;
                    form.requestSubmit(document.querySelector('[name="btnWalkIn"]'));
                });
            });

        });

        // Change page size -> reload with new limit, reset back to page 1
        function changePageSize(value) {
            var params = new URLSearchParams(window.location.search);
            params.set('limit', value);
            params.set('page', '1');
            window.location.search = params.toString();
        }

        const tableTokens = <?php
                            $tblRes = mysqli_query($myCon, "SELECT TableID FROM RestaurantTables");
                            $tokenMap = [];
                            while ($t = mysqli_fetch_assoc($tblRes)) {
                                $tokenMap[(int) $t['TableID']] = makeTableToken($t['TableID']);
                            }
                            echo json_encode($tokenMap);
                            ?>;

        const siteBaseUrl = <?php echo json_encode($baseUrl); ?>;

        function showTableQR(tableID, tableName) {
            const token = tableTokens[tableID];
            const url = siteBaseUrl + '/table_order.php?t=' + tableID + '&k=' + token;

            document.getElementById('qrTableName').textContent = 'QR for ' + tableName;

            const urlLink = document.getElementById('qrUrl');
            urlLink.textContent = url;
            urlLink.href = url;

            document.getElementById('qrImage').src =
                'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' + encodeURIComponent(url);
        }

        function printQR() {
            const w = window.open('', '_blank', 'width=400,height=520');
            w.document.write(
                '<html><body style="text-align:center;font-family:sans-serif;padding-top:30px;">' +
                '<h3>' + document.getElementById('qrTableName').textContent + '</h3>' +
                '<img src="' + document.getElementById('qrImage').src + '" width="220" height="220">' +
                '<p style="font-size:11px;color:#888;">' + document.getElementById('qrUrl').textContent + '</p>' +
                '</body></html>'
            );
            w.document.close();
            w.focus();
            setTimeout(() => {
                w.print();
            }, 400);
        }

        setTimeout(() => {
            document.querySelectorAll('.smooth-alert').forEach((alert, index) => {
                setTimeout(() => {
                    alert.classList.add('hide');
                    setTimeout(() => alert.remove(), 400);
                }, index * 150);
            });
        }, 1500);

        // Form fields bindings for update modals
        function fillEditForm(id, name, capacity, location, status) {
            document.getElementById('editTableID').value = id;
            document.getElementById('editTableName').value = name;
            document.getElementById('editCapacity').value = capacity;
            document.getElementById('editLocation').value = location;
            document.getElementById('editStatus').value = status;

        }

        function applyFilters() {
            var search = document.getElementById('searchInput').value.toLowerCase();

            document.querySelectorAll('.custom-table-scroll table tbody tr').forEach(function(row) {
                var name = row.querySelector('.tbl-name')?.textContent.toLowerCase() || '';
                var capacity = row.querySelector('.tbl-capacity')?.textContent.toLowerCase() || '';

                var matchesSearch = !search || name.includes(search) || capacity.includes(search);

                row.style.display = matchesSearch ? '' : 'none';
            });
        }
    </script>
</body>

</html>