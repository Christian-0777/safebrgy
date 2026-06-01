<?php
require_once __DIR__ . '/../admin_protect.php';
// admin_requests.php - SafeBrgy Admin Requests

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

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    
    if ($action === 'update_status') {
        $requestId = $_POST['request_id'] ?? 0;
        $newStatus = $_POST['status'] ?? 'Pending';
        
        $allowedStatuses = ['Pending', 'Approved', 'Rejected', 'Processing', 'Ready to Receive', 'Received'];
        if (!in_array($newStatus, $allowedStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit;
        }
        
        $dateReceived = null;
        if ($newStatus === 'Received') {
            $dateReceived = date('Y-m-d H:i:s');
        }
        
        $stmt = $pdo->prepare('UPDATE requests SET status = ?, date_received = ? WHERE id = ?');
        $result = $stmt->execute([$newStatus, $dateReceived, $requestId]);
        
        echo json_encode(['success' => $result, 'message' => $result ? 'Status updated successfully' : 'Failed to update status']);
        exit;
    }
}

// Get filter and search parameters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Build query
$query = '
    SELECT r.id, r.request_type, r.purpose, r.location, r.status, r.created_at, r.date_received, r.attachments,
           u.username, u.email, u.phone, res.first_name, res.last_name
    FROM requests r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN residents res ON u.id = res.user_id
    WHERE 1=1
';

$params = [];

