<?php
require_once __DIR__ . '/../../config/db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'resident') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$reportType = $_REQUEST['report_type'] ?? '';
$allowedTypes = ['Incident', 'Lost Property', 'Public Concerns', 'Blotter'];
if (!in_array($reportType, $allowedTypes, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid report type']);
    exit;
}

$pdo = safeBrgy_db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare('SELECT id, tag_name, is_predefined FROM report_tags WHERE report_type = ? ORDER BY is_predefined DESC, tag_name ASC');
    $stmt->execute([$reportType]);
    echo json_encode(['success' => true, 'tags' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$tagName = trim((string) ($_POST['tag_name'] ?? ''));
$tagName = mb_strtoupper(preg_replace('/\s+/', ' ', $tagName), 'UTF-8');
$words = preg_split('/\s+/', $tagName, -1, PREG_SPLIT_NO_EMPTY);
if ($tagName === '' || count($words) > 3 || mb_strlen($tagName, 'UTF-8') > 60 || !preg_match('/^[\p{L}\p{N}]+(?:[ \'-][\p{L}\p{N}]+)*$/u', $tagName)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tags must contain up to three words using letters and numbers.']);
    exit;
}

$stmt = $pdo->prepare('INSERT INTO report_tags (report_type, tag_name, created_by_user_id) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)');
$stmt->execute([$reportType, $tagName, $_SESSION['user']['id']]);
$tagId = (int) $pdo->lastInsertId();
$tagStmt = $pdo->prepare('SELECT id, tag_name, is_predefined FROM report_tags WHERE id = ?');
$tagStmt->execute([$tagId]);

echo json_encode(['success' => true, 'tag' => $tagStmt->fetch(PDO::FETCH_ASSOC)]);
