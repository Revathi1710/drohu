<?php
session_start();
require_once __DIR__ . '/connection.php';
header('Content-Type: application/json');
$in=json_decode(file_get_contents('php://input'),true);
$ch=in_array(($in['channel']??''),['sms','whatsapp'],true)?$in['channel']:'';
$msg=trim($in['message']??'');
if($ch===''||$msg===''){ echo json_encode(['ok'=>false,'error'=>'invalid_args']); exit; }
$res=$con->query("SELECT user_country_code,mobile_number FROM users WHERE mobile_number<>''");
$sent=0; if($res){ while($r=$res->fetch_assoc()){ /* send via provider */ $sent++; } }
$stmt=$con->prepare("INSERT INTO broadcasts (channel,message,sent_count) VALUES (?,?,?)");
$stmt->bind_param('ssi',$ch,$msg,$sent); $stmt->execute(); $stmt->close();
echo json_encode(['ok'=>true,'sent_count'=>$sent]);