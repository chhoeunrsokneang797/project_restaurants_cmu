<?php include 'include/cn.php';

if (isset($_POST['btnSavecate'])) {

    $name   = trim($_POST['CategoryName']);
    $status = $_POST['Status'];

    $stmt = $myCon->prepare("INSERT INTO Categories (CategoryName, Status) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $status);

    if ($stmt->execute()) {
        $success_message = "Added Categories Success!";
    } else {
        $error_message = "Error! Cannot insert data: " . $myCon->error;
    }
    $stmt->close();
}

/* UPDATE CATEGORY */
if (isset($_POST['btnUpdateCate'])) {

    $editId = (int) $_POST['CategoryID'];
    $name   = trim($_POST['CategoryName']);
    $status = $_POST['Status'];

    $stmt = $myCon->prepare("UPDATE Categories SET CategoryName = ?, Status = ? WHERE CategoryID = ?");
    $stmt->bind_param("ssi", $name, $status, $editId);

    if ($stmt->execute()) {
        $success_message = "Updated Categories Success!";
    } else {
        $error_message = "Error! Cannot update data: " . $myCon->error;
    }
    $stmt->close();
}

/* DELETE CATEGORY */
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];

    $stmt = $myCon->prepare("DELETE FROM Categories WHERE CategoryID = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $success_message = "Deleted Categories Success!";
    } else {
        $error_message = "Error! Cannot delete data: " . $myCon->error;
    }
    $stmt->close();
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
    $searchQueryCondition = " AND CategoryName LIKE '%$searchTerm%'";
}

// Fetch total row count for calculating structural links with search criteria
$countResult = $myCon->query("SELECT COUNT(*) AS total FROM Categories WHERE 1=1 $searchQueryCondition");
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
    <title>Category</title>
    <link rel="icon" type="image/png" href="../img/logo-ang.png">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <link href="https://fonts.googleapis.com/css2?family=Bayon&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="assets/dist/css/categories.css">
