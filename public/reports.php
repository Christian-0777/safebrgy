<?php
// reports.php - SafeBrgy My Reports
session_start();
$user = $_SESSION['user'] ?? "Juan Dela Cruz";

// Example reports array
$reports = [
  ["type" => "Incident Report", "date" => "Mar 5, 2026", "status" => "Pending"],
  ["type" => "Lost Property", "date" => "Mar 5, 2026", "status" => "Pending"]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SafeBrgy - My Reports</title>
  <link rel="icon" type="image/png" href="../assets/img/seal.png">
  <!-- Shared Styles -->
  <link rel="stylesheet" href="../assets/css/shared/shared-header.css">
  <link rel="stylesheet" href="../assets/css/shared/shared_sidebar.css">
  <link rel="stylesheet" href="../assets/css/shared/colors.css">
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="../assets/css/public/reports.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

  <!-- HEADER -->
  <header class="header">
    <div class="header-left">
      <button class="sidebar-toggle"><i class="fas fa-bars"></i></button>
      <a href="../index.php" class="header-logo">
        <img src="../assets/img/seal.png" alt="SafeBrgy Logo" class="logo-image">
        <span>SafeBrgy</span>
      </a>
    </div>

    <div class="header-right">
      <div class="user-profile">
        <div class="profile-avatar"><?php echo substr($user, 0, 1); ?></div>
        <div class="profile-info">
          <div class="profile-name"><?php echo htmlspecialchars($user); ?></div>
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
    <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>My Reports</h2>
      <span class="fw-bold"><?php echo htmlspecialchars($user); ?></span>
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
            <th>Report Type</th>
            <th>Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="reportsTable">
          <?php foreach ($reports as $r): ?>
            <tr>
              <td><?php echo htmlspecialchars($r['type']); ?></td>
              <td><?php echo htmlspecialchars($r['date']); ?></td>
              <td><span class="badge bg-primary"><?php echo htmlspecialchars($r['status']); ?></span></td>
              <td><button class="btn btn-sm btn-outline-secondary">...</button></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <nav aria-label="Reports pagination">
      <ul class="pagination justify-content-center">
        <li class="page-item disabled"><a class="page-link">Previous</a></li>
        <li class="page-item active"><a class="page-link">1</a></li>
        <li class="page-item"><a class="page-link">2</a></li>
        <li class="page-item"><a class="page-link">Next</a></li>
      </ul>
    </nav>

    </div>
  </main>

<!-- Shared JS -->
<script src="../assets/js/shared/shared-header.js"></script>
<script src="../assets/js/shared/shared-sidebar.js"></script>
<!-- Page-specific JS -->
<script src="../assets/js/public/reports.js"></script>
</body>
</html>
