<?php
require_once __DIR__ . '/../admin_protect.php';
require_once __DIR__ . '/../../config/mailer.php';
// announcements.php - SafeBrgy Announcements Admin - REWORKED

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

// Handle AJAX requests for announcements
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

            if ($result) {
                $announcementId = (int) $pdo->lastInsertId();
                $residentRecipientsStmt = $pdo->prepare('SELECT u.id AS user_id, u.email, COALESCE(NULLIF(CONCAT(TRIM(res.first_name), " ", TRIM(res.last_name)), " "), u.username, "Resident") AS resident_name, res.mobile_number FROM users u LEFT JOIN residents res ON u.id = res.user_id WHERE u.role = ?');
                $residentRecipientsStmt->execute(['resident']);
                $residentRecipients = $residentRecipientsStmt->fetchAll(PDO::FETCH_ASSOC);

                $baseUrl = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost');
                $attachmentPayload = is_array($attachmentsArray) ? $attachmentsArray : [];

                foreach ($residentRecipients as $recipient) {
                    sendAnnouncementNotification(
                        $recipient['email'],
                        $recipient['resident_name'] ?? 'Resident',
                        $recipient['mobile_number'] ?? null,
                        $title,
                        $description,
                        $priority,
                        $attachmentPayload,
                        $baseUrl,
                        !empty($recipient['user_id']) ? (int) $recipient['user_id'] : null
                    );
                }
            }

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
                if (is_array($attachments)) {
                    foreach ($attachments as $attachment) {
                        if (isset($attachment['file'])) {
                            @unlink('../../uploads/announcements/' . $attachment['file']);
                        }
                    }
                }
            }
            
            $stmt = $pdo->prepare('DELETE FROM announcements WHERE id = ?');
            $result = $stmt->execute([$id]);
            
            echo json_encode(['success' => $result]);
            exit;
        }
    } catch (Throwable $e) {
      http_response_code(500);
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
        SELECT a.id, a.title, a.body, a.published_at, a.priority, a.status, a.target_audience, a.pinned, a.archived, a.attachments, u.username as author,
          (SELECT COUNT(*) FROM announcement_reads ar WHERE ar.announcement_id = a.id) AS read_count,
          (SELECT COUNT(*) FROM users residents WHERE residents.role = "resident") AS resident_count
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

// Function to display audience type
function displayAudience($audienceJson) {
    if (!$audienceJson) return 'All Residents';
    $audience = json_decode($audienceJson, true);
    $type = $audience['type'] ?? 'all';
    
    if ($type === 'other') {
        return 'Custom: ' . htmlspecialchars($audience['message'] ?? '');
    }
    return htmlspecialchars(ucfirst(str_replace(['age:', 'education:'], '', $type)));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="/safebrgy/admin/main-pages/">
  <title>SafeBrgy - Announcements</title>
  <link rel="icon" type="image/png" href="../../assets/img/seal.png">
  <!-- Shared Styles -->
  <link rel="stylesheet" href="../../assets/css/shared/shared-header.css">
  <link rel="stylesheet" href="../../assets/css/shared/shared_sidebar.css">
  <link rel="stylesheet" href="../../assets/css/shared/colors.css">
  <!-- Page-specific styles -->
  <link rel="stylesheet" href="../../assets/css/admin/announcement.css">
  <link rel="stylesheet" href="../../assets/css/shared/layout.css">
  <link rel="stylesheet" href="../../assets/css/shared/loading-overlay.css">
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

      <!-- Tabs Navigation -->
      <ul class="nav nav-tabs mb-4" id="announcementTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="tableTab" data-bs-toggle="tab" data-bs-target="#tableContent" type="button" role="tab" aria-controls="tableContent" aria-selected="true">
            <i class="fas fa-table"></i> Announcements Table
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="newsfeedTab" data-bs-toggle="tab" data-bs-target="#newsfeedContent" type="button" role="tab" aria-controls="newsfeedContent" aria-selected="false">
            <i class="fas fa-newspaper"></i> Announcements Newsfeed
          </button>
        </li>
      </ul>

      <!-- Tab Contents -->
      <div class="tab-content" id="announcementTabsContent">
        
        <!-- TABLE TAB -->
        <div class="tab-pane fade show active" id="tableContent" role="tabpanel" aria-labelledby="tableTab">
          
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
                            <?php echo displayAudience($a['target_audience']); ?>
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

        <!-- NEWSFEED TAB -->
        <div class="tab-pane fade" id="newsfeedContent" role="tabpanel" aria-labelledby="newsfeedTab">
          
          <div id="announcementNewsfeed">
            <?php if (empty($announcements)): ?>
              <div class="text-center py-5 text-muted">
                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                <p>No announcements available</p>
              </div>
            <?php else: ?>
              <?php foreach ($announcements as $a): ?>
                <div class="card mb-4 announcement-card">
                  <div class="card-header bg-light border-bottom">
                    <div class="d-flex justify-content-between align-items-start">
                      <div>
                        <h5 class="card-title mb-1">
                          <?php if ($a['pinned']): ?>
                            <i class="fas fa-thumbtack text-warning me-2"></i>
                          <?php endif; ?>
                          <?php echo htmlspecialchars($a['title']); ?>
                        </h5>
                        <small class="text-muted">
                          Posted: <?php echo date('M d, Y H:i', strtotime($a['published_at'])); ?> by <?php echo htmlspecialchars($a['author'] ?? 'Admin'); ?>
                        </small>
                      </div>
                      <div class="d-flex gap-2">
                        <span class="badge bg-<?php 
                          echo match($a['priority']) {
                            'urgent' => 'danger',
                            'important' => 'warning',
                            default => 'secondary'
                          };
                        ?>"><?php echo ucfirst($a['priority']); ?></span>
                        <span class="badge bg-<?php 
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
                  <div class="card-body">
                    <p class="card-text"><?php echo htmlspecialchars($a['body']); ?></p>
                    
                    <?php 
                    $attachments = $a['attachments'] ? json_decode($a['attachments'], true) : [];
                    if (!empty($attachments)): 
                    ?>
                      <div class="announcement-images mt-3">
                        <div class="row g-3">
                          <?php foreach ($attachments as $attachment): ?>
                            <div class="col-md-4">
                              <img src="../../uploads/announcements/<?php echo htmlspecialchars($attachment['file']); ?>" class="img-fluid rounded" alt="Announcement Image" style="max-height: 300px; object-fit: cover;">
                            </div>
                          <?php endforeach; ?>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                      <small class="text-muted">
                        <strong>Target Audience:</strong> <?php echo displayAudience($a['target_audience']); ?>
                        <span class="ms-3"><strong>Mark as Read:</strong> <?php echo (int) $a['read_count']; ?>/<?php echo (int) $a['resident_count']; ?></span>
                      </small>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

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
              <label for="announcementAttachments" class="form-label">Upload Multiple Pictures</label>
              <input id="announcementAttachments" type="file" class="form-control" name="attachments[]" accept="image/*" multiple>
              <small class="text-muted">Accepted: JPG, PNG, GIF, WebP (max 5MB per file)</small>
              <div id="filePreview" class="mt-2"></div>
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
                <label class="form-label">Announcement Type *</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="announcement_type" id="typeImmediate" value="immediate" checked>
                  <label class="form-check-label" for="typeImmediate">
                    Publish Immediately
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="announcement_type" id="typeScheduled" value="scheduled">
                  <label class="form-check-label" for="typeScheduled">
                    Schedule for Later
                  </label>
                </div>
              </div>
            </div>

            <div id="scheduleDateWrapper" class="mb-3" style="display: none;">
              <label for="announcementScheduledAt" class="form-label">Schedule Date & Time</label>
              <input id="announcementScheduledAt" type="datetime-local" class="form-control" name="scheduled_at">
            </div>

            <div class="mb-3">
              <label for="announcementTargetAudience" class="form-label">Target Audience *</label>
              <select id="announcementTargetAudience" class="form-select" name="target_audience" required>
                <option value="all">All Residents</option>
                <option value="age:18-25">Age 18-25</option>
                <option value="age:26-40">Age 26-40</option>
                <option value="age:41-60">Age 41-60</option>
                <option value="age:60+">Age 60+</option>
                <option value="education:elementary">Elementary Education</option>
                <option value="education:secondary">Secondary Education</option>
                <option value="education:tertiary">Tertiary Education</option>
                <option value="other">Other (Custom Message)</option>
              </select>
            </div>

            <div id="otherAudienceWrapper" class="mb-3" style="display: none;">
              <label for="announcementTargetAudienceOther" class="form-label">Custom Audience Message</label>
              <textarea id="announcementTargetAudienceOther" class="form-control" name="target_audience_other" rows="3" placeholder="Describe your target audience..."></textarea>
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

  <!-- SUCCESS MODAL -->
  <div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body text-center py-5">
          <div class="mb-3">
            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
          </div>
          <h5 class="modal-title mb-2">Success!</h5>
          <p class="text-muted mb-0">Announcement Have Been Created</p>
        </div>
        <div class="modal-footer justify-content-center border-0">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- VIEW ANNOUNCEMENT MODALS -->
  <?php foreach ($announcements as $a): ?>
    <div class="modal fade" id="viewAnnouncementModal<?php echo $a['id']; ?>" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><?php echo htmlspecialchars($a['title']); ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p class="text-muted small mb-3">
              Posted: <?php echo date('M d, Y H:i', strtotime($a['published_at'])); ?> by <?php echo htmlspecialchars($a['author'] ?? 'Admin'); ?>
            </p>
            <div class="mb-3">
              <p><?php echo htmlspecialchars($a['body']); ?></p>
            </div>
            
            <?php 
            $attachments = $a['attachments'] ? json_decode($a['attachments'], true) : [];
            if (!empty($attachments)): 
            ?>
              <div class="announcement-images mb-3">
                <h6>Attachments:</h6>
                <div class="row g-3">
                  <?php foreach ($attachments as $attachment): ?>
                    <div class="col-md-6">
                      <img src="../../uploads/announcements/<?php echo htmlspecialchars($attachment['file']); ?>" class="img-fluid rounded" alt="Announcement Image" style="max-height: 300px; object-fit: cover;">
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>

            <div class="row">
              <div class="col-md-6 mb-2">
                <strong>Priority:</strong> <span class="badge bg-<?php 
                  echo match($a['priority']) {
                    'urgent' => 'danger',
                    'important' => 'warning',
                    default => 'secondary'
                  };
                ?>"><?php echo ucfirst($a['priority']); ?></span>
              </div>
              <div class="col-md-6 mb-2">
                <strong>Status:</strong> <span class="badge bg-<?php 
                  echo match($a['status']) {
                    'active' => 'success',
                    'scheduled' => 'info',
                    'expired' => 'dark',
                    default => 'secondary'
                  };
                ?>"><?php echo ucfirst($a['status']); ?></span>
              </div>
              <div class="col-12 mb-2">
                <strong>Target Audience:</strong> <?php echo displayAudience($a['target_audience']); ?>
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
<script src="../../assets/js/shared/loading-overlay.js"></script>
<script src="../../assets/js/admin/announcement.js"></script>
</body>
</html>
