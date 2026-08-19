<?php
require_once __DIR__ . '/../../config/db.php';
// dashboard.php - SafeBrgy Dashboard
session_start();

// Check if user is logged in and verified
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'resident') {
    header('Location: ../login.php');
    exit;
}

$pdo = safeBrgy_db_connect();
$user = $_SESSION['user'];
$userId = $user['id'] ?? null;
$residentEmail = $user['email'] ?? '';
$name = $user['name'] ?? 'Resident';
$email = $user['email'] ?? '';
$phone = $user['phone'] ?? '';

// Determine if user is new or existing (created within last 7 days)
$isNewUser = isset($user['created_at']) && (time() - strtotime($user['created_at'])) < (7 * 24 * 60 * 60);

// Get statistics for status tracker
$stats = [
    'pending_requests' => 0,
    'approved_requests' => 0,
    'total_reports' => 0,
    'notifications' => 0
];

if ($residentEmail !== '') {
    // Count pending requests
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM requests WHERE resident_email = ? AND status = "Pending"');
    $stmt->execute([$residentEmail]);
    $stats['pending_requests'] = $stmt->fetch()['count'] ?? 0;

    // Count approved requests
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM requests WHERE resident_email = ? AND status IN ("Approved", "Ready for Pickup", "Received", "Ready to Receive")');
    $stmt->execute([$residentEmail]);
    $stats['approved_requests'] = $stmt->fetch()['count'] ?? 0;

    // Count total reports
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM reports WHERE user_id = ?');
    $stmt->execute([$userId]);
    $stats['total_reports'] = $stmt->fetch()['count'] ?? 0;

    // Count unread notifications (latest requests within the last 7 days)
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM requests WHERE resident_email = ? AND status != "Rejected" AND updated_at > DATE_SUB(NOW(), INTERVAL 7 DAY)');
    $stmt->execute([$residentEmail]);
    $stats['notifications'] = $stmt->fetch()['count'] ?? 0;
}

