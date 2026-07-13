<?php
require_once __DIR__ . '/../admin_protect.php';

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
$response = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    
    if ($action === 'update_status') {
        $requestId = $_POST['request_id'] ?? 0;
        $newStatus = $_POST['status'] ?? '';
        $dateReceived = ($newStatus === 'Received') ? date('Y-m-d H:i:s') : null;
        
        $stmt = $pdo->prepare('UPDATE requests SET status = ?, updated_at = NOW() WHERE id = ?');
        $result = $stmt->execute([$newStatus, $requestId]);
        
        echo json_encode(['success' => $result]);
        exit;
    }
}

// Get filter and search parameters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$requestsColumns = $pdo->query('SHOW COLUMNS FROM requests')->fetchAll(PDO::FETCH_COLUMN);
$requestNumberColumn = in_array('request_number', $requestsColumns, true) ? 'request_number' : (in_array('reference_no', $requestsColumns, true) ? 'reference_no' : 'id');
$requestTypeColumn = in_array('request_type', $requestsColumns, true) ? 'request_type' : (in_array('document_type', $requestsColumns, true) ? 'document_type' : null);
$createdAtColumn = in_array('created_at', $requestsColumns, true) ? 'created_at' : (in_array('submitted_at', $requestsColumns, true) ? 'submitted_at' : 'id');
$dateReceivedColumn = in_array('date_received', $requestsColumns, true) ? 'date_received' : null;
$documentDataColumn = in_array('document_data', $requestsColumns, true) ? 'document_data' : null;
$purposeColumn = in_array('purpose', $requestsColumns, true) ? 'purpose' : null;
$locationColumn = in_array('location', $requestsColumns, true) ? 'location' : null;
$hasUserIdColumn = in_array('user_id', $requestsColumns, true);

$selectParts = [
    'r.id',
    "r.$requestNumberColumn as request_number",
    'r.status',
    $requestTypeColumn ? "r.$requestTypeColumn as request_type" : 'NULL as request_type',
    $createdAtColumn ? "r.$createdAtColumn as created_at" : 'NULL as created_at',
    $dateReceivedColumn ? "r.$dateReceivedColumn as date_received" : 'NULL as date_received',
    $documentDataColumn ? "r.$documentDataColumn as document_data" : 'NULL as document_data',
    $purposeColumn ? "r.$purposeColumn as purpose" : 'NULL as purpose',
    $locationColumn ? "r.$locationColumn as location" : 'NULL as location',
    'u.username',
    'u.email',
    'u.phone',
    'u.id as user_id',
    'res.resident_id',
    'res.first_name',
    'res.last_name',
    'res.birthdate',
    'res.age',
    'res.gender',
    'res.civil_status',
    'res.complete_address',
    'res.purok',
    'res.mobile_number',
    'res.valid_id_path'
];

// Build query
$query = 'SELECT ' . implode(', ', $selectParts) . '
    FROM requests r
    LEFT JOIN users u ON ' . ($hasUserIdColumn ? 'r.user_id = u.id' : 'u.email = r.resident_email') . '
    LEFT JOIN residents res ON u.id = res.user_id
    WHERE 1=1
';

$params = [];

