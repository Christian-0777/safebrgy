<?php
require_once __DIR__ . '/../admin_protect.php';
require_once __DIR__ . '/../../includes/shared/profile_avatar.php';
// announcements.php - SafeBrgy Announcements Admin

$pdo = safeBrgy_db_connect();
$adminId = $_SESSION['admin_user']['id'] ?? null;

if ($adminId) {
    $stmt = $pdo->prepare('SELECT username, email FROM users WHERE id = :id');
    $stmt->execute(['id' => $adminId]);
    $admin = $stmt->fetch();
    $user = adminDisplayName($admin['username'] ?? 'Admin');
} else {
    $user = 'Admin';
}

// Handle AJAX requests for announcements
$response = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        $action = $_POST['action'];
        
        if ($action === 'create') {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $priority = $_POST['priority'] ?? 'normal';
            $targetAudience = $_POST['target_audience'] ?? 'all';
            $targetAudienceOther = $_POST['target_audience_other'] ?? '';
            $announcementType = $_POST['announcement_type'] ?? 'immediate';
            $scheduledAt = $_POST['scheduled_at'] ?? null;
            
            // Validate required fields
            if (empty($title) || empty($description)) {
                echo json_encode(['success' => false, 'error' => 'Title and description are required']);
                exit;
            }
            
            // Build target audience JSON
            $audienceData = ['type' => $targetAudience];
            if ($targetAudience === 'other' && !empty($targetAudienceOther)) {
                $audienceData['message'] = $targetAudienceOther;
            }
            $targetAudienceJson = json_encode($audienceData);
            
            // Handle multiple file uploads
            $attachmentsArray = [];
            $uploadDir = '../../uploads/announcements/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            if (!empty($_FILES['attachments']['name'][0])) {
                $fileCount = count($_FILES['attachments']['name']);
                for ($i = 0; $i < $fileCount; $i++) {
                    if (!empty($_FILES['attachments']['name'][$i])) {
                        $file = [
                            'name' => $_FILES['attachments']['name'][$i],
                            'type' => $_FILES['attachments']['type'][$i],
                            'tmp_name' => $_FILES['attachments']['tmp_name'][$i],
                            'error' => $_FILES['attachments']['error'][$i],
                            'size' => $_FILES['attachments']['size'][$i]
                        ];
                        
                        // Validate file type
                        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                        if (!in_array($file['type'], $allowedTypes)) {
                            continue;
                        }
                        
                        // Validate file size (max 5MB)
                        if ($file['size'] > 5242880) {
                            continue;
                        }
                        
                        $fileName = time() . '_' . uniqid() . '_' . basename($file['name']);
                        if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
                            $attachmentsArray[] = [
                                'file' => $fileName,
                                'type' => $file['type'],
                                'size' => $file['size']
                            ];
                        }
                    }
                }
            }
            
            $attachmentsJson = !empty($attachmentsArray) ? json_encode($attachmentsArray) : null;
            
            // Set status based on type and scheduled date
            if ($announcementType === 'scheduled' && $scheduledAt) {
                $status = 'scheduled';
                $publishedAt = $scheduledAt;
            } else {
                $status = 'active';
                $publishedAt = date('Y-m-d H:i:s');
                $scheduledAt = null;
            }
            
            $stmt = $pdo->prepare('
                INSERT INTO announcements (title, body, author_id, priority, status, target_audience, attachments, scheduled_at, published_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            
            $result = $stmt->execute([
                $title,
                $description,
                $adminId,
                $priority,
                $status,
                $targetAudienceJson,
                $attachmentsJson,
                $scheduledAt,
                $publishedAt
            ]);
            
            echo json_encode(['success' => $result]);
            exit;
        } elseif ($action === 'pin') {
            $id = $_POST['id'] ?? 0;
            $pinned = $_POST['pinned'] ?? 0;
            
            if (empty($id)) {
                echo json_encode(['success' => false, 'error' => 'Invalid announcement ID']);
                exit;
            }
            
            $stmt = $pdo->prepare('UPDATE announcements SET pinned = ? WHERE id = ?');
            $result = $stmt->execute([$pinned, $id]);
            
            echo json_encode(['success' => $result]);
            exit;
        } elseif ($action === 'archive') {
            $id = $_POST['id'] ?? 0;
            
            if (empty($id)) {
                echo json_encode(['success' => false, 'error' => 'Invalid announcement ID']);
                exit;
            }
            
            $stmt = $pdo->prepare('UPDATE announcements SET archived = 1 WHERE id = ?');
            $result = $stmt->execute([$id]);
            
            echo json_encode(['success' => $result]);
            exit;
        } elseif ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            
            if (empty($id)) {
                echo json_encode(['success' => false, 'error' => 'Invalid announcement ID']);
                exit;
            }
            
            // Get announcement to delete attachments
            $stmt = $pdo->prepare('SELECT attachments FROM announcements WHERE id = ?');
            $stmt->execute([$id]);
            $announcement = $stmt->fetch();
            
            if ($announcement && $announcement['attachments']) {
                $attachments = json_decode($announcement['attachments'], true);
                if (isset($attachments['file'])) {
                    @unlink('../../uploads/announcements/' . $attachments['file']);
                }
            }
            
            $stmt = $pdo->prepare('DELETE FROM announcements WHERE id = ?');
            $result = $stmt->execute([$id]);
            
            echo json_encode(['success' => $result]);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'An error occurred: ' . $e->getMessage()]);
        exit;
    }
}

