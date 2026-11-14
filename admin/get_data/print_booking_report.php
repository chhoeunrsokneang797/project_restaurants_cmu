<?php
include '../include/cn.php';

$dateFilter = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Only Paid, Dine In (table booking) orders belong on this report —
// matches the filters used on booking_report.php.
// NumberOfGuests can be blank on history_orders (e.g. walk-in archives that
// didn't record it), so fall back to the matching tablebookings row.
$sql = "SELECT ho.*,
        COALESCE(
            NULLIF(ho.NumberOfGuests, ''),
            (SELECT tb.NumberOfGuests
             FROM tablebookings tb
             WHERE tb.TableID = ho.TableID
             ORDER BY tb.BookingID DESC
             LIMIT 1),
            ''
        ) AS ResolvedGuests
    FROM history_orders ho
    WHERE DATE(ho.ArchivedDate) = '$dateFilter' AND ho.PaymentStatus = 'Paid'
    ORDER BY ho.ArchivedDate ASC";
$result = mysqli_query($myCon, $sql);
$total = mysqli_num_rows($result);

$topItems = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $topItems[] = $row;
    }
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
// $result = $myCon->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Daily Booking Report</title>
    <link rel="icon" type="image/png" href="../images/logo-ang.png">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Battambang:wght@100;300;400;700;900&family=Bayon&family=Kantumruy+Pro:ital,wght@0,100..700;1,100..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap');

        * {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            box-sizing: border-box;
            font-family: "Bayon", sans-serif;
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

        .font-kantumruy{
            font-family: 'Kantumruy Pro', sans-serif;
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

        /* Status pills — same scheme as order.php / booking_report.php */
        .status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
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
            color: #16a34a;
        }

        .st-green::before {
            background: #16a34a;
        }

        .st-amber {
            background: rgba(245, 166, 35, 0.12);
            color: #b45309;
        }

        .st-amber::before {
            background: #b45309;
        }

        .st-red {
            background: rgba(242, 92, 92, 0.12);
            color: #dc2626;
        }

        .st-red::before {
            background: #dc2626;
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
</head>

<body>    

    <?php
    // Same Paid + Dine In scope as the table below, and the real status
    // values the app writes are 'Completed' / 'Cancelled' (not Accept/Reject).
    $total  = mysqli_fetch_assoc(mysqli_query($myCon, "SELECT COUNT(*) as total FROM history_orders WHERE DATE(ArchivedDate) = '$dateFilter' AND PaymentStatus = 'Paid' AND OrderType = 'Dine In'"));
    $accept = mysqli_fetch_assoc(mysqli_query($myCon, "SELECT COUNT(*) as total FROM history_orders WHERE DATE(ArchivedDate) = '$dateFilter' AND PaymentStatus = 'Paid' AND OrderType = 'Dine In' AND (OrderStatus = 'Completed' OR OrderStatus = '' OR OrderStatus IS NULL)"));
    $reject = mysqli_fetch_assoc(mysqli_query($myCon, "SELECT COUNT(*) as total FROM history_orders WHERE DATE(ArchivedDate) = '$dateFilter' AND PaymentStatus = 'Paid' AND OrderType = 'Dine In' AND OrderStatus = 'Cancelled'"));
    ?>

    <div class="print-bar">
        <button onclick="window.print()">🖨 Print</button>
    </div>

    <div class="report-header">
        <div>
            <div class="shop-name font-bayon">Angkor Siemreap Restaurant</div>
            <div class="report-sub">Daily Booking Report</div>
        </div>
        <div class="report-meta">
            <div>Report Date: <strong><?= date('d-M-Y h:i A', strtotime($dateFilter)); ?></strong></div>
        </div>
    </div>

    <div class="report-title font-bayon">
        របាយការណ៍កក់ប្រចាំថ្ងៃ — <?= date('d-M-Y', strtotime($dateFilter)); ?>
    </div>

    <!-- All figures below count Paid, Dine In (table booking) orders only. -->
    <div class="stats-row">
        <div class="stats-box revenue">
            <div class="lbl">ការកក់សរុប</div>
            <div class="val"><?= $total['total']; ?></div>
        </div>
        <div class="stats-box">
            <div class="lbl">ការកក់ជោគជ័យ</div>
            <div class="val"><?= $accept['total']; ?></div>
        </div>
        <div class="stats-box">
            <div class="lbl">ការកក់បរាជ័យ</div>
            <div class="val"><?= $reject['total']; ?></div>
        </div>
    </div>

    <div class="section-title font-bayon">ទិន្ន័យអតិថិជន</div>
    <table>
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th class=" text-center">ឈ្មោះអតិថិជន</th>
                <th class=" text-center">លេខទូរស័ព្ទអតិថិជន</th>
                <th class=" text-center">ចំនួនមនុស្ស</th>
                <th class=" text-center">ឈ្មោះតុ</th>
                <th class=" text-center">ស្ថានភាពកក់</th>
                <th class=" text-center">ការទូទាត់</th>
                <th class=" text-center">កាលបរិច្ឆេទ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($topItems)): ?>
                <tr class="font-kantumruy">
                    <td colspan="8" class="empty-note">មិនមានទិន្នន័យសម្រាប់ថ្ងៃនេះទេ</td>
                </tr>
            <?php else: ?>
                <?php $r = 1;

                foreach ($topItems as $t): ?>
                    <tr>
                        <td class="font-kantumruy"> <?= $r++; ?></td>
                        <td class="font-kantumruy  text-center"><?= $t['customer_name'] ? htmlspecialchars($t['customer_name']) : 'N/A'; ?></td>
                        <td class="font-kantumruy  text-center"><?= $t['customer_phone']; ?></td>
                        <td class="font-kantumruy  text-center"><?= $t['ResolvedGuests'] !== '' ? htmlspecialchars($t['ResolvedGuests']) : 'N/A'; ?> នាក់</td>
                        <td class="font-kantumruy  text-center"><?= $t['tableType']; ?></td>                      
                        <td class="text-center">
                            <?php
                                // Blank OrderStatus (e.g. walk-in bookings) is treated as
                                // Completed, since this table is already scoped to Paid orders.
                                $orderStatus = trim((string)$t['OrderStatus']);
                                if ($orderStatus === '') {
                                    $orderStatus = 'Completed';
                                }
                                if ($orderStatus == 'Completed') {
                                    echo '<span class="font-kantumruy">' . htmlspecialchars($orderStatus) . '</span>';
                                } elseif ($orderStatus == 'Cancelled') {
                                    echo '<span class="font-kantumruy">' . htmlspecialchars($orderStatus) . '</span>';
                                } else {
                                    echo '<span class="font-kantumruy">' . htmlspecialchars($orderStatus) . '</span>';
                                }
                                ?>
                        </td>
                        
                        <td class="text-center">
                            <?php
                                $paymentStatus = $t['PaymentStatus'];
                                if ($paymentStatus == 'Paid') {
                                    echo '<span class="font-kantumruy">ទូទាត់រួចរាល់</span>';
                                } elseif ($paymentStatus == 'Unpaid') {
                                    echo '<span class="font-kantumruy">មិនទាន់ទូទាត់</span>';
                                }
                                ?>
                        </td>
                        <td class="font-kantumruy  text-center"><?= date('d/M/Y H:i A', strtotime($t['ArchivedDate'])); ?></td>                                                                
                    </tr>
                <?php endforeach; ?>                
            <?php endif; ?>
        </tbody>
    </table>

    <div class="report-footer">
        This report was automatically generated and reflects Paid, Dine In (table booking) orders only as of <?= date('d-M-Y'); ?>.
    </div>

</body>

</html>