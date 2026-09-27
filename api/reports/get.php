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
        SELECT r.id, r.case_number, r.report_type, r.title, r.description, r.location,
               r.attachments, r.status, r.created_at, r.expires_at,
               (SELECT GROUP_CONCAT(t.tag_name ORDER BY t.tag_name SEPARATOR \', \')
            FROM report_tag_assignments a JOIN report_tags t ON t.id = a.tag_id
            WHERE a.report_id = r.id) AS tags
        FROM reports r
        WHERE r.id = ? AND r.user_id = ?
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
