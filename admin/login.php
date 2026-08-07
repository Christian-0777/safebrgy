<?php
require_once __DIR__ . '/../config/db.php';
// admin_login.php - SafeBrgy Admin Login
session_start();
$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeBrgy - Admin Login</title>
    <link rel="icon" type="image/png" href="../assets/img/seal.png">
    <link rel="stylesheet" href="../assets/css/shared/colors.css">
    <link rel="stylesheet" href="../assets/css/shared/auth.css">
</head>
<body>
    <div class="login-container">
        <div class="branding-section">
            <div class="brand-logo"><img src="../assets/img/seal.png" alt="seal"></div>
            <div class="brand-title">SafeBrgy Admin</div>
            <div class="brand-description">Admin access to manage services and requests securely.</div>
        </div>

        <div class="form-section">
            <h2 class="form-title">Admin Sign In</h2>
            <p class="form-subtitle">Enter your admin credentials</p>

            <?php if (!empty($flashError)): ?>
              <div class="error-banner"><?php echo htmlspecialchars($flashError); ?></div>
            <?php endif; ?>

            <form id="adminLoginForm" method="POST" action="/safebrgy/admin/admin_auth.php">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="admin@barangay.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <div class="remember-forgot">
                    <div class="checkbox-group">
                        <input type="checkbox" id="rememberMe" name="rememberMe">
                        <label for="rememberMe" style="margin-bottom:0; font-weight:400;">Remember me</label>
                    </div>
                    <a href="forgot_password.php" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit">Sign In</button>

                <div class="signup-link">
                    New admin? <a href="register.php">Create account</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const adminForm = document.getElementById('adminLoginForm');
        adminForm.addEventListener('submit', (e)=>{
            const email = document.getElementById('email').value.trim();
            const pass = document.getElementById('password').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email) || pass === ''){ e.preventDefault(); alert('Please fill the form correctly.'); }
        });
    </script>
</body>
</html>
