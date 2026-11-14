<?php include '../include/cn.php';

$catResult = $myCon->query("SELECT CategoryID FROM Categories WHERE CategoryName = 'Food'");
if ($catResult && $catResult->num_rows > 0) {
    $catRow = $catResult->fetch_assoc();
    $foodCatID = (int)$catRow['CategoryID'];
} else {
    $foodCatID = 0; // Fallback to safe integer value
}

// Create food
if (isset($_POST['btnAddFood'])) {
    $ItemName = $myCon->real_escape_string($_POST['ItemName']);
    $CategoryID = $foodCatID;
    $Price = $myCon->real_escape_string($_POST['Price']);
    $StockQty = $myCon->real_escape_string($_POST['StockQty']);
    $Status = $myCon->real_escape_string($_POST['Status']);
    $Image = $_FILES['Image']['name'];
    $ImageTmp = $_FILES['Image']['tmp_name'];
    $newImgName = date("Y_m_d_H_i_s") . '_' . rand(10, 100) . '_' . $Image;

    if (!empty($Image)) {
        $sqlInsert = "INSERT INTO MenuItems (ItemName, CategoryID, Price, StockQty, Status, Image) VALUES ('$ItemName','$CategoryID', '$Price', '$StockQty', '$Status', '$newImgName')";
        move_uploaded_file($ImageTmp, '../images/trash/' . $newImgName);
    } else {
        $sqlInsert = "INSERT INTO MenuItems (ItemName, CategoryID, Price, StockQty, Status, Image) VALUES ('$ItemName', '$CategoryID', '$Price', '$StockQty', '$Status', 'blank.png')";
    }

    if ($myCon->query($sqlInsert) === TRUE) {
        $success_message = "Add Food Success!";
    } else {
        $error_message = "Error! Cannot insert data: " . $myCon->error;
    }
}

// Update food
if (isset($_POST['btnUpdate'])) {
    $foodID = (int)$_POST['foodID'];
    $ItemName = $myCon->real_escape_string($_POST['ItemName']);
    $CategoryID = (int)$_POST['CategoryID'];
    $Price = $myCon->real_escape_string($_POST['Price']);
    $StockQty = $myCon->real_escape_string($_POST['StockQty']);
    $Status = $myCon->real_escape_string($_POST['Status']);
    $Image = $_FILES['Image']['name'];
    $ImageTmp = $_FILES['Image']['tmp_name'];
    $newImgName = date("Y_m_d_H_i_s") . '_' . rand(10, 100) . '_' . $Image;

    if (!empty($Image)) {
        $getImage = $myCon->query("SELECT * FROM MenuItems WHERE MenuItemID=$foodID")->fetch_assoc();
        if ($getImage && $getImage['Image'] != 'blank.png') {
            unlink("../images/trash/" . $getImage['Image']);
        }
        move_uploaded_file($ImageTmp, '../images/trash/' . $newImgName);
        $sqlUpdate = "UPDATE MenuItems SET ItemName='$ItemName', CategoryID='$CategoryID', Price='$Price', StockQty='$StockQty', Status='$Status', Image='$newImgName' WHERE MenuItemID=$foodID";
    } else {
        $sqlUpdate = "UPDATE MenuItems SET ItemName='$ItemName', CategoryID='$CategoryID', Price='$Price', StockQty='$StockQty', Status='$Status' WHERE MenuItemID=$foodID";
    }

    if ($myCon->query($sqlUpdate) === TRUE) {
        $success_message = "Update Food Success!";
    } else {
        $error_message = "Error! Cannot update data: " . $myCon->error;
    }
}