// Get recent announcements (active only, limit to 5)
$announcements = [];
$stmt = $pdo->prepare('
    SELECT id, title, published_at 
    FROM announcements 
    WHERE status = "active" AND archived = 0 
    ORDER BY pinned DESC, published_at DESC 
    LIMIT 5
');
$stmt->execute();
$announcements = $stmt->fetchAll();

// Get recent request updates for notifications summary
$recentUpdates = [];
if ($residentEmail !== '') {
    $stmt = $pdo->prepare('
        SELECT document_type, status, COUNT(*) as count 
        FROM requests 
        WHERE resident_email = ? AND updated_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY document_type, status
        LIMIT 3
    ');
    $stmt->execute([$residentEmail]);
    $recentUpdates = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="/safebrgy/public/public-pages/">
  <title>SafeBrgy - Dashboard</title>
  <link rel="icon" type="image/png" href="../../assets/img/seal.png">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Shared Styles -->
  <link rel="stylesheet" href="../../assets/css/shared/shared-header.css">
  <link rel="stylesheet" href="../../assets/css/shared/shared_sidebar.css">
  <link rel="stylesheet" href="../../assets/css/shared/colors.css">
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="../../assets/css/public/dashboard.css">
  <link rel="stylesheet" href="../../assets/css/shared/layout.css">
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
        <div class="profile-avatar"><?php echo substr($name, 0, 1); ?></div>
        <div class="profile-info">
          <div class="profile-name"><?php echo htmlspecialchars($name); ?></div>
          <div class="profile-role">Resident</div>
        </div>
        <div class="profile-dropdown">
          <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
          <a href="account.php"><i class="fas fa-cog"></i> Settings</a>
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
      <li><a href="reports.php"><i class="fas fa-file-alt"></i> <span class="menu-label">My Reports</span></a></li>
      <li><a href="requests.php"><i class="fas fa-clipboard-list"></i> <span class="menu-label">My Requests</span></a></li>
    </ul>
    
    <div class="sidebar-footer">
      <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span class="menu-label">Logout</span></a>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <div class="container-fluid p-4">

      <!-- Welcome Section -->
      <div class="welcome-section mb-4">
        <div class="welcome-header">
          <div>
            <h1 class="welcome-message">
              <?php 
                if ($isNewUser) {
                    echo "Welcome to SafeBrgy, " . htmlspecialchars($name) . "! 🎉";
                } else {
                    echo "Welcome back, " . htmlspecialchars($name) . "!";
                }
              ?>
            </h1>
            <p class="welcome-subtitle">
              <i class="fas fa-calendar-alt"></i> 
              <span id="current-date-time"></span>
            </p>
            <?php if ($isNewUser): ?>
              <p class="new-user-tip">
                <i class="fas fa-lightbulb"></i> Get started by exploring our services below.
              </p>
            <?php endif; ?>
          </div>
          <div class="welcome-icon">
            <i class="fas fa-smile"></i>
          </div>
        </div>
      </div>

      <!-- Status Tracker Section -->
      <div class="status-tracker-section mb-4">
        <h3 class="section-title">Your Activity Overview</h3>
        <div class="row g-3">
          <!-- Pending Requests Card -->
          <div class="col-md-3 col-sm-6">
            <div class="tracker-card pending">
              <div class="tracker-icon">
                <i class="fas fa-hourglass-half"></i>
              </div>
              <div class="tracker-content">
                <div class="tracker-value"><?php echo $stats['pending_requests']; ?></div>
                <div class="tracker-label">Pending Requests</div>
              </div>
              <a href="requests.php" class="tracker-link">View Details →</a>
            </div>
          </div>

          <!-- Approved Requests Card -->
          <div class="col-md-3 col-sm-6">
            <div class="tracker-card approved">
              <div class="tracker-icon">
                <i class="fas fa-check-circle"></i>
              </div>
              <div class="tracker-content">
                <div class="tracker-value"><?php echo $stats['approved_requests']; ?></div>
                <div class="tracker-label">Approved Documents</div>
              </div>
              <a href="requests.php" class="tracker-link">View Details →</a>
            </div>
          </div>

          <!-- Total Reports Card -->
          <div class="col-md-3 col-sm-6">
            <div class="tracker-card reports">
              <div class="tracker-icon">
                <i class="fas fa-file-alt"></i>
              </div>
              <div class="tracker-content">
                <div class="tracker-value"><?php echo $stats['total_reports']; ?></div>
                <div class="tracker-label">Your Reports</div>
              </div>
              <a href="reports.php" class="tracker-link">View Details →</a>
            </div>
          </div>

          <!-- Notifications Summary Card -->
          <div class="col-md-3 col-sm-6">
            <div class="tracker-card notifications">
              <div class="tracker-icon">
                <i class="fas fa-bell"></i>
              </div>
              <div class="tracker-content">
                <div class="tracker-value"><?php echo $stats['notifications']; ?></div>
                <div class="tracker-label">Recent Updates</div>
              </div>
              <a href="notifications.php" class="tracker-link">View Details →</a>
            </div>
          </div>
        </div>

        <!-- Notification Summary Details -->
        <?php if (!empty($recentUpdates)): ?>
          <div class="notification-summary mt-3">
            <div class="summary-title">Latest Updates</div>
            <ul class="summary-list">
              <?php foreach ($recentUpdates as $update): ?>
                <li class="summary-item">
                  <span class="update-type"><?php echo htmlspecialchars($update['document_type']); ?></span>
                  <span class="update-status badge bg-<?php 
                    echo match($update['status']) {
                      'Pending' => 'warning',
                      'Approved' => 'success',
                      'Processing' => 'info',
                      'Ready to Receive' => 'success',
                      'Received' => 'dark',
                      'Rejected' => 'danger',
                      default => 'secondary'
                    };
                  ?>">
                    <?php echo htmlspecialchars($update['status']); ?>
                  </span>
                  <span class="update-count"><?php echo $update['count']; ?> request<?php echo $update['count'] > 1 ? 's' : ''; ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
      </div>

      <!-- Services Section -->
      <div class="services-section mb-4">
        <h3 class="section-title">Available Services</h3>
        <div class="row g-3">
          <?php
          $services = [
            [
              "title" => "Barangay Clearance",
              "description" => "Proof of no bad record in the barangay, often needed for jobs or permits.",
              "icon" => "fa-certificate"
            ],
            [
              "title" => "Barangay Residency",
              "description" => "Confirms that a person is a resident of the barangay.",
              "icon" => "fa-home"
            ],
            [
              "title" => "Barangay Indigency",
              "description" => "Issued to low income individuals for aid, scholarships, or subsidies.",
              "icon" => "fa-hand-holding-heart"
            ],
            [
              "title" => "Barangay Business Clearance",
              "description" => "Permission for a business to operate within the barangay.",
              "icon" => "fa-briefcase"
            ],
            [
              "title" => "Incident Report",
              "description" => "Record of complaints or incidents filed at the barangay.",
              "icon" => "fa-exclamation-triangle"
            ],
            [
              "title" => "Lost Property",
              "description" => "Assistance for residents who lost items, with issuance of Lost & Found document.",
              "icon" => "fa-search"
            ]
          ];
          
          foreach ($services as $service) {
            echo '<div class="col-lg-4 col-md-6">
                    <div class="service-card">
                      <div class="service-icon">
                        <i class="fas ' . htmlspecialchars($service['icon']) . '"></i>
                      </div>
                      <h5 class="service-title">' . htmlspecialchars($service['title']) . '</h5>
                      <p class="service-description">' . htmlspecialchars($service['description']) . '</p>
                      <a href="requests.php?service=' . urlencode($service['title']) . '" class="btn btn-request-now">
                        <i class="fas fa-plus-circle"></i> Request Now
                      </a>
                    </div>
                  </div>';
          }
          ?>
        </div>
      </div>

      <!-- Announcements Section -->
      <div class="announcements-section">
        <div class="announcements-header">
          <h3 class="section-title">Latest Announcements</h3>
          <a href="announcement.php" class="see-all-link">See All →</a>
        </div>
        
        <?php if (!empty($announcements)): ?>
          <div class="announcements-list">
            <?php foreach ($announcements as $announcement): ?>
              <div class="announcement-item">
                <div class="announcement-dot"></div>
                <div class="announcement-content">
                  <h6 class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></h6>
                  <small class="announcement-date">
                    <i class="fas fa-calendar"></i> 
                    <?php echo date('M d, Y', strtotime($announcement['published_at'])); ?>
                  </small>
                </div>
                <a href="announcement.php#announcement-<?php echo $announcement['id']; ?>" class="announcement-link">
                  <i class="fas fa-arrow-right"></i>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="announcements-empty">
            <i class="fas fa-info-circle"></i>
            <p>No announcements at this time.</p>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </main>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/shared/layout_functions.js"></script>
<!-- Shared JS -->
<script src="../../assets/js/shared/logo_functions.js"></script>
<script src="../../assets/js/shared/shared-header.js"></script>
<script src="../../assets/js/shared/shared-sidebar.js"></script>
<!-- Page-specific JS -->
<script src="../../assets/js/public/dashboard.js"></script>
</body>
</html>
