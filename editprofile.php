<?php
session_start();
include('connection.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name     = trim($_POST['first_name'] ?? '');
    $last_name      = trim($_POST['last_name'] ?? '');
    $mobile_number  = trim($_POST['mobile_number'] ?? '');
    $email          = trim($_POST['email'] ?? '');

    $errors = [];

    if ($first_name === '' || $last_name === '' || $mobile_number === '' || $email === '') {
        $errors[] = "All fields are required";
    }
    if (strlen($mobile_number) !== 10 || !ctype_digit($mobile_number)) {
        $errors[] = "Mobile number must be exactly 10 digits";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($errors)) {
        $stmt = $con->prepare("UPDATE users SET first_name=?, last_name=?, mobile_number=?, email=? WHERE id=?");
        $stmt->bind_param("ssssi", $first_name, $last_name, $mobile_number, $email, $user_id);

        if ($stmt->execute()) {
            echo "<script>alert('Profile updated successfully!'); window.location='profile.php';</script>";
            exit;
        } else {
            echo "<script>alert('Error updating profile.');</script>";
        }
    } else {
        echo "<script>alert('" . implode("\\n", $errors) . "');</script>";
    }
}

// Fetch current user details
$stmt = $con->prepare("SELECT first_name, last_name, mobile_number, email FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$getuser = $result->fetch_assoc();

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Address</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700&display=swap" rel="stylesheet">
<style>
	:root{
		--brand-a:#7a1fa2; --brand-b:#b42acb; --bg:#f6f7fb; --card:#ffffff; --border:#eef2f7; --muted:#6b7280; --ok:#1a9c46; --bad:#dc2626;
	}
	*{box-sizing:border-box}
	body{margin:0;background:var(--bg);font-family:Inter,system-ui,Segoe UI,Roboto,Arial,sans-serif;color:#0b1020}
	/* Zepto-like header */
	.z-header{position:sticky;top:0;z-index:10;color:#fff;background:linear-gradient(135deg,#1a9cfa 0%,#0d6efd 100%);;border-radius:0 0 18px 18px;box-shadow:0 8px 24px rgba(0,0,0,.15)}
	.z-head{display:flex;align-items:center;justify-content:space-between;padding:16px}
	.z-title{font-weight:900}
	.z-head .btn-icon{background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.35);color:#fff;width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;text-decoration:none}

	.wrap{max-width:560px;margin:14px auto;padding:0 14px 90px 14px}
	.card{background:var(--card);border:1px solid var(--border);border-radius:14px}
	.section{padding:14px}

	/* Label chips */
	.label-chips{display:flex;gap:10px;flex-wrap:wrap}
	.chip{border:1px solid #dfe4ea;background:#fff;border-radius:999px;padding:8px 12px;font-weight:800;cursor:pointer}
	.chip.active{border-color:#1a9c46;background:#f6fff9;box-shadow:0 0 0 2px #c9f3d8 inset}

	/* Inputs */
	.group{margin-bottom:12px}
	.label{display:block;font-weight:800;margin-bottom:6px}
	.input, .select, .textarea{width:100%;height:44px;border:1px solid #dfe4ea;border-radius:10px;padding:0 12px;background:#fff}
	.textarea{height:auto;min-height:90px;padding:10px 12px;resize:vertical}
	.row2{display:grid;grid-template-columns:1fr 1fr;gap:10px}
	.readonly{background:#f8fafc;color:var(--muted)}

	/* Messages */
	.alert{display:none;margin-bottom:12px;padding:10px 12px;border-radius:10px;font-weight:700}
	.alert.ok{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46}
	.alert.err{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}

	/* Sticky footer */
	.sticky{position:fixed;left:0;right:0;bottom:0;background:#fff;border-top:1px solid var(--border)}
	.foot{max-width:560px;margin:0 auto;padding:12px 14px;display:flex;gap:10px}
	.btn{border:none;border-radius:12px;padding:12px 14px;font-weight:900;cursor:pointer}
	.btn-outline{background:#fff;border:1px solid #dfe4ea}
	.btn-primary{background:var(--ok);color:#fff}
	.btn:disabled{opacity:.6;cursor:not-allowed}

	/* Helper */
	.muted{color:var(--muted);font-size:12px}
</style>
</head>
<body>

<header class="z-header">
	<div class="z-head">
		<a href="javascript:history.back()" class="btn-icon" aria-label="Back"><i class="fa-solid fa-arrow-left"></i></a>
		<div class="z-title">Edit Profile</div>
		<a href="profile.php" class="btn-icon" aria-label="Close"><i class="fa-solid fa-xmark"></i></a>
	</div>
</header>

<div class="wrap">
	<div class="card section">
		<div id="successBox" class="alert ok"><i class="fa-solid fa-check me-1"></i><span>Saved</span></div>
		<div id="errorBox" class="alert err"><i class="fa-solid fa-circle-exclamation me-1"></i><span>Error</span></div>

	<form id="addrForm" method="POST" action="">
    <div class="row2">
        <div class="group">
            <label class="label">First Name</label>
            <input class="input" name="first_name" placeholder="e.g. John" required value="<?=h($getuser['first_name'])?>">
        </div>
        <div class="group">
            <label class="label">Last Name</label>
            <input class="input" name="last_name" placeholder="e.g. Doe" required value="<?=h($getuser['last_name'])?>">
        </div>
    </div>

    <div class="group">
        <label class="label">Mobile Number</label>
        <input class="input" name="mobile_number" required value="<?=h($getuser['mobile_number'])?>">
    </div>

    <div class="group">
        <label class="label">Email Id</label>
        <input class="input" name="email" placeholder="abc@gmail.com" required value="<?=h($getuser['email'])?>">
    </div>
</form>

	</div>
</div>

<div class="sticky">
	<div class="foot">
		<a href="profile.php" class="btn btn-outline">Cancel</a>
		<button class="btn btn-primary" id="saveBtn" form="addrForm">Save Profile</button>
	</div>
</div>

<script>

</script>
</body>
</html>