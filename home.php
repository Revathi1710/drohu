<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Choose Login - Drohu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <meta name="theme-color" content="#1e90ff">
    <style>
        :root {
            --brand-main: #1e90ff;
            --brand-dark: #0056b3;
            --brand-light: #f0f8ff;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --surface-bg: #f8f9fa;
            --card-bg: #ffffff;
            --shadow-md: 0 4px 12px rgba(0,0,0,0.1);
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.15);
            --radius-lg: 1.25rem;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--surface-bg);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .hero {
            background-color: var(--brand-dark);
            color: #fff;
            padding: 3rem 1.5rem 4rem;
            border-bottom-left-radius: var(--radius-lg);
            border-bottom-right-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            text-align: center;
        }

        .brand-title {
            font-weight: 800;
            font-size: clamp(2rem, 5vw, 2.5rem);
            letter-spacing: -0.5px;
            margin-bottom: 0.5rem;
        }

        .brand-sub {
            opacity: 0.9;
            font-size: clamp(0.9rem, 2.5vw, 1.1rem);
        }

        .container-max {
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
            padding: 1.5rem 1rem;
        }

        .login-card {
            border: none;
            border-radius: 1rem;
            background-color: var(--card-bg);
            box-shadow: var(--shadow-md);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .login-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .icon-badge {
            width: 5rem;
            height: 5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: var(--brand-light);
            color: var(--brand-main);
            font-size: 2.25rem;
            margin: 0 auto 1.5rem;
        }

        .btn-cta {
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-weight: 700;
            letter-spacing: 0.2px;
            text-decoration: none;
            text-align: center;
        }

        .btn-primary {
            background-color: var(--brand-main);
            color: #fff;
            box-shadow: 0 4px 12px rgba(30, 144, 255, 0.3);
            transition: background-color 0.2s ease;
        }

        .btn-primary:hover {
            background-color: var(--brand-dark);
        }

        .btn-outline-secondary {
            background-color: transparent;
            border: 2px solid var(--text-secondary);
            color: var(--text-secondary);
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .btn-outline-secondary:hover {
            background-color: var(--text-secondary);
            color: #fff;
        }

        .footer-help {
            color: var(--text-secondary);
            font-size: 0.85rem;
            text-align: center;
            margin-top: auto;
            padding: 1rem 0;
        }
    </style>
</head>
<body>
    <header class="hero">
        <div class="container-max">
            <h1 class="brand-title">Drohu</h1>
            <p class="brand-sub">Your trusted partner for fresh water delivery.</p>
        </div>
    </header>

    <main class="container-max">
        <div class="row g-4 mt-1">
            <div class="col-md-6">
                <div class="login-card h-100 p-4 text-center">
                    <div class="icon-badge"><i class="bi bi-person-fill"></i></div>
                    <h4 class="card-title mb-2 fw-bold">Customer</h4>
                    <p class="text-secondary mb-4">Order and manage your water deliveries effortlessly.</p>
                    <a href="login.php" class="btn btn-primary btn-cta w-100">Customer Login</a>
                </div>
            </div>

            <div class="col-md-6">
                <div class="login-card h-100 p-4 text-center">
                    <div class="icon-badge"><i class="bi bi-truck"></i></div>
                    <h4 class="card-title mb-2 fw-bold">Delivery Partner</h4>
                    <p class="text-secondary mb-4">Accept and manage delivery assignments on the go.</p>
                    <a href="deliveryLogin.php" class="btn btn-outline-secondary btn-cta w-100">Partner Login</a>
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