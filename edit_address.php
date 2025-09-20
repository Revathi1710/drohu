<?php
session_start();
include('connection.php');

if (!isset($_SESSION['mobile_number'])) {
    header("Location: login.php");
    exit();
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$addressId = (int)($_GET['id'] ?? 0);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

if ($userId <= 0 || $addressId <= 0) {
    header("Location: address_book.php");
    exit();
}

// Fetch address
$stmt = $con->prepare("SELECT id, address_label, door_no, street_address, city, state, pincode, receiver_name, receiver_number FROM address_details WHERE id=? AND user_id=? LIMIT 1");
$stmt->bind_param("ii", $addressId, $userId);
$stmt->execute();
$res = $stmt->get_result();
$addr = $res->fetch_assoc();
$stmt->close();

if (!$addr) {
    header("Location:address_book.php");
    exit();
}

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'delete') {
        $del = $con->prepare("DELETE FROM address_details WHERE id=? AND user_id=?");
        $del->bind_param("ii", $addressId, $userId);
        $del->execute();
        $del->close();
        // Clear session selected if it was this one
        if ((int)($_SESSION['selected_address_id'] ?? 0) === $addressId) {
            unset($_SESSION['selected_address_id']);
        }
        header("Location: address_book.php?msg=deleted");
        exit();
    }

    $label  = trim($_POST['address_label'] ?? '');
    $door   = trim($_POST['door_no'] ?? '');
    $street = trim($_POST['street_address'] ?? '');
    $city   = trim($_POST['city'] ?? '');
    $state  = trim($_POST['state'] ?? '');
    $pin    = trim($_POST['pincode'] ?? '');
    $recv   = trim($_POST['receiver_name'] ?? '');
    $phone  = trim($_POST['receiver_number'] ?? '');

    // Basic validation
    if ($label === '') $label = 'Home';
    if ($city === '' || $state === '' || $pin === '') {
        $error = "Please fill city, state and pincode.";
    } else {
        $up = $con->prepare("UPDATE address_details SET address_label=?, door_no=?, street_address=?, city=?, state=?, pincode=?, receiver_name=?, receiver_number=? WHERE id=? AND user_id=?");
        $up->bind_param("ssssssssii", $label, $door, $street, $city, $state, $pin, $recv, $phone, $addressId, $userId);
        $up->execute();
        $up->close();

        // If user ticked set_default, store in session
        if (isset($_POST['set_default'])) {
            $_SESSION['selected_address_id'] = $addressId;
        }

        header("Location:address_book.php?msg=updated");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Address</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700&display=swap" rel="stylesheet">
<style>
    :root{ --z-primary:#7a1fa2; --z-primary-2:#b42acb; --bg:#f6f7fb; --card:#fff; --border:#eef2f7; --muted:#6b7280; --ok:#1a9c46; --danger:#ff4d4f; }
    body{ margin:0; background:var(--bg); font-family:Inter,system-ui,Segoe UI,Roboto,Arial,sans-serif; color:#0b1020; }

    .z-header{ position:sticky; top:0; z-index:10; color:#fff; background:linear-gradient(135deg,#1a9cfa 0%,#0d6efd 100%); border-radius:0 0 18px 18px; box-shadow:0 6px 18px rgba(0,0,0,.15); }
    .z-head{ display:flex; align-items:center; justify-content:space-between; padding:16px; }
    .z-title{ font-weight:800; }

    .wrap{ max-width:680px; margin:14px auto; padding:0 14px 90px 14px; }
    .card{ background:var(--card); border:1px solid var(--border); border-radius:14px; padding:12px; }
    .group{ margin-bottom:12px; }
    .label{ font-weight:800; margin-bottom:6px; display:block; }
    .input,.select{ width:100%; height:44px; border:1px solid #dfe4ea; border-radius:10px; background:#fff; }
    .row{ display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .muted{ color:var(--muted); font-size:12px; }
    .error{ background:#fff0f0; border:1px solid #ffd7d9; color:#b42318; padding:10px; border-radius:10px; margin-bottom:10px; }

    .sticky{ position:fixed; left:0; right:0; bottom:0; background:#fff; border-top:1px solid var(--border); }
    .foot{ max-width:680px; margin:0 auto; padding:12px 14px; display:flex; gap:10px; }
    .btn{ border:none; border-radius:12px; padding:12px 14px; font-weight:900; cursor:pointer; }
    .btn-outline{ background:#fff; border:1px solid #dfe4ea; }
    .btn-primary{ background:var(--ok); color:#fff; }
    .btn-danger{ background:#fff0f0; color:#b42318; border:1px solid #ffd7d9; }
    .chip{ display:inline-block; font-size:12px; background:#eaf5ff; color:#0b67d3; border-radius:999px; padding:4px 10px; font-weight:800; }
</style>
</head>
<body>

<header class="z-header">
    <div class="z-head">
        <div class="z-title">Edit Address</div>
        <a href="address_book.php" style="color:#fff;text-decoration:none"><i class="fa-solid fa-xmark"></i></a>
    </div>
</header>

<div class="wrap">
    <form method="post" class="card" id="addressForm" novalidate>
        <?php if (!empty($error)): ?>
            <div class="error"><?= h($error) ?></div>
        <?php endif; ?>

        <div class="group">
            <label class="label">Address label</label>
            <select name="address_label" class="select">
                <?php
                    $labels = ['Home','Work','Other'];
                    $cur = $addr['address_label'] ?: 'Home';
                    foreach($labels as $l){
                        $sel = (strcasecmp($cur,$l)===0)?'selected':'';
                        echo '<option '.$sel.'>'.h($l).'</option>';
                    }
                ?>
            </select>
        </div>

        <div class="group row">
            <div>
                <label class="label">Receiver name</label>
                <input class="input" name="receiver_name" value="<?= h($addr['receiver_name']) ?>"  placeholder="Full name">
            </div>
            <div>
                <label class="label">Phone number</label>
                <input class="input" name="receiver_number" value="<?= h($addr['receiver_number']) ?>" placeholder="10-digit mobile" inputmode="numeric" pattern="\d{10}">
            </div>
        </div>

        <div class="group row">
            <div>
                <label class="label">Door / Flat no.</label>
                <input class="input" name="door_no" value="<?= h($addr['door_no']) ?>" placeholder="e.g. 12B">
            </div>
            <div>
                <label class="label">Pincode</label>
                <input class="input" name="pincode" value="<?= h($addr['pincode']) ?>" placeholder="e.g. 560001" inputmode="numeric" pattern="\d{5,6}">
            </div>
        </div>

        <div class="group">
            <label class="label">Street & Area</label>
            <input class="input" name="street_address" value="<?= h($addr['street_address']) ?>" placeholder="Street, area, landmark">
        </div>

        <div class="group row">
            <div>
                <label class="label">City</label>
                <input class="input" name="city" value="<?= h($addr['city']) ?>" placeholder="City">
            </div>
            <div>
                <label class="label">State</label>
                <input class="input" name="state" value="<?= h($addr['state']) ?>" placeholder="State">
            </div>
        </div>

        <div class="group" style="display:flex;align-items:center;gap:8px">
            <input type="checkbox" id="set_default" name="set_default" <?= ((int)($_SESSION['selected_address_id'] ?? 0) === $addressId) ? 'checked' : '' ?>>
            <label for="set_default" class="muted">Set as default for checkout</label>
        </div>

        <div class="muted"><span class="chip"><i class="fa-solid fa-shield-heart"></i> Saved securely</span></div>

        <div style="margin-top:12px;display:flex;gap:10px">
            <button type="submit" name="action" value="delete" class="btn btn-danger" onclick="return confirm('Delete this address?');">
                <i class="fa-solid fa-trash-can"></i> Delete
            </button>
        </div>
    </form>
</div>

<div class="sticky">
    <div class="foot">
        <a href="address_book.php" style="text-decoration:none"><button class="btn btn-outline" type="button">Cancel</button></a>
        <button form="addressForm" class="btn btn-primary" type="submit">Save address</button>
    </div>
</div>

</body>
</html>