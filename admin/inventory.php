<?php
include 'include/cn.php';
require_once 'include/stock_history.php';

$success_message = '';
$error_message = '';

if (isset($_POST['btnSavecate'])) {
    $menuItemId = isset($_POST['MenuItemID']) && ctype_digit((string)$_POST['MenuItemID'])
        ? (int)$_POST['MenuItemID']
        : 0;
    $transactionType = isset($_POST['TransactionType']) ? (string)$_POST['TransactionType'] : '';
    $quantity = isset($_POST['Quantity']) && ctype_digit((string)$_POST['Quantity'])
        ? (int)$_POST['Quantity']
        : 0;
    $reason = trim((string)($_POST['Reason'] ?? ''));

    if (strlen($reason) > 500) {
        $error_message = 'The reason must be 500 characters or fewer.';
    } elseif (
        $menuItemId < 1 ||
        $quantity < 1 ||
        $quantity > 2147483647 ||
        !in_array($transactionType, ['Stock In', 'Stock Out'], true)
    ) {
        $error_message = 'Please choose a valid stock movement and quantity.';
    } else {
        try {
            ensureStockHistoryTable($myCon);
            $myCon->begin_transaction();

            $stockStatement = $myCon->prepare(
                'SELECT ItemName, StockQty FROM MenuItems WHERE MenuItemID = ? FOR UPDATE'
            );
            $stockStatement->bind_param('i', $menuItemId);
            $stockStatement->execute();
            $stockRow = $stockStatement->get_result()->fetch_assoc();
            $stockStatement->close();

            if (!$stockRow) {
                throw new RuntimeException('The selected product no longer exists.');
            }

            $previousStock = (int)$stockRow['StockQty'];
            if ($transactionType === 'Stock In' && $previousStock > 2147483647 - $quantity) {
                throw new RuntimeException('The resulting stock would exceed the allowed quantity.');
            }
            $newStock = $transactionType === 'Stock In'
                ? $previousStock + $quantity
                : $previousStock - $quantity;

            if ($newStock < 0) {
                throw new RuntimeException('Stock out quantity cannot exceed the current stock.');
            }

            $updateStatement = $myCon->prepare(
                'UPDATE MenuItems SET StockQty = ? WHERE MenuItemID = ?'
            );
            $updateStatement->bind_param('ii', $newStock, $menuItemId);
            $updateStatement->execute();
            $updateStatement->close();

            if (!recordStockHistory(
                $myCon,
                $menuItemId,
                $stockRow['ItemName'],
                $transactionType,
                $quantity,
                $previousStock,
                $newStock,
                $reason
            )) {
                throw new RuntimeException('Stock was not updated because its history could not be saved.');
            }

            $myCon->commit();
            $success_message = 'Stock updated and history recorded.';
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $myCon->rollback();
            $error_message = $exception->getMessage();
        }
    }
}

// Get Search Term Safely
$searchTerm = isset($_GET['search']) ? $myCon->real_escape_string(trim($_GET['search'])) : '';

// --- PAGINATION & SEARCH CALCULATION LOGIC (mirrors food.php) ---

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

