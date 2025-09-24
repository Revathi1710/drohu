<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Login</title>
    <meta name="description" content="Secure mobile authentication with OTP verification">
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
            --gradient-primary: linear-gradient(135deg, var(--brand-main), #5b36f0);
            --selected-border: 2px solid var(--brand-main);
            --transition-primary: all 0.2s ease-in-out;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--surface-bg);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 1rem;
            padding-top: 5rem;
            margin: 0;
            line-height: 1.5;
            overflow-x: hidden;
        }
        
        .role-selection {
            width: 100%;
            max-width: 420px;
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
            position: relative;
            z-index: 10;
        }

        .role-card {
            background-color: var(--card-bg);
            border: 2px solid transparent;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            cursor: pointer;
            padding: 1rem;
            text-align: center;
            transition: var(--transition-primary);
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .role-card.selected {
            border-color: var(--brand-main);
            box-shadow: 0 0 0 4px rgba(30, 144, 255, 0.25);
        }

        .role-card img {
            max-width: 80px;
            margin-bottom: 0.5rem;
        }
        
        .role-card p {
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0;
            color: var(--text-primary);
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
            position: relative;
            margin-top: 0;
        }
        
        .auth-card {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            border: none;
            transition: all 0.3s ease;
            position: relative;
            z-index: 20;
        }

        .auth-icon {
            display: none;
        }

        .auth-title {
            text-align: left;
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .auth-subtitle {
            text-align: left;
            margin-bottom: 1.5rem;
        }
        
        .auth-btn {
            height: 48px;
            font-size: 0.95rem;
            border-radius: var(--radius-md);
        }
        
        .btn-link {
            font-weight: 700;
        }
    </style>
</head>
<body>

    <div class="role-selection">
        <div class="role-card" id="delivery-person-card">
            <img src="images/deliveryPerson.png" alt="Delivery Person">
            <p>Delivery Person</p>
        </div>
        <div class="role-card selected" id="customer-card">
            <img src="images/customer.png" alt="Customer">
            <p>Customer</p>
        </div>
    </div>

    <div class="auth-container">
        <div id="customer-login-form" class="auth-card">
            <h1 class="auth-title">Hello Customer!</h1>
            <p class="auth-subtitle">Enter your mobile number to get started.</p>

            <form id="customer-form" class="auth-form">
                <div class="form-group">
                    <label for="mobileNumber" class="form-label">Mobile Number</label>
                    <div class="input-group mobile-input-group">
                        <select class="form-select country-select" id="countryCode">
                            <option value="+91">🇮🇳 +91</option>
                        </select>
                        <input
                            type="tel"
                            class="form-control"
                            id="mobileNumber"
                            placeholder="Mobile number"
                            maxlength="10"
                            inputmode="numeric"
                            autocomplete="tel"
                            required
                        >
                    </div>
                </div>

                <button
                    type="submit"
                    id="customer-login-button"
                    class="btn btn-primary auth-btn w-100"
                    disabled
                >
                    Continue
                </button>
            </form>
        </div>

        <div id="delivery-person-login-form" class="auth-card d-none">
            <h1 class="auth-title">Hello Delivery Person!</h1>
            <p class="auth-subtitle">Please fill out the form below to get started.</p>

            <form id="delivery-person-form">
                <div class="form-group">
                    <label for="deliveryUsername" class="form-label">Username</label>
                    <input type="text" class="form-control" id="deliveryUsername" placeholder="Enter your username" required>
                </div>
                <div class="form-group">
                    <label for="deliveryPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="deliveryPassword" placeholder="Enter your password" required>
                </div>
                <div class="d-flex justify-content-end mb-4">
                    <a href="#" class="btn-link">Forgot?</a>
                </div>
                <div class="d-flex gap-2">
                    <div class="flex-grow-1">
                        <p class="mb-0 text-secondary small">No account? <a href="#" class="btn-link">Signup</a></p>
                    </div>
                    <button type="submit" class="btn btn-primary auth-btn px-4">Login</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const deliveryCard = document.getElementById('delivery-person-card');
            const customerCard = document.getElementById('customer-card');
            const deliveryForm = document.getElementById('delivery-person-login-form');
            const customerForm = document.getElementById('customer-login-form');
            const mobileNumberInput = document.getElementById('mobileNumber');
            const loginButton = document.getElementById('customer-login-button');

            // Set Customer as default selected
            customerCard.classList.add('selected');
            deliveryCard.classList.remove('selected');
            customerForm.classList.remove('d-none');
            deliveryForm.classList.add('d-none');

            // Event listener for Customer card
            customerCard.addEventListener('click', () => {
                customerCard.classList.add('selected');
                deliveryCard.classList.remove('selected');
                customerForm.classList.remove('d-none');
                deliveryForm.classList.add('d-none');
            });

            // Event listener for Delivery Person card
            deliveryCard.addEventListener('click', () => {
                deliveryCard.classList.add('selected');
                customerCard.classList.remove('selected');
                deliveryForm.classList.remove('d-none');
                customerForm.classList.add('d-none');
            });

            // Add input validation for customer mobile number
            mobileNumberInput.addEventListener('input', (e) => {
                const phoneNumber = e.target.value.replace(/\D/g, '').slice(0, 10);
                e.target.value = phoneNumber;
                loginButton.disabled = phoneNumber.length < 10;
            });
        });
    </script>
</body>
</html>