<?php
require_once __DIR__ . '/../config/db.php';
session_start();

if (!empty($_SESSION['user'])) {
    header('Location: /safebrgy/dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/safebrgy/public/">
    <title>SafeBrgy - Reset Password</title>
    <link rel="icon" type="image/png" href="../assets/img/seal.png">
    <link rel="stylesheet" href="../assets/css/shared/colors.css">
    <link rel="stylesheet" href="../assets/css/shared/auth.css">
    <style>
        .reset-steps { display: flex; align-items: center; margin: 0 0 30px; }
        .reset-step { color: var(--text-secondary); font-size: 12px; text-align: center; min-width: 58px; }
        .reset-step-number { display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; margin: 0 auto 5px; border: 2px solid var(--form-border); border-radius: 50%; font-weight: 700; }
        .reset-step.active, .reset-step.completed { color: var(--button-primary); }
        .reset-step.active .reset-step-number, .reset-step.completed .reset-step-number { border-color: var(--button-primary); background: var(--button-primary); color: #fff; }
        .reset-connector { flex: 1; height: 2px; margin: 0 4px 20px; background: var(--form-border); }
        .reset-connector.completed { background: var(--button-primary); }
        .reset-content { display: none; }
        .reset-content.active { display: block; }
        .reset-note { color: var(--text-secondary); font-size: 14px; line-height: 1.5; margin-bottom: 20px; }
        .reset-message { display: none; padding: 11px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
        .reset-message.visible { display: block; }
        .reset-message.error { background: #fdecea; color: #842029; }
        .reset-message.success { background: #d4edda; color: #155724; }
        .reset-back { display: block; text-align: center; margin-top: 16px; color: var(--button-primary); font-size: 14px; text-decoration: none; }
        .reset-link { background: none; color: var(--button-primary); padding: 0; width: auto; font-size: 14px; }
        .reset-link:disabled { opacity: .5; cursor: default; }
        .password-hint { color: var(--text-secondary); font-size: 12px; margin-top: 7px; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="branding-section">
            <div class="brand-logo"><img src="../assets/img/seal.png" alt="SafeBrgy seal"></div>
            <div class="brand-title">SafeBrgy</div>
            <div class="brand-description">Your trusted platform for barangay services and requests.</div>
        </div>
        <div class="form-section">
            <h2 class="form-title">Reset Password</h2>
            <p class="form-subtitle">Recover access to your resident account</p>
            <div class="reset-steps" aria-label="Password reset progress">
                <div class="reset-step active" data-step="1"><span class="reset-step-number">1</span>Email</div>
                <div class="reset-connector"></div>
                <div class="reset-step" data-step="2"><span class="reset-step-number">2</span>Code</div>
                <div class="reset-connector"></div>
                <div class="reset-step" data-step="3"><span class="reset-step-number">3</span>Password</div>
            </div>
            <div id="resetMessage" class="reset-message" role="alert"></div>
            <section class="reset-content active" data-content="1">
                <p class="reset-note">Enter the email address linked to your verified resident account. We will send you a six-digit reset code.</p>
                <form id="emailForm" method="post" action="/safebrgy/reset-password/send-code">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="you@example.com" required autocomplete="email">
                    </div>
                    <button type="submit" id="sendCodeButton">Send Reset Code</button>
                </form>
                <a class="reset-back" href="login.php">Remember your password? Back to Login</a>
            </section>
            <section class="reset-content" data-content="2">
                <p class="reset-note">Enter the six-digit code sent to <strong id="maskedEmail"></strong>.</p>
                <form id="codeForm" method="post" action="/safebrgy/reset-password/verify-code">
                    <div class="form-group">
                        <label for="code">Verification Code</label>
                        <input type="text" id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="000000" required autocomplete="one-time-code">
                    </div>
                    <button type="submit">Verify Code</button>
                </form>
                <button type="button" class="reset-link" id="resendButton" disabled>Resend code in <span id="resendTimer">60</span>s</button>
                <button type="button" class="reset-link" id="backButton">Use a different email</button>
            </section>
            <section class="reset-content" data-content="3">
                <p class="reset-note">Create a new password for your SafeBrgy resident account.</p>
                <form id="passwordForm" method="post" action="/safebrgy/reset-password/confirm">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter a strong password" required autocomplete="new-password">
                        <div class="password-hint">At least 8 characters, one uppercase letter, one number, and one special character.</div>
                    </div>
                    <div class="form-group">
                        <label for="confirmation">Re-enter New Password</label>
                        <input type="password" id="confirmation" name="confirmation" placeholder="Re-enter your password" required autocomplete="new-password">
                    </div>
                    <button type="submit" id="confirmButton" disabled>Confirm Password</button>
                </form>
            </section>
        </div>
    </div>
    <script src="../assets/js/public/reset-password.js"></script>
</body>
</html>