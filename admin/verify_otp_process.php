<?php
require_once __DIR__ . '/../config/db.php';
session_start();

function redirectToOtp(string $message)
{
    $_SESSION['flash_error'] = $message;
    header('Location: /safebrgy/admin/otp-view.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /safebrgy/admin/login.php');
    exit;
}

if (empty($_SESSION['pending_verification']) || empty($_SESSION['pending_user_id'])) {
    header('Location: /safebrgy/admin/login.php');
    exit;
}

$otpCode = trim($_POST['otp_code'] ?? '');
if ($otpCode === '') {
    $otpCode = '';
    for ($i = 1; $i <= 7; $i++) {
        $otpCode .= trim($_POST['otp' . $i] ?? '');
    }
}

if (!preg_match('/^\d{7}$/', $otpCode)) {
    redirectToOtp('Please enter the 7-digit verification code.');
}

if (empty($_SESSION['otp_code']) || time() > ($_SESSION['otp_expires'] ?? 0)) {
    redirectToOtp('Your OTP has expired. Please request a new code.');
}

if ($otpCode !== $_SESSION['otp_code']) {
    redirectToOtp('The OTP you entered is incorrect. Please try again.');
}

$pdo = safeBrgy_db_connect();
$update = $pdo->prepare('UPDATE users SET is_verified = 1, updated_at = NOW() WHERE id = :id');
$update->execute(['id' => $_SESSION['pending_user_id']]);

$adminUser = [
    'id' => $_SESSION['pending_user_id'],
    'email' => $_SESSION['pending_email'] ?? '',
    'username' => strtok($_SESSION['pending_email'] ?? '', '@'),
];

unset(
    $_SESSION['pending_verification'],
    $_SESSION['verification_method'],
    $_SESSION['masked_target'],
    $_SESSION['pending_user_id'],
    $_SESSION['pending_email'],
    $_SESSION['otp_code'],
    $_SESSION['otp_expires']
);

$_SESSION['admin_user'] = $adminUser;
header('Location: /safebrgy/admin/main-pages/dashboard.php');
exit;