if ($search) {
    $query .= ' AND (u.username LIKE ? OR res.first_name LIKE ? OR res.last_name LIKE ? OR r.id LIKE ?)';
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($status && $status !== 'all') {
    $query .= ' AND r.status = ?';
    $params[] = $status;
}

// Add sort
if ($sort === 'oldest') {
    $query .= ' ORDER BY r.created_at ASC';
} else {
    $query .= ' ORDER BY r.created_at DESC';
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Get statistics
$statsQuery = '
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = "Processing" THEN 1 ELSE 0 END) as processing,
        SUM(CASE WHEN status = "Approved" THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = "Ready to Receive" THEN 1 ELSE 0 END) as ready,
        SUM(CASE WHEN status = "Received" THEN 1 ELSE 0 END) as received,
        SUM(CASE WHEN status = "Rejected" THEN 1 ELSE 0 END) as rejected
    FROM requests
';
$statsStmt = $pdo->prepare($statsQuery);
$statsStmt->execute();
$stats = $statsStmt->fetch();

// Function to generate 4-digit request number
function generateRequestNumber($id) {
    return str_pad($id % 10000, 4, '0', STR_PAD_LEFT);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SafeBrgy - Admin Requests</title>
  <link rel="icon" type="image/png" href="../../assets/img/seal.png">
  <!-- Shared Styles -->
  <link rel="stylesheet" href="../../assets/css/shared/shared-header.css">
  <link rel="stylesheet" href="../../assets/css/shared/shared_sidebar.css">
  <link rel="stylesheet" href="../../assets/css/shared/colors.css">
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="../../assets/css/admin/requests.css">
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
      <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> <span class="menu-label">Logout</span></a>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <div class="container-fluid p-4">
      <!-- Page Header -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="mb-2">Document Requests</h2>
          <p class="text-muted">Manage and process resident document requests</p>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="row mb-4">
        <div class="col-md-2">
          <div class="stat-card">
            <div class="stat-value"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total</div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="stat-card stat-card-pending">
            <div class="stat-value"><?php echo $stats['pending']; ?></div>
            <div class="stat-label">Pending</div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="stat-card stat-card-processing">
            <div class="stat-value"><?php echo $stats['processing']; ?></div>
            <div class="stat-label">Processing</div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="stat-card stat-card-approved">
            <div class="stat-value"><?php echo $stats['approved']; ?></div>
            <div class="stat-label">Approved</div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="stat-card stat-card-ready">
            <div class="stat-value"><?php echo $stats['ready']; ?></div>
            <div class="stat-label">Ready</div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="stat-card stat-card-received">
            <div class="stat-value"><?php echo $stats['received']; ?></div>
            <div class="stat-label">Received</div>
          </div>
        </div>
      </div>

      <!-- Search and Filter -->
      <div class="card mb-4">
        <div class="card-body">
          <form method="get" class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Search by Number or Name</label>
              <input type="text" name="search" class="form-control" placeholder="Search by request number, resident name..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="col-md-3">
              <label class="form-label">Filter by Status</label>
              <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="Processing" <?php echo $status === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                <option value="Approved" <?php echo $status === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                <option value="Ready to Receive" <?php echo $status === 'Ready to Receive' ? 'selected' : ''; ?>>Ready to Receive</option>
                <option value="Received" <?php echo $status === 'Received' ? 'selected' : ''; ?>>Received</option>
                <option value="Rejected" <?php echo $status === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label">Sort By</label>
              <select name="sort" class="form-select">
                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest</option>
              </select>
            </div>

            <div class="col-12">
              <button type="submit" class="btn btn-outline-primary">
                <i class="fas fa-search"></i> Search
              </button>
              <a href="requests.php" class="btn btn-outline-secondary">
                <i class="fas fa-redo"></i> Reset
              </a>
            </div>
          </form>
        </div>
      </div>

      <!-- Requests Table -->
      <div class="card">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th>Request #</th>
                <th>Resident Name</th>
                <th>Request Type</th>
                <th>Submitted Date</th>
                <th>Status</th>
                <th>Date Received</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="requestsTable">
              <?php if (count($requests) > 0): ?>
                <?php foreach ($requests as $req): ?>
                  <tr>
                    <td>
                      <strong>#<?php echo generateRequestNumber($req['id']); ?></strong>
                    </td>
                    <td>
                      <div>
                        <strong><?php echo htmlspecialchars(($req['first_name'] ?? '') . ' ' . ($req['last_name'] ?? '') ?: $req['username']); ?></strong>
                      </div>
                      <small class="text-muted"><?php echo htmlspecialchars($req['email']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($req['request_type']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($req['created_at'])); ?></td>
                    <td>
                      <span class="badge badge-status badge-status-<?php echo strtolower(str_replace(' ', '-', $req['status'])); ?>">
                        <?php echo htmlspecialchars($req['status']); ?>
                      </span>
                    </td>
                    <td>
                      <?php echo $req['date_received'] ? date('M d, Y', strtotime($req['date_received'])) : '-'; ?>
                    </td>
                    <td>
                      <button class="btn btn-sm btn-outline-info view-request-btn" 
                              data-id="<?php echo $req['id']; ?>"
                              data-request-number="<?php echo generateRequestNumber($req['id']); ?>"
                              data-name="<?php echo htmlspecialchars(($req['first_name'] ?? '') . ' ' . ($req['last_name'] ?? '') ?: $req['username']); ?>"
                              data-type="<?php echo htmlspecialchars($req['request_type']); ?>"
                              data-purpose="<?php echo htmlspecialchars($req['purpose'] ?? 'N/A'); ?>"
                              data-location="<?php echo htmlspecialchars($req['location'] ?? 'N/A'); ?>"
                              data-status="<?php echo htmlspecialchars($req['status']); ?>"
                              data-created="<?php echo date('M d, Y', strtotime($req['created_at'])); ?>"
                              data-phone="<?php echo htmlspecialchars($req['phone'] ?? 'N/A'); ?>"
                              data-email="<?php echo htmlspecialchars($req['email']); ?>">
                        <i class="fas fa-eye"></i> View
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="text-center text-muted py-4">
                    <i class="fas fa-inbox"></i> No requests found
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- View Request Modal -->
  <div class="modal fade" id="viewRequestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Request Details - <span id="modalRequestNumber">#0000</span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">Resident Name</label>
              <p id="modalResidentName">-</p>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Phone Number</label>
              <p id="modalPhoneNumber">-</p>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">Email</label>
              <p id="modalEmail">-</p>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Request Type</label>
              <p id="modalRequestType">-</p>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">Submitted Date</label>
              <p id="modalCreatedDate">-</p>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Purpose</label>
              <p id="modalPurpose">-</p>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label fw-bold">Location</label>
              <p id="modalLocation">-</p>
            </div>
          </div>
          <hr>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">Current Status</label>
              <p id="modalCurrentStatus">-</p>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Update Status</label>
              <select id="statusSelect" class="form-select">
                <option value="Pending">Pending</option>
                <option value="Processing">Processing</option>
                <option value="Approved">Approved</option>
                <option value="Ready to Receive">Ready to Receive</option>
                <option value="Received">Received</option>
                <option value="Rejected">Rejected</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="updateStatusBtn">
            <i class="fas fa-save"></i> Update Status
          </button>
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
<script src="../../assets/js/admin/requests.js"></script>
</body>
</html>
