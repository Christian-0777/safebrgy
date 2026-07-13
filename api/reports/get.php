<?php
require_once __DIR__ . '/../../config/db.php';
session_start();

// Check if user is logged in and is a resident
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'resident') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user']['id'];
$reportId = $_GET['id'] ?? null;

if (!$reportId) {
    echo json_encode(['success' => false, 'message' => 'Report ID is required']);
    exit;
}

$pdo = safeBrgy_db_connect();

try {
    $stmt = $pdo->prepare('
        SELECT id, case_number, report_type, title, description, location, attachments, status, created_at
        FROM reports
        WHERE id = ? AND user_id = ?
    ');

    $stmt->execute([$reportId, $userId]);
    $report = $stmt->fetch();

    if (!$report) {
        echo json_encode(['success' => false, 'message' => 'Report not found']);
        exit;
    }

    // Parse attachments JSON
    if ($report['attachments']) {
        $report['attachments'] = json_decode($report['attachments'], true);
    } else {
        $report['attachments'] = [];
    }

    echo json_encode([
        'success' => true,
        'report' => $report
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
