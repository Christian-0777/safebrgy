<?php
// dashboard.php - SafeBrgy Dashboard
session_start();
// Example: fetch user info from session
$user = $_SESSION['user'] ?? "Juan Dela Cruz";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SafeBrgy - Dashboard</title>
  <link rel="icon" type="image/png" href="../assets/img/seal.png">
  <!-- Shared Styles -->
  <link rel="stylesheet" href="../assets/css/shared/shared-header.css">
  <link rel="stylesheet" href="../assets/css/shared/shared_sidebar.css">
  <link rel="stylesheet" href="../assets/css/shared/colors.css">
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="../assets/css/public/dashboard.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

  <!-- HEADER -->
  <header class="header">
    <div class="header-left">
      <button class="sidebar-toggle"><i class="fas fa-bars"></i></button>
      <a href="../index.php" class="header-logo">
        <img src="../assets/img/seal.png" alt="SafeBrgy Logo" class="logo-image">
        <span>SafeBrgy</span>
      </a>
    </div>

    <div class="header-right">
      <div class="user-profile">
        <div class="profile-avatar"><?php echo substr($user, 0, 1); ?></div>
        <div class="profile-info">
          <div class="profile-name"><?php echo htmlspecialchars($user); ?></div>
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
    <div>
      <h2 class="mb-4">Welcome, <?php echo htmlspecialchars($user); ?>!</h2>

    <!-- Status Buttons -->
    <div class="row mb-4">
      <div class="col-md-4 mb-2">
        <button class="btn btn-primary w-100">My Requests</button>
      </div>
      <div class="col-md-4 mb-2">
        <button class="btn btn-warning w-100">Pending Requests</button>
      </div>
      <div class="col-md-4 mb-2">
        <button class="btn btn-success w-100">Approved Documents</button>
      </div>
    </div>

    <!-- Service Cards -->
    <div class="row g-3">
      <?php
      $services = [
        ["Barangay Clearance","Proof of no bad record in the barangay, often needed for jobs or permits."],
        ["Barangay Residency","Confirms that a person is a resident of the barangay."],
        ["Barangay Indigency","Issued to low income individuals for aid, scholarships, or subsidies."],
        ["Barangay Business Clearance","Permission for a business to operate within the barangay."],
        ["Incident Report","Record of complaints or incidents filed at the barangay."],
        ["Lost Property","Assistance for residents who lost items, with issuance of Lost & Found document."]
      ];
      foreach ($services as $service) {
        echo '<div class="col-md-4">
                <div class="card h-100 shadow-sm">
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title">'.htmlspecialchars($service[0]).'</h5>
                    <p class="card-text flex-grow-1">'.htmlspecialchars($service[1]).'</p>
                    <a href="request.php?service='.urlencode($service[0]).'" class="btn btn-outline-primary mt-auto">Request Now</a>
                  </div>
                </div>
              </div>';
      }
      ?>
    </div>

    </div>
  </main>

<!-- Shared JS -->
<script src="../assets/js/shared/shared-header.js"></script>
<script src="../assets/js/shared/shared-sidebar.js"></script>
<!-- Page-specific JS -->
<script src="../assets/js/public/dashboard.js"></script>
</body>
</html>
