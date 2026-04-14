<?php
require_once __DIR__ . '/../admin_protect.php';
// admin_reports.php - SafeBrgy Admin Reports

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

$stmt = $pdo->prepare('
    SELECT r.id, r.title, r.created_at, r.status, u.username
    FROM reports r
    LEFT JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
');
$stmt->execute();
$reports = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SafeBrgy - Admin Reports</title>
  <link rel="icon" type="image/png" href="../../assets/img/seal.png">
  <!-- Shared Styles -->
  <link rel="stylesheet" href="../../assets/css/shared/shared-header.css">
  <link rel="stylesheet" href="../../assets/css/shared/shared_sidebar.css">
  <link rel="stylesheet" href="../../assets/css/shared/colors.css">
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="../../assets/css/admin/reports.css">
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
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Incident Reports</h2>
      <div class="d-flex align-items-center">
        <img src="../../assets/img/profile.png" alt="Profile" class="rounded-circle me-2" style="width:40px;height:40px;">
        <span class="fw-bold"><?php echo htmlspecialchars($user); ?></span>
      </div>
    </div>

    <!-- Search -->
    <div class="mb-3">
      <input type="text" id="searchReports" class="form-control" placeholder="Search reports...">
    </div>

    <!-- Reports Table -->
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead class="table-dark">
          <tr>
            <th>Title</th>
            <th>Date Reported</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="reportsTable">
          <?php foreach ($reports as $r): ?>
            <tr>
              <td><?php echo htmlspecialchars($r['title']); ?></td>
              <td><?php echo htmlspecialchars(date('M d, Y, g:i A', strtotime($r['created_at']))); ?></td>
              <td><?php echo htmlspecialchars($r['status']); ?></td>
              <td><?php echo htmlspecialchars($r['username'] ?? 'Anonymous'); ?></td>
              <td><a href="view_report.php?id=<?php echo $r['id'] ?? 0; ?>" class="btn btn-sm btn-outline-info">View</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    </div>
  </main>

<!-- Shared JS -->
<script src="../../assets/js/shared/shared-header.js"></script>
<script src="../../assets/js/shared/shared-sidebar.js"></script>
<!-- Page-specific JS -->
<script src="../../assets/js/admin/reports.js"></script>
</body>
</html>
