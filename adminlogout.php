<?php
session_start();

// Only remove admin session data
unset($_SESSION['auth']);
unset($_SESSION['adminId']);
unset($_SESSION['login_time']);

// Redirect to login page
header("Location: admin.php");
exit();
?>
