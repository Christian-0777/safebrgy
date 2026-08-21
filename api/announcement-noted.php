<?php
require_once __DIR__ . '/../config/db.php';
session_start();

header('Content-Type: application/json');

// Only residents can mark announcements as read.
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? null) !== 'resident') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get announcement ID
$announcementId = filter_var($_POST['announcement_id'] ?? null, FILTER_VALIDATE_INT);
$userId = filter_var($_SESSION['user']['id'] ?? null, FILTER_VALIDATE_INT);

if (!$announcementId || !$userId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid announcement ID']);
    exit;
}

try {
    $pdo = safeBrgy_db_connect();

    $announcementStmt = $pdo->prepare('SELECT id FROM announcements WHERE id = ? AND status = "active" AND archived = 0');
    $announcementStmt->execute([$announcementId]);
    if (!$announcementStmt->fetchColumn()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Announcement not found']);
        exit;
    }

    $readStmt = $pdo->prepare('INSERT IGNORE INTO announcement_reads (announcement_id, user_id) VALUES (?, ?)');
    $readStmt->execute([$announcementId, $userId]);

    echo json_encode([
        'success' => true,
        'marked_as_read' => $readStmt->rowCount() > 0,
        'message' => 'Announcement marked as read'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
