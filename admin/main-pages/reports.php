<?php
require_once __DIR__ . '/../admin_protect.php';
require_once __DIR__ . '/../../includes/shared/profile_avatar.php';
require_once __DIR__ . '/../../includes/shared/report_status.php';
require_once __DIR__ . '/../../config/mailer.php';
// admin_reports.php - SafeBrgy Admin Reports

$pdo = safeBrgy_db_connect();
$adminId = $_SESSION['admin_user']['id'] ?? null;
$unreadReportIds = [];
if ($adminId) {
  $unreadReportsStmt = $pdo->prepare('SELECT entity_id FROM notifications WHERE user_id = ? AND entity_type = "report" AND is_read = 0 AND entity_id IS NOT NULL');
  $unreadReportsStmt->execute([(int) $adminId]);
  $unreadReportIds = array_map('intval', $unreadReportsStmt->fetchAll(PDO::FETCH_COLUMN));
}

if ($adminId) {
    $stmt = $pdo->prepare('SELECT username, email FROM users WHERE id = :id');
    $stmt->execute(['id' => $adminId]);
    $admin = $stmt->fetch();
    $user = adminDisplayName($admin['username'] ?? 'Admin');
} else {
    $user = 'Admin';
}

$notifyReportStatus = static function (string $source, int $reportId, string $newStatus) use ($pdo): void {
  if ($source === 'guest') {
    $stmt = $pdo->prepare('SELECT case_number, guest_aka AS reporter_name, contact_email AS email, contact_mobile AS mobile_number, NULL AS user_id FROM guest_reports WHERE id = ?');
  } else {
    $stmt = $pdo->prepare('SELECT r.case_number, u.id AS user_id, u.username, u.email, res.first_name, res.last_name, res.mobile_number FROM reports r LEFT JOIN users u ON r.user_id = u.id LEFT JOIN residents res ON u.id = res.user_id WHERE r.id = ?');
  }
  $stmt->execute([$reportId]);
  $details = $stmt->fetch();
  if (!$details) return;

  $reporterName = $source === 'guest'
    ? ($details['reporter_name'] ?: 'Guest')
    : (trim(($details['first_name'] ?? '') . ' ' . ($details['last_name'] ?? '')) ?: ($details['username'] ?? 'Resident'));
  $caseNumber = $details['case_number'] ?: $reportId;
  $mobileNumber = $details['mobile_number'] ?? null;
  $userId = !empty($details['user_id']) ? (int) $details['user_id'] : null;

  if (!empty($details['email'])) {
    sendReportStatusNotification($details['email'], $reporterName, $mobileNumber, $caseNumber, $newStatus, $userId);
  } elseif ($mobileNumber) {
    sendSms($mobileNumber, "Your report {$caseNumber} status has been updated to {$newStatus}.");
  }

  if ($userId) {
    safeBrgy_create_notification($pdo, $userId, 'report_status', 'reports', 'Report Updated', 'Your report ' . $caseNumber . ' is now ' . $newStatus . '.', '/public/public-pages/reports.php', 'report', $reportId);
  }
};

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
        SELECT r.*, "resident" AS source, u.username, u.email, res.resident_id, res.first_name, res.last_name,
               (SELECT GROUP_CONCAT(t.tag_name ORDER BY t.tag_name SEPARATOR \', \')
          FROM report_tag_assignments a JOIN report_tags t ON t.id = a.tag_id
          WHERE a.report_id = r.id) AS tags
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
                r.report_type, r.location, r.status, r.attachments, r.created_at, r.expires_at,
                 r.updated_at, u.username, u.email, res.resident_id,
                res.first_name, res.last_name,
                (SELECT GROUP_CONCAT(t.tag_name ORDER BY t.tag_name SEPARATOR \', \')
                 FROM report_tag_assignments a JOIN report_tags t ON t.id = a.tag_id
                 WHERE a.report_id = r.id) AS tags,
                NULL AS reporter_name,
                 NULL AS guest_email, NULL AS guest_mobile
            FROM reports r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN residents res ON u.id = res.user_id
            UNION ALL
              SELECT g.id, "guest" AS source, g.case_number, g.title, g.description,
                g.report_type, g.location, g.status, g.attachments, g.created_at, g.expires_at,
                g.updated_at, NULL, g.contact_email, NULL, NULL, NULL, NULL, g.guest_aka,
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

        if (!in_array($newStatus, ['Pending', 'In Progress', 'Resolved', 'Dismissed'], true)) {
          echo json_encode(['success' => false, 'message' => 'Invalid report status.']);
          exit;
        }
        
        $table = $source === 'guest' ? 'guest_reports' : 'reports';
        $stmt = $pdo->prepare("UPDATE {$table} SET expires_at = CASE WHEN status = 'Pending' AND ? = 'In Progress' THEN DATE_ADD(UTC_TIMESTAMP(), INTERVAL 15 DAY) ELSE expires_at END, status = ?, updated_at = UTC_TIMESTAMP() WHERE id = ?");
        $result = $stmt->execute([$newStatus, $newStatus, $reportId]);

        if ($result) {
          try {
            $notifyReportStatus($source, (int) $reportId, $newStatus);
          } catch (Throwable $notificationError) {
            error_log('Report status notification failed: ' . $notificationError->getMessage());
          }
        }
        
        echo json_encode(['success' => $result]);
        exit;
    } elseif ($action === 'quick_update') {
        $newStatus = $_POST['status'] ?? '';
        $selectedReports = json_decode($_POST['reports'] ?? '[]', true);
        if (!in_array($newStatus, ['Pending', 'In Progress', 'Resolved', 'Dismissed'], true) || !is_array($selectedReports) || !$selectedReports) {
          echo json_encode(['success' => false, 'message' => 'Select at least one report and choose a valid status.']);
          exit;
        }
        try {
          $pdo->beginTransaction();
          foreach ($selectedReports as $selectedReport) {
            $table = ($selectedReport['source'] ?? '') === 'guest' ? 'guest_reports' : 'reports';
            $reportId = (int) ($selectedReport['id'] ?? 0);
            if ($reportId < 1) {
              throw new RuntimeException('Invalid report selection.');
            }
            $updateStmt = $pdo->prepare("UPDATE {$table} SET expires_at = CASE WHEN status = 'Pending' AND ? = 'In Progress' THEN DATE_ADD(UTC_TIMESTAMP(), INTERVAL 15 DAY) ELSE expires_at END, status = ?, updated_at = UTC_TIMESTAMP() WHERE id = ?");
            $updateStmt->execute([$newStatus, $newStatus, $reportId]);
          }
          $pdo->commit();
          foreach ($selectedReports as $selectedReport) {
            try {
              $notifyReportStatus(($selectedReport['source'] ?? '') === 'guest' ? 'guest' : 'resident', (int) $selectedReport['id'], $newStatus);
            } catch (Throwable $notificationError) {
              error_log('Report status notification failed: ' . $notificationError->getMessage());
            }
          }
          echo json_encode(['success' => true, 'updated' => count($selectedReports)]);
        } catch (Throwable $e) {
          if ($pdo->inTransaction()) $pdo->rollBack();
          echo json_encode(['success' => false, 'message' => 'Unable to update the selected reports.']);
        }
        exit;
    }
}

