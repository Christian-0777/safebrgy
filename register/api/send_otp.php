<?php
/**
 * api/send_otp.php
 * Generates a 6-digit OTP for the given email, stores its hash in
 * registration_otps (5 minute expiry), and emails it to the applicant.
 * Called when the user clicks "Create Account" on the password step,
 * and again for "Resend code" on the OTP step.
 */

declare(strict_types=1);

require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/mailer.php';

require_post();

if (!verify_csrf((string) ($_POST['csrf_token'] ?? ''))) {
    json_response(false, 'Your session has expired. Please refresh the page and try again.', [], 419);
}

$email = clean_str($_POST['email'] ?? '');

if ($email === '' || !is_valid_email($email)) {
    json_response(false, 'Please provide a valid email address.', [], 422);
}

$otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$otpHash = hash_hmac('sha256', $otp, $email);
$expiresAt = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
    ->modify('+5 minutes')
    ->format('Y-m-d H:i:s');

try {
    $pdo = safeBrgy_db_connect();
    $stmt = $pdo->prepare(
        'INSERT INTO registration_otps (email, otp_hash, expires_at) VALUES (:email, :otp_hash, :expires_at)'
    );
    $stmt->execute([
        'email'      => $email,
        'otp_hash'   => $otpHash,
        'expires_at' => $expiresAt,
    ]);
} catch (PDOException $e) {
    json_response(false, 'Could not generate a verification code right now. Please try again.', [], 500);
}

/*
 * Send the email. mail() is used here so the endpoint works out of the
 * box; swap this block for PHPMailer/SMTP (or an SMS gateway such as
 * Semaphore/Twilio, if you'd rather verify by text) in production:
 *
 *   $mailer = new PHPMailer\PHPMailer\PHPMailer(true);
 *   $mailer->isSMTP();
 *   ...
 */
$subject = 'SafeBrgy resident registration verification code';
$body = "<p>Your resident registration verification code is <strong>{$otp}</strong>.</p><p>This code expires in 5 minutes.</p>";
$mailSent = sendMail($email, $subject, $body);

if (!$mailSent && !(getenv('APP_ENV') === 'local')) {
    json_response(false, 'Could not send the verification code. Please try again later.', [], 502);
}

$response = ['expiresInSeconds' => 300];

// Convenience for local testing only — never expose the OTP in production.
if (defined('DEV_MODE') && DEV_MODE) {
    $response['devOtp'] = $otp;
}

json_response(true, 'A verification code has been sent to your email.', $response);
