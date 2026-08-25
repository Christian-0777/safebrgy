<?php
/**
 * Announcements API Endpoint
 * GET /api/announcements - Get last 2 active announcements
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $pdo = getDBConnection();
    
    // Get last 2 active announcements ordered by published_at desc
    $stmt = $pdo->prepare("
        SELECT id, title, body, published_at, priority, attachments
        FROM announcements
        WHERE status = 'active' AND archived = 0
        ORDER BY published_at DESC
        LIMIT 2
    ");
    $stmt->execute();
    $announcements = $stmt->fetchAll();
    
    // Parse attachments JSON if present
    foreach ($announcements as &$announcement) {
        if ($announcement['attachments']) {
            $announcement['attachments'] = json_decode($announcement['attachments'], true);
        }
    }
    
    jsonResponse([
        'success' => true,
        'data' => $announcements
    ]);
    
} catch (Exception $e) {
    error_log("Announcements API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Failed to fetch announcements'], 500);
}