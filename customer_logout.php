<?php
include 'include/cn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (!is_string($csrfToken) || !isset($_SESSION['customer_csrf'])
    || !hash_equals($_SESSION['customer_csrf'], $csrfToken)) {
    http_response_code(403);
    exit('Invalid logout request.');
}

unset(
    $_SESSION['customer_id'],
    $_SESSION['customer_name'],
    $_SESSION['customer_phone'],
    $_SESSION['customer_csrf']
);
session_regenerate_id(true);

header('Location: menu.php');
exit;
