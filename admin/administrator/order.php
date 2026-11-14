<?php include '../include/cn.php';

if (isset($_POST['accept_order'])) {
    $success_message = "Accept Success!";
}

if (isset($_POST['reject_order'])) {
    $reject_success_message = "Reject Success!";
}

$sql = "SELECT o.OrderID,
    o.OrderDate,
    customer_name,
    c.Phone,
    c.Email,
    c.Address,
    o.OrderType,
    o.OrderStatus,
    o.PaymentStatus,
    o.SubTotal,
    o.TaxAmount,
    o.GrandTotal FROM Orders o LEFT JOIN Customers c ON o.CustomerID = c.CustomerID ORDER BY o.OrderID DESC";

$result = mysqli_query($myCon, $sql);

// CONFIRM PAYMENT
if (isset($_POST['btnConfirmPayment'])) {

    $orderID        = (int) $_POST['OrderID'];
    $paymentMethod  = $_POST['PaymentMethod'];
    $discountAmount = isset($_POST['DiscountAmount']) ? (float) $_POST['DiscountAmount'] : 0;

    // Get current total so we can safely clamp/apply the discount server-side
    $stmtCur = $myCon->prepare("SELECT GrandTotal FROM Orders WHERE OrderID = ?");
    $stmtCur->bind_param("i", $orderID);
    $stmtCur->execute();
    $curRow = $stmtCur->get_result()->fetch_assoc();
    $stmtCur->close();

    $currentTotal = $curRow ? (float) $curRow['GrandTotal'] : 0;
    if ($discountAmount > $currentTotal) $discountAmount = $currentTotal;
    if ($discountAmount < 0) $discountAmount = 0;

    $newGrandTotal = $currentTotal - $discountAmount;

    // Mark the order as paid
    $stmt = $myCon->prepare("
        UPDATE Orders
        SET OrderStatus = 'Completed',
            PaymentStatus = 'Paid',
            PaymentMethod = ?,
            DiscountAmount = ?,
            GrandTotal = ?
        WHERE OrderID = ?
    ");
    $stmt->bind_param("sddi", $paymentMethod, $discountAmount, $newGrandTotal, $orderID);
    $paidOk = $stmt->execute();
    $stmt->close();

    if ($paidOk) {
        $stmtArchive = $myCon->prepare("
            INSERT INTO history_orders
                (OrderID, CustomerID, customer_name, customer_phone, date, time, NumberOfGuests,
                 EmployeeID, TableID, tableType, OrderDate, OrderType, OrderStatus, PaymentStatus,
                 SubTotal, DiscountAmount, TaxAmount, GrandTotal, DeliveryAddress, Note,
                 message, reject_message, ArchivedDate)
            SELECT
                OrderID, CustomerID, customer_name, customer_phone, date, time, '' AS NumberOfGuests,
                EmployeeID, TableID, tableType, OrderDate,
                CASE WHEN OrderType = 'Takeout' THEN 'Take Away' ELSE OrderType END,
                OrderStatus, PaymentStatus, SubTotal, DiscountAmount, TaxAmount, GrandTotal,
                DeliveryAddress, Note, '' AS message, '' AS reject_message, NOW()
            FROM Orders
            WHERE OrderID = ?
            ON DUPLICATE KEY UPDATE
                PaymentStatus = VALUES(PaymentStatus),
                OrderStatus   = VALUES(OrderStatus),
                DiscountAmount = VALUES(DiscountAmount),
                GrandTotal    = VALUES(GrandTotal),
                ArchivedDate  = NOW()
        ");
        $stmtArchive->bind_param("i", $orderID);
        $stmtArchive->execute();
        $stmtArchive->close();

        // Free up the table this order was attached to, if any
        $stmtTbl = $myCon->prepare("SELECT TableID FROM Orders WHERE OrderID = ?");
        $stmtTbl->bind_param("i", $orderID);
        $stmtTbl->execute();
        $orderRow = $stmtTbl->get_result()->fetch_assoc();
        $stmtTbl->close();

        if ($orderRow && !empty($orderRow['TableID'])) {
            $tableID = (int) $orderRow['TableID'];

            // Table -> Available
            $stmtTable = $myCon->prepare("UPDATE RestaurantTables SET Status = 'Available' WHERE TableID = ?");
            $stmtTable->bind_param("i", $tableID);
            $stmtTable->execute();
            $stmtTable->close();

            // Whichever booking is currently CheckedIn for this table -> CheckedOut
            $stmtBooking = $myCon->prepare("
                UPDATE tablebookings
                SET BookingStatus = 'CheckedOut'
                WHERE TableID = ? AND BookingStatus = 'CheckedIn'
            ");
            $stmtBooking->bind_param("i", $tableID);
            $stmtBooking->execute();
            $stmtBooking->close();
        }

        $success_message = "Order has confirm paid!";
    } else {
        $error_message = "Order cannot confirm paid!!";
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
    $searchQueryCondition = " AND customer_name LIKE '%$searchTerm%'";
}

// Fetch total row count for calculating structural links with search criteria
$countSql = "SELECT COUNT(*) AS total FROM orders WHERE DATE(OrderDate) >= CURDATE() $searchQueryCondition";
$countResult = $myCon->query($countSql);
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = $showAll ? 1 : max(1, (int) ceil($totalRows / $limit));

// Function to maintain search query + page-size parameters across pagination links
function buildPaginationUrl($pageNum, $search, $limitParam)
{
    $url = "?page=" . $pageNum . "&limit=" . urlencode($limitParam);
    if (!empty($search)) {
        $url .= "&search=" . urlencode($search);
    }
    return $url;
}

// Holds each of today's orders (plus their line items) for the JS view-detail modal.
// Initialized here so json_encode() always gets an array, even when there are
// zero orders today (previously this stayed undefined -> `orders` became null in JS).
$historyRows = [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>
    <link rel="icon" type="image/png" href="../../img/logo-ang.png">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link href="https://fonts.googleapis.com/css2?family=Bayon&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="../assets/dist/css/order.css">
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

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }

        .detail-row .lbl {
            color: #6b7280;
        }

        .detail-row .val {
            font-weight: 600;
            text-align: right;
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

        .toast-wrap {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .cool-alert {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 320px;
            max-width: 380px;
            padding: 16px 16px 16px 18px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.14), 0 2px 6px rgba(0, 0, 0, 0.06);
            border-left: 5px solid transparent;
            position: relative;
            overflow: hidden;
            transform: translateX(140%);
            opacity: 0;
            animation: slideIn .5s cubic-bezier(.34, 1.56, .64, 1) forwards;
        }

        .cool-alert.success {
            border-left-color: #16a34a;
        }

        .cool-alert.error {
            border-left-color: #dc2626;
        }

        .cool-alert .icon-badge {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .cool-alert.success .icon-badge {
            background: linear-gradient(135deg, #34d399, #16a34a);
        }

        .cool-alert.error .icon-badge {
            background: linear-gradient(135deg, #f87171, #dc2626);
        }

        .cool-alert .msg {
            flex: 1;
            font-weight: 600;
            font-size: 14.5px;
            color: #1f2937;
            line-height: 1.3;
        }

        .cool-alert .close-btn {
            background: none;
            border: none;
            color: #9ca3af;
            font-size: 14px;
            cursor: pointer;
            padding: 4px 6px;
            line-height: 1;
            transition: color .2s, transform .2s;
        }

        .cool-alert .close-btn:hover {
            color: #374151;
            transform: scale(1.15);
        }

        .cool-alert .progress-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            width: 100%;
            background: rgba(0, 0, 0, 0.06);
        }

        .cool-alert .progress-bar::after {
            content: '';
            display: block;
            height: 100%;
            width: 100%;
            transform-origin: left;
            animation: shrink 1.5s linear forwards;
        }

        .cool-alert.success .progress-bar::after {
            background: linear-gradient(90deg, #34d399, #16a34a);
        }

        .cool-alert.error .progress-bar::after {
            background: linear-gradient(90deg, #f87171, #dc2626);
        }

        .cool-alert.hide {
            animation: slideOut .2s ease forwards;
        }

        @keyframes slideIn {
            from {
                transform: translateX(140%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(140%);
                opacity: 0;
            }
        }

        @keyframes shrink {
            from {
                transform: scaleX(1);
            }

            to {
                transform: scaleX(0);
            }
        }

        .btn-outline-success {
            color: #fff;
            border-color: #10b981;
        }

        .btn-outline-success:hover,
        .btn-outline-success:focus,
        .btn-outline-success.active {
            color: #fff;
            background-color: #10b981;
            border-color: #10b981;
        }

        .btn-check:checked+.btn-outline-success,
        .btn-check:active+.btn-outline-success {
            color: #fff;
            background-color: #10b981;
            border-color: #10b981;
        }

        .btn-confirm {
            background: #10b981;
            color: #fff;
            transition: .3s;
        }

        .btn-confirm:hover {
            background: #16a34a;
            color: #fff;
        }

        /* Unselected payment method buttons: white with green outline */
        #paymentMethodGroup .btn-outline-success {
            background-color: #fff !important;
            color: #10b981 !important;
            border-color: #10b981 !important;
        }

        /* Selected payment method button: filled green */
        #paymentMethodGroup .btn-check:checked+.btn-outline-success,
        #paymentMethodGroup .btn-outline-success.active {
            background-color: #10b981 !important;
            color: #fff !important;
            border-color: #10b981 !important;
        }

        /* ── Table ── */
        .table-card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            margin-bottom: 22px;
        }

        .metric-card,
        .chart-card,
        .table-card,
        .customer-card,
        .settings-panel,
        .settings-nav {
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05);
        }

        .table-card-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            padding: 12px 22px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: var(--text3);
            letter-spacing: 0.5px;
            text-transform: uppercase;
            background: var(--bg3);
            border-bottom: 1px solid var(--border);
        }

        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background .15s;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody tr:hover {
            background: var(--bg3);
        }

        tbody td {
            padding: 14px 22px;
            font-size: 12.5px;
            color: #000;
        }

        .td-main {
            color: var(--text);
            font-weight: 500;
        }

        .td-mono {
            font-family: var(--mono);
            font-size: 12px;
        }
    </style>
</head>

<body>
    <?php include '../include/administratorbd.php'; ?>
    <?php include '../include/administratorhd.php'; ?>
    <?php include '../include/smooth_refresh.php'; ?>

    <!-- My PAGE CONTENT -->
    <div class="container py-3">
        <!-- Title -->
        <div class="page-title font-bayon">
            បញ្ជីកម្មង់
        </div>

        <!-- Add Button -->
        <div class="d-flex justify-content-end mb-4">
            <button class="font-bayon btn-success create-btn" data-bs-toggle="modal" data-bs-target="#tableModal">
                <i class="fas fa-credit-card me-2"></i>
                ទូទាត់ប្រាក់
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
                        <th class="text-center">លេខបញ្ជាទិញ</th>
                        <th class="text-center">អតិថិជន</th>
                        <th class="text-center">លេខទូរស័ព្ទ</th>
                        <th class="text-center">ប្រភេទការកម្មង់</th>
                        <th class="text-center">តម្លៃសរុបរង</th>
                        <th class="text-center">ពន្ធ</th>
                        <th class="text-center">តម្លៃសរុប</th>
                        <th class="text-center">ស្ថានភាពការកម្មង់</th>
                        <th class="text-center">ស្ថានភាពការទូទាត់</th>
                        <th class="text-center">កាលបរិច្ឆេទ</th>
                        <th class="text-center">អនុកម្ម</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $limitClause = $showAll ? "" : "LIMIT $limit OFFSET $offset";
                    $sql = "SELECT * FROM orders WHERE DATE(OrderDate) >= CURDATE() $searchQueryCondition ORDER BY OrderID ASC $limitClause";
                    $qr = $myCon->query($sql);

                    $i = 1;
                    $result = mysqli_query($myCon, $sql);

                    if (mysqli_num_rows($result) === 0) {
                    ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted​ text-danger font-kantumruy py-4" style="font-size: 13px;">
                                មិនទាន់មានការកម្មង់សម្រាប់ថ្ងៃនេះទេ
                            </td>
                        </tr>
                        <?php
                    } else {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $orderID  = (int)$row['OrderID'];
                            $itemsSql = "SELECT od.MenuItemID, od.Quantity, od.UnitPrice, od.SubTotal,
                                                mi.ItemName AS MenuItemName
                                        FROM orderdetails od
                                        LEFT JOIN menuitems mi ON od.MenuItemID = mi.MenuItemID
                                        WHERE od.OrderID = $orderID";
                            $itemsRes = mysqli_query($myCon, $itemsSql);
                            $lineItems = [];
                            if ($itemsRes) {
                                while ($it = mysqli_fetch_assoc($itemsRes)) {
                                    $lineItems[] = $it;
                                }
                            }
                            $row['Items'] = $lineItems;

                            $historyRows[] = $row;
                        ?>
                            <tr class="text-center font-kantumruy">
                                <td><span class="rank-badge"><?= $i; $i++; ?></span></td>
                                <td class="cust-name"><?= htmlspecialchars($row['customer_name']); ?></td>
                                <td class="cust-phone"><?= htmlspecialchars($row['customer_phone']); ?></td>
                                <td><?= htmlspecialchars($row['OrderType']); ?></td>
                                <td>$<?= number_format($row['SubTotal'], 2); ?></td>
                                <td>$<?= number_format($row['TaxAmount'], 2); ?></td>
                                <td>$<?= number_format($row['GrandTotal'], 2); ?></td>
                                <td><?= htmlspecialchars($row['OrderStatus']); ?></td>
                                <td>
                                    <?php if ($row['PaymentStatus'] == 'Paid') { ?>
                                        <span class="status st-green">
                                            ទូទាត់រួច
                                        </span>
                                    <?php } else { ?>
                                        <span class="status st-amber">
                                            មិនទាន់ទូទាត់
                                        </span>
                                    <?php } ?>
                                </td>
                                <td><?= date('d-M-Y / h:i A', strtotime($row['OrderDate'])); ?></td>
                                <!-- STATUS  -->
                                <td>
                                    <?php if ($row['PaymentStatus'] == 'UnPaid') { ?>

                                        <a href="payment.php?id=<?= (int)$row['OrderID']; ?>" class="btn btn-warning btn-sm">
                                            Pay
                                        </a>

                                    <?php } else { ?>

                                        <button onclick="openViewModal(<?= (int)$row['OrderID']; ?>)" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal">
                                            View
                                        </button>

                                    <?php } ?>
                                </td>
                            </tr>
                    <?php }
                    } ?>
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

    <!-- Modal -->
    <form action="" method="POST" enctype="multipart/form-data" class="modal fade" id="tableModal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header" style="background: #10b981;">
                    <h5 class="modal-title font-bayon">
                        ទទួលការទូទាត់
                    </h5>

                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Order Reference -->
                        <div class="col-md-6">
                            <label class="form-label font-bayon">
                                លេខការកម្មង់
                            </label>
                            <?php
                            $sqlOrder = "
                                    SELECT OrderID, GrandTotal
                                    FROM Orders
                                    WHERE PaymentStatus = 'UnPaid'
                                    ORDER BY OrderID DESC
                                ";

                            $resultOrder = mysqli_query($myCon, $sqlOrder);
                            ?>
                            <select class="form-select font-kantumruy" name="OrderID" id="OrderID"
                                onchange="getAmountDue()">

                                <option value="">
                                    ជ្រើសរើសលេខការកម្មង់
                                </option>

                                <?php while ($row = mysqli_fetch_assoc($resultOrder)) { ?>
                                    <option value="<?= $row['OrderID']; ?>" data-total="<?= $row['GrandTotal']; ?>">
                                        #ORD-<?= str_pad($row['OrderID'], 4, '0', STR_PAD_LEFT); ?>
                                    </option>
                                <?php } ?>

                            </select>
                        </div>

                        <!-- Amount Due -->
                        <div class="col-md-6">
                            <label class="form-label font-bayon">
                                ទឹកប្រាក់ត្រូវបង់
                            </label>

                            <input type="text" class="form-control font-kantumruy" id="amountDue" value="$ 0.00" readonly>
                        </div>

                        <!-- Discount (percent only) -->
                        <div class="col-md-6">
                            <label class="form-label font-bayon">
                                ការបញ្ចុះតម្លៃ (%)
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" max="100"
                                    class="form-control font-kantumruy"
                                    id="discountValue" placeholder="0" oninput="calcDiscount()">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>

                        <!-- Amount Due After Discount -->
                        <div class="col-md-6">
                            <label class="form-label font-bayon">
                                តម្លៃបន្ទាប់ពីបញ្ចុះតម្លៃ
                            </label>
                            <input type="text" class="form-control font-kantumruy fw-bold" id="amountDueAfterDiscount" value="$ 0.00" readonly>
                        </div>

                        <input type="hidden" name="DiscountAmount" id="DiscountAmountHidden" value="0">
                        <input type="hidden" id="dueAfterDiscountUSD" value="0">

                        <!-- Payment Method -->
                        <div class="col-12">
                            <label class="form-label font-bayon">
                                វិធីសាស្ត្រទូទាត់
                            </label>

                            <div class="btn-group w-100" id="paymentMethodGroup" role="group">
                                <input type="radio"
                                    class="btn-check"
                                    name="PaymentMethodChoice"
                                    id="methodCash"
                                    value="Cash"
                                    checked>
                                <label class="btn btn-outline-success font-kantumruy"
                                    for="methodCash">
                                    <i class="bi bi-cash-coin"></i> សាច់ប្រាក់
                                </label>

                                <input type="radio"
                                    class="btn-check"
                                    name="PaymentMethodChoice"
                                    id="methodKHQR"
                                    value="KHQR">
                                <label class="btn btn-outline-success font-kantumruy"
                                    for="methodKHQR">
                                    <i class="bi bi-qr-code"></i> KHQR
                                </label>

                                <input type="radio"
                                    class="btn-check"
                                    name="PaymentMethodChoice"
                                    id="methodBank"
                                    value="BankTransfer">
                                <label class="btn btn-outline-success font-kantumruy"
                                    for="methodBank">
                                    <i class="bi bi-bank"></i> ផ្ទេរប្រាក់
                                </label>

                            </div>

                            <!-- Only this hidden field is actually submitted as PaymentMethod -->
                            <input type="hidden" name="PaymentMethod" id="PaymentMethod" value="Cash">
                        </div>

                        <!-- ========= CASH PANEL ========= -->
                        <div class="col-12 payment-panel" id="panelCash">

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label font-bayon">
                                        ប្រាក់ដែលទទួលបាន
                                    </label>
                                    <input type="number"
                                        step="0.01"
                                        class="form-control font-kantumruy"
                                        id="cashReceived"
                                        name="CashReceived"
                                        placeholder="$ 0.00"
                                        oninput="calcChange()">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label font-bayon">
                                        ប្រាក់អាប់
                                    </label>
                                    <input type="text"
                                        class="form-control font-kantumruy fw-bold"
                                        id="cashChange"
                                        name="CashChange"
                                        value="$ 0.00 / ៛0"
                                        readonly>
                                </div>

                            </div>

                        </div>

                        <!-- ========= KHQR PANEL ========= -->
                        <div class="col-12 payment-panel d-none" id="panelKHQR">

                            <div class="text-center">

                                <img src="Images/QR-Code/QR_Code.png"
                                    id="khqrImg"
                                    class="img-fluid mb-2"
                                    style="max-width:180px;"
                                    alt="KHQR Code"
                                    onerror="this.onerror=null;this.src='https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' + encodeURIComponent('KHQR Payment');">

                                <p class="small text-muted mb-1">
                                    Scan to pay
                                </p>

                                <h5 class="text-success fw-bold mb-3 font-kantumruy"
                                    id="qrAmountDisplay">
                                    $0.00 / ៛0
                                </h5>

                            </div>

                        </div>

                        <!-- ========= BANK TRANSFER PANEL ========= -->
                        <div class="col-12 payment-panel d-none" id="panelBank">

                            <div class="border rounded p-3">

                                <div class="d-flex justify-content-between mb-2 font-kantumruy">
                                    <span>Bank</span>
                                    <strong>ABA Bank</strong>
                                </div>

                                <div class="d-flex justify-content-between font-kantumruy">
                                    <span>Amount</span>
                                    <strong id="bankAmtDisplay">$0.00 / ៛0</strong>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="modal-footer font-kantumruy">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Back
                    </button>

                    <button type="submit" class="btn btn-confirm" name="btnConfirmPayment" onclick="return confirmPayment();">
                        <i class="fas fa-check-circle me-2"></i>Confirm Payment
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- View Detail Modal (Bootstrap) -->
    <div class="modal fade" id="viewModal">
        <div class="modal-dialog modal-md">
            <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden">
                <div class="modal-header" style="background:#2563eb;color:#fff">
                    <h5 class="modal-title font-bayon" id="viewModalTitle">ព័ត៌មានលម្អិតនៃការបញ្ជាទិញ</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewModalBody" style="padding:20px"></div>
                <div class="modal-footer" id="viewModalFooter"></div>
            </div>
        </div>
    </div>

    <!-- Delete/Reject Order Modal -->
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

    <script src="assets/dist/js/main.js"></script>
    <script>
        // ── Reusable SweetAlert2 confirm dialog styled with font-kantumruy ──
        // Replaces every native confirm()/alert() so the popup, buttons, and text
        // all render in the Khmer font (impossible with native browser dialogs).
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
            return false; // always block the native action; onYes() re-triggers it if confirmed
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

        // Change page size -> reload with new limit, reset back to page 1
        function changePageSize(value) {
            var params = new URLSearchParams(window.location.search);
            params.set('limit', value);
            params.set('page', '1');
            window.location.search = params.toString();
        }

        const exchangeRate = 4100;

        // Display Amount Due
        function getAmountDue() {
            const select = document.getElementById('OrderID');
            const option = select.options[select.selectedIndex];

            const totalUSD = parseFloat(option.dataset.total || 0);
            const totalKHR = totalUSD * exchangeRate;

            const displayText =
                '$ ' + totalUSD.toFixed(2) +
                ' / ៛ ' + totalKHR.toLocaleString();

            document.getElementById('amountDue').value = displayText;

            // Reset cash fields when order changes
            document.getElementById('cashReceived').value = '';
            document.getElementById('cashChange').value = '$ 0.00 / ៛0';

            // Reset discount when order changes
            document.getElementById('discountValue').value = '';

            calcDiscount();
        }

        // Calculate discount (percent only) and the amount due after discount
        function calcDiscount() {
            const select = document.getElementById('OrderID');
            const option = select.options[select.selectedIndex];
            const totalUSD = parseFloat(option.dataset.total || 0);

            let discPercent = parseFloat(document.getElementById('discountValue').value) || 0;
            if (discPercent < 0) discPercent = 0;
            if (discPercent > 100) discPercent = 100;

            const discountAmount = totalUSD * (discPercent / 100);
            const dueAfterDiscount = totalUSD - discountAmount;
            const dueAfterDiscountKHR = dueAfterDiscount * exchangeRate;
            const displayText = '$ ' + dueAfterDiscount.toFixed(2) + ' / ៛ ' + dueAfterDiscountKHR.toLocaleString();

            document.getElementById('DiscountAmountHidden').value = discountAmount.toFixed(2);
            document.getElementById('dueAfterDiscountUSD').value = dueAfterDiscount.toFixed(2);
            document.getElementById('amountDueAfterDiscount').value = displayText;

            // Keep KHQR / Bank panels in sync with the discounted amount
            document.getElementById('qrAmountDisplay').innerText = displayText;
            document.getElementById('bankAmtDisplay').innerText = displayText;

            calcChange();
        }

        // Calculate Change (always derives due amount live from total + discount %)
        function calcChange() {
            const select = document.getElementById('OrderID');
            const option = select.options[select.selectedIndex];
            const totalUSD = parseFloat(option.dataset.total || 0);

            let discPercent = parseFloat(document.getElementById('discountValue').value) || 0;
            if (discPercent < 0) discPercent = 0;
            if (discPercent > 100) discPercent = 100;

            const dueUSD = totalUSD - (totalUSD * (discPercent / 100));
            const receivedUSD =
                parseFloat(document.getElementById('cashReceived').value) || 0;

            let changeUSD = receivedUSD - dueUSD;

            if (changeUSD < 0) {
                changeUSD = 0;
            }

            const changeKHR = changeUSD * exchangeRate;

            document.getElementById('cashChange').value =
                '$ ' + changeUSD.toFixed(2) +
                ' / ៛ ' + changeKHR.toLocaleString();
        }

        document.addEventListener('DOMContentLoaded', function() {

            const panelCash = document.getElementById('panelCash');
            const panelKHQR = document.getElementById('panelKHQR');
            const panelBank = document.getElementById('panelBank');
            const paymentMethodField = document.getElementById('PaymentMethod');

            const allMethodRadios = document.querySelectorAll('input[name="PaymentMethodChoice"]');

            function showPanel(panel) {
                panelCash.classList.add('d-none');
                panelKHQR.classList.add('d-none');
                panelBank.classList.add('d-none');

                panel.classList.remove('d-none');
            }

            function setActiveLabel(selectedRadio) {
                allMethodRadios.forEach(function(radio) {
                    const label = document.querySelector('label[for="' + radio.id + '"]');
                    if (label) label.classList.remove('active');
                });
                const selectedLabel = document.querySelector('label[for="' + selectedRadio.id + '"]');
                if (selectedLabel) selectedLabel.classList.add('active');
            }

            document.getElementById('methodCash').addEventListener('change', function() {
                showPanel(panelCash);
                paymentMethodField.value = 'Cash';
                setActiveLabel(this);
            });

            document.getElementById('methodKHQR').addEventListener('change', function() {
                showPanel(panelKHQR);
                paymentMethodField.value = 'KHQR';
                setActiveLabel(this);
                document.getElementById('qrAmountDisplay').innerText =
                    document.getElementById('amountDueAfterDiscount').value;
            });

            document.getElementById('methodBank').addEventListener('change', function() {
                showPanel(panelBank);
                paymentMethodField.value = 'BankTransfer';
                setActiveLabel(this);
                document.getElementById('bankAmtDisplay').innerText =
                    document.getElementById('amountDueAfterDiscount').value;
            });

            // Initial state on page load (Cash is checked by default)
            setActiveLabel(document.getElementById('methodCash'));

        });


        function confirmPayment() {
            // Check Order ID
            const orderId = document.getElementById('OrderID').value;

            if (!orderId) {
                notifyKantumruy('សូមជ្រើសរើសលេខការកម្មង់!');
                return false;
            }

            // Get payment method
            const paymentMethod =
                document.getElementById('PaymentMethod').value;

            if (!paymentMethod) {
                notifyKantumruy('សូមជ្រើសរើសវិធីបង់ប្រាក់!');
                return false;
            }

            // Cash validation (uses due amount AFTER discount)
            if (paymentMethod === 'Cash') {
                const due = parseFloat(document.getElementById('dueAfterDiscountUSD').value) || 0;
                const received = parseFloat(document.getElementById('cashReceived').value) || 0;

                if (received < due) {
                    notifyKantumruy('ប្រាក់ដែលទទួលបានមិនគ្រប់ចំនួនទេ!');
                    document.getElementById('cashReceived').focus();
                    return false;
                }
            }

            // Final confirmation (styled, font-kantumruy dialog instead of native confirm())
            return confirmKantumruy('តើអ្នកពិតជាចង់បញ្ជាក់ការទូទាត់នេះមែនទេ?', () => {
                const form = document.getElementById('tableModal');
                const btn = document.querySelector('[name="btnConfirmPayment"]');
                if (form && btn) form.requestSubmit(btn);
            });
        }

        // Smooth slide out
        setTimeout(() => {
            document.querySelectorAll('.smooth-alert').forEach((alert, index) => {
                setTimeout(() => {
                    alert.classList.add('hide');
                    setTimeout(() => alert.remove(), 400);
                }, index * 150);
            });
        }, 1500);

        function slideOutRight(el) {
            el.style.transform = 'translateX(100%)';
            el.style.opacity = '0';
        }

        function applyFilters() {
            var search = document.getElementById('searchInput').value.toLowerCase();

            document.querySelectorAll('.custom-table-scroll table tbody tr').forEach(function(row) {
                var name = row.querySelector('.cust-name')?.textContent.toLowerCase() || '';
                var phone = row.querySelector('.cust-phone')?.textContent.toLowerCase() || '';

                var matchesSearch = !search || name.includes(search) || phone.includes(search);

                row.style.display = matchesSearch ? '' : 'none';
            });
        }

        var orders = <?= json_encode($historyRows); ?>;
        var viewModalBS = new bootstrap.Modal(document.getElementById('viewModal'));

        function escHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function orderStatusLabel(status) {
            if (!status || status.trim() === '') return 'Completed';
            if (status === 'Completed') return 'Completed';
            if (status === 'Cancelled') return 'Cancelled';
            return status;
        }

        function orderStatusBadgeClass(status) {
            if (!status || status.trim() === '') return 'status st-green';
            if (status === 'Completed') return 'status st-green';
            if (status === 'Cancelled') return 'status st-red';
            return 'status st-amber'; // Pending / other
        }

        /* ── Payment status (Paid / Unpaid) ───────────────────────── */
        function paymentStatusLabel(status) {
            if (status === 'Paid') return 'Paid';
            if (status === 'Unpaid') return 'Unpaid';
            return 'Unpaid';
        }

        function paymentStatusBadgeClass(status) {
            if (status === 'Paid') return 'status st-green';
            return 'status st-amber';
        }

        function formatTime12h(timeStr) {
            if (!timeStr) return '';
            var parts = String(timeStr).split(':');
            if (parts.length < 2) return timeStr;
            var h = parseInt(parts[0], 10);
            var m = parts[1];
            var ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12;
            if (h === 0) h = 12;
            return h + ':' + m + ' ' + ampm;
        }

        function formatDateDMY(dateStr) {
            if (!dateStr) return '';
            var parts = String(dateStr).split('-'); // expects YYYY-MM-DD
            if (parts.length !== 3) return dateStr;
            return parts[2] + '/' + parts[1] + '/' + parts[0];
        }

        /* ── View detail modal (styled to match order.php's modal) ── */
        function openViewModal(id) {
            var o = orders.find(function(x) {
                return x.OrderID == id;
            });
            if (!o) return;

            var orderStatus = (o.OrderStatus && o.OrderStatus.trim() !== '') ? o.OrderStatus : 'Completed';
            var paymentStatus = o.PaymentStatus || 'Unpaid';

            var addressHtml = '';
            if (o.OrderType === 'Delivery' && o.DeliveryAddress) {
                addressHtml = detailRow('អាសយដ្ឋានដឹកជញ្ជូន', escHtml(o.DeliveryAddress));
            }

            document.getElementById('viewModalBody').innerHTML =
                detailRow('អតិថិជន', escHtml(o.customer_name)) +
                detailRow('លេខទូរស័ព្ទ', escHtml(o.customer_phone)) +
                detailRow('ប្រភេទ', escHtml(o.OrderType)) +
                addressHtml +
                detailRow('ស្ថានភាព', '<span class="' + orderStatusBadgeClass(orderStatus) + '">' + escHtml(orderStatus) + '</span>') +
                detailRow('ការទូទាត់', '<span class="' + paymentStatusBadgeClass(paymentStatus) + '">' + paymentStatusLabel(paymentStatus) + '</span>') +
                itemsList(o.Items) +
                detailRow('សរុប', '<span style="color:#042C53;font-weight:700;font-size:16px">$ ' + parseFloat(o.GrandTotal || 0).toFixed(2) + '</span>') +
                (o.message ? detailRow('សារ', escHtml(o.message)) : '') +
                detailRow('ថ្ងៃកម្មង់', formatDateDMY(o.date) + ' ' + formatTime12h(o.time));

            document.getElementById('viewModalFooter').innerHTML =
                '<button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>';

            viewModalBS.show();
        }

        function detailRow(lbl, val) {
            return '<div class="detail-row font-kantumruy">' +
                '<span class="lbl">' + lbl + '</span>' +
                '<span class="val">' + val + '</span>' +
                '</div>';
        }

        function itemsList(items) {
            if (!items || !items.length) {
                return detailRow('មុខម្ហូប', '<span class="text-muted">មិនមានទិន្នន័យ</span>');
            }

            var rows = items.map(function(it) {
                var name = it.MenuItemName ? escHtml(it.MenuItemName) : ('#' + escHtml(it.MenuItemID));
                return '<tr><td>' + name + '</td>' +
                    '<td class="text-center">' + escHtml(it.Quantity) + '</td>' +
                    '<td class="text-end">$ ' + parseFloat(it.SubTotal).toFixed(2) + '</td></tr>';
            }).join('');

            return '<div class="detail-row font-kantumruy" style="align-items:flex-start">' +
                '<span class="lbl">មុខម្ហូប</span>' +
                '<span class="val"><table class="table table-sm mb-0" style="font-size:13px">' +
                '<thead><tr><th>ម្ហូប</th><th class="text-center">ចំនួន</th><th class="text-end">តម្លៃ</th></tr></thead>' +
                '<tbody>' + rows + '</tbody></table></span></div>';
        }
    </script>
</body>

</html>

