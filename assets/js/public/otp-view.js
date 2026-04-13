// SafeBrgy Admin OTP Verification Script

document.addEventListener('DOMContentLoaded', function() {
  // Initialize OTP inputs
  initializeOtpInputs();
  
  // Initialize timers
  startExpiryTimer();
  initializeResendButton();
  
  // Form submission
  document.getElementById('otpForm').addEventListener('submit', handleOtpSubmit);
});

/**
 * Initialize OTP input boxes with auto-focus functionality
 */
function initializeOtpInputs() {
  const otpInputs = document.querySelectorAll('.otp-input');
  
  otpInputs.forEach((input, index) => {
    // Allow only numbers
    input.addEventListener('input', function(e) {
      // Remove non-numeric characters
      this.value = this.value.replace(/[^0-9]/g, '');
      
      // Move to next input if value entered
      if (this.value.length === 1 && index < otpInputs.length - 1) {
        otpInputs[index + 1].focus();
      }
      
      // Update combined OTP code
      updateOtpCode();
      
      // Remove error state when user types
      if (this.classList.contains('error')) {
        this.classList.remove('error');
      }
    });
    
    // Handle backspace
    input.addEventListener('keydown', function(e) {
      if (e.key === 'Backspace') {
        e.preventDefault();
        
        // Clear current input
        this.value = '';
        
        // Move to previous input
        if (index > 0) {
          otpInputs[index - 1].focus();
          otpInputs[index - 1].value = '';
          updateOtpCode();
        }
      }
      
      // Handle arrow keys for navigation
      if (e.key === 'ArrowLeft' && index > 0) {
        e.preventDefault();
        otpInputs[index - 1].focus();
      }
      
      if (e.key === 'ArrowRight' && index < otpInputs.length - 1) {
        e.preventDefault();
        otpInputs[index + 1].focus();
      }
    });
    
    // Handle paste event
    input.addEventListener('paste', function(e) {
      e.preventDefault();
      const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '');
      
      // Distribute pasted numbers across inputs
      for (let i = 0; i < Math.min(pastedData.length, otpInputs.length - index); i++) {
        otpInputs[index + i].value = pastedData[i];
      }
      
      // Focus last filled input
      const lastIndex = Math.min(index + pastedData.length - 1, otpInputs.length - 1);
      otpInputs[lastIndex].focus();
      
      updateOtpCode();
    });
    
    // Set initial focus to first input
    if (index === 0) {
      input.focus();
    }
  });
}

/**
 * Update the hidden OTP code input with combined values
 */
function updateOtpCode() {
  const otpInputs = document.querySelectorAll('.otp-input');
  const otpCode = Array.from(otpInputs)
    .map(input => input.value)
    .join('');
  
  document.getElementById('otpCode').value = otpCode;
  
  // Auto-submit if all 6 digits are entered
  if (otpCode.length === 6) {
    // Optional: auto-submit after a short delay
    // submitOtp();
  }
}

/**
 * Handle OTP form submission
 */
function handleOtpSubmit(e) {
  e.preventDefault();
  
  const otpCode = document.getElementById('otpCode').value;
  const errorAlert = document.getElementById('errorAlert');
  const errorMessage = document.getElementById('errorMessage');
  
  // Validate OTP
  if (otpCode.length !== 6) {
    showError('Please enter all 6 digits');
    shakeOtpInputs();
    return;
  }
  
  if (!/^\d{6}$/.test(otpCode)) {
    showError('Invalid OTP format. Please enter numeric digits only.');
    shakeOtpInputs();
    return;
  }
  
  // Disable button and show loading state
  const verifyBtn = document.getElementById('verifyBtn');
  const loadingState = document.getElementById('loadingState');
  
  verifyBtn.disabled = true;
  verifyBtn.classList.add('hide');
  loadingState.classList.add('show');
  
  // Submit form
  this.submit();
}

/**
 * Show error message
 */
function showError(message) {
  const errorAlert = document.getElementById('errorAlert');
  const errorMessage = document.getElementById('errorMessage');
  
  errorMessage.textContent = message;
  errorAlert.classList.remove('hide');
  
  // Auto-hide after 5 seconds
  setTimeout(() => {
    errorAlert.classList.add('hide');
  }, 5000);
}

/**
 * Shake OTP inputs for visual feedback
 */
