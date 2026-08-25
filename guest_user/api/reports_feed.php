<?php
/**
 * Reports Feed API Endpoint
 * GET /api/reports/feed - Get lost property reports for feed tab
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
    
    // Get published lost property reports from both report sources.
    $stmt = $pdo->prepare("
        SELECT 
            'registered' as source,
            r.id,
            r.case_number,
            r.report_type,
            r.title,
            r.description,
            r.location,
            r.attachments,
            r.status,
            r.created_at,
            u.username as reporter_name
        FROM reports r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.report_type = 'Lost Property'
        AND r.status IN ('Pending', 'Ongoing', 'Resolved')
        
        UNION ALL
        
        SELECT 
            'guest' as source,
            gr.id,
            gr.case_number,
            gr.report_type,
            gr.title,
            gr.description,
            gr.location,
            gr.attachments,
            gr.status,
            gr.created_at,
            gr.guest_aka as reporter_name
        FROM guest_reports gr
        WHERE gr.report_type = 'Lost Property'
        AND gr.status IN ('Pending', 'Ongoing', 'Resolved')
        AND gr.expires_at > NOW()
        
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $stmt->execute();
    $reports = $stmt->fetchAll();
    
    // Parse attachments JSON if present
    foreach ($reports as &$report) {
        if ($report['attachments']) {
            $report['attachments'] = json_decode($report['attachments'], true);
        }
        $attachmentPrefix = $report['source'] === 'guest' ? '../upload/' : '../uploads/reports/';
        $report['attachments'] = array_map(
            static fn($attachment) => $attachmentPrefix . basename((string) $attachment),
            is_array($report['attachments']) ? $report['attachments'] : []
        );
    }
    
    jsonResponse([
        'success' => true,
        'data' => $reports
    ]);
    
} catch (Exception $e) {
    error_log("Reports Feed API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Failed to fetch reports'], 500);
}