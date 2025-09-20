<?php
session_start();
include('connection.php');

include('auth.php');


$userId = (int)($_SESSION['user_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Drohu Delivery App</title>
<meta name="theme-color" content="#0d6efd">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
    body{ font-family:Inter,system-ui,Segoe UI,Roboto,Arial,sans-serif; background:#f6f7fb; }
    .app-header{ position:fixed; top:0; left:0; right:0; z-index:1000; color:#fff; background: linear-gradient(135deg, #00c6ff, #0072ff); box-shadow:0 2px 10px rgba(13,110,253,.2); }
    .app-header .inner{ display:flex; align-items:center; justify-content:space-between; padding:14px 16px; }
    .brand{ display:flex; align-items:center; gap:10px; font-weight:800; }
    .brand .logo{ width:36px; height:36px; border-radius:10px; background:#fff; display:flex; align-items:center; justify-content:center; color:#0d6efd; }
    .loc{ font-size:12px; opacity:.95; margin-top:4px; }
    .hdr-actions{ display:flex; align-items:center; gap:12px; }
    .hdr-btn{ position:relative; width:38px; height:38px; border-radius:10px; background:rgba(255,255,255,.2); border:1px solid rgba(255,255,255,.35); color:#fff; display:flex; align-items:center; justify-content:center; }
    .hdr-btn:hover{ background:rgba(255,255,255,.3); }
    .cart-badge{ position:absolute; top:-6px; right:-6px; min-width:18px; height:18px; border-radius:10px; background:#ff4d4f; border:2px solid #0d6efd; color:#fff; font-size:11px; font-weight:800; display:flex; align-items:center; justify-content:center; padding:0 4px; }
    .search-row{ padding:0 16px 12px 16px; }
    .search-input{ width:100%; height:44px; border-radius:12px; border:1px solid rgba(255,255,255,.6); background:rgba(255,255,255,.95); padding:0 12px 0 42px; color:#0b1020; }
    .search-wrap{ position:relative;display:none; }
    .search-wrap i{ position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#6c757d; }
    .page{ padding:110px 0 24px; }
</style>
</head>
<body>

<header class="app-header">
    <div class="inner">
        <div>
            <div class="brand">
                <div class="logo"><i class="fa-solid fa-droplet"></i></div>
                <div>
                    <div style="line-height:1;">Drohu Waters</div>
                    <div class="loc"><i class="fa-solid fa-location-dot me-1"></i>Delivering near you</div>
                </div>
            </div>
        </div>
        <div class="hdr-actions">
            <a href="cart.php" class="hdr-btn" id="cartBtn" aria-label="Cart">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="cart-badge" id="cart-badge">0</span>
            </a>
            <a href="profile.php" class="hdr-btn">
                <i class="fa-solid fa-user"></i>
            </a>
        </div>
    </div>
</header>

<div class="page container">
  
</div>

<script>
(function(){
    const badge = document.getElementById('cart-badge');

    function updateCartBadge(count){
        const n = Number(count||0);
        if (!badge) return;
        badge.textContent = n;
        badge.style.display = n > 0 ? 'flex' : 'none';
    }

    async function refreshCartBadge(){
        try{
            const res = await fetch('cart_count.php', { credentials:'same-origin' });
            const data = await res.json();
            updateCartBadge(data.count ?? 0);
        }catch(e){ console.error(e); }
    }

    refreshCartBadge();
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refreshCartBadge(); });
    setInterval(refreshCartBadge, 1000);
    window.addEventListener('cart:updated', refreshCartBadge);
    window.addEventListener('storage', (e) => { if (e.key === 'cartUpdated') refreshCartBadge(); });

    // Handle Add to Cart buttons
    document.querySelectorAll('.add-to-cart').forEach(btn=>{
        btn.addEventListener('click', async()=>{
            const productId = btn.dataset.productId;
            try {
                const res = await fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ product_id: productId, quantity: 1 })
                });
                const data = await res.json();
                if(data.success){
                    updateCartBadge(data.cart_count);
                    localStorage.setItem('cartUpdated', Date.now().toString());
                    window.dispatchEvent(new Event('cart:updated'));
                }
            } catch(e){ console.error(e); }
        });
    });
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