// Build the resident and guest report datasets with the same columns.
$combinedReportsSql = '
  SELECT r.id, "resident" AS source, r.case_number, r.title, r.description, r.report_type,
    r.location, r.status, r.created_at, r.updated_at, r.expires_at, u.username, u.email,
    res.resident_id, res.first_name, res.last_name,
    (SELECT GROUP_CONCAT(t.tag_name ORDER BY t.tag_name SEPARATOR ", ")
     FROM report_tag_assignments a JOIN report_tags t ON t.id = a.tag_id WHERE a.report_id = r.id) AS tags,
    NULL AS reporter_name, NULL AS guest_email, NULL AS guest_mobile
  FROM reports r
  LEFT JOIN users u ON r.user_id = u.id
  LEFT JOIN residents res ON u.id = res.user_id
  UNION ALL
  SELECT g.id, "guest" AS source, g.case_number, g.title, g.description, g.report_type,
    g.location, g.status, g.created_at, g.updated_at, g.expires_at, NULL, g.contact_email,
    NULL, NULL, NULL, NULL, g.guest_aka, g.contact_email, g.contact_mobile
  FROM guest_reports g
';

$search = trim($_GET['search'] ?? '');
$reportTypeFilter = $_GET['type'] ?? '';
$tagFilter = $_GET['tag'] ?? '';
$allowedReportTypes = ['Incident', 'Lost Property', 'Public Concerns', 'Blotter'];
if (!in_array($reportTypeFilter, $allowedReportTypes, true)) $reportTypeFilter = '';

