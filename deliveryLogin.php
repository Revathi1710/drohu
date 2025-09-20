<?php
session_start();
require_once __DIR__ . '/connection.php';

// If already logged in as delivery, go to dashboard
if (!empty($_SESSION['deliveryperson_id'])) {
  header('Location: deliveryDashboard.php');
  exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if ($username === '' || $password === '') {
    $error = 'Username and password are required.';
  } else {
    // Query: SELECT `id`, `deliveryperson_name`, `number`, `email`, `username`, `password`, `pincode` FROM `deliveryPerson`
    $sql = 'SELECT id, deliveryperson_name, number, email, username, password, pincode FROM deliveryPerson WHERE username = ? LIMIT 1';
    if ($stmt = $con->prepare($sql)) {
      $stmt->bind_param('s', $username);
      $stmt->execute();
      $result = $stmt->get_result();
      $user = $result ? $result->fetch_assoc() : null;
      $stmt->close();

      if ($user) {
        $storedPassword = (string)($user['password'] ?? '');

        $isValid = false;
        // If passwords stored as hashes, verify with password_verify; else fallback to plain match
        if (preg_match('/^\$2y\$|^\$argon2|^\$2a\$/', $storedPassword)) {
          $isValid = password_verify($password, $storedPassword);
        } else {
          $isValid = hash_equals($storedPassword, $password);
        }

        if ($isValid) {
          $_SESSION['deliveryperson_id'] = (int)$user['id'];
          $_SESSION['deliveryperson_name'] = $user['deliveryperson_name'];
          $_SESSION['deliveryperson_pincode'] = $user['pincode'];
          header('Location: deliveryDashboard.php');
          exit;
        }
      }

      $error = 'Invalid username or password.';
    } else {
      $error = 'Failed to prepare login query.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>Delivery Partner Login</title>
  <meta name="theme-color" content="#7b2ff7">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
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
      --radius-md:12px;
      --shadow-lg:0 16px 44px rgba(0,0,0,.18);
      --shadow-md:0 10px 32px rgba(0,0,0,.12);
    }
    *{box-sizing:border-box}
    body{
      margin:0; background:var(--bg); color:var(--text);
      font-family:'Poppins',system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;
      min-height:100dvh; display:flex; flex-direction:column;
    }
    .hero{
      background:linear-gradient(135deg,var(--brand-start),var(--brand-end));
      color:#fff;
      padding: clamp(20px,6vw,32px) 18px calc(28px + env(safe-area-inset-top));
      border-bottom-left-radius: var(--radius-xl);
      border-bottom-right-radius: var(--radius-xl);
      box-shadow: var(--shadow-lg);
    }
    .brand-title{
      font-weight:800; letter-spacing:.3px; margin:0;
      font-size:clamp(20px,5.2vw,26px);
    }
    .brand-sub{ opacity:.92; margin-top:6px; }
    .container-narrow{
      max-width: 460px; width:100%; margin: 0 auto; padding: 20px 16px 24px;
    }
    .login-card{
      border:0; border-radius: var(--radius-lg); background:var(--surface);
      box-shadow: var(--shadow-md);
    }
    .form-label{ font-weight:600; font-size:14px; margin-bottom:6px; }
    .input-group-text{ background:#f3f4f6; border:none; }
    .form-control{ border-radius: var(--radius-md); padding: 10px 12px; }
    .btn-primary{
      border:none; border-radius:14px; padding:12px 16px; font-weight:700; letter-spacing:.2px;
      background:linear-gradient(135deg,var(--brand-start),var(--brand-end));
      box-shadow:0 8px 24px rgba(241,7,163,.24);
    }
    .btn-primary:active{ transform: translateY(1px); }
    .back-link{ color:var(--muted); text-decoration:none; font-weight:600; }
    .back-link:hover{ text-decoration:underline; }
    .icon-badge{
      width:56px; height:56px; display:inline-flex; align-items:center; justify-content:center;
      border-radius:16px; background:#efe7ff; color:#5b36f0; font-size:24px;
    }
  </style>
</head>
<body>
  <header class="hero">
    <div class="container-narrow">
      <div class="d-flex align-items-center mb-2">
        <a href="home.php" class="text-white text-decoration-none"><i class="bi bi-chevron-left" style="font-size:20px;"></i></a>
        <span style="width:20px;"></span>
      
      <h1 class="brand-title">Delivery Partner</h1></div>
      <p class="brand-sub mb-0">Sign in to continue</p>
    </div>
  </header>

  <main class="container-narrow">
    <div class="login-card p-4 mt-3">
      <div class="d-flex align-items-center gap-2 mb-3">
        <div class="icon-badge"><i class="bi bi-truck"></i></div>
        <div>
          <div style="font-weight:700;">Welcome back</div>
          <div class="text-muted" style="font-size:13px;">Enter your credentials</div>
        </div>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger py-2" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <form method="post" action="">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" name="username" class="form-control" placeholder="Enter username" required>
          </div>
        </div>

        <div class="mb-2">
          <label class="form-label">Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
            <button class="btn btn-light border" type="button" id="togglePwd" aria-label="Show password"><i class="bi bi-eye"></i></button>
          </div>
        </div>

        <div class="d-grid mt-3">
          <button type="submit" class="btn btn-primary">Login</button>
        </div>

        <div class="text-center mt-3">
          <a href="choose-login.php" class="back-link">Back</a>
        </div>
      </form>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const toggle = document.getElementById('togglePwd');
    const pwd = document.getElementById('password');
    toggle?.addEventListener('click', () => {
      const isText = pwd.type === 'text';
      pwd.type = isText ? 'password' : 'text';
      toggle.innerHTML = isText ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
      toggle.setAttribute('aria-label', isText ? 'Show password' : 'Hide password');
    });
  </script>
</body>
</html>