<?php
session_start();
require_once __DIR__ . '/connection.php';
include __DIR__ . '/sidebar.php';

$successMessage = '';
$errorMessage = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  $errorMessage = 'Invalid delivery person reference.';
}

function getDeliveryPerson(mysqli $con, int $id): ?array {
  $sql = 'SELECT id, deliveryperson_name, number, email, username, password, pincode FROM deliveryPerson WHERE id = ? LIMIT 1';
  if (!$stmt = $con->prepare($sql)) return null;
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res ? $res->fetch_assoc() : null;
  $stmt->close();
  return $row ?: null;
}

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

$person = $id > 0 ? getDeliveryPerson($con, $id) : null;
if ($id > 0 && !$person) {
  $errorMessage = 'Delivery person not found.';
}

$deliveryperson_name = $person['deliveryperson_name'] ?? '';
$number              = $person['number'] ?? '';
$email               = $person['email'] ?? '';
$username            = $person['username'] ?? '';
$pincode             = $person['pincode'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id > 0 && $person) {
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
  } elseif ($password_plain !== '' && mb_strlen($password_plain) < 6) {
    $errorMessage = 'Password must be at least 6 characters.';
  } elseif (!is_valid_pincode($pincode)) {
    $errorMessage = 'Please enter a valid 6-digit pincode.';
  } else {
    // Duplicate checks excluding current ID
    $dupStmt = $con->prepare('SELECT id, username, email, number FROM deliveryPerson WHERE (username = ? OR email = ? OR number = ?) AND id <> ? LIMIT 1');
    if ($dupStmt) {
      $dupStmt->bind_param('sssi', $username, $email, $number, $id);
      $dupStmt->execute();
      $dup = $dupStmt->get_result()->fetch_assoc();
      $dupStmt->close();

      if ($dup) {
        if (!empty($dup['username']) && $dup['username'] === $username) {
          $errorMessage = 'Username already taken.';
        } elseif (!empty($dup['email']) && $dup['email'] === $email) {
          $errorMessage = 'Email already in use.';
        } elseif (!empty($dup['number']) && $dup['number'] === $number) {
          $errorMessage = 'Mobile number already in use.';
        }
      } else {
        // Build dynamic UPDATE
        $setParts = ['deliveryperson_name = ?', 'number = ?', 'email = ?', 'username = ?', 'pincode = ?','password=?'];
        $types = 'sssss';
        $values = [$deliveryperson_name, $number, $email, $username, $pincode,$password_plain];

 
        $types .= 'i';
        $values[] = $id;

        $sql = 'UPDATE deliveryPerson SET ' . implode(', ', $setParts) . ' WHERE id = ? LIMIT 1';
        $stmt = $con->prepare($sql);
        if ($stmt) {
          $stmt->bind_param($types, ...$values);
          if ($stmt->execute()) {
            $successMessage = 'Delivery person updated successfully.';
            $person = getDeliveryPerson($con, $id);
            $deliveryperson_name = $person['deliveryperson_name'] ?? $deliveryperson_name;
            $number              = $person['number'] ?? $number;
            $email               = $person['email'] ?? $email;
            $username            = $person['username'] ?? $username;
            $pincode             = $person['pincode'] ?? $pincode;
          } else {
            $errorMessage = 'Update failed. Please try again.';
          }
          $stmt->close();
        } else {
          $errorMessage = 'Failed to prepare update statement.';
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
  <title>Edit Delivery Person</title>
  <meta name="theme-color" content="#7b2ff7">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet"><style>
      :root {
            --primary-color: #4A90E2;
            --primary-hover: #3A7BC8;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --light-bg: #F0F4F8;
            --white: #ffffff;
            --border-color: #E0E6ED;
            --text-primary: #212529;
            --text-secondary: #6C757D;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 2px 4px 0 rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 4px 8px 0 rgba(0, 0, 0, 0.12);
            --radius-sm: 0.25rem;
            --radius-md: 0.35rem;
            --radius-lg: 0.5rem;
        }

        * {
            box-sizing: border-box;
        }

        body {
             background: var(--light-bg);
             font-family: 'Inter', sans-serif;
             font-size: 0.9375rem;
        }

        

        .page-header {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
        }
        .page-title {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1.5rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-title i {
            color: var(--primary-color);
            font-size: 1.75rem;
        }

        .page-subtitle {
            color: var(--text-secondary);
            margin: 0.5rem 0 0 0;
            font-size: 0.9rem;
            font-weight: 400;
        }

        .form-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .form-card-header {
            background: var(--white);
            color: var(--text-primary);
            padding: 1.25rem 2rem;
            border-bottom: 1px solid var(--border-color);
        }

        .form-card-header h5 {
            margin: 0;
            font-weight: 600;
            font-size: 1.125rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-card-body {
            padding: 2rem;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1.125rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: var(--primary-color);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            font-size: 0.825rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .form-label i {
            color: var(--text-secondary);
            font-size: 0.8rem;
        }

        .form-control {
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            padding: 0.65rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background: var(--white);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.15);
            outline: none;
        }

        .form-control::placeholder {
            color: var(--text-secondary);
            opacity: 0.7;
        }

        .submit-section {
            background: #f8fafc;
            margin: 2rem -2rem -2rem;
            padding: 2rem;
            border-top: 1px solid var(--border-color);
        }

        .submit-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            padding: 0.75rem 2rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-width: 180px;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .submit-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }
        
        .required-indicator {
            color: var(--danger-color);
            font-weight: 700;
        }
        
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }
            .page-header {
                padding: 1.5rem;
            }
            .form-card-body {
                padding: 1.5rem;
            }
            .page-title {
                font-size: 1.5rem;
            }
            .submit-section {
                margin: 1.5rem -1.5rem -1.5rem;
                padding: 1.5rem;
            }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
 <div class="main-container">
        <div class="form-container fade-in">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-truck-fast"></i>
                   Edit Delivery Person
                </h1>
                <p class="page-subtitle">Update delivery partner details</p>
            </div>
            
 

    <div class="card card-elevated p-4">
      <?php if ($successMessage): ?>
        <div class="alert alert-success py-2" role="alert"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>
      <?php if ($errorMessage): ?>
        <div class="alert alert-danger py-2" role="alert"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <?php if ($person): ?>
      <form method="post" action="" novalidate>
        <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person"></i></span>
              <input type="text" name="deliveryperson_name" class="form-control" placeholder="e.g., Rohan Kumar" value="<?php echo htmlspecialchars($deliveryperson_name, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Mobile Number</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-phone"></i></span>
              <input type="text" name="number" class="form-control" placeholder="10-digit number" inputmode="numeric" pattern="\d{10}" maxlength="10" value="<?php echo htmlspecialchars($number, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Email</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope"></i></span>
              <input type="email" name="email" class="form-control" placeholder="name@example.com" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Username</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-at"></i></span>
              <input type="text" name="username" class="form-control" placeholder="unique username" pattern="[A-Za-z0-9._-]{4,32}" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="help mt-1">4–32 chars; letters, numbers, dot, dash, underscore</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Password (leave blank to keep unchanged)</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock"></i></span>
              <input type="password" name="password" id="password" class="form-control" placeholder="min 6 characters" value="<?php echo htmlspecialchars($password_plain, ENT_QUOTES, 'UTF-8'); ?>">
              <button class="btn btn-light border" type="button" id="togglePwd" aria-label="Show password"><i class="bi bi-eye"></i></button>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Pincode</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
              <input type="text" name="pincode" class="form-control" placeholder="6-digit pincode" inputmode="numeric" pattern="\d{6}" maxlength="6" value="<?php echo htmlspecialchars($pincode, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
          </div>
        </div>

        <div class="d-grid d-sm-flex justify-content-sm-end mt-4">
          <button type="submit" class="btn btn-primary px-4">Save Changes</button>
        </div>
      </form>
      <?php endif; ?>
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