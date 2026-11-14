<?php 
include 'include/cn.php';

$orderId = $_POST['orderId'];
$status = $_POST['status'];

// Update status only
$sql = "UPDATE orders SET OrderStatus = '$status' WHERE OrderID = $orderId";

if (mysqli_query($myCon, $sql)) {
    echo "Success";
} else {
    echo "Error: " . mysqli_error($myCon);
}

mysqli_close($myCon);

// Get orders older than 24 hours
$sql = "SELECT * FROM orders WHERE OrderDate < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
$result = mysqli_query($myCon, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    // Insert into history_orders
    $insert = "INSERT INTO history_orders (
        OrderID, CustomerID, customer_name, customer_phone, date, time, 
        persons, EmployeeID, TableID, tableType, OrderDate, OrderType, 
        OrderStatus, PaymentStatus, SubTotal, DiscountAmount, TaxAmount, 
        GrandTotal, DeliveryAddress, Note, message, reject_message, ArchivedDate
    ) VALUES (
        '".$row['OrderID']."', '".$row['CustomerID']."', '".$row['customer_name']."', 
        '".$row['customer_phone']."', '".$row['date']."', '".$row['time']."', 
        '".$row['persons']."', '".$row['EmployeeID']."', '".$row['TableID']."', 
        '".$row['tableType']."', '".$row['OrderDate']."', '".$row['OrderType']."', 
        '".$row['OrderStatus']."', '".$row['PaymentStatus']."', '".$row['SubTotal']."', 
        '".$row['DiscountAmount']."', '".$row['TaxAmount']."', '".$row['GrandTotal']."', 
        '".$row['DeliveryAddress']."', '".$row['Note']."', '".$row['message']."', 
        '".$row['reject_message']."', NOW()
    )";
    
    mysqli_query($myCon, $insert);
    mysqli_query($myCon, "DELETE FROM orders WHERE OrderID = ".$row['OrderID']);
}

mysqli_close($myCon);
?>