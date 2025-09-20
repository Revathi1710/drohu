<?php
session_start();
require_once __DIR__ . '/connection.php';

header('Content-Type: application/json');

function respond($ok, $extra = []) {
  echo json_encode(array_merge(['ok' => $ok], $extra));
  exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

// Support form-encoded fallback
$date   = trim($data['date']   ?? ($_POST['date']   ?? ''));
$reason = trim($data['reason'] ?? ($_POST['reason'] ?? ''));

// Validate date: YYYY-MM-DD
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
  respond(false, ['error' => 'invalid_date']);
}

if (mb_strlen($reason) > 200) {
  $reason = mb_substr($reason, 0, 200);
}

$sql = "INSERT INTO closed_days (`date`, `reason`)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE `reason` = VALUES(`reason`)";

$stmt = $con->prepare($sql);
if (!$stmt) {
  respond(false, ['error' => 'prepare_failed']);
}

$stmt->bind_param('ss', $date, $reason);
if (!$stmt->execute()) {
  $stmt->close();
  respond(false, ['error' => 'save_failed']);
}

$stmt->close();
respond(true);