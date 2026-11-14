<?php
/*
    Walk-in table session helper.

    A QR code printed/stuck on each physical table encodes a URL like:
        menu.php?t=5&k=<token>

    Scanning it lets a seated walk-in guest order from their own phone
    without typing name/phone/email — the table itself already tells us
    who they are, because tblbooking.php created a 'CheckedIn' booking
    row for that TableID when staff seated them.

    IMPORTANT: change QR_SECRET to your own long random string, and keep
    it out of version control in a real deployment (e.g. load from an
    environment variable or a config file outside the web root).
*/

define('QR_SECRET', 'CHANGE_THIS_TO_A_LONG_RANDOM_SECRET_STRING');

// Deterministic token for a given TableID. Guests can't forge a token for
// a table they weren't actually given a QR code for, since they don't
// know QR_SECRET.
function makeTableToken($tableID)
{
    return substr(hash_hmac('sha256', (string) $tableID, QR_SECRET), 0, 16);
}

function verifyTableToken($tableID, $token)
{
    if (!is_string($token) || $token === '') {
        return false;
    }
    return hash_equals(makeTableToken($tableID), $token);
}

/**
 * Call once near the top of any customer-facing page (menu.php, etc.)
 * before any HTML output. Returns the active walk-in session array, or
 * null if the visitor is a normal online customer.
 */
function initWalkInSession($myCon)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Guest chose to leave walk-in mode (e.g. wants to order for later /
    // delivery instead) -- simple manual escape hatch.
    if (isset($_GET['clear'])) {
        unset($_SESSION['walkin']);
    }

    if (isset($_GET['t'], $_GET['k'])) {
        $tableID = (int) $_GET['t'];
        $token   = $_GET['k'];

        if ($tableID && verifyTableToken($tableID, $token)) {
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

            if ($row) {
                $_SESSION['walkin'] = [
                    'TableID'      => (int) $row['TableID'],
                    'BookingID'    => (int) $row['BookingID'],
                    'TableName'    => $row['TableName'],
                    'Location'     => $row['Location'],
                    'CustomerName' => $row['CustomerName'],
                    'Phone'        => $row['Phone'],
                ];
            }
            // If the table has no active CheckedIn booking (guest already
            // checked out, or the QR was scanned before staff seated them),
            // we silently ignore it and fall back to normal online-order mode
            // rather than showing a scary error to the guest.
        }
    }

    return $_SESSION['walkin'] ?? null;
}