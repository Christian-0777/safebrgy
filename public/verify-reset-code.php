<?php
require_once __DIR__ . '/../config/db.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['password_reset_user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Your reset session has expired. Please request a new code.']);
    exit;
}

$code = trim($_POST['code'] ?? '');
if (!preg_match('/^\d{6}$/', $code)) {
    echo json_encode(['success' => false, 'message' => 'Enter the 6-digit verification code.']);
    exit;
}

$pdo = safeBrgy_db_connect();
$stmt = $pdo->prepare('SELECT id, otp_hash FROM password_reset_otps WHERE id = (SELECT MAX(id) FROM password_reset_otps WHERE user_id = :user_id AND consumed_at IS NULL AND expires_at >= NOW()) AND user_id = :same_user');
$stmt->execute(['user_id' => $_SESSION['password_reset_user_id'], 'same_user' => $_SESSION['password_reset_user_id']]);
$reset = $stmt->fetch();

if (!$reset || !hash_equals($reset['otp_hash'], hash_hmac('sha256', $code, $_SESSION['password_reset_email'] ?? ''))) {
    echo json_encode(['success' => false, 'message' => 'That code is invalid or has expired.']);
    exit;
}

$_SESSION['password_reset_verified'] = true;
$_SESSION['password_reset_otp_id'] = (int) $reset['id'];
echo json_encode(['success' => true, 'message' => 'Code verified.']);
