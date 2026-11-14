<?php include 'include/cn.php';

/* ── Date filter (defaults to today, same pattern as sale_report.php) ── */
$dateFilter = isset($_GET['date']) && $_GET['date'] !== '' ? $_GET['date'] : date('Y-m-d');
$dateEsc    = mysqli_real_escape_string($myCon, $dateFilter);

// METRICS (all-time, not affected by the date filter)
$sqlRevenue = "SELECT IFNULL(SUM(GrandTotal),0) AS TotalRevenue, COUNT(*) AS TotalTxns
               FROM Orders WHERE PaymentStatus = 'Paid'";
$revRow = mysqli_fetch_assoc(mysqli_query($myCon, $sqlRevenue));

$sqlPending = "SELECT COUNT(*) AS PendingCount FROM Orders WHERE PaymentStatus = 'UnPaid'";
$pendRow = mysqli_fetch_assoc(mysqli_query($myCon, $sqlPending));

$sqlCustomers = "SELECT COUNT(DISTINCT customer_phone) AS CustomerCount
                  FROM Orders WHERE DATE(OrderDate) = '$dateEsc'";
$custRow = mysqli_fetch_assoc(mysqli_query($myCon, $sqlCustomers));

// SELECTED-DATE REVENUE (follows $dateFilter, defaults to today)
$sqlSelectedDay = "SELECT IFNULL(SUM(GrandTotal),0) AS DayRevenue,
                    COUNT(*) AS DayTxns, IFNULL(SUM(GrandTotal - (GrandTotal / 1.10)),0) AS DayTax
                    FROM Orders
                    WHERE PaymentStatus = 'Paid' AND DATE(OrderDate) = '$dateEsc'";
$selectedDayRow = mysqli_fetch_assoc(mysqli_query($myCon, $sqlSelectedDay));

// DAILY BREAKDOWN (last 30 days that had a paid order), split by method
$sqlDaily = "
    SELECT
        DATE(OrderDate) AS PayDate,
        COUNT(*) AS TxnCount,
        SUM(GrandTotal) AS DailyTotal,
        SUM(CASE WHEN PaymentMethod = 'Cash'         THEN GrandTotal ELSE 0 END) AS CashTotal,
        SUM(CASE WHEN PaymentMethod = 'KHQR'         THEN GrandTotal ELSE 0 END) AS KHQRTotal,
        SUM(CASE WHEN PaymentMethod = 'BankTransfer' THEN GrandTotal ELSE 0 END) AS BankTotal
    FROM Orders
    WHERE PaymentStatus = 'Paid'
    GROUP BY DATE(OrderDate)
    ORDER BY PayDate DESC
    LIMIT 30
";
$resultDaily = mysqli_query($myCon, $sqlDaily);

// TRANSACTIONS FOR THE SELECTED DATE (feeds the transactions table)
$sqlTxns = "
    SELECT o.OrderID, o.customer_name, o.customer_phone, o.PaymentMethod,
           o.OrderDate, o.GrandTotal, o.PaymentStatus
    FROM Orders o
    WHERE DATE(o.OrderDate) = '$dateEsc'
    ORDER BY o.OrderDate DESC
";
$resultTxns = mysqli_query($myCon, $sqlTxns);

$isToday = ($dateFilter === date('Y-m-d'));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="icon" type="image/png" href="../img/logo-ang.png">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <link href="https://fonts.googleapis.com/css2?family=Bayon&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="assets/dist/css/payment.css">
    <link rel="stylesheet" href="assets/dist/css/index.css">
    <style>    
        .font-kantumruy {
            font-family: 'Kantumruy Pro', sans-serif;
        }    
        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #dfe6ef; border-radius: 4px; }
    </style>
</head>