</head>
<style>      
    /* ── Scrollbar ── */
    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #dfe6ef; border-radius: 4px; }
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
    thead th { padding: 12px 22px; text-align: left; font-size: 11px; font-weight: 600; color: var(--text3); letter-spacing: 0.5px; text-transform: uppercase; background: var(--bg3); border-bottom: 1px solid var(--border); }
    tbody tr { border-bottom: 1px solid var(--border); transition: background .15s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: var(--bg3); }
    tbody td { padding: 14px 22px; font-size: 13px; color: #000; }
    .td-main { color: var(--text); font-weight: 500; }
    .td-mono { font-family: var(--mono); font-size: 12px; }
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
    .toast-wrap {
        position: fixed;
        top: 24px;
        right: 24px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .cool-alert {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 320px;
        max-width: 380px;
        padding: 16px 16px 16px 18px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.14), 0 2px 6px rgba(0, 0, 0, 0.06);
        border-left: 5px solid transparent;
        position: relative;
        overflow: hidden;
        transform: translateX(140%);
        opacity: 0;
        animation: slideIn .5s cubic-bezier(.34, 1.56, .64, 1) forwards;
    }

    .cool-alert.success {
        border-left-color: #16a34a;
    }

    .cool-alert.error {
        border-left-color: #dc2626;
    }

    .cool-alert .icon-badge {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: #fff;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    .cool-alert.success .icon-badge {
        background: linear-gradient(135deg, #34d399, #16a34a);
    }

    .cool-alert.error .icon-badge {
        background: linear-gradient(135deg, #f87171, #dc2626);
    }

    .cool-alert .msg {
        flex: 1;
        font-weight: 600;
        font-size: 14.5px;
        color: #1f2937;
        line-height: 1.3;
    }

    .cool-alert .close-btn {
        background: none;
        border: none;
        color: #9ca3af;
        font-size: 14px;
        cursor: pointer;
        padding: 4px 6px;
        line-height: 1;
        transition: color .2s, transform .2s;
    }

    .cool-alert .close-btn:hover {
        color: #374151;
        transform: scale(1.15);
    }

    .cool-alert .progress-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        width: 100%;
        background: rgba(0, 0, 0, 0.06);
    }

    .cool-alert .progress-bar::after {
        content: '';
        display: block;
        height: 100%;
        width: 100%;
        transform-origin: left;
        animation: shrink 1.5s linear forwards;
    }

    .cool-alert.success .progress-bar::after {
        background: linear-gradient(90deg, #34d399, #16a34a);
    }

    .cool-alert.error .progress-bar::after {
        background: linear-gradient(90deg, #f87171, #dc2626);
    }

    .cool-alert.hide {
        animation: slideOut .2s ease forwards;
    }

    @keyframes slideIn {
        from {
            transform: translateX(140%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }

        to {
            transform: translateX(140%);
            opacity: 0;
        }
    }

    @keyframes shrink {
        from {
            transform: scaleX(1);
        }

        to {
            transform: scaleX(0);
        }
    }
</style>
<body>
    <?php include 'include/adminsbd.php'; ?>
    <?php include 'include/adminhd.php'; ?>
    <?php include 'include/smooth_refresh.php'; ?>

    <!-- My PAGE CONTENT -->
    <div class="container py-3">

        <!-- Title -->
        <div class="page-title font-bayon">
            បញ្ជីប្រភេទទំនិញ
        </div>

        <!-- Add Button -->
        <div class="d-flex justify-content-end mb-4">
            <button class="font-bayon create-btn" data-bs-toggle="modal" data-bs-target="#categoryModal">
                បង្កើតប្រភេទទំនិញ
            </button>
        </div>

        <!-- Table Card -->
         <div class="table-card">
            <div class="table-card-header font-kantumruy">                
                <div class="d-flex flex-column flex-md-row align-items-center gap-3">
                    <div class="align-items-center gap-2 font-kantumruy">
                        <label for="pageSizeSelect" class="mb-0 text-nowrap">Show</label>
                        <div style="max-width:260px; position: relative;">                            
                            <select id="pageSizeSelect" class="form-select" onchange="changePageSize(this.value)">
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
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Category Name</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="text-center font-kantumruy">
                    <?php
                    // Dynamic query string including SQL search filter condition and page size
                    $limitClause = $showAll ? "" : "LIMIT $limit OFFSET $offset";
                    $sql = "SELECT * FROM Categories WHERE 1=1 $searchQueryCondition ORDER BY CategoryID ASC $limitClause";
                    $qr = $myCon->query($sql);
                    $i = 1;

                    if (!$qr || $qr->num_rows === 0) {
                    ?>
                    <tr>
                        <td colspan="4" class="text-center text-danger py-4">មិនមានទិន្នន័យសម្រាប់បង្ហាញទេ</td>
                    </tr>
                    <?php
                    } else {
                        while ($row = $qr->fetch_assoc()) {
                    ?>
                    <tr>
                        <td><span class="rank-badge"><?= $i; $i++; ?></span></td>
                        <td class="cat-name"><?= htmlspecialchars($row['CategoryName']); ?></td>
                        <td>
                            <?php if ($row['Status'] == 'Active') { ?>
                                <span class="status st-green" style="font-size: .9rem;">មានក្នុងស្តុក</span>
                            <?php } else { ?>
                                <span class="status st-red" style="font-size: .9rem;">អស់ស្តុក</span>
                            <?php } ?>
                        </td>
                        <td>
                            <button type="button"
                                class="btn text-white btn-warning btn-sm me-2"
                                data-bs-toggle="modal"
                                data-bs-target="#editCategoryModal"
                                onclick="openEditModal(<?= (int)$row['CategoryID']; ?>, '<?= htmlspecialchars($row['CategoryName'], ENT_QUOTES); ?>', '<?= htmlspecialchars($row['Status'], ENT_QUOTES); ?>')">
                                <i class="fas fa-edit"></i>
                            </button>

                            <a href="categories.php?delete_id=<?= (int)$row['CategoryID'] ?>"
                                class="btn btn-danger btn-sm font-kantumruy" onclick="return confirmKantumruy('តើអ្នកពិតជាចង់លុបតុនេះមែនទេ?', () => { window.location.href = this.href; })">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>  
                    <?php
                        }
                    } ?>
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
        </div>

    </div>

    <!-- Create Modal -->
    <form method="POST" enctype="multipart/form-data" class="modal fade" id="categoryModal" novalidate>
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="font-bayon">បង្កើតប្រភេទទំនិញ</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-bayon">ឈ្មោះប្រភេទទំនិញ</label>
                        <input type="text" name="CategoryName" class="form-control font-kantumruy" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-bayon">ស្ថានភាព</label>
                        <select name="Status" class="form-select font-kantumruy">
                            <option value="Active">មានក្នុងស្កុត</option>
                            <option value="Inactive">អស់ស្តុក</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer font-kantumruy">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                    <button type="submit" class="btn btn-primary" name="btnSavecate">Save</button>
                </div>

            </div>

        </div>

    </form>

    <!-- Edit Modal -->
    <form method="POST" enctype="multipart/form-data" class="modal fade" id="editCategoryModal" novalidate>
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="font-kantumruy mb-0">Edit Categories</h5>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="CategoryID" id="editCategoryID">

                    <div class="mb-3">
                        <label class="form-label font-bayon">ឈ្មោះប្រភេទទំនិញ</label>
                        <input type="text" name="CategoryName" id="editCategoryName" class="form-control font-kantumruy" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-bayon">ស្ថានភាព</label>
                        <select name="Status" id="editCategoryStatus" class="form-select font-kantumruy">
                            <option value="Active">មានក្នុងស្កុត</option>
                            <option value="Inactive">អស់ស្តុក</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer font-kantumruy">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                    <button type="submit" class="btn btn-warning" name="btnUpdateCate">Update</button>
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

            // ── Create Employee ──────────────────────────────
            let bypassCreateValidation = false;
            document.getElementById('categoryModal').addEventListener('submit', function (e) {
                if (bypassCreateValidation) { bypassCreateValidation = false; return; }
                e.preventDefault();
                const form = this;
                const name = form.CategoryName.value.trim();

                if (!name) return notifyKantumruy('សូមបញ្ចូលឈ្មោះប្រភេទទំនិញ!');

                confirmKantumruy('តើអ្នកពិតជាចង់បង្កើតប្រភេទទំនិញនេះមែនទេ?', () => {
                    bypassCreateValidation = true;
                    form.requestSubmit(form.querySelector('[name="btnSavecate"]'));
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

            document.querySelectorAll('table tbody tr').forEach(function(row) {
                var nameEl = row.querySelector('.cat-name');
                if (!nameEl) return;
                var name = nameEl.textContent.toLowerCase();
                var matchesSearch = !search || name.includes(search);

                row.style.display = matchesSearch ? '' : 'none';
            });
        }

        function openEditModal(id, name, status) {
            document.getElementById('editCategoryID').value = id;
            document.getElementById('editCategoryName').value = name;
            document.getElementById('editCategoryStatus').value = status;
        }
    </script>
</body>

</html>