<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/mailer.php';
session_start();

// Check if user is logged in and is a resident
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'resident') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user']['id'];
$pdo = safeBrgy_db_connect();

// Validate input
$report_type = $_POST['report_type'] ?? null;
$title = $_POST['title'] ?? null;
$description = $_POST['description'] ?? null;
$location = trim((string) ($_POST['location'] ?? ''));
$tagIds = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['tag_ids'] ?? [])))));

if (!$report_type || !$title || !$description || $location === '') {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Validate report type
if (!in_array($report_type, ['Incident', 'Lost Property', 'Public Concerns', 'Blotter'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid report type']);
    exit;
}

if (count($tagIds) < 1 || count($tagIds) > 5) {
    echo json_encode(['success' => false, 'message' => 'Choose between one and five tags.']);
    exit;
}

$tagPlaceholders = implode(',', array_fill(0, count($tagIds), '?'));
$tagStmt = $pdo->prepare("SELECT id FROM report_tags WHERE report_type = ? AND id IN ({$tagPlaceholders})");
$tagStmt->execute(array_merge([$report_type], $tagIds));
if (count($tagStmt->fetchAll(PDO::FETCH_COLUMN)) !== count($tagIds)) {
    echo json_encode(['success' => false, 'message' => 'One or more tags do not match the selected report type.']);
    exit;
}

$attachments = null;
if (isset($_FILES['picture']) && !empty($_FILES['picture']['name'][0])) {
    $uploads_dir = __DIR__ . '/../../uploads/reports/';

    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0755, true);
    }

    $fileCount = count($_FILES['picture']['name']);
    if ($fileCount > 10) {
        echo json_encode(['success' => false, 'message' => 'You can upload up to 10 pictures.']);
        exit;
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $attachmentPaths = [];

    for ($index = 0; $index < $fileCount; $index++) {
        if ($_FILES['picture']['error'][$index] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'One or more pictures could not be uploaded.']);
            exit;
        }

        $file_tmp = $_FILES['picture']['tmp_name'][$index];
        $file_type = mime_content_type($file_tmp);
        if (!in_array($file_type, $allowed_types, true)) {
            echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, GIF, and WEBP pictures are allowed.']);
            exit;
        }

        $ext = strtolower(pathinfo($_FILES['picture']['name'][$index], PATHINFO_EXTENSION));
        $unique_name = uniqid('report_', true) . '.' . $ext;
        $file_path = $uploads_dir . $unique_name;

        if (!move_uploaded_file($file_tmp, $file_path)) {
            echo json_encode(['success' => false, 'message' => 'One or more pictures could not be saved.']);
            exit;
        }

        $attachmentPaths[] = 'uploads/reports/' . $unique_name;
    }

    if ($attachmentPaths) {
        $attachments = json_encode($attachmentPaths);
    }
}

// Generate case number
$case_number = 'CASE-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('
        INSERT INTO reports (case_number, user_id, report_type, title, description, location, attachments, status, expires_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, "Pending", DATE_ADD(UTC_TIMESTAMP(), INTERVAL 15 DAY))
    ');

    $stmt->execute([
        $case_number,
        $userId,
        $report_type,
        $title,
        $description,
        $location,
        $attachments
    ]);

    $reportId = (int) $pdo->lastInsertId();
    $assignmentStmt = $pdo->prepare('INSERT INTO report_tag_assignments (report_id, tag_id) VALUES (?, ?)');
    foreach ($tagIds as $tagId) {
        $assignmentStmt->execute([$reportId, $tagId]);
    }
    $pdo->commit();

    $userStmt = $pdo->prepare('SELECT r.mobile_number FROM residents r WHERE r.user_id = ?');
    $userStmt->execute([$userId]);
    $userRow = $userStmt->fetch(PDO::FETCH_ASSOC);
    $mobileNumber = $userRow['mobile_number'] ?? null;
    $residentName = trim($_SESSION['user']['name'] ?? '') ?: 'Resident';
    $email = $_SESSION['user']['email'] ?? null;

    if ($email) {
        sendReportSubmissionNotification($email, $residentName, $mobileNumber, $case_number, $userId);
    }

    $adminIds = $pdo->query("SELECT id FROM users WHERE role = 'admin'")->fetchAll(PDO::FETCH_COLUMN);
    safeBrgy_notify_users(
        $pdo,
        $adminIds,
        'new_report',
        'reports',
        'New Incident Report',
        $residentName . ' submitted a new incident report.',
        '/public/public-pages/reports.php',
        'report',
        $reportId
    );

    echo json_encode([
        'success' => true,
        'message' => 'Report created successfully',
        'case_number' => $case_number
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
