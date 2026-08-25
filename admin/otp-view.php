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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <base href="/admin/">
  <title>SafeBrgy - OTP Verification</title>
  <link rel="icon" type="image/png" href="../assets/img/seal.png">
  <link href="../assets/css/shared/colors.css" rel="stylesheet">
  <link href="../assets/css/shared/auth.css" rel="stylesheet">
</head>
<body>
  <div class="login-container">
    <div class="branding-section">
      <div class="brand-logo"><img src="../assets/img/seal.png" alt="seal"></div>
      <div class="brand-title">SafeBrgy Admin</div>
      <div class="brand-description">Securely verify your identity to continue managing services and requests.</div>
    </div>

    <div class="form-section">
      <h2 class="form-title">Verify Your Identity</h2>
      <p class="form-subtitle">Enter the 7-digit code sent to your <?php echo htmlspecialchars($verification_method); ?></p>
      <p class="form-subtitle"><strong><?php echo htmlspecialchars($masked_target); ?></strong></p>

      <form id="otpForm" method="POST" action="/admin/verify_otp_process.php">
        <div class="otp-input-group">
            <input type="text" class="otp-input" id="otp1" name="otp1" maxlength="1" placeholder="0" inputmode="numeric">
            <input type="text" class="otp-input" id="otp2" name="otp2" maxlength="1" placeholder="0" inputmode="numeric">
            <input type="text" class="otp-input" id="otp3" name="otp3" maxlength="1" placeholder="0" inputmode="numeric">
            <input type="text" class="otp-input" id="otp4" name="otp4" maxlength="1" placeholder="0" inputmode="numeric">
            <input type="text" class="otp-input" id="otp5" name="otp5" maxlength="1" placeholder="0" inputmode="numeric">
            <input type="text" class="otp-input" id="otp6" name="otp6" maxlength="1" placeholder="0" inputmode="numeric">
            <input type="text" class="otp-input" id="otp7" name="otp7" maxlength="1" placeholder="0" inputmode="numeric">
        </div>

        <input type="hidden" id="otpCode" name="otp_code">

        <div id="errorAlert" class="error-banner <?php echo empty($errorMessage) ? 'hide' : ''; ?>" role="alert">
          <span id="errorMessage"><?php echo htmlspecialchars($errorMessage); ?></span>
        </div>

        <button type="submit" id="verifyBtn">Verify OTP</button>

        <div id="loadingState" class="loading-state hide">Verifying your code...</div>
      </form>

      <div class="signup-link">
        <p>Didn't receive the code?</p>
        <button type="button" class="secondary-button" id="resendBtn" data-resend-cooldown="60">
          <span id="resendBtnText">Resend Code</span>
          <span id="resendTimer" class="hide"> (<span id="resendSeconds">60</span>s)</span>
        </button>
        <p>Code expires in <strong id="expiryTimer">05:00</strong></p>
      </div>

      <div class="signup-link">
        <a href="login.php">Back to Login</a>
      </div>
    </div>
  </div>
<script src="../assets/js/admin/otp-view.js"></script>

</body>
</html>
