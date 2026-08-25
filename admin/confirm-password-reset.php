<?php
require_once __DIR__ . '/../config/db.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['admin_password_reset_verified']) || empty($_SESSION['admin_password_reset_user_id']) || empty($_SESSION['admin_password_reset_otp_id'])) {
    echo json_encode(['success' => false, 'message' => 'Verify your reset code before setting a new password.']);
    exit;
}

$password = $_POST['password'] ?? '';
$confirmation = $_POST['confirmation'] ?? '';
if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
    echo json_encode(['success' => false, 'message' => 'Use at least 8 characters, one uppercase letter, one number, and one special character.']);
    exit;
}
if ($password !== $confirmation) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
    exit;
}

$pdo = safeBrgy_db_connect();
try {
    $pdo->beginTransaction();
    $userStmt = $pdo->prepare('SELECT id, username, email, role, is_verified FROM users WHERE id = :id AND role = :role AND is_verified = 1');
    $userStmt->execute(['id' => $_SESSION['admin_password_reset_user_id'], 'role' => 'admin']);
    $admin = $userStmt->fetch();
    if (!$admin) {
        throw new RuntimeException('Admin account not found.');
    }

    $update = $pdo->prepare('UPDATE users SET password_hash = :password_hash, updated_at = NOW() WHERE id = :id');
    $update->execute([
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'id' => $admin['id'],
    ]);
    $consume = $pdo->prepare('UPDATE password_reset_otps SET consumed_at = NOW() WHERE id = :id AND consumed_at IS NULL');
    $consume->execute(['id' => $_SESSION['admin_password_reset_otp_id']]);
    if ($consume->rowCount() !== 1) {
        throw new RuntimeException('Reset code has already been used.');
    }
    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $message = $exception->getMessage() === 'Admin account not found.' || $exception->getMessage() === 'Reset code has already been used.'
        ? $exception->getMessage()
        : 'Unable to reset your password right now.';
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

session_regenerate_id(true);
$_SESSION['admin_user'] = [
    'id' => (int) $admin['id'],
    'email' => $admin['email'],
    'username' => $admin['username'] ?: strtok($admin['email'], '@'),
];
unset(
    $_SESSION['admin_password_reset_user_id'],
    $_SESSION['admin_password_reset_email'],
    $_SESSION['admin_password_reset_otp_id'],
    $_SESSION['admin_password_reset_verified']
);

echo json_encode([
    'success' => true,
    'redirect' => '/safebrgy/admin/main-pages/dashboard.php',
]);
