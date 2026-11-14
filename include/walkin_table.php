<?php

function resolveTableOrderSession($myCon)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_GET['t'], $_GET['k'])) {
        return null;
    }

    $tableID = (int) $_GET['t'];
    $token   = $_GET['k'];

    if (!$tableID || !verifyTableToken($tableID, $token)) {
        return null; // bad/forged link
    }

    $sessionKey = 'walkin_table_' . $tableID;

    // Guest/staff explicitly leaving this table's order screen.
    if (isset($_GET['clear'])) {
        unset($_SESSION[$sessionKey]);
        return null;
    }

    $stmt = $myCon->prepare("
        SELECT b.BookingID, b.TableID, t.TableName, t.Location,
               COALESCE(c.CustomerName, b.WalkInLabel) AS CustomerName,
               c.Phone
        FROM tablebookings b
        JOIN RestaurantTables t ON b.TableID = t.TableID
        LEFT JOIN Customers c ON b.CustomerID = c.CustomerID
        WHERE b.TableID = ? AND b.BookingStatus = 'CheckedIn'
        ORDER BY b.BookingID DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $tableID);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        // Table isn't currently checked in (guest already left, or QR
        // scanned before staff seated anyone). Clear any stale slot.
        unset($_SESSION[$sessionKey]);
        return null;
    }

    $_SESSION[$sessionKey] = [
        'TableID'      => (int) $row['TableID'],
        'BookingID'    => (int) $row['BookingID'],
        'TableName'    => $row['TableName'],
        'Location'     => $row['Location'],
        'CustomerName' => $row['CustomerName'],
        'Phone'        => $row['Phone'],
    ];

    return $_SESSION[$sessionKey];
}