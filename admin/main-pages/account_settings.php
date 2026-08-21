<?php
require_once __DIR__ . '/../admin_protect.php';
// admin_settings.php - SafeBrgy Admin Account Settings

$pdo = safeBrgy_db_connect();
$adminId = $_SESSION['admin_user']['id'] ?? null;
$admin = [];
$barangay = [];
$logs = [];

if ($adminId) {
  $stmt = $pdo->prepare('SELECT username, email, phone, profile_image, two_factor_enabled FROM users WHERE id = :id');
  $stmt->execute(['id' => $adminId]);
  $admin = $stmt->fetch() ?: [];

  $stmt = $pdo->query('SELECT * FROM barangay_settings WHERE id = 1');
  $barangay = $stmt->fetch() ?: [];

  $logsSql = <<<SQL
SELECT 'admin' AS log_type, al.id, al.admin_id AS user_id, u.username,
       al.action AS event_type, al.meta AS event_meta, NULL AS email,
       NULL AS mobile_number, NULL AS email_sent, NULL AS sms_sent,
       NULL AS status, al.created_at
FROM admin_logs al
LEFT JOIN users u ON u.id = al.admin_id
UNION ALL
SELECT 'notification' AS log_type, sl.id, sl.user_id, u.username,
       sl.event_type, sl.event_meta, sl.email, sl.mobile_number,
       sl.email_sent, sl.sms_sent, sl.status, sl.created_at
FROM sms_logs sl
LEFT JOIN users u ON u.id = sl.user_id
ORDER BY created_at DESC
LIMIT 200
SQL;
  $logs = $pdo->query($logsSql)->fetchAll(PDO::FETCH_ASSOC);
}

