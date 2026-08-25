<?php
require_once __DIR__ . '/../config/db.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['admin_password_reset_user_id']) || empty($_SESSION['admin_password_reset_otp_id'])) {
    echo json_encode(['success' => false, 'message' => 'Your reset session has expired. Please request a new code.']);
    exit;
}

$code = trim($_POST['code'] ?? '');
if (!preg_match('/^\d{6}$/', $code)) {
    echo json_encode(['success' => false, 'message' => 'Enter the 6-digit verification code.']);
    exit;
}

$pdo = safeBrgy_db_connect();
$stmt = $pdo->prepare('SELECT id, otp_hash FROM password_reset_otps WHERE id = :id AND user_id = :user_id AND consumed_at IS NULL AND expires_at >= NOW()');
$stmt->execute([
    'id' => $_SESSION['admin_password_reset_otp_id'],
    'user_id' => $_SESSION['admin_password_reset_user_id'],
]);
$reset = $stmt->fetch();

if (!$reset || !hash_equals($reset['otp_hash'], hash_hmac('sha256', $code, $_SESSION['admin_password_reset_email'] ?? ''))) {
    echo json_encode(['success' => false, 'message' => 'That code is invalid or has expired.']);
    exit;
}

$_SESSION['admin_password_reset_verified'] = true;
echo json_encode(['success' => true, 'message' => 'Code verified.']);
