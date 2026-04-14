<?php
require_once __DIR__ . '/../admin_protect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['user_id'] ?? null;
$action = $data['action'] ?? null;

if (!$userId || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit;
}

$pdo = safeBrgy_db_connect();

if ($action === 'approve') {
    $stmt = $pdo->prepare('UPDATE users SET is_verified = 1, updated_at = NOW() WHERE id = :id');
    $result = $stmt->execute(['id' => $userId]);
} elseif ($action === 'reject') {
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
    $result = $stmt->execute(['id' => $userId]);
}

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
exit;
