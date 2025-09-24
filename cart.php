<?php
// cart.php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['mobile_number'])) {
    header("Location: login.php");
    exit();
}

include 'connection.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Pull identifiers
$userId = (int)($_SESSION['user_id'] ?? 0);
$selectedAddressId = isset($_SESSION['selected_address_id']) ? (int)$_SESSION['selected_address_id'] : 0;

// Helpers
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function rupee(float $n): string { return number_format($n, 2); }
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

// Cart items
$cart_items = [];
$total_price = 0.0;
if ($userId > 0) {
    $sql = "SELECT ac.id, ac.quantity, p.id AS product_id, p.product_name, p.product_image, p.selling_price FROM addcart ac JOIN product p ON ac.prod_id = p.id WHERE ac.user_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['quantity'] = (int)$row['quantity'];
        $row['selling_price'] = (float)$row['selling_price'];
        $cart_items[] = $row;
        $total_price += $row['selling_price'] * $row['quantity'];
    }
    $stmt->close();
}

// Addresses
$user_addresses = [];
$selected_address = null;
if ($userId > 0) {
    $sql_addresses = "SELECT id, door_no, pincode, address_label, street_address, city, state, receiver_name, receiver_number FROM address_details WHERE user_id = ? ORDER BY id DESC";
    $stmt_addresses = $con->prepare($sql_addresses);
    $stmt_addresses->bind_param("i", $userId);
    $stmt_addresses->execute();
    $result_addresses = $stmt_addresses->get_result();
    while ($row = $result_addresses->fetch_assoc()) {
        $user_addresses[] = $row;
        if ($selected_address === null && ((int)$row['id'] === $selectedAddressId || $selectedAddressId === 0)) {
            $selected_address = $row;
            $_SESSION['selected_address_id'] = $row['id'];
        }
    }
    $stmt_addresses->close();
}

$has_items = count($cart_items) > 0;
$to_pay = $total_price;

