// SafeBrgy Reset Password Page - JavaScript Logic

document.addEventListener('DOMContentLoaded', function() {
  
  // Initialize
  const emailForm = document.getElementById('emailForm');
  const otpForm = document.getElementById('otpForm');
  const newPasswordForm = document.getElementById('newPasswordForm');
  const newPasswordInput = document.getElementById('newPassword');
  const confirmPasswordInput = document.getElementById('confirmPassword');
  const verificationCodeInput = document.getElementById('verificationCode');
  const togglePasswordBtn = document.getElementById('togglePassword');
  const backToEmailBtn = document.getElementById('backToEmailBtn');
  const resendLink = document.getElementById('resendLink');

  // ============ STEP 1: EMAIL VERIFICATION ============
  if (emailForm) {
    emailForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const emailOrContact = document.getElementById('emailOrContact').value.trim();

      if (!emailOrContact) {
        showError('Please enter an email or contact number.');
        return;
      }

      // Validate email or contact format
      if (!isValidEmailOrContact(emailOrContact)) {
        showError('Please enter a valid email address or contact number.');
        return;
      }

      // Simulate sending reset code
      console.log('Sending reset code to:', emailOrContact);
      
      // Store the email/contact for next step
      document.getElementById('emailOrContactHidden').value = emailOrContact;
      document.getElementById('emailOrContactHidden2').value = emailOrContact;

      // Move to step 2
      moveToStep(2);
      startResendTimer();
      
      showMessage('A verification code has been sent to ' + maskEmail(emailOrContact), 'success');
    });
  }

  // ============ STEP 2: OTP VERIFICATION ============
  // Auto-format OTP input to uppercase and numbers only
  if (verificationCodeInput) {
    verificationCodeInput.addEventListener('input', function(e) {
      this.value = this.value.toUpperCase().replace(/[^0-9]/g, '');
    });

    verificationCodeInput.addEventListener('keypress', function(e) {
      if (!/[0-9]/.test(e.key)) {
        e.preventDefault();
      }
    });
  }

  if (otpForm) {
    otpForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const verificationCode = verificationCodeInput.value.trim();

      if (!verificationCode || verificationCode.length !== 6) {
        showError('Please enter a valid 6-digit code.');
        return;
      }

      console.log('Verifying code:', verificationCode);
      
      // Store code for next step
      document.getElementById('verificationCodeHidden').value = verificationCode;

      // Move to step 3
      moveToStep(3);
      showMessage('Code verified successfully!', 'success');
    });
  }

  // Back to Email button
  if (backToEmailBtn) {
    backToEmailBtn.addEventListener('click', function(e) {
      e.preventDefault();
      moveToStep(1);
      clearMessages();
    });
  }

  // Resend Code
  if (resendLink) {
    resendLink.addEventListener('click', function(e) {
      e.preventDefault();
      const emailOrContact = document.getElementById('emailOrContactHidden').value;
      
      if (emailOrContact) {
        console.log('Resending code to:', emailOrContact);
        showMessage('Verification code resent to ' + maskEmail(emailOrContact), 'success');
        startResendTimer();
      }
    });
  }

  // ============ STEP 3: NEW PASSWORD ============
  if (newPasswordInput) {
    newPasswordInput.addEventListener('input', function() {
      validatePassword(this.value);
    });
  }

  if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener('input', function() {
      validatePasswordMatch();
    });
  }

  if (togglePasswordBtn) {
    togglePasswordBtn.addEventListener('click', function() {
      const type = newPasswordInput.type === 'password' ? 'text' : 'password';
      newPasswordInput.type = type;
      this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
    });
  }

  if (newPasswordForm) {
    newPasswordForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const newPassword = newPasswordInput.value;
      const confirmPassword = confirmPasswordInput.value;

      if (!newPassword || !confirmPassword) {
        showError('Please fill in all password fields.');
        return;
      }

      if (newPassword !== confirmPassword) {
        showError('Passwords do not match.');
        return;
      }

      if (!isPasswordValid(newPassword)) {
        showError('Password does not meet all requirements.');
        return;
      }

      // Simulate password reset
      console.log('Resetting password...');
      
      // Show success message
      showMessage('Password reset successfully! Redirecting to login...', 'success');
      
      // Redirect after a delay
      setTimeout(function() {
        window.location.href = 'login.php';
      }, 2000);
    });
  }

  // ============ HELPER FUNCTIONS ============

  function isValidEmailOrContact(value) {
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    // Contact validation (11 digits for PH numbers)
    const contactRegex = /^(\+63|0)?9\d{9}$/;
    
    return emailRegex.test(value) || contactRegex.test(value);
  }

  function maskEmail(email) {
    if (email.includes('@')) {
      const [name, domain] = email.split('@');
      const maskedName = name.substring(0, 2) + '*'.repeat(Math.max(0, name.length - 4)) + name.substring(name.length - 2);
      return maskedName + '@' + domain;
    } else {
      // It's a phone number
      return email.substring(0, 3) + '*'.repeat(5) + email.substring(email.length - 3);
    }
  }

  function validatePassword(password) {
    const checkBoxes = {
      length: document.getElementById('lengthCheck'),
      uppercase: document.getElementById('uppercaseCheck'),
      number: document.getElementById('numberCheck'),
      special: document.getElementById('specialCheck')
    };

    // Check length
    if (password.length >= 8) {
      checkBoxes.length.classList.add('valid');
      checkBoxes.length.querySelector('i').classList.remove('fa-times');
      checkBoxes.length.querySelector('i').classList.add('fa-check');
    } else {
      checkBoxes.length.classList.remove('valid');
      checkBoxes.length.querySelector('i').classList.remove('fa-check');
      checkBoxes.length.querySelector('i').classList.add('fa-times');
    }

    // Check uppercase
    if (/[A-Z]/.test(password)) {
      checkBoxes.uppercase.classList.add('valid');
      checkBoxes.uppercase.querySelector('i').classList.remove('fa-times');
      checkBoxes.uppercase.querySelector('i').classList.add('fa-check');
    } else {
      checkBoxes.uppercase.classList.remove('valid');
      checkBoxes.uppercase.querySelector('i').classList.remove('fa-check');
      checkBoxes.uppercase.querySelector('i').classList.add('fa-times');
    }

    // Check number
    if (/[0-9]/.test(password)) {
      checkBoxes.number.classList.add('valid');
      checkBoxes.number.querySelector('i').classList.remove('fa-times');
      checkBoxes.number.querySelector('i').classList.add('fa-check');
    } else {
      checkBoxes.number.classList.remove('valid');
      checkBoxes.number.querySelector('i').classList.remove('fa-check');
      checkBoxes.number.querySelector('i').classList.add('fa-times');
    }

    // Check special character
    if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
      checkBoxes.special.classList.add('valid');
      checkBoxes.special.querySelector('i').classList.remove('fa-times');
      checkBoxes.special.querySelector('i').classList.add('fa-check');
    } else {
      checkBoxes.special.classList.remove('valid');
      checkBoxes.special.querySelector('i').classList.remove('fa-check');
      checkBoxes.special.querySelector('i').classList.add('fa-times');
    }

    // Update submit button state
    updateSubmitButton();
    validatePasswordMatch();
  }

  function validatePasswordMatch() {
    const password = newPasswordInput.value;
    const confirmPassword = confirmPasswordInput.value;
    const matchMsg = document.getElementById('passwordMatchMsg');

    if (confirmPassword && password !== confirmPassword) {
      matchMsg.classList.remove('d-none');
      updateSubmitButton();
    } else {
      matchMsg.classList.add('d-none');
      updateSubmitButton();
    }
  }

  function isPasswordValid(password) {
    return password.length >= 8 &&
           /[A-Z]/.test(password) &&
           /[0-9]/.test(password) &&
           /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
  }

  function updateSubmitButton() {
    const submitBtn = document.getElementById('submitBtn');
    const password = newPasswordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    if (isPasswordValid(password) && password === confirmPassword) {
      submitBtn.disabled = false;
    } else {
      submitBtn.disabled = true;
    }
  }

  function moveToStep(stepNumber) {
    // Hide all steps
    document.querySelectorAll('.step-content').forEach(el => {
      el.classList.remove('active');
    });

    // Show current step
    document.getElementById('content-step' + stepNumber).classList.add('active');

    // Update step indicator
    document.querySelectorAll('.step').forEach((el, index) => {
      const currentStep = index + 1;
      el.classList.remove('active', 'completed');
      if (currentStep < stepNumber) {
        el.classList.add('completed');
      } else if (currentStep === stepNumber) {
        el.classList.add('active');
      }
    });

    // Update step connectors
    document.querySelectorAll('.step-connector').forEach((el, index) => {
      if (index + 1 < stepNumber) {
        el.style.backgroundColor = '#28a745';
      } else {
        el.style.backgroundColor = '#dee2e6';
      }
    });

    // Clear messages
    clearMessages();
  }

  function startResendTimer() {
    const resendLink = document.getElementById('resendLink');
    const resendTimer = document.getElementById('resendTimer');
    let seconds = 60;

    resendLink.style.pointerEvents = 'none';
    resendLink.style.opacity = '0.5';

    const timer = setInterval(function() {
      seconds--;
      resendTimer.textContent = seconds;

      if (seconds === 0) {
        clearInterval(timer);
        resendLink.style.pointerEvents = 'auto';
        resendLink.style.opacity = '1';
        resendLink.innerHTML = 'Resend now';
      }
    }, 1000);
  }

  function showMessage(message, type) {
    const messageEl = type === 'success' ? 
      document.getElementById('successMessage') : 
      document.getElementById('errorMessage');

    const errorText = document.getElementById('errorText');
    
    if (type === 'error') {
      errorText.textContent = message;
      messageEl.classList.remove('d-none');
      document.getElementById('successMessage').classList.add('d-none');
    } else {
      messageEl.innerHTML = '<i class="fas fa-check-circle"></i> ' + message;
      messageEl.classList.remove('d-none');
      document.getElementById('errorMessage').classList.add('d-none');
    }
  }

  function showError(message) {
    showMessage(message, 'error');
  }

  function clearMessages() {
    document.getElementById('successMessage').classList.add('d-none');
    document.getElementById('errorMessage').classList.add('d-none');
  }
});
