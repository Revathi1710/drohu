<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Login</title>
    <meta name="description" content="Secure mobile authentication with OTP verification">
    <meta name="theme-color" content="#0d6efd">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
    <!-- Mobile Number Input Form -->
    <div id="mobile-form" class="auth-card">
        <div class="text-center mb-4">
            <div class="auth-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>
        
        <h1 class="auth-title">Welcome back</h1>
        <p class="auth-subtitle">Enter your mobile number to continue</p>

        <form id="phoneForm" class="auth-form">
            <div class="form-group">
                <label for="mobileNumber" class="form-label">Mobile Number</label>
                <div class="input-group mobile-input-group">
                    <select class="form-select country-select" id="countryCode" data-testid="select-country-code">
                        <option value="+91">🇮🇳 +91</option>
                        <option value="+1">🇺🇸 +1</option>
                        <option value="+44">🇬🇧 +44</option>
                        <option value="+86">🇨🇳 +86</option>
                    </select>
                    <input 
                        type="tel" 
                        class="form-control mobile-input" 
                        id="mobileNumber" 
                        placeholder="Mobile number"
                        maxlength="10"
                        data-testid="input-phone"
                        inputmode="numeric"
                        autocomplete="tel"
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
                <svg class="btn-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5 12h14m-7-7l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </form>

        <div id="statusMessage" class="alert-message d-none"></div>
        
        <div class="auth-footer">
            By continuing, you agree to our Terms of Service and Privacy Policy
        </div>
    </div>

    <!-- OTP Verification Form -->
    <div id="otp-form" class="auth-card d-none">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <button 
                type="button" 
                class="btn btn-icon back-btn" 
                id="backButton"
                data-testid="button-back"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 12H5m7-7l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            
            <div class="auth-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                    <circle cx="12" cy="16" r="1" fill="currentColor"/>
                    <path d="M7 11V7a5 5 0 0110 0v4" stroke="currentColor" stroke-width="2"/>
                </svg>
            </div>
            
            <div style="width: 40px;"></div>
        </div>

        <h1 class="auth-title">Enter verification code</h1>
        <p class="auth-subtitle" id="otpSubtitle">We sent a code to your number</p>

        <form id="otpVerifyForm" class="auth-form">
            <div class="form-group">
                <label class="form-label">Verification Code</label>
                <div class="otp-input-container">
                    <input 
                        type="text" 
                        class="otp-input" 
                        maxlength="1" 
                        data-index="0"
                        data-testid="input-otp-0"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                    >
                    <input 
                        type="text" 
                        class="otp-input" 
                        maxlength="1" 
                        data-index="1"
                        data-testid="input-otp-1"
                        inputmode="numeric"
                    >
                    <input 
                        type="text" 
                        class="otp-input" 
                        maxlength="1" 
                        data-index="2"
                        data-testid="input-otp-2"
                        inputmode="numeric"
                    >
                    <input 
                        type="text" 
                        class="otp-input" 
                        maxlength="1" 
                        data-index="3"
                        data-testid="input-otp-3"
                        inputmode="numeric"
                    >
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

<!-- Bootstrap JS and dependencies -->
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
                    window.location.href = 'index.php'; // Redirect to dashboard
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
        const btnArrow = button.querySelector('.btn-arrow');
        
        if (loading) {
            button.disabled = true;
            button.classList.add('loading');
            btnText.style.opacity = '0';
            if (btnArrow) btnArrow.style.opacity = '0';
            btnSpinner.classList.remove('d-none');
        } else {
            button.disabled = false;
            button.classList.remove('loading');
            btnText.style.opacity = '1';
            if (btnArrow) btnArrow.style.opacity = '1';
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