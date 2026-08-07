<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mailer.php';
// SafeBrgy Login Page
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $pdo = safeBrgy_db_connect();

    // Check if email exists
    $stmt = $pdo->prepare('SELECT id, password_hash, is_verified, role FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = 'No Account Found! Please Click Register Now to Create Account';
    } elseif (!password_verify($password, $user['password_hash'])) {
        $error = 'Uh oh! If you forgot your password, click the Forgot Password';
    } elseif ($user['is_verified'] == 0) {
        $error = 'Your account was pending for approval, I will notify you using your email for account approval';
    } else {
        // Login successful
        // Fetch resident details
        $stmt = $pdo->prepare('SELECT r.first_name, r.last_name, r.mobile_number FROM residents r WHERE r.user_id = ?');
        $stmt->execute([$user['id']]);
        $resident = $stmt->fetch();

        $full_name = $resident ? ($resident['first_name'] . ' ' . $resident['last_name']) : $user['username'];

        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $email,
            'role' => $user['role'],
            'name' => $full_name,
            'phone' => $resident['mobile_number'] ?? ''
        ];

        if ($user['role'] === 'admin') {
            header('Location: ../admin/main-pages/dashboard.php');
        } else {
            header('Location: public-pages/dashboard.php');
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeBrgy - Login</title>
    <link rel="icon" type="image/png" href="../assets/img/seal.png">
    <link rel="stylesheet" href="../assets/css/shared/colors.css">
    <link rel="stylesheet" href="../assets/css/shared/auth.css">
</head>
<body>
    <div class="login-container">
        <div class="branding-section">
            <div class="brand-logo"><img src="../assets/img/seal.png" alt="seal"></div>
            <div class="brand-title">SafeBrgy</div>
            <div class="brand-description">Your trusted platform for barangay services and requests.</div>
        </div>

        <div class="form-section">
            <h2 class="form-title">Sign In</h2>
            <p class="form-subtitle">Welcome back! Please sign in to your account</p>

            <?php if (isset($error)): ?>
            <div class="error-banner"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form id="loginForm" method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <div class="remember-forgot">
                    <div class="checkbox-group">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember" style="margin-bottom:0; font-weight:400;">Remember me</label>
                    </div>
                    <a href="reset-password.php" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" id="loginBtn">Sign In</button>

                <div class="signup-link">
                    Do not have an account? <a href="../register.php">Create one</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Client-side validation only; allow normal form submit to preserve server flow
        const loginForm = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        loginForm.addEventListener('submit', (e) => {
            const email = emailInput.value.trim();
            const password = passwordInput.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email) || password === '') {
                e.preventDefault();
                alert('Please fill the form correctly.');
            }
        });
    </script>
</body>
</html>
