<?php
require_once __DIR__ . '/../config/db.php';
// reset-password.php - SafeBrgy Password Reset Page
session_start();

// Check if we have a reset token from email
$token = isset($_GET['token']) ? $_GET['token'] : '';
$step = isset($_POST['step']) ? $_POST['step'] : 'email';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="/safebrgy/public/">
  <title>SafeBrgy - Reset Password</title>
  <link rel="icon" type="image/png" href="../assets/img/seal.png">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome for Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="../assets/css/shared/colors.css" rel="stylesheet">
  <link href="../assets/css/shared/auth.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid min-vh-100 d-flex">
  <div class="row flex-grow-1 w-100">

    <!-- Left Section -->
    <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-center align-items-center text-white bg-primary p-5">
      <img src="../assets/img/seal.png" alt="Barangay Logo" class="mb-4" style="max-width:120px;
      border-radius:50%;">
      <h2 class="fw-bold">SafeBrgy</h2>
      <p class="lead">Secure Password Reset</p>
      <p class="text-center">Regain access to your SafeBrgy account securely. Follow the steps to reset your password.</p>
      <div class="mt-5 text-center">
        <i class="fas fa-lock" style="font-size: 80px; opacity: 0.3;"></i>
      </div>
    </div>

    <!-- Right Section -->
    <div class="col-lg-6 d-flex align-items-center justify-content-center bg-white p-5">
      <div class="w-100" style="max-width:450px;">
        
        <!-- Step Indicator -->
        <div class="step-indicator mb-5">
          <div class="step active" id="step1">
            <span class="step-number">1</span>
            <span class="step-title">Verify Email</span>
          </div>
          <div class="step-connector"></div>
          <div class="step" id="step2">
            <span class="step-number">2</span>
            <span class="step-title">Enter Code</span>
          </div>
          <div class="step-connector"></div>
          <div class="step" id="step3">
            <span class="step-number">3</span>
            <span class="step-title">New Password</span>
          </div>
        </div>

        <!-- Step 1: Email Verification -->
        <div class="step-content active" id="content-step1">
          <h4 class="mb-4" style="color: #007bff;">Reset Your Password</h4>
          <p class="text-muted mb-4">Enter the email address or contact number associated with your SafeBrgy account.</p>
          
          <form id="emailForm" method="POST" action="send-reset.php">
            <input type="hidden" name="step" value="email">
            <div class="mb-3">
              <label for="emailOrContact" class="form-label">Email Address or Contact Number</label>
              <input type="text" class="form-control" id="emailOrContact" name="emailOrContact" 
                     placeholder="Enter your email or contact number" required>
              <small class="form-text text-muted d-block mt-2">We'll send you a code to verify your identity.</small>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">Send Reset Code</button>
          </form>

          <div class="text-center">
            <p class="small mb-0">Remember your password? <a href="login.php">Back to Login</a></p>
          </div>
        </div>

        <!-- Step 2: OTP Verification -->
        <div class="step-content" id="content-step2">
          <h4 class="mb-4" style="color: #007bff;">Verify Your Code</h4>
          <p class="text-muted mb-4">Enter the verification code sent to your email or phone.</p>
          
          <form id="otpForm" method="POST" action="verify-reset-code.php">
            <input type="hidden" name="step" value="otp">
            <input type="hidden" id="emailOrContactHidden" name="emailOrContact" value="">
            
            <div class="mb-4">
              <label for="verificationCode" class="form-label">Verification Code</label>
              <input type="text" class="form-control form-control-lg text-center" id="verificationCode" 
                     name="verificationCode" placeholder="000000" maxlength="6" required 
                     style="letter-spacing: 10px; font-weight: bold; font-size: 24px;">
            </div>

            <div class="mb-3" style="text-align: center; color: #666;">
              <small>Didn't receive the code? 
                <a href="#" id="resendLink" style="text-decoration: none; color: #007bff; font-weight: 500;">Resend in <span id="resendTimer">60</span>s</a>
              </small>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">Verify Code</button>
          </form>

          <div class="text-center">
            <a href="#" class="btn btn-link btn-sm" id="backToEmailBtn">Back to Email</a>
          </div>
        </div>

        <!-- Step 3: New Password -->
        <div class="step-content" id="content-step3">
          <h4 class="mb-4" style="color: #007bff;">Create New Password</h4>
          <p class="text-muted mb-4">Enter a strong password to secure your account.</p>
          
          <form id="newPasswordForm" method="POST" action="confirm-password-reset.php">
            <input type="hidden" name="step" value="password">
            <input type="hidden" id="verificationCodeHidden" name="verificationCode" value="">
            <input type="hidden" id="emailOrContactHidden2" name="emailOrContact" value="">

            <div class="mb-3">
              <label for="newPassword" class="form-label">New Password</label>
              <div class="input-group">
                <input type="password" class="form-control" id="newPassword" name="newPassword" 
                       placeholder="Enter a strong password" required>
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
              <small class="form-text text-muted d-block mt-2">
                <ul class="mb-0 ps-3 mt-2" style="font-size: 12px;">
                  <li id="lengthCheck"><i class="fas fa-times" style="color: #dc3545;"></i> At least 8 characters</li>
                  <li id="uppercaseCheck"><i class="fas fa-times" style="color: #dc3545;"></i> One uppercase letter</li>
                  <li id="numberCheck"><i class="fas fa-times" style="color: #dc3545;"></i> One number</li>
                  <li id="specialCheck"><i class="fas fa-times" style="color: #dc3545;"></i> One special character (!@#$%)</li>
                </ul>
              </small>
            </div>

            <div class="mb-4">
              <label for="confirmPassword" class="form-label">Confirm Password</label>
              <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" 
                     placeholder="Confirm your password" required>
              <small id="passwordMatchMsg" class="form-text text-danger d-none">Passwords do not match!</small>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3" id="submitBtn" disabled>Reset Password</button>
          </form>

          <div class="text-center">
            <p class="small mb-0">Remember your password? <a href="login.php">Back to Login</a></p>
          </div>
        </div>

        <!-- Success Message -->
        <div class="alert alert-success d-none" id="successMessage" role="alert">
          <i class="fas fa-check-circle"></i> Password reset successful! 
          <a href="login.php" class="alert-link">Click here to login</a>
        </div>

        <!-- Error Message -->
        <div class="alert alert-danger d-none" id="errorMessage" role="alert">
          <i class="fas fa-exclamation-circle"></i> <span id="errorText">An error occurred. Please try again.</span>
        </div>

      </div>
    </div>

  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/public/reset-password.js"></script>
</body>
</html>
