<?php
require_once __DIR__ . '/../admin_protect.php';
// admin_dashboard.php - SafeBrgy Admin Dashboard

$pdo = safeBrgy_db_connect();
$adminId = $_SESSION['admin_user']['id'] ?? null;

if ($adminId) {
    $stmt = $pdo->prepare('SELECT username, email FROM users WHERE id = :id');
    $stmt->execute(['id' => $adminId]);
    $admin = $stmt->fetch();
    $user = $admin['username'] ?? 'Admin';
} else {
    $user = 'Admin';
}

// Fetch stats
$totalResidents = $pdo->query('SELECT COUNT(*) FROM residents')->fetchColumn();
$newReports = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'New'")->fetchColumn();
$pendingRequests = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'Pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SafeBrgy - Admin Dashboard</title>
  <link rel="icon" type="image/png" href="../../assets/img/seal.png">
  <!-- Shared Styles -->
  <link rel="stylesheet" href="../../assets/css/shared/shared-header.css">
  <link rel="stylesheet" href="../../assets/css/shared/shared_sidebar.css">
  <link rel="stylesheet" href="../../assets/css/shared/colors.css">
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="../../assets/css/admin/dashboard.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

  <!-- HEADER -->
  <header class="header">
    <div class="header-left">
      <button class="sidebar-toggle"><i class="fas fa-bars"></i></button>
      <a href="../../index.php" class="header-logo">
        <img src="../../assets/img/seal.png" alt="SafeBrgy Logo" class="logo-image">
        <span>SafeBrgy</span>
      </a>
    </div>

    <div class="header-right">
      <div class="user-profile">
        <div class="profile-avatar"><?php echo substr($user, 0, 1); ?></div>
        <div class="profile-info">
          <div class="profile-name"><?php echo htmlspecialchars($user); ?></div>
          <div class="profile-role">Admin</div>
        </div>
        <div class="profile-dropdown">
          <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
          <a href="account_settings.php"><i class="fas fa-cog"></i> Settings</a>
          <button class="logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </div>
      </div>
    </div>
  </header>

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <ul class="sidebar-menu">
      <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span class="menu-label">Dashboard</span></a></li>
      <li><a href="announcement.php"><i class="fas fa-bullhorn"></i> <span class="menu-label">Announcements</span></a></li>
      <li><a href="reports.php"><i class="fas fa-file-alt"></i> <span class="menu-label">Reports</span></a></li>
      <li><a href="requests.php"><i class="fas fa-clipboard-list"></i> <span class="menu-label">Requests</span></a></li>
      <li><a href="user_verification.php"><i class="fas fa-check-circle"></i> <span class="menu-label">Verification</span></a></li>
    </ul>
    
    <div class="sidebar-footer">
      <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> <span class="menu-label">Logout</span></a>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <div>
      <div class="breadcrumb">
        <a href="dashboard.php">Dashboard</a>
        <span class="separator">/</span>
        <span class="current">Home</span>
      </div>

      <h2>Welcome back, <?php echo htmlspecialchars($user); ?>!</h2>

      <!-- Quick Stats -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 30px 0;">
        <div style="background: var(--color-neutral-white); padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
          <h5 style="color: var(--color-primary-deep);">Total Residents</h5>
          <p style="font-size: 32px; font-weight: bold; margin: 10px 0 0 0;"><?php echo number_format($totalResidents); ?></p>
        </div>
        <div style="background: var(--color-neutral-white); padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
          <h5 style="color: var(--color-accent-orange);">New Reports</h5>
          <p style="font-size: 32px; font-weight: bold; margin: 10px 0 0 0;"><?php echo number_format($newReports); ?></p>
        </div>
        <div style="background: var(--color-neutral-white); padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
          <h5 style="color: var(--color-primary-medium);">Pending Requests</h5>
          <p style="font-size: 32px; font-weight: bold; margin: 10px 0 0 0;"><?php echo number_format($pendingRequests); ?></p>
        </div>
      </div>

      <!-- Alert Map -->
      <h4 style="margin-top: 40px; margin-bottom: 20px;">Alert Map</h4>
      <div style="background: var(--color-neutral-white); border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid var(--color-neutral-light-gray);">
          <span>🟡 Road Accident</span>
          <div style="width: 300px; height: 20px; background: #f0f0f0; border-radius: 10px; overflow: hidden;">
            <div style="width: 40%; height: 100%; background: var(--color-accent-orange);"></div>
          </div>
        </div>
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid var(--color-neutral-light-gray);">
          <span>🟠 Fire Incident</span>
          <div style="width: 300px; height: 20px; background: #f0f0f0; border-radius: 10px; overflow: hidden;">
            <div style="width: 25%; height: 100%; background: #ff4444;"></div>
          </div>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px;">
          <span>🟢 Street Light Repair</span>
          <div style="width: 300px; height: 20px; background: #f0f0f0; border-radius: 10px; overflow: hidden;">
            <div style="width: 70%; height: 100%; background: #4CAF50;"></div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <footer style="margin-top: 50px; text-align: center; color: var(--color-neutral-medium-gray); padding: 20px 0;">
        <p>Barangay San Jose</p>
        <a href="../terms.php" style="margin-right: 15px; color: var(--color-primary-deep); text-decoration: none;">Terms of Service</a>
        <a href="../privacy.php" style="margin-right: 15px; color: var(--color-primary-deep); text-decoration: none;">Privacy Policy</a>
        <a href="../support.php" style="color: var(--color-primary-deep); text-decoration: none;">Contact Support</a>
      </footer>
    </div>
  </main>

  <!-- Shared Scripts -->
  <script src="../../assets/js/shared/shared-header.js"></script>
  <script src="../../assets/js/shared/shared-sidebar.js"></script>
  <!-- Page-specific scripts -->
  <script src="../../assets/js/admin/dashboard.js"></script>
</body>
</html>