// Handle remove item
if (isset($_POST['remove-item'])) {
    $itemId = (int) $_POST['item_id'];
    if ($itemId > 0) {
        $stmt = $con->prepare("DELETE FROM addcart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $itemId, $userId);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: cart.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cart</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
    body { background:#f6f7fb; font-family:Inter,system-ui,Segoe UI,Roboto,Arial,sans-serif; }
    .app-header{ position:sticky; top:0; z-index:1030;color:white; background:linear-gradient(135deg, #00c6ff, #0072ff); padding:14px 16px; border-bottom:1px solid #eee; display:flex; align-items:center; gap:12px; }
    .app-header h5{ margin:0; font-weight:700; }
    .container-narrow{ max-width:520px; margin:0 auto; }
    .section-card{ background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,.05); border:1px solid #eee; }
    .row-item{ display:flex; align-items:center; gap:12px; padding:14px 14px; }
    .row-item + .row-item{ border-top:1px solid #f0f0f0; }
    .row-icon{ width:34px; height:34px; border-radius:8px; background:#f4f6ff; color:#375dfb; display:flex; align-items:center; justify-content:center; }
    .address-row .title{ font-weight:700; }
    .address-row .sub{ color:#6b7280; font-size:13px; line-height:1.2; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
    .add-address-pill{ border:1px dashed #d0d5dd; background:#fafafa; color:#444; border-radius:10px; padding:12px; text-align:center; font-weight:600; }
    .cart-item{ display:flex; gap:12px; padding:14px 14px; }
    .cart-item + .cart-item{ border-top:1px solid #f0f0f0; }
    .cart-img{ width:58px; height:58px; border-radius:10px; object-fit:cover; background:#f7f7f7; border:1px solid #eee; }
    .cart-title{ font-weight:600; margin:0; }
    .muted{ color:#6b7280; font-size:13px; }
    .bill-line{ display:flex; justify-content:space-between; padding:8px 0; }
    .sticky-cta{ position:sticky; bottom:0; z-index:1020; background:#fff; border-top:1px solid #eee; }
    .cta-inner{ max-width:520px; margin:0 auto; padding:12px; display:flex; gap:10px; }
    .btn-cta{ flex:1; padding:14px 16px; font-weight:700; border:none; border-radius:10px; background:#008b9b; color:#fff; }
    .btn-cta:disabled{ background:#cfd4dc; color:#7b8794; }
    .modal.modal-sheet .modal-dialog{ margin:0; position:fixed; bottom:0; left:0; right:0; width:auto; }
    .modal.modal-sheet .modal-content{ border-radius:16px 16px 0 0; border:0; box-shadow:0 -6px 24px rgba(0,0,0,.15); }
    .modal.modal-sheet .modal-header{ border:0; padding:14px 16px 0 16px; }
    .modal.modal-sheet .modal-body{ padding:6px 16px 16px 16px; }
    .saved-address{ border:1px solid #ececf2; border-radius:12px; padding:12px; margin-bottom:10px; cursor:pointer; }
    .saved-address.selected{ border-color:#1a9c46; background:#f1fff6; box-shadow:0 0 0 2px #c9f3d8 inset; }
    .chip{ display:inline-block; font-size:12px; padding:2px 8px; border-radius:999px; background:#eaf5ff; color:#0b67d3; margin-left:6px; }
    .pay-summary{ background:#f8fafc; border:1px solid #eef2f7; border-radius:12px; padding:10px 12px; display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; }
    .pay-group-title{ font-size:12px; color:#6b7280; font-weight:700; margin:12px 0 8px 0; }
    .upi-apps{ display:flex; gap:10px; overflow-x:auto; padding-bottom:2px; }
    .upi-app{ min-width:86px; border:1px solid #e6eaf0; border-radius:12px; padding:10px 10px; text-align:center; cursor:pointer; background:#fff; }
    .upi-app img{ width:28px; height:28px; border-radius:8px; object-fit:contain; }
    .upi-app .label{ font-size:12px; font-weight:700; margin-top:6px; color:#0b1020; }
    .upi-app.selected{ border-color:#1a9c46; box-shadow:0 0 0 2px #c9f3d8 inset; background:#f6fff9; }
    .pay-option{ display:flex; align-items:center; gap:12px; padding:12px; border:1px solid #e6eaf0; border-radius:12px; cursor:pointer; background:#fff; }
    .pay-option + .pay-option{ margin-top:10px; }
    .pay-option .icon{ width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; background:#f4f6ff; color:#375dfb; }
    .pay-option .title{ font-weight:700; }
    .pay-option .sub{ font-size:12px; color:#6b7280; }
    .pay-option.selected{ border-color:#1a9c46; background:#f6fff9; box-shadow:0 0 0 2px #c9f3d8 inset; }
    .sheet-footer{ position:sticky; bottom:0; background:#fff; padding:12px 0 4px 0; }
    .btn-pay{ width:100%; padding:12px 14px; font-weight:800; border:none; border-radius:10px; background:#008b9b; color:#fff; }
    .remove-cart-item{ border-radius: 8px; background: #fff4f4; color: #fb3737; display: flex; align-items: center; justify-content: center; margin-top: 10px; border: 1px solid #ff0404; }
</style>
</head>
<body>
<div class="app-header container-narrow">
    <a href="products.php" style="color:white"><i class="fa-solid fa-arrow-left"></i></a>
    <h5>Cart</h5>
</div>
<div class="container-narrow p-3 pb-5">
    <div class="section-card mb-3">
        <?php if ($selected_address): ?>
            <div class="row-item address-row" data-bs-toggle="modal" data-bs-target="#addressSheet">
                <div class="row-icon"><i class="fa-solid fa-house"></i></div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center">
                        <div class="title">Delivering to <?= h($selected_address['address_label'] ?: 'Home') ?></div>
                        <span class="chip ms-2">Selected</span>
                    </div>
                    <div class="sub mt-1"><?= h(short_address($selected_address)) ?></div>
                </div>
                <i class="fa-solid fa-chevron-down text-secondary"></i>
            </div>
        <?php else: ?>
            <div class="p-3">
                <div class="add-address-pill" data-bs-toggle="modal" data-bs-target="#addressSheet">
                    <i class="fa-solid fa-plus me-2"></i> Add Delivery Address
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="section-card mb-3">
        <div class="row-item"><strong>My Cart (<?= count($cart_items) ?>)</strong></div>
        <?php if (!$cart_items): ?>
            <div class="row-item"><span class="muted">Your cart is empty. Shop products to continue.</span></div>
        <?php else: ?>
            <?php foreach ($cart_items as $it): ?>
                <div class="cart-item">
                    <img src="./<?= h($it['product_image']) ?>" alt="<?= h($it['product_name']) ?>" class="cart-img">
                    <div class="flex-grow-1">
                        <p class="cart-title"><?= h($it['product_name']) ?></p>
                        <div class="muted">Qty: <?= (int)$it['quantity'] ?></div>
                        <form method="post"><input type="hidden" name="item_id" value="<?= $it['id'] ?>"><button type="submit" class="remove-cart-item" name="remove-item">Remove</button></form>
                    </div>
                    <div class="fw-semibold">₹<?= rupee($it['selling_price'] * $it['quantity']) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="section-card mb-3">
        <div class="row-item"><strong>Bill Details</strong></div>
        <div class="px-3">
            <div class="bill-line"><span>Item Total</span><span>₹<?= rupee($total_price) ?></span></div>
            <hr class="my-2">
            <div class="bill-line fw-bold"><span>To Pay</span><span>₹<?= rupee($has_items ? $to_pay : 0) ?></span></div>
        </div>
    </div>
    <div class="section-card mb-4">
        <div class="row-item"><div class="row-icon"><i class="fa-regular fa-message"></i></div><div class="flex-grow-1"><div class="fw-semibold">Delivery Instructions</div><div class="muted">Delivery partner will be notified</div></div><i class="fa-solid fa-chevron-right text-secondary"></i></div>
        <div class="row-item"><div class="row-icon"><i class="fa-solid fa-shield-heart"></i></div><div class="flex-grow-1"><div class="fw-semibold">Delivery Partner's Safety</div><div class="muted">Learn how we ensure their safety</div></div><i class="fa-solid fa-chevron-right text-secondary"></i></div>
    </div>
</div>
<div class="sticky-cta">
    <div class="cta-inner">
        <button class="btn-cta" id="placeOrderBtn" data-amount="<?= (float)$to_pay ?>" <?= ($selected_address && $has_items) ? '' : 'disabled' ?>>
            <?= ($selected_address && $has_items) ? 'Proceed to Checkout • ₹'.rupee($to_pay) : 'Add address to place order' ?>
        </button>
    </div>
</div>
<div class="modal fade modal-sheet" id="addressSheet" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><div class="mx-auto" style="width:40px;height:4px;border-radius:999px;background:#d9d9de;"></div></div><div class="modal-body"><div class="d-flex justify-content-between align-items-center mb-2"><h6 class="m-0 fw-bold">Select Address</h6><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><a href="add_address.php" class="text-decoration-none"><div class="add-address-pill mb-3"><i class="fa-solid fa-plus me-2"></i>Add New Address</div></a><?php if ($user_addresses): ?><div class="mb-2 fw-semibold text-secondary">Saved Addresses</div><?php foreach ($user_addresses as $addr): ?><?php $isSel = ($selected_address && (int)$selected_address['id'] === (int)$addr['id']); ?><div class="saved-address <?= $isSel ? 'selected' : '' ?>" data-id="<?= (int)$addr['id'] ?>"><div class="d-flex align-items-start"><div class="me-2 text-success"><i class="fa-solid fa-house"></i></div><div class="flex-grow-1"><div class="fw-semibold"><?= h($addr['address_label'] ?: 'Home') ?><?php if ($isSel): ?><span class="chip">Selected</span><?php endif; ?></div><div class="muted"><?= h(short_address($addr)) ?></div></div><i class="fa-solid fa-chevron-right text-secondary"></i></div></div><?php endforeach; ?><?php else: ?><div class="muted">No addresses saved yet.</div><?php endif; ?></div></div></div></div>
<div class="modal fade modal-sheet" id="paymentSheet" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><div class="mx-auto" style="width:40px;height:4px;border-radius:999px;background:#d9d9de;"></div></div><div class="modal-body"><div class="d-flex justify-content-between align-items-center mb-2"><h6 class="m-0 fw-bold">Choose payment method</h6><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="pay-summary"><div><div class="fw-bold">To Pay</div><div class="text-secondary small"><?php if ($selected_address): ?>Deliver to <?= h($selected_address['address_label'] ?: 'Home') ?><?php else: ?>Select address to continue<?php endif; ?></div></div><div class="fw-bolder fs-5">₹<?= rupee($has_items ? $to_pay : 0) ?></div></div><div class="pay-group-title">Other options</div><div class="pay-option selected" data-method="razorpay"><div class="icon"><i class="fa-solid fa-credit-card"></i></div><div class="flex-grow-1"><div class="title">Card / UPI / Netbanking (Razorpay)</div><div class="sub">Pay securely online</div></div><i class="fa-solid fa-check text-success"></i></div><div class="pay-option" data-method="cod"><div class="icon"><i class="fa-solid fa-money-bill-wave"></i></div><div class="flex-grow-1"><div class="title">Cash on Delivery (COD)</div><div class="sub">Pay with cash when delivered</div></div></div><div class="sheet-footer"><button class="btn-pay" id="payConfirmBtn">Pay ₹<?= rupee($has_items ? $to_pay : 0) ?></button></div></div></div></div>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
    const hasItems = <?= $has_items ? 'true' : 'false' ?>;
    const hasAddress = <?= $selected_address ? 'true' : 'false' ?>;
    const toPay = <?= json_encode((float)$to_pay) ?>;

    const placeBtn = document.getElementById('placeOrderBtn');
    const paymentSheetEl = document.getElementById('paymentSheet');
    const paymentSheet = paymentSheetEl ? bootstrap.Modal.getOrCreateInstance(paymentSheetEl) : null;

    if (placeBtn) {
        placeBtn.addEventListener('click', function(){
            if (!hasItems || !hasAddress) return;
            if (paymentSheet) paymentSheet.show();
        });
    }

    let selectedMethod = 'razorpay';
    document.querySelectorAll('.pay-option').forEach(opt => {
        opt.addEventListener('click', () => {
            document.querySelectorAll('.pay-option').forEach(o => o.classList.remove('selected'));
            opt.classList.add('selected');
            selectedMethod = opt.dataset.method;
            updateConfirmText();
        });
    });

    function updateConfirmText(){
        const btn = document.getElementById('payConfirmBtn');
        if (!btn) return;
        if (selectedMethod === 'cod') {
            btn.textContent = 'Place order (COD)';
        } else {
            btn.textContent = 'Pay ₹' + Number(toPay).toFixed(2);
        }
    }
    updateConfirmText();

    const payBtn = document.getElementById('payConfirmBtn');
    if (payBtn) {
        payBtn.addEventListener('click', () => {
            if (selectedMethod === 'cod') {
                window.location.href = 'process_order.php?method=cod';
                return;
            }
            initiateRazorpayPayment(toPay);
        });
    }

    function initiateRazorpayPayment(amountInRs){
        const totalAmountPaisa = Math.round(Number(amountInRs) * 100);
        const options = {
            key: "rzp_live_fcHSAUVkadcMaw",
            amount: totalAmountPaisa,
            currency: "INR",
            name: "Water Delivery App",
            description: "Payment for Water Order",
            image: "https://dummyimage.com/80x80/3498db/ffffff.png&text=Water",
            handler: function (response) {
                window.location.href = "process_order.php?method=razorpay&payment_id=" + encodeURIComponent(response.razorpay_payment_id);
            },
            prefill: {
                name: <?= json_encode($_SESSION['user_name'] ?? 'Customer') ?>,
                email: <?= json_encode($_SESSION['user_email'] ?? 'test@example.com') ?>,
                contact: <?= json_encode($_SESSION['mobile_number']) ?>
            },
            theme: { color: "#1a9c46" },
        };
        const rzp = new Razorpay(options);
        rzp.on('payment.failed', function (response){
            const reason = response.error.reason || 'Payment failed';
            window.location.href = "order_failed.php?reason=" + encodeURIComponent(reason);
        });
        rzp.open();
    }

    const addressCards = document.querySelectorAll('.saved-address');
    const addressSheetEl = document.getElementById('addressSheet');
    const addressSheet = addressSheetEl ? bootstrap.Modal.getOrCreateInstance(addressSheetEl) : null;
    addressCards.forEach(card => {
        card.addEventListener('click', async () => {
            const id = Number(card.getAttribute('data-id'));
            addressCards.forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            try {
                const res = await fetch('update_selected_address.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ address_id: id })
                });
                const data = await res.json();
                if (data.success) {
                    const title = document.querySelector('.address-row .title');
                    const sub = document.querySelector('.address-row .sub');
                    if (title && data.address_label) title.textContent = 'Delivering to ' + data.address_label;
                    if (sub && data.short_address) sub.textContent = data.short_address;
                    const enabled = (data.has_items && data.selected);
                    placeBtn.disabled = !enabled;
                    placeBtn.textContent = enabled ? ('Proceed to Checkout • ₹' + (data.to_pay ?? '')) : 'Add address to place order';
                    if (addressSheet) addressSheet.hide();
                }
            } catch(e) {
                console.error(e);
                if (addressSheet) addressSheet.hide();
            }
        });
    });
})();
</script>
</body>
</html>