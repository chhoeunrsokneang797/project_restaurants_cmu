<?php
include 'include/cn.php';
require_once 'include/stock_history.php';

ensureStockHistoryTable($myCon);

$menuItemId = isset($_GET['id']) && ctype_digit((string)$_GET['id']) ? (int)$_GET['id'] : 0;
$dateFilter = isset($_GET['date']) ? trim((string)$_GET['date']) : '';
if ($dateFilter !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFilter)) {
    $dateFilter = '';
}

$where = [];
if ($menuItemId > 0) {
    $where[] = 'MenuItemID = ?';
}
if ($dateFilter !== '') {
    $where[] = 'DATE(CreatedAt) = ?';
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$page = isset($_GET['page']) && ctype_digit((string)$_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$pageSize = 50;

$statsStatement = $myCon->prepare(
    "SELECT COUNT(*) AS TotalChanges,
            COALESCE(SUM(CASE WHEN TransactionType = 'Stock In' THEN Quantity ELSE 0 END), 0) AS TotalIn,
            COALESCE(SUM(CASE WHEN TransactionType = 'Stock Out' THEN Quantity ELSE 0 END), 0) AS TotalOut
     FROM StockHistory $whereSql"
);
if (!$statsStatement) {
    throw new RuntimeException('Unable to load stock history summary: ' . $myCon->error);
}
if ($menuItemId > 0 && $dateFilter !== '') {
    $statsStatement->bind_param('is', $menuItemId, $dateFilter);
} elseif ($menuItemId > 0) {
    $statsStatement->bind_param('i', $menuItemId);
} elseif ($dateFilter !== '') {
    $statsStatement->bind_param('s', $dateFilter);
}
$statsStatement->execute();
$stats = $statsStatement->get_result()->fetch_assoc();
$statsStatement->close();
$totalRows = (int)$stats['TotalChanges'];
$totalPages = max(1, (int)ceil($totalRows / $pageSize));
$page = min($page, $totalPages);
$offset = ($page - 1) * $pageSize;

$historyStatement = $myCon->prepare(
    "SELECT HistoryID, MenuItemID, ItemName, TransactionType, Quantity, PreviousStock, NewStock, Reason, CreatedAt
     FROM StockHistory $whereSql
     ORDER BY CreatedAt DESC, HistoryID DESC
     LIMIT ? OFFSET ?"
);
if (!$historyStatement) {
    throw new RuntimeException('Unable to load stock history: ' . $myCon->error);
}
if ($menuItemId > 0 && $dateFilter !== '') {
    $historyStatement->bind_param('isii', $menuItemId, $dateFilter, $pageSize, $offset);
} elseif ($menuItemId > 0) {
    $historyStatement->bind_param('iii', $menuItemId, $pageSize, $offset);
} elseif ($dateFilter !== '') {
    $historyStatement->bind_param('sii', $dateFilter, $pageSize, $offset);
} else {
    $historyStatement->bind_param('ii', $pageSize, $offset);
}
$historyStatement->execute();
$historyResult = $historyStatement->get_result();

$itemName = '';
if ($menuItemId > 0) {
    $itemStatement = $myCon->prepare('SELECT ItemName FROM MenuItems WHERE MenuItemID = ?');
    if (!$itemStatement) {
        throw new RuntimeException('Unable to load product details: ' . $myCon->error);
    }
    $itemStatement->bind_param('i', $menuItemId);
    $itemStatement->execute();
    $item = $itemStatement->get_result()->fetch_assoc();
    $itemName = $item ? $item['ItemName'] : '';
    $itemStatement->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock History</title>
    <link rel="icon" type="image/png" href="../img/logo-ang.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background: #f5f7fb;
            font-family: 'Segoe UI', sans-serif;
        }

        .history-content {
            padding: 24px;
        }

        .page-heading,
        .history-card,
        .stat-card {
            background: #fff;
            border: 1px solid #e8edf5;
            border-radius: 16px;
            box-shadow: 0 5px 22px rgba(15, 23, 42, .05);
        }

        .page-heading {
            padding: 20px 24px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .page-heading h1 {
            font-size: 24px;
            margin: 0;
            color: #172033;
        }

        .page-heading p {
            margin: 5px 0 0;
            color: #718096;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .stat-card {
            padding: 18px 20px;
        }

        .stat-card span {
            display: block;
            color: #718096;
            font-size: 13px;
        }

        .stat-card strong {
            display: block;
            margin-top: 5px;
            font-size: 25px;
            color: #172033;
        }

        .history-card {
            overflow: hidden;
        }

        .history-toolbar {
            padding: 16px 20px;
            border-bottom: 1px solid #e8edf5;
        }

        .table-responsive {
            max-height: 65vh;
        }

        .table {
            margin: 0;
        }

        .table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #f8fafc;
            color: #667085;
            font-size: 12px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        .movement-badge {
            border-radius: 20px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .movement-in {
            background: #e8f8ef;
            color: #16834b;
        }

        .movement-out {
            background: #fff0ed;
            color: #c2412d;
        }

        .movement-adjustment {
            background: #eef2ff;
            color: #4f46a5;
        }

        @media (max-width: 700px) {
            .history-content {
                padding: 16px;
            }

            .page-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }
        }
    </style>
</head>

<body>
    <?php include 'include/adminsbd.php'; ?>
    <?php include 'include/adminhd.php'; ?>

    <main class="history-content">
        <section class="page-heading">
            <div>
                <h1><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Stock History</h1>
                <p><?= $itemName !== '' ? 'Product: ' . htmlspecialchars($itemName, ENT_QUOTES, 'UTF-8') : 'Review stock movements for all products'; ?></p>
            </div>
            <a class="btn btn-outline-secondary" href="inventory.php">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Inventory
            </a>
        </section>

        <section class="stats-grid" aria-label="Stock history summary">
            <div class="stat-card">
                <span>Recorded changes</span>
                <strong><?= (int)$stats['TotalChanges']; ?></strong>
            </div>
            <div class="stat-card">
                <span>Stock added</span>
                <strong class="text-success">+<?= (int)$stats['TotalIn']; ?></strong>
            </div>
            <div class="stat-card">
                <span>Stock removed</span>
                <strong class="text-danger">−<?= (int)$stats['TotalOut']; ?></strong>
            </div>
        </section>

        <section class="history-card">
            <form class="history-toolbar d-flex flex-wrap gap-2 align-items-center" method="get">
                <?php if ($menuItemId > 0): ?>
                    <input type="hidden" name="id" value="<?= $menuItemId; ?>">
                <?php endif; ?>
                <label for="dateFilter" class="form-label mb-0">Date</label>
                <input class="form-control" style="max-width: 190px" type="date" id="dateFilter" name="date" value="<?= htmlspecialchars($dateFilter, ENT_QUOTES, 'UTF-8'); ?>">
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-filter me-1"></i>Filter</button>
                <?php if ($dateFilter !== ''): ?>
                    <a class="btn btn-outline-secondary" href="<?= $menuItemId > 0 ? '?id=' . $menuItemId : 'stockhistory.php'; ?>">Clear</a>
                <?php endif; ?>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Date &amp; time</th>
                            <th>Product</th>
                            <th>Movement</th>
                            <th>Quantity</th>
                            <th>Stock before</th>
                            <th>Stock after</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($historyResult->num_rows > 0): ?>
                            <?php while ($row = $historyResult->fetch_assoc()): ?>
                                <?php
                                $movementClass = $row['TransactionType'] === 'Stock In'
                                    ? 'movement-in'
                                    : ($row['TransactionType'] === 'Stock Out' ? 'movement-out' : 'movement-adjustment');
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($row['CreatedAt'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($row['ItemName'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><span class="movement-badge <?= $movementClass; ?>"><?= htmlspecialchars($row['TransactionType'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <td><?= (int)$row['Quantity']; ?></td>
                                    <td><?= (int)$row['PreviousStock']; ?></td>
                                    <td><?= (int)$row['NewStock']; ?></td>
                                    <td><?= htmlspecialchars($row['Reason'] !== '' ? $row['Reason'] : '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fa-regular fa-folder-open fs-3 d-block mb-2"></i>
                                    No stock changes have been recorded for this selection.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($totalPages > 1): ?>
                <nav class="d-flex justify-content-between align-items-center gap-3 p-3" aria-label="Stock history pages">
                    <?php
                    $pageParams = [];
                    if ($menuItemId > 0) {
                        $pageParams['id'] = $menuItemId;
                    }
                    if ($dateFilter !== '') {
                        $pageParams['date'] = $dateFilter;
                    }
                    $previousParams = $pageParams;
                    $previousParams['page'] = max(1, $page - 1);
                    $nextParams = $pageParams;
                    $nextParams['page'] = min($totalPages, $page + 1);
                    ?>
                    <a class="btn btn-outline-secondary <?= $page <= 1 ? 'disabled' : ''; ?>" href="?<?= htmlspecialchars(http_build_query($previousParams), ENT_QUOTES, 'UTF-8'); ?>">Previous</a>
                    <span class="text-muted">Page <?= $page; ?> of <?= $totalPages; ?></span>
                    <a class="btn btn-outline-secondary <?= $page >= $totalPages ? 'disabled' : ''; ?>" href="?<?= htmlspecialchars(http_build_query($nextParams), ENT_QUOTES, 'UTF-8'); ?>">Next</a>
                </nav>
            <?php endif; ?>
        </section>
    </main>
</body>

</html>
<?php $historyStatement->close(); ?>
