<?php
require_once __DIR__ . '/../admin_protect.php';
// profile.php - SafeBrgy Admin Profile

$pdo = safeBrgy_db_connect();
$adminId = $_SESSION['admin_user']['id'] ?? null;

function adminAssetUrl($path) {
  $path = trim((string) $path);
  if ($path === '') {
    return '';
  }

  if (filter_var($path, FILTER_VALIDATE_URL)) {
    return $path;
  }

  $path = '/' . ltrim(str_replace('\\', '/', $path), '/');
  $applicationRoot = rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');

  if (strpos($path, $applicationRoot . '/') === 0) {
    return $path;
  }

  return $applicationRoot . $path;
}

if ($adminId) {
  $stmt = $pdo->prepare('SELECT username, email, phone, profile_image, cover_photo, created_at, updated_at FROM users WHERE id = :id');
    $stmt->execute(['id' => $adminId]);
    $admin = $stmt->fetch();
    $user = $admin['username'] ?? 'Admin';
    $email = $admin['email'] ?? '';
    $phone = $admin['phone'] ?? '';
    $profileImage = adminAssetUrl($admin['profile_image'] ?? '');
    $coverPhoto = adminAssetUrl($admin['cover_photo'] ?? '');
    $dateJoined = $admin['created_at'] ?? null;
    $lastUpdated = $admin['updated_at'] ?? null;
} else {
    $user = 'Admin';
    $email = '';
    $phone = '';
    $profileImage = null;
    $coverPhoto = null;
    $dateJoined = null;
    $lastUpdated = null;
}

// ===== ACCOUNT STATISTICS =====
// Total Residents Managed
$totalResidentsManaged = $pdo->query('SELECT COUNT(*) FROM residents r JOIN users u ON r.user_id = u.id WHERE u.is_verified = 1')->fetchColumn();

// Total Documents Processed (completed requests)
$totalDocumentsProcessed = $pdo->query("SELECT COUNT(*) FROM requests WHERE status IN ('Approved', 'Ready to Receive', 'Received')")->fetchColumn();

// Total Announcements Posted
$stmtAnnouncements = $pdo->prepare('SELECT COUNT(*) FROM announcements WHERE author_id = :adminId');
$stmtAnnouncements->execute(['adminId' => $adminId]);
$totalAnnouncementsPosted = $stmtAnnouncements->fetchColumn();

// Total Cases Handled (resolved reports)
$totalCasesHandled = $pdo->query("SELECT COUNT(*) FROM reports WHERE status IN ('Resolved', 'Dismissed')")->fetchColumn();

