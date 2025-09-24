<?php
session_start();

// Initialize the error variable to an empty string.
$error = '';

// Check if the form has been submitted using the 'login' button.
if (isset($_POST['login'])) {
    // Trim whitespace from username and password inputs.
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // --- SECURITY WARNING: HARDCODED CREDENTIALS ---
    // This is for demonstration purposes ONLY.
    // In a real application, you should NEVER hardcode credentials.
    // Instead, you would validate against a database using hashed passwords.
    $valid_username = 'admin';
    $valid_password = 'admin';

    // Check if the username and password fields are not empty.
    if ($username === '' || $password === '') {
        $error = "Username and Password are required!";
    } else {
        // Perform a case-insensitive check for the hardcoded credentials.
        if (strtolower($username) === $valid_username && strtolower($password) === $valid_password) {
            // Regenerate the session ID to prevent session fixation attacks.
            session_regenerate_id(true);

            // Set session variables for authentication.
            $_SESSION['auth'] = true;
            $_SESSION['adminId'] = 1;
            $_SESSION['login_time'] = time();

            // Redirect to the admin dashboard and exit to prevent further code execution.
            header("Location: adminDashboard.php");
            exit();
        } else {
            // Set an error message for invalid credentials.
            $error = "Invalid Username or Password!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        'primary-blue': '#1A73E8',
                        'light-gray-bg': '#F4F6F9',
                        'dark-text': '#2D3748',
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F4F6F9;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 md:p-12 rounded-lg shadow-2xl w-full max-w-md border border-gray-200">
        
        <!-- Logo or Company Name -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-dark-text">Admin Panel</h1>
            <p class="text-gray-500 mt-2">Sign in to your account</p>
        </div>

        <!-- Display an error message if it exists -->
        <?php if ($error !== ''): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md relative mb-6" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form action="" method="POST" class="space-y-6">
            
            <!-- Username Field -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <div class="mt-1">
                    <!-- The value attribute retains the username on failed login -->
                    <input id="username" name="username" type="text" autocomplete="username" required
                        class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-blue focus:border-primary-blue sm:text-sm"
                        value="<?= htmlspecialchars($username) ?>">
                </div>
            </div>

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <div class="mt-1">
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                        class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-blue focus:border-primary-blue sm:text-sm">
                </div>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" name="login"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-blue hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-blue transition-colors duration-200">
                    Sign in
                </button>
            </div>
            
        </form>
        
        <!-- Legal/Copyright Information -->
        <div class="mt-8 text-center text-xs text-gray-500">
            &copy; 2025 Drohu. All rights reserved.
        </div>
        
    </div>

</body>
</html>