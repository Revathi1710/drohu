<?php
session_start();
include('connection.php');

// Check if user is logged in
$userId = (int)($_SESSION['user_id'] ?? 0);
if ($userId === 0) {
    header("Location: login.php");
    exit();
}

// Handle form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');

    if ($firstName && $lastName) {
        // Use prepared statements to prevent SQL injection
        $stmt = $con->prepare("UPDATE users SET first_name=?, last_name=? WHERE id=?");
        $stmt->bind_param("ssi", $firstName, $lastName, $userId);
        if ($stmt->execute()) {
            header("Location: products.php"); // Redirect to the app's home page
            exit();
        } else {
            $error = "Failed to update. Please try again.";
        }
        $stmt->close();
    } else {
        $error = "Both fields are required.";
    }
}

// Fetch current user data
$user = [];
$stmt = $con->prepare("SELECT first_name, last_name FROM users WHERE id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Complete Profile</title>
    <meta name="description" content="Complete your user profile for AquaGo.">
    <meta name="theme-color" content="#1e90ff">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
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
            --shadow-md: 0 4px 12px rgba(0,0,0,0.1);
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.15);
            --radius-lg: 1.25rem;
            --radius-md: 0.75rem;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--surface-bg);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            margin: 0;
            line-height: 1.5;
        }
        
        .auth-container {
            width: 100%;
            max-width: 420px;
            position: relative;
        }
        
        .auth-card {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            box-shadow: var(--shadow-lg);
            border: none;
            transition: all 0.3s ease;
        }

        .auth-icon {
            width: 64px;
            height: 64px;
            background: var(--brand-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: var(--brand-main);
            font-size: 2rem;
        }

        .auth-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .auth-subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: 2rem;
            text-align: center;
            line-height: 1.4;
        }

        .form-control {
            height: 56px;
            border-radius: var(--radius-md);
            font-size: 1rem;
            border: 1px solid #dee2e6;
        }
        
        .form-control:focus {
            border-color: var(--brand-main);
            box-shadow: 0 0 0 0.25rem rgba(30, 144, 255, 0.25);
            outline: none;
        }
        
        .auth-btn {
            height: 56px;
            font-weight: 700;
            font-size: 1rem;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            position: relative;
            background: linear-gradient(135deg, var(--brand-main), #5b36f0);
            box-shadow: 0 8px 20px rgba(30, 144, 255, 0.2);
            border: none;
        }
        
        .auth-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(30, 144, 255, 0.25);
        }

        .alert-message {
            padding: 0.75rem 1rem;
            border-radius: var(--radius-md);
            font-size: 0.9rem;
            margin-top: 1rem;
            text-align: center;
            border: 1px solid transparent;
        }
        
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-icon">
            <i class="bi bi-person-fill"></i>
        </div>

        <h1 class="auth-title">Complete Your Profile</h1>
        <p class="auth-subtitle">Please enter your name to continue your Drohu journey!</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3 text-start">
                <label for="firstName" class="form-label">First Name</label>
                <input type="text" class="form-control" id="firstName" name="first_name"
                       value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
            </div>

            <div class="mb-3 text-start">
                <label for="lastName" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="lastName" name="last_name"
                       value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
            </div>

            <button type="submit" class="btn btn-primary auth-btn w-100">Complete Profile</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>