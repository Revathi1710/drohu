<?php
session_start();
include('connection.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['mobile_number'])) {
    header("Location: login.php");
    exit();
}

$userId = (int)($_SESSION['user_id'] ?? 0);

function getUser($con, $userId){
    $query = "SELECT id, first_name, email, mobile_number, create_at FROM users WHERE id = ? LIMIT 1";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?: [];
}

$u = getUser($con, $userId);
$name = htmlspecialchars($u['first_name'] ?? 'Guest');
$mobile = htmlspecialchars($u['mobile_number'] ?? '');
$email = htmlspecialchars($u['email'] ?? '');
$initial = strtoupper(substr(trim($name), 0, 1) ?: 'U');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Account</title>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700&display=swap" rel="stylesheet">

<style>
    :root{
        --z-primary:#7a1fa2;
        --z-primary-2:#b42acb;
        --bg:#f6f7fb;
        --card:#ffffff;
        --muted:#6b7280;
        --text:#0b1020;
        --border:#eef2f7;
        --accent:#1a9c46;
        --danger:#ff4d4f;
    }
    *{box-sizing:border-box}
    body{margin:0;background:var(--bg);font-family:Inter,system-ui,Segoe UI,Roboto,Arial,sans-serif;color:var(--text)}

    /* Header (Zepto-like) */
    .acc-header{
        position:relative;
        background:linear-gradient(135deg,#1a9cfa 0%,#0d6efd 100%);
        color:#fff;
        padding:18px 16px 22px 16px;
        border-radius:0 0 18px 18px;
        box-shadow:0 6px 18px rgba(0,0,0,.15);
    }
    .acc-top{
        display:flex;align-items:center;justify-content:space-between;margin-bottom:12px
    }
    .acc-title{font-weight:800}
    .acc-card{
        display:flex;align-items:center;gap:12px;
        background:rgba(255,255,255,.14);
        border:1px solid rgba(255,255,255,.35);
        padding:12px;border-radius:14px
    }
    .avatar{
        width:48px;height:48px;border-radius:12px;background:#fff;color:var(--z-primary);
        display:flex;align-items:center;justify-content:center;font-weight:900;font-size:20px
    }
    .u-name{font-weight:800}
    .u-meta{color:white;font-size:12px}
    .manage-btn{
        margin-left:auto;background:#fff;color:var(--z-primary);border:none;border-radius:999px;
        padding:8px 12px;font-weight:800;cursor:pointer
    }

    /* Sections */
    .container{max-width:560px;margin:14px auto;padding:0 14px}
    .section{background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:14px}
    .sec-title{padding:12px 14px;font-weight:800;border-bottom:1px solid var(--border);background:#fafbff}
    .item{display:flex;align-items:center;gap:12px;padding:14px}
    .item + .item{border-top:1px solid var(--border)}
    .ico{
        width:36px;height:36px;border-radius:10px;background:#f4f6ff;color:#375dfb;
        display:flex;align-items:center;justify-content:center
    }
    .it-title{font-weight:800}
    .it-sub{font-size:12px;color:var(--muted)}
    .chev{margin-left:auto;color:#9aa3af}
    a.item{text-decoration:none;color:inherit}
    .tag{display:inline-block;font-size:11px;background:#eaf5ff;color:#0b67d3;border-radius:999px;padding:2px 8px;margin-left:6px;font-weight:800}

    /* Sticky logout */
    .logout-wrap{padding:14px}
    .btn-logout{
        width:100%;padding:12px 14px;border-radius:12px;border:1px solid #ffd7d9;
        background:#fff5f5;color:#b42318;font-weight:800;cursor:pointer
    }

    /* Footer spacer */
    .spacer{height:10px}
</style>
</head>
<body>

<header class="acc-header">
    <div class="acc-top">
        <div class="acc-title">Account</div>
        <a href="index.php" style="color:#fff;text-decoration:none"><i class="fa-solid fa-xmark"></i></a>
    </div>
    <div class="acc-card">
        <div class="avatar" aria-hidden="true"><?= $initial ?></div>
        <div>
            <div class="u-name"><?= $name ?></div>
            <div class="u-meta"><i class="fa-solid fa-phone me-1"></i> <?= $mobile ?><?= $email ? ' • '.$email : '' ?></div>
        </div>
        <a href="profile.php"><button class="manage-btn">Manage</button></a>
    </div>
</header>

<div class="container">

    <div class="section">
        <div class="sec-title">My Stuff</div>
        <a class="item" href="orders.php">
            <div class="ico"><i class="fa-solid fa-bag-shopping"></i></div>
            <div>
                <div class="it-title">My Orders</div>
                <div class="it-sub">Track and manage your orders</div>
            </div>
            <i class="fa-solid fa-chevron-right chev"></i>
        </a>
        <a class="item" href="address_book.php">
            <div class="ico"><i class="fa-solid fa-location-dot"></i></div>
            <div>
                <div class="it-title">Saved Addresses</div>
                <div class="it-sub">Add or edit delivery addresses</div>
            </div>
            <i class="fa-solid fa-chevron-right chev"></i>
        </a>
      
        <a class="item" href="editprofile.php">
            <div class="ico"><i class="fa-solid fa-user"></i></div>
            <div>
                <div class="it-title">My Profile</div>
                <div class="it-sub">UManage your profile details</div>
            </div>
            <i class="fa-solid fa-chevron-right chev"></i>
        </a>
    </div>

    <div class="section">
        <div class="sec-title">Support</div>
        <a class="item" href="help.php">
            <div class="ico"><i class="fa-regular fa-circle-question"></i></div>
            <div>
                <div class="it-title">Help & FAQs</div>
                <div class="it-sub">Get answers or contact support</div>
            </div>
            <i class="fa-solid fa-chevron-right chev"></i>
        </a>
        <a class="item" href="privacy.php">
            <div class="ico"><i class="fa-solid fa-shield"></i></div>
            <div>
                <div class="it-title">Privacy Policy</div>
                <div class="it-sub">How we use your data</div>
            </div>
            <i class="fa-solid fa-chevron-right chev"></i>
        </a>
        <a class="item" href="terms.php">
            <div class="ico"><i class="fa-solid fa-file-contract"></i></div>
            <div>
                <div class="it-title">Terms & Conditions</div>
                <div class="it-sub">User agreement</div>
            </div>
            <i class="fa-solid fa-chevron-right chev"></i>
        </a>
    </div>

    <div class="logout-wrap">
        <a href="logout.php" style="text-decoration:none">
            <button class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
        </a>
    </div>

    <div class="spacer"></div>
</div>

</body>
</html>
