<?php
require_once __DIR__ . '/../../config/db.php';
// profile.php - SafeBrgy Resident Profile
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
$email = $user['email'] ?? '';
$phone = $user['phone'] ?? '';
$residentEmail = $user['email'] ?? '';

// Get resident detailed information
$residentData = null;
$profileImage = null;
$validIdPath = null;
$isVerified = false;

if ($userId) {
    $stmt = $pdo->prepare('
        SELECT r.*, u.is_verified, u.profile_image 
        FROM residents r 
        LEFT JOIN users u ON r.user_id = u.id 
        WHERE r.user_id = ?
    ');
    $stmt->execute([$userId]);
    $residentData = $stmt->fetch();
    
    if ($residentData) {
        $profileImage = $residentData['profile_image_path'] ?? $residentData['profile_image'];
        $validIdPath = $residentData['valid_id_path'];
        $isVerified = (bool) $residentData['is_verified'];
    }
}

// Get requested documents history
$documentsHistory = [];
if ($residentEmail !== '') {
    $stmt = $pdo->prepare(
        'SELECT id, document_type AS request_type, submitted_at AS created_at, status
           FROM requests
          WHERE resident_email = ?
          ORDER BY submitted_at DESC
          LIMIT 10'
    );
    $stmt->execute([$residentEmail]);
    $documentsHistory = $stmt->fetchAll();
}

// Helper function to calculate age
function calculateAge($birthdate) {
    if (!$birthdate) return null;
    $birthDate = new DateTime($birthdate);
    $today = new DateTime('today');
    $age = $birthDate->diff($today);
    return [
        'years' => $age->y,
        'months' => $age->m,
        'days' => $age->d
    ];
}

// Helper function to format date
function formatDate($date) {
    if (!$date) return 'N/A';
    return date('F d, Y', strtotime($date));
}

// Helper function to get status badge class
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'Pending':
            return 'badge-warning';
        case 'Approved':
            return 'badge-success';
        case 'Processing':
            return 'badge-info';
        case 'Ready for Pickup':
        case 'Ready to Receive':
            return 'badge-success';
        case 'Received':
            return 'badge-secondary';
        case 'Rejected':
            return 'badge-danger';
        default:
            return 'badge-secondary';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SafeBrgy - Profile</title>
  <link rel="icon" type="image/png" href="../../assets/img/seal.png">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- Shared Styles -->
  <link rel="stylesheet" href="../../assets/css/shared/shared-header.css">
  <link rel="stylesheet" href="../../assets/css/shared/shared_sidebar.css">
  <link rel="stylesheet" href="../../assets/css/shared/colors.css">
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="../../assets/css/public/profile.css">
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
        <div class="profile-avatar"><?php echo substr($name, 0, 1); ?></div>
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

      <!-- ===== PROFILE HEADER SECTION ===== -->
      <div class="profile-header mb-5">
        <div class="profile-cover">
          <div class="cover-placeholder"></div>
        </div>
        
        <div class="profile-header-content">
          <div class="profile-pic-container">
            <?php if ($profileImage): ?>
              <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile" class="profile-picture">
            <?php else: ?>
              <div class="profile-avatar-large">
                <?php echo substr($name, 0, 1); ?>
              </div>
            <?php endif; ?>
          </div>

          <div class="profile-header-info">
            <h1 class="profile-full-name"><?php echo htmlspecialchars($name); ?></h1>
            <p class="profile-resident-id">
              <i class="fas fa-id-card"></i> 
              Resident ID: <strong><?php echo htmlspecialchars($residentData['resident_id'] ?? 'N/A'); ?></strong>
            </p>
            <div class="profile-actions">
              <a href="account.php" class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> Edit Profile
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== PERSONAL INFORMATION SECTION ===== -->
      <section class="profile-section mb-5">
        <div class="section-header">
          <h3 class="section-title"><i class="fas fa-user-circle"></i> Personal Information</h3>
        </div>
        <div class="section-content">
          <div class="row">
            <div class="col-md-6 col-lg-3 mb-4">
              <div class="info-item">
                <label class="info-label">First Name</label>
                <p class="info-value"><?php echo htmlspecialchars($residentData['first_name'] ?? 'N/A'); ?></p>
              </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
              <div class="info-item">
                <label class="info-label">Middle Name</label>
                <p class="info-value"><?php echo htmlspecialchars($residentData['middle_name'] ?? 'N/A'); ?></p>
              </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
              <div class="info-item">
                <label class="info-label">Last Name</label>
                <p class="info-value"><?php echo htmlspecialchars($residentData['last_name'] ?? 'N/A'); ?></p>
              </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
              <div class="info-item">
                <label class="info-label">Gender</label>
                <p class="info-value"><?php echo htmlspecialchars($residentData['gender'] ?? 'N/A'); ?></p>
              </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
              <div class="info-item">
                <label class="info-label">Date of Birth</label>
                <p class="info-value"><?php echo formatDate($residentData['birthdate']); ?></p>
              </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
              <div class="info-item">
                <label class="info-label">Age</label>
                <p class="info-value">
                  <?php 
                    $ageCalc = calculateAge($residentData['birthdate']);
                    if ($ageCalc) {
                        echo $ageCalc['years'] . ' years, ' . $ageCalc['months'] . ' months, ' . $ageCalc['days'] . ' days';
                    } else {
                        echo 'N/A';
                    }
                  ?>
                </p>
              </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
              <div class="info-item">
                <label class="info-label">Civil Status</label>
                <p class="info-value"><?php echo htmlspecialchars($residentData['civil_status'] ?? 'N/A'); ?></p>
              </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
              <div class="info-item">
                <label class="info-label">Nationality</label>
                <p class="info-value"><?php echo htmlspecialchars($residentData['nationality'] ?? 'N/A'); ?></p>
              </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
              <div class="info-item">
                <label class="info-label">Occupation</label>
                <p class="info-value"><?php echo htmlspecialchars($residentData['occupation'] ?? 'N/A'); ?></p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ===== CONTACT INFORMATION SECTION ===== -->
      <section class="profile-section mb-5">
        <div class="section-header">
          <h3 class="section-title"><i class="fas fa-phone-alt"></i> Contact Information</h3>
        </div>
        <div class="section-content">
          <div class="row">
            <div class="col-md-6 mb-4">
              <div class="info-item">
                <label class="info-label"><i class="fas fa-phone"></i> Mobile Number</label>
                <p class="info-value"><?php echo htmlspecialchars($residentData['mobile_number'] ?? 'N/A'); ?></p>
              </div>
            </div>
            <div class="col-md-6 mb-4">
              <div class="info-item">
                <label class="info-label"><i class="fas fa-envelope"></i> Email Address</label>
                <p class="info-value"><?php echo htmlspecialchars($email); ?></p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ===== IDENTIFICATION SECTION ===== -->
      <section class="profile-section mb-5">
        <div class="section-header">
          <h3 class="section-title"><i class="fas fa-passport"></i> Identification</h3>
        </div>
        <div class="section-content">
          <div class="row align-items-center">
            <div class="col-md-6 mb-4">
              <div class="info-item">
                <label class="info-label">Valid ID</label>
                <?php if ($validIdPath): ?>
                  <div class="id-preview-wrapper">
                    <img src="<?php echo htmlspecialchars($validIdPath); ?>" alt="Valid ID" class="id-preview" onclick="openImageModal(this)">
                    <p class="text-muted mt-2"><small><i class="fas fa-image"></i> Click to view full image</small></p>
                  </div>
                <?php else: ?>
                  <p class="info-value text-muted">No ID uploaded</p>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-6 mb-4">
              <div class="info-item">
                <label class="info-label">Verification Status</label>
                <div class="verification-status">
                  <?php if ($isVerified): ?>
                    <span class="badge badge-success">
                      <i class="fas fa-check-circle"></i> Verified
                    </span>
                  <?php else: ?>
                    <span class="badge badge-warning">
                      <i class="fas fa-clock"></i> Pending Verification
                    </span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ===== REQUESTED DOCUMENTS HISTORY SECTION ===== -->
      <section class="profile-section mb-5">
        <div class="section-header">
          <h3 class="section-title"><i class="fas fa-file-alt"></i> Requested Documents History</h3>
        </div>
        <div class="section-content">
          <?php if (!empty($documentsHistory)): ?>
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Document Type</th>
                    <th>Request Date</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($documentsHistory as $doc): ?>
                    <tr>
                      <td>
                        <div class="document-type">
                          <i class="fas fa-file-pdf"></i>
                          <?php echo htmlspecialchars($doc['request_type']); ?>
                        </div>
                      </td>
                      <td>
                        <span class="request-date"><?php echo formatDate($doc['created_at']); ?></span>
                      </td>
                      <td>
                        <span class="badge <?php echo getStatusBadgeClass($doc['status']); ?>">
                          <?php echo htmlspecialchars($doc['status']); ?>
                        </span>
                      </td>
                      <td>
                        <?php if ($doc['status'] === 'Ready to Receive' || $doc['status'] === 'Received'): ?>
                          <a href="../../api/requests/download.php?id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-primary" title="Download">
                            <i class="fas fa-download"></i>
                          </a>
                        <?php else: ?>
                          <button class="btn btn-sm btn-secondary" disabled title="Not available">
                            <i class="fas fa-download"></i>
                          </button>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="empty-state">
              <i class="fas fa-inbox"></i>
              <p>No requested documents yet</p>
              <a href="requests.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Create Request
              </a>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <!-- ===== LOGOUT BUTTON ===== -->
      <div class="profile-footer">
        <a href="../../public/logout.php" class="btn btn-danger">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>

    </div>
  </main>

  <!-- ===== IMAGE MODAL ===== -->
  <div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Valid ID Preview</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
          <img id="modalImage" src="" alt="ID Preview" class="img-fluid">
        </div>
      </div>
    </div>
  </div>

<!-- Shared JS -->
<script src="../../assets/js/shared/logo_functions.js"></script>
<script src="../../assets/js/shared/shared-header.js"></script>
<script src="../../assets/js/shared/shared-sidebar.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/shared/layout_functions.js"></script>
<!-- Page-specific JS -->
<script src="../../assets/js/public/profile.js"></script>
</body>
</html>
