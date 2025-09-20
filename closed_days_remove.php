<?php
session_start();
require_once __DIR__ . '/connection.php';
header('Content-Type: application/json');
$in = json_decode(file_get_contents('php://input'), true);
$date = trim($in['date'] ?? '');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) { echo json_encode(['ok'=>false,'error'=>'invalid_date']); exit; }
$stmt=$con->prepare("DELETE FROM closed_days WHERE date=? LIMIT 1");
$stmt->bind_param('s',$date);
echo $stmt->execute()?json_encode(['ok'=>true]):json_encode(['ok'=>false,'error'=>'delete_failed']);
$stmt->close();