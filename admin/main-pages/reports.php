<?php
require_once __DIR__ . '/../admin_protect.php';
require_once __DIR__ . '/../../includes/shared/profile_avatar.php';
require_once __DIR__ . '/../../config/mailer.php';
// admin_reports.php - SafeBrgy Admin Reports

$pdo = safeBrgy_db_connect();
$adminId = $_SESSION['admin_user']['id'] ?? null;

if ($adminId) {
    $stmt = $pdo->prepare('SELECT username, email FROM users WHERE id = :id');
    $stmt->execute(['id' => $adminId]);
    $admin = $stmt->fetch();
    $user = adminDisplayName($admin['username'] ?? 'Admin');
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
      $source = $_POST['source'] ?? 'resident';
        
      if ($source === 'guest') {
        $stmt = $pdo->prepare('
          SELECT g.*, "guest" AS source, NULL AS username, NULL AS email, NULL AS resident_id,
               g.guest_aka AS reporter_name, g.contact_email AS guest_email,
               g.contact_mobile AS guest_mobile
          FROM guest_reports g
          WHERE g.id = ?
        ');
      } else {
        $stmt = $pdo->prepare('
        SELECT r.*, "resident" AS source, u.username, u.email, res.resident_id, res.first_name, res.last_name
            FROM reports r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN residents res ON u.id = res.user_id
            WHERE r.id = ?
        ');
      }
        $stmt->execute([$reportId]);
        $report = $stmt->fetch();

        if ($report) {
          $report['attachments'] = !empty($report['attachments'])
            ? (json_decode($report['attachments'], true) ?: [])
            : [];
        }
        
        echo json_encode(['success' => true, 'report' => $report]);
        exit;
      } elseif ($action === 'search_case') {
        $caseNumber = trim($_POST['case_number'] ?? '');
        $stmt = $pdo->prepare('
          SELECT * FROM (
            SELECT r.id, "resident" AS source, r.case_number, r.title, r.description,
                 r.report_type, r.location, r.status, r.attachments, r.created_at,
                 r.updated_at, u.username, u.email, res.resident_id,
                 res.first_name, res.last_name, NULL AS reporter_name,
                 NULL AS guest_email, NULL AS guest_mobile
            FROM reports r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN residents res ON u.id = res.user_id
            UNION ALL
            SELECT g.id, "guest" AS source, g.case_number, g.title, g.description,
                 g.report_type, g.location, g.status, g.attachments, g.created_at,
                 g.updated_at, NULL, NULL, NULL, NULL, NULL, g.guest_aka,
                 g.contact_email, g.contact_mobile
            FROM guest_reports g
          ) AS combined
          WHERE case_number = ?
          LIMIT 1
        ');
        $stmt->execute([$caseNumber]);
        $report = $stmt->fetch();
        if ($report) {
          $report['attachments'] = !empty($report['attachments'])
            ? (json_decode($report['attachments'], true) ?: [])
            : [];
        }
        echo json_encode($report
          ? ['success' => true, 'report' => $report]
          : ['success' => false, 'message' => 'No report found for that case number.']);
        exit;
    } elseif ($action === 'update_status') {
        $reportId = $_POST['id'] ?? 0;
        $newStatus = $_POST['status'] ?? '';
        $source = $_POST['source'] ?? 'resident';
        
        $table = $source === 'guest' ? 'guest_reports' : 'reports';
        $stmt = $pdo->prepare("UPDATE {$table} SET status = ?, updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$newStatus, $reportId]);

        if ($result) {
          if ($source === 'guest') {
            $reportStmt = $pdo->prepare('SELECT case_number, guest_aka, contact_email AS email, contact_mobile AS mobile_number FROM guest_reports WHERE id = ?');
          } else {
            $reportStmt = $pdo->prepare('SELECT r.case_number, u.id AS user_id, u.email, res.first_name, res.last_name, res.mobile_number FROM reports r LEFT JOIN users u ON r.user_id = u.id LEFT JOIN residents res ON u.id = res.user_id WHERE r.id = ?');
          }
            $reportStmt->execute([$reportId]);
            $reportDetails = $reportStmt->fetch();

          if ($reportDetails && ($source === 'guest' || !empty($reportDetails['email']))) {
                $residentName = trim(($reportDetails['first_name'] ?? '') . ' ' . ($reportDetails['last_name'] ?? '')) ?: ($reportDetails['username'] ?? 'Resident');
            $residentName = $source === 'guest' ? ($reportDetails['guest_aka'] ?: 'Guest') : $residentName;
                $caseNumber = $reportDetails['case_number'] ?: $reportId;
                $mobileNumber = $reportDetails['mobile_number'] ?? null;
                $userId = !empty($reportDetails['user_id']) ? (int) $reportDetails['user_id'] : null;
            if (!empty($reportDetails['email'])) {
              sendReportStatusNotification($reportDetails['email'], $residentName, $mobileNumber, $caseNumber, $newStatus, $userId);
            } elseif ($mobileNumber) {
              sendSms($mobileNumber, "Your report {$caseNumber} status has been updated to {$newStatus}.");
            }
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
  SELECT * FROM (
    SELECT r.id, "resident" AS source, r.case_number, r.title, r.description, r.report_type,
         r.location, r.status, r.created_at, r.updated_at, u.username, u.email,
         res.resident_id, res.first_name, res.last_name, NULL AS reporter_name
    FROM reports r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN residents res ON u.id = res.user_id
    UNION ALL
    SELECT g.id, "guest" AS source, g.case_number, g.title, g.description, g.report_type,
         g.location, g.status, g.created_at, g.updated_at, NULL, NULL, NULL, NULL, NULL,
         g.guest_aka
    FROM guest_reports g
  ) AS combined
  WHERE 1=1
';

$params = [];

if ($search) {
    $query .= ' AND (case_number LIKE ? OR username LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR reporter_name LIKE ?)';
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$query .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$reports = $stmt->fetchAll();

// Get statistics
$statsQuery = '
    SELECT COUNT(*) as total,
      SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) as pending,
      SUM(CASE WHEN status = "Ongoing" THEN 1 ELSE 0 END) as ongoing,
      SUM(CASE WHEN status = "Resolved" THEN 1 ELSE 0 END) as resolved,
      SUM(CASE WHEN status = "Dismissed" THEN 1 ELSE 0 END) as dismissed
    FROM (
      SELECT status FROM reports
      UNION ALL
      SELECT status FROM guest_reports
    ) AS all_reports
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
  <base href="/admin/main-pages/">
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
        <div class="profile-avatar"><?php echo renderProfileAvatar($user, $pdo); ?></div>
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
      <li><a href="dashboard.php"<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? ' class="active"' : ''; ?>><i class="fas fa-tachometer-alt"></i> <span class="menu-label">Dashboard</span></a></li>
      <li><a href="announcement.php"<?php echo basename($_SERVER['PHP_SELF']) === 'announcement.php' ? ' class="active"' : ''; ?>><i class="fas fa-bullhorn"></i> <span class="menu-label">Announcements</span></a></li>
      <li><a href="reports.php"<?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? ' class="active"' : ''; ?>><i class="fas fa-file-alt"></i> <span class="menu-label">Reports</span></a></li>
      <li><a href="requests.php"<?php echo basename($_SERVER['PHP_SELF']) === 'requests.php' ? ' class="active"' : ''; ?>><i class="fas fa-clipboard-list"></i> <span class="menu-label">Requests</span></a></li>
      <li><a href="user_verification.php"<?php echo basename($_SERVER['PHP_SELF']) === 'user_verification.php' ? ' class="active"' : ''; ?>><i class="fas fa-check-circle"></i> <span class="menu-label">Verification</span></a></li>
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
        <div class="col-md-4">
          <div class="stat-card">
            <div class="stat-value"><?php echo $stats['total'] ?? 0; ?></div>
            <div class="stat-label">Total Reports</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card stat-card-pending">
            <div class="stat-value"><?php echo $stats['pending'] ?? 0; ?></div>
            <div class="stat-label">Pending</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card stat-card-ongoing">
            <div class="stat-value"><?php echo $stats['ongoing'] ?? 0; ?></div>
            <div class="stat-label">Ongoing</div>
          </div>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-md-6">
          <div class="stat-card stat-card-resolved">
            <div class="stat-value"><?php echo $stats['resolved'] ?? 0; ?></div>
            <div class="stat-label">Resolved</div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="stat-card stat-card-dismissed">
            <div class="stat-value"><?php echo $stats['dismissed'] ?? 0; ?></div>
            <div class="stat-label">Dismissed</div>
          </div>
        </div>
      </div>

      <!-- Search Bar -->
      <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#manage-reports-pane" type="button" role="tab">All Reports</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#search-report-pane" type="button" role="tab">Search by Case Number</button>
        </li>
      </ul>

      <div class="tab-content">
      <div class="tab-pane fade show active" id="manage-reports-pane" role="tabpanel">
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
                      <?php if (($r['source'] ?? '') === 'guest'): ?><span class="badge bg-dark ms-1">Guest</span><?php endif; ?>
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
                        $reporterName = $r['reporter_name'] ?? ($r['username'] ?? 'Anonymous');
                        if ($r['first_name'] && $r['last_name']) {
                          $reporterName = htmlspecialchars($r['first_name'] . ' ' . $r['last_name']);
                        }
                        echo $reporterName;
                      ?>
                    </td>
                    <td>
                      <button class="btn btn-sm btn-outline-info view-btn" data-id="<?php echo $r['id']; ?>" data-source="<?php echo htmlspecialchars($r['source'] ?? 'resident'); ?>" data-bs-toggle="modal" data-bs-target="#reportDetailsModal">
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

      <div class="tab-pane fade" id="search-report-pane" role="tabpanel">
        <div class="card mb-4">
          <div class="card-body">
            <form id="caseSearchForm" class="row g-3">
              <div class="col-md-10">
                <label class="form-label" for="caseSearchInput">Specific Case Number</label>
                <input type="text" id="caseSearchInput" class="form-control" placeholder="CASE-20260822-0472" required>
              </div>
              <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100" id="caseSearchBtn"><i class="fas fa-search"></i> Search</button>
              </div>
              <div class="col-12"><div id="caseSearchAlert" class="alert alert-danger d-none mb-0"></div></div>
            </form>
          </div>
        </div>
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
          <button type="button" class="btn btn-primary" id="applyStatusBtn" disabled>Apply Changes</button>
        </div>
      </div>
    </div>
  </div>

<!-- Shared JS -->
<script src="../../assets/js/shared/logo_functions.js"></script>
<script src="../../assets/js/shared/shared-header.js"></script>
<script src="../../assets/js/shared/shared-sidebar.js"></script>
<script src="../../assets/js/shared/layout_functions.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Page-specific JS -->
<script src="../../assets/js/shared/loading-overlay.js"></script>
<script src="../../assets/js/admin/reports.js"></script>
</body>
</html>
