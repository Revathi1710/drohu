<?php
// add_address.php
session_start();
include('connection.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// PHP for server-side processing remains the same
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('Please log in to continue.'); window.location='login.php';</script>";
        exit;
    }
    $user_id = (int)$_SESSION['user_id'];
    $door_no = trim($_POST['door_no'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $address_label = trim($_POST['address_label'] ?? 'Home');
    $street_address = trim($_POST['street_address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $area = trim($_POST['area'] ?? '');
    $receiver_name = trim($_POST['receiver_name'] ?? '');
    $receiver_number = trim($_POST['receiver_number'] ?? '');

    $errors = [];
    if (strlen($pincode) !== 6 || !ctype_digit($pincode)) $errors[] = "Pincode must be exactly 6 digits";
    if (strlen($receiver_number) !== 10 || !ctype_digit($receiver_number)) $errors[] = "Mobile number must be exactly 10 digits";
    if ($door_no === '' || $address_label === '' || $street_address === '' || $area === '' || $receiver_name === '' || $city === '' || $state === '') $errors[] = "All fields are required";

    if (!empty($errors)) {
        // Correctly handle errors on the client-side
        $error_message = implode('. ', $errors);
        echo "<script>window.addEventListener('DOMContentLoaded', () => showErrorMessage('$error_message'));</script>";
    } else {
        $sql = "INSERT INTO address_details 
                (door_no, pincode, address_label, street_address, city, state, area, receiver_name, receiver_number, user_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("sisssssssi", $door_no, $pincode, $address_label, $street_address, $city, $state, $area, $receiver_name, $receiver_number, $user_id);
        if ($stmt->execute()) {
            $_SESSION['selected_address_id'] = $con->insert_id;
            echo "<script>window.addEventListener('DOMContentLoaded', () => { showSuccessMessage('Address saved successfully!'); setTimeout(() => location.href='cart.php', 1200); });</script>";
        } else {
            echo "<script>window.addEventListener('DOMContentLoaded', () => showErrorMessage('Error saving address. Please try again.'));</script>";
        }
    }
}
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
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
    .z-header{position:sticky;top:0;z-index:10;color:#fff;background:linear-gradient(135deg,#1a9cfa 0%,#0d6efd 100%);border-radius:0 0 18px 18px;box-shadow:0 8px 24px rgba(0,0,0,.15)}
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
    
    /* NEW: Error styling */
    .group.error .label { color: var(--bad); }
    .group.error .input,
    .group.error .select,
    .group.error .textarea { border-color: var(--bad); }

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
        <div class="z-title">Add Address</div>
        <a href="addresses.php" class="btn-icon" aria-label="Close"><i class="fa-solid fa-xmark"></i></a>
    </div>
</header>

<div class="wrap">
    <div class="card section">
        <div id="successBox" class="alert ok"><i class="fa-solid fa-check me-1"></i><span>Saved</span></div>
        <div id="errorBox" class="alert err"><i class="fa-solid fa-circle-exclamation me-1"></i><span>Error</span></div>

        <form id="addrForm" method="POST" action="">
            <div class="group">
                <span class="label">Address label</span>
                <div class="label-chips" id="chips">
                    <button type="button" class="chip active" data-val="Home">Home</button>
                    <button type="button" class="chip" data-val="Work">Work</button>
                    <button type="button" class="chip" data-val="Other">Other</button>
                </div>
                <input type="hidden" name="address_label" id="address_label" value="Home">
            </div>

            <div class="row2">
                <div class="group">
                    <label class="label">Door / Flat no.</label>
                    <input class="input" name="door_no" id="door_no" placeholder="e.g. 12B" required>
                </div>
                <div class="group">
                    <label class="label">Pincode</label>
                    <input class="input" name="pincode" id="pincode" maxlength="6" inputmode="numeric" placeholder="6 digits" required>
                </div>
            </div>

            <div class="group">
                <label class="label">Street & Area</label>
                <textarea class="textarea" name="street_address" id="street_address" placeholder="House/building, street, landmark" required></textarea>
            </div>

            <div class="group">
                <label class="label">Locality / Post Office</label>
                <select class="select" name="area" id="area" required>
                    <option value="">Select area (auto after pincode)</option>
                </select>
                <div class="muted">Tip: Fill pincode to auto-populate locality, city and state</div>
            </div>

            <div class="row2">
                <div class="group">
                    <label class="label">City</label>
                    <input class="input readonly" name="city" id="city" readonly required>
                </div>
                <div class="group">
                    <label class="label">State</label>
                    <input class="input readonly" name="state" id="state" readonly required>
                </div>
            </div>

            <div class="row2">
                <div class="group">
                    <label class="label">Receiver name</label>
                    <input class="input" name="receiver_name" id="receiver_name" placeholder="Full name" required>
                </div>
                <div class="group">
                    <label class="label">Mobile number</label>
                    <input class="input" name="receiver_number" id="receiver_number" maxlength="10" inputmode="numeric" placeholder="10 digits" required>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="sticky">
    <div class="foot">
        <a href="addresses.php" class="btn btn-outline">Cancel</a>
        <button class="btn btn-primary" id="saveBtn" type="submit" form="addrForm">Save address</button>
    </div>
</div>

<script>
(function(){
    const chips = document.getElementById('chips');
    const labelInput = document.getElementById('address_label');
    chips.addEventListener('click', (e) => {
        const chip = e.target.closest('.chip');
        if (!chip) return;
        chips.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        labelInput.value = chip.dataset.val;
    });

    // Helpers
    const successBox = document.getElementById('successBox');
    const errorBox = document.getElementById('errorBox');
    window.showSuccessMessage = (m) => { successBox.querySelector('span').textContent = m; successBox.style.display = 'block'; setTimeout(() => successBox.style.display = 'none', 4000); };
    window.showErrorMessage = (m) => { errorBox.querySelector('span').textContent = m; errorBox.style.display = 'block'; setTimeout(() => errorBox.style.display = 'none', 5000); };

    // Numeric guards
    const pincode = document.getElementById('pincode');
    const phone = document.getElementById('receiver_number');
    pincode.addEventListener('input', () => pincode.value = pincode.value.replace(/[^0-9]/g, '').slice(0, 6));
    phone.addEventListener('input', () => phone.value = phone.value.replace(/[^0-9]/g, '').slice(0, 10));

    // Pincode -> populate area/city/state (India)
    const areaSel = document.getElementById('area');
    const cityInp = document.getElementById('city');
    const stateInp = document.getElementById('state');

    async function fetchPincode(pin) {
        areaSel.innerHTML = '<option value="">Loading areas…</option>';
        try {
            const resp = await fetch('https://api.postalpincode.in/pincode/' + pin);
            const data = await resp.json();
            if (data[0]?.Status === 'Success') {
                const list = data[0].PostOffice || [];
                cityInp.value = list[0]?.District || '';
                stateInp.value = list[0]?.State || '';
                areaSel.innerHTML = '<option value="">Select your area</option>';
                list.forEach(po => {
                    const opt = document.createElement('option');
                    opt.value = po.Name;
                    opt.textContent = po.Name;
                    areaSel.appendChild(opt);
                });
            } else {
                cityInp.value = stateInp.value = '';
                areaSel.innerHTML = '<option value="">Select area</option>';
                showErrorMessage('Invalid pincode. Please check and try again.');
            }
        } catch (e) {
            cityInp.value = stateInp.value = '';
            areaSel.innerHTML = '<option value="">Select area</option>';
            showErrorMessage('Unable to fetch pincode details. Try again.');
        }
    }

    pincode.addEventListener('blur', () => {
        if (pincode.value.length === 6) fetchPincode(pincode.value);
    });

    // NEW: Function to validate and highlight fields
    const fieldsToValidate = ['door_no', 'pincode', 'street_address', 'area', 'city', 'state', 'receiver_name', 'receiver_number'];
    const form = document.getElementById('addrForm');

    function validateAndHighlight() {
        let formIsValid = true;
        
        fieldsToValidate.forEach(id => {
            const el = document.getElementById(id);
            const group = el.closest('.group');
            const isValid = el.value.trim() !== '';

            if (isValid) {
                group.classList.remove('error');
            } else {
                group.classList.add('error');
                formIsValid = false;
            }
        });
        
        // Specific length validations for pincode and phone number
        const pincodeIsValid = pincode.value.length === 6;
        const phoneIsValid = phone.value.length === 10;
        if (!pincodeIsValid) {
            document.getElementById('pincode').closest('.group').classList.add('error');
            formIsValid = false;
        }
        if (!phoneIsValid) {
            document.getElementById('receiver_number').closest('.group').classList.add('error');
            formIsValid = false;
        }

        return formIsValid;
    }

    // NEW: Event listener for form submission
    form.addEventListener('submit', function(event) {
        // Prevent default form submission
        event.preventDefault();

        // Run validation and submit if valid
        if (validateAndHighlight()) {
            this.submit();
        } else {
            // Optional: Scroll to the first error
            const firstError = document.querySelector('.group.error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
})();
</script>
</body>
</html>