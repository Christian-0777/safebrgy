<?php
require_once __DIR__ . '/../../config/db.php';
// account.php - SafeBrgy Account Settings
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

// Get resident detailed information
$residentData = null;
$profileImage = null;
$validIdPath = null;

if ($userId) {
    $stmt = $pdo->prepare('
        SELECT r.*, u.is_verified, u.profile_image, u.created_at
        FROM residents r 
        LEFT JOIN users u ON r.user_id = u.id 
        WHERE r.user_id = ?
    ');
    $stmt->execute([$userId]);
    $residentData = $stmt->fetch();
    
    if ($residentData) {
        $profileImage = $residentData['profile_image_path'] ?? $residentData['profile_image'];
        $validIdPath = $residentData['valid_id_path'];
    }
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

// Helper function to format birthdate
function formatBirthdate($date) {
    if (!$date) return 'Not set';
    return date('F d, Y', strtotime($date));
}

// Prepare data for display
$firstName = $residentData['first_name'] ?? '';
$middleName = $residentData['middle_name'] ?? '';
$lastName = $residentData['last_name'] ?? '';
$birthdate = $residentData['birthdate'] ?? '';
$gender = $residentData['gender'] ?? '';
$civilStatus = $residentData['civil_status'] ?? '';
$nationality = $residentData['nationality'] ?? '';
$occupation = $residentData['occupation'] ?? '';
$mobileNumber = $residentData['mobile_number'] ?? '';
$emergencyContact = $residentData['emergency_contact_name'] ?? '';
$emergencyPhone = $residentData['emergency_contact_number'] ?? '';

$age = calculateAge($birthdate);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SafeBrgy - Account Settings</title>
  <link rel="icon" type="image/png" href="../../assets/img/seal.png">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- Shared Styles -->
  <link rel="stylesheet" href="../../assets/css/shared/shared-header.css">
  <link rel="stylesheet" href="../../assets/css/shared/shared_sidebar.css">
  <link rel="stylesheet" href="../../assets/css/shared/colors.css">
  <link rel="stylesheet" href="../../assets/css/shared/layout.css">
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="../../assets/css/public/account.css">
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
    <div class="container-fluid">
      
      <!-- Page Header -->
      <div class="settings-header mb-4">
        <h1><i class="fas fa-cog"></i> Account Settings</h1>
        <p class="text-muted">Manage your personal information, security, and preferences</p>
      </div>

      <!-- Alert Messages -->
      <?php
      if (isset($_SESSION['account_success'])) {
          echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                  <i class="fas fa-check-circle me-2"></i>' . htmlspecialchars($_SESSION['account_success']) . '
                  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
          unset($_SESSION['account_success']);
      }
      if (isset($_SESSION['account_error'])) {
          echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <i class="fas fa-exclamation-circle me-2"></i>' . htmlspecialchars($_SESSION['account_error']) . '
                  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
          unset($_SESSION['account_error']);
      }
      ?>

      <!-- Settings Navigation Tabs -->
      <div class="settings-tabs mb-4">
        <button class="settings-tab active" data-tab="personal">
          <i class="fas fa-user-circle"></i> Personal Info
        </button>
        <button class="settings-tab" data-tab="contact">
          <i class="fas fa-phone"></i> Contact Info
        </button>
        <button class="settings-tab" data-tab="identification">
          <i class="fas fa-id-card"></i> ID
        </button>
        <button class="settings-tab" data-tab="security">
          <i class="fas fa-shield-alt"></i> Security
        </button>
        <button class="settings-tab" data-tab="notifications">
          <i class="fas fa-bell"></i> Notifications
        </button>
        <button class="settings-tab" data-tab="privacy">
          <i class="fas fa-lock"></i> Privacy
        </button>
        <button class="settings-tab" data-tab="support">
          <i class="fas fa-headset"></i> Support
        </button>
        <button class="settings-tab" data-tab="about">
          <i class="fas fa-info-circle"></i> About
        </button>
        <button class="settings-tab" data-tab="danger">
          <i class="fas fa-exclamation-triangle"></i> Danger Zone
        </button>
      </div>

      <!-- ========================================
           1. PERSONAL INFORMATION SECTION
           ======================================== -->
      <div class="settings-content" id="personal-tab">
        <div class="settings-card">
          <div class="section-header">
            <h2 class="section-title"><i class="fas fa-user-circle"></i> Personal Information</h2>
          </div>
          <div class="section-content">
            
            <!-- Profile Picture & Cover Photo -->
            <div class="profile-media-section mb-5">
              <h5 class="mb-3"><i class="fas fa-image"></i> Profile Media</h5>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="media-item">
                    <label class="form-label fw-bold">Profile Picture</label>
                    <div class="media-preview">
                      <?php if ($profileImage): ?>
                        <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile" id="profilePreview" class="img-fluid">
                      <?php else: ?>
                        <div class="media-placeholder">
                          <i class="fas fa-user-circle"></i>
                          <p>No Profile Picture</p>
                        </div>
                      <?php endif; ?>
                    </div>
                    <input type="file" id="profilePictureInput" class="form-control mt-3" accept="image/*">
                    <small class="text-muted d-block mt-2">Recommended size: 400x400px, Max 5MB</small>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="media-item">
                    <label class="form-label fw-bold">Cover Photo</label>
                    <div class="media-preview cover-preview">
                      <div class="cover-placeholder">
                        <i class="fas fa-image"></i>
                        <p>Cover Photo</p>
                      </div>
                    </div>
                    <input type="file" id="coverPhotoInput" class="form-control mt-3" accept="image/*">
                    <small class="text-muted d-block mt-2">Recommended size: 1200x300px, Max 10MB</small>
                  </div>
                </div>
              </div>
            </div>

            <!-- Personal Information Form -->
            <form id="personalInfoForm" method="POST" action="../../api/account/update_personal.php">
              <h5 class="mb-3"><i class="fas fa-pen"></i> Personal Details</h5>
              
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label for="firstName" class="form-label">First Name</label>
                  <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($firstName); ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                  <label for="middleName" class="form-label">Middle Name</label>
                  <input type="text" class="form-control" id="middleName" name="middleName" value="<?php echo htmlspecialchars($middleName); ?>">
                </div>
                <div class="col-md-4 mb-3">
                  <label for="lastName" class="form-label">Last Name</label>
                  <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($lastName); ?>" required>
                </div>
              </div>

              <div class="row">
                <div class="col-md-3 mb-3">
                  <label for="suffix" class="form-label">Suffix (if applicable)</label>
                  <select class="form-select" id="suffix" name="suffix">
                    <option value="">None</option>
                    <option value="Jr.">Jr.</option>
                    <option value="Sr.">Sr.</option>
                    <option value="II">II</option>
                    <option value="III">III</option>
                    <option value="IV">IV</option>
                  </select>
                </div>
                <div class="col-md-3 mb-3">
                  <label for="gender" class="form-label">Gender</label>
                  <select class="form-select" id="gender" name="gender">
                    <option value="">Select Gender</option>
                    <option value="Male" <?php echo $gender === 'Male' ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo $gender === 'Female' ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo $gender === 'Other' ? 'selected' : ''; ?>>Other</option>
                  </select>
                </div>
                <div class="col-md-3 mb-3">
                  <label for="birthdate" class="form-label">Date of Birth</label>
                  <input type="date" class="form-control" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($birthdate); ?>">
                  <?php if ($age): ?>
                    <small class="text-muted d-block mt-1">Age: <?php echo $age['years'] ?> years, <?php echo $age['months'] ?> months</small>
                  <?php endif; ?>
                </div>
                <div class="col-md-3 mb-3">
                  <label for="civilStatus" class="form-label">Civil Status</label>
                  <select class="form-select" id="civilStatus" name="civilStatus">
                    <option value="">Select Status</option>
                    <option value="Single" <?php echo $civilStatus === 'Single' ? 'selected' : ''; ?>>Single</option>
                    <option value="Married" <?php echo $civilStatus === 'Married' ? 'selected' : ''; ?>>Married</option>
                    <option value="Widowed" <?php echo $civilStatus === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                    <option value="Separated" <?php echo $civilStatus === 'Separated' ? 'selected' : ''; ?>>Separated</option>
                    <option value="Divorced" <?php echo $civilStatus === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                  </select>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="nationality" class="form-label">Nationality</label>
                  <input type="text" class="form-control" id="nationality" name="nationality" value="<?php echo htmlspecialchars($nationality); ?>">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="occupation" class="form-label">Occupation</label>
                  <input type="text" class="form-control" id="occupation" name="occupation" value="<?php echo htmlspecialchars($occupation); ?>">
                </div>
              </div>

              <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save"></i> Save Personal Information
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- ========================================
           2. CONTACT INFORMATION SECTION
           ======================================== -->
      <div class="settings-content" id="contact-tab" style="display:none;">
        <div class="settings-card">
          <div class="section-header">
            <h2 class="section-title"><i class="fas fa-phone"></i> Contact Information</h2>
          </div>
          <div class="section-content">
            <form id="contactForm" method="POST" action="../../api/account/update_contact.php">
              
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="phone" class="form-label">Phone Number</label>
                  <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="09XX-XXX-XXXX" required>
                  <small class="text-muted">Main contact number for important updates</small>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="mobileNumber" class="form-label">Mobile Number</label>
                  <input type="tel" class="form-control" id="mobileNumber" name="mobileNumber" value="<?php echo htmlspecialchars($mobileNumber); ?>" placeholder="09XX-XXX-XXXX">
                  <small class="text-muted">Alternative mobile number</small>
                </div>
              </div>

              <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                <small class="text-muted">Used for account recovery and notifications</small>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="emergencyContact" class="form-label">Emergency Contact Name</label>
                  <input type="text" class="form-control" id="emergencyContact" name="emergencyContact" value="<?php echo htmlspecialchars($emergencyContact); ?>">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="emergencyPhone" class="form-label">Emergency Contact Number</label>
                  <input type="tel" class="form-control" id="emergencyPhone" name="emergencyPhone" value="<?php echo htmlspecialchars($emergencyPhone); ?>" placeholder="09XX-XXX-XXXX">
                </div>
              </div>

              <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save"></i> Save Contact Information
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- ========================================
           3. IDENTIFICATION SECTION
           ======================================== -->
      <div class="settings-content" id="identification-tab" style="display:none;">
        <div class="settings-card">
          <div class="section-header">
            <h2 class="section-title"><i class="fas fa-id-card"></i> Valid ID Update</h2>
          </div>
          <div class="section-content">
            <form id="idForm" method="POST" action="../../api/account/update_id.php" enctype="multipart/form-data">
              
              <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle"></i> Keep your valid ID updated for verification purposes
              </div>

              <?php if ($validIdPath): ?>
                <div class="mb-4">
                  <h6 class="mb-3">Current ID</h6>
                  <div class="id-preview-container">
                    <img src="<?php echo htmlspecialchars($validIdPath); ?>" alt="Current ID" class="id-preview" id="currentIdPreview">
                    <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="document.getElementById('currentIdPreview').click()">
                      <i class="fas fa-expand"></i> View Full Size
                    </button>
                  </div>
                </div>
              <?php endif; ?>

              <div class="mb-4">
                <label for="validIdFile" class="form-label">Upload New ID</label>
                <div class="upload-area" id="idUploadArea">
                  <i class="fas fa-cloud-upload-alt"></i>
                  <p>Drag and drop your ID here or click to browse</p>
                  <input type="file" id="validIdFile" name="validIdFile" class="form-control" accept="image/*,.pdf" required>
                </div>
                <small class="text-muted d-block mt-2">Supported formats: JPG, PNG, PDF | Max size: 10MB</small>
              </div>

              <div id="idPreviewNew" style="display:none;">
                <h6 class="mb-2">Preview</h6>
                <img id="newIdPreview" src="" alt="New ID Preview" class="img-fluid" style="max-width: 300px;">
              </div>

              <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-upload"></i> Upload New ID
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- ========================================
           4. SECURITY SECTION
           ======================================== -->
      <div class="settings-content" id="security-tab" style="display:none;">
        <div class="settings-card">
          <div class="section-header">
            <h2 class="section-title"><i class="fas fa-shield-alt"></i> Security Settings</h2>
          </div>
          <div class="section-content">

            <!-- Change Password -->
            <div class="security-item mb-4 pb-4 border-bottom">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <h6 class="mb-1"><i class="fas fa-key"></i> Change Password</h6>
                  <p class="text-muted small">Update your password regularly to keep your account secure</p>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#changePasswordForm">
                  <i class="fas fa-edit"></i> Update
                </button>
              </div>
              
              <div class="collapse" id="changePasswordForm">
                <form method="POST" action="../../api/account/update_password.php" class="mt-3">
                  <div class="mb-3">
                    <label for="currentPassword" class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                  </div>
                  <div class="mb-3">
                    <label for="newPassword" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                    <small class="text-muted d-block mt-1">At least 8 characters with uppercase, lowercase, and numbers</small>
                  </div>
                  <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                  </div>
                  <button type="submit" class="btn btn-primary">Save New Password</button>
                </form>
              </div>
            </div>

            <!-- Two-Factor Authentication -->
            <div class="security-item mb-4 pb-4 border-bottom">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-1"><i class="fas fa-mobile-alt"></i> Two-Factor Authentication (2FA)</h6>
                  <p class="text-muted small">Add an extra layer of security to your account</p>
                </div>
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="twoFactorToggle" onchange="toggleTwoFactor(this)">
                  <label class="form-check-label" for="twoFactorToggle">
                    <span id="twoFactorStatus">Enable</span>
                  </label>
                </div>
              </div>
            </div>

            <!-- Login Activity -->
            <div class="security-item mb-4 pb-4 border-bottom">
              <h6 class="mb-3"><i class="fas fa-history"></i> Login Activity</h6>
              <div class="login-activity-list">
                <div class="activity-item">
                  <div class="activity-info">
                    <strong>Current Session</strong>
                    <p class="text-muted small mb-0">
                      <i class="fas fa-desktop"></i> Windows • Chrome • Today at 2:30 PM
                    </p>
                  </div>
                  <small class="text-success">Active</small>
                </div>
                <div class="activity-item">
                  <div class="activity-info">
                    <strong>Mobile</strong>
                    <p class="text-muted small mb-0">
                      <i class="fas fa-mobile-alt"></i> iPhone • Safari • Yesterday
                    </p>
                  </div>
                </div>
                <div class="activity-item">
                  <div class="activity-info">
                    <strong>Device</strong>
                    <p class="text-muted small mb-0">
                      <i class="fas fa-tablet-alt"></i> iPad • Chrome • 3 days ago
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Logout All Devices -->
            <div class="security-item">
              <h6 class="mb-3"><i class="fas fa-sign-out-alt"></i> Device Management</h6>
              <p class="text-muted small mb-3">Sign out from all other devices and sessions</p>
              <button type="button" class="btn btn-warning" onclick="logoutAllDevices()">
                <i class="fas fa-sign-out-alt"></i> Logout All Other Devices
              </button>
            </div>

          </div>
        </div>
      </div>

      <!-- ========================================
           5. NOTIFICATION PREFERENCES SECTION
           ======================================== -->
      <div class="settings-content" id="notifications-tab" style="display:none;">
        <div class="settings-card">
          <div class="section-header">
            <h2 class="section-title"><i class="fas fa-bell"></i> Notification Preferences</h2>
          </div>
          <div class="section-content">
            <form id="notificationForm" method="POST" action="../../api/account/update_notifications.php">
              
              <div class="notification-item mb-4 pb-4 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="mb-1"><i class="fas fa-file-alt"></i> Document Request Updates</h6>
                    <p class="text-muted small mb-0">Get notified about changes to your document requests</p>
                  </div>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="notifDocUpdate" name="notifDocUpdate" checked>
                  </div>
                </div>
              </div>

              <div class="notification-item mb-4 pb-4 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="mb-1"><i class="fas fa-bullhorn"></i> Barangay Announcements</h6>
                    <p class="text-muted small mb-0">Receive important announcements from the barangay</p>
                  </div>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="notifAnnouncements" name="notifAnnouncements" checked>
                  </div>
                </div>
              </div>

              <div class="notification-item mb-4">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="mb-1"><i class="fas fa-chart-bar"></i> Reports Update</h6>
                    <p class="text-muted small mb-0">Get updates on your filed reports and incidents</p>
                  </div>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="notifReports" name="notifReports" checked>
                  </div>
                </div>
              </div>

              <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save"></i> Save Preferences
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- ========================================
           6. PRIVACY SECTION
           ======================================== -->
      <div class="settings-content" id="privacy-tab" style="display:none;">
        <div class="settings-card">
          <div class="section-header">
            <h2 class="section-title"><i class="fas fa-lock"></i> Privacy & Data</h2>
          </div>
          <div class="section-content">
            
            <div class="privacy-item mb-4 pb-4 border-bottom">
              <h6 class="mb-3"><i class="fas fa-download"></i> Download Your Personal Data</h6>
              <p class="text-muted mb-3">Download all your personal information stored in the system in a portable format (JSON)</p>
              <button type="button" class="btn btn-primary" onclick="downloadPersonalData()">
                <i class="fas fa-download"></i> Download Data
              </button>
            </div>

            <div class="privacy-item mb-4 pb-4 border-bottom">
              <h6 class="mb-3"><i class="fas fa-trash-alt"></i> Data Privacy Policy</h6>
              <p class="text-muted mb-3">We take your privacy seriously. Your personal data is protected and handled according to data protection regulations.</p>
              <a href="../../external-links/privacy-policy.html" target="_blank" class="btn btn-outline-primary">
                <i class="fas fa-external-link-alt"></i> Read Privacy Policy
              </a>
            </div>

            <div class="privacy-item">
              <h6 class="mb-3"><i class="fas fa-eye"></i> Activity Log</h6>
              <p class="text-muted mb-3">View your recent account activities</p>
              <a href="#" class="btn btn-outline-secondary">
                <i class="fas fa-history"></i> View Activity Log
              </a>
            </div>

          </div>
        </div>
      </div>

      <!-- ========================================
           7. SUPPORT SECTION
           ======================================== -->
      <div class="settings-content" id="support-tab" style="display:none;">
        <div class="settings-card">
          <div class="section-header">
            <h2 class="section-title"><i class="fas fa-headset"></i> Support & Help</h2>
          </div>
          <div class="section-content">
            
            <div class="support-item mb-4 pb-4 border-bottom">
              <h6 class="mb-3"><i class="fas fa-phone-alt"></i> Contact Barangay Office</h6>
              <p class="text-muted mb-3">Get in touch with the barangay office for general inquiries and assistance</p>
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contactModal">
                <i class="fas fa-envelope"></i> Send Message
              </button>
            </div>

            <div class="support-item mb-4 pb-4 border-bottom">
              <h6 class="mb-3"><i class="fas fa-comment-dots"></i> Submit Feedback</h6>
              <p class="text-muted mb-3">Share your feedback and suggestions to help us improve SafeBrgy</p>
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#feedbackModal">
                <i class="fas fa-edit"></i> Send Feedback
              </button>
            </div>

            <div class="support-item">
              <h6 class="mb-3"><i class="fas fa-flag"></i> Report an Issue</h6>
              <p class="text-muted mb-3">Report technical issues or security concerns</p>
              <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#reportIssueModal">
                <i class="fas fa-exclamation-triangle"></i> Report Issue
              </button>
            </div>

          </div>
        </div>
      </div>

      <!-- ========================================
           8. ABOUT SECTION
           ======================================== -->
      <div class="settings-content" id="about-tab" style="display:none;">
        <div class="settings-card">
          <div class="section-header">
            <h2 class="section-title"><i class="fas fa-info-circle"></i> About SafeBrgy</h2>
          </div>
          <div class="section-content">
            
            <div class="about-item mb-4 pb-4 border-bottom">
              <h6 class="mb-2">Version Information</h6>
              <p class="text-muted mb-2">
                <strong>SafeBrgy Version:</strong> 1.0.0
              </p>
              <p class="text-muted mb-2">
                <strong>Last Updated:</strong> June 2024
              </p>
              <p class="text-muted">
                <i class="fas fa-check-circle text-success"></i> You're using the latest version
              </p>
            </div>

            <div class="about-item mb-4 pb-4 border-bottom">
              <h6 class="mb-3"><i class="fas fa-file-contract"></i> Terms & Conditions</h6>
              <p class="text-muted mb-3">By using SafeBrgy, you agree to our terms and conditions</p>
              <a href="../../external-links/terms-of-service.html" target="_blank" class="btn btn-outline-primary">
                <i class="fas fa-external-link-alt"></i> Read Terms of Service
              </a>
            </div>

            <div class="about-item pb-4 border-bottom">
              <h6 class="mb-3"><i class="fas fa-shield-alt"></i> Privacy Policy</h6>
              <p class="text-muted mb-3">Learn how we protect your personal information</p>
              <a href="../../external-links/privacy-policy.html" target="_blank" class="btn btn-outline-primary">
                <i class="fas fa-external-link-alt"></i> Read Privacy Policy
              </a>
            </div>

            <div class="about-item">
              <h6 class="mb-3"><i class="fas fa-code"></i> Developed By</h6>
              <p class="text-muted mb-0">SafeBrgy Development Team</p>
            </div>

          </div>
        </div>
      </div>

      <!-- ========================================
           9. DANGER ZONE SECTION
           ======================================== -->
      <div class="settings-content" id="danger-tab" style="display:none;">
        <div class="settings-card danger-card">
          <div class="section-header danger-header">
            <h2 class="section-title"><i class="fas fa-exclamation-triangle"></i> Danger Zone</h2>
            <p class="text-muted small">These actions cannot be undone</p>
          </div>
          <div class="section-content">
            
            <div class="danger-item mb-4 pb-4 border-bottom">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <h6 class="mb-1"><i class="fas fa-pause-circle"></i> Deactivate Account</h6>
                  <p class="text-muted small">Your account will be temporarily disabled. You can reactivate it anytime.</p>
                </div>
                <button type="button" class="btn btn-warning" onclick="showDeactivateConfirm()">
                  <i class="fas fa-pause-circle"></i> Deactivate
                </button>
              </div>
            </div>

            <div class="danger-item">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <h6 class="mb-1"><i class="fas fa-trash-alt"></i> Delete Account</h6>
                  <p class="text-muted small">Permanently delete your account and all associated data. This action cannot be reversed.</p>
                </div>
                <button type="button" class="btn btn-danger" onclick="showDeleteConfirm()">
                  <i class="fas fa-trash-alt"></i> Delete Account
                </button>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>
  </main>

  <!-- ========================================
       MODALS
       ======================================== -->

  <!-- Contact Modal -->
  <div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-envelope"></i> Contact Barangay Office</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" action="../../api/account/send_contact.php">
          <div class="modal-body">
            <div class="mb-3">
              <label for="contactSubject" class="form-label">Subject</label>
              <input type="text" class="form-control" id="contactSubject" name="subject" required>
            </div>
            <div class="mb-3">
              <label for="contactMessage" class="form-label">Message</label>
              <textarea class="form-control" id="contactMessage" name="message" rows="4" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Send Message</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Feedback Modal -->
  <div class="modal fade" id="feedbackModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-comment-dots"></i> Submit Feedback</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" action="../../api/account/send_feedback.php">
          <div class="modal-body">
            <div class="mb-3">
              <label for="feedbackType" class="form-label">Feedback Type</label>
              <select class="form-select" id="feedbackType" name="type" required>
                <option value="">Select Type</option>
                <option value="bug">Bug Report</option>
                <option value="feature">Feature Request</option>
                <option value="improvement">Improvement Suggestion</option>
                <option value="general">General Feedback</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="feedbackMessage" class="form-label">Your Feedback</label>
              <textarea class="form-control" id="feedbackMessage" name="message" rows="4" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Send Feedback</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Report Issue Modal -->
  <div class="modal fade" id="reportIssueModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-flag"></i> Report an Issue</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" action="../../api/account/report_issue.php">
          <div class="modal-body">
            <div class="mb-3">
              <label for="issueType" class="form-label">Issue Type</label>
              <select class="form-select" id="issueType" name="type" required>
                <option value="">Select Type</option>
                <option value="technical">Technical Issue</option>
                <option value="security">Security Concern</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="issueDescription" class="form-label">Issue Description</label>
              <textarea class="form-control" id="issueDescription" name="description" rows="4" required></textarea>
            </div>
            <div class="mb-3">
              <label for="issuePage" class="form-label">Affected Page/Feature</label>
              <input type="text" class="form-control" id="issuePage" name="page">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Report Issue</button>
          </div>
        </form>
      </div>
    </div>
  </div>

<!-- Shared JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/shared/logo_functions.js"></script>
<script src="../../assets/js/shared/shared-header.js"></script>
<script src="../../assets/js/shared/shared-sidebar.js"></script>
<!-- Page-specific JS -->
<script src="../../assets/js/public/account.js"></script>
</body>
</html>
