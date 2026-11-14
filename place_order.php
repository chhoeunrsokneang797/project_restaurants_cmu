<?php
include 'include/cn.php';
include 'include/walkin.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$customerID = (int) ($_SESSION['customer_id'] ?? 0);
if ($customerID <= 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please sign in before placing an order.']);
    exit;
}

$customerStmt = $myCon->prepare(
    'SELECT CustomerID, CustomerName, Phone, Address
     FROM Customers
     WHERE CustomerID = ?
     LIMIT 1'
);
$customerStmt->bind_param('i', $customerID);
$customerStmt->execute();
$customer = $customerStmt->get_result()->fetch_assoc();
$customerStmt->close();

if (!$customer) {
    unset($_SESSION['customer_id'], $_SESSION['customer_name'], $_SESSION['customer_phone']);
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Your customer account could not be verified. Please sign in again.']);
    exit;
}

$claimedTableId = (int) ($data['tableId'] ?? 0);
$walkin = $claimedTableId ? ($_SESSION['walkin_table_' . $claimedTableId] ?? null) : null;

if ($walkin && (int) $walkin['TableID'] !== $claimedTableId) {
    $walkin = null;
}

// Sanitize inputs
if ($walkin) {
    $customerName = $myCon->real_escape_string($walkin['CustomerName'] ?: 'Walk-in Guest');
    $phone        = $myCon->real_escape_string($walkin['Phone'] ?: '');
    $address      = '';
} else {
    $customerName  = $myCon->real_escape_string($customer['CustomerName']);
    $phone         = $myCon->real_escape_string($customer['Phone'] ?? '');
    $address       = $myCon->real_escape_string($customer['Address'] ?? '');
}

$orderTypeRaw  = $walkin ? 'Dine In' : ($data['orderType'] ?? 'Dine In');
$orderTypeMap = [
    'Dine In'   => 'Dine In',
    'Take Away' => 'Takeout',
    'Takeout'   => 'Takeout',
    'Delivery'  => 'Delivery',
];
$orderType = $myCon->real_escape_string($orderTypeMap[$orderTypeRaw] ?? 'Dine In');

if (!$walkin && $orderTypeRaw === 'Delivery') {
    $deliveryAddress = $data['address'] ?? '';
    if (!is_string($deliveryAddress) || trim($deliveryAddress) === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Enter a delivery address.']);
        exit;
    }
    $address = $myCon->real_escape_string(trim($deliveryAddress));
}