<body>

    <?php include 'include/adminsbd.php'; ?>
    <?php include 'include/adminhd.php'; ?>
    <?php include 'include/smooth_refresh.php'; ?>

    <!-- My PAGE CONTENT -->
    <div class="container py-3">

        
        <div class="page-title font-bayon">            
            បញ្ជីទូទាត់            
        </div>

        <div class="pay-wrap">
            <div class="metrics-grid">
                <div class="metric-card mc-blue">
                    <div class="mc-label font-bayon">
                        <div class="mc-icon mc-icon-blue"><svg viewBox="0 0 24 24">
                                <line x1="12" y1="1" x2="12" y2="23" />
                                <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
                            </svg></div>
                        ប្រាក់ចំណូលសរុបប្រចាំថ្ងៃ
                    </div>
                    <div class="mc-value font-kantumruy">$ <?= number_format($selectedDayRow['DayRevenue'], 2); ?></div>
                    <div class="mc-delta font-kantumruy"><?= (int) $selectedDayRow['DayTxns']; ?> <span>transactions on this date</span></div>
                </div>
                <div class="metric-card mc-green">
                    <div class="mc-label font-bayon">
                        <div class="mc-icon mc-icon-green"><svg viewBox="0 0 24 24">
                                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                                <line x1="3" y1="6" x2="21" y2="6" />
                                <path d="M16 10a4 4 0 01-8 0" />
                            </svg></div>
                        <?= $isToday ? "ពន្ធសរុប" : 'Tax on ' . htmlspecialchars($dateFilter); ?>
                    </div>
                    <div class="mc-value font-kantumruy">$ <?= number_format($selectedDayRow['DayTax'], 2); ?></div>   
                    <div class="mc-delta font-kantumruy"><span>10% tax collected</span></div>                 
                </div>
                <div class="metric-card mc-purple">
                    <div class="mc-label font-bayon">
                        <div class="mc-icon mc-icon-purple"><svg viewBox="0 0 24 24">
                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 00-3-3.87" />
                                <path d="M16 3.13a4 4 0 010 7.75" />
                            </svg></div>
                        <?= $isToday ? "អតិថិជនប្រចាំថ្ងៃ" : 'Customers on ' . htmlspecialchars($dateFilter); ?>
                    </div>
                    <div class="mc-value font-kantumruy"><?= (int) $custRow['CustomerCount']; ?> នាក់</div>
                    <div class="mc-delta font-kantumruy"><span>people who ordered on this date</span></div>
                </div>
                <div class="metric-card mc-amber">
                    <div class="mc-label font-bayon">
                        <div class="mc-icon mc-icon-amber"><svg viewBox="0 0 24 24">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                            </svg></div>
                        មិនទាន់ទូទាត់
                    </div>
                    <div class="mc-value font-kantumruy"><?= (int) $pendRow['PendingCount']; ?></div>
                    <div class="mc-delta font-kantumruy"><span>orders awaiting payment</span></div>
                </div>
            </div>

            <!-- Filters -->
            <div class="pay-filters font-kantumruy">
                <input type="text" id="searchInput" placeholder="Search by order, customer..." oninput="applyFilters()" />
                <select id="methodFilter" onchange="applyFilters()">
                    <option value="">All methods</option>
                    <option value="Cash">Cash</option>
                    <option value="KHQR">KHQR</option>
                    <option value="BankTransfer">BankTransfer</option>
                </select>
                <select id="statusFilter" onchange="applyFilters()">
                    <option value="">All status</option>
                    <option value="Paid">ទូទាត់រួច</option>
                    <option value="UnPaid">មិនទាន់ទូទាត់</option>
                    <option value="Refunded">សងប្រាក់វិញ</option>
                </select>

                <!-- Date filter: same pattern as sale_report.php / history_order.php -->
                <input type="date" id="dateFilter" class="form-control" style="max-width:180px;"
                    value="<?= htmlspecialchars($dateFilter); ?>" onchange="filterByDate()">

                <button type="button" onclick="printPaymentReport()" class="btn bg-primary text-white font-kantumruy">
                    <i class="fas fa-print"></i>ទាញយករបាយការណ៏
                </button>
            </div>
            
            <div class="table-card">
                <div class="table-card-header font-kantumruy">
                    <div>
                        <div class="chart-title">
                            <?= $isToday ? "Today's Transactions" : 'Transactions on ' . htmlspecialchars($dateFilter); ?>
                        </div>
                        <div class="chart-sub">All orders for the selected date</div>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Method</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($resultTxns) === 0) { ?>
                        <tr>
                            <td colspan="6" class="text-center text-danger py-4">No transactions on this date.</td>
                        </tr>
                        <?php } else { while ($t = mysqli_fetch_assoc($resultTxns)) {
                            $status = $t['PaymentStatus'];

                            if ($status === 'Paid') {
                                $badgeClass = 'st-green';
                                $badgeLabel = 'ទូទាត់រួច';
                            } elseif ($status === 'Refunded') {
                                $badgeClass = 'st-gray';
                                $badgeLabel = 'សងប្រាក់';
                            } else {
                                $badgeClass = 'st-amber';
                                $badgeLabel = 'មិនទាន់ទូទាត់';
                            }
                        ?>
                        <tr data-method="<?= htmlspecialchars($t['PaymentMethod'] ?? ''); ?>" data-status="<?= htmlspecialchars($status); ?>">
                            <td class="td-main td-mono">#ORD-<?= str_pad($t['OrderID'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td class="td-main"><?= htmlspecialchars($t['customer_name']); ?></td>
                            <td><?= htmlspecialchars($t['PaymentMethod'] ?? '-'); ?></td>
                            <td><?= date('d-M-Y / h:i A', strtotime($t['OrderDate'])) ?></td>
                            <td>$<?= number_format($t['GrandTotal'], 2); ?></td>
                            <td class="font-kantumruy"><span class="status <?= $badgeClass; ?>" style="font-size: 0.9rem;"><?= $badgeLabel; ?></span></td>
                        </tr>
                        <?php } } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // sale_report.php / history_order.php's filterByDate().
        function filterByDate() {
            var date = document.getElementById('dateFilter').value;
            window.location.href = 'payment.php' + (date ? '?date=' + encodeURIComponent(date) : '');
            smoothRefresh(450, url);
        }

        // Live real-time clock, updates every second, independent of
        // whatever date is selected in the filter above.
        function tickClock() {
            var now = new Date();

            var hh = String(now.getHours()).padStart(2, '0');
            var mm = String(now.getMinutes()).padStart(2, '0');
            var ss = String(now.getSeconds()).padStart(2, '0');

            var yyyy = now.getFullYear();
            var mo = String(now.getMonth() + 1).padStart(2, '0');
            var dd = String(now.getDate()).padStart(2, '0');

            document.getElementById('liveClock').textContent = hh + ':' + mm + ':' + ss;
            document.getElementById('liveDate').textContent = yyyy + '-' + mo + '-' + dd;
        }

        tickClock();
        setInterval(tickClock, 1000);

        function applyFilters() {
            var search = document.getElementById('searchInput').value.toLowerCase();
            var method = document.getElementById('methodFilter').value;
            var status = document.getElementById('statusFilter').value;

            document.querySelectorAll('.table-card table tbody tr').forEach(function(row) {
                if (row.dataset.method === undefined) return; // skip the "No transactions" placeholder row

                var text = row.textContent.toLowerCase();
                var matchesSearch = !search || text.includes(search);
                var matchesMethod = !method || row.dataset.method === method;
                var matchesStatus = !status || row.dataset.status === status;

                row.style.display = (matchesSearch && matchesMethod && matchesStatus) ? '' : 'none';
            });
        }

        function smoothRefresh(delay = 450, url = null) {
            document.body.classList.add('refreshing');
            document.getElementById('refresh-veil').classList.add('show');
            setTimeout(() => {
                if (url) {
                    window.location.href = url;
                } else {
                    window.location.reload();
                }
            }, delay);
        }

        // Print report
        function printPaymentReport() {
            var date = document.getElementById('dateFilter').value || <?= json_encode($dateFilter); ?>;

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
            iframe.src = '../admin/get_data/print_payment_report.php?date=' + encodeURIComponent(date);

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