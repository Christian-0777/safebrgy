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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SafeBrgy - Login</title>
  <link rel="icon" type="image/png" href="../assets/img/seal.png">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/public/login.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid min-vh-100 d-flex">
  <div class="row flex-grow-1">

    <!-- Left Section -->
    <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-center align-items-center text-white bg-primary p-5">
      <img src="../assets/img/seal.png" alt="Barangay Logo" class="mb-4" style="max-width:120px;\
      border-radius:50%;">
      <h2 class="fw-bold">SafeBrgy</h2>
      <p class="lead">Request Documents Anytime, Anywhere!</p>
      <p>Experience fast, easy, and hassle-free barangay transactions with our SafeBrgy portal.</p>
      <img src="assets/img/phone_mockup.png" alt="Phone Mockup" class="img-fluid mt-3" style="max-width:250px;">
    </div>

    <!-- Right Section -->
    <div class="col-lg-6 d-flex align-items-center justify-content-center bg-white p-5">
      <div class="w-100" style="max-width:400px;">
        <h3 class="mb-4 text-center">Barangay Residents Access</h3>
        <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
          <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        <form id="loginForm" method="POST" action="login.php">
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
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
<script src="assets/js/public/login.js"></script>
</body>
</html>
