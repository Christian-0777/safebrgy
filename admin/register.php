<?php
// admin_register.php - SafeBrgy Admin Registration
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SafeBrgy - Admin Registration</title>
  <link rel="icon" type="image/png" href="../assets/img/seal.png">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/admin/register.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid min-vh-100 d-flex">
  <div class="row flex-grow-1">

    <!-- Left Section -->
    <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-center align-items-center text-white bg-primary p-5">
      <img src="../assets/img/seal.png" alt="Barangay Logo" class="mb-4" style="max-width:120px; border-radius:50%;">
      <h2 class="fw-bold">SafeBrgy Admin</h2>
      <p class="lead">Request Documents Anytime, Anywhere!</p>
      <p>Create your admin account to manage services and requests.</p>
      <img src="../assets/img/phone_mockup.png" alt="Phone Mockup" class="img-fluid mt-3" style="max-width:250px;">
    </div>

    <!-- Right Section -->
    <div class="col-lg-6 d-flex align-items-center justify-content-center bg-white p-5">
      <div class="w-100" style="max-width:400px;">
        <h3 class="mb-4 text-center">Create Admin Account</h3>
        <form id="adminRegisterForm" method="POST" action="admin_register_process.php">
          
          <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="admin@barangay.com" required>
            <small class="text-muted">Use your official email address</small>
          </div>

          <div class="mb-3">
            <label for="number" class="form-label">Phone Number</label>
            <input type="tel" class="form-control" id="number" name="number" placeholder="+63 9XX XXX XXXX" required>
            <small class="text-muted">Contact number for verification</small>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
            <small class="text-muted">At least 8 characters with uppercase, lowercase, and numbers</small>
          </div>

          <div class="mb-3">
            <label for="confirmPassword" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm password" required>
            <div id="passwordMatch" class="small"></div>
          </div>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="agreeTerms" name="agreeTerms" required>
            <label class="form-check-label" for="agreeTerms">
              I agree to the <a href="terms.php" target="_blank">Terms of Use</a> and <a href="privacy.php" target="_blank">Privacy Policy</a>
            </label>
          </div>

          <button type="submit" class="btn btn-primary w-100">Create Account</button>
        </form>

        <div class="text-center mt-3">
          <p class="mb-1">Already have an account? <a href="login.php">Login here</a></p>
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
<script src="../assets/js/admin/register.js"></script>
</body>
</html>
