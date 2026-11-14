<?php
include 'include/cn.php';

// ─── UPDATE (customers are created automatically via table booking, not here) ───
if (isset($_POST['btnSaveCustomer'])) {
    $customerID = isset($_POST['CustomerID']) ? (int)$_POST['CustomerID'] : 0;

    if ($customerID <= 0) {
        echo "<script>alert('Invalid customer.'); window.location='customers.php';</script>";
        exit();
    }

    $custName = mysqli_real_escape_string($myCon, $_POST['CustomerName']);
    $phone = mysqli_real_escape_string($myCon, $_POST['Phone']);
    $email = mysqli_real_escape_string($myCon, $_POST['Email']);
    $address = mysqli_real_escape_string($myCon, $_POST['Address']);
    $custType = mysqli_real_escape_string($myCon, $_POST['CustomerType']);
    $location = mysqli_real_escape_string($myCon, $_POST['Location']);
    $points = isset($_POST['Points']) && $_POST['Points'] !== '' ? (int)$_POST['Points'] : 0;

    // Check duplicate phone (exclude current customer)
    if (!empty($phone)) {
        $checkPhone = mysqli_query($myCon, "SELECT CustomerID FROM Customers WHERE Phone='$phone' AND CustomerID != $customerID");

        if (mysqli_num_rows($checkPhone) > 0) {
            echo "<script>alert('Phone number already exists!'); window.location='customers.php';</script>";
            exit();
        }
    }

    $sql = "UPDATE Customers SET 
                CustomerName = '$custName', Phone = '$phone', Email = '$email', 
                Address = '$address', CustomerType = '$custType', Location = '$location', 
                Points = $points 
            WHERE CustomerID = $customerID";

    if (mysqli_query($myCon, $sql)) {
        $success_message = "Customer Updated Successfully!";
    } else {
        $error_message = "Error: " . mysqli_error($myCon);
    }
}

