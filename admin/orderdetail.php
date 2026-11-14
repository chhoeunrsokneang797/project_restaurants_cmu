<?php include 'include/cn.php';

$dateFilter = isset($_GET['date']) && $_GET['date'] !== '' ? $_GET['date'] : date('Y-m-d');
$dateEsc    = mysqli_real_escape_string($myCon, $dateFilter);

$sql = "SELECT
    od.OrderDetailID,
    od.OrderID,
    od.MenuItemID,
    COALESCE(m.ItemName,'Item Deleted') AS ItemName,
    od.Quantity,
    od.UnitPrice,
    od.Discount,
    od.SubTotal,
    COALESCE(rt.TableName, o.tableType, 'N/A') AS TableName
FROM OrderDetails od
LEFT JOIN MenuItems m
    ON od.MenuItemID = m.MenuItemID
INNER JOIN Orders o
    ON od.OrderID = o.OrderID
LEFT JOIN RestaurantTables rt
    ON o.TableID = rt.TableID
WHERE o.date = '$dateEsc'
ORDER BY od.OrderDetailID ASC";

$result = mysqli_query($myCon, $sql);

// $result = $myCon->query($sql);
$result = mysqli_query($myCon,$sql);

// Delete Logic
if (isset($_GET['delete_id'])) {
    $delID = (int)$_GET['delete_id'];
    $sqlDelete = "DELETE FROM Employees WHERE EmployeeID = $delID";

    if ($myCon->query($sqlDelete) === TRUE) {
        $success_message = "Order Deleted Successfully!";
    } else {
        $error_message = "Order Deleted Failed!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="icon" type="image/png" href="../img/logo-ang.png">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <link
        href="https://fonts.googleapis.com/css2?family=Bayon&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="assets/dist/css/orderdetail.css">
    <style>        
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

        <!-- Title -->
        <div class="page-title font-bayon">
            ព័ត៌មានលម្អិតនៃការបញ្ជាទិញ
        </div>

        <!-- Table Card -->
        <div class="card-custom">
            <!-- Top Controls -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-4 mb-4">
                <div class="top-controls-left">
                    <div class="font-kantumruy">
                        <label class="form-label mb-1" style="font-size:12px;color:#6b7280;">ជ្រើសរើសថ្ងៃ</label>
                        <input type="date" id="dateFilter" class="form-control" style="max-width: 200px;" value="<?= htmlspecialchars($dateFilter); ?>" onchange="filterByDate()">
                    </div>
                </div>

                <div style="max-width:260px; position: relative;" class="font-kantumruy">
                    <i class="fas fa-search"
                        style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#888;"></i>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search Name..."
                        style="padding-left:35px;">
                </div>
            </div>

            <div class="custom-table-scroll">
                <table class="table align-middle">
                    <thead class="font-bayon">
                        <tr>
                            <th>Detail ID</th>
                            <th>លេខបញ្ជាទិញ</th>
                            <th>លេខតុ</th>
                            <th>ឈ្មោះមឺនុយ</th>
                            <th>ចំនួនមឺនុយ</th>
                            <th>តម្លៃលក់រាយ</th>
                            <th>បញ្ចុះតម្លៃ</th>
                            <th>តម្លៃសរុប</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody class="font-kantumruy">

                        <?php if(mysqli_num_rows($result) > 0): ?>

                    <?php while($row = mysqli_fetch_assoc($result)): ?>

                    <tr>
                        <td><?= $row['OrderDetailID']; ?></td>
                        <td><?= $row['MenuItemID']; ?></td>
                        <td><?= $row['TableName']; ?></td>
                        <td class="item-name"><?= htmlspecialchars($row['ItemName']); ?></td>
                        <td><?= $row['Quantity']; ?></td>
                        <td>$ <?= number_format($row['UnitPrice'], 2); ?></td>
                        <td>$ <?= number_format($row['Discount'], 2); ?></td>
                        <td>$ <?= number_format($row['SubTotal'], 2); ?></td>
                        <td>
                            <!-- <a href="orderdetails.php?id=<?= $row['OrderID']; ?>"
                            class="btn btn-primary btn-sm">
                            
                            </a> -->
                            <a href="orderdetail.php?delete_id=<?= $row['OrderDetailID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure to delete?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-danger">
                            មិនមានទិន្នន័យសម្រាប់ថ្ងៃនេះទេ
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
                        <!-- <?php
                        while($row = mysqli_fetch_assoc($result)){
                        ?>

                        <tr>
                            <td><?= $row['OrderDetailID']; ?></td>
                            <td><?= $row['ItemName']; ?></td>
                            <td><?= $row['Quantity']; ?></td>
                            <td>$<?= number_format($row['UnitPrice'],2); ?></td>
                            <td>$<?= number_format($row['SubTotal'],2); ?></td>
                        </tr>

                        <?php } ?>
                    </tbody> -->
                </table>
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

    </div> <!-- end content -->
    </div> <!-- end main -->


    <script>
        $('#searchInput').on('keyup', function() {
            let value = $(this).val().toLowerCase().trim();
            $(".custom-table-scroll table tbody tr").each(function() {
                let nameText = $(this).find('.item-name').text().toLowerCase();
                if (nameText.indexOf(value) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        function filterByDate() {
            var date = document.getElementById('dateFilter').value;
            window.location.href = 'orderdetail.php' + (date ? '?date=' + encodeURIComponent(date) : '');
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