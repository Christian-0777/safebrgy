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
  <link rel="stylesheet" href="assets/css/shared/shared-header.css">
</head>
<body>
  <!-- Navigation -->
  <header class="admin-header">
    <div class="container nav-inner">
      <h1 class="brand">SafeBrgy Admin</h1>
      <nav class="admin-nav">
        <a href="#dashboard">Dashboard</a>
        <a href="#residents">Residents</a>
        <a href="#reports">Reports</a>
        <a href="#announcements">Announcements</a>
      </nav>
      <button class="burger-menu" id="navToggle" aria-label="Toggle navigation" aria-expanded="false">
        <span class="burger-line"></span>
        <span class="burger-line"></span>
        <span class="burger-line"></span>
      </button>
    </div>
    <!-- Mobile Nav Dropdown -->
    <div class="mobile-nav-dropdown" id="adminNav">
      <ul class="mobile-nav-list">
        <li><a href="#dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="#residents"><i class="fas fa-users"></i> Residents</a></li>
        <li><a href="#reports"><i class="fas fa-file-alt"></i> Reports</a></li>
        <li><a href="#announcements"><i class="fas fa-bullhorn"></i> Announcements</a></li>
      </ul>
    </div>
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

  <script src="assets/js/admin.js"></script>
</body>
</html>
