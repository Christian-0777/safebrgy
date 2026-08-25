<?php
/**
 * Report Search API Endpoint
 * GET /api/reports/search?case_number=CASE-20260822-0472 - Search report by case number
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$caseNumber = strtoupper(trim((string) ($_GET['case_number'] ?? '')));
$caseNumber = preg_replace('/^CASE-/', '', $caseNumber);
$caseNumber = 'CASE-' . $caseNumber;

if (!preg_match('/^CASE-\d{8}-\d{4}$/', $caseNumber)) {
    jsonResponse(['success' => false, 'message' => 'Case number is required'], 400);
}

try {
    $pdo = getDBConnection();
    
    // Search in guest_reports table first
    $stmt = $pdo->prepare("
        SELECT 
            'guest' as source,
            id,
            case_number,
            report_type,
            title,
            description,
            location,
            attachments,
            guest_aka as reporter_name,
            contact_method,
            contact_email,
            contact_mobile,
            status,
            created_at
        FROM guest_reports
        WHERE case_number = ? AND expires_at > NOW()
    ");
    $stmt->execute([$caseNumber]);
    $report = $stmt->fetch();
    
    // If not found in guest_reports, search in reports table
    if (!$report) {
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
                u.username as reporter_name,
                NULL as contact_method,
                NULL as contact_email,
                NULL as contact_mobile,
                r.status,
                r.created_at
            FROM reports r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.case_number = ?
        ");
        $stmt->execute([$caseNumber]);
        $report = $stmt->fetch();
    }
    
    if (!$report) {
        jsonResponse(['success' => false, 'message' => 'Report not found or has expired'], 404);
    }
    
    // Parse attachments JSON if present
    if ($report['attachments']) {
        $report['attachments'] = json_decode($report['attachments'], true);
    }
    $attachmentPrefix = $report['source'] === 'guest' ? '../upload/' : '../uploads/reports/';
    $report['attachments'] = array_map(
        static fn($attachment) => $attachmentPrefix . basename((string) $attachment),
        is_array($report['attachments']) ? $report['attachments'] : []
    );
    
    jsonResponse([
        'success' => true,
        'data' => $report
    ]);
    
} catch (Exception $e) {
    error_log("Report Search API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Failed to search report'], 500);
}