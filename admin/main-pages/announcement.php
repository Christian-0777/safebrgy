<?php
require_once __DIR__ . '/../admin_protect.php';
// announcements.php - SafeBrgy Announcements

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
    SELECT a.title, a.body, a.published_at, u.username as author
    FROM announcements a
    LEFT JOIN users u ON a.author_id = u.id
    ORDER BY a.published_at DESC
');
$stmt->execute();
$announcements = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SafeBrgy - Announcements</title>
  <link rel="icon" type="image/png" href="../../assets/img/seal.png">
  <!-- Shared Styles -->
  <link rel="stylesheet" href="../../assets/css/shared/shared-header.css">
  <link rel="stylesheet" href="../../assets/css/shared/shared_sidebar.css">
  <link rel="stylesheet" href="../../assets/css/shared/colors.css">
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="../../assets/css/admin/announcement.css">
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
      <h2>Announcements</h2>
      <span class="fw-bold"><?php echo htmlspecialchars($user); ?></span>
    </div>
    <p class="text-muted">Stay updated with the latest notices and updates from the municipality</p>

    <!-- Search and Filter -->
    <div class="row mb-4">
      <div class="col-md-6 mb-2">
        <input type="text" class="form-control" placeholder="Search announcements...">
      </div>
      <div class="col-md-6 mb-2">
        <input type="date" class="form-control">
      </div>
    </div>

    <!-- Announcement Cards -->
    <div class="row g-3">
      <?php foreach ($announcements as $a): ?>
        <div class="col-md-6">
          <div class="card shadow-sm h-100">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title"><?php echo htmlspecialchars($a['title']); ?></h5>
              <small class="text-muted mb-2"><?php echo htmlspecialchars(date('M d, Y', strtotime($a['published_at']))); ?> by <?php echo htmlspecialchars($a['author'] ?? 'Admin'); ?></small>
              <p class="card-text flex-grow-1"><?php echo htmlspecialchars(substr($a['body'], 0, 150)) . (strlen($a['body']) > 150 ? '...' : ''); ?></p>
              <a href="#" class="btn btn-outline-primary mt-auto">Read More</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    </div>
  </main>

<!-- Shared JS -->
<script src="../../assets/js/shared/shared-header.js"></script>
<script src="../../assets/js/shared/shared-sidebar.js"></script>
<!-- Page-specific JS -->
<script src="../../assets/js/admin/announcement.js"></script>
</body>
</html>