// Fetch total row count for pagination (matches the same filters as the main query)
$countSql = "SELECT COUNT(*) AS total FROM MenuItems m WHERE 1=1 $searchQueryCondition";
$countResult = $myCon->query($countSql);
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = $showAll ? 1 : max(1, (int) ceil($totalRows / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = $showAll ? 0 : ($page - 1) * $limit;
}

// Function to maintain search query + page-size parameters across pagination links
function buildPaginationUrl($pageNum, $search, $limitParam)
{
    $url = "?page=" . $pageNum . "&limit=" . urlencode($limitParam);
    if (!empty($search)) {
        $url .= "&search=" . urlencode($search);
    }
    return $url;
}

// Inventory Query
$minimumStockLevel = 5;
$limitClause = $showAll ? "" : "LIMIT $limit OFFSET $offset";
$sql = "SELECT
    m.MenuItemID,
    m.ItemName,
    m.Image,
    m.Price,
    m.StockQty,
    $minimumStockLevel AS MinimumStock,
    c.CategoryName,

    COALESCE(SUM(
        CASE
            WHEN o.OrderStatus = 'Completed'
            THEN od.Quantity
            ELSE 0
        END
    ),0) AS TotalSold,

    CASE
        WHEN m.StockQty = 0 THEN 'Out of Stock'
        WHEN m.StockQty <= $minimumStockLevel THEN 'Low Stock'
        ELSE 'In Stock'
    END AS StockStatus

FROM MenuItems m

LEFT JOIN Categories c
    ON m.CategoryID = c.CategoryID

LEFT JOIN OrderDetails od
    ON m.MenuItemID = od.MenuItemID

LEFT JOIN Orders o
    ON od.OrderID = o.OrderID

WHERE 1=1 $searchQueryCondition

GROUP BY
    m.MenuItemID,
    m.ItemName,
    m.Image,
    m.Price,
    m.StockQty,
    c.CategoryName

ORDER BY
    m.ItemName ASC $limitClause";

$inventoryResult = mysqli_query($myCon, $sql);

// Add Item (kept for the "Update Stock" style CRUD if used elsewhere on this page)
if (isset($_POST['btnAddFood'])) {
    $ItemName = $myCon->real_escape_string($_POST['ItemName']);
    $CategoryID = (int)$_POST['CategoryID'];
    $Price = (float)$_POST['Price'];
    $StockQty = (int)$_POST['StockQty'];
    $Status = $myCon->real_escape_string($_POST['Status']);
    $Image = $_FILES['Image']['name'];
    $ImageTmp = $_FILES['Image']['tmp_name'];

    if (!empty($Image)) {
        $newImgName = date("Y_m_d_H_i_s") . '_' . rand(10, 100) . '_' . basename($Image);
        $sqlInsert = "INSERT INTO MenuItems (ItemName, CategoryID, Price, StockQty, Status, Image) VALUES ('$ItemName','$CategoryID', '$Price', '$StockQty', '$Status', '$newImgName')";
        move_uploaded_file($ImageTmp, '../images/trash/' . $newImgName);
    } else {
        $sqlInsert = "INSERT INTO MenuItems (ItemName, CategoryID, Price, StockQty, Status, Image) VALUES ('$ItemName', '$CategoryID', '$Price', '$StockQty', '$Status', 'blank.png');";
    }

    if ($myCon->query($sqlInsert) === TRUE) {
        $success_message = "Item Added Successfully";
    } else {
        $error_message = "Item Added Failed";
    }
}

$categoriesList = [];
$catLookupQuery = $myCon->query("SELECT CategoryID, CategoryName FROM Categories ORDER BY CategoryName ASC");
if ($catLookupQuery) {
    while ($cRow = $catLookupQuery->fetch_assoc()) {
        $categoriesList[] = $cRow;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>
    <link rel="icon" type="image/png" href="../img/logo-ang.png">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Bayon&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <style>
        body {
            background: #f5f7fb;
            font-family: 'Segoe UI', sans-serif;
        }

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

        .page-title {
            background: #fff;
            border-radius: 16px;
            padding: 14px 20px;
            text-align: center;
            font-size: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
            overflow: hidden;
        }

        .modal-header {
            background: #1565ED;
            color: #fff;
        }

        .font-bayon {
            font-family: 'Bayon', sans-serif;
        }

        .font-kantumruy {
            font-family: 'Kantumruy Pro', sans-serif;
        }

        /* ── Metric cards ── */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 22px;
        }

        .metric-card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 20px 22px;
            position: relative;
            overflow: hidden;
            transition: border-color .2s;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05);
        }

        .metric-card:hover {
            border-color: var(--border2);
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            border-radius: 14px 14px 0 0;
        }

        .mc-blue::before {
            background: var(--accent);
        }

        .mc-green::before {
            background: var(--green);
        }

        .mc-purple::before {
            background: var(--purple);
        }

        .mc-amber::before {
            background: var(--amber);
        }

        .mc-label {
            font-size: 12px;
            color: var(--text3);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .mc-icon {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mc-icon svg {
            width: 14px;
            height: 14px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .mc-icon-blue {
            background: rgba(79, 142, 247, 0.12);
            color: var(--accent);
        }

        .mc-icon-green {
            background: rgba(34, 201, 122, 0.12);
            color: var(--green);
        }

        .mc-icon-purple {
            background: rgba(155, 127, 250, 0.12);
            color: var(--purple);
        }

        .mc-icon-amber {
            background: rgba(245, 166, 35, 0.12);
            color: var(--amber);
        }

        .mc-value {
            font-size: 26px;
            font-weight: 600;
            color: var(--text);
            letter-spacing: -0.8px;
            margin-bottom: 8px;
            font-variant-numeric: tabular-nums;
        }

        .mc-delta {
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .delta-up {
            color: var(--green);
        }

        .delta-down {
            color: var(--red);
        }

        .mc-delta span {
            color: var(--text3);
        }

        /* ── Table card (matches food/product pages) ── */
        .table-card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            margin-bottom: 22px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05);
        }

        .table-card-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
        }

        .table-card table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-card thead th {
            padding: 12px 22px;
            text-align: center;
            font-size: 11px;
            font-weight: 600;
            color: var(--text3);
            letter-spacing: 0.5px;
            text-transform: uppercase;
            background: var(--bg3);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .table-card tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background .15s;
        }

        .table-card tbody tr:last-child {
            border-bottom: none;
        }

        .table-card tbody tr:hover {
            background: var(--bg3);
        }

        .table-card tbody td {
            padding: 14px 22px;
            font-size: 13px;
            color: #000;
            text-align: center;
            vertical-align: middle;
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

        #pageSizeSelect {
            width: auto;
            min-width: 90px;
        }

        @media(max-width:768px) {
            .page-title {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>
    <?php include 'include/adminsbd.php'; ?>
    <?php include 'include/adminhd.php'; ?>
    <?php include 'include/smooth_refresh.php'; ?>

    <div class="container py-3">

        <!-- Title -->
        <div class="page-title font-bayon">
            គ្រប់គ្រងស្តុកទំនិញ
        </div>

        <?php if ($success_message !== ''): ?>
            <div class="alert alert-success" role="status"><?= htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php elseif ($error_message !== ''): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="metrics-grid">
            <!-- Total Products -->
            <div class="metric-card mc-blue">
                <div class="mc-label font-bayon ">
                    <div class="mc-icon mc-icon-blue">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z" />
                        </svg>
                    </div>
                    ផលិតផលសរុប
                </div>

                <?php
                $sql2 = "SELECT COUNT(*) AS TotalProducts FROM MenuItems";
                $result2 = mysqli_query($myCon, $sql2);
                $rowMetric = mysqli_fetch_assoc($result2);
                ?>

                <div class="mc-value font-kantumruy"><?= $rowMetric['TotalProducts']; ?></div>
                <div class="mc-delta delta-up ">Products</div>
            </div>

            <!-- Total Stock -->
            <div class="metric-card mc-green">
                <div class="mc-label font-bayon">
                    <div class="mc-icon mc-icon-green">
                        <svg viewBox="0 0 24 24">
                            <path d="M3 7L12 3L21 7L12 11L3 7Z" />
                            <path d="M3 12L12 16L21 12" />
                            <path d="M3 17L12 21L21 17" />
                        </svg>
                    </div>
                    ស្តុកសរុប
                </div>

                <?php
                $sql2 = "SELECT IFNULL(SUM(StockQty),0) AS TotalStock FROM MenuItems";
                $result2 = mysqli_query($myCon, $sql2);
                $rowMetric = mysqli_fetch_assoc($result2);
                ?>

                <div class="mc-value font-kantumruy"><?= $rowMetric['TotalStock']; ?></div>
                <div class="mc-delta delta-up">Items</div>
            </div>

            <!-- Low Stock -->
            <div class="metric-card mc-amber">
                <div class="mc-label font-bayon">
                    <div class="mc-icon mc-icon-amber">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 2L2 22h20L12 2z" />
                            <line x1="12" y1="9" x2="12" y2="13" />
                            <circle cx="12" cy="17" r="1" />
                        </svg>
                    </div>
                    ស្តុកជិតអស់
                </div>

                <?php
                $sql2 = "SELECT COUNT(*) AS LowStock FROM MenuItems WHERE StockQty <= $minimumStockLevel AND StockQty > 0";
                $result2 = mysqli_query($myCon, $sql2);
                $rowMetric = mysqli_fetch_assoc($result2);
                ?>

                <div class="mc-value font-kantumruy"><?= $rowMetric['LowStock']; ?></div>
                <div class="mc-delta delta-down">Need Restock</div>
            </div>

            <!-- Out Of Stock -->
            <div class="metric-card mc-purple">
                <div class="mc-label font-bayon">
                    <div class="mc-icon mc-icon-purple">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="8" y1="8" x2="16" y2="16" />
                            <line x1="16" y1="8" x2="8" y2="16" />
                        </svg>
                    </div>
                    អស់ស្តុក
                </div>

                <?php
                $sql2 = "SELECT COUNT(*) AS OutStock FROM MenuItems WHERE StockQty = 0";
                $result2 = mysqli_query($myCon, $sql2);
                $rowMetric = mysqli_fetch_assoc($result2);
                ?>

                <div class="mc-value font-kantumruy"><?= $rowMetric['OutStock']; ?></div>
                <div class="mc-delta delta-down">Unavailable</div>
            </div>

        </div>

        <!-- Inventory Table Card -->
        <div class="table-card">
            <div class="table-card-header font-kantumruy">
                <div class="d-flex flex-column flex-md-row align-items-center gap-3">
                    <div class="align-items-center gap-2 font-kantumruy">
                        <label for="pageSizeSelect" class="mb-0 text-nowrap">Show</label>
                        <div style="max-width:260px; position: relative;">
                            <select id="pageSizeSelect" class="form-select" onchange="changePageSize(this.value)" style="padding-right: 0;">
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
                            <input type="text" id="searchInput" class="form-control" placeholder="ស្វែងរកតាមឈ្មោះ..." value="<?php echo htmlspecialchars($searchTerm); ?>" style="padding-left:35px;">
                        </div>
                    </div>
                </div>
            </div>

            <table>
                <thead class="font-bayon">
                    <tr>
                        <th>No</th>
                        <th>រូបភាព</th>
                        <th>ឈ្មោះទំនិញ</th>
                        <th>ប្រភេទ</th>
                        <th>ស្តុកបច្ចុប្បន្ន</th>
                        <th>បានលក់</th>
                        <th>ស្តុកអប្បបរមា</th>
                        <th>ស្ថានភាព</th>
                        <th>សកម្មភាព</th>
                    </tr>
                </thead>

                <tbody class="font-kantumruy">
                    <?php if (mysqli_num_rows($inventoryResult) > 0) { ?>
                        <?php $i = $showAll ? 1 : $offset + 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($inventoryResult)) { ?>
                            <tr>
                                <!-- No -->
                                <td><span class="rank-badge"><?= $i++; ?></span></td>

                                <!-- Image -->
                                <td>
                                    <?php if (!empty($row['Image']) && $row['Image'] !== 'blank.png') { ?>
                                        <img src="../images/trash/<?= htmlspecialchars($row['Image'], ENT_QUOTES, 'UTF-8'); ?>" width="60" height="60"
                                            style="object-fit:cover;border-radius:8px;">
                                    <?php } else { ?>
                                        <span class="text-muted">No Image</span>
                                    <?php } ?>
                                </td>

                                <!-- Product -->
                                <td class="ItemName"><?= htmlspecialchars($row['ItemName'], ENT_QUOTES, 'UTF-8'); ?></td>

                                <!-- Category -->
                                <td><?= htmlspecialchars($row['CategoryName'], ENT_QUOTES, 'UTF-8'); ?></td>

                                <!-- Current Stock -->
                                <td><?= $row['StockQty']; ?></td>

                                <!-- Total Sold -->
                                <td><?= $row['TotalSold']; ?></td>

                                <!-- Minimum Stock -->
                                <td><?= $row['MinimumStock']; ?></td>

                                <!-- Status -->
                                <td>
                                    <?php if ($row['StockStatus'] == 'In Stock') { ?>
                                        <span class="status st-green">មានស្តុក</span>
                                    <?php } elseif ($row['StockStatus'] == 'Low Stock') { ?>
                                        <span class="status st-amber">ស្តុកជិតអស់</span>
                                    <?php } else { ?>
                                        <span class="status st-red">អស់ស្តុក</span>
                                    <?php } ?>
                                </td>

                                <!-- Action -->
                                <td>
                                    <button
                                        class="btn btn-warning btn-sm stockBtn"
                                        data-id="<?= $row['MenuItemID']; ?>"
                                        data-name="<?= htmlspecialchars($row['ItemName']); ?>"
                                        data-stock="<?= $row['StockQty']; ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#inventory">
                                        <i class="fas fa-box text-white"></i>
                                    </button>

                                    <a href="stockhistory.php?id=<?= $row['MenuItemID']; ?>"
                                        class="btn btn-secondary btn-sm" title="Stock History">
                                        <i class="fas fa-clock-rotate-left"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="9" class="text-center text-danger">មិនមានទិន្នន័យស្តុក</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <div class="align-items-center gap-3" style="margin-left: 10px;">
                <?php if ($totalPages > 0): ?>
                    <div class="d-flex justify-content-start mt-4 mb-3" id="paginationWrapper">
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

    <!-- Modal -->
    <form method="POST" enctype="multipart/form-data" class="modal fade" id="inventory">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="font-bayon text-dark">បច្ចុប្បន្នភាពស្តុក (Update Stock)</h5>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="MenuItemID" id="MenuItemID">
                    <div class="mb-3">
                        <label class="form-label font-bayon">ឈ្មោះទំនិញ</label>
                        <input type="text" name="CategoryName" id="ItemName" class="form-control font-kantumruy" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-bayon">ស្តុកបច្ចុប្បន្ន</label>
                        <input type="number" id="CurrentStock" class="form-control font-kantumruy" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-bayon">ប្រភេទប្រតិបត្តិការ</label>
                        <select name="TransactionType" class="form-select font-kantumruy" required>
                            <option value="Stock In">➕ Stock In</option>
                            <option value="Stock Out">➖ Stock Out</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-bayon">បរិមាណ</label>
                        <input type="number" name="Quantity" class="form-control font-kantumruy" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-bayon">មូលហេតុ</label>
                        <textarea name="Reason" class="form-control font-kantumruy" rows="3"
                            placeholder="ឧ. Purchase, Supplier Delivery, Damage, Kitchen Usage"></textarea>
                    </div>
                </div>

                <div class="modal-footer font-kantumruy">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                    <button class="btn btn-warning" name="btnSavecate">Update</button>
                </div>
            </div>
        </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Change page size -> reload with new limit, reset back to page 1
        function changePageSize(value) {
            var params = new URLSearchParams(window.location.search);
            params.set('limit', value);
            params.set('page', '1');
            window.location.search = params.toString();
        }

        document.querySelectorAll(".stockBtn").forEach(button => {
            button.addEventListener("click", function() {
                document.getElementById("MenuItemID").value = this.dataset.id;
                document.getElementById("ItemName").value = this.dataset.name;
                document.getElementById("CurrentStock").value = this.dataset.stock;
            });
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
                var name = row.querySelector('.ItemName')?.textContent.toLowerCase();
                var matchesSearch = !search || name.includes(search);

                row.style.display = matchesSearch ? '' : 'none';
            });
        }
    </script>
</body>

</html>