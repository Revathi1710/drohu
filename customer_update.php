<?php
session_start();
require_once __DIR__ . '/connection.php';
header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$id   = (int)($data['id'] ?? 0);
$name = trim($data['name'] ?? '');
$mob  = trim($data['mobile_number'] ?? '');
$email= trim($data['email'] ?? '');

if ($id <= 0) { echo json_encode(['ok'=>false,'error'=>'invalid_id']); exit; }
if ($name === '' || mb_strlen($name) > 120) { echo json_encode(['ok'=>false,'error'=>'invalid_name']); exit; }
if (!preg_match('/^\d{10}$/', $mob)) { echo json_encode(['ok'=>false,'error'=>'invalid_mobile']); exit; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['ok'=>false,'error'=>'invalid_email']); exit; }

// Optional: prevent duplicate mobile/email across users (excluding current)
$dup = $con->prepare("SELECT id FROM users WHERE (mobile_number = ? OR email = ?) AND id <> ? LIMIT 1");
$dup->bind_param('ssi', $mob, $email, $id);
$dup->execute();
$hasDup = $dup->get_result()->fetch_assoc();
$dup->close();
if ($hasDup) { echo json_encode(['ok'=>false,'error'=>'mobile_or_email_exists']); exit; }

$sql = "UPDATE users SET name = ?, mobile_number = ?, email = ? WHERE id = ? LIMIT 1";
$stmt = $con->prepare($sql);
$stmt->bind_param('sssi', $name, $mob, $email, $id);
if ($stmt->execute()) {
  $stmt->close();
  echo json_encode(['ok'=>true,'user'=>['id'=>$id,'name'=>$name,'mobile_number'=>$mob,'email'=>$email]]);
} else {
  $stmt->close();
  echo json_encode(['ok'=>false,'error'=>'update_failed']);
}