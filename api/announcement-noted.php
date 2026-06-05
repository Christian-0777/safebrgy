<?php
require_once __DIR__ . '/../config/db.php';
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get announcement ID
$announcementId = $_POST['announcement_id'] ?? null;

if (!$announcementId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid announcement ID']);
    exit;
}

try {
    $pdo = safeBrgy_db_connect();
    
    // Log the action (optional: store in a notes table or admin logs)
    // For now, we just acknowledge the action was received
    
    echo json_encode(['success' => true, 'message' => 'Announcement marked as noted']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
