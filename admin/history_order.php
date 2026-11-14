<?php include 'include/cn.php';
$dateFilter = isset($_GET['date']) ? $_GET['date'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders History</title>
    <link rel="icon" type="image/png" href="../img/logo-ang.png">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <link href="https://fonts.googleapis.com/css2?family=Bayon&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>

<style>
    body {
        background: #f5f7fb;
        font-family: Arial, sans-serif;
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

    .card-custom {
        background: #fff;
        border-radius: 18px;
        padding: 25px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    .table thead {
        background: #f1f5f9;
    }

    .table thead th {
        text-align: center;
        color: #6b7280;
        padding: 12px 10px;
        font-size: 13px;
        white-space: nowrap;
    }

    .table tbody td {
        text-align: center;
        vertical-align: middle;
        padding: 10px 8px;
        font-size: 12px;
        white-space: nowrap;
    }

    .table tbody tr:hover {
        background: #f9fafb;
        transition: .3s;
    }

    .font-bayon {
        font-family: 'Bayon', sans-serif;
    }

    .font-kantumruy {
        font-family: 'Kantumruy Pro', sans-serif;
    }

    .custom-table-scroll {
        width: 100%;
        max-height: 400px;
        overflow-x: auto;
        overflow-y: auto;
        border: 1px solid #e5e7eb;
        border-radius: 15px;
        background: #fff;
    }

    .custom-table-scroll table {
        width: max-content;
        min-width: 100%;
        margin: 0;
    }

    .custom-table-scroll thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background: #f8fafc;
    }

    .custom-table-scroll::-webkit-scrollbar {
        height: 6px;
        width: 6px;
    }

    .custom-table-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .custom-table-scroll::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    @media(max-width:768px) {
        .page-title {
            font-size: 22px;
        }

        .card-custom {
            padding: 18px;
        }

        .custom-table-scroll {
            max-height: 350px;
        }

        .custom-table-scroll table {
            min-width: 900px;
        }
    }

    .badge-pending {
        background: #f59e0b;
        color: #fff;
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 11px;
    }

    .badge-accepted {
        background: #10b981;
        color: #fff;
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 11px;
    }

    .badge-rejected {
        background: #ef4444;
        color: #fff;
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 11px;
    }

    .btn-back {
        background: #6b7280;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 10px 18px;
        text-decoration: none;
    }

    .btn-back:hover {
        background: #4b5563;
        color: #fff;
    }

    .top-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .stats-row {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }

    .stats-box {
        background: #fff;
        border-radius: 12px;
        padding: 15px 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        text-align: center;
        flex: 1;
    }

    .stats-box h5 {
        color: #6b7280;
        font-size: 14px;
        margin-bottom: 5px;
    }

    .stats-box h3 {
        color: #2563eb;
        font-size: 24px;
        font-weight: bold;
    }
</style>

<body>
    <?php include 'include/adminsbd.php'; ?>
    <?php include 'include/adminhd.php'; ?>

    <div class="container py-3">

        <div class="page-title font-bayon">
            បញ្ជីប្រវត្តិ
        </div>

        <!-- Stats -->
        <?php
        // If date filter is set, filter stats by date
        if ($dateFilter) {
            $total = mysqli_fetch_assoc(mysqli_query($myCon, "SELECT COUNT(*) as total FROM history_orders WHERE DATE(ArchivedDate) = '$dateFilter'"));
            $accept = mysqli_fetch_assoc(mysqli_query($myCon, "SELECT COUNT(*) as total FROM history_orders WHERE DATE(ArchivedDate) = '$dateFilter' AND OrderStatus = 'Accept'"));
            $reject = mysqli_fetch_assoc(mysqli_query($myCon, "SELECT COUNT(*) as total FROM history_orders WHERE DATE(ArchivedDate) = '$dateFilter' AND OrderStatus = 'Reject'"));
        } else {
            $total = mysqli_fetch_assoc(mysqli_query($myCon, "SELECT COUNT(*) as total FROM history_orders"));
            $accept = mysqli_fetch_assoc(mysqli_query($myCon, "SELECT COUNT(*) as total FROM history_orders WHERE OrderStatus = 'Accept'"));
            $reject = mysqli_fetch_assoc(mysqli_query($myCon, "SELECT COUNT(*) as total FROM history_orders WHERE OrderStatus = 'Reject'"));
        }

        // $total = mysqli_fetch_assoc(mysqli_query($myCon, "SELECT COUNT(*) as total FROM history_orders"));
        // $accept = mysqli_fetch_assoc(mysqli_query($myCon, "SELECT COUNT(*) as total FROM history_orders WHERE OrderStatus = 'Accept'"));
        // $reject = mysqli_fetch_assoc(mysqli_query($myCon, "SELECT COUNT(*) as total FROM history_orders WHERE OrderStatus = 'Reject'"));
        ?>
        <div class="stats-row">
            <div class="stats-box">
                <h5 class="font-bayon">បញ្ជីសរុប</h5>
                <h3 class="font-kantumruy"><?= $total['total']; ?></h3>
            </div>
            <div class="stats-box">
                <h5 class="font-bayon">ទទួលយក</h5>
                <h3 class="font-kantumruy" style="color: #10b981;"><?= $accept['total']; ?></h3>
            </div>
            <div class="stats-box">
                <h5 class="font-bayon">បដិសេដ</h5>
                <h3 class="font-kantumruy" style="color: #ef4444;"><?= $reject['total']; ?></h3>
            </div>
        </div>

        <div class="card-custom">
            <div class="top-controls font-kantumruy">
                <input type="date" id="dateFilter" class="form-control" style="max-width: 200px;">
                <button class="btn-print" onclick="filterByDate()" style="background: #2563eb; color: #fff; padding: 8px 15px; border-radius: 8px; text-decoration: none; display: inline-block; border: none;">
                    <i class="fas fa-search"></i> ស្វែងរក
                </button>

                <?php $printDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); ?>

                <a href="../admin/get_data/print_report.php?date=<?= $printDate; ?>" target="_blank" class="btn-print font-kantumruy" style="background: #2563eb; color: #fff; padding: 8px 15px; border-radius: 8px; text-decoration: none; display: inline-block;">
                    <i class="fas fa-print"></i> Print Daily Report
                </a>
            </div>

            <div class="print-header font-bayon">
                <h2> បញ្ជីប្រវត្តិកម្ម</h2>
                <?php if ($dateFilter): ?>
                    <p style="font-size: 14px; color: #666; margin-top: 5px;">
                        ថ្ងៃ: <?= date('d/m/Y', strtotime($dateFilter)); ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="custom-table-scroll">
                <table class="table align-middle">
                    <thead class="font-bayon">
                        <tr>
                            <th>លេខ</th>
                            <th>ឈ្មោះ</th>
                            <th>ទូរស័ព្ទ</th>
                            <th>ថ្ងៃ</th>
                            <th>ម៉ោង</th>
                            <th>ចំនួន</th>
                            <th>តុ</th>
                            <th>សារ</th>
                            <th>ស្ថានភាព</th>
                            <th>Archived</th>
                        </tr>
                    </thead>

                    <tbody class="font-kantumruy">
                        <?php
                        $dateFilter = isset($_GET['date']) ? $_GET['date'] : '';

                        if ($dateFilter) {
                            $sql = "SELECT * FROM history_orders WHERE DATE(ArchivedDate) = '$dateFilter' ORDER BY ArchivedDate ASC";
                        } else {
                            $sql = "SELECT * FROM history_orders ORDER BY ArchivedDate ASC";
                        }
                        $result = mysqli_query($myCon, $sql);
                        $i = 1;
                        while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                            <tr>
                                <td><?= $i;
                                    $i++; ?></td>
                                <td><?= $row['customer_name']; ?></td>
                                <td><?= $row['customer_phone']; ?></td>
                                <td><?= $row['date']; ?></td>
                                <td><?= $row['time']; ?></td>
                                <td><?= $row['persons']; ?></td>
                                <td><?= $row['tableType']; ?></td>
                                <td><?= $row['message']; ?></td>
                                <td>
                                    <?php
                                    $status = $row['OrderStatus'];
                                    if ($status == 'Pending' || $status == '') {
                                        echo '<span class="badge-pending">Pending</span>';
                                    } elseif ($status == 'Accept') {
                                        echo '<span class="badge-accepted">Accept</span>';
                                    } elseif ($status == 'Reject') {
                                        echo '<span class="badge-rejected">Reject</span>';
                                    }
                                    ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($row['ArchivedDate'])); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Print Footer -->
            <!-- <div class="print-footer">
                <div class="print-signature">
                    <div>
                        <div class="line">Signature</div>
                    </div>
                    <div>
                        <div class="line">Signature</div>
                    </div>
                </div>
            </div> -->

        </div>
    </div>



    <script>
        // Filter date
        function filterByDate() {
            var date = document.getElementById('dateFilter').value;
            if (date) {
                window.location.href = 'history_order.php?date=' + date;
            } else {
                window.location.href = 'history_order.php';
            }
        }
    </script>

</body>

</html>