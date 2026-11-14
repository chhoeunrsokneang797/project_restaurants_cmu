<?php
include 'include/cn.php';

header('Content-Type: application/json');

// ── Adjust this if your phone column in tblbooking is named differently ──
$phoneColumn = 'Phone';
$nameColumn  = 'CustomerName'; // used only to personalize the success message

$rawPhone = isset($_POST['phone']) ? trim($_POST['phone']) : '';

if ($rawPhone === '') {
    echo json_encode(['success' => false, 'message' => 'Please enter your phone number.']);
    exit;
}

// Normalize: keep digits only, so "012 345 678", "012-345-678", and
// "012345678" are all treated as the same number when comparing.
function normalizePhone($p) {
    return preg_replace('/\D+/', '', $p);
}

$normalizedInput = normalizePhone($rawPhone);

if ($normalizedInput === '') {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid phone number.']);
    exit;
}

// Pull every booked phone number and compare normalized versions in PHP,
// since MySQL would otherwise need an exact (format-sensitive) match.
$sql = "SELECT * FROM tblbooking";
$result = $myCon->query($sql);

if ($result === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Booking lookup failed: ' . mysqli_error($myCon)
    ]);
    exit;
}

$matchedBooking = null;
while ($booking = $result->fetch_assoc()) {
    if (!isset($booking[$phoneColumn])) {
        // Column name doesn't match what's configured above — bail out clearly
        // instead of silently never matching.
        echo json_encode([
            'success' => false,
            'message' => "Server misconfiguration: column '$phoneColumn' not found in tblbooking."
        ]);
        exit;
    }
    if (normalizePhone($booking[$phoneColumn]) === $normalizedInput) {
        $matchedBooking = $booking;
        break;
    }
}

if ($matchedBooking) {
    echo json_encode([
        'success' => true,
        'message' => 'Booking verified! You can now place your order.',
        'name'    => $matchedBooking[$nameColumn] ?? ''
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No table booking found for this phone number. Please book a table first.'
    ]);
}