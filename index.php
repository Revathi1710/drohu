<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Customer Login</title>
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
            --text-secondary:#6c757d;
            --surface-bg: #f8f9fa;
            --card-bg: #ffffff;
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
   

        * {
            box-sizing: border-box;
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


        .auth-container {
            width: 100%;
           
            position: relative;
        }
        
        .hero {
            background-color: var(--brand-dark);
            color: #fff;
            padding: 2.5rem 1.5rem 3rem;
            border-bottom-left-radius: var(--radius-lg);
            border-bottom-right-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 10;
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
        .text-header {
    font-weight: 700;
    color: var(--text-primary);
}
        .hero-sub {
            opacity: 0.9;
            font-size: clamp(0.9rem, 2.5vw, 1.1rem);
            margin: 0;
        }
        
        .auth-card {
           
            padding:0.5 1rem;
          
            border: none;
            transition: all 0.3s ease;
            position: relative;
            z-index: 20;
           
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

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .mobile-input-group .input-group-text,
        .mobile-input-group .form-control {
            height: 56px;
            border-radius: var(--radius-md) !important;
        }
        
        .input-group-text {
            background: var(--surface-bg);
            border: 1px solid #dee2e6;
        }
        
        .form-control {
            border: 1px solid #dee2e6;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--brand-main);
            box-shadow: 0 0 0 0.25rem rgba(30, 144, 255, 0.25);
            outline: none;
        }
        
        .country-select {
            flex: 0 0 80px !important;
        }

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
            border: 2px solid var(--surface-bg);
            border-radius: var(--radius-md);
            background: var(--surface-bg);
            transition: all 0.2s ease;
            outline: none;
        }

        .otp-input:focus {
            border-color: var(--brand-main);
            box-shadow: 0 0 0 3px rgba(30, 144, 255, 0.1);
        }
        
        .otp-input.filled {
            background: rgba(30, 144, 255, 0.05);
            border-color: var(--brand-main);
        }

        /* Button Styles */
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

        .auth-btn:active {
            transform: translateY(0);
        }
        
        .btn-icon {
            width: 48px;
            height: 48px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            color: var(--text-secondary);
        }

        /* Alert Messages */
        .alert-message {
            padding: 0.75rem 1rem;
            border-radius: var(--radius-md);
            font-size: 0.9rem;
            margin-top: 1rem;
            text-align: center;
            border: 1px solid transparent;
        }
        
        /* Footer */
        .auth-footer {
            text-align: center;
            font-size: 0.8rem;
            color: var(--text-secondary);
            line-height: 1.4;
            margin-top: 1rem;
        }

        /* Resend Button */
        .btn-link {
            color: var(--brand-main);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            border: none;
            background: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-link:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Loading States */
        .loading .btn-text, .loading .btn-arrow { opacity: 0; }
        .loading .btn-spinner { display: flex !important; }

        .resending .resend-text { opacity: 0; }
        .resending .resend-spinner { display: flex !important; }
    .country-select {
    width: 30px;
    flex: 0.2 !important;
}
    </style>
</head>
<body> <h2 class="mb-4">Choose Account type</h2>
  <div class="role-selection">
     
    <div class="role-card selected" onclick="window.location.href='index.php'" id="customer-card">
        <img src="images/customer.png" alt="Customer">
        <p>Customer</p>
        <span class="selected-icon bi bi-check-circle-fill"></span>
    </div>
    <div class="role-card" id="delivery-person-card" onclick="window.location.href='deliveryLogin.php'">
        <img src="images/deliveryPerson.png" alt="Delivery Person">
        <p>Delivery Person</p>
        <span class="selected-icon bi bi-check-circle-fill"></span>
    </div>
</div>

   <!-- <header class="hero">
        <div class="container-narrow" style="max-width:420px;margin:0 auto;">
            <div class="hero-header">
                <a href="index.php" class="text-white text-decoration-none"><i class="bi bi-chevron-left" style="font-size:24px;"></i></a>
                <h1 class="hero-title">Customer Login</h1>
            </div>
            <p class="hero-sub">Sign in to start your order.</p>
        </div>
    </header>-->

    <div class="auth-container">
        <div id="mobile-form" class="auth-card">
           <!-- <div class="text-center mb-4">
                <div class="auth-icon"><i class="bi bi-person-fill-lock"></i></div>
            </div>-->
<div class="login-card p-4">
            <div class="text-center mb-4">
              <!--  <div class="icon-badge"><i class="bi bi-person"></i></div>
                <div>-->
                    <div class="text-header fs-5">Welcome back</div>
                    <div class="text-secondary small">Enter your mobile number to get started.</div>
                </div>
            </div>
           

            <form id="phoneForm" class="auth-form">
                <div class="form-group">
                    <label for="mobileNumber" class="form-label">Mobile Number</label>
                    <div class="input-group mobile-input-group">
                       <select class="form-select country-select" id="countryCode" data-testid="select-country-code">
                            <option value="+91"> +91</option>
                        </select>
                        <input
                            type="tel"
                            class="form-control"
                            id="mobileNumber"
                            placeholder="Mobile number"
                            maxlength="10"
                            data-testid="input-phone"
                            inputmode="numeric"
                            autocomplete="tel"
                            required
                        >
                    </div>
                </div>

                <button
                    type="submit"
                    id="loginButton"
                    class="btn btn-primary auth-btn w-100"
                    data-testid="button-continue"
                >
                    <span class="btn-text">Continue</span>
                    <div class="btn-spinner d-none">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        Sending...
                    </div>
                </button>
            </form>

            <div id="statusMessage" class="alert-message d-none"></div>

            <div class="auth-footer">
                By continuing, you agree to our Terms of Service and Privacy Policy.
            </div>
        </div>

        <div id="otp-form" class="auth-card d-none">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <button
                    type="button"
                    class="btn btn-icon back-btn"
                    id="backButton"
                    data-testid="button-back"
                >
                    <i class="bi bi-chevron-left"></i>
                </button>
                <div class="auth-icon"><i class="bi bi-shield-lock-fill"></i></div>
                <div style="width: 48px;"></div>
            </div>

            <h1 class="auth-title">Enter verification code</h1>
            <p class="auth-subtitle" id="otpSubtitle">We sent a code to your number.</p>

            <form id="otpVerifyForm" class="auth-form">
                <div class="form-group">
                    <label class="form-label">Verification Code</label>
                    <div class="otp-input-container">
                        <input type="text" class="otp-input" maxlength="1" data-index="0" inputmode="numeric" autocomplete="one-time-code">
                        <input type="text" class="otp-input" maxlength="1" data-index="1" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" data-index="2" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" data-index="3" inputmode="numeric">
                    </div>
                </div>

                <button
                    type="submit"
                    id="verifyOtpButton"
                    class="btn btn-primary auth-btn w-100"
                    disabled
                    data-testid="button-verify"
                >
                    <span class="btn-text">Verify & Continue</span>
                    <div class="btn-spinner d-none">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        Verifying...
                    </div>
                </button>
            </form>

            <div id="otpStatusMessage" class="alert-message d-none"></div>

            <div class="text-center mt-4">
                <p class="mb-2 text-muted small">Didn't receive the code?</p>
                <div id="resendSection">
                    <span id="resendCountdown" class="text-muted small">Resend code in <span id="countdown">30</span>s</span>
                    <button
                        type="button"
                        id="resendButton"
                        class="btn btn-link p-0 d-none"
                        data-testid="button-resend"
                    >
                        <span class="resend-text">Resend code</span>
                        <div class="resend-spinner d-none">
                            <div class="spinner-border spinner-border-sm me-1" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            Sending...
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script>
        class MobileAuth {
            constructor() {
                this.currentStep = 'phone';
                this.phoneNumber = '';
                this.countryCode = '+91';
                this.otpCode = '';
                this.resendCountdown = 30;
                this.resendTimer = null;
                
                this.init();
            }

            init() {
                this.bindEvents();
                this.initOTPInputs();
                this.focusFirstInput();
            }

            bindEvents() {
                // Phone form submission
                document.getElementById('phoneForm').addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.handlePhoneSubmit();
                });

                // OTP form submission  
                document.getElementById('otpVerifyForm').addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.handleOTPVerify();
                });

                // Back button
                document.getElementById('backButton').addEventListener('click', () => {
                    this.showPhoneForm();
                });

                // Resend button
                document.getElementById('resendButton').addEventListener('click', () => {
                    this.handleResendOTP();
                });

                // Phone number input formatting
                document.getElementById('mobileNumber').addEventListener('input', (e) => {
                    e.target.value = e.target.value.replace(/\D/g, '').slice(0, 10);
                    this.validatePhoneForm();
                });

                // Country code change
                document.getElementById('countryCode').addEventListener('change', (e) => {
                    this.countryCode = e.target.value;
                });
            }

            initOTPInputs() {
                const inputs = document.querySelectorAll('.otp-input');
                
                inputs.forEach((input, index) => {
                    // Input event - handle typing
                    input.addEventListener('input', (e) => {
                        const value = e.target.value.replace(/\D/g, '');
                        e.target.value = value;
                        
                        if (value) {
                            e.target.classList.add('filled');
                            // Auto-focus next input
                            if (index < inputs.length - 1) {
                                inputs[index + 1].focus();
                            }
                        } else {
                            e.target.classList.remove('filled');
                        }
                        
                        this.updateOTPCode();
                        this.validateOTPForm();
                    });

                    // Keydown event - handle backspace
                    input.addEventListener('keydown', (e) => {
                        if (e.key === 'Backspace' && !e.target.value && index > 0) {
                            inputs[index - 1].focus();
                            inputs[index - 1].classList.remove('filled');
                        }
                        
                        // Handle arrow keys
                        if (e.key === 'ArrowLeft' && index > 0) {
                            inputs[index - 1].focus();
                        }
                        if (e.key === 'ArrowRight' && index < inputs.length - 1) {
                            inputs[index + 1].focus();
                        }
                    });

                    // Paste event - handle pasting full OTP
                    input.addEventListener('paste', (e) => {
                        e.preventDefault();
                        const pastedData = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 4);
                        
                        if (pastedData.length === 4) {
                            inputs.forEach((inp, idx) => {
                                inp.value = pastedData[idx] || '';
                                if (pastedData[idx]) {
                                    inp.classList.add('filled');
                                }
                            });
                            this.updateOTPCode();
                            this.validateOTPForm();
                            
                            // Auto-submit if all filled
                            if (pastedData.length === 4) {
                                setTimeout(() => this.handleOTPVerify(), 300);
                            }
                        }
                    });

                    // Focus/blur events for better UX
                    input.addEventListener('focus', () => {
                        input.select();
                    });
                });
            }

            updateOTPCode() {
                const inputs = document.querySelectorAll('.otp-input');
                this.otpCode = Array.from(inputs).map(input => input.value).join('');
            }

            validatePhoneForm() {
                const phoneNumber = document.getElementById('mobileNumber').value;
                const submitBtn = document.getElementById('loginButton');
                
                if (phoneNumber.length >= 10) {
                    submitBtn.disabled = false;
                } else {
                    submitBtn.disabled = true;
                }
            }

            validateOTPForm() {
                const submitBtn = document.getElementById('verifyOtpButton');
                
                if (this.otpCode.length === 4) {
                    submitBtn.disabled = false;
                    // Auto-submit after short delay
                    setTimeout(() => {
                        if (this.otpCode.length === 4 && !submitBtn.disabled) {
                            this.handleOTPVerify();
                        }
                    }, 500);
                } else {
                    submitBtn.disabled = true;
                }
            }

            async handlePhoneSubmit() {
                const phoneNumber = document.getElementById('mobileNumber').value;
                const countryCode = document.getElementById('countryCode').value;
                
                if (phoneNumber.length < 10) {
                    this.showMessage('statusMessage', 'Please enter a valid mobile number', 'error');
                    return;
                }

                this.setButtonLoading('loginButton', true);
                this.hideMessage('statusMessage');

                try {
                    const response = await fetch('loginbackend.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            mobile_number: phoneNumber,
                            country_code: countryCode
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        if (result.otp_required) {
                            this.phoneNumber = phoneNumber;
                            this.countryCode = countryCode;
                            this.showOTPForm();
                            this.startResendCountdown();
                        } else {
                            // Direct login for pre-existing users
                            window.location.href = 'products.php'; // Redirect to dashboard
                        }
                    } else {
                        this.showMessage('statusMessage', result.message || 'Failed to send OTP', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.showMessage('statusMessage', 'Network error. Please try again.', 'error');
                }

                this.setButtonLoading('loginButton', false);
            }

            async handleOTPVerify() {
                if (this.otpCode.length !== 4) {
                    this.showMessage('otpStatusMessage', 'Please enter the complete OTP', 'error');
                    this.shakeOTPInputs();
                    return;
                }

                this.setButtonLoading('verifyOtpButton', true);
                this.hideMessage('otpStatusMessage');

                try {
                    const response = await fetch('loginbackend.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            mobile_number: this.phoneNumber,
                            country_code: this.countryCode,
                            otp: this.otpCode
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showMessage('otpStatusMessage', 'Verification successful!', 'success');
                        setTimeout(() => {
                            window.location.href = 'enter_name.php';
                        }, 1500);
                    } else {
                        this.showMessage('otpStatusMessage', result.message || 'Invalid OTP', 'error');
                        this.shakeOTPInputs();
                        this.clearOTPInputs();
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.showMessage('otpStatusMessage', 'Network error. Please try again.', 'error');
                    this.shakeOTPInputs();
                }

                this.setButtonLoading('verifyOtpButton', false);
            }

            async handleResendOTP() {
                const resendBtn = document.getElementById('resendButton');
                resendBtn.classList.add('resending');

                try {
                    const response = await fetch('loginbackend.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            mobile_number: this.phoneNumber,
                            country_code: this.countryCode,
                            action: 'resend'
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showMessage('otpStatusMessage', 'New OTP sent successfully', 'success');
                        this.clearOTPInputs();
                        this.startResendCountdown();
                        setTimeout(() => this.hideMessage('otpStatusMessage'), 3000);
                    } else {
                        this.showMessage('otpStatusMessage', result.message || 'Failed to resend OTP', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.showMessage('otpStatusMessage', 'Network error. Please try again.', 'error');
                }

                resendBtn.classList.remove('resending');
            }

            showPhoneForm() {
                document.getElementById('mobile-form').classList.remove('d-none');
                document.getElementById('otp-form').classList.add('d-none');
                this.currentStep = 'phone';
                this.focusFirstInput();
                this.hideMessage('statusMessage');
            }

            showOTPForm() {
                document.getElementById('mobile-form').classList.add('d-none');
                document.getElementById('otp-form').classList.remove('d-none');
                this.currentStep = 'otp';
                
                // Update subtitle with masked phone
                const maskedPhone = `${this.countryCode} ${this.phoneNumber.slice(0, 2)}****${this.phoneNumber.slice(-2)}`;
                document.getElementById('otpSubtitle').textContent = `We sent a code to ${maskedPhone}`;
                
                // Focus first OTP input
                setTimeout(() => {
                    document.querySelector('.otp-input').focus();
                }, 100);
                
                this.hideMessage('otpStatusMessage');
            }

            startResendCountdown() {
                this.resendCountdown = 30;
                const countdownEl = document.getElementById('countdown');
                const resendCountdownEl = document.getElementById('resendCountdown');
                const resendButtonEl = document.getElementById('resendButton');
                
                resendCountdownEl.classList.remove('d-none');
                resendButtonEl.classList.add('d-none');
                
                this.resendTimer = setInterval(() => {
                    this.resendCountdown--;
                    countdownEl.textContent = this.resendCountdown;
                    
                    if (this.resendCountdown <= 0) {
                        clearInterval(this.resendTimer);
                        resendCountdownEl.classList.add('d-none');
                        resendButtonEl.classList.remove('d-none');
                    }
                }, 1000);
            }

            clearOTPInputs() {
                document.querySelectorAll('.otp-input').forEach(input => {
                    input.value = '';
                    input.classList.remove('filled', 'error');
                });
                this.otpCode = '';
                this.validateOTPForm();
            }

            shakeOTPInputs() {
                document.querySelectorAll('.otp-input').forEach(input => {
                    input.classList.add('error');
                    setTimeout(() => input.classList.remove('error'), 300);
                });
            }

            focusFirstInput() {
                if (this.currentStep === 'phone') {
                    setTimeout(() => document.getElementById('mobileNumber').focus(), 100);
                } else if (this.currentStep === 'otp') {
                    setTimeout(() => document.querySelector('.otp-input').focus(), 100);
                }
            }

            setButtonLoading(buttonId, loading) {
                const button = document.getElementById(buttonId);
                const btnText = button.querySelector('.btn-text');
                const btnSpinner = button.querySelector('.btn-spinner');
                
                if (loading) {
                    button.disabled = true;
                    button.classList.add('loading');
                    btnText.style.opacity = '0';
                    btnSpinner.classList.remove('d-none');
                } else {
                    button.disabled = false;
                    button.classList.remove('loading');
                    btnText.style.opacity = '1';
                    btnSpinner.classList.add('d-none');
                    
                    // Re-validate form
                    if (buttonId === 'loginButton') {
                        this.validatePhoneForm();
                    } else if (buttonId === 'verifyOtpButton') {
                        this.validateOTPForm();
                    }
                }
            }

            showMessage(messageId, text, type) {
                const messageEl = document.getElementById(messageId);
                messageEl.textContent = text;
                messageEl.className = `alert-message ${type}`;
                messageEl.classList.remove('d-none');
            }

            hideMessage(messageId) {
                document.getElementById(messageId).classList.add('d-none');
            }
        }

        // Initialize the application when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            new MobileAuth();
            
            // Add some visual polish
            document.body.style.opacity = '0';
            setTimeout(() => {
                document.body.style.transition = 'opacity 0.3s ease';
                document.body.style.opacity = '1';
            }, 50);
        });
    </script>
</body>
</html>