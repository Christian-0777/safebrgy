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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SafeBrgy - Admin Login</title>
  <link rel="icon" type="image/png" href="../assets/img/seal.png">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/admin/login.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid min-vh-100 d-flex">
  <div class="row flex-grow-1">

    <!-- Left Section -->
    <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-center align-items-center text-white bg-primary p-5">
      <img src="../assets/img/seal.png" alt="Barangay Logo" class="mb-4" style="max-width:120px; border-radius:50%;">
      <h2 class="fw-bold">SafeBrgy Admin</h2>
      <p class="lead">Request Documents Anytime, Anywhere!</p>
      <p>Experience online services transactions with our SafeBrgy platform.</p>
      <img src="assets/img/phone_mockup.png" alt="Phone Mockup" class="img-fluid mt-3" style="max-width:250px;">
    </div>

    <!-- Right Section -->
    <div class="col-lg-6 d-flex align-items-center justify-content-center bg-white p-5">
      <div class="w-100" style="max-width:400px;">
        <h3 class="mb-4 text-center">Barangay Admin Access</h3>
        <?php if (!empty($flashError)): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($flashError); ?></div>
        <?php endif; ?>
        <form id="adminLoginForm" method="POST" action="/safebrgy/admin/admin_auth.php">
          <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="rememberMe" name="rememberMe">
              <label class="form-check-label" for="rememberMe">Remember Me</label>
            </div>
            <a href="forgot_password.php" class="small">Forgot Password?</a>
          </div>
          <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <div class="text-center mt-3">
          <p class="mb-1">New to SafeBrgy? <a href="register.php">Register now</a></p>
          <a href="help.php" class="small me-2">Help Center</a>
          <a href="terms.php" class="small me-2">Terms of Use</a>
          <a href="privacy.php" class="small">Privacy Policy</a>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/admin/login.js"></script>
</body>
</html>
