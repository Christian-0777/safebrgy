<?php
/**
 * api/verify_otp.php
 * Lightweight check used only to give the user immediate inline feedback
 * ("Invalid or expired code") while they are typing. It does NOT consume
 * the OTP — api/register.php performs the authoritative check-and-consume
 * when the account is actually created, so this endpoint can be called
 * as many times as needed without locking the user out.
 */

declare(strict_types=1);

require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../../config/db.php';

require_post();

if (!verify_csrf((string) ($_POST['csrf_token'] ?? ''))) {
    json_response(false, 'Your session has expired. Please refresh the page and try again.', [], 419);
}

$email = clean_str($_POST['email'] ?? '');
$otp = clean_str($_POST['otp'] ?? '');

if ($email === '' || !is_valid_email($email) || !preg_match('/^\d{6}$/', $otp)) {
    json_response(false, 'Enter the 6-digit code sent to your email.', [], 422);
}

try {
    $pdo = safeBrgy_db_connect();
    $stmt = $pdo->prepare(
        'SELECT otp_hash FROM registration_otps
         WHERE email = :email AND consumed_at IS NULL AND expires_at >= NOW()
         ORDER BY id DESC LIMIT 1'
    );
    $stmt->execute(['email' => $email]);
    $row = $stmt->fetch();
} catch (PDOException $e) {
    json_response(false, 'Could not verify the code right now. Please try again.', [], 500);
}

if (!$row || !hash_equals($row['otp_hash'], hash_hmac('sha256', $otp, $email))) {
    json_response(false, 'That code is invalid or has expired.', [], 422);
}

json_response(true, 'Code verified.');