// Delete food via POST
if (isset($_POST['btnDelete']) && isset($_POST['delID'])) {
    $delID = (int)$_POST['delID'];
    $getImage = $myCon->query("SELECT Image FROM MenuItems WHERE MenuItemID=$delID")->fetch_assoc();
    if ($getImage && $getImage['Image'] != 'blank.png') {
        @unlink("../images/trash/" . $getImage['Image']);
    }
    $sqlDelete = "DELETE FROM MenuItems WHERE MenuItemID=$delID";
    if ($myCon->query($sqlDelete) === TRUE) {
        $success_message = "Delete Food Success!";
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
    $searchQueryCondition = " AND ItemName LIKE '%$searchTerm%'";
}

// Fetch total row count for calculating structural links with search criteria
$countResult = $myCon->query("SELECT COUNT(*) AS total FROM MenuItems WHERE CategoryID = $foodCatID $searchQueryCondition");
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
    <title>Food</title>
    <link rel="icon" type="image/png" href="../../img/logo-ang.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Bayon&family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="../assets/dist/css/food.css">
</head>
<style>
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

    #pageSizeSelect {
        width: auto;
        min-width: 90px;
    }
    .font-bayon{
        font-family: 'Bayon', sans-serif;
    }
    .font-kantumruy{
        font-family: "Kantumruy Pro", sans-serif;
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
</style>

<body>
    <?php include '../include/administratorbd.php'; ?>
    <?php include '../include/administratorhd.php'; ?>
    <?php include '../include/smooth_refresh.php'; ?>

    <div class="container py-3">
        <div class="page-title font-bayon">បញ្ជីទំនិញអាហារ</div>        

        <div class="table-card">
            <div class="table-card-header font-kantumruy">                
                <div class="d-flex flex-column flex-md-row align-items-center gap-3">
                    <div class="align-items-center gap-2 font-kantumruy">
                        <label for="pageSizeSelect" class="mb-0 text-nowrap">Show</label>
                        <div style="max-width:260px; position: relative;">                            
                            <select id="pageSizeSelect" class="form-select" onchange="changePageSize(this.value)" style="padding-right: 0;">
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
                        <th class="text-center">Image</th>
                        <th class="text-center">Food Name</th>
                        <th class="text-center">Category</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Status</th>
                        <!-- <th class="text-center">Action</th> -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Dynamic query string including SQL search filter condition and page size
                    $limitClause = $showAll ? "" : "LIMIT $limit OFFSET $offset";
                    $sql = "SELECT * FROM MenuItems WHERE CategoryID = $foodCatID $searchQueryCondition ORDER BY MenuItemID ASC $limitClause";
                    $qr = $myCon->query($sql);
                    $i = 1;

                    if ($qr && $qr->num_rows > 0) {
                        while ($row = $qr->fetch_assoc()) {
                            $catIdQuery = (int)$row['CategoryID'];
                            $rowCategory = $myCon->query("SELECT CategoryName FROM Categories WHERE CategoryID=$catIdQuery")->fetch_assoc();
                            $categoryName = $rowCategory ? $rowCategory['CategoryName'] : 'Unassigned';
                            $badgeClass = ($row["Status"] == "Available") ? "status st-green" : "status st-red";

                            $escapedName = addslashes(htmlspecialchars($row['ItemName'], ENT_QUOTES, 'UTF-8'));
                            $escapedStatus = addslashes(htmlspecialchars($row['Status'], ENT_QUOTES, 'UTF-8'));
                            $escapedImage = addslashes(htmlspecialchars($row['Image'], ENT_QUOTES, 'UTF-8'));
                    ?>
                            <tr class="text-center font-kantumruy">
                                <td><span class="rank-badge"><?php echo $i; $i++; ?></span></td>
                                <td>
                                    <img src="../../images/trash/<?php echo htmlspecialchars($row['Image'], ENT_QUOTES, 'UTF-8'); ?>"
                                        width="60"
                                        height="60"
                                        style="object-fit:cover; border-radius:8px;"
                                        alt="Food Image" />
                                </td>
                                <td class="font-kantumruy item-name"><?php echo htmlspecialchars($row['ItemName'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>$ <?php echo htmlspecialchars($row['Price'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['StockQty'], ENT_QUOTES, 'UTF-8'); ?> pcs </td>
                                <td>
                                    <span class="status <?php echo $badgeClass; ?>">
                                        <?php echo ($row['Status'] == 'Available') ? 'មានក្នុងស្តុក' : 'អស់ស្តុក'; ?>
                                    </span>
                                </td>
                                <!-- <td>
                                    <button type="button"
                                        class="btn btn-sm btn-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editFoodModal"
                                        onclick="fillEditForm(<?php echo (int)$row['MenuItemID']; ?>, '<?php echo $escapedName; ?>', '<?php echo $catIdQuery; ?>', '<?php echo (float)$row['Price']; ?>', '<?php echo (int)$row['StockQty']; ?>', '<?php echo $escapedStatus; ?>', '<?php echo $escapedImage; ?>')">
                                        <i class="fas fa-edit text-white"></i>
                                    </button>

                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete <?php echo $escapedName; ?>?');">
                                        <input type="hidden" name="delID" value="<?php echo (int)$row['MenuItemID']; ?>">
                                        <button type="submit" name="btnDelete" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td> -->
                            </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="8" class="text-center py-4 text-muted">No foods found matching your criteria.</td></tr>';
                    }
                    ?>
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

    <form method="POST" enctype="multipart/form-data" class="modal fade" id="foodModal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="font-kantumruy mb-0">Create Foods</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-bayon">ឈ្មោះអាហារ</label>
                        <input type="text" name="ItemName" class="form-control font-kantumruy" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-bayon">ប្រភេទទំនិញ</label>
                        <select name="CategoryID" class="form-select font-kantumruy">
                            <?php
                            $sql = "SELECT * FROM Categories ORDER BY CategoryID ASC";
                            $qr = $myCon->query($sql);
                            while ($rowCategory = $qr->fetch_assoc()) {
                                echo '<option value="' . $rowCategory['CategoryID'] . '"> ' . htmlspecialchars($rowCategory['CategoryName']) . ' </option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-bayon">តម្លៃអាហារ</label>
                        <div style="position: relative;" class="font-kantumruy">
                            <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%);">$</span>
                            <input type="number" step="1.00" min="0" name="Price" class="form-control" placeholder="0.00" style="padding-left: 28px;" required>
                        </div>
                        <!-- <input type="number" step="0.01" name="Price" class="form-control font-kantumruy" placeholder="$" required> -->
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-bayon">ចំនួនស្តុក</label>
                        <div style="position: relative;" class="font-kantumruy">
                            <span style="position:absolute; left:10px; top:50%; font-size: 12px; transform:translateY(-50%);">PCS</span>
                            <input type="number" name="StockQty" class="form-control" placeholder="0" style="padding-left: 40px;" required>
                        </div>
                        <!-- <input type="number" name="StockQty" class="form-control font-kantumruy" placeholder="0" required> -->
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-bayon">ស្ថានភាព</label>
                        <select name="Status" class="form-select font-kantumruy">
                            <option value="Available">មានក្នុងស្តុក</option>
                            <option value="Unavailable">អស់ស្តុក</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-bayon">រូបភាព</label>
                        <input type="file" class="form-control font-kantumruy" name="Image" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer font-kantumruy">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                    <button type="submit" class="btn btn-primary" name="btnAddFood">Save</button>
                </div>
            </div>
        </div>
    </form>

    <form method="POST" enctype="multipart/form-data" class="modal fade" id="editFoodModal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="font-kantumruy mb-0">Edit Foods</h5>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="foodID" id="editFoodID">
                        <div class="mb-3 col-6">
                            <label class="form-label font-bayon">ឈ្មោះអាហារ</label>
                            <input type="text" name="ItemName" id="editItemName" class="form-control font-kantumruy" required>
                        </div>
                        <div class="mb-3 col-6">
                            <label class="form-label font-bayon">ប្រភេទទំនិញ</label>
                            <select name="CategoryID" id="editCategoryID" class="form-select font-kantumruy">
                                <?php
                                $sql = "SELECT * FROM Categories ORDER BY CategoryID ASC";
                                $qr = $myCon->query($sql);
                                while ($rowCategory = $qr->fetch_assoc()) {
                                    echo '<option value="' . $rowCategory['CategoryID'] . '"> ' . htmlspecialchars($rowCategory['CategoryName']) . ' </option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3 col-6">
                            <label class="form-label font-bayon">តម្លៃអាហារ</label>
                            <div style="position: relative;" class="font-kantumruy">
                                <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%);">$</span>
                                <input type="number" step="1.00" min="0" name="Price" id="editPrice" class="form-control" style="padding-left: 28px;" required>
                            </div>
                            <!-- <input type="number" step="0.01" name="Price" id="editPrice" class="form-control font-kantumruy" required> -->
                        </div>
                        <div class="mb-3 col-6">
                            <label class="form-label font-bayon">ចំនួនស្តុក</label>
                            <div style="position: relative;" class="font-kantumruy">
                                <span style="position:absolute; left:10px; top:50%; font-size: 12px; transform:translateY(-50%);">PCS</span>
                                <input type="number" name="StockQty" id="editStockQty" class="form-control" style="padding-left: 40px;" required>
                            </div>
                            <!-- <input type="number" name="StockQty" id="editStockQty" class="form-control font-kantumruy" required> -->
                        </div>
                        <div class="mb-3">
                            <label class="form-label font-bayon">ស្ថានភាព</label>
                            <select name="Status" id="editStatus" class="form-select font-kantumruy">
                                <option value="Available">មានក្នុងស្តុក</option>
                                <option value="Unavailable">អស់ស្តុក</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label font-bayon">រូបភាព</label>
                            <input type="file" class="form-control font-kantumruy" name="Image" id="editImageInput" accept="image/*">
                            <div class="mt-2">
                                <img src="" id="editImagePreview" width="80" height="80" style="object-fit:cover;border-radius:8px; display:none;">
                                <span id="editImageLabel" class="text-muted d-block small" style="display:none;">Current Image</span>
                            </div>
                        </div>
                    </div>                    
                </div>
                <div class="modal-footer font-kantumruy">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning" name="btnUpdate">Update</button>
                </div>
            </div>
        </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function applyFilters() {
            var search = document.getElementById('searchInput').value.toLowerCase();

            document.querySelectorAll('.custom-table-scroll table tbody tr').forEach(function(row) {
                var name = row.querySelector('.item-name')?.textContent.toLowerCase();
                var matchesSearch = !search || name.includes(search);

                row.style.display = matchesSearch ? '' : 'none';
            });
        }

        // Change page size -> reload with new limit, reset back to page 1
        function changePageSize(value) {
            var params = new URLSearchParams(window.location.search);
            params.set('limit', value);
            params.set('page', '1');
            window.location.search = params.toString();
        }

        // --- Existing UI Logic Elements ---
        function fillEditForm(id, name, catID, price, stock, status, image) {
            document.getElementById('editFoodID').value = id;
            document.getElementById('editItemName').value = name;
            document.getElementById('editCategoryID').value = catID;
            document.getElementById('editPrice').value = price;
            document.getElementById('editStockQty').value = stock;
            document.getElementById('editStatus').value = status;

            const preview = document.getElementById('editImagePreview');
            const label = document.getElementById('editImageLabel');

            if (image && image !== 'blank.png') {
                preview.src = '../images/trash/' + image;
                preview.style.display = 'inline-block';
                label.style.display = 'block';
            } else {
                preview.style.display = 'none';
                label.style.display = 'none';
            }
        }

        const editImageInput = document.getElementById('editImageInput');
        const editImagePreview = document.getElementById('editImagePreview');

        if (editImageInput) {
            editImageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        editImagePreview.src = e.target.result;
                        editImagePreview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                }
            });
        }

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