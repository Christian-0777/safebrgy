<?php
require_once __DIR__ . '/../config/db.php';
// admin_register.php - SafeBrgy Admin Registration
session_start();
$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeBrgy - Admin Registration</title>
    <link rel="stylesheet" href="../assets/css/shared/colors.css">
    <link rel="stylesheet" href="../assets/css/shared/auth.css">
</head>
<body>
    <div class="login-container">
        <div class="branding-section">
            <div class="brand-logo"><img src="../assets/img/seal.png" alt="seal"></div>
            <div class="brand-title">SafeBrgy Admin</div>
            <div class="brand-description">Create an admin account to manage services and requests securely.</div>
        </div>

        <div class="form-section">
            <h2 class="form-title">Create Admin Account</h2>
            <p class="form-subtitle">Register your admin account</p>

            <?php if (!empty($flashError)): ?>
              <div class="error-banner"><?php echo htmlspecialchars($flashError); ?></div>
            <?php endif; ?>

            <form id="adminRegisterForm" method="POST" action="/safebrgy/admin/admin_register_process.php">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="admin@barangay.com" required>
                    <small style="color:#666; display:block; margin-top:6px;">Use your official email address</small>
                </div>

                <div class="form-group">
                    <label for="fullName">Full Name</label>
                    <input type="text" id="fullName" name="fullName" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label for="number">Phone Number</label>
                    <input type="tel" id="number" name="number" placeholder="+63 9XX XXX XXXX" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                    <small style="color:#666; display:block; margin-top:6px;">At least 8 characters with uppercase, lowercase, and numbers</small>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm password" required>
                    <div id="passwordMatch" style="font-size:13px; margin-top:6px;"></div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="agreeTerms" name="agreeTerms">
                    <label class="form-check-label" for="agreeTerms"> I agree to the <a href="terms.php" target="_blank">Terms of Use</a> and <a href="privacy.php" target="_blank">Privacy Policy</a></label>
                </div>

                <button type="submit" id="createAccountBtn">Create Account</button>
            </form>

            <div class="signup-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <script>
        // Simple password match UI
        const pwd = document.getElementById('password');
        const cpwd = document.getElementById('confirmPassword');
        const matchDiv = document.getElementById('passwordMatch');
        function checkMatch(){
            if (pwd.value && cpwd.value){
                if (pwd.value === cpwd.value){ matchDiv.textContent = 'Passwords match'; matchDiv.style.color = '#198754'; }
                else { matchDiv.textContent = 'Passwords do not match'; matchDiv.style.color = '#dc3545'; }
            } else { matchDiv.textContent = ''; }
        }
        pwd.addEventListener('input', checkMatch);
        cpwd.addEventListener('input', checkMatch);
    </script>
</body>
</html>
