<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mailer.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (empty($_SESSION['pending_verification']) || empty($_SESSION['pending_user_id']) || empty($_SESSION['pending_email'])) {
    echo json_encode(['success' => false, 'message' => 'No verification session found.']);
    exit;
}

$otpCode = (string) random_int(1000000, 9999999);
$_SESSION['otp_code'] = $otpCode;
$_SESSION['otp_expires'] = time() + 300;

if (!sendAdminOtpEmail($_SESSION['pending_email'], $otpCode)) {
    echo json_encode(['success' => false, 'message' => 'Unable to resend OTP at this time.']);
    exit;
}

echo json_encode(['success' => true]);
exit;
