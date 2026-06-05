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

// ===== STATISTICS CARDS =====
// Total Residents (verified)
$totalResidents = $pdo->query('SELECT COUNT(*) FROM residents r JOIN users u ON r.user_id = u.id WHERE u.is_verified = 1')->fetchColumn();

// Total Registered Voters
$totalVoters = $pdo->query("SELECT COUNT(*) FROM residents WHERE voter_status = 'Yes'")->fetchColumn();

// Senior Citizens (60+)
$seniorCitizens = $pdo->query('SELECT COUNT(*) FROM residents WHERE age >= 60')->fetchColumn();

// Persons with Disabilities
$personsWithDisabilities = $pdo->query("SELECT COUNT(*) FROM residents WHERE disabilities IS NOT NULL AND disabilities != '' AND disabilities != '[]'")->fetchColumn();

// Pending Document Requests
$pendingRequests = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'Pending'")->fetchColumn();

// Pending Cases (Pending or Ongoing Reports)
$pendingCases = $pdo->query("SELECT COUNT(*) FROM reports WHERE status IN ('Pending', 'Ongoing')")->fetchColumn();

// ===== RESIDENTS STATISTICS =====
// Gender Distribution
$genderStats = $pdo->query('
    SELECT gender, COUNT(*) as count 
    FROM residents 
    WHERE gender IS NOT NULL 
    GROUP BY gender
')->fetchAll(PDO::FETCH_KEY_PAIR);

// Age Group Distribution
$ageGroupStats = $pdo->query('
    SELECT 
        CASE 
            WHEN age < 13 THEN "0-12"
            WHEN age BETWEEN 13 AND 19 THEN "13-19"
            WHEN age BETWEEN 20 AND 35 THEN "20-35"
            WHEN age BETWEEN 36 AND 50 THEN "36-50"
            WHEN age BETWEEN 51 AND 60 THEN "51-60"
            WHEN age > 60 THEN "60+"
            ELSE "Unknown"
        END as age_group,
        COUNT(*) as count
    FROM residents
    WHERE age IS NOT NULL
    GROUP BY age_group
    ORDER BY age_group
')->fetchAll(PDO::FETCH_KEY_PAIR);

// ===== RECENT ACTIVITIES =====
// Get recent admin logs and combined activities
$recentActivities = $pdo->query('
    SELECT 
        al.id,
        al.admin_id,
        u.username as actor,
        al.action,
        al.meta,
        al.created_at,
        "admin_log" as activity_type
    FROM admin_logs al
    LEFT JOIN users u ON al.admin_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 10
')->fetchAll();

// Supplement with recent requests and reports if needed
if (count($recentActivities) < 10) {
    $additionalActivities = $pdo->query('
        SELECT 
            r.id,
            u.id as actor_id,
            u.username as actor,
            CONCAT("submitted a new request: ", r.request_type) as action,
            NULL as meta,
            r.created_at,
            "request" as activity_type
        FROM requests r
        JOIN users u ON r.user_id = u.id
        ORDER BY r.created_at DESC
        LIMIT 5
    ')->fetchAll();
    $recentActivities = array_merge($recentActivities, $additionalActivities);
    usort($recentActivities, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
}

// ===== NOTIFICATIONS SUMMARY =====
// User Verification Pending
$verificationPending = $pdo->query('
    SELECT COUNT(*) FROM users WHERE is_verified = 0
')->fetchColumn();

// Pending Document Requests (Summary)
$pendingDocuments = [
    'total' => $pendingRequests,
    'by_type' => $pdo->query('
        SELECT request_type, COUNT(*) as count 
        FROM requests 
        WHERE status = "Pending" 
        GROUP BY request_type
    ')->fetchAll(PDO::FETCH_KEY_PAIR)
];

// Upcoming Expirations (Scheduled announcements in next 7 days)
$upcomingEvents = $pdo->query('
    SELECT COUNT(*) FROM announcements 
    WHERE status = "scheduled" 
    AND scheduled_at <= DATE_ADD(NOW(), INTERVAL 7 DAY)
    AND scheduled_at >= NOW()
')->fetchColumn();

// New Reports
$newReports = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'Pending'")->fetchColumn();

// ===== CALENDAR WIDGET - SCHEDULED EVENTS =====
$scheduledEvents = $pdo->query('
    SELECT 
        id,
        title,
        scheduled_at,
        priority,
        status
    FROM announcements 
    WHERE status IN ("scheduled", "active")
    ORDER BY scheduled_at ASC
    LIMIT 10
')->fetchAll();
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
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
          <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
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
    <div class="container-fluid p-4">
      <!-- Welcome Section -->
      <div class="mb-4">
        <h2 class="welcome-title">Welcome back, <?php echo htmlspecialchars($user); ?>!</h2>
        <p class="text-muted">Here's your administrative dashboard overview</p>
      </div>

      <!-- ===== STATISTICS CARDS ===== -->
      <h4 class="section-title mt-5 mb-3">Statistics Summary</h4>
      <div class="row mb-4">
        <div class="col-md-6 col-lg-4 mb-3">
          <div class="stat-card stat-card-primary">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-content">
              <div class="stat-value"><?php echo number_format($totalResidents); ?></div>
              <div class="stat-label">Total Residents</div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4 mb-3">
          <div class="stat-card stat-card-success">
            <div class="stat-icon"><i class="fas fa-vote-yea"></i></div>
            <div class="stat-content">
              <div class="stat-value"><?php echo number_format($totalVoters); ?></div>
              <div class="stat-label">Registered Voters</div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4 mb-3">
          <div class="stat-card stat-card-warning">
            <div class="stat-icon"><i class="fas fa-person-cane"></i></div>
            <div class="stat-content">
              <div class="stat-value"><?php echo number_format($seniorCitizens); ?></div>
              <div class="stat-label">Senior Citizens (60+)</div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4 mb-3">
          <div class="stat-card stat-card-danger">
            <div class="stat-icon"><i class="fas fa-wheelchair"></i></div>
            <div class="stat-content">
              <div class="stat-value"><?php echo number_format($personsWithDisabilities); ?></div>
              <div class="stat-label">Persons with Disabilities</div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4 mb-3">
          <div class="stat-card stat-card-info">
            <div class="stat-icon"><i class="fas fa-file-invoice"></i></div>
            <div class="stat-content">
              <div class="stat-value"><?php echo number_format($pendingRequests); ?></div>
              <div class="stat-label">Pending Document Requests</div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4 mb-3">
          <div class="stat-card stat-card-secondary">
            <div class="stat-icon"><i class="fas fa-exclamation-circle"></i></div>
            <div class="stat-content">
              <div class="stat-value"><?php echo number_format($pendingCases); ?></div>
              <div class="stat-label">Pending Cases</div>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== RESIDENTS STATISTICS ===== -->
      <div class="row mt-5 mb-4">
        <div class="col-lg-6 mb-4">
          <div class="card stats-card">
            <div class="card-header">
              <h5 class="card-title mb-0"><i class="fas fa-venus-mars"></i> Gender Distribution</h5>
            </div>
            <div class="card-body">
              <?php if (!empty($genderStats)): ?>
                <div class="chart-container">
                  <?php 
                    $totalGender = array_sum($genderStats);
                    foreach ($genderStats as $gender => $count):
                      $percentage = ($count / $totalGender) * 100;
                  ?>
                    <div class="chart-item mb-3">
                      <div class="d-flex justify-content-between mb-2">
                        <span class="chart-label">
                          <?php 
                            $icon = match($gender) {
                              'Male' => '<i class="fas fa-mars text-primary"></i>',
                              'Female' => '<i class="fas fa-venus text-danger"></i>',
                              default => '<i class="fas fa-user text-secondary"></i>'
                            };
                            echo $icon . ' ' . htmlspecialchars($gender);
                          ?>
                        </span>
                        <span class="chart-value"><?php echo number_format($count); ?> (<?php echo number_format($percentage, 1); ?>%)</span>
                      </div>
                      <div class="progress" style="height: 20px;">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo $percentage; ?>%;" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <p class="text-muted text-center py-3">No gender data available</p>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="col-lg-6 mb-4">
          <div class="card stats-card">
            <div class="card-header">
              <h5 class="card-title mb-0"><i class="fas fa-birthday-cake"></i> Population by Age Group</h5>
            </div>
            <div class="card-body">
              <?php if (!empty($ageGroupStats)): ?>
                <div class="chart-container">
                  <?php 
                    $totalAge = array_sum($ageGroupStats);
                    foreach ($ageGroupStats as $ageGroup => $count):
                      $percentage = ($count / $totalAge) * 100;
                  ?>
                    <div class="chart-item mb-3">
                      <div class="d-flex justify-content-between mb-2">
                        <span class="chart-label">Age <?php echo htmlspecialchars($ageGroup); ?></span>
                        <span class="chart-value"><?php echo number_format($count); ?> (<?php echo number_format($percentage, 1); ?>%)</span>
                      </div>
                      <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $percentage; ?>%;" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <p class="text-muted text-center py-3">No age group data available</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== RECENT ACTIVITIES ===== -->
      <h4 class="section-title mt-5 mb-3">Recent Activities</h4>
      <div class="card activity-card">
        <div class="card-body">
          <?php if (!empty($recentActivities)): ?>
            <div class="activity-list">
              <?php foreach (array_slice($recentActivities, 0, 10) as $activity): ?>
                <div class="activity-item">
                  <div class="activity-marker">
                    <div class="activity-dot"></div>
                  </div>
                  <div class="activity-content">
                    <div class="activity-header">
                      <span class="activity-actor">
                        <?php 
                          if ($activity['activity_type'] === 'admin_log') {
                            echo '<i class="fas fa-user-shield"></i> ' . htmlspecialchars($activity['actor'] ?? 'Admin');
                          } else {
                            echo '<i class="fas fa-user"></i> ' . htmlspecialchars($activity['actor'] ?? 'Resident');
                          }
                        ?>
                      </span>
                      <span class="activity-time">
                        <?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?>
                      </span>
                    </div>
                    <p class="activity-description mb-0">
                      <?php echo htmlspecialchars($activity['action']); ?>
                    </p>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="text-muted text-center py-4">
              <i class="fas fa-history fa-2x mb-3 d-block"></i>
              No recent activities
            </p>
          <?php endif; ?>
        </div>
      </div>

      <!-- ===== NOTIFICATIONS SUMMARY ===== -->
      <div class="row mt-5 mb-4">
        <div class="col-lg-6 mb-4">
          <div class="card notification-card">
            <div class="card-header">
              <h5 class="card-title mb-0"><i class="fas fa-bell"></i> Notifications Summary</h5>
            </div>
            <div class="card-body">
              <div class="notification-item mb-3">
                <div class="notification-icon bg-warning">
                  <i class="fas fa-user-check"></i>
                </div>
                <div class="notification-content">
                  <div class="notification-title">User Verification Pending</div>
                  <div class="notification-count"><?php echo number_format($verificationPending); ?> users</div>
                </div>
              </div>

              <div class="notification-item mb-3">
                <div class="notification-icon bg-info">
                  <i class="fas fa-file-alt"></i>
                </div>
                <div class="notification-content">
                  <div class="notification-title">Pending Document Requests</div>
                  <div class="notification-count"><?php echo number_format($pendingDocuments['total']); ?> requests</div>
                </div>
              </div>

              <div class="notification-item mb-3">
                <div class="notification-icon bg-primary">
                  <i class="fas fa-calendar-check"></i>
                </div>
                <div class="notification-content">
                  <div class="notification-title">Upcoming Events (Next 7 Days)</div>
                  <div class="notification-count"><?php echo number_format($upcomingEvents); ?> scheduled</div>
                </div>
              </div>

              <div class="notification-item">
                <div class="notification-icon bg-danger">
                  <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="notification-content">
                  <div class="notification-title">New Reports</div>
                  <div class="notification-count"><?php echo number_format($newReports); ?> pending</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pending Requests by Type -->
        <div class="col-lg-6 mb-4">
          <div class="card notification-card">
            <div class="card-header">
              <h5 class="card-title mb-0"><i class="fas fa-list-check"></i> Pending Requests by Type</h5>
            </div>
            <div class="card-body">
              <?php if (!empty($pendingDocuments['by_type']) && count($pendingDocuments['by_type']) > 0): ?>
                <div class="request-list">
                  <?php foreach ($pendingDocuments['by_type'] as $requestType => $count): ?>
                    <div class="request-item mb-3">
                      <div class="d-flex justify-content-between align-items-center">
                        <span class="request-type">
                          <i class="fas fa-file"></i> <?php echo htmlspecialchars($requestType); ?>
                        </span>
                        <span class="badge bg-warning text-dark"><?php echo number_format($count); ?></span>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <p class="text-muted text-center py-4">
                  <i class="fas fa-check-circle fa-2x mb-3 d-block text-success"></i>
                  All requests are being processed
                </p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== CALENDAR WIDGET - SCHEDULED EVENTS ===== -->
      <h4 class="section-title mt-5 mb-3">Scheduled Events & Announcements</h4>
      <div class="card calendar-card">
        <div class="card-body">
          <?php if (!empty($scheduledEvents)): ?>
            <div class="events-list">
              <?php foreach ($scheduledEvents as $event): ?>
                <div class="event-item">
                  <div class="event-date">
                    <span class="event-day"><?php echo date('d', strtotime($event['scheduled_at'])); ?></span>
                    <span class="event-month"><?php echo date('M', strtotime($event['scheduled_at'])); ?></span>
                  </div>
                  <div class="event-content">
                    <h6 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h6>
                    <div class="event-meta">
                      <span class="event-time">
                        <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($event['scheduled_at'])); ?>
                      </span>
                      <span class="badge badge-priority priority-<?php echo strtolower($event['priority']); ?>">
                        <?php echo ucfirst($event['priority']); ?>
                      </span>
                      <span class="badge bg-<?php echo $event['status'] === 'active' ? 'success' : 'info'; ?>">
                        <?php echo ucfirst($event['status']); ?>
                      </span>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="text-muted text-center py-4">
              <i class="fas fa-calendar fa-2x mb-3 d-block"></i>
              No scheduled events
            </p>
          <?php endif; ?>
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
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Page-specific scripts -->
  <script src="../../assets/js/admin/dashboard.js"></script>
</body>
</html>
