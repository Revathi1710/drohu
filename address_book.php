<?php
session_start();
include('connection.php');

if (!isset($_SESSION['mobile_number'])) {
    header("Location: login.php");
    exit();
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$selectedAddressId = (int)($_SESSION['selected_address_id'] ?? 0);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function short_address(array $a): string {
    $parts = array_filter([
        $a['door_no'] ?? '',
        $a['street_address'] ?? '',
        $a['city'] ?? '',
        $a['state'] ?? '',
        $a['pincode'] ?? '',
    ]);
    return implode(', ', $parts);
}

$user_addresses = [];
$selected_address = null;

if ($userId > 0) {
    $sql = "SELECT id, door_no, pincode, address_label, street_address, city, state, receiver_name, receiver_number
            FROM address_details
            WHERE user_id = ?
            ORDER BY id DESC";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $user_addresses[] = $row;
        if ($selected_address === null && ($selectedAddressId === 0 || (int)$row['id'] === $selectedAddressId)) {
            $selected_address = $row;
        }
    }
    $stmt->close();

    if ($selected_address === null && $selectedAddressId !== 0) {
        unset($_SESSION['selected_address_id']);
    }
}

$from = $_GET['from'] ?? ''; // if from=cart, redirect back to cart after selecting
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Addresses</title>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700&display=swap" rel="stylesheet">
<style>
    :root{ --z-primary:#7a1fa2; --z-primary-2:#b42acb; --bg:#f6f7fb; --card:#fff; --border:#eef2f7; --muted:#6b7280; }
    body{ margin:0; background:var(--bg); font-family:Inter,system-ui,Segoe UI,Roboto,Arial,sans-serif; }

    /* Header */
    .z-header{ position:sticky; top:0; z-index:10; color:#fff;
        background:linear-gradient(135deg,#1a9cfa 0%,#0d6efd 100%);
        border-radius:0 0 18px 18px; box-shadow:0 6px 18px rgba(0,0,0,.15); }
    .z-head{ display:flex; align-items:center; justify-content:space-between; padding:16px; }
    .z-title{ font-weight:800; }

    .wrap{ max-width:680px; margin:14px auto; padding:0 14px 40px 14px; }
    .add-pill{ border:1px dashed #d0d5dd; background:#fafafa; color:#222; border-radius:12px; padding:12px; font-weight:800; text-align:center; text-decoration:none; display:block; }
    .hint{ color:var(--muted); font-size:12px; margin:10px 0; }

    .saved-title{ margin:12px 0 8px; color:#6b7280; font-weight:700; font-size:13px; }

    .card{ background:var(--card); border:1px solid var(--border); border-radius:14px; overflow:hidden; }
    .addr{ border:1px solid #ececf2; border-radius:12px; padding:12px; margin-bottom:10px; cursor:pointer; background:#fff; }
    .addr.selected{ border-color:#1a9c46; background:#f6fff9; box-shadow:0 0 0 2px #c9f3d8 inset; }
    .row{ display:flex; align-items:flex-start; gap:10px; }
    .home-ico{ width:32px; height:32px; border-radius:10px; background:#f4f6ff; color:#22a06b; display:flex; align-items:center; justify-content:center; }
    .label{ font-weight:800; }
    .chip{ display:inline-block; font-size:11px; padding:2px 8px; border-radius:999px; background:#eaf5ff; color:#0b67d3; margin-left:6px; font-weight:800;text-decoration:none; }
    .muted{ color:var(--muted); font-size:12px; margin-top:2px; }
    .chev{ color:#9aa3af; margin-left:auto; }

    .empty{ background:var(--card); border:1px solid var(--border); border-radius:14px; padding:24px; text-align:center; color:var(--muted); }
</style>
</head>
<body>

<header class="z-header">
    <div class="z-head">
        <div class="z-title">Your Addresses</div>
        <a href="<?= $from==='cart' ? 'cart.php' : 'profile.php' ?>" style="color:#fff;text-decoration:none"><i class="fa-solid fa-xmark"></i></a>
    </div>
</header>

<div class="wrap">
    <a href="add_address.php" class="add-pill"><i class="fa-solid fa-plus me-2"></i> Add New Address</a>
    <div class="hint">Tap an address to select it for delivery.</div>

    <?php if ($user_addresses): ?>
        <div class="saved-title">Saved Addresses</div>
        <div class="card" style="padding:10px">
            <?php foreach ($user_addresses as $addr): ?>
                <?php $isSel = ($selected_address && (int)$selected_address['id'] === (int)$addr['id']); ?>
                <div class="addr <?= $isSel ? 'selected' : '' ?>" data-id="<?= (int)$addr['id'] ?>">
                    <div class="row">
                        <div class="home-ico"><i class="fa-solid fa-house"></i></div>
                        <div class="flex-grow-1">
                            <div class="label">
                                <?= h($addr['address_label'] ?: 'Home') ?>
                                <?php if ($isSel): ?><span class="chip">Selected</span><?php endif; ?>
                            </div>
                            <div class="muted"><?= h(short_address($addr)) ?></div>
                            <div class="muted"><?= h($addr['receiver_name'] ?? '') ?> • <?= h($addr['receiver_number'] ?? '') ?></div>
                        </div> <a class="chip" href="edit_address.php?id=<?= (int)$addr['id'] ?>">Edit</a>
                        <i class="fa-solid fa-chevron-right chev"></i>
                        
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty">
            <div style="font-weight:800;margin-bottom:6px;">No addresses saved</div>
            <div>Add one to speed up checkout.</div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const cards = document.querySelectorAll('.addr');
    const from = <?= json_encode($from) ?>;

    cards.forEach(card => {
        card.addEventListener('click', async () => {
            const id = Number(card.getAttribute('data-id'));
            try{
                const res = await fetch('update_selected_address.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ address_id: id })
                });
                const data = await res.json();
                if (data && data.success) {
                    // Update UI highlight
                    document.querySelectorAll('.addr').forEach(c => c.classList.remove('selected'));
                    card.classList.add('selected');

                    // Add/Update "Selected" chip
                    const label = card.querySelector('.label');
                    if (label && !label.querySelector('.chip')) {
                        const chip = document.createElement('span');
                        chip.className = 'chip';
                        chip.textContent = 'Selected';
                        label.appendChild(chip);
                    }

                    // If coming from cart, go back to cart so CTA enables
                    if (from === 'cart') {
                        window.location.href = 'cart.php';
                    }
                }
            }catch(e){
                console.error(e);
            }
        });
    });
});
</script>

</body>
</html>