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
$location = $_POST['location'] ?? null;

if (!$report_type || !$title || !$description) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Validate report type
if (!in_array($report_type, ['Incident', 'Lost Property', 'Blotter'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid report type']);
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
    $stmt = $pdo->prepare('
        INSERT INTO reports (case_number, user_id, report_type, title, description, location, attachments, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, "Pending")
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

    $userStmt = $pdo->prepare('SELECT r.mobile_number FROM residents r WHERE r.user_id = ?');
    $userStmt->execute([$userId]);
    $userRow = $userStmt->fetch(PDO::FETCH_ASSOC);
    $mobileNumber = $userRow['mobile_number'] ?? null;
    $residentName = trim($_SESSION['user']['name'] ?? '') ?: 'Resident';
    $email = $_SESSION['user']['email'] ?? null;

    if ($email) {
        sendReportSubmissionNotification($email, $residentName, $mobileNumber, $case_number, $userId);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Report created successfully',
        'case_number' => $case_number
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
