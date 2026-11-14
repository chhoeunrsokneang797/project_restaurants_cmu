<?php include 'include/cn.php';

/* ── Single day filter (defaults to today) ────────────────────────── */
$dateFilter = isset($_GET['date']) && $_GET['date'] !== '' ? $_GET['date'] : date('Y-m-d');
$dateEsc = $myCon->real_escape_string($dateFilter);

function esc($conn, $val) {
    return mysqli_real_escape_string($conn, $val);
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
    $searchQueryCondition = " AND CustomerName LIKE '%$searchTerm%'";
}

// Fetch total row count for calculating structural links with search criteria
$countSql = "SELECT COUNT(*) AS total FROM (
    SELECT od.MenuItemID
    FROM orderdetails od
    INNER JOIN history_orders ho ON od.OrderID = ho.OrderID
    WHERE DATE(ho.ArchivedDate) = '$dateEsc'
    AND ho.PaymentStatus = 'Paid'
    GROUP BY od.MenuItemID
) AS counted";
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Sale Report</title>
    <link rel="icon" type="image/png" href="../img/logo-ang.png">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <link href="https://fonts.googleapis.com/css2?family=Bayon&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="assets/dist/css/sale_report.css">
    <style>        
        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #dfe6ef; border-radius: 4px; }
        .print-bar { display: none; }
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

    <div class="container py-3">

        <div class="page-title font-bayon">
            របាយការណ៍លក់ប្រចាំថ្ងៃ
        </div>

        <?php
        $revenueRow = mysqli_fetch_assoc(mysqli_query($myCon,
            "SELECT COALESCE(SUM(GrandTotal),0) AS total, COUNT(*) AS cnt FROM history_orders
             WHERE DATE(ArchivedDate) = '$dateEsc' AND PaymentStatus = 'Paid'"));
        $totalRevenue = (float)$revenueRow['total'];
        $paidOrders   = (int)$revenueRow['cnt'];
        $avgOrderValue = $paidOrders > 0 ? $totalRevenue / $paidOrders : 0;
        ?>

        <div class="pay-wrap">
            <div class="metrics-grid">
                <div class="metric-card mc-green">
                    <div class="mc-label font-bayon">
                        <div class="mc-icon mc-icon-green"><svg viewBox="0 0 24 24">
                                <line x1="12" y1="1" x2="12" y2="23" />
                                <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
                            </svg></div>
                        ចំណូលលក់បានសរុប
                    </div>
                    <div class="mc-value font-kantumruy">$ <?= number_format($totalRevenue, 2); ?></div>
                    <!-- <div class="mc-delta delta-up">▲ 12.4% <span>vs last month</span></div> -->
                </div>
                <div class="metric-card mc-blue">
                    <div class="mc-label font-bayon">
                        <div class="mc-icon mc-icon-blue"><svg viewBox="0 0 24 24">
                                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                                <line x1="3" y1="6" x2="21" y2="6" />
                                <path d="M16 10a4 4 0 01-8 0" />
                            </svg></div>
                        កម្មង់ដែលទូទាត់រួច
                    </div>
                    <div class="mc-value font-kantumruy"><?= $paidOrders; ?></div>
                    <!-- <div class="mc-delta delta-up">▲ 12.4% <span>vs last month</span></div> -->
                </div>
                <div class="metric-card mc-purple">
                    <div class="mc-label font-bayon">
                        <div class="mc-icon mc-icon-purple"><svg viewBox="0 0 24 24">
                                <line x1="12" y1="1" x2="12" y2="23" />
                                <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
                            </svg></div>
                        តម្លៃមធ្យមក្នុងមួយកម្មង់
                    </div>
                    <div class="mc-value font-kantumruy">$ <?= number_format($avgOrderValue, 2); ?></div>
                    <!-- <div class="mc-delta delta-up">▲ 12.4% <span>vs last month</span></div> -->
                </div>
            </div>
        </div>

        <!-- TABLE -->
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
                    
                    <div class="align-items-center gap-2 font-kantumruy">
                        <label class="form-label mb-1" style="font-size:12px;color:#6b7280;">ជ្រើសរើសថ្ងៃ</label>
                        <input type="date" id="dateFilter" class="form-control" style="max-width: 200px;" value="<?= htmlspecialchars($dateFilter); ?>" onchange="filterByDate()">
                    </div>

                    <div class="align-items-center gap-2 font-kantumruy mt-4">
                        <button type="button" onclick="printSaleReport()" class="btn btn-primary">
                            <i class="fas fa-print"></i> ទាញយករបាយការណ៍លក់
                        </button>
                    </div>                    
                </div>
                <!-- <h5 class="section-title font-bayon">ម្ហូបលក់ប្រចាំថ្ងៃ</h5> -->
            </div> 
                    

            <table>
                <thead>
                    <tr class="font-bayon">
                        <th class="text-center">ចំណាត់ថ្នាក់</th>                        
                        <th class="text-center">ឈ្មោះម្ហូប</th>
                        <th class="text-center">ចំនួនលក់បាន</th>
                        <th class="text-center">ចំណូល</th>
                    </tr>
                </thead>
                <tbody class="font-kantumruy text-center">
                    <?php
                    $limitClause = $showAll ? "" : "LIMIT $limit OFFSET $offset";
                    $topItemsSql = "SELECT mi.ItemName AS name,
                                            SUM(od.Quantity) AS qty,
                                            SUM(od.SubTotal) AS revenue
                                        FROM orderdetails od
                                        INNER JOIN history_orders ho ON od.OrderID = ho.OrderID
                                        LEFT JOIN menuitems mi ON od.MenuItemID = mi.MenuItemID
                                        WHERE DATE(ho.ArchivedDate) = '$dateEsc'
                                        AND ho.PaymentStatus = 'Paid'
                                        GROUP BY od.MenuItemID, mi.ItemName
                                        ORDER BY qty DESC $limitClause";
                    $topItemsRes = mysqli_query($myCon, $topItemsSql);
                    $hasTopItems = false;
                    $rank = 1;
                    if ($topItemsRes) {
                        while ($t = mysqli_fetch_assoc($topItemsRes)) {
                            $hasTopItems = true;
                    ?>
                    <tr>
                        <td><span class="rank-badge"><?= $rank; ?></span></td>
                        <td><?= $t['name'] ? htmlspecialchars($t['name']) : 'N/A'; ?></td>
                        <td><?= (int)$t['qty']; ?> PCS </td>
                        <td style="font-weight:600;color:#10b981;">$ <?= number_format($t['revenue'], 2); ?></td>
                    </tr>
                    <?php
                            $rank++;
                        }
                    }
                    if (!$hasTopItems) {
                    ?>
                        <tr class="text-center">
                            <td colspan="4" class="text-muted​ text-danger py-4">មិនមានទិន្នន័យសម្រាប់ថ្ងៃនេះទេ</td>
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

    <script>
        // Change page size -> reload with new limit, reset back to page 1
        function changePageSize(value) {
            var params = new URLSearchParams(window.location.search);
            params.set('limit', value);
            params.set('page', '1');
            window.location.search = params.toString();
        }

        function filterByDate() {
            var date = document.getElementById('dateFilter').value;
            window.location.href = 'sale_report.php' + (date ? '?date=' + encodeURIComponent(date) : '');
        }

        // Print report
        function printSaleReport() {
            var date = <?= json_encode($dateFilter); ?>;

            var old = document.getElementById('printFrame');
            if (old) old.remove();

            var iframe = document.createElement('iframe');
            iframe.id = 'printFrame';
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = '0';
            iframe.src = '../admin/get_data/print_sale_report.php?date=' + encodeURIComponent(date);

            iframe.onload = function () {
                try {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                } catch (e) {
                    console.error('Print failed:', e);
                }
            };

            document.body.appendChild(iframe);
        }

        function printReportViaIframe(url) {
            var old = document.getElementById('printFrame');
            if (old) old.remove();
            var iframe = document.createElement('iframe');
            iframe.id = 'printFrame';
            Object.assign(iframe.style, { position: 'fixed', right: '0', bottom: '0', width: '0', height: '0', border: '0' });
            iframe.src = url;
            iframe.onload = function () {
                try { iframe.contentWindow.focus(); iframe.contentWindow.print(); }
                catch (e) { console.error('Print failed:', e); }
            };
            document.body.appendChild(iframe);
        }
    </script>

</body>

</html>