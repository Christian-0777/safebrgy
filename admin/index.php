<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/shared/remember_me.php';
session_start();
$rememberedRole = restoreRememberedLogin(safeBrgy_db_connect());
$isAdminSubdomain = preg_match('/^admin\.safebrgy\.com(?::\d+)?$/i', $_SERVER['HTTP_HOST'] ?? '') === 1;
$appBasePath = '';
if ($rememberedRole === 'admin') {
  header('Location: ' . $appBasePath . '/admin/dashboard');
  exit;
}
if ($rememberedRole === 'resident') {
  header('Location: ' . $appBasePath . '/dashboard');
  exit;
}
// admin.php - Admin Landing Page
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <base href="<?php echo htmlspecialchars($appBasePath . '/admin/', ENT_QUOTES, 'UTF-8'); ?>">
  <title>SafeBrgy Admin — Barangay San Jose</title>
  <link rel="icon" type="image/png" href="../assets/img/seal.png">
  <link rel="stylesheet" href="../assets/css/shared/cookie-consent.css?v=2">
  <link rel="stylesheet" href="../assets/admin_landing.css">
</head>
<body>
  <!-- Navigation -->
  <header class="admin-header">
    <div class="container nav-inner">
      <a href="https://safebrgy.com/landing" class="brand">
        <img src="../assets/img/seal.png" alt="Barangay Seal" class="brand-logo">
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
      <a href="login">Admin Login</a>
    </nav>
  </header>

  <!-- Hero -->
  <section class="hero">
    <div class="hero-overlay">
      <div class="container hero-content">
        <h2>Efficient Barangay Management System</h2>
        <p>Barangay San Jose, San Luis, Pampanga</p>
        <a href="login" class="btn-login">Admin Login</a>
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
        <a href="/external-links/terms-of-service.html">Terms of Service</a> |
        <a href="/external-links/privacy-policy.html">Privacy Policy</a> |
        <a href="https://safebrgy.com/landing#contact">Contact Support</a>
      </div>
    </div>
    <div class="copyright">
      &copy; <?php echo date('Y'); ?> SafeBrgy Admin — All rights reserved.
    </div>
  </footer>

  <div id="cookieConsentModal" class="cookie-consent-backdrop" role="dialog" aria-modal="true" aria-labelledby="cookieConsentTitle" hidden>
    <div class="cookie-consent-modal">
      <h2 id="cookieConsentTitle">Do you want to allow cookies?</h2>
      <p>Cookies help SafeBrgy remember your preferences and keep your signed-in session available.</p>
      <div class="cookie-consent-actions">
        <button type="button" class="cookie-consent-deny" id="cookieConsentDeny">Deny</button>
        <button type="button" class="cookie-consent-allow" id="cookieConsentAllow">Allow</button>
      </div>
    </div>
  </div>

  <script src="../assets/js/shared/logo_functions.js"></script>
  <script src="../assets/admin_landing.js"></script>
  <script src="../assets/js/shared/cookie-consent.js?v=2"></script>
</body>
</html>
