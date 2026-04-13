// SafeBrgy Admin Registration Form Validation
document.addEventListener('DOMContentLoaded', function() {
  const passwordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('confirmPassword');
  const passwordMatchDiv = document.getElementById('passwordMatch');
  const submitBtn = document.querySelector('button[type="submit"]');
  const emailInput = document.getElementById('email');
  const numberInput = document.getElementById('number');
  const agreeCheckbox = document.getElementById('agreeTerms');
  const form = document.getElementById('adminRegisterForm');

  // Validate password match on input
  confirmPasswordInput.addEventListener('keyup', function() {
    validatePasswordMatch();
  });

  passwordInput.addEventListener('keyup', function() {
    validatePasswordMatch();
  });

  // Validate entire form on submit
  form.addEventListener('submit', function(e) {
    e.preventDefault();

    // Clear previous messages
    if (!validateForm()) {
      return;
    }

    // If all validations pass, submit the form
    this.submit();
  });

  // Validate password strength on password input change
  passwordInput.addEventListener('keyup', function() {
    validatePasswordStrength();
  });

  // Real-time form validation
  emailInput.addEventListener('blur', function() {
    validateEmail();
  });

  numberInput.addEventListener('blur', function() {
    validatePhoneNumber();
  });

  function validatePasswordMatch() {
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    if (confirmPassword === '') {
      passwordMatchDiv.textContent = '';
      passwordMatchDiv.className = '';
      return;
    }

    if (password === confirmPassword) {
      passwordMatchDiv.textContent = '✓ Passwords match';
      passwordMatchDiv.className = 'small match-success';
      return true;
    } else {
      passwordMatchDiv.textContent = '✗ Passwords do not match';
      passwordMatchDiv.className = 'small match-error';
      return false;
    }
  }

  function validatePasswordStrength() {
    const password = passwordInput.value;
    const errors = [];

    if (password.length < 8) {
      errors.push('At least 8 characters');
    }
    if (!/[A-Z]/.test(password)) {
      errors.push('One uppercase letter');
    }
    if (!/[a-z]/.test(password)) {
      errors.push('One lowercase letter');
    }
    if (!/[0-9]/.test(password)) {
      errors.push('One number');
    }

    if (errors.length > 0) {
      passwordInput.classList.add('is-invalid');
    } else {
      passwordInput.classList.remove('is-invalid');
    }
  }

  function validateEmail() {
    const email = emailInput.value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailRegex.test(email)) {
      emailInput.classList.add('is-invalid');
      return false;
    } else {
      emailInput.classList.remove('is-invalid');
      return true;
    }
  }

  function validatePhoneNumber() {
    const number = numberInput.value;
    const phoneRegex = /^(\+?63|0)?9\d{9}$/;

    if (number !== '' && !phoneRegex.test(number.replace(/\s/g, ''))) {
      numberInput.classList.add('is-invalid');
      return false;
    } else {
      numberInput.classList.remove('is-invalid');
      return true;
    }
  }

  function validateForm() {
    let isValid = true;

    // Validate email
    if (!validateEmail()) {
      isValid = false;
    }

    // Validate phone number
    if (!validatePhoneNumber()) {
      isValid = false;
    }

    // Validate password match
    if (!validatePasswordMatch()) {
      isValid = false;
    }

    // Validate password strength
    const password = passwordInput.value;
    if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(password)) {
      passwordInput.classList.add('is-invalid');
      isValid = false;
    }

    // Validate terms agreement
    if (!agreeCheckbox.checked) {
      alert('You must agree to the Terms of Use and Privacy Policy');
      isValid = false;
    }

    return isValid;
  }
});
