<?php
require_once __DIR__ . '/config/db.php';
// admin.php - Admin Landing Page
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>SafeBrgy Admin — Barangay San Jose</title>
  <link rel="icon" type="image/png" href="assets/img/seal.png">
  <link rel="stylesheet" href="assets/admin_landing.css">
</head>
<body>
  <!-- Navigation -->
  <header class="admin-header">
    <div class="container nav-inner">
      <a href="index.php" class="brand">
        <img src="assets/img/seal.png" alt="Barangay Seal" class="brand-logo">
        <span>Brgy San Jose</span>
      </a>
      <nav class="admin-nav" id="adminNav">
        <a href="#dashboard">Dashboard</a>
        <a href="#residents">Residents</a>
        <a href="#reports">Reports</a>
        <a href="#announcements">Announcements</a>
      </nav>
      <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">☰</button>
    </div>
    <nav class="mobile-nav" id="mobileNav">
      <a href="#dashboard">Dashboard</a>
      <a href="#residents">Residents</a>
      <a href="#reports">Reports</a>
      <a href="#announcements">Announcements</a>
      <a href="admin/login.php">Admin Login</a>
    </nav>
  </header>

  <!-- Hero -->
  <section class="hero">
    <div class="hero-overlay">
      <div class="container hero-content">
        <h2>Efficient Barangay Management System</h2>
        <p>Barangay San Jose, San Luis, Pampanga</p>
        <a href="admin/login.php" class="btn-login">Admin Login</a>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="admin-footer">
    <div class="container footer-grid">
      <div>
        <h4>Barangay San Jose Admin</h4>
      </div>
      <div>
        <a href="#">Terms of Service</a> | 
        <a href="#">Privacy Policy</a> | 
        <a href="#">Contact Support</a>
      </div>
    </div>
    <div class="copyright">
      &copy; <?php echo date('Y'); ?> SafeBrgy Admin — All rights reserved.
    </div>
  </footer>

  <script src="assets/js/shared/logo_functions.js"></script>
  <script src="assets/admin_landing.js"></script>
</body>
</html>
