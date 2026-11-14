<?php

function ensureStockHistoryTable(mysqli $connection): void
{
    $sql = "CREATE TABLE IF NOT EXISTS StockHistory (
        HistoryID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        MenuItemID INT NULL,
        ItemName VARCHAR(255) NOT NULL,
        TransactionType VARCHAR(20) NOT NULL,
        Quantity INT UNSIGNED NOT NULL,
        PreviousStock INT NOT NULL,
        NewStock INT NOT NULL,
        Reason VARCHAR(500) NOT NULL DEFAULT '',
        CreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (HistoryID),
        KEY idx_stock_history_item_date (MenuItemID, CreatedAt),
        KEY idx_stock_history_date (CreatedAt)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if (!$connection->query($sql)) {
        throw new RuntimeException('Unable to prepare stock history: ' . $connection->error);
    }
}

function recordStockHistory(
    mysqli $connection,
    int $menuItemId,
    string $itemName,
    string $transactionType,
    int $quantity,
    int $previousStock,
    int $newStock,
    string $reason = ''
): bool {
    $statement = null;
    try {
        $statement = $connection->prepare(
            'INSERT INTO StockHistory (MenuItemID, ItemName, TransactionType, Quantity, PreviousStock, NewStock, Reason)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        if (!$statement) {
            return false;
        }

        $statement->bind_param(
            'issiiis',
            $menuItemId,
            $itemName,
            $transactionType,
            $quantity,
            $previousStock,
            $newStock,
            $reason
        );

        return $statement->execute();
    } catch (mysqli_sql_exception $exception) {
        return false;
    } finally {
        if ($statement) {
            $statement->close();
        }
    }
}
