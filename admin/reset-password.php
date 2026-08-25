<?php
require_once __DIR__ . '/../config/db.php';
session_start();

if (!empty($_SESSION['admin_user'])) {
    header('Location: /safebrgy/admin/main-pages/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/admin/">
    <title>SafeBrgy - Reset Admin Password</title>
    <link rel="icon" type="image/png" href="../assets/img/seal.png">
    <link rel="stylesheet" href="../assets/css/shared/colors.css">
    <link rel="stylesheet" href="../assets/css/shared/auth.css">
    <style>
        .reset-steps { display: flex; align-items: flex-start; margin: 0 0 28px; }
        .reset-step { color: var(--text-secondary); font-size: 12px; text-align: center; min-width: 62px; }
        .reset-step-number { display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; margin: 0 auto 5px; border: 2px solid var(--form-border); border-radius: 50%; font-weight: 700; }
        .reset-step.active, .reset-step.completed { color: var(--button-primary); }
        .reset-step.active .reset-step-number, .reset-step.completed .reset-step-number { border-color: var(--button-primary); background: var(--button-primary); color: #fff; }
        .reset-connector { flex: 1; height: 2px; margin: 14px 4px 0; background: var(--form-border); }
        .reset-connector.completed { background: var(--button-primary); }
        .reset-content { display: none; }
        .reset-content.active { display: block; }
        .reset-note { color: var(--text-secondary); font-size: 14px; line-height: 1.5; margin-bottom: 20px; }
        .reset-message { display: none; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
        .reset-message.visible { display: block; }
        .reset-message.error { background: #fdecea; color: #842029; }
        .reset-message.success { background: #d4edda; color: #155724; }
        .reset-back, .reset-action { display: block; width: auto; margin: 16px auto 0; padding: 0; background: none; color: var(--button-primary); font-size: 14px; font-weight: 500; text-align: center; }
        .reset-action:disabled { color: var(--text-secondary); cursor: default; }
        .code-input { text-align: center; letter-spacing: 8px; font-weight: 700; }
        .password-hint { color: var(--text-secondary); font-size: 12px; line-height: 1.5; margin-top: 7px; }
        .password-match { color: #842029; font-size: 12px; margin-top: 7px; }
        .password-match.hidden { display: none; }
        .password-toggle { width: auto; position: absolute; right: 8px; top: 8px; padding: 5px 8px; background: transparent; color: var(--text-secondary); font-size: 14px; }
        .password-wrap { position: relative; }
        .password-wrap input { padding-right: 48px; }
        @media (max-width: 768px) { .reset-steps { margin-bottom: 22px; } .reset-step { min-width: 54px; } }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="branding-section">
            <div class="brand-logo"><img src="../assets/img/seal.png" alt="seal"></div>
            <div class="brand-title">SafeBrgy Admin</div>
            <div class="brand-description">Recover secure access to your admin account.</div>
        </div>

        <div class="form-section">
            <h2 class="form-title">Reset Password</h2>
            <p class="form-subtitle">Complete the steps to regain access</p>

            <div class="reset-steps" aria-label="Password reset progress">
                <div class="reset-step active" data-step="1"><span class="reset-step-number">1</span>Email</div>
                <div class="reset-connector"></div>
                <div class="reset-step" data-step="2"><span class="reset-step-number">2</span>Code</div>
                <div class="reset-connector"></div>
                <div class="reset-step" data-step="3"><span class="reset-step-number">3</span>Password</div>
            </div>

            <div id="resetMessage" class="reset-message" role="alert"></div>

            <section class="reset-content active" data-content="1">
                <p class="reset-note">Enter the email address connected to your verified admin account. We will send you a six-digit reset code.</p>
                <form id="emailForm" method="post" action="send-reset.php">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="admin@barangay.com" required autocomplete="email">
                    </div>
                    <button type="submit" id="sendCodeButton">Send Reset Code</button>
                </form>
                <a class="reset-back" href="login.php">Remember your password? Back to Login</a>
            </section>

            <section class="reset-content" data-content="2">
                <p class="reset-note">Enter the six-digit code sent to <strong id="maskedEmail"></strong>.</p>
                <form id="codeForm" method="post" action="verify-reset-code.php">
                    <div class="form-group">
                        <label for="code">Verification Code</label>
                        <input class="code-input" type="text" id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="000000" required autocomplete="one-time-code">
                    </div>
                    <button type="submit">Verify Code</button>
                </form>
                <button type="button" class="reset-action" id="resendButton" disabled>Resend code in <span id="resendTimer">60</span>s</button>
                <button type="button" class="reset-action" id="backButton">Use a different email</button>
            </section>

            <section class="reset-content" data-content="3">
                <p class="reset-note">Create a strong new password for your admin account.</p>
                <form id="passwordForm" method="post" action="confirm-password-reset.php">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div class="password-wrap">
                            <input type="password" id="password" name="password" placeholder="Enter a strong password" required autocomplete="new-password">
                            <button type="button" class="password-toggle" id="togglePassword" aria-label="Show password">Show</button>
                        </div>
                        <div class="password-hint">At least 8 characters, one uppercase letter, one number, and one special character.</div>
                    </div>
                    <div class="form-group">
                        <label for="confirmation">Re-enter New Password</label>
                        <input type="password" id="confirmation" name="confirmation" placeholder="Re-enter your password" required autocomplete="new-password">
                        <div id="passwordMatchMsg" class="password-match hidden">Passwords do not match.</div>
                    </div>
                    <button type="submit" id="confirmButton" disabled>Confirm Password</button>
                </form>
            </section>
        </div>
    </div>
    <script src="../assets/js/admin/reset-password.js"></script>
</body>
</html>
