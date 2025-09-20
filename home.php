<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>Choose Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
  <meta name="theme-color" content="#7b2ff7">
  <style>
    :root{
      --brand-start:#7b2ff7;
      --brand-end:#f107a3;
      --text:#1f2937;
      --muted:#6b7280;
      --surface:#ffffff;
      --bg:#fafafa;
      --radius-xl:22px;
      --radius-lg:18px;
      --shadow-lg:0 16px 44px rgba(0,0,0,.18);
      --shadow-md:0 10px 32px rgba(0,0,0,.12);
    }
    *{box-sizing:border-box}
    body{
      margin:0;background:var(--bg);color:var(--text);
      font-family:'Poppins',system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;
      min-height:100dvh; display:flex; flex-direction:column;
    }
    .hero{
      background:linear-gradient(135deg,var(--brand-start),var(--brand-end));
      color:#fff; padding: clamp(20px,6vw,32px) 20px calc(28px + env(safe-area-inset-top));
      border-bottom-left-radius: var(--radius-xl);
      border-bottom-right-radius: var(--radius-xl);
      box-shadow: var(--shadow-lg);
    }
    .brand-title{ font-weight:800; letter-spacing:.3px; margin:0; font-size:clamp(20px,5.2vw,26px); }
    .brand-sub{ opacity:.92; margin-top:6px; }
    .container-max{ max-width: 900px; margin: 0 auto; width:100%; padding: 20px 16px 24px; }
    .login-card{
      border:none; border-radius: var(--radius-lg); background:var(--surface);
      box-shadow: var(--shadow-md); transition: transform .2s ease, box-shadow .2s ease;
      overflow:hidden;
    }
    .login-card:hover{ transform: translateY(-2px); box-shadow: 0 16px 40px rgba(0,0,0,.16); }
    .icon-badge{
      width:60px; height:60px; display:inline-flex; align-items:center; justify-content:center;
      border-radius:16px; background:#efe7ff; color:#5b36f0; font-size:26px;
    }
    .btn-cta{
      border:none; border-radius:14px; padding:12px 16px; font-weight:700; letter-spacing:.2px;
    }
    .btn-primary{
      background:linear-gradient(135deg,var(--brand-start),var(--brand-end)); box-shadow:0 8px 24px rgba(241,7,163,.24);
    }
    .btn-outline{
      background:#fff; border:2px solid #efe7ff; color:#5b36f0;
    }
    .footer-help{
      color:var(--muted); font-size:13px; text-align:center; margin-top:auto; padding: 8px 0 calc(8px + env(safe-area-inset-bottom));
    }
  </style>
  <link rel="icon" href="data:,">
</head>
<body>
  <header class="hero">
    <div class="container-max">
      <h1 class="brand-title">Welcome to <span style="opacity:.98;">YourApp</span></h1>
      <p class="brand-sub mb-0">Choose how you want to sign in</p>
    </div>
  </header>

  <main class="container-max">
    <div class="row g-4 mt-1">
      <div class="col-md-6">
        <div class="login-card h-100">
          <div class="card-body p-4 d-flex flex-column">
            <div class="icon-badge mb-3"><i class="bi bi-bag-heart"></i></div>
            <h4 class="card-title mb-1">Customer</h4>
            <p class="text-muted mb-4">Shop your essentials and manage your orders.</p>
            <div class="mt-auto">
              <a href="login.php" class="btn btn-primary btn-cta w-100">Customer Login</a>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="login-card h-100">
          <div class="card-body p-4 d-flex flex-column">
            <div class="icon-badge mb-3"><i class="bi bi-truck"></i></div>
            <h4 class="card-title mb-1">Delivery Partner</h4>
            <p class="text-muted mb-4">Deliver orders in your area and track assignments.</p>
            <div class="mt-auto">
              <a href="deliveryLogin.php" class="btn btn-outline btn-cta w-100">Delivery Person Login</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <div class="footer-help">
    Need help? <a href="#" class="text-decoration-none">Contact support</a>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>