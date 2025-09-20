<?php
session_start();
require_once __DIR__ . '/connection.php';
header('Content-Type: application/json');

$inRaw = file_get_contents('php://input');
$in = json_decode($inRaw, true);
$id = (int)($in['id'] ?? 0);
$role = trim((string)($in['role'] ?? ''));

$allowed = ['customer','delivery','admin'];
if ($id <= 0 || !in_array($role, $allowed, true)) { echo json_encode(['ok'=>false,'error'=>'invalid_args']); exit; }

// fetch user
$stmt = $con->prepare("SELECT id, name, mobile_number, email, COALESCE(role,'customer') AS role FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$user) { echo json_encode(['ok'=>false,'error'=>'user_not_found']); exit; }
$prev = strtolower($user['role']);

// if delivery: must have username, password, pincode
if ($role === 'delivery') {
  $username = trim((string)($in['username'] ?? ''));
  $password = (string)($in['password'] ?? '');
  $pincode  = trim((string)($in['pincode'] ?? ''));
  if (!preg_match('/^[A-Za-z0-9._-]{4,32}$/', $username)) { echo json_encode(['ok'=>false,'error'=>'invalid_username','prev'=>$prev]); exit; }
  if (strlen($password) < 6) { echo json_encode(['ok'=>false,'error'=>'weak_password','prev'=>$prev]); exit; }
  if (!preg_match('/^\d{6}$/', $pincode)) { echo json_encode(['ok'=>false,'error'=>'invalid_pincode','prev'=>$prev]); exit; }

  // ensure username unique in deliveryPerson
  $ck = $con->prepare("SELECT id FROM deliveryPerson WHERE username = ? LIMIT 1");
  $ck->bind_param('s', $username);
  $ck->execute();
  $exists = $ck->get_result()->fetch_assoc();
  $ck->close();
  if ($exists) { echo json_encode(['ok'=>false,'error'=>'username_taken','prev'=>$prev]); exit; }

  // update users.role
  $st = $con->prepare("UPDATE users SET role = ? WHERE id = ? LIMIT 1");
  $st->bind_param('si', $role, $id);
  if (!$st->execute()) { echo json_encode(['ok'=>false,'error'=>'role_update_failed','prev'=>$prev]); exit; }
  $st->close();

  // insert into deliveryPerson
  $name  = (string)($user['name'] ?? '');
  $num   = (string)($user['mobile_number'] ?? '');
  $email = (string)($user['email'] ?? '');
  $pwdHash = password_hash($password, PASSWORD_DEFAULT);
  $ins = $con->prepare("INSERT INTO deliveryPerson (deliveryperson_name, number, email, username, password, pincode) VALUES (?,?,?,?,?,?)");
  $ins->bind_param('ssssss', $name, $num, $email, $username, $pwdHash, $pincode);
  if (!$ins->execute()) { echo json_encode(['ok'=>false,'error'=>'delivery_person_insert_failed','prev'=>$prev]); exit; }
  $ins->close();

  echo json_encode(['ok'=>true,'prev'=>$prev]); exit;
}

// non-delivery: just update role
$st = $con->prepare("UPDATE users SET role = ? WHERE id = ? LIMIT 1");
$st->bind_param('si', $role, $id);
if (!$st->execute()) { echo json_encode(['ok'=>false,'error'=>'role_update_failed','prev'=>$prev]); exit; }
$st->close();
echo json_encode(['ok'=>true,'prev'=>$prev]);