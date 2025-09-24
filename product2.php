<?php
session_start();
require_once __DIR__ . '/connection.php';
include __DIR__ . '/header.php';
ini_set('display_errors', 1);

function getAllProducts() {
    global $con;
    if (!$con) return [];
    $query = "SELECT id, product_name, product_image, original_price, selling_price FROM product ORDER BY id ASC";
    $result = mysqli_query($con, $query);
    if (!$result) return [];
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) { $products[] = $row; }
    return $products;
}

$products = getAllProducts();
$totalProducts = count($products);

// Prefill stepper from cart
$userId = (int)($_SESSION['user_id'] ?? 0);
$productIdToQty = [];
if ($userId > 0 && isset($con)) {
    $q = $con->prepare("SELECT prod_id, quantity FROM addcart WHERE user_id = ?");
    $q->bind_param("i", $userId);
    $q->execute();
    $r = $q->get_result();
    while ($row = $r->fetch_assoc()) { $productIdToQty[(int)$row['prod_id']] = (int)$row['quantity']; }
    $q->close();
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function fmt($n){ return number_format((float)$n, 2); }
function derive_size($name, $category){
    if (preg_match('/(\d+)\s*l/i', (string)$name, $m)) return $m[1].'L';
    if (preg_match('/(\d+)\s*l/i', (string)$category, $m)) return $m[1].'L';
    return '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AquaFresh - Order</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
    :root {
        --primary-blue: #1E90FF;
        --secondary-blue: #E8F4FF;
        --light-grey: #eef2f7;
        --text-color: #0b1020;
        --muted-text: #6b7280;
        --border-color: #eef2f7;
        --success-green: #28a745;
        --danger-red: #dc3545;
    }
    body { 
        background: #fafbfc; 
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; 
    }
    .section-head { 
        display: flex; 
        align-items: center; 
        justify-content: space-between; 
        padding: 14px 16px 8px 16px; 
    }
    .section-head h6 { 
        margin: 0; 
        font-weight: 700; 
    }
    .product-list { 
        margin: 0 12px 80px 12px; 
        background: #fff; 
        border: 1px solid var(--border-color); 
        border-radius: 14px; 
        overflow: hidden; 
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    .product-row { 
        display: flex; 
        gap: 12px; 
        padding: 12px; 
        align-items: center; 
    }
    .product-row + .product-row { 
        border-top: 1px solid var(--border-color); 
    }
    .img-wrap { 
        position: relative; 
        width: 56px; 
        height: 56px; 
        flex: 0 0 56px; 
    }
    .row-img { 
        width: 56px; 
        height: 56px; 
        border-radius: 12px; 
        object-fit: cover; 
        background: #f4f6f9; 
        border: 1px solid var(--border-color); 
    }
    .size-pill { 
        position: absolute; 
        left: 6px; 
        bottom: -6px; 
        background: var(--secondary-blue); 
        color: var(--primary-blue); 
        border: 1px solid var(--primary-blue); 
        border-radius: 999px; 
        padding: 2px 8px; 
        font-weight: 800; 
        font-size: 11px; 
    }
    .row-title { 
        margin: 0; 
        font-size: 14px; 
        font-weight: 800; 
        line-height: 1.2; 
        color: var(--text-color); 
    }
    .row-price { 
        font-weight: 900; 
        color: var(--text-color); 
    }
    .row-mrp { 
        color: var(--muted-text); 
        text-decoration: line-through; 
        font-size: 12px; 
        font-weight: 700; 
        margin-left: 6px; 
    }
    .row-bottom { 
        display: flex; 
        align-items: center; 
        justify-content: space-between; 
    }
    .stepper { 
        display: flex; 
        align-items: center; 
        gap: 10px; 
    }
    .stepper button { 
        width: 30px; 
        height: 30px; 
        border-radius: 999px; 
        border: 1px solid #dfe4ea; 
        background: #fff; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        color: var(--primary-blue); 
        font-weight: 900;
        transition: transform 0.2s ease, background-color 0.2s ease;
    }
    .stepper button:hover {
        background: var(--secondary-blue);
    }
    .stepper button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f8f9fa;
    }
    .stepper .qty { 
        min-width: 22px; 
        text-align: center; 
        font-weight: 900; 
    }
    .bottom-nav { 
        position: fixed; 
        left: 0; 
        right: 0; 
        bottom: 0; 
        background: #fff; 
        border-top: 1px solid var(--border-color); 
        display: grid; 
        grid-template-columns: repeat(4, 1fr); 
        padding: 8px 0; 
        z-index: 50; 
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.05);
    }
    .bottom-nav button { 
        border: none; 
        background: transparent; 
        color: var(--muted-text); 
        display: flex; 
        flex-direction: column; 
        align-items: center; 
        gap: 4px; 
        font-size: 11px; 
        transition: color 0.2s ease;
        position: relative; /* Added for the badge */
    }
    .bottom-nav .active { 
        color: var(--primary-blue); 
    }
    .notification-badge {
        position: absolute;
        top: 0;
        right: 15px;
        background-color: var(--danger-red);
        color: white;
        font-size: 10px;
        font-weight: bold;
        padding: 2px 6px;
        border-radius: 50%;
        line-height: 1;
        display: none; /* Initially hidden */
    }
    .bottom-nav-cart {
        position: fixed;
        left: 50%;
        bottom: 70px;
        transform: translateX(-50%);
        width: 90%;
        max-width: 380px;
        z-index: 60;
    }
    .cart-btn {
        width: 100%;
        background: linear-gradient(135deg, var(--primary-blue), #0056b3);
        color: white;
        padding: 12px 20px;
        font-size: 16px;
        font-weight: 700;
        border-radius: 999px;
        border: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 8px 16px rgba(30, 144, 255, 0.3);
    }
    .cart-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 20px rgba(30, 144, 255, 0.4);
    }
    .cart-btn:active {
        transform: translateY(0);
    }
    .cart-details span {
        font-size: 14px;
        opacity: 0.8;
    }
    .cart-details strong {
        font-size: 18px;
        font-weight: 900;
    }
    .cart-btn-icon {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        padding: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .cart-btn-text {
        flex-grow: 1;
        text-align: center;
    }

    /* Other styles */
    .closed-chip { display: inline-block; background: #fde68a; color: #713f12; border: 1px solid #fcd34d; border-radius: 999px; padding: 4px 10px; font-weight: 800; font-size: 12px; margin-top: 10px; }
    .snackbar { position: fixed; left: 50%; bottom: 18px; transform: translateX(-50%) translateY(100px); background: #111827; color: #fff; border: 1px solid #374151; border-radius: 12px; padding: 10px 14px; font-weight: 700; opacity: 0; z-index: 2500; transition: all .25s ease; }
    .snackbar.show { opacity: 1; transform: translateX(-50%) translateY(0); }
    .snackbar.warn { background: #78350f; border-color: #f59e0b; }
    .snackbar.err { background: #7f1d1d; border-color: #ef4444; }
</style>
</head>
<body>

<div class="section-head">
    <h6>All Products</h6>
    <div class="text-muted small"><?= (int)$totalProducts ?> items</div>
</div>

<div class="product-list" id="productsList">
    <?php foreach ($products as $p): ?>
        <?php
            $pid  = (int)$p['id'];
            $name = $p['product_name'] ?? '';
            $img  = $p['product_image'] ?? '';
            $mrp  = (float)($p['original_price'] ?? 0);
            $sp   = (float)($p['selling_price'] ?? 0);
            $cat  = $p['category'] ?? '';
            $size = derive_size($name, $cat);
            $qty  = (int)($productIdToQty[$pid] ?? 0);
        ?>
        <div class="product-row" data-id="<?= $pid ?>">
            <div class="img-wrap">
                <img class="row-img" src="./<?= h($img) ?>" alt="<?= h($name) ?>"
                     onerror="this.src='https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=160&h=160&fit=crop&crop=center'">
                <?php if ($size): ?><div class="size-pill"><?= h($size) ?></div><?php endif; ?>
            </div>
            <div class="flex-grow-1">
                <h4 class="row-title"><?= h($name) ?></h4>
                <div class="row-bottom">
                    <div>
                        <span class="row-price">&#8377;<?= fmt($sp) ?></span>
                        <?php if ($mrp > $sp): ?><span class="row-mrp">&#8377;<?= fmt($mrp) ?></span><?php endif; ?>
                    </div>
                    <div class="stepper" data-id="<?= $pid ?>" data-name="<?= h($name) ?>" data-price="<?= $sp ?>" data-image="<?= h($img) ?>">
                        <button class="btn-minus" aria-label="decrease" <?= $qty <= 0 ? 'disabled' : '' ?>>−</button>
                        <div class="qty" data-qty><?= $qty ?></div>
                        <button class="btn-plus" aria-label="increase">+</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (!$products): ?>
        <div class="p-4 text-center text-muted">No products found</div>
    <?php endif; ?>
</div>

<nav class="bottom-nav-cart" id="cartSummaryBtn">
    <button class="cart-btn" onclick="location.href='cart.php'">
        <span id="cart-item-count" class="cart-details">0 Items</span>
        <span class="cart-btn-text">View Cart</span>
        <span id="cart-total-price" class="cart-details">&#8377;0.00</span>
    </button>
</nav>

<nav class="bottom-nav">
    <button class="active"><i class="fa-solid fa-house"></i><span>Home</span></button>
    <button onclick="location.href='orders.php'"><i class="fa-solid fa-bag-shopping"></i><span>Orders</span></button>
    <button onclick="location.href='profile.php'"><i class="fa-solid fa-user"></i><span>Profile</span></button>
    <button id="bellButton"><i class="fa-solid fa-bell"></i><span>Bell</span><span class="notification-badge" id="unreadCountBadge">0</span></button>
</nav>

<div class="modal fade" id="closedModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center" style="border-radius: 12px; border: 1px solid #e0e0e0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
      <div class="modal-body p-5">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#6c757d" class="bi bi-clock" viewBox="0 0 16 16">
          <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/>
          <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7.5-8a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0"/>
        </svg>
        <h4 class="closed-title mt-3 mb-2" id="closedTitle" style="font-weight: 600; color: #343a40;">We're closed right now</h4>
        <p class="closed-sub" id="closedMsg" style="color: #6c757d;">Ordering is unavailable today. Please check back later.</p>
        <div class="closed-chip mt-3" id="reopenChip" style="display: none;"></div>
      </div>
      <div class="modal-footer border-0 d-flex justify-content-center pb-4">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" style="width: 100%; max-width: 200px; padding: 10px; border-radius: 8px; background-color: #007bff; border: none; font-weight: 500; transition: background-color 0.2s;">
          Okay
        </button>
      </div>
    </div>
  </div>
</div>

<div id="snackbar" class="snackbar" role="status"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Logic for stepper buttons (add/remove items)
(function(){
    const list = document.getElementById('productsList');
    async function postJSON(url, payload){
        try{
            const res = await fetch(url, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload) });
            return await res.json().catch(()=>({}));
        }catch(e){ console.error(e); return {}; }
    }
    list.addEventListener('click', async (e) => {
        const minus = e.target.closest('.btn-minus');
        const plus  = e.target.closest('.btn-plus');
        if (!minus && !plus) return;
        if (window.__IS_CLOSED_TODAY__) {
            e.preventDefault();
            window.__toast && window.__toast('Store is closed right now', 'err');
            return;
        }
        const stepper = e.target.closest('.stepper');
        const qtyEl = stepper.querySelector('[data-qty]');
        let qty = parseInt(qtyEl.textContent || '0', 10);
        const productId = parseInt(stepper.dataset.id, 10);
        const name  = stepper.dataset.name;
        const price = parseFloat(stepper.dataset.price);
        const image = stepper.dataset.image;

        if (plus) {
            qty++;
            await postJSON('add_to_cart.php', { id: productId, name, price, image, delta: +1 });
        } else if (minus) {
            qty = Math.max(0, qty - 1);
            await postJSON('add_to_cart.php', { id: productId, name, price, image, delta: -1 });
        }
        
        qtyEl.textContent = qty;
        stepper.querySelector('.btn-minus').disabled = qty <= 0;
        
        // Trigger a custom event to update the cart summary
        window.dispatchEvent(new Event('cart:updated'));
    });
})();
</script>

<script>
// Logic for checking store closed status
(function(){
    const toastEl = document.getElementById('snackbar');
    let isClosed = false;
    function showToast(msg, type){
        if (!toastEl) return;
        toastEl.className = 'snackbar ' + (type||'');
        toastEl.textContent = msg;
        requestAnimationFrame(()=>toastEl.classList.add('show'));
        setTimeout(()=>toastEl.classList.remove('show'), 2500);
    }
    window.__toast = showToast;
    function setSteppersDisabled(disabled){
        document.querySelectorAll('.stepper .btn-plus, .stepper .btn-minus').forEach(b=>{
            b.disabled = disabled || (b.classList.contains('btn-minus') && (b.closest('.stepper')?.querySelector('[data-qty]')?.textContent.trim() === '0'));
        });
        document.querySelectorAll('.stepper').forEach(s=>s.style.opacity = disabled?'.5':'');
    }
    async function checkClosedStatus(){
        try{
            const res = await fetch('closed_today_status.php', { cache:'no-store' });
            const data = await res.json();
            if (!data || !data.ok) return;
            isClosed = !!data.isClosedToday;
            window.__IS_CLOSED_TODAY__ = isClosed;
            if (isClosed){
                document.getElementById('closedTitle').textContent = data.headline || 'We’re closed right now';
                document.getElementById('closedMsg').textContent = data.todayReason || 'Ordering is unavailable today.';
                const reopenChip = document.getElementById('reopenChip');
                if (data.reopenAt){
                    reopenChip.style.display = '';
                    reopenChip.textContent = 'Reopens at ' + data.reopenAt;
                } else { reopenChip.style.display = 'none'; }
                const closedModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('closedModal'));
                closedModal.show();
                setSteppersDisabled(true);
            } else {
                setSteppersDisabled(false);
                if (data.isClosedTomorrow){
                    showToast('Heads up: closed tomorrow. Order today.', 'warn');
                }
            }
        }catch(e){ /* silent */ }
    }
    document.addEventListener('visibilitychange', ()=>{ if (!document.hidden) checkClosedStatus(); });
    checkClosedStatus();
    setInterval(checkClosedStatus, 10*60*1000);
})();
</script>

<script>
// Logic for updating the floating cart button
(function(){
    const cartSummaryBtn = document.getElementById('cartSummaryBtn');
    const itemCountEl = document.getElementById('cart-item-count');
    const totalPriceEl = document.getElementById('cart-total-price');

    async function refreshCartSummary(){
        try {
            const res = await fetch('cart_summary.php', { credentials: 'same-origin' });
            const data = await res.json();
            
            const totalItems = data.total_items ?? 0;
            const totalPrice = data.total_price ?? 0;
            
            itemCountEl.textContent = totalItems > 0 ? `${totalItems} Items` : '0 Items';
            totalPriceEl.textContent = `₹${totalPrice.toFixed(2)}`;
            
            if (totalItems > 0) {
                cartSummaryBtn.style.display = 'block';
            } else {
                cartSummaryBtn.style.display = 'none';
            }
        } catch(e) {
            console.error('Failed to fetch cart summary:', e);
            cartSummaryBtn.style.display = 'none';
        }
    }

    refreshCartSummary();
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refreshCartSummary(); });
    window.addEventListener('cart:updated', refreshCartSummary);
    setInterval(refreshCartSummary, 5000); 
})();
</script>

<script>
// New logic for unread broadcast messages
(function() {
    const unreadCountBadge = document.getElementById('unreadCountBadge');
    const bellButton = document.getElementById('bellButton');

    // Function to fetch the unread count from the server
    async function fetchUnreadCount() {
        try {
            const res = await fetch('get_unread_count.php', { cache: 'no-store' });
            const data = await res.json();
            const unreadCount = parseInt(data.unread_count, 10) || 0;
            if (unreadCount > 0) {
                unreadCountBadge.textContent = unreadCount;
                unreadCountBadge.style.display = 'block';
            } else {
                unreadCountBadge.style.display = 'none';
            }
        } catch (e) {
            console.error('Failed to fetch unread count:', e);
            unreadCountBadge.style.display = 'none';
        }
    }

    // Function to mark all broadcasts as read
    async function markBroadcastsAsRead() {
        try {
            await fetch('mark_as_read.php', { method: 'POST', body: JSON.stringify({ action: 'read_all' }), headers: { 'Content-Type': 'application/json' }});
            unreadCountBadge.style.display = 'none';
            unreadCountBadge.textContent = '0';
        } catch (e) {
            console.error('Failed to mark broadcasts as read:', e);
        }
    }

    // Set up click handler for the bell button
    if (bellButton) {
        bellButton.addEventListener('click', () => {
            // Check if there are unread messages to clear
            if (parseInt(unreadCountBadge.textContent, 10) > 0) {
                markBroadcastsAsRead();
            }
            // Add navigation logic here, e.g., to a notifications page
            window.location.href = 'notifications.php';
        });
    }

    // Fetch the count on page load and periodically
    fetchUnreadCount();
    setInterval(fetchUnreadCount, 60000); // Poll every 60 seconds
})();
</script>

</body>
</html>