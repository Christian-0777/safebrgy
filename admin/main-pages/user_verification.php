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

// Fetch unverified users
$stmt = $pdo->prepare('
    SELECT u.id, u.username, u.email, u.phone, u.created_at, r.resident_id, r.first_name, r.last_name, r.complete_address
    FROM users u
    LEFT JOIN residents r ON u.id = r.user_id
    WHERE u.role = :role AND u.is_verified = 0
    ORDER BY u.created_at DESC
');
$stmt->execute(['role' => 'resident']);
$unverifiedUsers = $stmt->fetchAll();

// Fetch verified users
$stmt = $pdo->prepare('
    SELECT u.id, u.username, u.email, u.phone, u.created_at, u.updated_at, r.resident_id, r.first_name, r.last_name, r.complete_address
    FROM users u
    LEFT JOIN residents r ON u.id = r.user_id
    WHERE u.role = :role AND u.is_verified = 1
    ORDER BY u.updated_at DESC
');
$stmt->execute(['role' => 'resident']);
$verifiedUsers = $stmt->fetchAll();

// Count verification statistics
$totalVerified = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "resident" AND is_verified = 1')->fetchColumn();
$totalPending = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "resident" AND is_verified = 0')->fetchColumn();
$totalRejected = $pdo->query('SELECT COUNT(*) FROM admin_logs WHERE action = "reject_user"')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SafeBrgy - User Verification</title>
  <link rel="icon" type="image/png" href="../../assets/img/seal.png">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Shared Styles -->
  <link rel="stylesheet" href="../../assets/css/shared/shared-header.css">
  <link rel="stylesheet" href="../../assets/css/shared/shared_sidebar.css">
  <link rel="stylesheet" href="../../assets/css/shared/colors.css">
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="../../assets/css/admin/user_verification.css">
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
      <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span class="menu-label">Logout</span></a>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <div class="container-fluid p-4">
      <!-- Page Title -->
      <div class="mb-4">
        <h2 class="welcome-title">User Account Verification</h2>
        <p class="text-muted">Manage and verify resident account applications</p>
      </div>

      <!-- ===== STATISTICS CARDS ===== -->
      <h4 class="section-title mt-4 mb-3">Verification Summary</h4>
      <div class="row mb-5">
        <div class="col-md-6 col-lg-4 mb-3">
          <div class="stat-card stat-card-warning">
            <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
            <div class="stat-content">
              <div class="stat-value"><?php echo number_format($totalPending); ?></div>
              <div class="stat-label">Pending Verification</div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4 mb-3">
          <div class="stat-card stat-card-success">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-content">
              <div class="stat-value"><?php echo number_format($totalVerified); ?></div>
              <div class="stat-label">Verified Accounts</div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4 mb-3">
          <div class="stat-card stat-card-danger">
            <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
            <div class="stat-content">
              <div class="stat-value"><?php echo number_format($totalRejected); ?></div>
              <div class="stat-label">Rejected Accounts</div>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== USER VERIFICATION TABLES ===== -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="section-title mb-0">User Accounts</h4>
    </div>

    <!-- UNVERIFIED USERS SECTION -->
    <div class="mb-5">
      <h5 class="section-title mb-3" style="border-bottom-color: #ffc107; color: #ffc107;">Pending Verification</h5>
      <div class="table-responsive card">
        <table class="table table-striped align-middle mb-0">
          <thead class="table-dark">
            <tr>
              <th>Resident ID</th>
              <th>User Information</th>
              <th>Register Date</th>
              <th>Address</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="unverifiedTable">
            <?php if (count($unverifiedUsers) > 0): ?>
              <?php foreach ($unverifiedUsers as $u): ?>
                <tr>
                  <td>
                    <strong><?php echo htmlspecialchars($u['resident_id'] ?? 'N/A'); ?></strong>
                  </td>
                  <td>
                    <strong><?php echo htmlspecialchars(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '') ?: $u['username']); ?></strong><br>
                    <small><?php echo htmlspecialchars($u['email']); ?> | <?php echo htmlspecialchars($u['phone'] ?? 'N/A'); ?></small>
                  </td>
                  <td><?php echo htmlspecialchars(date('M d, Y', strtotime($u['created_at']))); ?></td>
                  <td><?php echo htmlspecialchars($u['complete_address'] ?? 'N/A'); ?></td>
                  <td>
                    <button class="btn btn-sm btn-info me-1" onclick="viewUser(<?php echo $u['id']; ?>)">View</button>
                    <button class="btn btn-sm btn-success me-1" onclick="verifyUser(<?php echo $u['id']; ?>)">Approve</button>
                    <button class="btn btn-sm btn-danger" onclick="rejectUser(<?php echo $u['id']; ?>)">Reject</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center text-muted py-4">
                  <i class="fas fa-check-circle fa-2x mb-2 d-block text-success"></i>
                  No pending verifications
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- VERIFIED USERS SECTION -->
    <div>
      <h5 class="section-title mb-3" style="border-bottom-color: #28a745; color: #28a745;">Verified Accounts</h5>
      <div class="table-responsive card">
        <table class="table table-striped align-middle mb-0">
          <thead class="table-dark">
            <tr>
              <th>Resident ID</th>
              <th>Name</th>
              <th>Date of Registration</th>
              <th>Date of Approval</th>
              <th>Address</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="verifiedTable">
            <?php if (count($verifiedUsers) > 0): ?>
              <?php foreach ($verifiedUsers as $u): ?>
                <tr>
                  <td>
                    <strong><?php echo htmlspecialchars($u['resident_id'] ?? 'N/A'); ?></strong>
                  </td>
                  <td>
                    <strong><?php echo htmlspecialchars(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '') ?: $u['username']); ?></strong>
                  </td>
                  <td><?php echo htmlspecialchars(date('M d, Y', strtotime($u['created_at']))); ?></td>
                  <td><?php echo htmlspecialchars(date('M d, Y', strtotime($u['updated_at']))); ?></td>
                  <td><?php echo htmlspecialchars($u['complete_address'] ?? 'N/A'); ?></td>
                  <td>
                    <button class="btn btn-sm btn-info" onclick="viewUser(<?php echo $u['id']; ?>)">View</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center text-muted py-4">
                  <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                  No verified accounts yet
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    </div>
  </main>

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewUserModalLabel">Resident Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="userDetails">
        <!-- User details will be loaded here -->
      </div>
    </div>
  </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="approveModalLabel">Account Approved</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
        <p class="mt-3 fw-bold" id="approvalMessage">Resident Approved Successfully</p>
        <p class="text-muted" id="approvalDate"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" onclick="closeAndRefresh()">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="rejectModalLabel">Reject Resident</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <label for="rejectReason" class="form-label">Reason for rejection:</label>
        <textarea class="form-control" id="rejectReason" rows="3" placeholder="Enter reason..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" onclick="confirmReject()">Send</button>
      </div>
    </div>
  </div>
