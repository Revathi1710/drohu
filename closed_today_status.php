<?php
session_start();
require_once __DIR__ . '/connection.php';
header('Content-Type: application/json');

date_default_timezone_set('Asia/Kolkata'); // change if needed

$today     = date('Y-m-d');
$tomorrow  = date('Y-m-d', strtotime('+1 day'));

$out = [
  'ok' => true,
  'isClosedToday' => false,
  'todayReason' => '',
  'isClosedTomorrow' => false,
  'tomorrowReason' => ''
];

$sql = "SELECT date, reason FROM closed_days WHERE date IN (?, ?)";
$stmt = $con->prepare($sql);
$stmt->bind_param('ss', $today, $tomorrow);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
  if ($r['date'] === $today) {
    $out['isClosedToday'] = true;
    $out['todayReason'] = (string)($r['reason'] ?? '');
  } else if ($r['date'] === $tomorrow) {
    $out['isClosedTomorrow'] = true;
    $out['tomorrowReason'] = (string)($r['reason'] ?? '');
  }
}
$stmt->close();

echo json_encode($out);