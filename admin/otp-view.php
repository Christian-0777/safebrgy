<?php
require_once __DIR__ . '/../config/db.php';
// admin/otp-view.php - SafeBrgy Admin OTP Verification
session_start();
$errorMessage = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

// Check if we have a pending verification
if (!isset($_SESSION['pending_verification']) || !isset($_SESSION['verification_method'])) {
    header("Location: login.php");
    exit;
}

$verification_method = $_SESSION['verification_method']; // 'email' or 'phone'
$masked_target = $_SESSION['masked_target'] ?? 'your registered account';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="/admin/">
  <title>SafeBrgy - OTP Verification</title>
  <link rel="icon" type="image/png" href="../assets/img/seal.png">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/shared/colors.css" rel="stylesheet">
  <link href="../assets/css/shared/auth.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

<div class="container-fluid min-vh-100 d-flex">
  <div class="row flex-grow-1 w-100">

    <!-- Left Section -->
    <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-center align-items-center text-white bg-primary p-5">
      <img src="../assets/img/seal.png" alt="Barangay Logo" class="mb-4" style="max-width:120px; border-radius:50%;">
      <h2 class="fw-bold">SafeBrgy Admin</h2>
      <p class="lead">Two-Factor Authentication</p>
      <p>Your account security is our priority. Verify your identity with a one-time password.</p>
      <div class="mt-5">
        <div class="feature-item mb-4">
          <i class="fas fa-shield-alt fa-2x mb-2"></i>
          <h5>Enhanced Security</h5>
          <p class="small">Protect your admin account with two-factor authentication</p>
        </div>
        <div class="feature-item mb-4">
          <i class="fas fa-lock fa-2x mb-2"></i>
          <h5>Secure Verification</h5>
          <p class="small">One-time codes ensure only you can access your account</p>
        </div>
      </div>
    </div>

    <!-- Right Section -->
    <div class="col-lg-6 d-flex align-items-center justify-content-center bg-white p-5">
      <div class="w-100" style="max-width:450px;">
        
        <!-- Header -->
        <div class="text-center mb-4">
          <div class="otp-icon-wrapper mb-3">
            <i class="fas fa-envelope-open-text"></i>
          </div>
          <h3 class="mb-2">Verify Your Identity</h3>
          <p class="text-muted">Enter the 7-digit code sent to your <?php echo htmlspecialchars($verification_method); ?></p>
          <p class="text-secondary small"><strong><?php echo htmlspecialchars($masked_target); ?></strong></p>
        </div>

        <!-- OTP Form -->
        <form id="otpForm" method="POST" action="/admin/verify_otp_process.php">
          
          <!-- OTP Input Boxes -->
          <div class="otp-input-group mb-4">
            <input type="text" class="otp-input" id="otp1" name="otp1" maxlength="1" placeholder="0" inputmode="numeric">
            <input type="text" class="otp-input" id="otp2" name="otp2" maxlength="1" placeholder="0" inputmode="numeric">
            <input type="text" class="otp-input" id="otp3" name="otp3" maxlength="1" placeholder="0" inputmode="numeric">
            <input type="text" class="otp-input" id="otp4" name="otp4" maxlength="1" placeholder="0" inputmode="numeric">
            <input type="text" class="otp-input" id="otp5" name="otp5" maxlength="1" placeholder="0" inputmode="numeric">
            <input type="text" class="otp-input" id="otp6" name="otp6" maxlength="1" placeholder="0" inputmode="numeric">
            <input type="text" class="otp-input" id="otp7" name="otp7" maxlength="1" placeholder="0" inputmode="numeric">
          </div>

          <!-- Hidden input for full OTP code -->
          <input type="hidden" id="otpCode" name="otp_code">

          <!-- Error Message -->
          <div id="errorAlert" class="alert alert-danger alert-dismissible fade <?php echo !empty($errorMessage) ? 'show' : 'hide'; ?>" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <span id="errorMessage"><?php echo htmlspecialchars($errorMessage); ?></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn btn-primary w-100 mb-3" id="verifyBtn">
            <i class="fas fa-check me-2"></i>Verify OTP
          </button>

          <!-- Loading State -->
          <div id="loadingState" class="text-center hide">
            <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
              <span class="visually-hidden">Verifying...</span>
            </div>
            <span>Verifying your code...</span>
          </div>

        </form>

        <!-- Divider -->
        <div class="divider my-3">
          <span>or</span>
        </div>

        <!-- Additional Actions -->
        <div class="text-center">
          <p class="small text-muted mb-2">Didn't receive the code?</p>
          <button type="button" class="btn btn-outline-secondary btn-sm mb-3" id="resendBtn" data-resend-cooldown="60">
            <i class="fas fa-redo me-1"></i>
            <span id="resendBtnText">Resend Code</span>
            <span id="resendTimer" class="hide"> (<span id="resendSeconds">60</span>s)</span>
          </button>
          <p class="small text-muted">
            Code expires in <strong id="expiryTimer">05:00</strong>
          </p>
        </div>

        <!-- Footer Links -->
        <div class="text-center mt-4">
          <p class="small">
            <a href="javascript:history.back()" class="text-decoration-none">← Back to Login</a>
          </p>
        </div>

      </div>
    </div>

  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- OTP Script -->
<script src="../assets/js/admin/otp-view.js"></script>

</body>
</html>
