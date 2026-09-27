<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user']) && empty($_SESSION['admin_user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required.']);
    exit;
}

$user = $_SESSION['user'] ?? null;
$isResidentSession = is_array($user) && ($user['role'] ?? '') === 'resident';
if (!$isResidentSession && is_array($_SESSION['admin_user'] ?? null) && !empty($_SESSION['admin_user']['id'])) {
    $user = [
        'id' => (int) ($_SESSION['admin_user']['id'] ?? 0),
        'role' => 'admin',
        'name' => $_SESSION['admin_user']['username'] ?? ($_SESSION['admin_user']['email'] ?? 'Admin')
    ];
}
if (!is_array($user) && !empty($_SESSION['user'])) {
    $user = [
        'id' => (int) ($_SESSION['admin_id'] ?? $_SESSION['admin_user']['id'] ?? 0),
        'role' => 'admin',
        'name' => (string) $_SESSION['user']
    ];
}
$userRole = $user['role'] ?? 'admin';
$userId = (int) ($user['id'] ?? $_SESSION['admin_user']['id'] ?? 0);

if ($userId <= 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User session is invalid.']);
    exit;
}

$pdo = safeBrgy_db_connect();
$action = strtolower(trim((string) ($_GET['action'] ?? $_POST['action'] ?? '')));
$lastId = max(0, (int) ($_GET['last_id'] ?? 0));

if ($action === 'read_entity') {
    $entityType = trim((string) ($_GET['entity_type'] ?? $_POST['entity_type'] ?? ''));
    $entityId = max(0, (int) ($_GET['entity_id'] ?? $_POST['entity_id'] ?? 0));
    $allowedEntityTypes = ['report', 'request', 'announcement'];

    if ($entityId <= 0 || !in_array($entityType, $allowedEntityTypes, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid notification entity.']);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND entity_type = :entity_type AND entity_id = :entity_id AND is_read = 0');
    $stmt->execute(['user_id' => $userId, 'entity_type' => $entityType, 'entity_id' => $entityId]);
    echo json_encode(['success' => true, 'entity_type' => $entityType, 'entity_id' => $entityId]);
    exit;
}

if ($action === 'read') {
    $target = trim((string) ($_GET['target'] ?? $_POST['target'] ?? ''));
    $allowedTargets = ['dashboard', 'announcement', 'reports', 'requests', 'verification'];

    if ($target === '' || !in_array($target, $allowedTargets, true)) {
        echo json_encode(['success' => false, 'error' => 'Invalid target.']);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND target = :target AND entity_id IS NULL AND is_read = 0');
    $stmt->execute(['user_id' => $userId, 'target' => $target]);

    echo json_encode(['success' => true, 'target' => $target]);
    exit;
}

$targetClause = $userRole === 'admin' ? " AND target IN ('dashboard', 'announcement', 'reports', 'requests', 'verification')" : " AND target IN ('dashboard', 'announcement', 'reports', 'requests')";

$stmt = $pdo->prepare(
    'SELECT id, type, target, title, message, target_url, entity_type, entity_id, is_read, created_at
     FROM notifications
     WHERE user_id = :user_id' . $targetClause . ' AND id > :last_id
     ORDER BY id DESC
     LIMIT 50'
);
$stmt->execute(['user_id' => $userId, 'last_id' => $lastId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$badges = [];
$badgeStmt = $pdo->prepare(
    'SELECT target, COUNT(*) AS unread_count
     FROM notifications
     WHERE user_id = :user_id AND is_read = 0' . $targetClause . '
     GROUP BY target'
);
$badgeStmt->execute(['user_id' => $userId]);
foreach ($badgeStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $badges[$row['target']] = (int) $row['unread_count'];
}

foreach (['dashboard', 'announcement', 'reports', 'requests', 'verification'] as $target) {
    if (!isset($badges[$target])) {
        $badges[$target] = 0;
    }
}

foreach (['dashboard', 'announcement', 'reports', 'requests', 'verification'] as $target) {
    if ($userRole !== 'admin' && !in_array($target, ['dashboard', 'announcement', 'reports', 'requests'], true)) {
        unset($badges[$target]);
    }
}

$notificationRows = array_reverse($notifications);

foreach ($notificationRows as &$notification) {
    $notification['type'] = (string) ($notification['type'] ?? 'info');
    $notification['title'] = (string) ($notification['title'] ?? 'Notification');
    $notification['message'] = (string) ($notification['message'] ?? '');
    if (empty($notification['message']) && !empty($notification['target_url'])) {
        $notification['message'] = 'You have a new update.';
    }
}
unset($notification);

echo json_encode([
    'success' => true,
    'notifications' => $notificationRows,
    'badges' => $badges,
    'server_time' => date('Y-m-d H:i:s')
], JSON_UNESCAPED_SLASHES);