$allReportsWhere = ' WHERE 1=1';
$allReportsParams = [];
if ($search !== '') {
    $allReportsWhere .= ' AND (case_number LIKE ? OR username LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR reporter_name LIKE ?)';
    $searchTerm = '%' . $search . '%';
    array_push($allReportsParams, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
}
if ($reportTypeFilter !== '') {
    $allReportsWhere .= ' AND report_type = ?';
    $allReportsParams[] = $reportTypeFilter;
}
if ($tagFilter !== '') {
    $allReportsWhere .= ' AND EXISTS (SELECT 1 FROM report_tag_assignments a JOIN report_tags t ON t.id = a.tag_id WHERE a.report_id = combined.id AND combined.source = "resident" AND t.tag_name = ?)';
    $allReportsParams[] = $tagFilter;
}
$stmt = $pdo->prepare('SELECT * FROM (' . $combinedReportsSql . ') AS combined' . $allReportsWhere . ' ORDER BY created_at DESC');
$stmt->execute($allReportsParams);
$reports = $stmt->fetchAll();

$availableTags = $pdo->query('SELECT DISTINCT t.tag_name FROM report_tags t JOIN report_tag_assignments a ON a.tag_id = t.id JOIN reports r ON r.id = a.report_id ORDER BY t.tag_name')->fetchAll(PDO::FETCH_COLUMN);

$recordFrom = $_GET['date_from'] ?? '';
$recordTo = $_GET['date_to'] ?? '';
$recordType = $_GET['record_type'] ?? '';
foreach (['recordFrom', 'recordTo'] as $dateVariable) {
    if ($$dateVariable !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $$dateVariable)) $$dateVariable = '';
}
if (!in_array($recordType, $allowedReportTypes, true)) $recordType = '';
$recordsWhere = ' WHERE 1=1';
$recordsParams = [];
if ($recordFrom !== '') { $recordsWhere .= ' AND created_at >= ?'; $recordsParams[] = $recordFrom . ' 00:00:00'; }
if ($recordTo !== '') { $recordsWhere .= ' AND created_at < DATE_ADD(?, INTERVAL 1 DAY)'; $recordsParams[] = $recordTo . ' 00:00:00'; }
if ($recordType !== '') { $recordsWhere .= ' AND report_type = ?'; $recordsParams[] = $recordType; }
$recordsStmt = $pdo->prepare('SELECT * FROM (' . $combinedReportsSql . ') AS combined' . $recordsWhere . ' ORDER BY created_at DESC');
$recordsStmt->execute($recordsParams);
$recordReports = $recordsStmt->fetchAll();

