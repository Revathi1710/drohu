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
    <meta name="theme-color" content="#1e90ff">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-main: #1e90ff;
            --brand-dark: #0056b3;
            --brand-light: #f0f8ff;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --surface-bg: #f8f9fa;
            --card-bg: #ffffff;
            --shadow-sm: 0 2px 6px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.1);
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.15);
            --radius-lg: 1.25rem;
            --radius-md: 0.75rem;
        }
.role-selection {
    width: 100%;
    max-width: 420px;
    display: flex
;
    justify-content: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
    position: relative;
    z-index: 10;
}
.role-card {
       background-color: var(--card-bg);
    border: 1px solid #5fe1ff;
    border-radius: var(--radius-md);
    /* box-shadow: var(--shadow-md); */
    cursor: pointer;
    padding: 1rem;
    text-align: center;
    transition: var(--transition-primary);
    flex: 1;
    display: flex
;
    flex-direction: column;
    align-items: center;
}
.role-card img {
    max-width:120px;
    margin-bottom: 0.5rem;
}
.role-card p {
    font-size: 0.9rem;
    font-weight: 600;
    margin: 0;
    color: var(--text-primary);
}
.role-card {
    /* Existing styles */
    position: relative; /* Added to position the checkmark icon */
}

.selected-icon {
    display: none; /* Hide by default */
    position: absolute;
    top: 5px; /* Adjust as needed */
    right: 5px; /* Adjust as needed */
    font-size: 1.25rem;
    color: var(--brand-main);
    background-color: white;
    border-radius: 50%;
}

.role-card.selected .selected-icon {
    display: block; /* Show icon when the card is selected */
}
        body {
           font-family: 'Plus Jakarta Sans', sans-serif;
   
    color: var(--text-primary);
    min-height: 100vh;
    display: flex
;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding: 1rem;
    padding-top: 3rem;
    margin: 0;
    line-height: 1.5;
    overflow-x: hidden;
        }

        .hero {
            background-color: var(--brand-dark);
            color: #fff;
            padding: 2.5rem 1.5rem 3rem;
            border-bottom-left-radius: var(--radius-lg);
            border-bottom-right-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
        }
        
        .hero-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .hero-title {
            font-weight: 800;
            font-size: clamp(1.5rem, 5vw, 2rem);
            letter-spacing: -0.5px;
            margin: 0;
        }

        .hero-sub {
            opacity: 0.9;
            font-size: clamp(0.9rem, 2.5vw, 1.1rem);
            margin: 0;
        }
        
        .container-narrow {
           
            width: 100%;
            margin: 0 auto;
           
        }

        .login-card {
            border: none;
            border-radius: var(--radius-lg);
           
        }

        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
        }
        
       .input-group-text {
    background-color: #ffffff;
    /* border: 1px solid #dee2e6; */
    border-right: none;
    /* border-radius: var(--radius-md) 0 0 var(--radius-md); */
    color: var(--text-secondary);
    padding: 0.75rem 1rem;
}
        
        .form-control {
           
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
           
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(30, 144, 255, 0.25);
            border-color: var(--brand-main);
        }
        
        .btn-primary {
            background-color: var(--brand-main);
            color: #fff;
            border: none;
            border-radius: var(--radius-md);
            padding: 0.75rem 1.5rem;
            font-weight: 700;
            letter-spacing: 0.2px;
            box-shadow: 0 4px 12px rgba(30, 144, 255, 0.3);
            transition: background-color 0.2s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--brand-dark);
        }
        
        .back-link {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }
        
        .back-link:hover {
            color: var(--brand-dark);
        }
        
        .icon-badge {
            width: 3.5rem;
            height: 3.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: var(--brand-light);
            color: var(--brand-main);
            font-size: 1.5rem;
        }

        .alert {
            font-size: 0.875rem;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-md);
        }
        
        .text-header {
            font-weight: 700;
            color: var(--text-primary);
        }
    </style>
</head>
<body><h2 class="mb-4">Choose Account type</h2>
    <div class="role-selection">
    <div class="role-card " id="customer-card"  onclick="window.location.href='index.php'">
        <img src="images/customer.png" alt="Customer">
        <p>Customer</p>
        <span class="selected-icon bi bi-check-circle-fill"></span>
    </div>
    <div class="role-card selected" id="delivery-person-card"  onclick="window.location.href='.php'">
        <img src="images/deliveryPerson.png" alt="Delivery Person">
        <p>Delivery Person</p>
        <span class="selected-icon bi bi-check-circle-fill"></span>
    </div>
</div>
  <!--  <header class="hero">
        <div class="container-narrow">
            <div class="hero-header">
                <a href="index.php" class="text-white text-decoration-none"><i class="bi bi-chevron-left" style="font-size:24px;"></i></a>
                <h1 class="hero-title">Delivery Partner Login</h1>
            </div>
            <p class="hero-sub">Sign in to manage your orders.</p>
        </div>
    </header>-->

    <main class="container-narrow">
        <div class="login-card p-4">
            <div class="text-center mb-4">
              <!--  <div class="icon-badge"><i class="bi bi-person"></i></div>
                <div>-->
                    <div class="text-header fs-5">Welcome back</div>
                    <div class="text-secondary small">Enter your credentials to continue</div>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
                        <button class="btn btn-light border" type="button" id="togglePwd" aria-label="Show password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>

                <!--<div class="text-center mt-3">
                    <a href="index.php" class="back-link">Back to home</a>
                </div>-->
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