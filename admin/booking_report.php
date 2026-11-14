<?php include 'include/cn.php';
$dateFilter = isset($_GET['date']) && $_GET['date'] !== '' ? $_GET['date'] : date('Y-m-d');

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
$countSql = "SELECT COUNT(*) AS total FROM history_orders 
             WHERE DATE(ArchivedDate) = '$dateFilter' 
             AND PaymentStatus = 'Paid' AND OrderType = 'Dine In'";
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
    <title>Daily Booking Report</title>
    <link rel="icon" type="image/png" href="../img/logo-ang.png">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <link href="https://fonts.googleapis.com/css2?family=Bayon&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="assets/dist/css/booking_report.css">
</head>

<style>
    .status { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 20px; font-size: 0.9rem; font-weight: 600; }
    .status::before { content: ''; width: 5px; height: 5px; border-radius: 50%; }
    .st-green { background: rgba(34,201,122,0.12); color: var(--green); }
    .st-green::before { background: var(--green); }
    .st-amber { background: rgba(245,166,35,0.12); color: var(--amber); }
    .st-amber::before { background: var(--amber); }
    .st-red { background: rgba(242,92,92,0.12); color: var(--red); }
    .st-red::before { background: var(--red); }
    .st-blue { background: rgba(79,142,247,0.12); color: var(--accent); }
    .st-blue::before { background: var(--accent); } 

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

    @media print {
        body { padding: 0; }
        .print-bar { display: none; }
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

<body>
    <?php include 'include/adminsbd.php'; ?>
    <?php include 'include/adminhd.php'; ?>
    <?php include 'include/smooth_refresh.php'; ?>

    <div class="container py-3">

        <div class="page-title font-bayon">
            របាយការណ៍ការកក់តុ
        </div>

        <!-- Stats (always scoped to $dateFilter, which defaults to today) -->
        <?php
        $total  = mysqli_fetch_assoc(mysqli_query($myCon, "SELECT COUNT(*) as total FROM history_orders WHERE DATE(ArchivedDate) = '$dateFilter' AND PaymentStatus = 'Paid' AND OrderType = 'Dine In'"));
        $accept = mysqli_fetch_assoc(mysqli_query($myCon, "SELECT COUNT(*) as total FROM history_orders WHERE DATE(ArchivedDate) = '$dateFilter' AND PaymentStatus = 'Paid' AND OrderType = 'Dine In' AND (OrderStatus = 'Completed' OR OrderStatus = '' OR OrderStatus IS NULL)"));
        $reject = mysqli_fetch_assoc(mysqli_query($myCon, "SELECT COUNT(*) as total FROM history_orders WHERE DATE(ArchivedDate) = '$dateFilter' AND PaymentStatus = 'Paid' AND OrderType = 'Dine In' AND OrderStatus = 'Cancelled'"));
        ?>

        <div class="pay-wrap">
            <div class="metrics-grid">
                <div class="metric-card mc-blue">
                    <div class="mc-label font-bayon">
                        <div class="mc-icon mc-icon-blue"><svg viewBox="0 0 24 24">
                                <line x1="12" y1="1" x2="12" y2="23" />
                                <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
                            </svg></div>
                        ការកក់សរុប
                    </div>
                    <div class="mc-value font-kantumruy"><?= $total['total']; ?></div>
                </div>
                <div class="metric-card mc-green">
                    <div class="mc-label font-bayon">
                        <div class="mc-icon mc-icon-green"><svg viewBox="0 0 24 24">
                                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                                <line x1="3" y1="6" x2="21" y2="6" />
                                <path d="M16 10a4 4 0 01-8 0" />
                            </svg></div>
                        ការកក់ជោគជ័យ
                    </div>
                    <div class="mc-value font-kantumruy"><?= $accept['total']; ?></div>
                </div>
                <div class="metric-card mc-red">
                    <div class="mc-label font-bayon">
                        <div class="mc-icon mc-icon-red">
                            <i class="fa-solid fa-x"></i>
                        </div>
                        ការកក់បរាជ័យ
                    </div>
                    <div class="mc-value font-kantumruy"><?= $reject['total']; ?></div>
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
                            <select id="pageSizeSelect" class="form-select" onchange="changePageSize(this.value)" style="padding-left: 35px;">
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
                        <button type="button" onclick="printBookingReport()" class="btn btn-primary">
                            <i class="fas fa-print"></i> ទាញយករបាយការណ៍ការកក់តុ
                        </button>
                    </div>                    
                </div>
            </div> 
                    
            <table>
                <thead>
                    <tr class="font-bayon">
                        <th class="text-center">លេខ</th>                        
                        <th class="text-center">ឈ្មោះ</th>
                        <th class="text-center">លេខទូរស័ព្ទ</th>
                        <th class="text-center">ប្រភេទការកម្មង់</th>
                        <th class="text-center">ស្ថានភាពការកក់</th>
                        <th class="text-center">ការទូទាត់</th>
                        <th class="text-center">កាលបរិច្ឆេទ</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="font-kantumruy text-center">
                    <?php
                    $limitClause = $showAll ? "" : "LIMIT $limit OFFSET $offset";
                    $sql = "SELECT * FROM history_orders WHERE DATE(ArchivedDate) = '$dateFilter' 
                            AND PaymentStatus = 'Paid' AND OrderType = 'Dine In' ORDER BY ArchivedDate ASC $limitClause";
                    $result = mysqli_query($myCon, $sql);
                    $i = 1;
                    $historyRows = [];
                    if (mysqli_num_rows($result) === 0) {
                    ?>
                        <tr>
                            <td colspan="8" class="text-center py-4  text-danger">
                                មិនមានទិន្នន័យកក់តុសម្រាប់ថ្ងៃនេះទេ
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
                        <tr>
                            <td><span class="rank-badge"><?= $i; $i++; ?></span></td>
                            <td><?= htmlspecialchars($row['customer_name']); ?></td>
                            <td><?= htmlspecialchars($row['customer_phone']); ?></td>
                            <td><?= htmlspecialchars($row['OrderType']); ?></td>
                            <td>
                                <?php
                                $orderStatus = trim((string)$row['OrderStatus']);
                                if ($orderStatus === '') {
                                    $orderStatus = 'Completed';
                                }
                                if ($orderStatus == 'Completed') {
                                    echo '<span class="status st-green">' . htmlspecialchars($orderStatus) . '</span>';
                                } elseif ($orderStatus == 'Cancelled') {
                                    echo '<span class="status st-red">' . htmlspecialchars($orderStatus) . '</span>';
                                } else {
                                    echo '<span class="status st-amber">' . htmlspecialchars($orderStatus) . '</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                $paymentStatus = $row['PaymentStatus'];
                                if ($paymentStatus == 'Paid') {
                                    echo '<span class="status st-green" style="font-size: 0.9rem;">ទូទាត់រួចរាល់</span>';
                                } elseif ($paymentStatus == 'Unpaid') {
                                    echo '<span class="status st-amber" style="font-size: 0.9rem;" >មិនទាន់ទូទាត់</span>';
                                }
                                ?>
                            </td>
                            <td><?= date('d/M/Y H:i A', strtotime($row['ArchivedDate'])); ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="openViewModal(<?= (int)$row['OrderID']; ?>)" data-bs-toggle="modal" data-bs-target="#viewModal">
                                    View
                                </button>
                            </td>
                        </tr>
                    <?php } } ?>                  
                </tbody>
            </table>

            <div class="align-items-center gap-3" style="margin-left: 10px;">
                <?php if ($totalPages > 1): ?>
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
            
            <!-- View Detail Modal (Bootstrap) -->
            <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-md">
                    <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden">
                        <div class="modal-header" style="background:#2563eb;color:#fff">
                            <h5 class="modal-title font-kantumruy" id="viewModalTitle">ព័ត៌មានលម្អិតនៃការបញ្ជាទិញ</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="viewModalBody" style="padding:20px"></div>
                        <div class="modal-footer" id="viewModalFooter"></div>
                    </div>
                </div>
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

        var orders = <?= json_encode($historyRows); ?>;

        var viewModalBS = new bootstrap.Modal(document.getElementById('viewModal'));

        // Filter date
        function filterByDate() {
            var date = document.getElementById('dateFilter').value;
            if (date) {
                window.location.href = 'booking_report.php?date=' + date;
            } else {
                window.location.href = 'booking_report.php';
            }
        }

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
                '<button class="btn btn-secondary btn-sm font-kantumruy" data-bs-dismiss="modal">Close</button>';

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

        // Print report
        function printBookingReport() {
            var date = <?= json_encode($dateFilter); ?>;

            // Remove any leftover print iframe from a previous click
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
            iframe.src = '../admin/get_data/print_booking_report.php?date=' + encodeURIComponent(date);

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
    </script>

</body>

</html>