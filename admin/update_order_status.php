<?php
include 'include/cn.php';

$orderId = $_POST['orderId'];
$status = $_POST['status'];
$reject_message = isset($_POST['reject_message']) ? $_POST['reject_message'] : '';

// First: get the order data
$getOrder = mysqli_query($myCon, "SELECT * FROM orders WHERE OrderID = $orderId");
$order = mysqli_fetch_assoc($getOrder);

// Second: insert into history_orders
$insert = "INSERT INTO history_orders (
    OrderID, CustomerID, customer_name, customer_phone, date, time, 
    persons, EmployeeID, TableID, tableType, OrderDate, OrderType, 
    OrderStatus, PaymentStatus, SubTotal, DiscountAmount, TaxAmount, 
    GrandTotal, DeliveryAddress, Note, message, reject_message, ArchivedDate
) VALUES (
    '" . $order['OrderID'] . "', '" . $order['CustomerID'] . "', '" . $order['customer_name'] . "', 
    '" . $order['customer_phone'] . "', '" . $order['date'] . "', '" . $order['time'] . "', 
    '" . $order['persons'] . "', '" . $order['EmployeeID'] . "', '" . $order['TableID'] . "', 
    '" . $order['tableType'] . "', '" . $order['OrderDate'] . "', '" . $order['OrderType'] . "', 
    '$status', '" . $order['PaymentStatus'] . "', '" . $order['SubTotal'] . "', 
    '" . $order['DiscountAmount'] . "', '" . $order['TaxAmount'] . "', '" . $order['GrandTotal'] . "', 
    '" . $order['DeliveryAddress'] . "', '" . $order['Note'] . "', '" . $order['message'] . "', 
    '" . $order['reject_message'] . "', NOW()
)";

mysqli_query($myCon, $insert);

// Third: update status in orders table
$update = "UPDATE orders SET OrderStatus = '$status' WHERE OrderID = $orderId";
mysqli_query($myCon, $update);

// Fourth: delete from orders table (so it moves to history)
mysqli_query($myCon, "DELETE FROM orders WHERE OrderID = $orderId");

echo "Success";

mysqli_close($myCon);
?>