// ===== ACTIVITY LOG - Last Login =====
$stmtLastLogin = $pdo->prepare('
    SELECT created_at FROM admin_logs 
    WHERE admin_id = :adminId 
    ORDER BY created_at DESC 
    LIMIT 1
');
$stmtLastLogin->execute(['adminId' => $adminId]);
$lastLogin = $stmtLastLogin->fetch();
$lastLoginTime = ($lastLogin && isset($lastLogin['created_at'])) ? $lastLogin['created_at'] : $dateJoined;

// ===== ACTIVITY LOG TABLE =====
$stmtActivityLogs = $pdo->prepare('
    SELECT 
        id,
        admin_id,
        action,
        meta,
        created_at
    FROM admin_logs 
    WHERE admin_id = :adminId
    ORDER BY created_at DESC
    LIMIT 20
');
$stmtActivityLogs->execute(['adminId' => $adminId]);
$activityLogs = $stmtActivityLogs->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="/safebrgy/admin/main-pages/">
  <title>SafeBrgy - Admin Profile</title>
  <link rel="icon" type="image/png" href="../../assets/img/seal.png">
  <!-- Shared Styles -->
  <link rel="stylesheet" href="../../assets/css/shared/shared-header.css">
  <link rel="stylesheet" href="../../assets/css/shared/shared_sidebar.css">
  <link rel="stylesheet" href="../../assets/css/shared/colors.css">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="../../assets/css/admin/profile.css">
  <link rel="stylesheet" href="../../assets/css/shared/layout.css">
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
    <div class="container-fluid p-4">

      <!-- ===== ADMIN INFORMATION SECTION ===== -->
      <div class="admin-info-card mb-5">
        <div class="admin-cover-photo<?php echo $coverPhoto ? ' has-cover-photo' : ''; ?>"<?php echo $coverPhoto ? ' style="background-image: url(\'' . htmlspecialchars($coverPhoto, ENT_QUOTES, 'UTF-8') . '\');"' : ''; ?>></div>
        <div class="row">
          <!-- Profile Picture and Basic Info -->
          <div class="col-md-3 text-center">
            <div class="profile-picture-wrapper">
              <?php if ($profileImage): ?>
                <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile" class="profile-picture">
              <?php else: ?>
                <div class="profile-avatar-large">
                  <?php echo substr($user, 0, 1); ?>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Admin Details -->
          <div class="col-md-6">
            <h2 class="admin-name"><?php echo htmlspecialchars($user); ?></h2>
            <p class="admin-role"><i class="fas fa-badge-check"></i> System Administrator</p>
            
            <div class="admin-details-list">
              <div class="detail-item">
                <span class="detail-label"><i class="fas fa-envelope"></i> Email:</span>
                <span class="detail-value"><?php echo htmlspecialchars($email); ?></span>
              </div>
              
              <div class="detail-item">
                <span class="detail-label"><i class="fas fa-phone"></i> Contact Number:</span>
                <span class="detail-value"><?php echo htmlspecialchars($phone ?? 'Not set'); ?></span>
              </div>
              
              <div class="detail-item">
                <span class="detail-label"><i class="fas fa-calendar-alt"></i> Date Joined:</span>
                <span class="detail-value"><?php echo $dateJoined ? date('F d, Y', strtotime($dateJoined)) : 'N/A'; ?></span>
              </div>
              
              <div class="detail-item">
                <span class="detail-label"><i class="fas fa-sign-in-alt"></i> Last Login:</span>
                <span class="detail-value"><?php 
                  if ($lastLoginTime) {
                    echo date('F d, Y \a\t h:i A', strtotime($lastLoginTime));
                  } else {
                    echo 'N/A';
                  }
                ?></span>
              </div>
            </div>
          </div>

          <!-- Edit Profile Button -->
          <div class="col-md-3 d-flex align-items-center justify-content-end">
            <a href="account_settings.php" class="btn btn-primary btn-edit-profile">
              <i class="fas fa-edit"></i> Edit Profile
            </a>
          </div>
        </div>
      </div>

      <!-- ===== ACCOUNT STATISTICS SECTION ===== -->
      <h4 class="section-title mb-4"><i class="fas fa-chart-bar"></i> Account Statistics</h4>
      <div class="row mb-5">
        <div class="col-md-6 col-lg-3 mb-3">
          <div class="stat-card stat-card-residents">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-content">
              <div class="stat-value"><?php echo number_format($totalResidentsManaged); ?></div>
              <div class="stat-label">Total Residents Managed</div>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
          <div class="stat-card stat-card-documents">
            <div class="stat-icon"><i class="fas fa-file-check"></i></div>
            <div class="stat-content">
              <div class="stat-value"><?php echo number_format($totalDocumentsProcessed); ?></div>
              <div class="stat-label">Documents Processed</div>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
          <div class="stat-card stat-card-announcements">
            <div class="stat-icon"><i class="fas fa-bullhorn"></i></div>
            <div class="stat-content">
              <div class="stat-value"><?php echo number_format($totalAnnouncementsPosted); ?></div>
              <div class="stat-label">Announcements Posted</div>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
          <div class="stat-card stat-card-cases">
            <div class="stat-icon"><i class="fas fa-briefcase"></i></div>
            <div class="stat-content">
              <div class="stat-value"><?php echo number_format($totalCasesHandled); ?></div>
              <div class="stat-label">Cases Handled</div>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== ACTIVITY LOG SECTION ===== -->
      <h4 class="section-title mb-4"><i class="fas fa-history"></i> Activity Log</h4>
      <div class="activity-log-card">
        <?php if (!empty($activityLogs)): ?>
          <div class="table-responsive">
            <table class="table activity-table">
              <thead>
                <tr>
                  <th>Date & Time</th>
                  <th>Activity</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($activityLogs as $log): ?>
                  <tr>
                    <td class="log-date">
                      <i class="fas fa-calendar-alt"></i>
                      <?php echo date('M d, Y \a\t h:i A', strtotime($log['created_at'])); ?>
                    </td>
                    <td class="log-activity">
                      <div class="activity-item">
                        <span class="activity-text"><?php echo htmlspecialchars($log['action']); ?></span>
                        <?php if ($log['meta']): ?>
                          <small class="activity-meta"><?php 
                            $meta = json_decode($log['meta'], true);
                            if (is_array($meta) && !empty($meta)) {
                              echo '(' . htmlspecialchars(implode(', ', array_slice($meta, 0, 2))) . ')';
                            }
                          ?></small>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="no-activity">
            <i class="fas fa-inbox"></i>
            <p>No activity logs available</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- ===== ACTION BUTTONS ===== -->
      <div class="action-buttons mt-5 mb-4">
        <button id="downloadInfoBtn" class="btn btn-secondary btn-action">
          <i class="fas fa-download"></i> Download Information
        </button>
        <form id="logoutForm" method="POST" action="../../admin/logout.php" style="display: inline;">
          <button type="submit" class="btn btn-danger btn-action">
            <i class="fas fa-sign-out-alt"></i> Logout
          </button>
        </form>
      </div>

    </div>
  </main>

<!-- Shared JS -->
<script src="../../assets/js/shared/logo_functions.js"></script>
<script src="../../assets/js/shared/shared-header.js"></script>
<script src="../../assets/js/shared/shared-sidebar.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/shared/layout_functions.js"></script>
<!-- Page-specific JS -->
<script src="../../assets/js/admin/profile.js"></script>
</body>
</html>
