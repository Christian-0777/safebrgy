<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mailer.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$email = strtolower(trim($_POST['email'] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Enter a valid email address.']);
    exit;
}

$pdo = safeBrgy_db_connect();
$stmt = $pdo->prepare('SELECT id, email FROM users WHERE email = :email AND role = :role AND is_verified = 1');
$stmt->execute(['email' => $email, 'role' => 'admin']);
$admin = $stmt->fetch();

if (!$admin) {
    echo json_encode(['success' => false, 'message' => 'No verified admin account was found with that email.']);
    exit;
}

$otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$otpHash = hash_hmac('sha256', $otp, $email);
$expiresAt = date('Y-m-d H:i:s', time() + 300);

try {
    $pdo->beginTransaction();
    $pdo->prepare('UPDATE password_reset_otps SET consumed_at = NOW() WHERE user_id = :user_id AND consumed_at IS NULL')
        ->execute(['user_id' => $admin['id']]);
    $insert = $pdo->prepare('INSERT INTO password_reset_otps (user_id, email, otp_hash, expires_at) VALUES (:user_id, :email, :otp_hash, :expires_at)');
    $insert->execute([
        'user_id' => $admin['id'],
        'email' => $email,
        'otp_hash' => $otpHash,
        'expires_at' => $expiresAt,
    ]);
    $otpId = (int) $pdo->lastInsertId();
    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($exception->getMessage());
    echo json_encode(['success' => false, 'message' => 'Unable to create a reset code right now.']);
    exit;
}

$subject = 'SafeBrgy admin password reset code';
$body = '<p>Your SafeBrgy admin password reset code is <strong>' . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') . '</strong>.</p><p>This code expires in 5 minutes.</p><p>If you did not request this, you can ignore this email.</p>';
if (!sendMail($email, $subject, $body)) {
    $pdo->prepare('UPDATE password_reset_otps SET consumed_at = NOW() WHERE id = :id AND consumed_at IS NULL')->execute(['id' => $otpId]);
    echo json_encode(['success' => false, 'message' => 'Unable to send the reset code. Please try again later.']);
    exit;
}

$_SESSION['admin_password_reset_user_id'] = (int) $admin['id'];
$_SESSION['admin_password_reset_email'] = $email;
$_SESSION['admin_password_reset_otp_id'] = $otpId;
unset($_SESSION['admin_password_reset_verified']);

$response = ['success' => true, 'message' => 'A reset code was sent to your email.'];
if (defined('DEV_MODE') && DEV_MODE) {
    $response['devOtp'] = $otp;
}
echo json_encode($response);
