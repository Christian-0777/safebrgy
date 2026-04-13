<?php
// admin_verification.php - SafeBrgy Admin User Verification
session_start();
$user = $_SESSION['user'] ?? "Juan Dela Cruz";

// Example users array
$users = [
  ["name" => "Maria Santos", "email" => "maria.santos@gmail.com", "phone" => "09123456789", "date" => "March 15, 2026", "address" => "Brgy. San Jose, San Luis, Pampanga"],
  ["name" => "Pedro Reyes", "email" => "pedro.reyes@gmail.com", "phone" => "09123456789", "date" => "March 15, 2026", "address" => "Brgy. San Jose, San Luis, Pampanga"],
  ["name" => "Ana Garcia", "email" => "ana.garcia@gmail.com", "phone" => "09123456789", "date" => "March 15, 2026", "address" => "Brgy. San Jose, San Luis, Pampanga"],
  ["name" => "Roberto Mendoza", "email" => "roberto.mendoza@gmail.com", "phone" => "09123456789", "date" => "March 15, 2026", "address" => "Brgy. San Jose, San Luis, Pampanga"]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SafeBrgy - User Verification</title>
  <link rel="icon" type="image/png" href="../assets/img/seal.png">
  <!-- Shared Styles -->
  <link rel="stylesheet" href="../assets/css/shared/shared-header.css">
  <link rel="stylesheet" href="../assets/css/shared/shared_sidebar.css">
  <link rel="stylesheet" href="../assets/css/shared/colors.css">
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="../assets/css/admin/user_verification.css">
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
      <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span class="menu-label">Logout</span></a>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Verify User Accounts</h2>
      <div class="d-flex align-items-center">
        <img src="assets/img/profile.png" alt="Profile" class="rounded-circle me-2" style="width:40px;height:40px;">
        <span class="fw-bold"><?php echo htmlspecialchars($user); ?></span>
      </div>
    </div>

    <!-- Users Table -->
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead class="table-dark">
          <tr>
            <th>User Information</th>
            <th>Register Date</th>
            <th>Address</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="usersTable">
          <?php foreach ($users as $u): ?>
            <tr>
              <td>
                <strong><?php echo htmlspecialchars($u['name']); ?></strong><br>
                <small><?php echo htmlspecialchars($u['email']); ?> | <?php echo htmlspecialchars($u['phone']); ?></small>
              </td>
              <td><?php echo htmlspecialchars($u['date']); ?></td>
              <td><?php echo htmlspecialchars($u['address']); ?></td>
              <td>
                <button class="btn btn-sm btn-success me-1">Approve</button>
                <button class="btn btn-sm btn-danger">Reject</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    </div>
  </main>

<!-- Shared JS -->
<script src="../assets/js/shared/shared-header.js"></script>
<script src="../assets/js/shared/shared-sidebar.js"></script>
<!-- Page-specific JS -->
<script src="../assets/js/admin/user_verification.js"></script>
</body>
</html>
