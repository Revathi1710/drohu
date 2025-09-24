<?php
session_start();
require_once __DIR__ . '/connection.php';
header('Content-Type: application/json');

$in = json_decode(file_get_contents('php://input'), true);
$msg = trim($in['message'] ?? '');
if ($msg === '') {
    echo json_encode(['ok' => false, 'error' => 'invalid_args']);
    exit;
}

// Fetch users
$res = $con->query("SELECT user_country_code, mobile_number FROM users WHERE mobile_number <> ''");
$sent = 0;

if ($res) {
    while ($r = $res->fetch_assoc()) {
        // TODO: send via provider
        $sent++;
    }
}

// Insert broadcast record
$stmt = $con->prepare("INSERT INTO broadcasts (message, sent_count) VALUES (?, ?)");
$stmt->bind_param('si', $msg, $sent);
$stmt->execute();
$stmt->close();

echo json_encode(['ok' => true, 'sent_count' => $sent]);