// Get filter and search parameters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$view = $_GET['view'] ?? 'all';

// Build query
$query = '
    SELECT a.id, a.title, a.body, a.published_at, a.priority, a.status, a.target_audience, a.pinned, a.archived, u.username as author
    FROM announcements a
    LEFT JOIN users u ON a.author_id = u.id
    WHERE a.archived = 0
';

$params = [];

if ($search) {
    $query .= ' AND (a.title LIKE ? OR a.body LIKE ?)';
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($status && $status !== 'all') {
    $query .= ' AND a.status = ?';
    $params[] = $status;
}

if ($view === 'archived') {
    $query = str_replace('WHERE a.archived = 0', 'WHERE a.archived = 1', $query);
}

// Add sort
if ($sort === 'oldest') {
    $query .= ' ORDER BY a.published_at ASC';
} else {
    $query .= ' ORDER BY a.pinned DESC, a.published_at DESC';
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$announcements = $stmt->fetchAll();

// Get statistics
$statsQuery = '
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = "scheduled" THEN 1 ELSE 0 END) as scheduled,
        SUM(CASE WHEN status = "expired" THEN 1 ELSE 0 END) as expired,
        SUM(CASE WHEN archived = 1 THEN 1 ELSE 0 END) as archived
    FROM announcements
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
  <title>SafeBrgy - Announcements</title>
  <link rel="icon" type="image/png" href="../../assets/img/seal.png">
  <!-- Shared Styles -->
  <link rel="stylesheet" href="../../assets/css/shared/shared-header.css">
  <link rel="stylesheet" href="../../assets/css/shared/shared_sidebar.css">
  <link rel="stylesheet" href="../../assets/css/shared/colors.css">
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="../../assets/css/admin/announcement.css">
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
        <div class="profile-avatar"><?php echo renderProfileAvatar($user, $pdo); ?></div>
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
      <li><a href="dashboard.php"<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? ' class="active"' : ''; ?>><i class="fas fa-tachometer-alt"></i> <span class="menu-label">Dashboard</span></a></li>
      <li><a href="announcement.php"<?php echo basename($_SERVER['PHP_SELF']) === 'announcement.php' ? ' class="active"' : ''; ?>><i class="fas fa-bullhorn"></i> <span class="menu-label">Announcements</span></a></li>
      <li><a href="reports.php"<?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? ' class="active"' : ''; ?>><i class="fas fa-file-alt"></i> <span class="menu-label">Reports</span></a></li>
      <li><a href="requests.php"<?php echo basename($_SERVER['PHP_SELF']) === 'requests.php' ? ' class="active"' : ''; ?>><i class="fas fa-clipboard-list"></i> <span class="menu-label">Requests</span></a></li>
      <li><a href="user_verification.php"<?php echo basename($_SERVER['PHP_SELF']) === 'user_verification.php' ? ' class="active"' : ''; ?>><i class="fas fa-check-circle"></i> <span class="menu-label">Verification</span></a></li>
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
          <h2 class="mb-2">Announcements</h2>
          <p class="text-muted">Manage and publish announcements to residents</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAnnouncementModal">
          <i class="fas fa-plus"></i> Create Announcement
        </button>
      </div>

      <!-- Statistics Cards -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="stat-card">
            <div class="stat-value"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total Announcements</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card">
            <div class="stat-value"><?php echo $stats['active']; ?></div>
            <div class="stat-label">Active</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card">
            <div class="stat-value"><?php echo $stats['scheduled']; ?></div>
            <div class="stat-label">Scheduled</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card">
            <div class="stat-value"><?php echo $stats['expired']; ?></div>
            <div class="stat-label">Expired</div>
          </div>
        </div>
      </div>

      <!-- Search and Filter -->
      <div class="card mb-4">
        <div class="card-body">
          <form method="get" class="row g-3">
            <div class="col-md-6">
              <label for="searchInput" class="form-label">Search by Title or Content</label>
              <input id="searchInput" type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="col-md-3">
              <label for="statusFilter" class="form-label">Filter by Status</label>
              <select id="statusFilter" name="status" class="form-select">
                <option value="">All Status</option>
                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="scheduled" <?php echo $status === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                <option value="expired" <?php echo $status === 'expired' ? 'selected' : ''; ?>>Expired</option>
                <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
              </select>
            </div>

            <div class="col-md-3">
              <label for="sortBy" class="form-label">Sort By</label>
              <select id="sortBy" name="sort" class="form-select">
                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest</option>
              </select>
            </div>

            <div class="col-12">
              <button type="submit" class="btn btn-outline-primary">
                <i class="fas fa-search"></i> Search
              </button>
              <a href="announcement.php" class="btn btn-outline-secondary">
                <i class="fas fa-redo"></i> Reset
              </a>
              <a href="announcement.php?view=archived" class="btn btn-outline-info ms-2">
                <i class="fas fa-archive"></i> View Archived (<?php echo $stats['archived']; ?>)
              </a>
            </div>
          </form>
        </div>
      </div>

      <!-- Announcements Table -->
      <div class="card">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th>Title</th>
                <th style="max-width: 200px;">Preview</th>
                <th>Target Audience</th>
                <th>Date Posted</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($announcements)): ?>
                <tr>
                  <td colspan="7" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                    No announcements found
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($announcements as $a): ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <?php if ($a['pinned']): ?>
                          <i class="fas fa-thumbtack text-warning" title="Pinned"></i>
                        <?php endif; ?>
                        <strong><?php echo htmlspecialchars($a['title']); ?></strong>
                      </div>
                    </td>
                    <td>
                      <small class="text-muted">
                        <?php echo htmlspecialchars(substr($a['body'], 0, 100)) . (strlen($a['body']) > 100 ? '...' : ''); ?>
                      </small>
                    </td>
                    <td>
                      <span class="badge bg-info">
                        <?php 
                          $audience = $a['target_audience'] ?? 'all';
                          echo htmlspecialchars(ucfirst($audience));
                        ?>
                      </span>
                    </td>
                    <td>
                      <small><?php echo date('M d, Y H:i', strtotime($a['published_at'])); ?></small>
                    </td>
                    <td>
                      <span class="badge bg-<?php 
                        echo match($a['priority']) {
                          'urgent' => 'danger',
                          'important' => 'warning',
                          default => 'secondary'
                        };
                      ?>">
                        <?php echo ucfirst($a['priority']); ?>
                      </span>
                    </td>
                    <td>
                      <span class="badge bg-<?php 
                        echo match($a['status']) {
                          'active' => 'success',
                          'scheduled' => 'info',
                          'expired' => 'dark',
                          default => 'secondary'
                        };
                      ?>">
                        <?php echo ucfirst($a['status']); ?>
                      </span>
                    </td>
                    <td>
                      <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewAnnouncementModal<?php echo $a['id']; ?>" title="View">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editAnnouncementModal<?php echo $a['id']; ?>" title="Edit">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-outline-warning pin-btn" data-id="<?php echo $a['id']; ?>" data-pinned="<?php echo $a['pinned']; ?>" title="Pin">
                          <i class="fas fa-thumbtack"></i>
                        </button>
                        <button type="button" class="btn btn-outline-info archive-btn" data-id="<?php echo $a['id']; ?>" title="Archive">
                          <i class="fas fa-archive"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger delete-btn" data-id="<?php echo $a['id']; ?>" title="Delete">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- CREATE ANNOUNCEMENT MODAL -->
  <div class="modal fade" id="createAnnouncementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Create New Announcement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="createAnnouncementForm" enctype="multipart/form-data">
          <div class="modal-body">
            <div class="mb-3">
              <label for="announcementTitle" class="form-label">Title *</label>
              <input id="announcementTitle" type="text" class="form-control" name="title" required>
            </div>

            <div class="mb-3">
              <label for="announcementDescription" class="form-label">Description *</label>
              <textarea id="announcementDescription" class="form-control" name="description" rows="5" required></textarea>
            </div>

            <div class="mb-3">
              <label for="announcementAttachment" class="form-label">Upload Attachment (Image or PDF)</label>
              <input id="announcementAttachment" type="file" class="form-control" name="attachment" accept="image/*,.pdf">
              <small class="text-muted">Accepted: Images (JPG, PNG, GIF) or PDF</small>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="announcementPriority" class="form-label">Priority *</label>
                <select id="announcementPriority" class="form-select" name="priority" required>
                  <option value="normal">Normal</option>
                  <option value="important">Important</option>
                  <option value="urgent">Urgent</option>
                </select>
              </div>

              <div class="col-md-6 mb-3">
                <label for="announcementTargetAudience" class="form-label">Target Audience *</label>
                <select id="announcementTargetAudience" class="form-select" name="target_audience" required>
                  <option value="all">All Residents</option>
                  <option value="age:18-25">Age 18-25</option>
                  <option value="age:26-40">Age 26-40</option>
                  <option value="age:41-60">Age 41-60</option>
                  <option value="age:60+">Age 60+</option>
                  <option value="education:elementary">Elementary</option>
                  <option value="education:secondary">Secondary</option>
                  <option value="education:tertiary">Tertiary</option>
                </select>
              </div>
            </div>

            <div class="mb-3">
              <label for="announcementScheduledAt" class="form-label">Schedule for Future (Optional)</label>
              <input id="announcementScheduledAt" type="datetime-local" class="form-control" name="scheduled_at">
              <small class="text-muted">Leave empty to publish immediately</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Create Announcement</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php foreach ($announcements as $a): ?>
    <!-- VIEW ANNOUNCEMENT MODAL -->
    <div class="modal fade" id="viewAnnouncementModal<?php echo $a['id']; ?>" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><?php echo htmlspecialchars($a['title']); ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p class="text-muted small">
              Posted: <?php echo date('M d, Y H:i', strtotime($a['published_at'])); ?> by <?php echo htmlspecialchars($a['author'] ?? 'Admin'); ?>
            </p>
            <div class="mb-3">
              <p><?php echo htmlspecialchars($a['body']); ?></p>
            </div>
            <div class="row">
              <div class="col-md-6">
                <strong>Priority:</strong> <span class="badge bg-<?php 
                  echo match($a['priority']) {
                    'urgent' => 'danger',
                    'important' => 'warning',
                    default => 'secondary'
                  };
                ?>"><?php echo ucfirst($a['priority']); ?></span>
              </div>
              <div class="col-md-6">
                <strong>Status:</strong> <span class="badge bg-<?php 
                  echo match($a['status']) {
                    'active' => 'success',
                    'scheduled' => 'info',
                    'expired' => 'dark',
                    default => 'secondary'
                  };
                ?>"><?php echo ucfirst($a['status']); ?></span>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

<!-- Shared JS -->
<script src="../../assets/js/shared/logo_functions.js"></script>
<script src="../../assets/js/shared/shared-header.js"></script>
<script src="../../assets/js/shared/shared-sidebar.js"></script>
<script src="../../assets/js/shared/layout_functions.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Page-specific JS -->
<script src="../../assets/js/admin/announcement.js"></script>
</body>
</html>
