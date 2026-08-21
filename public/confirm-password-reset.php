<?php
require_once __DIR__ . '/../config/db.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['password_reset_verified']) || empty($_SESSION['password_reset_otp_id'])) {
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
$pdo->beginTransaction();
try {
    $userStmt = $pdo->prepare('SELECT u.id, u.email, u.role, u.username, r.first_name, r.last_name, r.mobile_number FROM users u LEFT JOIN residents r ON r.user_id = u.id WHERE u.id = :user_id AND u.role = \'resident\' AND u.is_verified = 1');
    $userStmt->execute(['user_id' => $_SESSION['password_reset_user_id']]);
    $user = $userStmt->fetch();
    if (!$user) {
        throw new RuntimeException('Account not found.');
    }

    $update = $pdo->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?');
    $update->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
    $consume = $pdo->prepare('UPDATE password_reset_otps SET consumed_at = NOW() WHERE id = ? AND consumed_at IS NULL');
    $consume->execute([$_SESSION['password_reset_otp_id']]);
    if ($consume->rowCount() !== 1) {
        throw new RuntimeException('Reset code has already been used.');
    }
    $pdo->commit();
} catch (Throwable $exception) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $exception->getMessage() === 'Account not found.' ? $exception->getMessage() : 'Unable to reset your password right now.']);
    exit;
}

$_SESSION['user'] = [
    'id' => $user['id'],
    'email' => $user['email'],
    'role' => $user['role'],
    'name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: $user['username'],
    'phone' => $user['mobile_number'] ?? '',
];
unset($_SESSION['password_reset_user_id'], $_SESSION['password_reset_email'], $_SESSION['password_reset_verified'], $_SESSION['password_reset_otp_id']);
echo json_encode(['success' => true, 'redirect' => '/safebrgy/dashboard']);
