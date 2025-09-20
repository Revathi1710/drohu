<?php
session_start();
require_once __DIR__ . '/connection.php';

header('Content-Type: application/json');

function respond($ok, $extra = []) {
  echo json_encode(array_merge(['ok' => $ok], $extra));
  exit;
}

// Optional filters: from=YYYY-MM-DD, to=YYYY-MM-DD, limit=int
$from  = isset($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from']) ? $_GET['from'] : '';
$to    = isset($_GET['to'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to'])   ? $_GET['to']   : '';
$limit = isset($_GET['limit']) ? max(1, min((int)$_GET['limit'], 1000)) : 365;

$where = [];
$params = [];
$types  = '';

if ($from !== '') { $where[] = 'date >= ?'; $params[] = $from; $types .= 's'; }
if ($to   !== '') { $where[] = 'date <= ?'; $params[] = $to;   $types .= 's'; }

$sql = 'SELECT date, reason FROM closed_days';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY date DESC LIMIT ?';
$params[] = $limit; $types .= 'i';

$stmt = $con->prepare($sql);
if (!$stmt) respond(false, ['error' => 'prepare_failed']);

$stmt->bind_param($types, ...$params);
if (!$stmt->execute()) {
  $stmt->close();
  respond(false, ['error' => 'query_failed']);
}

$res = $stmt->get_result();
$days = [];
while ($row = $res->fetch_assoc()) {
  $days[] = [
    'date'   => $row['date'],
    'reason' => $row['reason'] ?? ''
  ];
}
$stmt->close();

respond(true, ['days' => $days]);