$stats = $pdo->query("SELECT COUNT(*) AS total,
  SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
  SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) AS in_progress,
  SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) AS resolved,
  SUM(CASE WHEN status = 'Dismissed' THEN 1 ELSE 0 END) AS dismissed
  FROM (SELECT status FROM reports UNION ALL SELECT status FROM guest_reports) AS all_reports")->fetch();
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
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- Page styles load last to override shared and framework defaults -->
  <link rel="stylesheet" href="../../assets/css/admin/reports.css">
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
      <li><a href="dashboard.php" data-notification-badge="dashboard"<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? ' class="active"' : ''; ?>><i class="fas fa-tachometer-alt"></i> <span class="menu-label">Dashboard</span><span class="notification-count d-none" aria-hidden="true">0</span></a></li>
      <li><a href="announcement.php" data-notification-badge="announcement"<?php echo basename($_SERVER['PHP_SELF']) === 'announcement.php' ? ' class="active"' : ''; ?>><i class="fas fa-bullhorn"></i> <span class="menu-label">Announcements</span><span class="notification-count d-none" aria-hidden="true">0</span></a></li>
      <li><a href="reports.php" data-notification-badge="reports"<?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? ' class="active"' : ''; ?>><i class="fas fa-file-alt"></i> <span class="menu-label">Reports</span><span class="notification-count d-none" aria-hidden="true">0</span></a></li>
      <li><a href="requests.php" data-notification-badge="requests"<?php echo basename($_SERVER['PHP_SELF']) === 'requests.php' ? ' class="active"' : ''; ?>><i class="fas fa-clipboard-list"></i> <span class="menu-label">Requests</span><span class="notification-count d-none" aria-hidden="true">0</span></a></li>
      <li><a href="user_verification.php" data-notification-badge="verification"<?php echo basename($_SERVER['PHP_SELF']) === 'user_verification.php' ? ' class="active"' : ''; ?>><i class="fas fa-check-circle"></i> <span class="menu-label">Verification</span><span class="notification-count d-none" aria-hidden="true">0</span></a></li>
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
      <div id="reportStatistics">
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
            <div class="stat-value"><?php echo $stats['in_progress'] ?? 0; ?></div>
            <div class="stat-label">In Progress</div>
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

      </div>

      <!-- Search Bar -->
      <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#manage-reports-pane" type="button" role="tab">All Reports</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#search-report-pane" type="button" role="tab">Search by Case Number</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#report-records-pane" type="button" role="tab">Report Records</button>
        </li>
      </ul>

      <div class="tab-content">
      <div class="tab-pane fade show active" id="manage-reports-pane" role="tabpanel">
      <div class="card mb-4">
        <div class="card-body">
          <form method="get" class="row g-3">
            <div class="col-lg-5">
              <label class="form-label">Search by Name or Case Number</label>
              <input type="text" name="search" class="form-control" placeholder="Name or case number" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-lg-3">
              <label class="form-label">Report Type</label>
              <select name="type" class="form-select">
                <option value="">All types</option>
                <?php foreach ($allowedReportTypes as $typeOption): ?><option value="<?php echo htmlspecialchars($typeOption); ?>" <?php echo $reportTypeFilter === $typeOption ? 'selected' : ''; ?>><?php echo htmlspecialchars($typeOption === 'Incident' ? 'Incident Report' : $typeOption); ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-lg-2">
              <label class="form-label">Tag</label>
              <select name="tag" class="form-select">
                <option value="">All tags</option>
                <?php foreach ($availableTags as $tagOption): ?><option value="<?php echo htmlspecialchars($tagOption); ?>" <?php echo $tagFilter === $tagOption ? 'selected' : ''; ?>><?php echo htmlspecialchars($tagOption); ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-lg-2 d-flex align-items-end gap-2">
              <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i> Search</button>
              <a href="reports.php" class="btn btn-outline-secondary" title="Reset filters"><i class="fas fa-redo-alt"></i></a>
            </div>
            <div class="col-12 d-flex align-items-center flex-wrap gap-2 report-selection-toolbar">
              <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAllReports"><i class="fas fa-check-square"></i> Select All</button>
              <button type="button" class="btn btn-sm btn-outline-secondary" id="unselectAllReports"><i class="far fa-square"></i> Unselect All</button>
              <button type="button" class="btn btn-sm btn-primary" id="openQuickUpdate" disabled><i class="fas fa-edit"></i> Quick Update <span id="selectedReportCount">0</span></button>
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
                <th><input type="checkbox" id="selectAllReportRows" aria-label="Select all reports"></th>
                <th>Case #</th>
                <th>Title</th>
                <th>Date Filed</th>
                <th>Type</th>
                <th>Tags</th>
                <th>Status</th>
                <th>Reporter</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="reportsTable">
              <?php if (empty($reports)): ?>
                <tr>
                  <td colspan="9" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                    No reports found
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($reports as $r): ?>
                  <tr>
                    <td><input type="checkbox" class="report-row-checkbox" data-id="<?php echo (int) $r['id']; ?>" data-source="<?php echo htmlspecialchars($r['source'] ?? 'resident'); ?>" aria-label="Select <?php echo htmlspecialchars($r['case_number'] ?? 'report'); ?>"></td>
                    <td>
                      <span class="badge bg-secondary"><?php echo htmlspecialchars($r['case_number'] ?? 'N/A'); ?></span>
                      <?php if (($r['source'] ?? '') === 'resident' && in_array((int) $r['id'], $unreadReportIds ?? [], true)): ?><span class="notification-dot" aria-label="Unread report"></span><?php endif; ?>
                    </td>
                    <td>
                      <strong><?php echo htmlspecialchars($r['title'] ?? 'Untitled'); ?></strong>
                      <?php if (($r['source'] ?? '') === 'guest'): ?><span class="badge bg-dark ms-1">Guest</span><?php endif; ?>
                    </td>
                    <td>
                      <small><?php echo htmlspecialchars(date('M d, Y g:i A', strtotime($r['created_at']))); ?></small>
                    </td>
                    <td>
                      <?php $typeIcon = ['Incident' => 'fas fa-exclamation-triangle', 'Public Concerns' => 'fas fa-bullhorn', 'Blotter' => 'fas fa-gavel', 'Lost Property' => 'fas fa-search'][$r['report_type'] ?? ''] ?? 'fas fa-file-alt'; ?>
                      <span class="report-type-badge type-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $r['report_type'] ?? 'unknown'))); ?>"><i class="<?php echo $typeIcon; ?>" aria-hidden="true"></i><?php echo htmlspecialchars(($r['report_type'] ?? '') === 'Incident' ? 'Incident Report' : ($r['report_type'] ?? 'Unknown')); ?></span>
                    </td>
                    <td class="report-tags-cell"><?php echo htmlspecialchars($r['tags'] ?? ''); ?></td>
                    <td class="report-status-cell"><?php echo safeBrgyRenderReportStatusIcon($r['status'] ?? null, $r['expires_at'] ?? null); ?></td>
                    <td>
                      <?php 
                        $reporterName = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?: ($r['reporter_name'] ?? ($r['username'] ?? 'Anonymous'));
                        echo htmlspecialchars($reporterName, ENT_QUOTES, 'UTF-8');
                      ?>
                    </td>
                    <td>
                      <button class="btn btn-sm btn-outline-info view-btn" data-id="<?php echo $r['id']; ?>" data-source="<?php echo htmlspecialchars($r['source'] ?? 'resident'); ?>"<?php echo ($r['source'] ?? '') === 'resident' ? ' data-notification-entity="report" data-notification-id="' . (int) $r['id'] . '"' : ''; ?> data-bs-toggle="modal" data-bs-target="#reportDetailsModal">
                        <i class="fas fa-eye" aria-hidden="true"></i> View
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
                <button type="submit" class="btn btn-primary w-100" id="caseSearchBtn"><i class="fas fa-search" aria-hidden="true"></i> Search</button>
              </div>
              <div class="col-12"><div id="caseSearchAlert" class="alert alert-danger d-none mb-0"></div></div>
            </form>
          </div>
        </div>
      </div>
      <div class="tab-pane fade" id="report-records-pane" role="tabpanel" data-admin-name="<?php echo htmlspecialchars($user, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="card mb-3">
          <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
              <input type="hidden" name="view" value="records">
              <div class="col-md-3"><label class="form-label" for="recordDateFrom">Date From</label><input type="date" id="recordDateFrom" name="date_from" class="form-control" value="<?php echo htmlspecialchars($recordFrom); ?>"></div>
              <div class="col-md-3"><label class="form-label" for="recordDateTo">Date To</label><input type="date" id="recordDateTo" name="date_to" class="form-control" value="<?php echo htmlspecialchars($recordTo); ?>"></div>
              <div class="col-md-3"><label class="form-label" for="recordType">Report Type</label><select id="recordType" name="record_type" class="form-select"><option value="">All types</option><?php foreach ($allowedReportTypes as $typeOption): ?><option value="<?php echo htmlspecialchars($typeOption); ?>" <?php echo $recordType === $typeOption ? 'selected' : ''; ?>><?php echo htmlspecialchars($typeOption === 'Incident' ? 'Incident Report' : $typeOption); ?></option><?php endforeach; ?></select></div>
              <div class="col-md-3 d-flex gap-2"><button type="submit" class="btn btn-primary"><i class="fas fa-filter" aria-hidden="true"></i> Filter</button><button type="button" class="btn btn-outline-primary" id="exportRecords"><i class="fas fa-file-pdf" aria-hidden="true"></i> Export PDF</button><button type="button" class="btn btn-outline-secondary" id="printRecords" title="Print filtered records" aria-label="Print filtered records"><i class="fas fa-print" aria-hidden="true"></i></button></div>
            </form>
          </div>
        </div>
        <div class="card">
          <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0" id="reportRecordsTable">
              <thead class="table-dark"><tr><th>Case Number</th><th>Title</th><th>Date Filed</th><th>Type</th><th>Tags</th><th>Status</th><th>Reporter</th><th>Reporter Email</th><th>Action</th></tr></thead>
              <tbody>
                <?php if (!$recordReports): ?><tr><td colspan="9" class="text-center py-4 text-muted">No report records match these filters.</td></tr>
                <?php else: foreach ($recordReports as $record): ?>
                  <?php
                    $recordReporter = trim(($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? '')) ?: ($record['reporter_name'] ?? $record['username'] ?? 'Anonymous');
                    $recordStatusInfo = safeBrgyReportStatusInfo($record['status'] ?? null, $record['expires_at'] ?? null);
                  ?>
                  <tr data-status-name="<?php echo htmlspecialchars($recordStatusInfo['name'], ENT_QUOTES, 'UTF-8'); ?>">
                    <td><?php echo htmlspecialchars($record['case_number'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($record['title'] ?? 'Untitled'); ?></td>
                    <td><?php echo htmlspecialchars(date('m/d/Y', strtotime($record['created_at']))); ?></td>
                    <td><?php echo htmlspecialchars(($record['report_type'] ?? '') === 'Incident' ? 'Incident Report' : ($record['report_type'] ?? '')); ?></td>
                    <td class="report-tags-cell"><?php echo htmlspecialchars($record['tags'] ?? ''); ?></td>
                    <td class="report-status-cell"><?php echo safeBrgyRenderReportStatusIcon($record['status'] ?? null, $record['expires_at'] ?? null); ?></td>
                    <td><?php echo htmlspecialchars($recordReporter); ?></td>
                    <td><?php echo htmlspecialchars($record['email'] ?? ''); ?></td>
                    <td><button type="button" class="btn btn-sm btn-outline-primary export-one-record" title="Export this report"><i class="fas fa-file-pdf" aria-hidden="true"></i></button></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      </div>

    </div>
  </main>

  <div class="modal fade" id="quickUpdateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Quick Update Status</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body"><p class="text-muted">Selected reports</p><div id="quickUpdateReportList" class="selected-report-list"></div>
        <label for="bulkStatusSelect" class="form-label mt-3">Update status</label>
        <select class="form-select" id="bulkStatusSelect"><option value="Pending">Pending</option><option value="In Progress">In Progress</option><option value="Resolved">Resolved</option><option value="Dismissed">Dismissed</option></select>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times" aria-hidden="true"></i> Cancel</button><button type="button" class="btn btn-primary" id="applyBulkStatus"><i class="fas fa-check-double" aria-hidden="true"></i> Update Selected</button></div>
    </div></div>
  </div>

  <div class="modal fade" id="exportConfirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl"><div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Confirm Report Records Export</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body"><p id="exportRangeSummary" class="text-muted"></p><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Case Number</th><th>Title</th><th>Date Filed</th><th>Type</th><th>Tags</th><th>Status</th><th>Reporter</th><th>Reporter Email</th></tr></thead><tbody id="exportReportList"></tbody></table></div></div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times" aria-hidden="true"></i> Cancel</button><button type="button" class="btn btn-outline-secondary" id="confirmPrintRecords"><i class="fas fa-print" aria-hidden="true"></i> Print</button><button type="button" class="btn btn-primary" id="confirmExportPdf"><i class="fas fa-file-pdf" aria-hidden="true"></i> Export PDF</button></div>
    </div></div>
  </div>

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
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times" aria-hidden="true"></i> Close</button>
          <button type="button" class="btn btn-outline-secondary" id="printReportDetails" disabled><i class="fas fa-print" aria-hidden="true"></i> Print</button>
          <button type="button" class="btn btn-outline-primary" id="exportReportDetailsPdf" disabled><i class="fas fa-file-pdf" aria-hidden="true"></i> Export PDF</button>
          <button type="button" class="btn btn-primary" id="applyStatusBtn" disabled><i class="fas fa-save" aria-hidden="true"></i> Apply Changes</button>
        </div>
      </div>
    </div>
  </div>

<?php include __DIR__ . '/../../includes/notification/notify.html'; ?>
<!-- Shared JS -->
<script src="../../assets/js/shared/logo_functions.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/shared/logo_functions.js'); ?>"></script>
<script src="../../assets/js/shared/shared-header.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/shared/shared-header.js'); ?>"></script>
<script src="../../assets/js/shared/shared-sidebar.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/shared/shared-sidebar.js'); ?>"></script>
<script src="../../assets/js/shared/layout_functions.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/shared/layout_functions.js'); ?>"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Page-specific JS -->
<script src="../../assets/js/shared/loading-overlay.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/shared/loading-overlay.js'); ?>"></script>
<script src="../../assets/js/realtime.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/realtime.js'); ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="../../assets/js/admin/reports.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/admin/reports.js'); ?>"></script>
</body>
</html>
