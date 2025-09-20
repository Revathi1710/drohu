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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');

    if ($firstName && $lastName) {
        $stmt = $con->prepare("UPDATE users SET first_name=?, last_name=? WHERE id=?");
        $stmt->bind_param("ssi", $firstName, $lastName, $userId);
        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        } else {
            $error = "Failed to update. Please try again.";
        }
    } else {
        $error = "Both fields are required.";
    }
}

// Fetch current user data
$stmt = $con->prepare("SELECT first_name, last_name FROM users WHERE id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Complete Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root {
  --primary-color: #0d6efd;
  --primary-hover: #0b5ed7;
  --success-color: #198754;
  --danger-color: #dc3545;
  --warning-color: #fd7e14;
  --dark-color: #212529;
  --light-color: #f8f9fa;
  --muted-color: #6c757d;
  --border-color: #dee2e6;
  --border-radius: 12px;
  --border-radius-sm: 8px;
  --shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  --shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  --shadow-lg: 0 1rem 3rem rgba(0, 0, 0, 0.175);
}

* {
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: linear-gradient(135deg, 
    rgba(13, 110, 253, 0.1) 0%, 
    rgba(248, 249, 250, 1) 35%, 
    rgba(13, 110, 253, 0.05) 100%
  );
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  margin: 0;
  line-height: 1.5;
}

.auth-container {
  width: 100%;
  max-width: 400px;
  position: relative;
}

.auth-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: var(--border-radius);
  padding: 2rem;
  box-shadow: var(--shadow-lg);
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
}

.auth-icon {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
  border-radius: var(--border-radius);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
  color: white;
}

.auth-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--dark-color);
  margin-bottom: 0.5rem;
  text-align: center;
}

.auth-subtitle {
  color: var(--muted-color);
  font-size: 0.95rem;
  margin-bottom: 2rem;
  text-align: center;
  line-height: 1.4;
}

.auth-form {
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  font-weight: 500;
  color: var(--dark-color);
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.mobile-input-group {
  display: flex;
  gap: 0.5rem;
}

.country-select {
  flex: 0 0 80px;
  height: 56px;
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius-sm);
  font-size: 0.9rem;
  transition: all 0.2s ease;
}

.mobile-input {
  flex: 1;
  height: 56px;
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius-sm);
  font-size: 1rem;
  padding: 0 1rem;
  transition: all 0.2s ease;
}

.mobile-input:focus,
.country-select:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
  outline: none;
}

/* OTP Input Styles */
.otp-input-container {
  display: flex;
  gap: 0.75rem;
  justify-content: center;
  margin: 1rem 0;
}

.otp-input {
  width: 56px;
  height: 56px;
  text-align: center;
  font-size: 1.25rem;
  font-weight: 600;
  border: 2px solid var(--border-color);
  border-radius: var(--border-radius-sm);
  background: white;
  transition: all 0.2s ease;
  outline: none;
}

.otp-input:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
  transform: scale(1.05);
}

.otp-input.filled {
  border-color: var(--primary-color);
  background: rgba(13, 110, 253, 0.05);
  color: var(--primary-color);
}

.otp-input.error {
  border-color: var(--danger-color);
  background: rgba(220, 53, 69, 0.05);
  animation: shake 0.3s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-2px); }
  75% { transform: translateX(2px); }
}

/* Button Styles */
.auth-btn {
  height: 48px;
  font-weight: 500;
  font-size: 1rem;
  border-radius: var(--border-radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
  position: relative;
  overflow: hidden;
}

.auth-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: var(--shadow);
}

.auth-btn:active:not(:disabled) {
  transform: translateY(0);
}

.auth-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-text,
.btn-arrow {
  transition: opacity 0.2s ease;
}

.btn-spinner {
  display: flex;
  align-items: center;
}

.btn-icon {
  width: 40px;
  height: 40px;
  border: none;
  background: rgba(108, 117, 125, 0.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  color: var(--muted-color);
}

.btn-icon:hover {
  background: rgba(108, 117, 125, 0.2);
  transform: scale(1.1);
}

.back-btn {
  color: var(--muted-color);
}

/* Alert Messages */
.alert-message {
  padding: 0.75rem 1rem;
  border-radius: var(--border-radius-sm);
  font-size: 0.9rem;
  margin-top: 1rem;
  text-align: center;
}

.alert-message.success {
  background: rgba(25, 135, 84, 0.1);
  color: var(--success-color);
  border: 1px solid rgba(25, 135, 84, 0.2);
}

.alert-message.error {
  background: rgba(220, 53, 69, 0.1);
  color: var(--danger-color);
  border: 1px solid rgba(220, 53, 69, 0.2);
}

.alert-message.warning {
  background: rgba(253, 126, 20, 0.1);
  color: var(--warning-color);
  border: 1px solid rgba(253, 126, 20, 0.2);
}

/* Footer */
.auth-footer {
  text-align: center;
  font-size: 0.8rem;
  color: var(--muted-color);
  line-height: 1.4;
  margin-top: 1rem;
}

/* Resend Button */
.btn-link {
  color: var(--primary-color);
  text-decoration: none;
  font-weight: 500;
  font-size: 0.9rem;
  border: none;
  background: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-link:hover:not(:disabled) {
  color: var(--primary-hover);
  text-decoration: underline;
}

.btn-link:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.resend-spinner {
  display: flex;
  align-items: center;
  font-size: 0.9rem;
}
.country-select{
    width:30px;
    flex:0.2 !important;
}

/* Responsive Design */
@media (max-width: 480px) {
  .auth-card {
    padding: 1.5rem;
    margin: 0.5rem;
  }
  
  .auth-title {
    font-size: 1.25rem;
  }
  
  .otp-input {
    width: 48px;
    height: 48px;
    font-size: 1.1rem;
  }
  
  .otp-input-container {
    gap: 0.5rem;
  }
  
  .mobile-input-group {
   
    gap: 0.75rem;
  }
  
  .country-select {
    flex: none;
  }
}

@media (max-width: 360px) {
  .otp-input {
    width: 44px;
    height: 44px;
  }
  
  .otp-input-container {
    gap: 0.4rem;
  }
}

/* Loading States */
.loading .btn-text,
.loading .btn-arrow {
  opacity: 0;
}

.loading .btn-spinner {
  opacity: 1;
}

.resending .resend-text {
  opacity: 0;
}

.resending .resend-spinner {
  opacity: 1;
}
</style>
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
       <div class="auth-icon">
    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
        <path fill-rule="evenodd" d="M8 9a5 5 0 0 0-5 5v1h10v-1a5 5 0 0 0-5-5z"/>
    </svg>
</div>

        <h1 class="auth-title">Complete Your Profile</h1>
        <p class="auth-subtitle">Please enter your name to continue</p>

        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
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

            <button type="submit" class="btn btn-primary w-100">Complete Profile</button>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