if ($search) {
    $query .= ' AND (r.' . $requestNumberColumn . ' LIKE ? OR res.first_name LIKE ? OR res.last_name LIKE ? OR u.username LIKE ? OR u.email LIKE ?)';
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
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
    $query .= ' ORDER BY r.' . $createdAtColumn . ' ASC';
} else {
    $query .= ' ORDER BY r.' . $createdAtColumn . ' DESC';
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Get statistics
$statsQuery = '
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = "Approved" THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = "Processing" THEN 1 ELSE 0 END) as processing,
        SUM(CASE WHEN status = "Received" THEN 1 ELSE 0 END) as received
    FROM requests
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
          <h2 class="mb-2">Resident Requests</h2>
          <p class="text-muted">Manage and process resident requests for documents and services</p>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="stat-card">
            <div class="stat-value"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total Requests</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card">
            <div class="stat-value"><?php echo $stats['pending']; ?></div>
            <div class="stat-label">Pending</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card">
            <div class="stat-value"><?php echo $stats['processing']; ?></div>
            <div class="stat-label">Processing</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card">
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
              <label class="form-label">Search by Name, Email, or Request Number</label>
              <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="col-md-3">
              <label class="form-label">Filter by Status</label>
              <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="Approved" <?php echo $status === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                <option value="Processing" <?php echo $status === 'Processing' ? 'selected' : ''; ?>>Processing</option>
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
          <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th>Request #</th>
                <th>Resident Name</th>
                <th>Request Type</th>
                <th>Submitted</th>
                <th>Status</th>
                <th>Date Received</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($requests)): ?>
                <tr>
                  <td colspan="7" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                    No requests found
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($requests as $req): ?>
                  <tr>
                    <td>
                      <strong><?php echo htmlspecialchars($req['request_number'] ?? 'N/A'); ?></strong>
                    </td>
                    <td>
                      <div>
                        <strong><?php echo htmlspecialchars(trim(($req['first_name'] ?? '') . ' ' . ($req['last_name'] ?? '')) ?: $req['username']); ?></strong>
                        <br>
                        <small class="text-muted"><?php echo htmlspecialchars($req['email']); ?></small>
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-info"><?php echo htmlspecialchars($req['request_type']); ?></span>
                    </td>
                    <td>
                      <small><?php echo date('M d, Y', strtotime($req['created_at'])); ?></small>
                    </td>
                    <td>
                      <span class="badge bg-<?php 
                        echo match($req['status']) {
                          'Pending' => 'warning',
                          'Approved' => 'info',
                          'Processing' => 'primary',
                          'Ready to Receive' => 'success',
                          'Received' => 'success',
                          'Rejected' => 'danger',
                          default => 'secondary'
                        };
                      ?>">
                        <?php echo htmlspecialchars($req['status']); ?>
                      </span>
                    </td>
                    <td>
                      <small><?php echo $req['date_received'] ? date('M d, Y', strtotime($req['date_received'])) : 'N/A'; ?></small>
                    </td>
                    <td>
                      <button type="button" class="btn btn-sm btn-outline-primary view-btn" data-bs-toggle="modal" data-bs-target="#viewRequestModal<?php echo $req['id']; ?>" title="View Details">
                        <i class="fas fa-eye"></i> View
                      </button>
                    </td>
                  </tr>

                  <!-- VIEW REQUEST MODAL -->
                  <div class="modal fade" id="viewRequestModal<?php echo $req['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-xl">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title">Request Details - <?php echo htmlspecialchars($req['request_number'] ?? 'N/A'); ?></h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          <!-- Applicant Information -->
                          <h6 class="mb-3 text-primary"><i class="fas fa-user-circle"></i> Applicant Information</h6>
                          <div class="row mb-4">
                            <div class="col-md-6">
                              <strong>Resident ID:</strong> 
                              <p><?php echo htmlspecialchars($req['resident_id'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-md-6">
                              <strong>Full Name:</strong> 
                              <p><?php echo htmlspecialchars(trim(($req['first_name'] ?? '') . ' ' . ($req['last_name'] ?? '')) ?: $req['username']); ?></p>
                            </div>
                          </div>
                          <div class="row mb-4">
                            <div class="col-md-6">
                              <strong>Age:</strong> 
                              <p><?php echo htmlspecialchars($req['age'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-md-6">
                            </div>
                          </div>
                          <div class="row mb-4">
                            <div class="col-md-6">
                              <strong>Date of Birth:</strong> 
                              <p><?php echo $req['birthdate'] ? date('M d, Y', strtotime($req['birthdate'])) : 'N/A'; ?></p>
                            </div>
                            <div class="col-md-6">
                              <strong>Gender:</strong> 
                              <p><?php echo htmlspecialchars($req['gender'] ?? 'N/A'); ?></p>
                            </div>
                          </div>
                          <div class="row mb-4">
                            <div class="col-md-6">
                              <strong>Civil Status:</strong> 
                              <p><?php echo htmlspecialchars($req['civil_status'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-md-6">
                              <strong>Contact Number:</strong> 
                              <p><?php echo htmlspecialchars($req['mobile_number'] ?? $req['phone'] ?? 'N/A'); ?></p>
                            </div>
                          </div>
                          <div class="row mb-4">
                            <div class="col-md-6">
                              <strong>House Number/Street/Purok:</strong> 
                              <p><?php echo htmlspecialchars($req['complete_address'] ?? '') . ($req['purok'] ? ', ' . htmlspecialchars($req['purok']) : ''); ?></p>
                            </div>
                            <div class="col-md-6">
                              <strong>Email:</strong> 
                              <p><?php echo htmlspecialchars($req['email']); ?></p>
                            </div>
                          </div>
                          <div class="row mb-4">
                            <div class="col-md-6">
                              <strong>Valid ID:</strong>
                              <p>
                                <?php if ($req['valid_id_path']): ?>
                                  <a href="../../<?php echo htmlspecialchars($req['valid_id_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-image"></i> View ID
                                  </a>
                                <?php else: ?>
                                  <span class="text-muted">Not uploaded</span>
                                <?php endif; ?>
                              </p>
                            </div>
                          </div>

                          <hr>

                          <!-- Request Details -->
                          <h6 class="mb-3 text-primary"><i class="fas fa-clipboard-list"></i> Request Details</h6>
                          <div class="row mb-4">
                            <div class="col-md-6">
                              <strong>Request Number:</strong> 
                              <p><?php echo htmlspecialchars($req['request_number'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-md-6">
                              <strong>Request Type:</strong> 
                              <p><span class="badge bg-info"><?php echo htmlspecialchars($req['request_type']); ?></span></p>
                            </div>
                          </div>
                          <div class="row mb-4">
                            <div class="col-md-6">
                              <strong>Date Requested:</strong> 
                              <p><?php echo date('M d, Y H:i A', strtotime($req['created_at'])); ?></p>
                            </div>
                            <div class="col-md-6">
                              <strong>Purpose of Request:</strong> 
                              <p><?php echo htmlspecialchars($req['purpose'] ?? 'N/A'); ?></p>
                            </div>
                          </div>
                          
                          <?php 
                          $doc_data = json_decode($req['document_data'], true);
                          if ($doc_data && !empty($doc_data)):
                          ?>
                          <hr>
                          <h6 class="mb-3 text-primary"><i class="fas fa-file-alt"></i> Document Information</h6>
                          <?php foreach ($doc_data as $key => $value): ?>
                            <?php if ($key !== 'purpose' && $key !== 'business_logo'): ?>
                              <div class="row mb-2">
                                <div class="col-md-6">
                                  <strong><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?>:</strong>
                                </div>
                                <div class="col-md-6">
                                  <p><?php echo htmlspecialchars(is_array($value) ? implode(', ', $value) : $value); ?></p>
                                </div>
                              </div>
                            <?php endif; ?>
                          <?php endforeach; ?>
                          <?php endif; ?>

                          <hr>

                          <!-- Status Update -->
                          <h6 class="mb-3 text-primary"><i class="fas fa-tasks"></i> Update Status</h6>
                          <div class="mb-3">
                            <label class="form-label">Current Status: <strong><?php echo htmlspecialchars($req['status']); ?></strong></label>
                            <select class="form-select status-select" data-request-id="<?php echo $req['id']; ?>">
                              <option value="">-- Select New Status --</option>
                              <option value="Pending" <?php echo $req['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                              <option value="Approved" <?php echo $req['status'] === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                              <option value="Processing" <?php echo $req['status'] === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                              <option value="Ready to Receive" <?php echo $req['status'] === 'Ready to Receive' ? 'selected' : ''; ?>>Ready to Receive</option>
                              <option value="Received" <?php echo $req['status'] === 'Received' ? 'selected' : ''; ?>>Received</option>
                              <option value="Rejected" <?php echo $req['status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                          </div>
                          <?php if ($req['date_received']): ?>
                            <div class="alert alert-info">
                              <strong>Date Received:</strong> <?php echo date('M d, Y H:i A', strtotime($req['date_received'])); ?>
                            </div>
                          <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="button" class="btn btn-primary update-status-btn" data-request-id="<?php echo $req['id']; ?>">Update Status</button>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Shared JS -->
  <script src="../../assets/js/shared/shared-header.js"></script>
  <script src="../../assets/js/shared/shared-sidebar.js"></script>
  <!-- Page-specific JS -->
  <script src="../../assets/js/admin/requests.js"></script>
</body>
</html>
