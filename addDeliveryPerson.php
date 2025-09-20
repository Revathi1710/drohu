<?php
session_start();
require_once __DIR__ . '/connection.php';
include __DIR__ . '/sidebar.php';

$successMessage = '';
$errorMessage = '';

function is_valid_name(string $name): bool {
  return $name !== '' && mb_strlen($name) <= 80;
}
function is_valid_mobile(string $mobile): bool {
  return preg_match('/^\d{10}$/', $mobile) === 1;
}
function is_valid_email(string $email): bool {
  return filter_var($email, FILTER_VALIDATE_EMAIL) !== false && mb_strlen($email) <= 120;
}
function is_valid_username(string $username): bool {
  return preg_match('/^[A-Za-z0-9._-]{4,32}$/', $username) === 1;
}
function is_valid_pincode(string $pincode): bool {
  return preg_match('/^\d{6}$/', $pincode) === 1;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $deliveryperson_name = trim($_POST['deliveryperson_name'] ?? '');
  $number              = trim($_POST['number'] ?? '');
  $email               = trim($_POST['email'] ?? '');
  $username            = trim($_POST['username'] ?? '');
  $password_plain      = (string)($_POST['password'] ?? '');
  $pincode             = trim($_POST['pincode'] ?? '');

  if (!is_valid_name($deliveryperson_name)) {
    $errorMessage = 'Please enter a valid full name.';
  } elseif (!is_valid_mobile($number)) {
    $errorMessage = 'Please enter a valid 10-digit mobile number.';
  } elseif (!is_valid_email($email)) {
    $errorMessage = 'Please enter a valid email address.';
  } elseif (!is_valid_username($username)) {
    $errorMessage = 'Username must be 4-32 chars (letters, numbers, dot, dash, underscore).';
  } elseif (mb_strlen($password_plain) < 6) {
    $errorMessage = 'Password must be at least 6 characters.';
  } elseif (!is_valid_pincode($pincode)) {
    $errorMessage = 'Please enter a valid 6-digit pincode.';
  } else {
    // Duplicate checks
    $dupStmt = $con->prepare('SELECT username, email, number FROM deliveryPerson WHERE username = ? OR email = ? OR number = ? LIMIT 1');
    if ($dupStmt) {
      $dupStmt->bind_param('sss', $username, $email, $number);
      $dupStmt->execute();
      $dup = $dupStmt->get_result()->fetch_assoc();
      $dupStmt->close();

      if ($dup) {
        if (isset($dup['username']) && $dup['username'] === $username) {
          $errorMessage = 'Username already taken.';
        } elseif (isset($dup['email']) && $dup['email'] === $email) {
          $errorMessage = 'Email already in use.';
        } elseif (isset($dup['number']) && $dup['number'] === $number) {
          $errorMessage = 'Mobile number already in use.';
        }
      } else {
        

        $sql = 'INSERT INTO deliveryPerson (deliveryperson_name, number, email, username, password, pincode)
                VALUES (?, ?, ?, ?, ?, ?)';
        $stmt = $con->prepare($sql);
        if ($stmt) {
          $stmt->bind_param(
            'ssssss',
            $deliveryperson_name,
            $number,
            $email,
            $username,
            $password_plain,
            $pincode
          );
          if ($stmt->execute()) {
            $successMessage = 'Delivery person added successfully.';
            // Clear form values
            $deliveryperson_name = $number = $email = $username = $pincode = '';
          } else {
            $errorMessage = 'Insert failed. Please try again.';
          }
          $stmt->close();
        } else {
          $errorMessage = 'Failed to prepare insert statement.';
        }
      }
    } else {
      $errorMessage = 'Failed to prepare duplicate check.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>Add Delivery Person</title>
  <meta name="theme-color" content="#7b2ff7">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
  <style>
    :root{
      --brand-start:#7b2ff7; --brand-end:#f107a3;
      --text:#1f2937; --muted:#6b7280; --surface:#ffffff; --bg:#fafafa;
      --radius-xl:22px; --radius-lg:18px; --radius-md:12px;
      --shadow-lg:0 16px 44px rgba(0,0,0,.18); --shadow-md:0 10px 32px rgba(0,0,0,.12);
    }
    *{box-sizing:border-box}
    body{ background:var(--bg); color:var(--text); font-family:'Poppins',system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif; }
     .main-container{ padding:16px; margin-left:var(--sidebar-width); }
    .hero{
      background:linear-gradient(135deg,var(--brand-start),var(--brand-end));
      color:#fff; padding: clamp(16px,5vw,28px) 18px calc(22px + env(safe-area-inset-top));
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-lg);
      margin: 16px 0px;
    }
    .brand-title{ font-weight:800; letter-spacing:.3px; margin:0; font-size:clamp(18px,4.8vw,22px); }
    .brand-sub{ opacity:.92; margin-top:6px; }
    .container-narrow{width:100%; margin: 0 auto; padding: 12px 16px 28px; }
    .card-elevated{ border:0; border-radius: var(--radius-lg); background:var(--surface); box-shadow: var(--shadow-md); }
    .form-label{ font-weight:600; font-size:14px; margin-bottom:6px; }
    .input-group-text{ background:#f3f4f6; border:none; }
    .form-control{ border-radius: var(--radius-md); padding: 10px 12px; }
    .btn-primary{
      border:none; border-radius:14px; padding:12px 16px; font-weight:700; letter-spacing:.2px;
      background:linear-gradient(135deg,var(--brand-start),var(--brand-end));
      box-shadow:0 8px 24px rgba(241,7,163,.24);
    }
    .btn-primary:active{ transform: translateY(1px); }
    .help{ font-size:12px; color:var(--muted); }
  </style>
</head>
<body>
    
  <div class="main-container">
    <div class="hero">
      <h1 class="brand-title">Add Delivery Person</h1>
      <p class="brand-sub mb-0">Create a new delivery partner account</p>
    </div>

    <div class="card card-elevated p-4">
      <?php if ($successMessage): ?>
        <div class="alert alert-success py-2" role="alert"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>
      <?php if ($errorMessage): ?>
        <div class="alert alert-danger py-2" role="alert"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <form method="post" action="" novalidate>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person"></i></span>
              <input type="text" name="deliveryperson_name" class="form-control" placeholder="e.g., Rohan Kumar" value="<?php echo htmlspecialchars($deliveryperson_name ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Mobile Number</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-phone"></i></span>
              <input type="text" name="number" class="form-control" placeholder="10-digit number" inputmode="numeric" pattern="\d{10}" maxlength="10" value="<?php echo htmlspecialchars($number ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Email</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope"></i></span>
              <input type="email" name="email" class="form-control" placeholder="name@example.com" value="<?php echo htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
          </div>

         <div class="col-md-6">
  <label class="form-label">Username</label>
  <div class="input-group">
    <span class="input-group-text"><i class="bi bi-at"></i></span>
    <input type="text" name="username" class="form-control" placeholder="unique username" pattern="[A-Za-z0-9]{4,32}" value="<?php echo htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
  </div>
  <div class="help mt-1">4–32 chars; letters and numbers only</div>
</div>


          <div class="col-md-6">
            <label class="form-label">Password</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock"></i></span>
              <input type="password" name="password" id="password" class="form-control" placeholder="min 6 characters" minlength="6" required>
              <button class="btn btn-light border" type="button" id="togglePwd" aria-label="Show password"><i class="bi bi-eye"></i></button>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Pincode</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
              <input type="text" name="pincode" class="form-control" placeholder="6-digit pincode" inputmode="numeric" pattern="\d{7}" maxlength="7" value="<?php echo htmlspecialchars($pincode ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
          </div>
        </div>

        <div class="d-grid d-sm-flex justify-content-sm-end mt-4">
          <button type="submit" class="btn btn-primary px-4">Add Delivery Person</button>
        </div>
      </form>
    </div>
  </div>

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