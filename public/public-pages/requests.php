<?php
require_once __DIR__ . '/../../config/db.php';
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

// Get resident data for form pre-fill
$residentData = null;
if ($userId) {
    $stmt = $pdo->prepare('SELECT * FROM residents WHERE user_id = ?');
    $stmt->execute([$userId]);
    $residentData = $stmt->fetch();
}

// Get all requests for this user with pagination
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
$limit = 10;
$offset = ($page - 1) * $limit;

$query = 'SELECT * FROM requests WHERE user_id = ?';
$params = [$userId];

if ($search) {
    $query .= ' AND (request_number LIKE ? OR request_type LIKE ?)';
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$query .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
$params[] = $limit;
$params[] = $offset;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Get total count for pagination
$countQuery = 'SELECT COUNT(*) as count FROM requests WHERE user_id = ?';
$countParams = [$userId];

if ($search) {
    $countQuery .= ' AND (request_number LIKE ? OR request_type LIKE ?)';
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
}

$stmt = $pdo->prepare($countQuery);
$stmt->execute($countParams);
$totalRequests = $stmt->fetch()['count'];
$totalPages = ceil($totalRequests / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SafeBrgy - My Requests</title>
  <link rel="icon" type="image/png" href="../../assets/img/seal.png">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- Shared Styles -->
  <link rel="stylesheet" href="../../assets/css/shared/shared-header.css">
  <link rel="stylesheet" href="../../assets/css/shared/shared_sidebar.css">
  <link rel="stylesheet" href="../../assets/css/shared/colors.css">
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="../../assets/css/public/requests.css">
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
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2>My Requests</h2>
          <p class="text-muted">Manage your document requests</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestModal">
          <i class="fas fa-plus"></i> Request Now
        </button>
      </div>

      <!-- Search -->
      <div class="card mb-4">
        <div class="card-body">
          <form method="get" class="d-flex gap-2">
            <input type="text" name="search" class="form-control" placeholder="Search by request number or type..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-outline-primary">
              <i class="fas fa-search"></i> Search
            </button>
            <?php if ($search): ?>
              <a href="requests.php" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i> Clear
              </a>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <!-- Requests Table -->
      <div class="card">
        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th>Request #</th>
                <th>Document Type</th>
                <th>Submitted</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($requests)): ?>
                <tr>
                  <td colspan="5" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                    No requests found
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($requests as $req): ?>
                  <tr>
                    <td>
                      <strong><?php echo htmlspecialchars($req['request_number']); ?></strong>
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
                      <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewRequestModal<?php echo $req['id']; ?>">
                        <i class="fas fa-eye"></i> View
                      </button>
                    </td>
                  </tr>

                  <!-- VIEW REQUEST MODAL -->
                  <div class="modal fade" id="viewRequestModal<?php echo $req['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title"><?php echo htmlspecialchars($req['request_number']); ?></h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          <h6 class="mb-3">Request Information</h6>
                          <div class="row mb-3">
                            <div class="col-md-6">
                              <strong>Document Type:</strong>
                              <p><?php echo htmlspecialchars($req['request_type']); ?></p>
                            </div>
                            <div class="col-md-6">
                              <strong>Status:</strong>
                              <p>
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
                              </p>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-md-6">
                              <strong>Submitted Date:</strong>
                              <p><?php echo date('M d, Y H:i A', strtotime($req['created_at'])); ?></p>
                            </div>
                            <div class="col-md-6">
                              <strong>Purpose:</strong>
                              <p><?php echo htmlspecialchars($req['purpose']); ?></p>
                            </div>
                          </div>
                          
                          <?php 
                          $doc_data = json_decode($req['document_data'], true);
                          if ($doc_data):
                          ?>
                          <hr>
                          <h6 class="mb-3">Document Details</h6>
                          <?php foreach ($doc_data as $key => $value): ?>
                            <?php if ($key !== 'purpose'): ?>
                              <div class="mb-2">
                                <strong><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?>:</strong>
                                <p><?php echo htmlspecialchars(is_array($value) ? implode(', ', $value) : $value); ?></p>
                              </div>
                            <?php endif; ?>
                          <?php endforeach; ?>
                          <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
          <ul class="pagination justify-content-end">
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
              <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
              </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
              <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next</a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>
    </div>
  </main>

  <!-- REQUEST MODAL (3-step form) -->
  <div class="modal fade" id="requestModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">New Request</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeRequestModal"></button>
        </div>
        <div class="modal-body">
          <!-- STEP 1: Document Type Selection -->
          <div id="step1" class="request-step">
            <h6 class="mb-4">Select Document Type</h6>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="document-card" data-type="Barangay Clearance">
                  <div class="card h-100 border-2" style="cursor: pointer;">
                    <div class="card-body text-center">
                      <i class="fas fa-certificate fa-2x mb-3" style="color: #4CAF50;"></i>
                      <h6>Barangay Clearance</h6>
                      <small class="text-muted">Certificate of good moral character</small>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="document-card" data-type="Barangay Residency">
                  <div class="card h-100 border-2" style="cursor: pointer;">
                    <div class="card-body text-center">
                      <i class="fas fa-home fa-2x mb-3" style="color: #2196F3;"></i>
                      <h6>Barangay Residency</h6>
                      <small class="text-muted">Proof of residency in barangay</small>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="document-card" data-type="Barangay Indigency">
                  <div class="card h-100 border-2" style="cursor: pointer;">
                    <div class="card-body text-center">
                      <i class="fas fa-hand-holding-usd fa-2x mb-3" style="color: #FF9800;"></i>
                      <h6>Barangay Indigency</h6>
                      <small class="text-muted">Certificate of indigency</small>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="document-card" data-type="Barangay Business Clearance">
                  <div class="card h-100 border-2" style="cursor: pointer;">
                    <div class="card-body text-center">
                      <i class="fas fa-store fa-2x mb-3" style="color: #9C27B0;"></i>
                      <h6>Business Clearance</h6>
                      <small class="text-muted">Certificate for business operations</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- STEP 2: Document Details Form -->
          <div id="step2" class="request-step" style="display: none;">
            <div class="step-header mb-4">
              <button type="button" class="btn btn-sm btn-outline-secondary" id="backStep1">
                <i class="fas fa-arrow-left"></i> Back
              </button>
              <h6 class="mb-0" style="display: inline-block; margin-left: 1rem;" id="step2Title"></h6>
            </div>

            <form id="requestForm" enctype="multipart/form-data">
              <input type="hidden" name="request_type" id="requestType">

              <!-- Barangay Clearance Form -->
              <div id="form-Barangay Clearance" class="form-section" style="display: none;">
                <div class="mb-3">
                  <label class="form-label">Purpose of Request <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="purpose" placeholder="Enter purpose" required>
                </div>
              </div>

              <!-- Barangay Residency Form -->
              <div id="form-Barangay Residency" class="form-section" style="display: none;">
                <div class="mb-3">
                  <label class="form-label">Years of Residency <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" name="years_residency" min="1" placeholder="Enter years" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Date Started Living in Barangay <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" name="date_started_living" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Purpose of Request <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="purpose" placeholder="Enter purpose" required>
                </div>
              </div>

              <!-- Barangay Indigency Form -->
              <div id="form-Barangay Indigency" class="form-section" style="display: none;">
                <div class="mb-3">
                  <label class="form-label">Monthly Income <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" name="monthly_income" min="0" step="0.01" placeholder="Enter monthly income" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Number of Household Members <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" name="household_members" min="1" placeholder="Enter number" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Purpose of Request <span class="text-danger">*</span></label>
                  <select class="form-select" name="indigency_purpose" id="indigencyPurpose" required>
                    <option value="">Select purpose</option>
                    <option value="Medical Assistance">Medical Assistance</option>
                    <option value="Educational Assistance">Educational Assistance</option>
                    <option value="Financial Assistance">Financial Assistance</option>
                    <option value="Burial Assistance">Burial Assistance</option>
                    <option value="Others">Others</option>
                  </select>
                </div>
                <div class="mb-3" id="othersReasonDiv" style="display: none;">
                  <label class="form-label">Please Specify <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="others_reason" placeholder="Enter reason">
                </div>
                <div class="mb-3">
                  <label class="form-label">Purpose of Request Text <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="purpose" placeholder="Enter purpose" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Supporting Documents (Optional)</label>
                  <input type="file" class="form-control" name="documents[]" multiple accept=".pdf,.jpg,.jpeg,.png">
                  <small class="text-muted">Accepted: PDF, JPG, PNG (max 5MB each)</small>
                </div>
                <div class="mb-3">
                  <label class="form-label">Images (Optional)</label>
                  <input type="file" class="form-control" name="images[]" multiple accept=".jpg,.jpeg,.png">
                  <small class="text-muted">Accepted: JPG, PNG (max 5MB each)</small>
                </div>
              </div>

              <!-- Business Clearance Form -->
              <div id="form-Barangay Business Clearance" class="form-section" style="display: none;">
                <div class="mb-3">
                  <label class="form-label">Business Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="business_name" placeholder="Enter business name" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Business Logo (Optional)</label>
                  <input type="file" class="form-control" name="business_logo" accept=".jpg,.jpeg,.png,.webp">
                  <small class="text-muted">Accepted: JPG, PNG, WebP (max 5MB)</small>
                </div>
                <div class="mb-3">
                  <label class="form-label">Business Description (Optional)</label>
                  <textarea class="form-control" name="business_description" rows="3" placeholder="Brief description of business"></textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label">Business Full Address <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="business_address" placeholder="Enter complete address" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                  <input type="tel" class="form-control" name="contact_number" placeholder="Enter contact number" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">TIN (Optional)</label>
                  <input type="text" class="form-control" name="tin" placeholder="Tax Identification Number">
                </div>
                <div class="mb-3">
                  <label class="form-label">Business Started <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" name="business_started" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Purpose of Request <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="purpose" placeholder="Enter purpose" required>
                </div>
              </div>

              <div class="mt-4 d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">Submit Request</button>
              </div>
            </form>
          </div>

          <!-- STEP 3: Confirmation -->
          <div id="step3" class="request-step" style="display: none;">
            <div class="text-center">
              <i class="fas fa-check-circle" style="font-size: 4rem; color: #4CAF50; margin-bottom: 1rem;"></i>
              <h5>Request Submitted Successfully</h5>
              <p class="text-muted mb-4">Your request has been submitted and is pending review</p>
              
              <div class="alert alert-light border p-4 mb-4">
                <div class="row">
                  <div class="col-md-6">
                    <strong>Reference Number:</strong>
                    <p id="confirmRefNo" style="font-size: 1.2rem; font-weight: bold;"></p>
                  </div>
                  <div class="col-md-6">
                    <strong>Date Requested:</strong>
                    <p id="confirmDate" style="font-size: 1rem;"></p>
                  </div>
                </div>
              </div>

              <div class="alert alert-info">
                <p id="confirmMessage" class="mb-0"></p>
              </div>

              <p class="text-muted small">You will receive email updates about your request status</p>
            </div>

            <div class="d-flex justify-content-center gap-2 mt-4">
              <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="location.reload()">
                Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Shared JS -->
  <script src="../../assets/js/shared/shared-header.js"></script>
  <script src="../../assets/js/shared/shared-sidebar.js"></script>
  <!-- Page-specific JS -->
  <script src="../../assets/js/public/requests.js"></script>
</body>
</html>