// ─── DELETE ───
if (isset($_REQUEST['delID'])) {
    $delID = (int)$_REQUEST['delID'];

    $sqlDelete = "DELETE FROM Customers WHERE CustomerID=$delID";
    if ($myCon->query($sqlDelete) === TRUE) {
        $success_message = "Customer Deleted Successfully!";
    } else {
        $error_message = "Error! Cannot delete data.";
    }
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
$countResult = $myCon->query("SELECT COUNT(*) AS total FROM Customers WHERE 1=1 $searchQueryCondition");
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = $showAll ? 1 : ceil($totalRows / $limit);

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
    <title>Customers</title>
    <link rel="icon" type="image/png" href="../img/logo-ang.png">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <link href="https://fonts.googleapis.com/css2?family=Bayon&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="assets/dist/css/customers.css">
    <style>
        /* Reserve scrollbar space so the page doesn't shift width when a modal opens or closes */
        html {
            overflow-y: scroll;
            scrollbar-gutter: stable;
        }

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
        .st-vip{
            background: #f59f0b30;
            color: #f59e0b;
        }
        .st-vip::before {
            background: #f59e0b;
        }

        .st-regular {
            background: rgba(97, 106, 102, 0.12);
            color: #888;
        }
        .st-regular::before{
            background: #888;
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

    <!-- My PAGE CONTENT -->
    <div class="container py-3">

        <!-- Title -->
        <div class="page-title font-bayon">
            បញ្ជីអតិថិជន
        </div>

        <!-- Table Card -->
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
                    <tr class="font-bayon">
                        <th class="text-center">លេខសម្គាល់</th>                        
                        <th class="text-center">ឈ្មោះអតិថិជន</th>
                        <th class="text-center">លេខទូរស័ព្ទ</th>
                        <th class="text-center">អ៊ីមែល</th>
                        <th class="text-center">អាសយដ្ឋាន</th>
                        <th class="text-center">ប្រភេទអតិថិជន</th>
                        <th class="text-center">ទីតាំង</th>
                        <th class="text-center">ពិន្ទុ</th>
                        <th class="text-center">ថ្ងៃចុះឈ្មោះ</th>
                        <th class="text-center">អនុកម្ម</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $limitClause = $showAll ? "" : "LIMIT $limit OFFSET $offset";
                    $sql = "SELECT * FROM Customers WHERE 1=1 $searchQueryCondition ORDER BY CustomerID ASC $limitClause";
                    $qr = $myCon->query($sql);
                    $i = 1;                    
                    $result = mysqli_query($myCon, $sql);                    
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                        <tr class="text-center font-kantumruy">
                            <td><span class="rank-badge"><?= $i++; ?></span></td>
                            <td class="cust-name"><?= $row['CustomerName']; ?></td>
                            <td class="cust-phone"><?= $row['Phone']; ?></td>
                            <td><?= $row['Email']; ?></td>
                            <td><?= $row['Address']; ?></td>
                            <td>
                                <span class="status <?= ($row['CustomerType'] == 'VIP') ? 'status st-vip' : 'status st-regular'; ?>">
                                    <?= $row['CustomerType']; ?>
                                </span>
                            </td>
                            <td><?= $row['Location']; ?></td>
                            <td><?= $row['Points']; ?></td>
                            <td><?= date('d-M-Y / h:i A', strtotime($row['RegisterDate'])); ?></td>

                            <!-- ACTION BUTTONS -->
                            <td>
                                <button class="btn text-white btn-warning btn-sm me-2 btn-edit"
                                    data-id="<?= $row['CustomerID'] ?>"
                                    data-name="<?= htmlspecialchars($row['CustomerName']) ?>"
                                    data-phone="<?= htmlspecialchars($row['Phone']) ?>"
                                    data-email="<?= htmlspecialchars($row['Email']) ?>"
                                    data-address="<?= htmlspecialchars($row['Address']) ?>"
                                    data-type="<?= $row['CustomerType'] ?>"
                                    data-location="<?= $row['Location'] ?>"
                                    data-points="<?= $row['Points'] ?>"
                                    data-bs-toggle="modal" data-bs-target="#editModal">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <a href="customers.php?delID=<?php echo (int)$row['CustomerID']; ?>"
                                    class="btn btn-danger btn-sm font-kantumruy"
                                    onclick="return confirmKantumruy('តើអ្នកពិតជាចង់លុបអតិថិជននេះមែនទេ?', () => { window.location.href = this.href; })">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
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

    <!-- ===================== EDIT MODAL ===================== -->
    <form method="POST" class="modal fade" id="editModal" tabindex="-1" novalidate>
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="font-bayon text-dark mb-0">កែប្រែអតិថិជន</h5>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="CustomerID" id="e_id">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label font-bayon">ឈ្មោះអតិថិជន</label>
                            <input type="text" class="form-control font-kantumruy" id="e_name" name="CustomerName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-bayon">លេខទូរស័ព្ទ</label>
                            <input type="text" class="form-control font-kantumruy" id="e_phone" name="Phone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-bayon">អ៊ីមែល</label>
                            <input type="email" class="form-control font-kantumruy" id="e_email" name="Email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-bayon">ប្រភេទអតិថិជន</label>
                            <select class="form-select font-kantumruy" id="e_type" name="CustomerType">
                                <option value="Regular">Regular</option>
                                <option value="VIP">VIP</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label font-bayon">អាសយដ្ឋាន</label>
                            <textarea class="form-control font-kantumruy" rows="2" id="e_address" name="Address"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-bayon">ទីតាំង</label>
                            <select class="form-select font-kantumruy" id="e_location" name="Location">
                                <option value="ក្នុង">ក្នុង</option>
                                <option value="ក្រៅវ">ក្រៅវ</option>
                                <option value="VIP">VIP</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-bayon">ពិន្ទុ</label>
                            <input type="number" class="form-control font-kantumruy" id="e_points" name="Points">
                        </div>
                    </div>
                </div>
                <div class="modal-footer font-kantumruy">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning" name="btnSaveCustomer">Update</button>
                </div>
            </div>
        </div>
    </form>

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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmKantumruy(message, onYes) {
            Swal.fire({
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'បាទ/ចាស',
                cancelButtonText: 'ទេ',
                confirmButtonColor: '#10b981',
                customClass: {
                    popup: 'font-kantumruy',
                    confirmButton: 'font-kantumruy',
                    cancelButton: 'font-kantumruy'
                }
            }).then((result) => {
                if (result.isConfirmed && typeof onYes === 'function') {
                    onYes();
                }
            });
            return false;
        }

        function notifyKantumruy(message, icon = 'warning') {
            Swal.fire({
                text: message,
                icon: icon,
                confirmButtonText: 'យល់ព្រម',
                confirmButtonColor: '#10b981',
                customClass: {
                    popup: 'font-kantumruy',
                    confirmButton: 'font-kantumruy'
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            let bypassEditValidation = false;
            document.getElementById('editModal').addEventListener('submit', function (e) {
                if (bypassEditValidation) { bypassEditValidation = false; return; }
                e.preventDefault();
                const form = this;
                const name = form.CustomerName.value.trim();

                if (!name) return notifyKantumruy('សូមបញ្ចូលឈ្មោះអតិថិជន!');

                confirmKantumruy('តើអ្នកពិតជាចង់រក្សាទុកការផ្លាស់ប្តូរនេះមែនទេ?', () => {
                    bypassEditValidation = true;
                    form.requestSubmit(form.querySelector('[name="btnSaveCustomer"]'));
                });
            });
        });

        // Change page size -> reload with new limit, reset back to page 1
        function changePageSize(value) {
            var params = new URLSearchParams(window.location.search);
            params.set('limit', value);
            params.set('page', '1');
            window.location.search = params.toString();
        }

        // POPULATE EDIT MODAL WITH ROW DATA
        $('.btn-edit').on('click', function() {
            $('#e_id').val($(this).data('id'));
            $('#e_name').val($(this).data('name'));
            $('#e_phone').val($(this).data('phone'));
            $('#e_email').val($(this).data('email'));
            $('#e_address').val($(this).data('address'));
            $('#e_type').val($(this).data('type'));
            $('#e_location').val($(this).data('location'));
            $('#e_points').val($(this).data('points'));
        });

        // Automatic Toast dismissals
        setTimeout(() => {
            document.querySelectorAll('.smooth-alert').forEach((alert, index) => {
                setTimeout(() => {
                    alert.classList.add('hide');
                    setTimeout(() => alert.remove(), 400);
                }, index * 150);
            });
        }, 1500);

        function applyFilters() {
            var search = document.getElementById('searchInput').value.toLowerCase();

            document.querySelectorAll('.custom-table-scroll table tbody tr').forEach(function(row) {
                var name = row.querySelector('.cust-name')?.textContent.toLowerCase() || '';
                var phone = row.querySelector('.cust-phone')?.textContent.toLowerCase() || '';

                var matchesSearch = !search || name.includes(search) || phone.includes(search);

                row.style.display = matchesSearch ? '' : 'none';
            });
        }
    </script>

</body>

</html>