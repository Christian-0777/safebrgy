<?php
require_once __DIR__ . '/../admin_protect.php';
require_once __DIR__ . '/../../config/mailer.php';
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

// Handle AJAX requests for reports
$response = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    
    if ($action === 'get_report') {
        $reportId = $_POST['id'] ?? 0;
        
        $stmt = $pdo->prepare('
            SELECT r.*, u.username, u.email, res.resident_id, res.first_name, res.last_name
            FROM reports r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN residents res ON u.id = res.user_id
            WHERE r.id = ?
        ');
        $stmt->execute([$reportId]);
        $report = $stmt->fetch();
        
        echo json_encode(['success' => true, 'report' => $report]);
        exit;
    } elseif ($action === 'update_status') {
        $reportId = $_POST['id'] ?? 0;
        $newStatus = $_POST['status'] ?? '';
        
        $stmt = $pdo->prepare('UPDATE reports SET status = ?, updated_at = NOW() WHERE id = ?');
        $result = $stmt->execute([$newStatus, $reportId]);

        if ($result) {
            $reportStmt = $pdo->prepare('SELECT r.case_number, u.email, res.first_name, res.last_name FROM reports r LEFT JOIN users u ON r.user_id = u.id LEFT JOIN residents res ON u.id = res.user_id WHERE r.id = ?');
            $reportStmt->execute([$reportId]);
            $reportDetails = $reportStmt->fetch();

            if ($reportDetails && !empty($reportDetails['email'])) {
                $residentName = trim(($reportDetails['first_name'] ?? '') . ' ' . ($reportDetails['last_name'] ?? '')) ?: 'Resident';
                $caseNumber = $reportDetails['case_number'] ?: $reportId;
                sendReportStatusEmail($reportDetails['email'], $residentName, $caseNumber, $newStatus);
            }
        }
        
        echo json_encode(['success' => $result]);
        exit;
    }
}

// Get filter and search parameters
$search = $_GET['search'] ?? '';

// Build query for reports
$query = '
    SELECT r.id, r.case_number, r.title, r.description, r.report_type, r.location, r.status, r.created_at, r.updated_at, u.username, u.email, res.resident_id, res.first_name, res.last_name
    FROM reports r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN residents res ON u.id = res.user_id
    WHERE 1=1
';

$params = [];

if ($search) {
    $query .= ' AND (r.case_number LIKE ? OR u.username LIKE ? OR res.first_name LIKE ? OR res.last_name LIKE ?)';
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$query .= ' ORDER BY r.created_at DESC';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$reports = $stmt->fetchAll();

// Get statistics
$statsQuery = '
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = "Ongoing" THEN 1 ELSE 0 END) as ongoing,
        SUM(CASE WHEN status = "Resolved" THEN 1 ELSE 0 END) as resolved,
        SUM(CASE WHEN status = "Dismissed" THEN 1 ELSE 0 END) as dismissed
    FROM reports
';
$statsStmt = $pdo->prepare($statsQuery);
$statsStmt->execute();
$stats = $statsStmt->fetch();
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
  <link rel="stylesheet" href="../../assets/css/shared/layout.css">
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="../../assets/css/admin/reports.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
      <!-- Page Header -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="mb-2">Incident Reports</h2>
          <p class="text-muted">Manage and track incident reports, lost property cases, and blotter entries</p>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="stat-card">
            <div class="stat-value"><?php echo $stats['total'] ?? 0; ?></div>
            <div class="stat-label">Total Reports</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card stat-card-pending">
            <div class="stat-value"><?php echo $stats['pending'] ?? 0; ?></div>
            <div class="stat-label">Pending</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card stat-card-ongoing">
            <div class="stat-value"><?php echo $stats['ongoing'] ?? 0; ?></div>
            <div class="stat-label">Ongoing</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card stat-card-resolved">
            <div class="stat-value"><?php echo $stats['resolved'] ?? 0; ?></div>
            <div class="stat-label">Resolved</div>
          </div>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-md-6 offset-md-6">
          <div class="stat-card stat-card-dismissed">
            <div class="stat-value"><?php echo $stats['dismissed'] ?? 0; ?></div>
            <div class="stat-label">Dismissed</div>
          </div>
        </div>
      </div>

      <!-- Search Bar -->
      <div class="card mb-4">
        <div class="card-body">
          <form method="get" class="row g-3">
            <div class="col-md-12">
              <label class="form-label">Search by Name or Case Number</label>
              <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Enter name or case number..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-outline-primary">
                  <i class="fas fa-search"></i> Search
                </button>
                <a href="reports.php" class="btn btn-outline-secondary">
                  <i class="fas fa-redo"></i> Reset
                </a>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Reports Table -->
      <div class="card">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th>Case #</th>
                <th>Title</th>
                <th>Date Filed</th>
                <th>Type</th>
                <th>Status</th>
                <th>Reporter</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="reportsTable">
              <?php if (empty($reports)): ?>
                <tr>
                  <td colspan="7" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                    No reports found
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($reports as $r): ?>
                  <tr>
                    <td>
                      <span class="badge bg-secondary"><?php echo htmlspecialchars($r['case_number'] ?? 'N/A'); ?></span>
                    </td>
                    <td>
                      <strong><?php echo htmlspecialchars($r['title'] ?? 'Untitled'); ?></strong>
                    </td>
                    <td>
                      <small><?php echo htmlspecialchars(date('M d, Y g:i A', strtotime($r['created_at']))); ?></small>
                    </td>
                    <td>
                      <span class="badge bg-info"><?php echo htmlspecialchars($r['report_type'] ?? 'Unknown'); ?></span>
                    </td>
                    <td>
                      <?php 
                        $statusClass = 'secondary';
                        if ($r['status'] === 'Pending') $statusClass = 'warning';
                        else if ($r['status'] === 'Ongoing') $statusClass = 'primary';
                        else if ($r['status'] === 'Resolved') $statusClass = 'success';
                        else if ($r['status'] === 'Dismissed') $statusClass = 'danger';
                      ?>
                      <span class="badge bg-<?php echo $statusClass; ?>"><?php echo htmlspecialchars($r['status']); ?></span>
                    </td>
                    <td>
                      <?php 
                        $reporterName = $r['username'] ?? 'Anonymous';
                        if ($r['first_name'] && $r['last_name']) {
                          $reporterName = htmlspecialchars($r['first_name'] . ' ' . $r['last_name']);
                        }
                        echo $reporterName;
                      ?>
                    </td>
                    <td>
                      <button class="btn btn-sm btn-outline-info view-btn" data-id="<?php echo $r['id']; ?>" data-bs-toggle="modal" data-bs-target="#reportDetailsModal">
                        <i class="fas fa-eye"></i> View
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>

  <!-- Report Details Modal -->
  <div class="modal fade" id="reportDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Report Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="modalBody">
          <div class="text-center">
            <div class="spinner-border" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="applyStatusBtn">Apply Changes</button>
        </div>
      </div>
    </div>
  </div>

<!-- Shared JS -->
<script src="../../assets/js/shared/shared-header.js"></script>
<script src="../../assets/js/shared/shared-sidebar.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Page-specific JS -->
<script src="../../assets/js/admin/reports.js"></script>
</body>
</html>
