<?php include '../../include/cn.php';

/* ── Single day filter (defaults to today) ────────────────────────── */
$dateFilter = isset($_GET['date']) && $_GET['date'] !== '' ? $_GET['date'] : date('Y-m-d');

function esc($conn, $val) {
    return mysqli_real_escape_string($conn, $val);
}
$dateEsc = esc($myCon, $dateFilter);

/* ── Same-day totals, Paid orders only (mirrors sale_report.php) ──── */
$revenueRow = mysqli_fetch_assoc(mysqli_query($myCon,
    "SELECT COALESCE(SUM(GrandTotal),0) AS total, COUNT(*) AS cnt FROM history_orders
     WHERE DATE(ArchivedDate) = '$dateEsc' AND PaymentStatus = 'Paid'"));
$totalRevenue  = (float)$revenueRow['total'];
$paidOrders    = (int)$revenueRow['cnt'];
$avgOrderValue = $paidOrders > 0 ? $totalRevenue / $paidOrders : 0;

$topItemsSql = "SELECT mi.ItemName AS name,
                        SUM(od.Quantity) AS qty,
                        SUM(od.SubTotal) AS revenue
                 FROM orderdetails od
                 INNER JOIN history_orders ho ON od.OrderID = ho.OrderID
                 LEFT JOIN menuitems mi ON od.MenuItemID = mi.MenuItemID
                 WHERE DATE(ho.ArchivedDate) = '$dateEsc'
                   AND ho.PaymentStatus = 'Paid'
                 GROUP BY od.MenuItemID, mi.ItemName
                 ORDER BY qty DESC";
$topItemsRes = mysqli_query($myCon, $topItemsSql);
$topItems = [];
if ($topItemsRes) {
    while ($t = mysqli_fetch_assoc($topItemsRes)) {
        $topItems[] = $t;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Sale Report</title>
    <link rel="icon" type="image/png" href="../images/logo-ang.png">
    <link href="https://fonts.googleapis.com/css2?family=Bayon&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<style>
    * {
        box-sizing: border-box;
    }

    body {
        font-family: 'Kantumruy Pro', Arial, sans-serif;
        color: #1f2937;
        margin: 0;
        padding: 30px;
        background: #fff;
    }

    .font-bayon {
        font-family: 'Bayon', sans-serif;
    }

    .report-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 3px solid #1f2937;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }

    .report-header .shop-name {
        font-size: 24px;
        font-weight: bold;
        color: #1f2937;
    }

    .report-header .report-sub {
        color: #6b7280;
        font-size: 13px;
        margin-top: 4px;
    }

    .report-header .report-meta {
        text-align: right;
        font-size: 13px;
        color: #6b7280;
    }

    .report-title {
        text-align: center;
        font-size: 20px;
        margin: 10px 0 25px;
    }

    .stats-row {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
    }

    .stats-box {
        flex: 1;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 12px 15px;
        text-align: center;
    }

    .stats-box .lbl {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 5px;
    }

    .stats-box .val {
        font-size: 20px;
        font-weight: bold;
        color: #1f2937;
    }

    .section-title {
        font-size: 15px;
        font-weight: bold;
        margin: 25px 0 10px;
        padding-bottom: 6px;
        border-bottom: 1px solid #e5e7eb;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    thead th {
        background: #f1f5f9;
        color: #6b7280;
        text-align: left;
        padding: 8px 10px;
        border-bottom: 1px solid #e5e7eb;
    }

    tbody td {
        padding: 7px 10px;
        border-bottom: 1px solid #f1f5f9;
    }

    tbody tr:nth-child(even) {
        background: #fafbfc;
    }

    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    .empty-note {
        color: #9ca3af;
        text-align: center;
        padding: 20px;
        font-size: 13px;
    }

    .grand-total-row td {
        font-weight: bold;
        border-top: 2px solid #1f2937;
        font-size: 13px;
    }

    .print-bar {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-bottom: 20px;
    }

    .print-bar button {
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 9px 18px;
        font-size: 13px;
        cursor: pointer;
    }

    .print-bar button:hover {
        background: #1d4fd1;
    }

    .report-footer {
        margin-top: 30px;
        padding-top: 10px;
        border-top: 1px solid #e5e7eb;
        font-size: 11px;
        color: #9ca3af;
        text-align: center;
    }

    @media print {
        body {
            padding: 0;
        }

        .print-bar {
            display: none;
        }
    }
</style>

<body>

    <div class="print-bar">
        <button onclick="window.print()">🖨 Print</button>
    </div>

    <div class="report-header">
        <div>
            <div class="shop-name font-bayon">Angkor Siemreap Restaurant</div>
            <div class="report-sub">Daily Sale Report</div>
        </div>
        <div class="report-meta">
            <div>Report Date: <strong><?= date('d-M-Y h:i A', strtotime($dateFilter)); ?></strong></div>
        </div>
    </div>

    <div class="report-title font-bayon">
        របាយការណ៍លក់ប្រចាំថ្ងៃ — <?= date('d-M-Y', strtotime($dateFilter)); ?>
    </div>

    <!-- All figures below count Paid orders only; Unpaid orders are excluded. -->
    <div class="stats-row">
        <div class="stats-box revenue">
            <div class="lbl">ចំណូលសរុប (Revenue)</div>
            <div class="val">$<?= number_format($totalRevenue, 2); ?></div>
        </div>
        <div class="stats-box">
            <div class="lbl">កម្មង់ដែលទូទាត់រួច (Paid Orders)</div>
            <div class="val"><?= $paidOrders; ?></div>
        </div>
        <div class="stats-box">
            <div class="lbl">តម្លៃមធ្យម (Avg. Order Value)</div>
            <div class="val">$<?= number_format($avgOrderValue, 2); ?></div>
        </div>
    </div>

    <div class="section-title font-bayon">ម្ហូបលក់ប្រចាំថ្ងៃ (Items Sold)</div>
    <table>
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th>ម្ហូប</th>
                <th class="text-center" style="width:100px;">ចំនួនលក់</th>
                <th class="text-right" style="width:100px;">ចំណូល</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($topItems)): ?>
                <tr>
                    <td colspan="4" class="empty-note">មិនមានទិន្នន័យសម្រាប់ថ្ងៃនេះទេ</td>
                </tr>
            <?php else: ?>
                <?php $r = 1;
                foreach ($topItems as $t): ?>
                    <tr>
                        <td><?= $r++; ?></td>
                        <td><?= $t['name'] ? htmlspecialchars($t['name']) : 'N/A'; ?></td>
                        <td class="text-center"><?= (int)$t['qty']; ?></td>
                        <td class="text-right">$<?= number_format($t['revenue'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="grand-total-row">
                    <td colspan="3" class="text-right">Total</td>
                    <td class="text-right">$<?= number_format($totalRevenue, 2); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="report-footer">
        This report was automatically and reflects Paid orders only as of <?= date('d-M-Y'); ?>.
    </div>

</body>

</html>