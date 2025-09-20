<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: choose-login.php');
exit;
?>
