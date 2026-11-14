<?php
// Place this file next to food.php / drink.php / dessert.php / product.php
// Called by the Edit modal's fillEditForm() via fetch() to load the
// ingredients already linked to a menu item.

include 'include/cn.php';
header('Content-Type: application/json');

$menuItemID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($menuItemID <= 0) {
    echo json_encode([]);
    exit;
}

$stmt = $myCon->prepare("
    SELECT mi.InventoryID, mi.QuantityUsed, i.IngredientName, i.Unit
    FROM MenuItemIngredients mi
    JOIN inventory i ON mi.InventoryID = i.InventoryID
    WHERE mi.MenuItemID = ?
");
$stmt->bind_param("i", $menuItemID);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = [
        'InventoryID'    => (int)$row['InventoryID'],
        'QuantityUsed'   => (float)$row['QuantityUsed'],
        'IngredientName' => $row['IngredientName'],
        'Unit'           => $row['Unit'],
    ];
}
$stmt->close();

echo json_encode($rows);