</div>
<script>
function viewUser(userId) {
  fetch('view_user.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ user_id: userId })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      document.getElementById('userDetails').innerHTML = data.html;
      new bootstrap.Modal(document.getElementById('viewUserModal')).show();
    } else {
      alert('Error: ' + data.message);
    }
  });
}

function verifyUser(userId) {
  fetch('verify_user.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ user_id: userId, action: 'approve' })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      document.getElementById('approvalMessage').textContent = 'Resident Account Approved Successfully';
      document.getElementById('approvalDate').textContent = 'Approval Date: ' + new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
      new bootstrap.Modal(document.getElementById('approveModal')).show();
    } else {
      alert('Error: ' + data.message);
    }
  });
}

function closeAndRefresh() {
  location.reload();
}

function rejectUser(userId) {
  document.getElementById('rejectReason').value = '';
  window.currentRejectUserId = userId;
  new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function confirmReject() {
  const reason = document.getElementById('rejectReason').value.trim();
  if (!reason) {
    alert('Please enter a reason for rejection');
    return;
  }

  fetch('verify_user.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ 
      user_id: window.currentRejectUserId, 
      action: 'reject', 
      reason: reason 
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
      // Show rejection confirmation
      alert('User has been rejected and notified via email.');
      location.reload();
    } else {
      alert('Error: ' + data.message);
    }
  });
}
</script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Shared Scripts -->
<script src="../../assets/js/shared/shared-header.js"></script>
<script src="../../assets/js/shared/shared-sidebar.js"></script>
<!-- Page-specific scripts -->
<script src="../../assets/js/admin/user_verification.js"></script>
</body>
</html>
