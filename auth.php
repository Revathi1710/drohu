<?php
$lifetime = 630720000; // 20 years
ini_set('session.gc_maxlifetime', $lifetime);
session_set_cookie_params($lifetime, "/");
session_start();

include('connection.php');

// If session exists, continue
if (isset($_SESSION['mobile_number'])) {
    return; // user already logged in
}

// If session expired, check remember-me cookie
if (isset($_COOKIE['rememberme'])) {
    $token = $_COOKIE['rememberme'];
    $token = $con->real_escape_string($token);

    $res = $con->query("SELECT user_id FROM user_tokens WHERE token='$token' LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $userId = $row['user_id'];

        $userRes = $con->query("SELECT mobile_number FROM users WHERE id=$userId LIMIT 1");
        if ($userRes && $userRes->num_rows > 0) {
            $user = $userRes->fetch_assoc();

            // Restore session
            $_SESSION['user_id'] = $userId;
            $_SESSION['mobile_number'] = $user['mobile_number'];
            return;
        }
    }
}

// If no session and no valid cookie → redirect
header("Location: login.php");
exit();
?>