function shakeOtpInputs() {
  const otpInputs = document.querySelectorAll('.otp-input');
  
  otpInputs.forEach((input, index) => {
    input.classList.add('shake', 'error');
    input.value = '';
    
    // Remove shake animation after it completes
    setTimeout(() => {
      input.classList.remove('shake');
    }, 400);
  });
  
  // Focus first input after shake
  setTimeout(() => {
    otpInputs[0].focus();
  }, 400);
  
  updateOtpCode();
}

/**
 * Start expiry timer (5 minutes default)
 */
function startExpiryTimer() {
  let timeLeft = 300; // 5 minutes in seconds
  const timerDisplay = document.getElementById('expiryTimer');
  
  const interval = setInterval(() => {
    timeLeft--;
    
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    
    timerDisplay.textContent = 
      String(minutes).padStart(2, '0') + ':' + 
      String(seconds).padStart(2, '0');
    
    // Change color when time is running out
    if (timeLeft <= 60) {
      timerDisplay.style.color = '#dc3545'; // Red
    }
    
    // Clear interval when time is up
    if (timeLeft <= 0) {
      clearInterval(interval);
      handleOtpExpired();
    }
  }, 1000);
}

/**
 * Handle OTP expiry
 */
function handleOtpExpired() {
  const otpInputs = document.querySelectorAll('.otp-input');
  const verifyBtn = document.getElementById('verifyBtn');
  
  // Disable all inputs
  otpInputs.forEach(input => {
    input.disabled = true;
    input.classList.add('error');
  });
  
  verifyBtn.disabled = true;
  
  showError('OTP has expired. Please request a new code.');
}

/**
 * Initialize resend button
 */
function initializeResendButton() {
  const resendBtn = document.getElementById('resendBtn');
  const resendCooldown = parseInt(resendBtn.dataset.resendCooldown) || 60;
  
  resendBtn.addEventListener('click', function(e) {
    e.preventDefault();
    
    // Make AJAX request to resend OTP
    fetch('resend_otp.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        action: 'resend'
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Show success message
        showSuccessNotification('OTP resent successfully!');
        
        // Start cooldown timer
        startResendCooldown(resendCooldown);
        
        // Clear current OTP inputs
        document.querySelectorAll('.otp-input').forEach(input => {
          input.value = '';
          input.classList.remove('error', 'success');
        });
        document.getElementById('otpCode').value = '';
        
        // Focus first input
        document.getElementById('otp1').focus();
        
        // Restart expiry timer
        location.reload(); // Or restart timer programmatically
      } else {
        showError(data.message || 'Failed to resend OTP. Please try again.');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showError('An error occurred. Please try again.');
    });
  });
}

/**
 * Start resend cooldown timer
 */
function startResendCooldown(cooldownSeconds) {
  const resendBtn = document.getElementById('resendBtn');
  const resendBtnText = document.getElementById('resendBtnText');
  const resendTimer = document.getElementById('resendTimer');
  const resendSeconds = document.getElementById('resendSeconds');
  
  let secondsLeft = cooldownSeconds;
  
  resendBtn.disabled = true;
  resendTimer.classList.remove('hide');
  
  const interval = setInterval(() => {
    secondsLeft--;
    resendSeconds.textContent = secondsLeft;
    
    if (secondsLeft <= 0) {
      clearInterval(interval);
      resendBtn.disabled = false;
      resendTimer.classList.add('hide');
      resendBtnText.textContent = 'Resend Code';
    }
  }, 1000);
}

/**
 * Show success notification
 */
function showSuccessNotification(message) {
  // Create temporary success alert
  const alertDiv = document.createElement('div');
  alertDiv.className = 'alert alert-success alert-dismissible fade show';
  alertDiv.setAttribute('role', 'alert');
  alertDiv.innerHTML = `
    <i class="fas fa-check-circle me-2"></i>
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  
  // Insert at top of form
  document.getElementById('otpForm').insertAdjacentElement('beforebegin', alertDiv);
  
  // Auto-remove after 5 seconds
  setTimeout(() => {
    alertDiv.remove();
  }, 5000);
}

/**
 * Utility: Format time to MM:SS
 */
function formatTime(seconds) {
  const minutes = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return String(minutes).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
}

/**
 * Utility: Clear all OTP inputs
 */
function clearAllOtpInputs() {
  document.querySelectorAll('.otp-input').forEach(input => {
    input.value = '';
    input.classList.remove('error', 'success');
  });
  document.getElementById('otpCode').value = '';
  document.getElementById('otp1').focus();
}

/**
 * Handle visibility change (page hidden/visible)
 */
document.addEventListener('visibilitychange', function() {
  if (!document.hidden) {
    // Page became visible
    // You can add logic here if needed (e.g., refresh timer status)
  }
});
