<?php
require_once __DIR__ . '/../../config/db.php';
// reports.php - SafeBrgy My Reports
session_start();

// Check if user is logged in and verified
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'resident') {
    header('Location: ../login.php');
    exit;
}

$pdo = safeBrgy_db_connect();
$user = $_SESSION['user'];
$userId = $user['id'] ?? null;
$name = $user['name'] ?? 'Resident';

// Get reports from database
$reports = [];
if ($userId) {
    $stmt = $pdo->prepare('
        SELECT id, case_number, report_type, title, description, location, attachments, 
               status, created_at 
        FROM reports 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ');
    $stmt->execute([$userId]);
    $reports = $stmt->fetchAll();
}

// Get status statistics
$statusStats = [
    'Pending' => 0,
    'Ongoing' => 0,
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
        $statusStats[$row['status']] = $row['count'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="../../assets/css/public/reports.css">
  <link rel="stylesheet" href="../../assets/css/shared/layout.css">
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
        <div class="profile-avatar"><?php echo substr($name, 0, 1); ?></div>
        <div class="profile-info">
          <div class="profile-name"><?php echo htmlspecialchars($name); ?></div>
          <div class="profile-role">Resident</div>
        </div>
        <div class="profile-dropdown">
          <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
          <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
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
    <div class="container-fluid p-4">

      <!-- Page Header -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="page-title">My Reports</h2>
          <p class="page-subtitle">Track and manage your incident reports, lost property, and blotters</p>
        </div>
        <button class="btn btn-primary btn-create-report" data-bs-toggle="modal" data-bs-target="#createReportModal">
          <i class="fas fa-plus"></i> Create New Report
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

          <!-- Ongoing Card -->
          <div class="col-md-3 col-sm-6">
            <div class="tracker-card ongoing">
              <div class="tracker-icon">
                <i class="fas fa-spinner"></i>
              </div>
              <div class="tracker-content">
                <div class="tracker-value"><?php echo $statusStats['Ongoing']; ?></div>
                <div class="tracker-label">Ongoing</div>
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
              <option value="Ongoing">Ongoing</option>
              <option value="Resolved">Resolved</option>
              <option value="Dismissed">Dismissed</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Reports Table -->
      <div class="reports-table-section">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Case No.</th>
                <th>Report Type</th>
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
                    </td>
                    <td>
                      <span class="badge bg-info">
                        <?php echo htmlspecialchars($report['report_type']); ?>
                      </span>
                    </td>
                    <td><?php echo htmlspecialchars($report['title'] ?? 'Untitled'); ?></td>
                    <td><?php echo date('M d, Y', strtotime($report['created_at'])); ?></td>
                    <td>
                      <span class="badge bg-<?php 
                        echo match($report['status']) {
                          'Pending' => 'warning',
                          'Ongoing' => 'info',
                          'Resolved' => 'success',
                          'Dismissed' => 'danger',
                          default => 'secondary'
                        };
                      ?>">
                        <?php echo htmlspecialchars($report['status']); ?>
                      </span>
                    </td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary btn-view-report" 
                              data-report-id="<?php echo $report['id']; ?>"
                              data-bs-toggle="modal" 
                              data-bs-target="#viewReportModal">
                        <i class="fas fa-eye"></i> View
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center text-muted py-5">
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
                <option value="Incident">Incident</option>
                <option value="Lost Property">Lost Property</option>
                <option value="Blotter">Blotter</option>
              </select>
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

            <!-- Location (optional) -->
            <div class="mb-3">
              <label for="reportLocation" class="form-label">Location</label>
              <input type="text" class="form-control" id="reportLocation" name="location" 
                     placeholder="Where did this occur?">
            </div>

            <!-- Picture Upload -->
            <div class="mb-3">
              <label for="reportPicture" class="form-label">Picture Upload 
                <span class="badge bg-secondary" title="Recommended">Recommended</span>
              </label>
              <div class="picture-upload-area" id="pictureUploadArea">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Click to upload or drag and drop</p>
                <small>PNG, JPG, GIF up to 5MB</small>
              </div>
              <input type="file" class="form-control d-none" id="reportPicture" name="picture" 
                     accept="image/*">
              <div id="picturePreview" class="mt-2"></div>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-paper-plane"></i> Submit Report
            </button>
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
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Shared JS -->
<script src="../../assets/js/shared/logo_functions.js"></script>
<script src="../../assets/js/shared/shared-header.js"></script>
<script src="../../assets/js/shared/shared-sidebar.js"></script>
<!-- Page-specific JS -->
<script src="../../assets/js/public/reports.js"></script>
</body>
</html>
