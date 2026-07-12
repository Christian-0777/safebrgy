<?php
require_once __DIR__ . '/../../config/db.php';
session_start();

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'resident') {
    header('Location: ../login.php');
    exit;
}

$pdo = safeBrgy_db_connect();
$user = $_SESSION['user'];
$residentName = $user['name'] ?? $user['username'] ?? 'Resident';
$residentEmail = $user['email'] ?? '';

$stmt = $pdo->prepare('SELECT reference_no, document_type, status, submitted_at FROM requests WHERE resident_email = ? ORDER BY submitted_at DESC');
$stmt->execute([$residentEmail]);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SafeBrgy - Request Portal</title>
  <link rel="icon" type="image/png" href="../../assets/img/seal.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/shared/shared-header.css">
  <link rel="stylesheet" href="../../assets/css/shared/shared_sidebar.css">
  <link rel="stylesheet" href="../../assets/css/shared/colors.css">
  <link rel="stylesheet" href="../../assets/css/public/request.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
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
        <div class="profile-avatar"><?php echo htmlspecialchars(substr($residentName, 0, 1)); ?></div>
        <div class="profile-info">
          <div class="profile-name"><?php echo htmlspecialchars($residentName); ?></div>
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

  <aside class="sidebar">
    <ul class="sidebar-menu">
      <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span class="menu-label">Dashboard</span></a></li>
      <li><a href="announcement.php"><i class="fas fa-bullhorn"></i> <span class="menu-label">Announcements</span></a></li>
      <li><a href="reports.php"><i class="fas fa-file-alt"></i> <span class="menu-label">My Reports</span></a></li>
      <li><a href="requests.php" class="active"><i class="fas fa-clipboard-list"></i> <span class="menu-label">My Requests</span></a></li>
    </ul>
    <div class="sidebar-footer">
      <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span class="menu-label">Logout</span></a>
    </div>
  </aside>

  <main class="main-content">
    <div class="page-shell">
      <div class="page-header">
        <div>
          <h2>Resident Request Portal</h2>
          <p>Submit barangay document requests and track them from one place.</p>
        </div>
        <button type="button" class="btn-primary" data-modal="modal-clearance">
          <i class="fas fa-plus"></i> Request Now
        </button>
      </div>

      <div class="hero-card">
        <p>Choose a document type below, fill out the short form, and our barangay office will review it and update the status after submission.</p>
      </div>

      <div class="service-grid">
        <div class="service-card">
          <div class="service-icon"><i class="fas fa-certificate"></i></div>
          <h4>Barangay Clearance</h4>
          <p>Commonly needed for employment, school, or other transaction requirements.</p>
          <button type="button" data-modal="modal-clearance">Request Now</button>
        </div>
        <div class="service-card">
          <div class="service-icon"><i class="fas fa-home"></i></div>
          <h4>Barangay Residency</h4>
          <p>Useful for scholarships, IDs, and proof of your length of stay in the barangay.</p>
          <button type="button" data-modal="modal-residency">Request Now</button>
        </div>
        <div class="service-card">
          <div class="service-icon"><i class="fas fa-hand-holding-usd"></i></div>
          <h4>Barangay Indigency</h4>
          <p>Certificates for medical, educational, financial, or burial assistance requests.</p>
          <button type="button" data-modal="modal-indigency">Request Now</button>
        </div>
        <div class="service-card">
          <div class="service-icon"><i class="fas fa-store"></i></div>
          <h4>Barangay Business Clearance</h4>
          <p>Required permit clearance for operating a business within the barangay.</p>
          <button type="button" data-modal="modal-business">Request Now</button>
        </div>
      </div>

      <div class="table-card">
        <h3 style="margin-top:0; margin-bottom:14px;">My Requests</h3>
        <div class="table-wrap">
          <table class="requests-table">
            <thead>
              <tr>
                <th>Reference No.</th>
                <th>Document Type</th>
                <th>Submitted On</th>
                <th>Status</th>
                <th>Remarks</th>
              </tr>
            </thead>
            <tbody id="requests-table-body">
              <?php if (empty($requests)): ?>
                <tr class="empty-state-row">
                  <td colspan="5" class="empty-state">No requests submitted yet. Start by creating your first request.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($requests as $request): ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($request['reference_no']); ?></strong></td>
                    <td><?php echo htmlspecialchars($request['document_type']); ?></td>
                    <td><?php echo htmlspecialchars(date('M d, Y g:i A', strtotime($request['submitted_at']))); ?></td>
                    <td><span class="status-pill status-<?php echo strtolower(str_replace(' ', '-', $request['status'])); ?>"><?php echo htmlspecialchars($request['status']); ?></span></td>
                    <td>Pending review</td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <div class="modal-overlay" id="modal-clearance">
    <div class="modal-box">
      <div class="modal-head">
        <h3>Barangay Clearance</h3>
        <button type="button" class="modal-close" data-close>&times;</button>
      </div>
      <div class="modal-body">
        <form class="request-form" data-doctype="Barangay Clearance" enctype="multipart/form-data">
          <div class="form-alert"></div>
          <div class="form-row">
            <div class="form-group">
              <label for="clearance-name">Full Name *</label>
              <input type="text" id="clearance-name" name="resident_name" value="<?php echo htmlspecialchars($residentName); ?>" required>
            </div>
            <div class="form-group">
              <label for="clearance-email">Email Address *</label>
              <input type="email" id="clearance-email" name="resident_email" value="<?php echo htmlspecialchars($residentEmail); ?>" required>
            </div>
          </div>
          <div class="form-group">
            <label for="clearance-purpose">Purpose of Request *</label>
            <textarea id="clearance-purpose" name="purpose" required placeholder="e.g. Employment requirement, school requirement..."></textarea>
          </div>
          <div class="form-group">
            <label for="clearance-file">Supporting Document / Image (optional)</label>
            <input type="file" id="clearance-file" name="supporting_file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
            <span class="hint">Accepted: JPG, PNG, PDF, DOC/DOCX — max 5MB.</span>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-secondary" data-close>Cancel</button>
            <button type="submit" class="btn-primary">Submit Request</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="modal-residency">
    <div class="modal-box">
      <div class="modal-head">
        <h3>Barangay Residency</h3>
        <button type="button" class="modal-close" data-close>&times;</button>
      </div>
      <div class="modal-body">
        <form class="request-form" data-doctype="Barangay Residency" enctype="multipart/form-data">
          <div class="form-alert"></div>
          <div class="form-row">
            <div class="form-group">
              <label for="residency-name">Full Name *</label>
              <input type="text" id="residency-name" name="resident_name" value="<?php echo htmlspecialchars($residentName); ?>" required>
            </div>
            <div class="form-group">
              <label for="residency-email">Email Address *</label>
              <input type="email" id="residency-email" name="resident_email" value="<?php echo htmlspecialchars($residentEmail); ?>" required>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="residency-years">Years of Residency *</label>
              <input type="number" id="residency-years" name="years_of_residency" min="0" required>
            </div>
            <div class="form-group">
              <label for="residency-date">Date Started Living in Barangay *</label>
              <input type="date" id="residency-date" name="date_started" required>
            </div>
          </div>
          <div class="form-group">
            <label for="residency-purpose">Purpose of Request *</label>
            <textarea id="residency-purpose" name="purpose" required placeholder="e.g. Scholarship application, ID requirement..."></textarea>
          </div>
          <div class="form-group">
            <label for="residency-file">Supporting Document / Image (optional)</label>
            <input type="file" id="residency-file" name="supporting_file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
            <span class="hint">Accepted: JPG, PNG, PDF, DOC/DOCX — max 5MB.</span>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-secondary" data-close>Cancel</button>
            <button type="submit" class="btn-primary">Submit Request</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="modal-indigency">
    <div class="modal-box">
      <div class="modal-head">
        <h3>Barangay Indigency</h3>
        <button type="button" class="modal-close" data-close>&times;</button>
      </div>
      <div class="modal-body">
        <form class="request-form" data-doctype="Barangay Indigency" enctype="multipart/form-data">
          <div class="form-alert"></div>
          <div class="form-row">
            <div class="form-group">
              <label for="indigency-name">Full Name *</label>
              <input type="text" id="indigency-name" name="resident_name" value="<?php echo htmlspecialchars($residentName); ?>" required>
            </div>
            <div class="form-group">
              <label for="indigency-email">Email Address *</label>
              <input type="email" id="indigency-email" name="resident_email" value="<?php echo htmlspecialchars($residentEmail); ?>" required>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="indigency-income">Monthly Income (₱) *</label>
              <input type="number" id="indigency-income" name="monthly_income" min="0" step="0.01" required>
            </div>
            <div class="form-group">
              <label for="indigency-members">Number of Household Members *</label>
              <input type="number" id="indigency-members" name="household_members" min="1" required>
            </div>
          </div>
          <div class="form-group">
            <label for="indigency-purpose">Purpose of Request *</label>
            <select id="indigency-purpose" name="purpose" required>
              <option value="" disabled selected>Select purpose...</option>
              <option value="Medical Assistance">Medical Assistance</option>
              <option value="Educational Assistance">Educational Assistance</option>
              <option value="Financial Assistance">Financial Assistance</option>
              <option value="Burial Assistance">Burial Assistance</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div class="form-group" id="indigency-other-wrap" style="display:none;">
            <label for="indigency-other">Please specify</label>
            <input type="text" id="indigency-other" name="purpose_other" placeholder="Specify other purpose">
          </div>
          <div class="form-group">
            <label for="indigency-file">Supporting Document / Image (optional)</label>
            <input type="file" id="indigency-file" name="supporting_file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
            <span class="hint">Accepted: JPG, PNG, PDF, DOC/DOCX — max 5MB.</span>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-secondary" data-close>Cancel</button>
            <button type="submit" class="btn-primary">Submit Request</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="modal-business">
    <div class="modal-box">
      <div class="modal-head">
        <h3>Barangay Business Clearance</h3>
        <button type="button" class="modal-close" data-close>&times;</button>
      </div>
      <div class="modal-body">
        <form class="request-form" data-doctype="Barangay Business Clearance" enctype="multipart/form-data">
          <div class="form-alert"></div>
          <div class="form-row">
            <div class="form-group">
              <label for="business-name-owner">Full Name (Owner) *</label>
              <input type="text" id="business-name-owner" name="resident_name" value="<?php echo htmlspecialchars($residentName); ?>" required>
            </div>
            <div class="form-group">
              <label for="business-email">Email Address *</label>
              <input type="email" id="business-email" name="resident_email" value="<?php echo htmlspecialchars($residentEmail); ?>" required>
            </div>
          </div>
          <div class="form-group">
            <label for="business-name">Business Name *</label>
            <input type="text" id="business-name" name="business_name" required>
          </div>
          <div class="form-group">
            <label for="business-description">Business Description *</label>
            <textarea id="business-description" name="business_description" required placeholder="Briefly describe the nature of the business"></textarea>
          </div>
          <div class="form-group">
            <label for="business-logo">Business Logo (optional)</label>
            <input type="file" id="business-logo" name="business_logo" accept=".jpg,.jpeg,.png">
            <span class="hint">JPG or PNG — max 5MB.</span>
          </div>
          <div class="form-group">
            <label for="business-address">Business Full Address *</label>
            <input type="text" id="business-address" name="business_address" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="business-contact">Contact Number *</label>
              <input type="tel" id="business-contact" name="contact_number" required>
            </div>
            <div class="form-group">
              <label for="business-tin">TIN (optional)</label>
              <input type="text" id="business-tin" name="tin_number">
            </div>
          </div>
          <div class="form-group">
            <label for="business-started">Business Started *</label>
            <input type="date" id="business-started" name="business_started" required>
          </div>
          <div class="form-group">
            <label for="business-purpose">Purpose of Request *</label>
            <textarea id="business-purpose" name="purpose" required placeholder="e.g. Renewal of permit, new application..."></textarea>
          </div>
          <div class="form-group">
            <label for="business-file">Supporting Document / Image (optional)</label>
            <input type="file" id="business-file" name="supporting_file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
            <span class="hint">Accepted: JPG, PNG, PDF, DOC/DOCX — max 5MB.</span>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-secondary" data-close>Cancel</button>
            <button type="submit" class="btn-primary">Submit Request</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="modal-confirm">
    <div class="modal-box confirm-box">
      <div class="confirm-icon"><i class="fas fa-check"></i></div>
      <h3 id="confirm-title">Request Submitted</h3>
      <p id="confirm-message"></p>
      <div class="confirm-ref" id="confirm-ref"></div>
      <div class="modal-footer" style="justify-content:center;">
        <button type="button" class="btn-primary" id="confirm-ok">Okay, got it</button>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../assets/js/shared/shared-header.js"></script>
  <script src="../../assets/js/shared/shared-sidebar.js"></script>
  <script src="../../assets/js/public/request.js"></script>
</body>
</html>
