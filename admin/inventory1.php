<?php
include 'include/cn.php';

// ============================================================
// SEARCH & PAGINATION SETUP (consolidated — was previously split
// into two conflicting blocks further down the file)
// ============================================================
$searchKeyword = isset($_GET['search']) ? $myCon->real_escape_string(trim($_GET['search'])) : '';

// Page size: 10 / 25 / 50 / all — default 10
$allowedLimits = ['10', '25', '50', 'all'];
$limitParam = isset($_GET['limit']) ? (string)$_GET['limit'] : '10';
if (!in_array($limitParam, $allowedLimits, true)) {
    $limitParam = '10';
}
$showAll = ($limitParam === 'all');
$limit   = $showAll ? 0 : (int)$limitParam;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

// Build selective condition for database query string adjustments
$whereClause = "";
if (!empty($searchKeyword)) {
    $whereClause = " WHERE IngredientName LIKE '%$searchKeyword%' ";
}

// Dynamic total record matching filters calculation
$totalQuery = $myCon->query("SELECT COUNT(*) AS total FROM inventory" . $whereClause);
$totalRow   = $totalQuery->fetch_assoc();
$totalItems = $totalRow['total'];
$totalPages = $showAll ? 1 : ceil($totalItems / $limit);

if (!$showAll && $page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
}
$offset = $showAll ? 0 : ($page - 1) * $limit;

// Preserve search context inside pagination links
$searchQueryParam = !empty($searchKeyword) ? '&search=' . urlencode($searchKeyword) : '';

// Function to maintain search query + page-size parameters across pagination links
function buildPaginationUrl($pageNum, $search, $limitParam)
{
    $url = "?page=" . $pageNum . "&limit=" . urlencode($limitParam);
    if (!empty($search)) {
        $url .= "&search=" . urlencode($search);
    }
    return $url;
}

// Add new inventory item
if (isset($_POST['btnAddInventory'])) {
    $IngredientName = $myCon->real_escape_string($_POST['IngredientName']);
    $Quantity       = $myCon->real_escape_string($_POST['Quantity']);
    $Unit           = !empty($_POST['Unit']) ? "'" . $myCon->real_escape_string($_POST['Unit']) . "'" : "NULL";
    $MinimumStock   = $myCon->real_escape_string($_POST['MinimumStock']);
    $LastUpdated    = date("Y-m-d H:i:s");

    $sqlInsert = "INSERT INTO inventory (IngredientName, Quantity, Unit, MinimumStock, LastUpdated) 
                  VALUES ('$IngredientName', '$Quantity', $Unit, '$MinimumStock', '$LastUpdated')";

    if ($myCon->query($sqlInsert) === TRUE) {
        $success_message = "Add Inventory Item Success!";
    } else {
        $error_message = "Error! Cannot insert data: " . $myCon->error;
    }
}

// Update inventory item
if (isset($_POST['btnUpdate']) && isset($_POST['inventoryID'])) {
    $inventoryID    = (int)$_POST['inventoryID'];
    $IngredientName = $myCon->real_escape_string($_POST['IngredientName']);
    $Quantity       = $myCon->real_escape_string($_POST['Quantity']);
    $Unit           = !empty($_POST['Unit']) ? "'" . $myCon->real_escape_string($_POST['Unit']) . "'" : "NULL";
    $MinimumStock   = $myCon->real_escape_string($_POST['MinimumStock']);
    $LastUpdated    = date("Y-m-d H:i:s");

    $sqlUpdate = "UPDATE inventory SET 
                  IngredientName='$IngredientName', 
                  Quantity='$Quantity', 
                  Unit=$Unit, 
                  MinimumStock='$MinimumStock', 
                  LastUpdated='$LastUpdated' 
                  WHERE InventoryID=$inventoryID";

    if ($myCon->query($sqlUpdate) === TRUE) {
        $success_message = "Update Inventory Success!";
    } else {
        $error_message = "Error! Cannot update data: " . $myCon->error;
    }
}

