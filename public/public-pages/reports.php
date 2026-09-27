<?php
require_once __DIR__ . '/../../config/db.php';
// reports.php - SafeBrgy My Reports
session_start();
require_once __DIR__ . '/../../includes/shared/profile_avatar.php';
require_once __DIR__ . '/../../includes/shared/report_status.php';

// Check if user is logged in and verified
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'resident') {
    header('Location: ../login.php');
    exit;
}

$pdo = safeBrgy_db_connect();
$user = $_SESSION['user'];
$userId = $user['id'] ?? null;
$name = $user['name'] ?? 'Resident';
$requestedReportType = $_GET['report_type'] ?? '';
$requestedReportType = in_array($requestedReportType, ['Incident', 'Lost Property', 'Public Concerns', 'Blotter'], true)
  ? $requestedReportType
  : '';

// Get reports from database
$reports = [];
if ($userId) {
    $stmt = $pdo->prepare('
         SELECT r.id, r.case_number, r.report_type, r.title, r.description, r.location,
           r.attachments, r.status, r.created_at, r.expires_at,
           (SELECT GROUP_CONCAT(t.tag_name ORDER BY t.tag_name SEPARATOR \', \')
            FROM report_tag_assignments a JOIN report_tags t ON t.id = a.tag_id
            WHERE a.report_id = r.id) AS tags
         FROM reports r
         WHERE r.user_id = ?
         ORDER BY r.created_at DESC
    ');
    $stmt->execute([$userId]);
    $reports = $stmt->fetchAll();
}
  $unreadReportIds = [];
  if ($userId) {
    $unreadReportsStmt = $pdo->prepare('SELECT entity_id FROM notifications WHERE user_id = ? AND entity_type = "report" AND is_read = 0 AND entity_id IS NOT NULL');
    $unreadReportsStmt->execute([(int) $userId]);
    $unreadReportIds = array_map('intval', $unreadReportsStmt->fetchAll(PDO::FETCH_COLUMN));
  }

$feedReports = [];
if ($userId) {
  $feedStmt = $pdo->query("SELECT r.id, r.case_number, r.report_type, r.title, r.description, r.location,
    r.attachments, r.status, r.created_at, r.expires_at,
    (SELECT GROUP_CONCAT(t.tag_name ORDER BY t.tag_name SEPARATOR ', ')
     FROM report_tag_assignments a JOIN report_tags t ON t.id = a.tag_id
     WHERE a.report_id = r.id) AS tags
    FROM reports r
    WHERE r.report_type IN ('Lost Property', 'Blotter')
    ORDER BY r.created_at DESC LIMIT 100");
  $feedReports = $feedStmt->fetchAll();
}

// Get status statistics
$statusStats = [
    'Pending' => 0,
    'In Progress' => 0,
    'Resolved' => 0,
    'Dismissed' => 0
];

if ($userId) {
    $stmt = $pdo->prepare('
        SELECT status, COUNT(*) as count 
        FROM reports 
        WHERE user_id = ? 
        GROUP BY status
    ');
    $stmt->execute([$userId]);
    $results = $stmt->fetchAll();
    foreach ($results as $row) {
        if (array_key_exists($row['status'], $statusStats)) {
          $statusStats[$row['status']] = $row['count'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="/public/public-pages/">
  <title>SafeBrgy - My Reports</title>
  <link rel="icon" type="image/png" href="../../assets/img/seal.png">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Shared Styles -->
  <link rel="stylesheet" href="../../assets/css/shared/shared-header.css">
  <link rel="stylesheet" href="../../assets/css/shared/shared_sidebar.css">
  <link rel="stylesheet" href="../../assets/css/shared/colors.css">
  <link rel="stylesheet" href="../../assets/css/shared/layout.css">
  <link rel="stylesheet" href="../../assets/css/shared/loading-overlay.css">
  <!-- Page styles load last to keep report typography consistent -->
  <link rel="stylesheet" href="../../assets/css/public/reports.css">
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
        <div class="profile-avatar"><?php echo renderProfileAvatar($name, $pdo); ?></div>
        <div class="profile-info">
          <div class="profile-name"><?php echo htmlspecialchars($name); ?></div>
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
      <li><a href="dashboard.php" data-notification-badge="dashboard"<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? ' class="active"' : ''; ?>><i class="fas fa-tachometer-alt"></i> <span class="menu-label">Dashboard</span><span class="notification-count d-none" aria-hidden="true">0</span></a></li>
      <li><a href="announcement.php" data-notification-badge="announcement"<?php echo basename($_SERVER['PHP_SELF']) === 'announcement.php' ? ' class="active"' : ''; ?>><i class="fas fa-bullhorn"></i> <span class="menu-label">Announcements</span><span class="notification-count d-none" aria-hidden="true">0</span></a></li>
      <li><a href="reports.php" data-notification-badge="reports"<?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? ' class="active"' : ''; ?>><i class="fas fa-file-alt"></i> <span class="menu-label">My Reports</span><span class="notification-count d-none" aria-hidden="true">0</span></a></li>
      <li><a href="requests.php" data-notification-badge="requests"<?php echo basename($_SERVER['PHP_SELF']) === 'requests.php' ? ' class="active"' : ''; ?>><i class="fas fa-clipboard-list"></i> <span class="menu-label">My Requests</span><span class="notification-count d-none" aria-hidden="true">0</span></a></li>
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
          <h2 class="page-title">My Reports</h2>
          <p class="page-subtitle">Track and manage your incident reports, lost property, and blotters</p>
        </div>
        <button class="btn btn-primary btn-create-report" data-bs-toggle="modal" data-bs-target="#createReportModal">
          <i class="fas fa-plus" aria-hidden="true"></i> Create New Report
        </button>
      </div>

      <!-- Status Tracker Section -->
      <div class="status-tracker-section mb-4">
        <h3 class="section-title">Report Status Overview</h3>
        <div class="row g-3">
          <!-- Pending Card -->
          <div class="col-md-3 col-sm-6">
            <div class="tracker-card pending">
              <div class="tracker-icon">
                <i class="fas fa-clock"></i>
              </div>
              <div class="tracker-content">
                <div class="tracker-value"><?php echo $statusStats['Pending']; ?></div>
                <div class="tracker-label">Pending</div>
              </div>
            </div>
          </div>

          <!-- In Progress Card -->
          <div class="col-md-3 col-sm-6">
            <div class="tracker-card ongoing">
                <div class="tracker-icon">
                <i class="fas fa-spinner"></i>
              </div>
              <div class="tracker-content">
                <div class="tracker-value"><?php echo $statusStats['In Progress']; ?></div>
                <div class="tracker-label">In Progress</div>
              </div>
            </div>
          </div>

          <!-- Resolved Card -->
          <div class="col-md-3 col-sm-6">
            <div class="tracker-card resolved">
              <div class="tracker-icon">
                <i class="fas fa-check-circle"></i>
              </div>
              <div class="tracker-content">
                <div class="tracker-value"><?php echo $statusStats['Resolved']; ?></div>
                <div class="tracker-label">Resolved</div>
              </div>
            </div>
          </div>

          <!-- Dismissed Card -->
          <div class="col-md-3 col-sm-6">
            <div class="tracker-card dismissed">
              <div class="tracker-icon">
                <i class="fas fa-times-circle"></i>
              </div>
              <div class="tracker-content">
                <div class="tracker-value"><?php echo $statusStats['Dismissed']; ?></div>
                <div class="tracker-label">Dismissed</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Search and Filter Section -->
      <div class="search-filter-section mb-4">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="search-box">
              <i class="fas fa-search"></i>
              <input type="text" id="searchReports" class="form-control" 
                     placeholder="Search by case number or title...">
            </div>
          </div>
          <div class="col-md-6">
            <select id="filterStatus" class="form-select">
              <option value="">All Status</option>
              <option value="Pending">Pending</option>
              <option value="In Progress">In Progress</option>
              <option value="Resolved">Resolved</option>
              <option value="Dismissed">Dismissed</option>
            </select>
          </div>
        </div>
      </div>

      <ul class="nav nav-tabs report-view-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#my-reports-pane" type="button" role="tab">My Reports</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#community-feed-pane" type="button" role="tab">Community Feed</button></li>
      </ul>
      <div class="tab-content">
      <div class="tab-pane fade show active" id="my-reports-pane" role="tabpanel">
      <!-- Reports Table -->
      <div class="reports-table-section">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Case No.</th>
                <th>Report Type</th>
                <th>Tags</th>
                <th>Title</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="reportsTable">
              <?php if (!empty($reports)): ?>
                <?php foreach ($reports as $report): ?>
                  <tr class="report-row" data-status="<?php echo htmlspecialchars($report['status']); ?>">
                    <td class="case-number">
                      <?php echo $report['case_number'] ? htmlspecialchars($report['case_number']) : 'N/A'; ?>
                      <?php if (in_array((int) $report['id'], $unreadReportIds, true)): ?><span class="notification-dot" aria-label="Unread report update"></span><?php endif; ?>
                    </td>
                    <td>
                      <?php $typeIcon = ['Incident' => 'fas fa-exclamation-triangle', 'Public Concerns' => 'fas fa-bullhorn', 'Blotter' => 'fas fa-gavel', 'Lost Property' => 'fas fa-search'][$report['report_type']] ?? 'fas fa-file-alt'; ?>
                      <span class="report-type-badge type-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $report['report_type']))); ?>"><i class="<?php echo $typeIcon; ?>" aria-hidden="true"></i><?php echo htmlspecialchars($report['report_type'] === 'Incident' ? 'Incident Report' : $report['report_type']); ?></span>
                    </td>
                    <td class="report-tags-cell"><?php echo htmlspecialchars($report['tags'] ?: ''); ?></td>
                    <td><?php echo htmlspecialchars($report['title'] ?? 'Untitled'); ?></td>
                    <td><?php echo date('M d, Y', strtotime($report['created_at'])); ?></td>
                    <td class="report-status-cell"><?php echo safeBrgyRenderReportStatusIcon($report['status'] ?? null, $report['expires_at'] ?? null); ?></td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary btn-view-report" 
                              data-report-id="<?php echo $report['id']; ?>"
                              data-notification-entity="report" data-notification-id="<?php echo (int) $report['id']; ?>"
                              data-bs-toggle="modal" 
                              data-bs-target="#viewReportModal">
                        <i class="fas fa-eye" aria-hidden="true"></i> View
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="text-center text-muted py-5">
                    <i class="fas fa-inbox"></i>
                    <p>No reports yet. Create your first report to get started!</p>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      </div>
      <div class="tab-pane fade" id="community-feed-pane" role="tabpanel">
        <div class="community-feed" id="communityFeed">
          <?php if (!$feedReports): ?>
            <div class="text-center text-muted py-5">No Blotter or Lost Property reports are available yet.</div>
          <?php else: ?>
            <?php foreach ($feedReports as $feedReport): ?>
              <?php $feedTags = array_filter(explode(', ', (string) ($feedReport['tags'] ?? ''))); ?>
              <article class="community-report">
                <div class="community-report-heading">
                  <span class="report-type-badge type-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $feedReport['report_type']))); ?>">
                    <i class="fas <?php echo $feedReport['report_type'] === 'Blotter' ? 'fa-gavel' : 'fa-search'; ?>" aria-hidden="true"></i>
                    <?php echo htmlspecialchars($feedReport['report_type']); ?>
                  </span>
                  <time><?php echo htmlspecialchars(date('M d, Y', strtotime($feedReport['created_at']))); ?></time>
                </div>
                <h3><?php echo htmlspecialchars($feedReport['title'] ?? 'Untitled'); ?></h3>
                <p><?php echo nl2br(htmlspecialchars($feedReport['description'] ?? '')); ?></p>
                <div class="community-report-meta">
                  <?php if ($feedReport['location']): ?><span><i class="fas fa-map-marker-alt" aria-hidden="true"></i><?php echo htmlspecialchars($feedReport['location']); ?></span><?php endif; ?>
                  <?php foreach ($feedTags as $feedTag): ?><span class="report-tag-chip"><?php echo htmlspecialchars($feedTag); ?></span><?php endforeach; ?>
                </div>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
      </div>

    </div>
  </main>

  <!-- CREATE REPORT MODAL -->
  <div class="modal fade" id="createReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Create New Report</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="createReportForm" enctype="multipart/form-data">
          <div class="modal-body">
            <!-- Report Type -->
            <div class="mb-3">
              <label for="reportType" class="form-label">Report Type <span class="text-danger">*</span></label>
              <select id="reportType" name="report_type" class="form-select" required>
                <option value="">Select a report type</option>
                <option value="Incident"<?php echo $requestedReportType === 'Incident' ? ' selected' : ''; ?>>Incident Report</option>
                <option value="Lost Property"<?php echo $requestedReportType === 'Lost Property' ? ' selected' : ''; ?>>Lost Property</option>
                <option value="Public Concerns"<?php echo $requestedReportType === 'Public Concerns' ? ' selected' : ''; ?>>Public Concerns</option>
                <option value="Blotter">Blotter</option>
              </select>
            </div>

            <div class="mb-3" id="reportTagsSection" hidden>
              <div class="d-flex align-items-center justify-content-between mb-2">
                <label class="form-label mb-0">Tags <span class="text-danger">*</span></label>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addReportTagButton" aria-label="Add a reusable tag" title="Add a reusable tag"><i class="fas fa-plus" aria-hidden="true"></i></button>
              </div>
              <div id="reportTagChoices" class="report-tag-choices" aria-live="polite"></div>
              <small class="text-muted">Choose 1 to 5 tags.</small>
            </div>

            <!-- Title -->
            <div class="mb-3">
              <label for="reportTitle" class="form-label">Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="reportTitle" name="title" 
                     placeholder="Enter report title" required>
            </div>

            <!-- Description -->
            <div class="mb-3">
              <label for="reportDescription" class="form-label">Description <span class="text-danger">*</span></label>
              <textarea class="form-control" id="reportDescription" name="description" 
                        rows="4" placeholder="Provide details about your report" required></textarea>
            </div>

            <!-- Location -->
            <div class="mb-3">
              <label for="reportLocation" class="form-label">Location <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="reportLocation" name="location" 
                     placeholder="Where did this occur?" required>
            </div>

            <!-- Picture Upload -->
            <div class="mb-3">
              <label for="reportPicture" class="form-label">Picture Upload 
                <span class="badge bg-secondary" title="Recommended">Recommended</span>
              </label>
              <div class="picture-upload-area" id="pictureUploadArea">
                <i class="fas fa-cloud-upload-alt" aria-hidden="true"></i>
                <p>Click to upload or drag and drop</p>
                <small>PNG, JPG, GIF, or WEBP. Up to 10 pictures.</small>
              </div>
              <input type="file" class="form-control d-none" id="reportPicture" name="picture[]" 
                     accept="image/*" multiple>
              <div id="picturePreview" class="mt-2"></div>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times" aria-hidden="true"></i> Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-paper-plane" aria-hidden="true"></i> Submit Report
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="addReportTagModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form id="addReportTagForm">
          <div class="modal-header">
            <h5 class="modal-title">Add a Reusable Tag</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <label for="newReportTagName" class="form-label">Tag name</label>
            <input type="text" class="form-control" id="newReportTagName" maxlength="60" required placeholder="Up to three words">
            <div class="form-text">This tag will be available to residents choosing this report type.</div>
            <div id="addReportTagError" class="text-danger small mt-2" role="alert"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times" aria-hidden="true"></i> Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> Add Tag</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- VIEW REPORT MODAL -->
  <div class="modal fade" id="viewReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Report Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="reportDetailsContent">
          <!-- Loaded dynamically -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times" aria-hidden="true"></i> Close</button>
        </div>
      </div>
    </div>
  </div>

<?php include __DIR__ . '/../../includes/notification/notify.html'; ?>
<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Shared JS -->
<script src="../../assets/js/shared/logo_functions.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/shared/logo_functions.js'); ?>"></script>
<script src="../../assets/js/shared/shared-header.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/shared/shared-header.js'); ?>"></script>
<script src="../../assets/js/shared/shared-sidebar.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/shared/shared-sidebar.js'); ?>"></script><script src="../../assets/js/shared/layout_functions.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/shared/layout_functions.js'); ?>"></script><!-- Page-specific JS -->
<script src="../../assets/js/shared/loading-overlay.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/shared/loading-overlay.js'); ?>"></script>
<script src="../../assets/js/realtime.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/realtime.js'); ?>"></script>
<script src="../../assets/js/public/reports.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/public/reports.js'); ?>"></script>
</body>
</html>