$paymentMethodRaw = $data['paymentMethod'] ?? 'Cash';
if (!is_string($paymentMethodRaw) || !in_array($paymentMethodRaw, ['Cash', 'KHQR'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Choose a valid payment method.']);
    exit;
}
$paymentMethod = $myCon->real_escape_string($paymentMethodRaw);
$paymentStatus = 'Unpaid';
$items         = $data['items'] ?? [];

if ($walkin) {
    $tableId    = (int) $walkin['TableID'];
    $tableLabel = $myCon->real_escape_string($walkin['TableName'] . ' ' . $walkin['Location']);
} else {
    $tableIdRaw = $data['tableId'] ?? '';
    $tableId    = ($tableIdRaw !== '' && $tableIdRaw !== null) ? (int) $tableIdRaw : null;
    $tableLabel = $myCon->real_escape_string($data['tableLabel'] ?? '');
}
$tableType = $tableLabel !== '' ? $tableLabel : 'N/A';

if ($walkin) {
    if (empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
} else {
    if (empty($customerName) || empty($phone) || empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
}

$reserveTableForOrder = !$walkin && $orderTypeRaw === 'Dine In';
if ($reserveTableForOrder && (!$tableId || $tableId < 1)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Choose an available table for dine-in.']);
    exit;
}

// Calculate totals
$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['price'] * $item['qty'];
}
$tax        = round($subtotal * 0.10, 2);
$grandTotal = round($subtotal + $tax, 2);
$subtotal   = round($subtotal, 2);

$today = date('Y-m-d');
$now   = date('H:i:s');

$tableIdSql = $tableId !== null ? (int)$tableId : 'NULL';

if ($reserveTableForOrder) {
    $myCon->begin_transaction();

    $tableStmt = $myCon->prepare(
        'SELECT TableName, Location, Status
         FROM RestaurantTables
         WHERE TableID = ?
         FOR UPDATE'
    );
    $tableStmt->bind_param('i', $tableId);
    $tableStmt->execute();
    $table = $tableStmt->get_result()->fetch_assoc();
    $tableStmt->close();

    if (!$table || $table['Status'] !== 'Available') {
        $myCon->rollback();
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'That table is no longer available. Please choose another table.']);
        exit;
    }

    $tableLabel = $myCon->real_escape_string(trim($table['TableName'] . ' ' . ($table['Location'] ?? '')));
    $tableType = $tableLabel;
}

$sqlOrder = "INSERT INTO Orders 
    (CustomerID, customer_name, customer_phone, date, time, OrderType, PaymentMethod, PaymentStatus,
     SubTotal, TaxAmount, GrandTotal, DeliveryAddress, OrderStatus, TableID, tableType)
    VALUES
    ($customerID, '$customerName', '$phone', '$today', '$now', '$orderType', '$paymentMethod', '$paymentStatus',
     '$subtotal', '$tax', '$grandTotal', '$address', 'Pending', $tableIdSql, '$tableType')";

if (!$myCon->query($sqlOrder)) {
    if ($reserveTableForOrder) {
        $myCon->rollback();
    }
    echo json_encode(['success' => false, 'message' => 'Order failed: ' . $myCon->error]);
    exit;
}

$orderID = $myCon->insert_id;

if ($reserveTableForOrder) {
    $bookingStatus = 'Reserved';
    $bookingStmt = $myCon->prepare(
        'INSERT INTO TableBookings
            (CustomerID, TableID, BookingDate, BookingTime, ContactPhone, BookingStatus)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $bookingStmt->bind_param(
        'iissss',
        $customerID,
        $tableId,
        $today,
        $now,
        $phone,
        $bookingStatus
    );
    $bookingCreated = $bookingStmt->execute();
    $bookingStmt->close();

    $tableUpdate = $myCon->prepare(
        "UPDATE RestaurantTables SET Status = 'Reserved' WHERE TableID = ? AND Status = 'Available'"
    );
    $tableUpdate->bind_param('i', $tableId);
    $tableUpdated = $tableUpdate->execute() && $tableUpdate->affected_rows === 1;
    $tableUpdate->close();

    if (!$bookingCreated || !$tableUpdated) {
        $myCon->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'The order could not reserve the selected table. Please try again.']);
        exit;
    }

    $myCon->commit();
}

$mergedItems = [];
foreach ($items as $item) {
    $key = strtolower(trim($item['name'] ?? ''));
    if ($key === '') continue;

    if (isset($mergedItems[$key])) {
        $mergedItems[$key]['qty'] += (int)$item['qty'];
    } else {
        $mergedItems[$key] = [
            'name'  => $item['name'],
            'price' => (float)$item['price'],
            'qty'   => (int)$item['qty'],
        ];
    }
}

foreach ($mergedItems as $item) {
    $itemName  = $myCon->real_escape_string($item['name']);
    $itemPrice = (float)$item['price'];
    $itemQty   = (int)$item['qty'];
    $itemSubTotal = round($itemPrice * $itemQty, 2);

    // Look up MenuItemID by name
    $menuRes = $myCon->query("SELECT MenuItemID FROM MenuItems WHERE ItemName = '$itemName' LIMIT 1");

    if ($menuRes && $menuRes->num_rows > 0) {
        $menuRow    = $menuRes->fetch_assoc();
        $menuItemID = $menuRow['MenuItemID'];

        $sqlDetail = "INSERT INTO OrderDetails 
            (OrderID, MenuItemID, Quantity, UnitPrice, SubTotal)
            VALUES
            ('$orderID', '$menuItemID', '$itemQty', '$itemPrice', '$itemSubTotal')";

        if (!$myCon->query($sqlDetail)) {
            // Log but don't stop — order is already created
            error_log('OrderDetail insert failed: ' . $myCon->error);
        }
    } else {
        // Item not found in MenuItems — log it
        error_log("Menu item not found: $itemName");
    }
}

echo json_encode(['success' => true, 'orderID' => $orderID]);