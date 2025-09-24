<?php
session_start();
include('connection.php');

// Assuming auth.php handles authentication checks
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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
<style>
    :root {
        --primary-color: #0072ff;
        --secondary-color: #00c6ff;
        --text-color-light: #fff;
        --text-color-dark: #212529;
        --background-light: #f6f7fb;
        --card-bg: #fff;
        --border-color: #dee2e6;
        --box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        --header-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    body { 
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; 
        background: var(--background-light); 
        color: var(--text-color-dark);
    }

    .app-header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        background:linear-gradient(135deg, var(--primary-blue), #0056b3);
        box-shadow: var(--header-shadow);
        color: var(--text-color-light);
        padding: 16px 0;
    }
    .app-header .inner {
        padding: 0 16px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .brand {
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 700;
        font-size: 1.1rem;
    }
    .brand .logo {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
        font-size: 1.2rem;
    }
    .loc {
        font-size: 12px;
        opacity: 0.9;
        margin-top: 2px;
        font-weight: 400;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .hdr-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .hdr-btn {
        position: relative;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.35);
        color: var(--text-color-light);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s ease;
    }
    .hdr-btn:hover {
        background: rgba(255, 255, 255, 0.3);
    }
    .cart-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        min-width: 20px;
        height: 20px;
        border-radius: 12px;
        background: #ff4d4f;
        border: 2px solid var(--primary-color);
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .search-row {
        width: 100%;
        position: relative;
    }
    .search-input {
        width: 100%;
        height: 48px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.6);
        background: rgba(255, 255, 255, 0.95);
        padding: 0 12px 0 48px;
        color: var(--text-color-dark);
        font-size: 15px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .search-input:focus {
        outline: none;
        border-color: #0d6efd;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.25);
    }
    .search-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--muted-text);
        color: #6c757d;
        font-size: 1rem;
    }
    .page {
        padding: 130px 0 24px;
    }
    
</style>
</head>
<body>

<header class="app-header">
    <div class="inner">
        <div class="header-top">
            <div class="brand">
                <div class="logo"><i class="fa-solid fa-droplet"></i></div>
                <div>
                    <div style="line-height:1;">Drohu Waters</div>
                    <div class="loc">
                        <i class="fa-solid fa-location-dot me-1"></i>
                        <span>Delivering near you</span>
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
      <div class="search-row">
    <form action="search.php" method="GET" class="search-form">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <input type="text" class="search-input" id="searchInput" name="query" placeholder="Search for products">
    </form>
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

    // Handle Add to Cart buttons (moved logic)
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