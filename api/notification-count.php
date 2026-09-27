<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

$pdo = safeBrgy_db_connect();

$user = $_SESSION['user'] ?? null;
$adminUser = $_SESSION['admin_user'] ?? null;

$isResidentSession = is_array($user) && ($user['role'] ?? '') === 'resident';
if (!$isResidentSession && is_array($adminUser) && !empty($adminUser['id'])) {
    $user = [
        'id' => (int) ($adminUser['id'] ?? 0),
        'role' => 'admin',
        'name' => $adminUser['username'] ?? ($adminUser['email'] ?? 'Admin')
    ];
} elseif (!is_array($user) && !empty($_SESSION['user'])) {
    $user = [
        'id' => (int) ($_SESSION['admin_id'] ?? $_SESSION['admin_user']['id'] ?? 0),
        'role' => 'admin',
        'name' => (string) $_SESSION['user']
    ];
}

if (!$user && !$adminUser) {
    echo json_encode([
        'success' => false,
        'count' => 0,
        'total' => 0,
        'badges' => [],
    ]);
    exit;
}

$userRole = $user['role'] ?? 'admin';
$userId = (int) ($user['id'] ?? $adminUser['id'] ?? 0);

if ($userId <= 0) {
    echo json_encode([
        'success' => false,
        'count' => 0,
        'total' => 0,
        'badges' => [],
    ]);
    exit;
}

$allowedTargets = $userRole === 'admin'
    ? ['dashboard', 'announcement', 'reports', 'requests', 'verification']
    : ['dashboard', 'announcement', 'reports', 'requests'];

$targetList = implode(', ', array_map(function ($target) {
    return "'" . str_replace("'", "''", $target) . "'";
}, $allowedTargets));

$stmt = $pdo->prepare(
    'SELECT target, COUNT(*) AS unread_count
     FROM notifications
     WHERE user_id = :user_id AND is_read = 0 AND target IN (' . $targetList . ')
     GROUP BY target'
);
$stmt->execute(['user_id' => $userId]);

$badges = [];
$totalUnread = 0;

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $target = (string) ($row['target'] ?? '');
    if ($target === '') {
        continue;
    }

    $count = (int) ($row['unread_count'] ?? 0);
    $badges[$target] = $count;
    $totalUnread += $count;
}

foreach (['dashboard', 'announcement', 'reports', 'requests', 'verification'] as $target) {
    if (!isset($badges[$target])) {
        $badges[$target] = 0;
    }
}

if ($userRole !== 'admin') {
    foreach (['verification'] as $target) {
        unset($badges[$target]);
    }
}

$counts = [
    'total' => $totalUnread,
    'count' => $totalUnread,
    'dashboard' => (int) ($badges['dashboard'] ?? 0),
    'announcement' => (int) ($badges['announcement'] ?? 0),
    'reports' => (int) ($badges['reports'] ?? 0),
    'requests' => (int) ($badges['requests'] ?? 0),
    'verification' => (int) ($badges['verification'] ?? 0),
    'badges' => $badges,
    'success' => true,
];

if ($userRole !== 'admin') {
    unset($counts['verification']);
}

echo json_encode($counts, JSON_UNESCAPED_SLASHES);
