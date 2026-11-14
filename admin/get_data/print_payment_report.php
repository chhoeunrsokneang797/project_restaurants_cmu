<?php include '../../include/cn.php';

/* ── Single day filter (defaults to today, mirrors payment.php) ──── */
$dateFilter = isset($_GET['date']) && $_GET['date'] !== '' ? $_GET['date'] : date('Y-m-d');
$dateEsc    = mysqli_real_escape_string($myCon, $dateFilter);

/* ── Same metrics as payment.php, scoped to the selected date ──── */
$sqlSelectedDay = "SELECT IFNULL(SUM(GrandTotal),0) AS DayRevenue,
                    COUNT(*) AS DayTxns, IFNULL(SUM(GrandTotal - (GrandTotal / 1.10)),0) AS DayTax
                    FROM Orders
                    WHERE PaymentStatus = 'Paid' AND DATE(OrderDate) = '$dateEsc'";
$selectedDayRow = mysqli_fetch_assoc(mysqli_query($myCon, $sqlSelectedDay));

$sqlCustomers = "SELECT COUNT(DISTINCT customer_phone) AS CustomerCount
                  FROM Orders WHERE DATE(OrderDate) = '$dateEsc'";
$custRow = mysqli_fetch_assoc(mysqli_query($myCon, $sqlCustomers));

$sqlPending = "SELECT COUNT(*) AS PendingCount FROM Orders
               WHERE PaymentStatus = 'UnPaid' AND DATE(OrderDate) = '$dateEsc'";
$pendRow = mysqli_fetch_assoc(mysqli_query($myCon, $sqlPending));

/* ── Transactions for the selected date ──── */
$sqlTxns = "SELECT o.OrderID, o.customer_name, o.customer_phone, o.PaymentMethod,
                   o.OrderDate, o.GrandTotal, o.PaymentStatus
            FROM Orders o
            WHERE DATE(o.OrderDate) = '$dateEsc'
            ORDER BY o.OrderDate DESC";
$resultTxns = mysqli_query($myCon, $sqlTxns);
$txns = [];
if ($resultTxns) {
    while ($t = mysqli_fetch_assoc($resultTxns)) {
        $txns[] = $t;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Daily Payment Report</title>
    <link rel="icon" type="image/png" href="../images/logo-ang.png">
    <link href="https://fonts.googleapis.com/css2?family=Bayon&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<style>
    * { box-sizing: border-box; }

    body {
        font-family: 'Kantumruy Pro', Arial, sans-serif;
        color: #1f2937;
        margin: 0;
        padding: 30px;
        background: #fff;
    }

    .font-bayon { font-family: 'Bayon', sans-serif; }

    .report-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 3px solid #1f2937;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }

    .report-header .shop-name { font-size: 24px; font-weight: bold; color: #1f2937; }
    .report-header .report-sub { color: #6b7280; font-size: 13px; margin-top: 4px; }
    .report-header .report-meta { text-align: right; font-size: 13px; color: #6b7280; }

    .report-title { text-align: center; font-size: 20px; margin: 10px 0 25px; }

    .stats-row { display: flex; gap: 15px; margin-bottom: 25px; }

    .stats-box {
        flex: 1;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 12px 15px;
        text-align: center;
    }

    .stats-box .lbl { font-size: 12px; color: #6b7280; margin-bottom: 5px; }
    .stats-box .val { font-size: 20px; font-weight: bold; color: #1f2937; }

    .section-title {
        font-size: 15px;
        font-weight: bold;
        margin: 25px 0 10px;
        padding-bottom: 6px;
        border-bottom: 1px solid #e5e7eb;
    }

    table { width: 100%; border-collapse: collapse; font-size: 12px; }

    thead th {
        background: #f1f5f9;
        color: #6b7280;
        text-align: left;
        padding: 8px 10px;
        border-bottom: 1px solid #e5e7eb;
    }

    tbody td { padding: 7px 10px; border-bottom: 1px solid #f1f5f9; }
    tbody tr:nth-child(even) { background: #fafbfc; }

    .text-right { text-align: right; }
    .text-center { text-align: center; }

    .empty-note { color: #9ca3af; text-align: center; padding: 20px; font-size: 13px; }

    .status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 20px;
        font-weight: 600;
    }
    .status::before { content: ''; width: 5px; height: 5px; border-radius: 50%; }
    .st-green { background: rgba(34,201,122,0.12); color: #16a34a; }
    .st-green::before { background: #16a34a; }
    .st-amber { background: rgba(245,166,35,0.12); color: #b45309; }
    .st-amber::before { background: #b45309; }
    .st-gray { background: rgba(148,163,184,0.15); color: #64748b; }
    .st-gray::before { background: #64748b; }

    .report-footer {
        margin-top: 30px;
        padding-top: 10px;
        border-top: 1px solid #e5e7eb;
        font-size: 11px;
        color: #9ca3af;
        text-align: center;
    }

    /* This page is only ever loaded silently inside a hidden print iframe */
    .print-bar { display: none; }
</style>

<body>

    <div class="report-header">
        <div>
            <div class="shop-name font-bayon">Angkor Siemreap Restaurant</div>
            <div class="report-sub">Daily Payment Report</div>
        </div>
        <div class="report-meta">
            <div>Report Date: <strong><?= date('d-M-Y h:i A', strtotime($dateFilter)); ?></strong></div>
        </div>
    </div>

    <div class="report-title font-bayon">
        បញ្ជីទូទាត់ប្រចាំថ្ងៃ — <?= date('d-M-Y', strtotime($dateFilter)); ?>
    </div>

    <div class="stats-row">
        <div class="stats-box">
            <div class="lbl">ប្រាក់ចំណូលសរុប</div>
            <div class="val">$<?= number_format($selectedDayRow['DayRevenue'], 2); ?></div>
        </div>
        <div class="stats-box">
            <div class="lbl">ពន្ធសរុប (10%)</div>
            <div class="val">$<?= number_format($selectedDayRow['DayTax'], 2); ?></div>
        </div>
        <div class="stats-box">
            <div class="lbl">អតិថិជន</div>
            <div class="val"><?= (int)$custRow['CustomerCount']; ?> នាក់</div>
        </div>
        <div class="stats-box">
            <div class="lbl">មិនទាន់ទូទាត់</div>
            <div class="val"><?= (int)$pendRow['PendingCount']; ?></div>
        </div>
    </div>

    <div class="section-title font-bayon">ប្រតិបត្តិការទូទាត់ (Transactions)</div>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Method</th>
                <th>Date</th>
                <th class="text-right">Amount</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($txns)): ?>
                <tr>
                    <td colspan="6" class="empty-note">No transactions on this date.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($txns as $t):
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
                    <tr>
                        <td>#ORD-<?= str_pad($t['OrderID'], 4, '0', STR_PAD_LEFT); ?></td>
                        <td><?= htmlspecialchars($t['customer_name']); ?></td>
                        <td><?= htmlspecialchars($t['PaymentMethod'] ?? '-'); ?></td>
                        <td><?= date('d-M-Y / h:i A', strtotime($t['OrderDate'])); ?></td>
                        <td class="text-right">$<?= number_format($t['GrandTotal'], 2); ?></td>
                        <td class="text-center"><span class="status <?= $badgeClass; ?>"><?= $badgeLabel; ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="report-footer">
        This report was automatically generated as of <?= date('d-M-Y'); ?>.
    </div>

</body>

</html>