$user = $admin['username'] ?? 'Admin';
$email = $admin['email'] ?? '';
$phone = $admin['phone'] ?? '';
$profileImage = $admin['profile_image'] ?? '';
$profileImageUrl = $profileImage ? '/' . ltrim(str_replace('\\', '/', $profileImage), '/') : '';
$e = static fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="/safebrgy/admin/main-pages/">
  <title>SafeBrgy - Admin Account Settings</title>
  <link rel="icon" type="image/png" href="../../assets/img/seal.png">
  <!-- Shared Styles -->
  <link rel="stylesheet" href="../../assets/css/shared/shared-header.css">
  <link rel="stylesheet" href="../../assets/css/shared/shared_sidebar.css">
  <link rel="stylesheet" href="../../assets/css/shared/colors.css">
  <link rel="stylesheet" href="../../assets/css/shared/layout.css">
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="../../assets/css/admin/account_settings.css">
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
      <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span class="menu-label">Logout</span></a>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <div class="settings-page">
      <div class="settings-heading">
        <div>
          <span class="eyebrow"><i class="fas fa-sliders-h"></i> Administration</span>
          <h1>Account settings</h1>
          <p>Manage your profile, barangay identity, and system controls.</p>
        </div>
      </div>

      <?php if (!empty($_SESSION['settings_success'])): ?><div class="settings-alert success"><i class="fas fa-check-circle"></i><?php echo $e($_SESSION['settings_success']); unset($_SESSION['settings_success']); ?></div><?php endif; ?>
      <?php if (!empty($_SESSION['settings_error'])): ?><div class="settings-alert error"><i class="fas fa-exclamation-circle"></i><?php echo $e($_SESSION['settings_error']); unset($_SESSION['settings_error']); ?></div><?php endif; ?>

      <nav class="settings-tabs" aria-label="Settings sections">
        <button class="settings-tab active" type="button" data-tab="account"><i class="fas fa-user"></i><span>Account</span></button>
        <button class="settings-tab" type="button" data-tab="barangay"><i class="fas fa-landmark"></i><span>Barangay Information</span></button>
        <button class="settings-tab" type="button" data-tab="security"><i class="fas fa-shield-alt"></i><span>Security</span></button>
        <button class="settings-tab" type="button" data-tab="maintenance"><i class="fas fa-server"></i><span>System Maintenance</span></button>
      </nav>

      <section class="settings-panel active" data-panel="account">
        <div class="panel-intro"><div class="panel-icon green"><i class="fas fa-user-cog"></i></div><div><h2>Personal account</h2><p>Keep your administrator profile and contact details current.</p></div></div>
        <form class="settings-form" method="POST" action="../update_settings.php" enctype="multipart/form-data">
          <input type="hidden" name="section" value="account">
          <div class="profile-upload"><div class="settings-avatar large"><?php if ($profileImageUrl): ?><img id="profilePreview" src="<?php echo $e($profileImageUrl); ?>" alt="Profile photo preview"><?php else: ?><span id="profileInitial"><?php echo $e(strtoupper(substr($user, 0, 1))); ?></span><?php endif; ?></div><div><h3>Profile photo</h3><p>JPG, PNG, or WEBP. Maximum 2 MB.</p><label class="button button-secondary" for="profileImage"><i class="fas fa-camera"></i> Choose photo</label><input class="visually-hidden" type="file" id="profileImage" name="profileImage" accept="image/jpeg,image/png,image/webp"></div></div>
          <div class="form-grid"><div class="field"><label for="fullName">Full name</label><input id="fullName" name="fullName" value="<?php echo $e($user); ?>" required></div><div class="field"><label for="email">Email address</label><input type="email" id="email" name="email" value="<?php echo $e($email); ?>" required></div><div class="field"><label for="phone">Phone number</label><input id="phone" name="phone" value="<?php echo $e($phone); ?>" required></div><div class="field"><label for="position">Position</label><input id="position" value="System Administrator" readonly></div></div>
          <div class="form-actions"><span class="form-note"><i class="fas fa-lock"></i> Your administrator details are private.</span><button class="button button-primary" type="submit"><i class="fas fa-save"></i> Save account</button></div>
        </form>
      </section>

      <section class="settings-panel" data-panel="barangay">
        <div class="panel-intro"><div class="panel-icon amber"><i class="fas fa-landmark"></i></div><div><h2>Barangay identity</h2><p>This information appears across public pages and official communications.</p></div></div>
        <form class="settings-form" method="POST" action="../update_settings.php" enctype="multipart/form-data"><input type="hidden" name="section" value="barangay"><div class="form-grid"><div class="field"><label for="barangayName">Barangay name</label><input id="barangayName" name="barangayName" value="<?php echo $e($barangay['name'] ?? 'Barangay San Jose'); ?>" required></div><div class="field"><label for="barangayContact">Contact number</label><input id="barangayContact" name="barangayContact" value="<?php echo $e($barangay['contact_number'] ?? ''); ?>"></div><div class="field full"><label for="barangayAddress">Address</label><input id="barangayAddress" name="barangayAddress" value="<?php echo $e($barangay['address'] ?? ''); ?>"></div><div class="field"><label for="officialEmail">Official email</label><input type="email" id="officialEmail" name="officialEmail" value="<?php echo $e($barangay['official_email'] ?? ''); ?>"></div><div class="field"><label for="websiteUrl">Website URL</label><input type="url" id="websiteUrl" name="websiteUrl" value="<?php echo $e($barangay['website_url'] ?? ''); ?>" placeholder="https://"></div><div class="field full"><label for="systemDescription">System description</label><textarea id="systemDescription" name="systemDescription" rows="5"><?php echo $e($barangay['description'] ?? ''); ?></textarea></div></div><div class="form-actions"><label class="button button-secondary" for="barangayLogo"><i class="fas fa-image"></i> Upload barangay logo</label><input class="visually-hidden" type="file" id="barangayLogo" name="barangayLogo" accept="image/jpeg,image/png,image/webp"><button class="button button-primary" type="submit"><i class="fas fa-save"></i> Save barangay information</button></div></form>
      </section>

      <section class="settings-panel" data-panel="security">
        <div class="panel-intro"><div class="panel-icon red"><i class="fas fa-shield-alt"></i></div><div><h2>Security and access</h2><p>Review account protection and recent administrator activity.</p></div></div>
        <div class="security-list"><div class="security-row"><div class="row-icon"><i class="fas fa-key"></i></div><div class="row-copy"><h3>Change password</h3><p>Email OTP verification is required before a password change.</p></div><button type="button" class="button button-secondary" id="requestOtp"><i class="fas fa-envelope"></i> Request email OTP</button></div><div class="security-row"><div class="row-icon"><i class="fas fa-mobile-alt"></i></div><div class="row-copy"><h3>Two-factor authentication</h3><p>Add an extra verification step to your administrator login.</p></div><label class="switch"><input type="checkbox" name="twoFactor" <?php echo !empty($admin['two_factor_enabled']) ? 'checked' : ''; ?>><span></span></label></div><div class="security-row"><div class="row-icon"><i class="fas fa-sign-out-alt"></i></div><div class="row-copy"><h3>Sign out all devices</h3><p>End every active administrator session except this one.</p></div><button type="button" class="button button-danger" id="logoutDevices"><i class="fas fa-power-off"></i> Sign out devices</button></div></div>
        <div class="log-heading"><div><h3>System and activity logs</h3><p>Review administrator actions and notification delivery records.</p></div><span class="log-count"><?php echo count($logs); ?> records</span></div>
        <div class="logs-table-wrap"><table class="logs-table"><thead><tr><th>Date</th><th>Type</th><th>Admin / User</th><th>Event</th><th>Recipient</th><th>Mobile</th><th>Email</th><th>SMS</th><th>Status</th></tr></thead><tbody>
          <?php if (!$logs): ?><tr><td colspan="9" class="empty-state">No log entries found.</td></tr><?php else: foreach ($logs as $log): ?>
            <?php
              $isNotification = $log['log_type'] === 'notification';
              $meta = '';
              if (!empty($log['event_meta'])) {
                  $decodedMeta = json_decode($log['event_meta'], true);
                  if (is_array($decodedMeta)) {
                      $metaParts = [];
                      foreach ($decodedMeta as $key => $value) $metaParts[] = $key . ': ' . (is_scalar($value) ? $value : json_encode($value));
                      $meta = implode(' | ', $metaParts);
                  } else $meta = $log['event_meta'];
              }
            ?>
            <tr><td><?php echo $e($log['created_at']); ?></td><td><span class="log-type <?php echo $isNotification ? 'notification' : 'admin'; ?>"><?php echo $isNotification ? 'Notification' : 'Admin action'; ?></span></td><td><?php echo $e($log['username'] ?? 'Admin'); ?></td><td><strong><?php echo $e($log['event_type'] ?? ''); ?></strong><?php if ($meta): ?><small><?php echo $e($meta); ?></small><?php endif; ?></td><td><?php echo $e($log['email'] ?? 'N/A'); ?></td><td><?php echo $e($log['mobile_number'] ?? 'N/A'); ?></td><td><?php echo $isNotification ? ($log['email_sent'] ? 'Yes' : 'No') : 'N/A'; ?></td><td><?php echo $isNotification ? ($log['sms_sent'] ? 'Yes' : 'No') : 'N/A'; ?></td><td><?php echo $e($log['status'] ?? ($isNotification ? 'unknown' : 'N/A')); ?></td></tr>
          <?php endforeach; endif; ?>
        </tbody></table></div>
      </section>

      <section class="settings-panel" data-panel="maintenance">
        <div class="panel-intro"><div class="panel-icon blue"><i class="fas fa-server"></i></div><div><h2>System maintenance</h2><p>Administrative tools for keeping SafeBrgy healthy and recoverable.</p></div></div>
        <div class="maintenance-grid"><div class="maintenance-card"><div class="row-icon"><i class="fas fa-tools"></i></div><h3>Maintenance mode</h3><p>Temporarily pause resident-facing services during system work.</p><button type="button" class="button button-secondary coming-soon"><i class="fas fa-wrench"></i> Enable maintenance mode</button></div><div class="maintenance-card"><div class="row-icon"><i class="fas fa-database"></i></div><h3>Database backup</h3><p>Create an export of your current SafeBrgy database.</p><button type="button" class="button button-secondary coming-soon"><i class="fas fa-download"></i> Back up database</button></div><div class="maintenance-card version-card"><span class="eyebrow">Installed release</span><strong>SafeBrgy v1.0.0</strong><span>Schema and application status</span><b><i class="fas fa-check-circle"></i> Up to date</b></div></div>
        <div class="system-log-link"><div><h3>Maintenance tools</h3><p>Backup and maintenance controls are available from this section.</p></div><button type="button" class="button button-primary" data-tab-target="security"><i class="fas fa-history"></i> View security logs</button></div>
      </section>
    </div>
  </main>
  
  <div class="maintenance-modal" id="maintenanceModal" role="dialog" aria-modal="true" aria-labelledby="maintenanceTitle">
    <div class="maintenance-modal-card"><button type="button" class="modal-close" id="closeMaintenanceModal" aria-label="Close"><i class="fas fa-times"></i></button><div class="modal-symbol"><i class="fas fa-tools"></i></div><h2 id="maintenanceTitle">Maintenance mode</h2><p>comming soon....</p><button type="button" class="button button-primary" id="closeMaintenanceModalButton">Understood</button></div>
  </div>

<!-- Shared JS -->
<script src="../../assets/js/shared/logo_functions.js"></script>
<script src="../../assets/js/shared/shared-header.js"></script>
<script src="../../assets/js/shared/shared-sidebar.js"></script>
<script src="../../assets/js/shared/layout_functions.js"></script>
<!-- Page-specific JS -->
<script src="../../assets/js/admin/account_settings.js"></script>
</body>
</html>
