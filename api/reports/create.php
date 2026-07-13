<?php
require_once __DIR__ . '/../../config/db.php';
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

// Handle file upload
$attachments = null;
if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
    $uploads_dir = __DIR__ . '/../../uploads/reports/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0755, true);
    }

    $file_tmp = $_FILES['picture']['tmp_name'];
    $file_name = $_FILES['picture']['name'];
    $file_size = $_FILES['picture']['size'];
    
    // Validate file
    if ($file_size > 5 * 1024 * 1024) { // 5MB limit
        echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit']);
        exit;
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = mime_content_type($file_tmp);
    
    if (!in_array($file_type, $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type']);
        exit;
    }

    // Generate unique filename
    $ext = pathinfo($file_name, PATHINFO_EXTENSION);
    $unique_name = uniqid('report_') . '_' . time() . '.' . $ext;
    $file_path = $uploads_dir . $unique_name;

    if (move_uploaded_file($file_tmp, $file_path)) {
        $attachments = json_encode(['uploads/reports/' . $unique_name]);
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
