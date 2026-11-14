<?php
include 'include/cn.php';
include 'include/walkin.php';

$tables = $myCon->query("SELECT TableID, TableName, Location FROM RestaurantTables ORDER BY TableName");

$baseUrl = 'https://Final_Project/table_order.php';

echo "<h2>Table Order Links</h2><table border='1' cellpadding='8'>";
echo "<tr><th>Table</th><th>Link to encode as QR code</th></tr>";

while ($row = $tables->fetch_assoc()) {
    $token = makeTableToken($row['TableID']);
    $link  = $baseUrl . '?t=' . $row['TableID'] . '&k=' . $token;
    echo "<tr><td>" . htmlspecialchars($row['TableName'] . ' ' . $row['Location']) . "</td>";
    echo "<td><a href='" . htmlspecialchars($link) . "' target='_blank'>" . htmlspecialchars($link) . "</a></td></tr>";
}

echo "</table>";