// Delete inventory item
if (isset($_POST['btnDelete']) && isset($_POST['delID'])) {
    $delID = (int)$_POST['delID'];

    $sqlDelete = "DELETE FROM inventory WHERE InventoryID=$delID";
    if ($myCon->query($sqlDelete) === TRUE) {
        $success_message = "Delete Inventory Item Success!";
    } else {
        $error_message = "Error! Cannot delete data.";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Bayon&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="assets/dist/css/inventory.css">
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

        /* ── Table ── */
        .table-card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            margin-bottom: 22px;
        }

        .table-card,
        .toast-wrap {
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05);
        }

        .table-card-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-card table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-card thead th {
            padding: 12px 22px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: var(--text3);
            letter-spacing: 0.5px;
            text-transform: uppercase;
            background: var(--bg4);
            border-bottom: 1px solid var(--border);
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
        }

        .td-main {
            color: var(--text);
            font-weight: 500;
        }

        .td-mono {
            font-family: var(--mono);
            font-size: 12px;
        }

        .table-card tbody tr.low-stock-row {
            background: rgba(220, 211, 38, 0.08);
        }

        .table-card tbody tr.low-stock-row:hover {
            background: rgba(217, 220, 38, 0.36);
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
    </style>
</head>

<body>
    <?php include 'include/adminsbd.php'; ?>
    <?php include 'include/adminhd.php'; ?>
    <?php include 'include/smooth_refresh.php'; ?>

    <div class="container py-3">
        <div class="page-title font-bayon">បញ្ជីសារពើភ័ណ្ឌ (Inventory)</div>

        <div class="d-flex justify-content-end mb-4">
            <button class="font-bayon create-btn btn btn-primary" data-bs-toggle="modal" data-bs-target="#inventoryModal">បញ្ចូលទំនិញបន្ថែម</button>
        </div>

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
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Ingredient Name</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-center">Unit</th>
                        <th class="text-center">Min Stock</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Last Updated</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php
                    // Fixed: skip the LIMIT/OFFSET clause entirely when "All" is selected.
                    // Previously $limit was 0 in that case, which produced "LIMIT 0"
                    // in the SQL and returned zero rows.
                    $sql = "SELECT * FROM inventory" . $whereClause . " ORDER BY InventoryID ASC";
                    if (!$showAll) {
                        $sql .= " LIMIT $limit OFFSET $offset";
                    }
                    $qr = $myCon->query($sql);
                    $i = 1;

                    if ($qr && $qr->num_rows > 0) {
                        while ($row = $qr->fetch_assoc()) {
                            $isLowStock = ($row["Quantity"] <= $row["MinimumStock"]);
                            $statusText = $isLowStock ? "ស្តុកសល់តិច" : "មានក្នុងស្តុក";
                            $badgeClass = $isLowStock ? "status st-amber" : "status st-green";

                            $escapedName = addslashes(htmlspecialchars($row['IngredientName'], ENT_QUOTES, 'UTF-8'));
                            $escapedUnit = $row['Unit'] ? addslashes(htmlspecialchars($row['Unit'], ENT_QUOTES, 'UTF-8')) : '';
                    ?>
                            <tr class="text-center font-kantumruy <?php echo $isLowStock ? 'low-stock-row' : ''; ?>">
                                <td><span class="rank-badge"><?php echo $i++; ?></span></td>
                                <td class="item-name"><strong><?php echo htmlspecialchars($row['IngredientName'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td><?php echo number_format($row['Quantity']); ?></td>
                                <td class="text-muted"><?php echo $row['Unit'] ? htmlspecialchars($row['Unit'], ENT_QUOTES, 'UTF-8') : 'pcs'; ?></td>
                                <td><?php echo number_format($row['MinimumStock']); ?></td>
                                <td>
                                    <span class="status <?php echo $badgeClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?php echo $row['LastUpdated'] ? date('Y-m-d h:i A', strtotime($row['LastUpdated'])) : 'N/A'; ?></td>
                                <td>
                                    <button type="button"
                                        class="btn btn-sm btn-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editInventoryModal"
                                        onclick="fillEditForm(<?php echo (int)$row['InventoryID']; ?>, '<?php echo $escapedName; ?>', '<?php echo (float)$row['Quantity']; ?>', '<?php echo $escapedUnit; ?>', '<?php echo (float)$row['MinimumStock']; ?>')">
                                        <i class="fas fa-edit text-white"></i>
                                    </button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete <?php echo $escapedName; ?>?');">
                                        <input type="hidden" name="delID" value="<?php echo (int)$row['InventoryID']; ?>">
                                        <button type="submit" name="btnDelete" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="8" class="text-center py-3 text-muted">No inventory records located.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>

            <div class="align-items-center gap-3" style="margin-left: 10px;">
                <?php if ($totalItems > 0): ?>
                    <div class="d-flex justify-content-start mt-4" id="paginationWrapper">
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-md align-items-center gap-1">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link rounded-3 border-0 text-secondary bg-light" href="<?php echo ($page <= 1) ? '#' : buildPaginationUrl($page - 1, $searchKeyword, $limitParam); ?>">
                                        <i class="fas fa-chevron-left small"></i>
                                    </a>
                                </li>

                                <?php
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $page + 2);

                                if ($startPage > 1) {
                                    echo '<li class="page-item"><a class="page-link rounded-3 border-0 text-dark bg-light" href="' . buildPaginationUrl(1, $searchKeyword, $limitParam) . '">1</a></li>';
                                    if ($startPage > 2) echo '<li class="page-item disabled"><span class="page-link border-0 bg-transparent text-muted">...</span></li>';
                                }

                                for ($p = $startPage; $p <= $endPage; $p++):
                                    $activeClass = ($p == $page) ? 'border border-primary bg-white text-primary fw-bold' : 'border-0 bg-light text-dark';
                                ?>
                                    <li class="page-item">
                                        <a class="page-link rounded-3 <?php echo $activeClass; ?>" href="<?php echo buildPaginationUrl($p, $searchKeyword, $limitParam); ?>">
                                            <?php echo $p; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php
                                if ($endPage < $totalPages) {
                                    if ($endPage < $totalPages - 1) {
                                        echo '<li class="page-item disabled"><span class="page-link border-0 bg-transparent text-muted">...</span></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link rounded-3 border-0 text-dark bg-light" href="' . buildPaginationUrl($totalPages, $searchKeyword, $limitParam) . '">' . $totalPages . '</a></li>';
                                }
                                ?>

                                <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                    <a class="page-link rounded-3 border-0 text-secondary bg-light" href="<?php echo ($page >= $totalPages) ? '#' : buildPaginationUrl($page + 1, $searchKeyword, $limitParam); ?>">
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

    <form method="POST" class="modal fade" id="inventoryModal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="font-kantumruy mb-0">Add Inventory Item</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-bayon">ឈ្មោះទំនិញ</label>
                        <input type="text" name="IngredientName" class="form-control font-kantumruy" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-bayon">ចំនួន</label>
                        <input type="number" step="0.01" name="Quantity" class="form-control font-kantumruy" placeholder="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Unit (e.g., Kg, Box, Pcs)</label>
                        <input type="text" name="Unit" class="form-control font-kantumruy" placeholder="Leave empty for generic pcs">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Minimum Stock Limit</label>
                        <input type="number" step="0.01" name="MinimumStock" class="form-control font-kantumruy" value="0.00" required>
                    </div>
                </div>
                <div class="modal-footer font-kantumruy">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                    <button type="submit" class="btn btn-primary" name="btnAddInventory">Save</button>
                </div>
            </div>
        </div>
    </form>

    <form method="POST" class="modal fade" id="editInventoryModal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="font-kantumruy mb-0">Edit Inventory Item</h5>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="inventoryID" id="editInventoryID">
                    <div class="mb-3">
                        <label class="form-label font-bayon">ឈ្មោះទំនិញ</label>
                        <input type="text" name="IngredientName" id="editIngredientName" class="form-control font-kantumruy" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-bayon">ចំនួន</label>
                        <input type="number" step="0.01" name="Quantity" id="editQuantity" class="form-control font-kantumruy" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Unit (e.g., Kg, Box, Pcs)</label>
                        <input type="text" name="Unit" id="editUnit" class="form-control font-kantumruy">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Minimum Stock Limit</label>
                        <input type="number" step="0.01" name="MinimumStock" id="editMinimumStock" class="form-control font-kantumruy" required>
                    </div>
                </div>
                <div class="modal-footer font-kantumruy">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning" name="btnUpdate">Update</button>
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

        function fillEditForm(id, name, qty, unit, minStock) {
            document.getElementById('editInventoryID').value = id;
            document.getElementById('editIngredientName').value = name;
            document.getElementById('editQuantity').value = qty;
            document.getElementById('editUnit').value = unit;
            document.getElementById('editMinimumStock').value = minStock;
        }

        function applyFilters() {
            var search = document.getElementById('searchInput').value.toLowerCase();

            document.querySelectorAll('#tableBody tr').forEach(function(row) {
                var name = row.querySelector('.item-name')?.textContent.toLowerCase();
                var matchesSearch = !search || name.includes(search);

                row.style.display = matchesSearch ? '' : 'none';
            });
        }

        // Automatic Toast dismissals
        setTimeout(() => {
            document.querySelectorAll('.smooth-alert').forEach((alert, index) => {
                setTimeout(() => {
                    alert.classList.add('hide');
                    setTimeout(() => alert.remove(), 400);
                }, index * 150);
            });
        }, 1500);
    </script>
</body>

</html>