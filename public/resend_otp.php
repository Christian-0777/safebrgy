<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mailer.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$type = $_GET['type'] ?? 'login';

if ($type === 'registration') {
    if (!isset($_SESSION['pending_registration'])) {
        echo json_encode(['success' => false, 'message' => 'No pending registration']);
        exit;
    }

    $otp = generateOTP();
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    $_SESSION['pending_registration']['otp'] = $otp;
    $_SESSION['pending_registration']['otp_expiry'] = $otp_expiry;

    $email = $_SESSION['pending_registration']['email'];
    if (sendOTPEmail($email, $otp)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send OTP']);
    }
} elseif ($type === 'login') {
    // For admin login resend
    if (!isset($_SESSION['pending_verification'])) {
        echo json_encode(['success' => false, 'message' => 'No pending verification']);
        exit;
    }

    // Similar logic for admin
    echo json_encode(['success' => false, 'message' => 'Not implemented']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid type']);
}

function generateOTP() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

function sendOTPEmail($email, $otp) {
    $subject = 'SafeBrgy - Verify Your Account';
    $htmlBody = "
    <html>
    <body>
        <h2>SafeBrgy OTP</h2>
        <p>Your new OTP code is: <strong>$otp</strong></p>
        <p>This code will expire in 10 minutes.</p>
    </body>
    </html>
    ";
    return sendMail($email, $subject, $htmlBody);
}
?>