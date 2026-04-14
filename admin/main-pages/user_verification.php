<?php
require_once __DIR__ . '/../admin_protect.php';
// admin_verification.php - SafeBrgy Admin User Verification

$pdo = safeBrgy_db_connect();
$adminId = $_SESSION['admin_user']['id'] ?? null;

if ($adminId) {
    $stmt = $pdo->prepare('SELECT username, email FROM users WHERE id = :id');
    $stmt->execute(['id' => $adminId]);
    $admin = $stmt->fetch();
    $user = $admin['username'] ?? 'Admin';
    $email = $admin['email'] ?? '';
} else {
    $user = 'Admin';
    $email = '';
}

$stmt = $pdo->prepare('
    SELECT u.id, u.username, u.email, u.phone, u.created_at, r.first_name, r.last_name, r.complete_address
    FROM users u
    LEFT JOIN residents r ON u.id = r.user_id
    WHERE u.role = :role AND u.is_verified = 0
    ORDER BY u.created_at DESC
');
$stmt->execute(['role' => 'resident']);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SafeBrgy - User Verification</title>
  <link rel="icon" type="image/png" href="../../assets/img/seal.png">
  <!-- Shared Styles -->
  <link rel="stylesheet" href="../../assets/css/shared/shared-header.css">
  <link rel="stylesheet" href="../../assets/css/shared/shared_sidebar.css">
  <link rel="stylesheet" href="../../assets/css/shared/colors.css">
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="../../assets/css/admin/user_verification.css">
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
      <h2>Verify User Accounts</h2>
      <div class="d-flex align-items-center">
        <img src="../../assets/img/profile.png" alt="Profile" class="rounded-circle me-2" style="width:40px;height:40px;">
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
                <strong><?php echo htmlspecialchars(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '') ?: $u['username']); ?></strong><br>
                <small><?php echo htmlspecialchars($u['email']); ?> | <?php echo htmlspecialchars($u['phone'] ?? 'N/A'); ?></small>
              </td>
              <td><?php echo htmlspecialchars(date('M d, Y', strtotime($u['created_at']))); ?></td>
              <td><?php echo htmlspecialchars($u['complete_address'] ?? 'N/A'); ?></td>
              <td>
                <button class="btn btn-sm btn-success me-1" onclick="verifyUser(<?php echo $u['id']; ?>)">Approve</button>
                <button class="btn btn-sm btn-danger" onclick="rejectUser(<?php echo $u['id']; ?>)">Reject</button>
              </td>
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
<script src="../../assets/js/admin/user_verification.js"></script>
<script>
function verifyUser(userId) {
  if (confirm('Are you sure you want to approve this user?')) {
    fetch('verify_user.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: userId, action: 'approve' })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        location.reload();
      } else {
        alert('Error: ' + data.message);
      }
    });
  }
}

function rejectUser(userId) {
  if (confirm('Are you sure you want to reject this user?')) {
    fetch('verify_user.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: userId, action: 'reject' })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        location.reload();
      } else {
        alert('Error: ' + data.message);
      }
    });
  }
}
</script>
</body>
</html>
