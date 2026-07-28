<?php
require_once __DIR__ . '/../../config/db.php';
// announcement.php - SafeBrgy Announcements (Resident)
session_start();

// Check if user is logged in and verified
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'resident') {
    header('Location: ../login.php');
    exit;
}

$user = $_SESSION['user'];
$name = $user['name'] ?? 'Resident';
$userId = $_SESSION['user']['id'] ?? null;

$pdo = safeBrgy_db_connect();

// Get search and sort parameters
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Build query for active announcements only
$query = '
    SELECT a.id, a.title, a.body, a.published_at, a.priority, a.attachments, a.target_audience, u.username as author
    FROM announcements a
    LEFT JOIN users u ON a.author_id = u.id
    WHERE a.status = "active" AND a.archived = 0
';

$params = [];

if ($search) {
    $query .= ' AND (a.title LIKE ? OR a.body LIKE ?)';
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Add sort
if ($sort === 'oldest') {
    $query .= ' ORDER BY a.published_at ASC';
} else {
    $query .= ' ORDER BY a.pinned DESC, a.published_at DESC';
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SafeBrgy - Announcements</title>
  <link rel="icon" type="image/png" href="../../assets/img/seal.png">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- Shared Styles -->
  <link rel="stylesheet" href="../../assets/css/shared/shared-header.css">
  <link rel="stylesheet" href="../../assets/css/shared/shared_sidebar.css">
  <link rel="stylesheet" href="../../assets/css/shared/colors.css">
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="../assets/css/public/announcement.css">
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
          <a href="Profile.php"><i class="fas fa-user"></i> Profile</a>
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
          <h2 class="mb-2">Announcements</h2>
          <p class="text-muted">Stay updated with the latest notices and updates from the municipality</p>
        </div>
        <span class="fw-bold"><?php echo htmlspecialchars($name); ?></span>
      </div>

      <!-- Search and Filter -->
      <div class="card mb-4">
        <div class="card-body">
          <form method="get" class="row g-3 align-items-end">
            <div class="col-md-6">
              <label class="form-label">Search by Title or Keyword</label>
              <input type="text" name="search" class="form-control" placeholder="Search announcements..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="col-md-3">
              <label class="form-label">Sort By</label>
              <select name="sort" class="form-select">
                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest</option>
              </select>
            </div>

            <div class="col-md-3">
              <button type="submit" class="btn btn-outline-primary w-100">
                <i class="fas fa-search"></i> Search
              </button>
            </div>

            <div class="col-12">
              <a href="announcement.php" class="btn btn-outline-secondary">
                <i class="fas fa-redo"></i> Reset
              </a>
            </div>
          </form>
        </div>
      </div>

      <!-- Announcement Cards -->
      <div class="row g-3">
        <?php if (empty($announcements)): ?>
          <div class="col-12">
            <div class="alert alert-info text-center py-5">
              <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
              <p class="mb-0">No announcements found</p>
            </div>
          </div>
        <?php else: ?>
          <?php foreach ($announcements as $a): ?>
            <div class="col-md-6">
              <div class="card shadow-sm h-100 announcement-card">
                <div class="card-body d-flex flex-column">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($a['title']); ?></h5>
                    <?php if ($a['priority'] !== 'normal'): ?>
                      <span class="badge bg-<?php 
                        echo match($a['priority']) {
                          'urgent' => 'danger',
                          'important' => 'warning',
                          default => 'secondary'
                        };
                      ?>">
                        <?php echo ucfirst($a['priority']); ?>
                      </span>
                    <?php endif; ?>
                  </div>
                  <small class="text-muted mb-2">
                    <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($a['published_at'])); ?>
                  </small>
                  <p class="card-text flex-grow-1">
                    <?php 
                      $excerpt = strlen($a['body']) > 150 ? substr($a['body'], 0, 150) . '...' : $a['body'];
                      echo htmlspecialchars($excerpt);
                    ?>
                  </p>
                  <div class="d-flex gap-2 mt-auto">
                    <button type="button" class="btn btn-outline-primary flex-grow-1" data-bs-toggle="modal" data-bs-target="#viewAnnouncementModal<?php echo $a['id']; ?>">
                      Read More
                    </button>
                    <button type="button" class="btn btn-outline-secondary noted-btn" data-id="<?php echo $a['id']; ?>" title="Mark as Noted">
                      <i class="fas fa-check"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Announcement Detail Modal -->
            <div class="modal fade" id="viewAnnouncementModal<?php echo $a['id']; ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <div>
                      <h5 class="modal-title"><?php echo htmlspecialchars($a['title']); ?></h5>
                      <small class="text-muted">
                        <i class="fas fa-calendar"></i> Posted on <?php echo date('M d, Y \a\t H:i', strtotime($a['published_at'])); ?>
                      </small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <?php if ($a['priority'] !== 'normal'): ?>
                      <div class="mb-3">
                        <span class="badge bg-<?php 
                          echo match($a['priority']) {
                            'urgent' => 'danger',
                            'important' => 'warning',
                            default => 'secondary'
                          };
                        ?>">
                          <?php echo ucfirst($a['priority']); ?> Priority
                        </span>
                      </div>
                    <?php endif; ?>

                    <!-- Target Audience -->
                    <?php 
                      $audience = $a['target_audience'] ? json_decode($a['target_audience'], true) : ['type' => 'all'];
                      $audienceType = $audience['type'] ?? 'all';
                    ?>
                    <div class="mb-3 pb-3 border-bottom">
                      <small class="text-muted">
                        <strong>Target Audience:</strong> 
                        <?php 
                          if ($audienceType === 'other') {
                            echo 'Custom - ' . htmlspecialchars($audience['message'] ?? '');
                          } else {
                            echo htmlspecialchars(ucfirst(str_replace(['age:', 'education:'], '', $audienceType)));
                          }
                        ?>
                      </small>
                    </div>

                    <!-- Description -->
                    <div class="announcement-content mb-4">
                      <h6 class="mb-2">Description</h6>
                      <?php echo nl2br(htmlspecialchars($a['body'])); ?>
                    </div>

                    <!-- Images/Attachments Preview -->
                    <?php if ($a['attachments']): ?>
                      <div class="announcement-images-section">
                        <h6 class="mb-3">Attachments</h6>
                        <?php 
                          $attachments = json_decode($a['attachments'], true);
                          
                          // Handle both old format (single file) and new format (array of files)
                          $fileList = [];
                          if (isset($attachments['file'])) {
                            // Old format: single file
                            $fileList[] = ['file' => $attachments['file'], 'type' => $attachments['type'] ?? 'image/jpeg'];
                          } elseif (is_array($attachments) && count($attachments) > 0 && isset($attachments[0]['file'])) {
                            // New format: array of files
                            $fileList = $attachments;
                          }
                          
                          if (!empty($fileList)):
                        ?>
                          <div class="row g-3">
                            <?php foreach ($fileList as $file): ?>
                              <div class="col-md-6">
                                <?php 
                                  $fileType = $file['type'] ?? '';
                                  $isImage = strpos($fileType, 'image/') === 0;
                                ?>
                                <?php if ($isImage): ?>
                                  <img src="../../uploads/announcements/<?php echo htmlspecialchars($file['file']); ?>" class="img-fluid rounded" alt="Announcement Image" style="max-height: 300px; object-fit: cover; width: 100%;">
                                <?php else: ?>
                                  <a href="../../uploads/announcements/<?php echo htmlspecialchars($file['file']); ?>" class="btn btn-sm btn-outline-primary w-100" download>
                                    <i class="fas fa-download"></i> <?php echo htmlspecialchars(basename($file['file'])); ?>
                                  </a>
                                <?php endif; ?>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        <?php endif; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary noted-btn-modal" data-id="<?php echo $a['id']; ?>">
                      <i class="fas fa-check"></i> Mark as Noted
                    </button>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </div>
  </main>

<!-- Shared JS -->
<script src="../../assets/js/shared/logo_functions.js"></script>
<script src="../../assets/js/shared/shared-header.js"></script>
<script src="../../assets/js/shared/shared-sidebar.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/shared/layout_functions.js"></script>
<!-- Page-specific JS -->
<script src="../assets/js/public/announcement.js"></script>
</body>
</html>
