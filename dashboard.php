<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <meta name="description" content="Welcome to your professional mobile app dashboard">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <div class="text-center mb-4">
            <div class="auth-icon" style="background: linear-gradient(135deg, #198754, #20c997);">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>
        
        <h1 class="auth-title" style="color: #198754;">Welcome!</h1>
        <p class="auth-subtitle">Your mobile number has been verified successfully</p>

        <div class="card mb-4" style="background: rgba(25, 135, 84, 0.05); border: 1px solid rgba(25, 135, 84, 0.2);">
            <div class="card-body text-center">
                <h5 class="card-title" style="color: #198754; margin-bottom: 0.5rem;">Verification Complete</h5>
                <p class="card-text text-muted small" id="userInfo">
                    Loading user information...
                </p>
            </div>
        </div>

        <div class="d-grid gap-2">
            <button class="btn btn-primary auth-btn" onclick="continueToApp()">
                <span class="btn-text">Continue to App</span>
                <svg class="btn-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5 12h14m-7-7l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            
            <button class="btn btn-outline-secondary auth-btn" onclick="logout()">
                <span class="btn-text">Logout</span>
            </button>
        </div>

        <div class="auth-footer mt-4">
            <strong>🎉 Success!</strong><br>
            You have successfully completed the mobile authentication process.
        </div>
    </div>
</div>

<script>
// Check authentication status and display user info
async function checkAuthStatus() {
    try {
        const response = await fetch('user_info.php');
        const result = await response.json();
        
        if (result.success && result.user) {
            document.getElementById('userInfo').innerHTML = `
                <strong>Mobile:</strong> ${result.user.mobile}<br>
                <small>Verified at: ${new Date(result.user.login_time * 1000).toLocaleString()}</small>
            `;
        } else {
            // Not authenticated, redirect to login
            window.location.href = 'index.html';
        }
    } catch (error) {
        console.error('Error checking auth status:', error);
        document.getElementById('userInfo').textContent = 'Error loading user information';
    }
}

function continueToApp() {
    // In a real app, this would navigate to the main application
    alert('🎉 This is where your main application would start!\n\nIn production, you would:\n• Navigate to your app\'s main interface\n• Load user dashboard\n• Initialize app features');
}

async function logout() {
    try {
        const response = await fetch('../api/logout.php', { method: 'POST' });
        const result = await response.json();
        
        if (result.success) {
            window.location.href = 'index.html';
        }
    } catch (error) {
        console.error('Logout error:', error);
        // Force redirect even if logout fails
        window.location.href = 'index.html';
    }
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', () => {
    checkAuthStatus();
    
    // Add fade-in effect
    document.body.style.opacity = '0';
    setTimeout(() => {
        document.body.style.transition = 'opacity 0.3s ease';
        document.body.style.opacity = '1';
    }, 50);
});
</script>

</body>
</html>